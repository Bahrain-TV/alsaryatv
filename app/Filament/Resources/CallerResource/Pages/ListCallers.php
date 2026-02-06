<?php

namespace App\Filament\Resources\CallerResource\Pages;

use App\Filament\Resources\CallerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCallers extends ListRecords
{
    protected static string $resource = CallerResource::class;

    protected static ?string $title = 'المتصلين';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
