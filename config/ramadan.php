<?php

/**
 * Ramadan Configuration
 *
 * Configuration for Ramadan dates and settings for the AlSarya TV registration system.
 */

return [
    // Gregorian date when Ramadan starts
    'start_date' => env('RAMADAN_START_DATE', '2026-02-18'),

    // Islamic (Hijri) date representation
    'hijri_date' => env('RAMADAN_HIJRI_DATE', '1 رمضان 1447 هـ'),

    // Timezone for Ramadan calculations
    'timezone' => env('RAMADAN_TIMEZONE', 'Asia/Bahrain'),
];
