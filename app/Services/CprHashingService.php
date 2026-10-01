<?php

namespace App\Services;

use App\Helpers\FormatHelper;
use Illuminate\Support\Facades\Hash;

class CprHashingService
{
    public function hashCpr(string $cpr): string
    {
        return Hash::make($cpr);
    }

    public function verifyCpr(string $plainCpr, string $hashedCpr): bool
    {
        return Hash::check($plainCpr, $hashedCpr);
    }

    public function maskCpr(string $cpr): string
    {
        return FormatHelper::maskCpr($cpr);
    }
}
