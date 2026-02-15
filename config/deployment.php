<?php

return [
    /*
    |--------------------------------------------------------------------------
    | GitHub Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for GitHub webhook-triggered deployments.
    |
    */

    'github_webhook_secret' => env('GITHUB_WEBHOOK_SECRET', ''),

    'webhook_allowed_branches' => env('WEBHOOK_ALLOWED_BRANCHES', 'main,production'),

    'webhook_allowed_ips' => env('WEBHOOK_ALLOWED_IPS', null),

    /*
    |--------------------------------------------------------------------------
    | Deployment Lock Configuration
    |--------------------------------------------------------------------------
    |
    | Prevent concurrent deployments using file-based locks.
    |
    */

    'lock_file' => '/tmp/deploy.lock',

    'lock_timeout' => 600, // 10 minutes

];
