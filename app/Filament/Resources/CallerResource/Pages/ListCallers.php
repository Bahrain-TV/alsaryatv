<?php

namespace App\Filament\Resources\CallerResource\Pages;

use App\Filament\Resources\CallerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCallers extends ListRecords
{
    protected static string $resource = CallerResource::class;

    protected static ?string $title = 'Callers List';

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return \App\Models\Caller::query();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableFilters(): array
    {
        return [];
    }
}
