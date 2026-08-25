<?php

use App\Mail\DownForMaintenance;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('send:callers-csv {email}', function ($email): void {
    $this->call('send:callers-csv', ['email' => $email]);
})->describe('Send a CSV copy of the callers to the specified email address');

Artisan::command('send:emails', function ($email = ''): void {
    $adminEmails = config('alsarya.admin_emails', []);

    foreach ($adminEmails as $adminEmail) {
        $this->info("Sending emails to {$adminEmail}");
        $this->call('send:callers-csv', ['email' => $adminEmail]);
    }
})->describe('Send a CSV copy of the callers to the configured admin email addresses');

Artisan::command('send:email:msg', function (): void {
    $adminEmails = config('alsarya.admin_emails', []);

    Mail::to($adminEmails)->send(new DownForMaintenance(
        120,
        'urgent database updates'
    ));

    $this->info('Emails have been sent successfully');
})->describe('Send a maintenance email to the configured admin email addresses');
