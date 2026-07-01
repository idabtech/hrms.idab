<?php

namespace App\Services\Idab;

use App\Models\Employee;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * IdabApiService
 *
 * Makes outbound REST API calls FROM the HRMS TO idabcard.
 *
 * Auth flow:
 *   1. POST /api/sso-login with the company's registered SSO email → Sanctum token.
 *   2. Cache the token for 24 h (Sanctum tokens have no built-in expiry).
 *   3. Send Bearer token on every subsequent request.
 *   4. On 401, clear cache and retry once.
 *
 * Design principles:
 *   - NEVER throws — every public method is wrapped in try/catch.
 *   - Failures are logged but never interrupt the HRMS request flow.
 *   - If IDABCARD_BASE_URL or IDABCARD_SSO_EMAIL is not configured, all calls
 *     are silently skipped (debug-logged only).
 *
 * ID mapping:
 *   HRMS and idabcard use separate ID sequences. The employee's EMAIL is the
 *   shared key. We always send `email` (single) or `emails` (bulk); idabcard
 *   resolves them to its own internal user IDs automatically.
 *   For update/delete of a specific shift we use the bulk endpoints scoped to
 *   a single email + exact date range — no idabcard shift ID is stored in HRMS.
 */
class IdabApiService
{
    private string $baseUrl;
    private int    $timeout;

    public function __construct()
    {
        $this->baseUrl  = rtrim((string) config('services.idabcard.base_url', ''), '/');
        $this->timeout  = (int) config('services.idabcard.api_timeout', 15);
    }

    // =========================================================================
    // Token management
    // =========================================================================

    /**
     * Resolve the SSO email from the currently authenticated user.
     * The authenticated user (company account) is the entity registered in
     * idabcard — their email is used to obtain the Bearer token via SSO login.
     * Returns null if no user is authenticated.
     */
    private function resolveSsoEmail(): ?string
    {
        $email = Auth::user()?->email ?? null;
        return !empty($email) ? $email : null;
    }

    private function tokenCacheKey(string $email): string
    {
        return 'idabcard_token_' . md5($email);
    }

    private function getToken(bool $forceRefresh = false): ?string
    {
        $email = $this->resolveSsoEmail();

        if (empty($email)) {
            Log::warning('IdabApiService: no authenticated user — cannot resolve SSO email.');
            return null;
        }

        $cacheKey = $this->tokenCacheKey($email);

        if (!$forceRefresh && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        return $this->fetchAndCacheToken($email);
    }

    private function fetchAndCacheToken(string $email): ?string
    {
        try {
            $response = Http::timeout(10)
                ->post($this->baseUrl . '/api/v1/sso-login', [
                    'email' => $email,
                ]);

            if (!$response->successful()) {
                Log::warning('IdabApiService: SSO login failed.', [
                    'email'  => $email,
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            }

            // Strip UTF-8 BOM if present before decoding
            $body = ltrim($response->body(), "\xEF\xBB\xBF");
            $data = json_decode($body, true) ?? [];

            $token = $data['token']
                ?? $data['access_token']
                ?? ($data['data']['token'] ?? null)
                ?? ($data['data']['access_token'] ?? null);

            if (empty($token)) {
                Log::warning('IdabApiService: SSO login returned no token.', [
                    'email' => $email,
                ]);
                return null;
            }

            // Cache for 24 h as a safety refresh interval
            Cache::put($this->tokenCacheKey($email), $token, now()->addHours(24));
            Log::info('IdabApiService: SSO token obtained and cached.', [
                'email' => $email,
            ]);

            return $token;

        } catch (\Exception $e) {
            Log::error('IdabApiService: SSO login exception.', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function isConfigured(): bool
    {
        return !empty($this->baseUrl);
    }

    // =========================================================================
    // Employee email helpers
    // =========================================================================

    /**
     * Resolve a single employee's email from the HRMS Employee record.
     */
    private function resolveEmail(int $employeeId): ?string
    {
        $employee = Employee::find($employeeId);
        $email    = $employee?->email ?? null;
        return !empty($email) ? $email : null;
    }

    /**
     * Resolve multiple employee emails from HRMS Employee records.
     * Returns a flat array of non-empty email strings.
     *
     * @param  int[]  $employeeIds
     * @return string[]
     */
    private function resolveEmails(array $employeeIds): array
    {
        return Employee::whereIn('id', $employeeIds)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->pluck('email')
            ->values()
            ->toArray();
    }

    // =========================================================================
    // Public API — Shift operations
    // All methods are fire-and-forget (void) or return ?array.
    // Every method is wrapped in try/catch — failures never propagate.
    // =========================================================================

    /**
     * Create a single shift in idabcard.
     * POST /api/v1/shifts
     *
     * Called when a new date-specific rota shift is added in HRMS.
     *
     * @param  int    $employeeId  HRMS employee.id
     * @param  array  $shiftData   Keys: date (Y-m-d), start_time (H:i or H:i:s),
     *                             end_time, type, notes, breaks, location
     */
    public function createShift(int $employeeId, array $shiftData): void
    {
        try {
            $email = $this->resolveEmail($employeeId);
            if (empty($email)) {
                Log::warning('IdabApiService: createShift — no email for employee.', [
                    'employeeId' => $employeeId,
                ]);
                return;
            }

            $payload = array_filter([
                'email'      => $email,
                'date'       => $shiftData['date'] ?? null,
                'start_time' => $this->formatTime($shiftData['start_time'] ?? $shiftData['company_start_time'] ?? null),
                'end_time'   => $this->formatTime($shiftData['end_time']   ?? $shiftData['company_end_time']   ?? null),
                'type'       => $shiftData['type']     ?? 'regular',
                'status'     => $shiftData['status']   ?? 'scheduled',
                'breaks'     => $shiftData['breaks']   ?? null,
                'location'   => $shiftData['location'] ?? null,
                'notes'      => $shiftData['notes']    ?? null,
            ], fn($v) => $v !== null && $v !== '');

            $this->post('/api/v1/shifts', $payload, "createShift(employeeId={$employeeId})");

        } catch (\Throwable $e) {
            Log::error('IdabApiService: createShift exception.', [
                'employeeId' => $employeeId,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update a shift in idabcard identified by email + date.
     * Uses PUT /api/v1/shifts/bulk (bulk update scoped to one email + one date).
     *
     * Since HRMS does not store idabcard's internal shift ID, we use the
     * bulk endpoint with a single email and exact date range (same day) to
     * update the matching shift.
     *
     * @param  int    $employeeId  HRMS employee.id
     * @param  array  $shiftData   Keys: date, start_time, end_time, type, notes
     */
    public function updateShift(int $employeeId, array $shiftData): void
    {
        try {
            $email = $this->resolveEmail($employeeId);
            if (empty($email)) {
                Log::warning('IdabApiService: updateShift — no email for employee.', [
                    'employeeId' => $employeeId,
                ]);
                return;
            }

            $date = $shiftData['date'] ?? null;
            if (empty($date)) {
                Log::warning('IdabApiService: updateShift — no date provided.', [
                    'employeeId' => $employeeId,
                ]);
                return;
            }

            // Bulk create with updateExisting=true on the exact date — this
            // effectively updates the existing shift for this employee on this day.
            $payload = array_filter([
                'emails'         => [$email],
                'start_date'     => $date,
                'end_date'       => $date,
                'startTime'      => $this->formatTime($shiftData['start_time'] ?? $shiftData['company_start_time'] ?? null),
                'endTime'        => $this->formatTime($shiftData['end_time']   ?? $shiftData['company_end_time']   ?? null),
                'type'           => $shiftData['type']  ?? 'regular',
                'status'         => $shiftData['status'] ?? null,
                'notes'          => $shiftData['notes']  ?? null,
                'updateExisting' => true,
                'skipExisting'   => false,
            ], fn($v) => $v !== null && $v !== '');

            $this->post('/api/v1/shifts/bulk', $payload, "updateShift(employeeId={$employeeId}, date={$date})");

        } catch (\Throwable $e) {
            Log::error('IdabApiService: updateShift exception.', [
                'employeeId' => $employeeId,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    /**
     * Delete a single shift from idabcard identified by email + date.
     * POST /api/v1/shifts/bulk-delete (scoped to one email + one date).
     *
     * @param  int     $employeeId  HRMS employee.id
     * @param  string  $date        Y-m-d
     */
    public function deleteShift(int $employeeId, string $date): void
    {
        try {
            $email = $this->resolveEmail($employeeId);
            if (empty($email)) {
                Log::warning('IdabApiService: deleteShift — no email for employee.', [
                    'employeeId' => $employeeId,
                    'date'       => $date,
                ]);
                return;
            }

            $this->post('/api/v1/shifts/bulk-delete', [
                'emails'     => [$email],
                'start_date' => $date,
                'end_date'   => $date,
            ], "deleteShift(employeeId={$employeeId}, date={$date})");

        } catch (\Throwable $e) {
            Log::error('IdabApiService: deleteShift exception.', [
                'employeeId' => $employeeId,
                'date'       => $date,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    /**
     * Bulk create shifts in idabcard for multiple employees across a date range.
     * POST /api/v1/shifts/bulk
     *
     * Called when the HRMS publishes a rota block for multiple employees.
     *
     * @param  int[]   $employeeIds  HRMS employee IDs — emails resolved internally
     * @param  string  $startDate    Y-m-d
     * @param  string  $endDate      Y-m-d
     * @param  string  $startTime    HH:MM
     * @param  string  $endTime      HH:MM
     * @param  array   $options      Optional: type, status, repeatPattern, customDays,
     *                               breaks, location, notes, skipExisting, skipTimeOff,
     *                               updateExisting
     */
    public function bulkCreateShifts(
        array  $employeeIds,
        string $startDate,
        string $endDate,
        string $startTime,
        string $endTime,
        array  $options = []
    ): void {
        try {
            $emails = $this->resolveEmails($employeeIds);

            if (empty($emails)) {
                Log::warning('IdabApiService: bulkCreateShifts — no valid emails resolved.', [
                    'employeeIds' => $employeeIds,
                ]);
                return;
            }

            $payload = array_merge([
                'emails'       => $emails,
                'start_date'   => $startDate,
                'end_date'     => $endDate,
                'startTime'    => $this->formatTime($startTime),
                'endTime'      => $this->formatTime($endTime),
                'skipExisting' => true,
                'skipTimeOff'  => true,
            ], $options);

            // Remove null values from options that were merged in
            $payload = array_filter($payload, fn($v) => $v !== null);

            $this->post(
                '/api/v1/shifts/bulk',
                $payload,
                "bulkCreateShifts(employees=" . count($emails) . ", {$startDate} to {$endDate})"
            );

        } catch (\Throwable $e) {
            Log::error('IdabApiService: bulkCreateShifts exception.', [
                'employeeIds' => $employeeIds,
                'startDate'   => $startDate,
                'endDate'     => $endDate,
                'error'       => $e->getMessage(),
            ]);
        }
    }

    /**
     * Bulk delete shifts in idabcard for multiple employees across a date range.
     * POST /api/v1/shifts/bulk-delete
     *
     * Called when the HRMS removes a rota block for multiple employees.
     *
     * @param  int[]   $employeeIds  HRMS employee IDs — emails resolved internally
     * @param  string  $startDate    Y-m-d
     * @param  string  $endDate      Y-m-d
     * @param  array   $options      Optional: customDays (int[] 0–6)
     */
    public function bulkDeleteShifts(
        array  $employeeIds,
        string $startDate,
        string $endDate,
        array  $options = []
    ): void {
        try {
            $emails = $this->resolveEmails($employeeIds);

            if (empty($emails)) {
                Log::warning('IdabApiService: bulkDeleteShifts — no valid emails resolved.', [
                    'employeeIds' => $employeeIds,
                ]);
                return;
            }

            $payload = array_merge([
                'emails'     => $emails,
                'start_date' => $startDate,
                'end_date'   => $endDate,
            ], $options);

            $this->post(
                '/api/v1/shifts/bulk-delete',
                $payload,
                "bulkDeleteShifts(employees=" . count($emails) . ", {$startDate} to {$endDate})"
            );

        } catch (\Throwable $e) {
            Log::error('IdabApiService: bulkDeleteShifts exception.', [
                'employeeIds' => $employeeIds,
                'startDate'   => $startDate,
                'endDate'     => $endDate,
                'error'       => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify idabcard of an approved leave by creating leave-type shifts.
     * POST /api/v1/shifts (one call per day in the leave period)
     *
     * "leave" type is exempt from business-hours validation in idabcard.
     * Each day of the leave period gets a full-day leave shift so the
     * employee's card reflects the absence in real time.
     *
     * @param  int     $employeeId  HRMS employee.id
     * @param  string  $startDate   Y-m-d
     * @param  string  $endDate     Y-m-d
     * @param  string  $leaveType   Leave type title (e.g. "Annual Leave")
     * @param  string  $reason      Leave reason / notes
     */
    public function notifyLeaveApproved(
        int    $employeeId,
        string $startDate,
        string $endDate,
        string $leaveType = 'Leave',
        string $reason = ''
    ): void {
        try {
            $email = $this->resolveEmail($employeeId);
            if (empty($email)) {
                Log::warning('IdabApiService: notifyLeaveApproved — no email for employee.', [
                    'employeeId' => $employeeId,
                ]);
                return;
            }

            $notes = trim($leaveType . ($reason ? ": {$reason}" : ''));

            // Use bulk create with type=leave for the full date range — cleaner
            // than one POST per day and handles multi-day leaves efficiently.
            $this->post('/api/v1/shifts/bulk', [
                'emails'       => [$email],
                'start_date'   => $startDate,
                'end_date'     => $endDate,
                'startTime'    => '00:00',
                'endTime'      => '23:59',
                'type'         => 'leave',
                'status'       => 'confirmed',
                'notes'        => $notes,
                'skipExisting' => false,   // overwrite any existing shift for leave days
                'updateExisting' => true,
            ], "notifyLeaveApproved(employeeId={$employeeId}, {$startDate} to {$endDate})");

        } catch (\Throwable $e) {
            Log::error('IdabApiService: notifyLeaveApproved exception.', [
                'employeeId' => $employeeId,
                'startDate'  => $startDate,
                'endDate'    => $endDate,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    /**
     * Normalise a time string to HH:MM (strips seconds if present).
     * idabcard expects HH:MM format for start_time / end_time fields.
     */
    private function formatTime(?string $time): ?string
    {
        if (empty($time)) return null;
        // If already HH:MM return as-is; if HH:MM:SS strip the seconds
        return strlen($time) > 5 ? substr($time, 0, 5) : $time;
    }

    /**
     * Build an authenticated HTTP client.
     */
    private function client(string $token): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'User-Agent'    => 'hrms-idabcard-client/1.0',
        ])->timeout($this->timeout);
    }

    /**
     * POST request with automatic 401 retry.
     * Returns decoded response body on success, null on failure.
     * Never throws — all exceptions are caught and logged.
     */
    private function post(string $path, array $body, string $context = ''): ?array
    {
        if (!$this->isConfigured()) {
            Log::debug('IdabApiService: skipped (not configured).', ['path' => $path]);
            return null;
        }

        $token = $this->getToken();
        if (empty($token)) return null;

        $url = $this->baseUrl . $path;

        try {
            $response = $this->client($token)->post($url, $body);

            if ($response->status() === 401) {
                // Token may have been revoked — refresh once and retry
                $token = $this->getToken(true);
                if (empty($token)) return null;
                $response = $this->client($token)->post($url, $body);
            }

            if ($response->successful()) {
                Log::info("IdabApiService: POST ok. {$context}", ['status' => $response->status()]);
                return $response->json();
            }

            Log::warning("IdabApiService: POST non-2xx. {$context}", [
                'url'    => $url,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("IdabApiService: POST connection failed. {$context}", [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            Log::error("IdabApiService: POST unexpected error. {$context}", [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * PUT request with automatic 401 retry.
     * Never throws.
     */
    private function put(string $path, array $body, string $context = ''): ?array
    {
        if (!$this->isConfigured()) {
            Log::debug('IdabApiService: skipped (not configured).', ['path' => $path]);
            return null;
        }

        $token = $this->getToken();
        if (empty($token)) return null;

        $url = $this->baseUrl . $path;

        try {
            $response = $this->client($token)->put($url, $body);

            if ($response->status() === 401) {
                $token = $this->getToken(true);
                if (empty($token)) return null;
                $response = $this->client($token)->put($url, $body);
            }

            if ($response->successful()) {
                Log::info("IdabApiService: PUT ok. {$context}", ['status' => $response->status()]);
                return $response->json();
            }

            Log::warning("IdabApiService: PUT non-2xx. {$context}", [
                'url'    => $url,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("IdabApiService: PUT connection failed. {$context}", [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            Log::error("IdabApiService: PUT unexpected error. {$context}", [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * DELETE request with automatic 401 retry.
     * Never throws.
     */
    private function delete(string $path, string $context = ''): void
    {
        if (!$this->isConfigured()) {
            Log::debug('IdabApiService: skipped (not configured).', ['path' => $path]);
            return;
        }

        $token = $this->getToken();
        if (empty($token)) return;

        $url = $this->baseUrl . $path;

        try {
            $response = $this->client($token)->delete($url);

            if ($response->status() === 401) {
                $token = $this->getToken(true);
                if (empty($token)) return;
                $response = $this->client($token)->delete($url);
            }

            if ($response->successful()) {
                Log::info("IdabApiService: DELETE ok. {$context}", ['status' => $response->status()]);
                return;
            }

            Log::warning("IdabApiService: DELETE non-2xx. {$context}", [
                'url'    => $url,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("IdabApiService: DELETE connection failed. {$context}", [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            Log::error("IdabApiService: DELETE unexpected error. {$context}", [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
