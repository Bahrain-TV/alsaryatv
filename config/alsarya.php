<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Version
    |--------------------------------------------------------------------------
    |
    | This version is auto-incremented on each deployment.
    | Format: major.minor.patch-build (e.g., 1.2.3-456)
    |
    */
    'version' => env('APP_VERSION', '1.0.0'),
    'build' => env('APP_BUILD', '1'),

    /*
    |--------------------------------------------------------------------------
    | Ramadan Settings
    |--------------------------------------------------------------------------
    |
    | Configure the Ramadan start date and related settings.
    | The date should be in YYYY-MM-DD format.
    |
    */
    'ramadan' => [
        'start_date' => env('RAMADAN_START_DATE', '2026-02-28'),
        'hijri_date' => env('RAMADAN_HIJRI_DATE', '1 رمضان 1447 هـ'),
        'timezone' => env('RAMADAN_TIMEZONE', 'Asia/Bahrain'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Registration Settings
    |--------------------------------------------------------------------------
    */
    'registration' => [
        'open_date' => env('REGISTRATION_OPEN_DATE', '2026-03-01'),
        'enabled' => env('REGISTRATION_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Arabic Translations
    |--------------------------------------------------------------------------
    */
    'ar_translations' => [
        'title' => 'السارية',
        'description' => 'البرنامج الرئيسي المباشر على شاشة تلفزيون البحرين خلال شهر رمضان المبارك.',
        'registration_closed' => 'التسجيل مغلق حالياً',
        'registration_open_soon' => 'سيتم فتح التسجيل مع بداية شهر رمضان المبارك',
    ],
];
