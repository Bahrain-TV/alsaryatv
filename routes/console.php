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

    // monitor the output
    $this->info('Sending emails to aldoyh@gmail.com');
    $this->call('send:callers-csv', ['email' => 'aldoyh@gmail.com']);
    if ($this->confirm('Do you want to send emails to alsaryatv@gmail.com?', true)) {
        $this->call('send:callers-csv', ['email' => 'alsaryatv@gmail.com']);
    }
})->describe('Send a CSV copy of the callers to the specified email address');

Artisan::command('send:email:msg', function (): void {
    // Or with custom downtime and reason
    Mail::to(['alsaryatv@gmail.com', 'aldoyh@gmail.com'])->send(new DownForMaintenance(
        120,
        'urgent database updates'
    ));

    $this->info('Emails have been sent successfully');
})->describe('Send a maintenance email to the specified email address');
