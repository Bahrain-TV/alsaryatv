<?php

namespace App\Services;

use App\Helpers\FormatHelper;
use App\Models\Caller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NtfyNotifier
{
    public function notifyRegistration(Caller $caller): void
    {
        $message = sprintf(
            'New registration: %s (CPR: %s, Phone: %s)',
            $caller->name,
            FormatHelper::maskCpr($caller->cpr),
            FormatHelper::maskPhone($caller->phone)
        );

        $this->send('New Registration', $message);
    }

    public function notifyWinner(Caller $caller): void
    {
        $message = sprintf(
            'New winner: %s (CPR: %s)',
            $caller->name,
            FormatHelper::maskCpr($caller->cpr)
        );

        $this->send('Winner Selected', $message);
    }

    private function send(string $title, string $message): void
    {
        $url = config('services.ntfy.url');
        if (! $url) {
            return;
        }

        try {
            $response = Http::withHeaders([
                'Title' => $title,
                'Priority' => '4',
            ])->post($url, $message);

            if ($response->failed()) {
                Log::warning('Ntfy notification failed', [
                    'title' => $title,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Ntfy notification request failed', [
                'title' => $title,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
