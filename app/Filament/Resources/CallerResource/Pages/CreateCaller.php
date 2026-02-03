<?php

namespace App\Filament\Resources\CallerResource\Pages;

use App\Filament\Resources\CallerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCaller extends CreateRecord
{
    protected static string $resource = CallerResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
