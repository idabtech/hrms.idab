<?php

namespace App\Listeners;

use App\Events\VerifyReCaptchaToken;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class VerifyReCaptchaTokenLis
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(VerifyReCaptchaToken $event)
    {
        $request   = $event->request->all();
        $token     = $request['g-recaptcha-response'] ?? '';
        $creatorId = $event->creatorId ?? null;

        // reCAPTCHA keys are always stored under the super admin (created_by=1).
        // Do NOT use the company's creatorId — the keys live in the global settings.
        $superAdminUser = \App\Models\User::where('type', 'super admin')->first();
        $superAdminId   = $superAdminUser ? $superAdminUser->id : 1;
        $settings       = \App\Models\Utility::fetchSettings($superAdminId);

        $secretKey = $settings['google_recaptcha_secret'] ?? '';

        if (empty($secretKey)) {
            return ['status' => true]; // no secret configured — don't block login
        }

        if (empty($token)) {
            return ['status' => true];
        }

        try {
            $context = stream_context_create([
                'http' => [
                    'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query(['secret' => $secretKey, 'response' => $token]),
                    'timeout' => 10,
                ],
            ]);

            $raw      = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
            $response = json_decode($raw);

            if (!$response || !$response->success) {
                $errors = implode(',', (array)($response->{'error-codes'} ?? []));

                return ['status' => false, 'error' => $errors];
            }

            // v3 score check — 0.5 is Google's recommended threshold
            if (isset($response->score) && $response->score < 0.5) {
                return ['status' => false, 'error' => 'score-too-low:' . $response->score];
            }

            return ['status' => true];

        } catch (\Exception $e) {
            return ['status' => true];
        }
    }
}
