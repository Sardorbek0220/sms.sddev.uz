<?php
/**
 * Global formatting helpers for Phone project.
 * Auto-loaded via composer.json "files".
 *
 * Naming convention: ph_*  (Phone-prefix to avoid collisions).
 */

if (!function_exists('ph_format_duration')) {
    /**
     * Format seconds as "0:42" (mm:ss) or "1:23:45" (hh:mm:ss).
     */
    function ph_format_duration($seconds): string
    {
        $sec = (int)$seconds;
        if ($sec < 0) $sec = 0;

        $h = intdiv($sec, 3600);
        $m = intdiv($sec % 3600, 60);
        $s = $sec % 60;

        if ($h > 0) {
            return sprintf('%d:%02d:%02d', $h, $m, $s);
        }
        return sprintf('%d:%02d', $m, $s);
    }
}

if (!function_exists('ph_format_duration_human')) {
    /**
     * Human-readable: "1ч 23м" / "5м 30с" / "42с".
     */
    function ph_format_duration_human($seconds): string
    {
        $sec = (int)$seconds;
        if ($sec < 60) return $sec . 'с';
        if ($sec < 3600) {
            $m = intdiv($sec, 60);
            $s = $sec % 60;
            return $s ? "{$m}м {$s}с" : "{$m}м";
        }
        $h = intdiv($sec, 3600);
        $m = intdiv($sec % 3600, 60);
        return $m ? "{$h}ч {$m}м" : "{$h}ч";
    }
}

if (!function_exists('ph_format_phone')) {
    /**
     * Pretty-print a phone:
     *   "998902226777"   -> "+998 90 222 67 77"
     *   "902226777"      -> "+998 90 222 67 77"  (assumes UZ if 9 digits)
     *   "+1 555 1234567" -> "+1 555 1234567"     (passes other formats through)
     */
    function ph_format_phone($raw): string
    {
        $digits = preg_replace('/\D+/', '', (string)$raw);
        if ($digits === '' || $digits === null) return (string)$raw;

        // UZ short form (9 digits) — prepend country code
        if (strlen($digits) === 9 && $digits[0] === '9') {
            $digits = '998' . $digits;
        }

        if (strlen($digits) === 12 && substr($digits, 0, 3) === '998') {
            return '+998 '
                . substr($digits, 3, 2) . ' '
                . substr($digits, 5, 3) . ' '
                . substr($digits, 8, 2) . ' '
                . substr($digits, 10, 2);
        }

        // Fallback — return original prefixed with +
        return '+' . $digits;
    }
}

if (!function_exists('ph_score_variant')) {
    /**
     * Map satisfaction score (0..4 yes-count, or 1..5 rating) to a UI variant.
     * Returns one of: success | warning | danger | muted.
     */
    function ph_score_variant($score): string
    {
        if ($score === null || $score === '') return 'muted';
        $s = (int)$score;

        // 1..5 rating scale
        if ($s >= 4) return 'success';
        if ($s === 3) return 'warning';
        if ($s >= 1) return 'danger';
        return 'muted';
    }
}

if (!function_exists('ph_short_text')) {
    /**
     * Truncate text with ellipsis.
     */
    function ph_short_text($text, $maxLen = 60): string
    {
        $t = (string)$text;
        if (mb_strlen($t) <= $maxLen) return $t;
        return mb_substr($t, 0, $maxLen - 1) . '…';
    }
}

if (!function_exists('ph_relative_time')) {
    /**
     * "5 мин назад", "2 ч назад", "вчера", "26 апр".
     * Accepts unix timestamp, datetime string, or DateTimeInterface.
     */
    function ph_relative_time($input): string
    {
        if (!$input) return '—';
        if (is_numeric($input)) {
            $ts = (int)$input;
        } elseif ($input instanceof \DateTimeInterface) {
            $ts = $input->getTimestamp();
        } else {
            $ts = strtotime((string)$input);
            if ($ts === false) return (string)$input;
        }

        $diff = time() - $ts;
        if ($diff < 0) return date('d M H:i', $ts);
        if ($diff < 60) return 'только что';
        if ($diff < 3600) return intdiv($diff, 60) . ' мин назад';
        if ($diff < 86400) return intdiv($diff, 3600) . ' ч назад';
        if ($diff < 86400 * 2) return 'вчера';
        if ($diff < 86400 * 7) return intdiv($diff, 86400) . ' дн назад';
        return date('d.m.Y', $ts);
    }
}

if (!function_exists('ph_gateway_name')) {
    /**
     * Convenience wrapper around GatewayService.
     */
    function ph_gateway_name($gw): string
    {
        return \App\Services\GatewayService::name($gw);
    }
}
