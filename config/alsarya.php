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
    | First day of Ramadan 1447 AH = February 18, 2026
    |
    */
    'ramadan' => [
        'start_date' => env('RAMADAN_START_DATE', '2026-02-18'),
        'hijri_date' => env('RAMADAN_HIJRI_DATE', '1 رمضان 1447 هـ'),
        'timezone' => env('RAMADAN_TIMEZONE', 'Asia/Bahrain'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Registration Settings
    |--------------------------------------------------------------------------
    |
    | Registration opens on the first day of Ramadan
    |
    */
    'registration' => [
        'open_date' => env('REGISTRATION_OPEN_DATE', '2026-02-18'),
        'enabled' => env('REGISTRATION_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | YouTube Videos Settings
    |--------------------------------------------------------------------------
    |
    | Control the visibility of YouTube videos section on the homepage
    | Keep disabled until ready to reveal on air
    |
    */
    'youtube_videos' => [
        'enabled' => env('YOUTUBE_VIDEOS_ENABLED', false),
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
