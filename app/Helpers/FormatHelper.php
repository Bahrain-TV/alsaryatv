<?php

namespace App\Helpers;

class FormatHelper
{
    /**
     * Format bytes into a human-readable string (B, KB, MB, GB, TB).
     */
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);

        $pow = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision).' '.$units[$pow];
    }

    /**
     * Mask a CPR number, showing only the first 3 digits.
     */
    public static function maskCpr(?string $cpr): string
    {
        if (! $cpr) {
            return 'N/A';
        }

        if (strlen($cpr) <= 3) {
            return str_repeat('*', strlen($cpr));
        }

        return substr($cpr, 0, 3).str_repeat('*', max(0, strlen($cpr) - 3));
    }

    /**
     * Mask a phone number, showing only the last 4 digits.
     */
    public static function maskPhone(?string $phone): string
    {
        if (! $phone) {
            return 'N/A';
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if (strlen($digits) <= 4) {
            return str_repeat('*', strlen($digits));
        }

        return str_repeat('*', max(0, strlen($digits) - 4)).substr($digits, -4);
    }
}
