<?php

namespace App\Filament\Resources\CallerResource\Pages;

use App\Filament\Resources\CallerResource;
use App\Models\Caller;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class ListWinners extends ListRecords
{
    protected static string $resource = CallerResource::class;

    protected static ?string $navigationLabel = 'Winners List';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-trophy';

    protected static \UnitEnum|string|null $navigationGroup = 'Callers';

    protected function getTableQuery(): Builder
    {
        return Caller::query()->where('is_winner', true);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label('Name')
                ->sortable()
                ->searchable(),
            TextColumn::make('phone')
                ->label('Phone')
                ->searchable(),
            TextColumn::make('cpr')
                ->label('CPR')
                ->searchable(),
            TextColumn::make('created_at')
                ->label('Registered On')
                ->sortable()
                ->dateTime('Y-m-d H:i:s'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Action::make('toggle_winner')
                ->icon(fn ($record): string => $record->is_winner ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                ->label(fn ($record): string => $record->is_winner ? 'Unmark Winner' : 'Mark as Winner')
                ->action(function ($record): void {
                    $record->update(['is_winner' => ! $record->is_winner]);
                    $this->notify('success', 'Winner status updated.');
                })
                ->requiresConfirmation(),
        ];
    }
}
