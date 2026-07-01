<?php

if (!function_exists('normalise_phone')) {
    function normalise_phone(?string $phone): string
    {
        if (empty($phone)) return '';

        // Clean input
        $phone = trim($phone);
        $phone = preg_replace('/[^\d+]/', '', $phone);

        // Dial codes list
        $dialCodes = [
            '1','7','20','27','30','31','32','33','34','36','39','40','41','43','44','45','46','47','48','49',
            '51','52','53','54','55','56','57','58','60','61','62','63','64','65','66','81','82','84','86','90',
            '91','92','93','94','95','98','211','212','213','216','218','220','221','222','223','224','225','226',
            '227','228','229','230','231','232','233','234','235','236','237','238','239','240','241','242','243',
            '244','245','246','248','249','250','251','252','253','254','255','256','257','258','260','261','262',
            '263','264','265','266','267','268','269','290','291','297','298','299','350','351','352','353','354',
            '355','356','357','358','359','370','371','372','373','374','375','376','377','378','380','381','382',
            '383','385','386','387','389','420','421','423','500','501','502','503','504','505','506','507','508',
            '509','590','591','592','593','594','595','596','597','598','599','670','672','673','674','675','676',
            '677','678','679','680','681','682','683','685','686','687','688','689','690','691','692','850','852',
            '853','855','856','880','886','960','961','962','963','964','965','966','967','968','970','971','972',
            '973','974','975','976','977','992','993','994','995','996','998'
        ];

        // longest first
        rsort($dialCodes, SORT_STRING);

        // Handle numbers starting with +
        if (str_starts_with($phone, '+')) {
            $clean = ltrim($phone, '+');

            foreach ($dialCodes as $code) {
                if (str_starts_with($clean, $code)) {
                    $local = substr($clean, strlen($code));

                    // Remove trunk prefix (leading 0)
                    // if (str_starts_with($local, '0')) {
                    //     $local = ltrim($local, '0');
                    // }

                    return '+' . $code . ' ' . $local;
                }
            }
        }

        // Handle numbers without +
        $cleanPhone = ltrim($phone, '+');

        foreach ($dialCodes as $code) {
            if (str_starts_with($cleanPhone, $code)) {
                $local = substr($cleanPhone, strlen($code));

                if (str_starts_with($local, '0')) {
                    $local = ltrim($local, '0');
                }

                if (strlen($local) >= 4) {
                    return '+' . $code . ' ' . $local;
                }
            }
        }

        // Handle local numbers starting with 0 (fallback → assume UK-style)
        if (preg_match('/^0\d{6,}$/', $phone)) {
            return '+44 ' . ltrim($phone, '0');
        }

        // Final fallback
        return $phone ?? null;
    }
}

if (!function_exists('superAdminId')) {
    function superAdminId() {
        $user = \App\Models\User::where('type', 'super admin')->where('created_by', 0)->first();
        if ($user) {
            return $user->id;
        } else {
            return 1;
        }
    }
}

if (!function_exists('formatTime12h')) {
    function formatTime12h(?string $time): string
    {
        if (empty($time)) {
            return '';
        }

        $time = trim($time);

        // Handle time range
        if (str_contains($time, '-')) {

            $times = preg_split('/\s*-\s*/', $time);

            if (count($times) === 2) {
                return formatSingleTime12h($times[0]) . ' - ' . formatSingleTime12h($times[1]);
            }
        }

        // Handle single time
        return formatSingleTime12h($time);
    }
}

if (!function_exists('formatSingleTime12h')) {
    function formatSingleTime12h(?string $time): string
    {
        if (empty($time)) {
            return '';
        }

        $time = trim($time);

        // Accept HH:mm or HH:mm:ss
        $parts = explode(':', $time);

        if (count($parts) < 2) {
            return $time;
        }

        $hour   = (int) $parts[0];
        $minute = (int) $parts[1];

        // Validate time
        if (
            $hour < 0 || $hour > 23 ||
            $minute < 0 || $minute > 59
        ) {
            return $time;
        }

        $period = $hour >= 12 ? 'PM' : 'AM';

        $hour12 = $hour % 12;
        $hour12 = $hour12 === 0 ? 12 : $hour12;

        return sprintf('%d:%02d %s', $hour12, $minute, $period);
    }
}

if (!function_exists('formatTimeRange12h')) {
    function formatTimeRange12h(?string $start, ?string $end): string
    {
        $s = formatTime12h($start);
        $e = formatTime12h($end);

        if ($s === '' && $e === '') {
            return '';
        }
        if ($s === '') {
            return $e;
        }
        if ($e === '') {
            return $s;
        }

        return "{$s} – {$e}";
    }
}

if (!function_exists('encodeIdToken')) {
    function encodeIdToken(int $id): string
    {
        // Build 8 raw bytes: 4-byte ID + 4-byte HMAC prefix
        $idBytes = pack('N', $id);
        $hmac    = hash_hmac('sha256', $idBytes, config('app.key'), true);
        $raw     = $idBytes . substr($hmac, 0, 4); // 8 bytes

        // Convert 8 raw bytes → unsigned decimal string (pure PHP, no extensions)
        $uNum = '0';
        for ($i = 0; $i < 8; $i++) {
            // uNum = uNum * 256 + byte[i]
            $carry  = ord($raw[$i]);
            $newStr = '';
            for ($j = strlen($uNum) - 1; $j >= 0; $j--) {
                $cur    = (int) $uNum[$j] * 256 + $carry;
                $newStr = (string) ($cur % 10) . $newStr;
                $carry  = intdiv($cur, 10);
            }
            while ($carry > 0) {
                $newStr = (string) ($carry % 10) . $newStr;
                $carry  = intdiv($carry, 10);
            }
            $uNum = $newStr ?: '0';
        }

        // Base62 encode using pure string division
        $chars  = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $result = '';
        $n      = $uNum;
        do {
            $rem    = 0;
            $newStr = '';
            for ($i = 0; $i < strlen($n); $i++) {
                $cur     = $rem * 10 + (int) $n[$i];
                $newStr .= (string) intdiv($cur, 62);
                $rem     = $cur % 62;
            }
            $result = $chars[$rem] . $result;
            $n      = ltrim($newStr, '0') ?: '0';
        } while ($n !== '0');

        // Always pad to exactly 10 characters
        return str_pad($result, 10, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('decodeIdToken')) {
    function decodeIdToken(string $token): ?int
    {
        if (strlen($token) !== 10) {
            return null;
        }

        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

        // Base62 decode → unsigned decimal string (pure PHP string arithmetic)
        $uNum = '0';
        for ($i = 0; $i < 10; $i++) {
            $pos = strpos($chars, $token[$i]);
            if ($pos === false) {
                return null; // invalid character
            }
            // uNum = uNum * 62 + pos
            $carry  = $pos;
            $newStr = '';
            for ($j = strlen($uNum) - 1; $j >= 0; $j--) {
                $cur    = (int) $uNum[$j] * 62 + $carry;
                $newStr = (string) ($cur % 10) . $newStr;
                $carry  = intdiv($cur, 10);
            }
            while ($carry > 0) {
                $newStr = (string) ($carry % 10) . $newStr;
                $carry  = intdiv($carry, 10);
            }
            $uNum = $newStr ?: '0';
        }

        // Convert unsigned decimal string back to 8 raw bytes
        $raw = '';
        $n   = $uNum;
        for ($i = 0; $i < 8; $i++) {
            $rem    = 0;
            $newStr = '';
            for ($j = 0; $j < strlen($n); $j++) {
                $cur     = $rem * 10 + (int) $n[$j];
                $newStr .= (string) intdiv($cur, 256);
                $rem     = $cur % 256;
            }
            $raw = chr($rem) . $raw;
            $n   = ltrim($newStr, '0') ?: '0';
        }

        if (strlen($raw) < 8) {
            return null;
        }

        $idBytes    = substr($raw, 0, 4);
        $checkBytes = substr($raw, 4, 4);

        // Re-derive HMAC and compare in constant time
        $hmac = hash_hmac('sha256', $idBytes, config('app.key'), true);
        if (!hash_equals(substr($hmac, 0, 4), $checkBytes)) {
            return null; // tampered or wrong key
        }

        $id = unpack('N', $idBytes)[1];
        return $id > 0 ? (int) $id : null;
    }
}

if (!function_exists('getUserEmailBasedOnId')) {
    function getUserEmailBasedOnId($id)
    {
        $user = \App\Models\User::find($id);
        if ($user != null) {
            return $user->email;
        } else {
            return null;
        }
    }
}

if (!function_exists('getUserIdBasedOnEmail')) {
    function getUserIdBasedOnEmail($email)
    {
        if (empty($email)) {
            return null;
        }

        static $cache = [];

        $key = trim($email);

        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        $user = \App\Models\User::where('email', $key)->first();

        return $cache[$key] = $user?->id;
    }
}
