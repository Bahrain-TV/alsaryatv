<?php

namespace App\Filament\Resources\CallerResource\Pages;

use App\Filament\Resources\CallerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Filters\SelectFilter;

class ListCallers extends ListRecords
{
    protected static string $resource = CallerResource::class;

    protected static ?string $title = 'Callers List';

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return \App\Models\Caller::query();
    }

    protected function index(): void
    {
        $this->getTableQuery()->where('is_family', 0);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            SelectFilter::make('is_family')
                ->label('عائلات فقط')
                ->options([
                    '1' => 'Yes',
                    '0' => 'No',
                ])
                ->sortable()
                ->placeholder('All'),
        ];
    }
}
