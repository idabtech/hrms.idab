<?php

namespace App\Services;

use App\Models\Utility;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * HMRC API Service
 *
 * Handles communication with HMRC Developer Hub APIs using OAuth2
 * Client Credentials flow (application-restricted endpoints).
 *
 * No user redirect required — uses Client ID + Client Secret directly.
 */
class HmrcService
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $serverToken;
    protected string $environment;
    protected string $baseUrl;

    public function __construct()
    {
        $settings = self::getHmrcSettings();

        $this->clientId     = $settings['hmrc_client_id'] ?? '';
        $this->clientSecret = $settings['hmrc_client_secret'] ?? '';
        $this->serverToken  = $settings['hmrc_server_token'] ?? '';
        $this->environment  = $settings['hmrc_environment'] ?? 'sandbox';

        $this->baseUrl = $this->environment === 'production'
            ? 'https://api.service.hmrc.gov.uk'
            : 'https://test-api.service.hmrc.gov.uk';
    }

    /**
     * Get HMRC settings from admin_payment_settings table.
     */
    public static function getHmrcSettings(): array
    {
        $data = DB::table('admin_payment_settings')
            ->where('created_by', 1)
            ->whereIn('name', [
                'hmrc_enabled',
                'hmrc_environment',
                'hmrc_client_id',
                'hmrc_client_secret',
                'hmrc_server_token',
                'hmrc_callback_uri',
            ])
            ->pluck('value', 'name')
            ->toArray();

        return $data;
    }

    /**
     * Check if HMRC integration is enabled and configured.
     */
    public static function isEnabled(): bool
    {
        $settings = self::getHmrcSettings();

        return ($settings['hmrc_enabled'] ?? 'off') === 'on'
            && !empty($settings['hmrc_client_id'])
            && !empty($settings['hmrc_client_secret']);
    }

    /**
     * Get an OAuth2 access token using Client Credentials grant.
     * Token is cached for 4 hours (HMRC token lifetime).
     * No redirect required.
     */
    public function getAccessToken(): ?string
    {
        $cacheKey = 'hmrc_access_token_' . $this->environment;

        return Cache::remember($cacheKey, 14000, function () {
            try {
                Log::info('🔑 [HMRC OAuth Token Request] Requesting token...', [
                    'url'         => $this->baseUrl . '/oauth/token',
                    'environment' => $this->environment,
                    'client_id'   => !empty($this->clientId) ? (substr($this->clientId, 0, 6) . '...') : 'MISSING',
                ]);

                $response = Http::asForm()->post($this->baseUrl . '/oauth/token', [
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'grant_type'    => 'client_credentials',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    Log::info('✅ [HMRC OAuth Token SUCCESS] Access token received successfully.');
                    return $data['access_token'] ?? null;
                }

                Log::error('❌ [HMRC OAuth Token FAILED]', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return null;
            } catch (\Throwable $e) {
                Log::error('💥 [HMRC OAuth Token EXCEPTION]', ['error' => $e->getMessage()]);
                return null;
            }
        });
    }

    /**
     * Test connectivity to HMRC API (Hello World endpoint).
     * Uses application-restricted access — no redirect needed.
     */
    public function testConnection(): array
    {
        $token = $this->getAccessToken();

        if (!$token) {
            return [
                'success' => false,
                'message' => 'Failed to obtain access token. Check Client ID and Client Secret.',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/vnd.hmrc.1.0+json',
            ])->get($this->baseUrl . '/hello/application');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => $response->json('message') ?? 'Connection successful',
                ];
            }

            return [
                'success' => false,
                'message' => 'HMRC responded with status ' . $response->status(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Validate a UK National Insurance Number format.
     *
     * Format: 2 prefix letters + 6 digits + 1 suffix letter
     * Example: QQ 12 34 56 C
     *
     * Rules:
     * - First two characters are letters (not D, F, I, Q, U, V as first letter;
     *   not D, F, I, O, Q, U, V as second letter)
     * - Prefixes BG, GB, NK, KN, TN, NT, ZZ are not valid
     * - Next 6 characters are digits
     * - Last character is A, B, C, or D
     */
    public static function validateNinoFormat(string $nino): array
    {
        // Remove spaces and convert to uppercase
        $nino = strtoupper(preg_replace('/\s+/', '', trim($nino)));

        if (strlen($nino) !== 9) {
            return [
                'valid'   => false,
                'nino'    => $nino,
                'message' => 'NI number must be exactly 9 characters (2 letters + 6 digits + 1 letter).',
            ];
        }

        // Check overall pattern: 2 letters + 6 digits + 1 letter
        if (!preg_match('/^[A-Z]{2}\d{6}[A-D]$/', $nino)) {
            return [
                'valid'   => false,
                'nino'    => $nino,
                'message' => 'Invalid format. Must be 2 letters, followed by 6 digits, followed by A, B, C, or D.',
            ];
        }

        $prefix = substr($nino, 0, 2);
        $firstChar = $nino[0];
        $secondChar = $nino[1];

        // First letter cannot be D, F, I, Q, U, V
        if (in_array($firstChar, ['D', 'F', 'I', 'Q', 'U', 'V'])) {
            return [
                'valid'   => false,
                'nino'    => $nino,
                'message' => "First letter '{$firstChar}' is not allowed in a NI number.",
            ];
        }

        // Second letter cannot be D, F, I, O, Q, U, V
        if (in_array($secondChar, ['D', 'F', 'I', 'O', 'Q', 'U', 'V'])) {
            return [
                'valid'   => false,
                'nino'    => $nino,
                'message' => "Second letter '{$secondChar}' is not allowed in a NI number.",
            ];
        }

        // Invalid prefixes
        $invalidPrefixes = ['BG', 'GB', 'NK', 'KN', 'TN', 'NT', 'ZZ'];
        if (in_array($prefix, $invalidPrefixes)) {
            return [
                'valid'   => false,
                'nino'    => $nino,
                'message' => "Prefix '{$prefix}' is not a valid NI number prefix.",
            ];
        }

        // Format the NINO nicely: AB 12 34 56 C
        $formatted = substr($nino, 0, 2) . ' '
            . substr($nino, 2, 2) . ' '
            . substr($nino, 4, 2) . ' '
            . substr($nino, 6, 2) . ' '
            . substr($nino, 8, 1);

        return [
            'valid'     => true,
            'nino'      => $nino,
            'formatted' => $formatted,
            'message'   => 'Valid NI number format.',
        ];
    }

    /**
     * Full NINO verification:
     * 1. Format validation (HMRC rules)
     * 2. API connectivity check (confirms credentials work)
     *
     * Note: HMRC withdrew the NVR (NINO Verification Request) service in
     * February 2025. There is currently no HMRC API to verify if a specific
     * NINO belongs to a specific person. Format validation is the maximum
     * that can be done programmatically at this time.
     */
    public function verifyNino(string $nino): array
    {
        // Step 1: Format validation
        $formatResult = self::validateNinoFormat($nino);

        if (!$formatResult['valid']) {
            return [
                'success'         => false,
                'format_valid'    => false,
                'hmrc_connected'  => false,
                'nino'            => $formatResult['nino'],
                'message'         => $formatResult['message'],
            ];
        }

        // Step 2: Verify HMRC API connectivity (proves credentials are valid)
        $connectionTest = $this->testConnection();

        return [
            'success'         => true,
            'format_valid'    => true,
            'hmrc_connected'  => $connectionTest['success'],
            'nino'            => $formatResult['nino'],
            'formatted_nino'  => $formatResult['formatted'],
            'message'         => $formatResult['message'],
            'hmrc_message'    => $connectionTest['message'],
        ];
    }
}
