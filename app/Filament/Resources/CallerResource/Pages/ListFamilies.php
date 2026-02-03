<?php

namespace App\Filament\Resources\CallerResource\Pages;

use App\Filament\Resources\CallerResource;
use App\Models\Caller;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListFamilies extends ListRecords
{
    protected static string $resource = CallerResource::class;

    protected static ?string $title = 'Families List';

    protected function getTableQuery(): Builder
    {
        return Caller::query()->where('is_family', true);
    }

    protected function getHeaderActions(): array
    {
        return [
            CallerResource::getCreateFamilyAction(),
            CallerResource::getImportAction(),
        ]; // No create action since this is just a view
    }

    protected function getTableActions(): array
    {
        return []; // No edit or delete actions for families view
    }

    protected function getTableBulkActions(): array
    {
        return []; // No bulk actions for families view
    }
}
