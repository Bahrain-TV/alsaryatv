<?php

namespace App\Filament\Resources\CallerResource\Pages;

use App\Filament\Resources\CallerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCaller extends EditRecord
{
    protected static string $resource = CallerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
