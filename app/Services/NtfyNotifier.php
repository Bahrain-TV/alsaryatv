<?php

namespace App\Services;

use App\Models\Caller;
use Illuminate\Support\Facades\Http;

class NtfyNotifier
{
    public function notifyRegistration(Caller $caller): void
    {
        $message = sprintf(
            'New registration: %s (CPR: %s, Phone: %s)',
            $caller->name,
            $this->maskCpr($caller->cpr),
            $this->maskPhone($caller->phone)
        );

        $this->send('New Registration', $message);
    }

    public function notifyWinner(Caller $caller): void
    {
        $message = sprintf(
            'New winner: %s (CPR: %s)',
            $caller->name,
            $this->maskCpr($caller->cpr)
        );

        $this->send('Winner Selected', $message);
    }

    private function send(string $title, string $message): void
    {
        $url = config('services.ntfy.url');
        if (! $url) {
            return;
        }

        Http::withHeaders([
            'Title' => $title,
            'Priority' => '4',
        ])->post($url, $message);
    }

    private function maskCpr(?string $cpr): string
    {
        if (! $cpr) {
            return 'N/A';
        }

        if (strlen($cpr) <= 3) {
            return str_repeat('*', strlen($cpr));
        }

        return substr($cpr, 0, 3).str_repeat('*', max(0, strlen($cpr) - 3));
    }

    private function maskPhone(?string $phone): string
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
