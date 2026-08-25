<?php

return [
    'recipients' => [
        'to' => explode(',', env('DAILY_SELECTED_EMAIL_RECIPIENTS', env('ADMIN_EMAILS', 'admin@alsarya.tv'))),
        'cc' => [
        ],
        'bcc' => [
        ],
    ],
    'limit' => 10,
    'last_updated' => '2026-02-19T13:53:29.858647Z',
];
