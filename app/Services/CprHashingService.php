<?php

namespace App\Services;

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
        return substr($cpr, 0, 3).str_repeat('*', strlen($cpr) - 3);
    }
}
