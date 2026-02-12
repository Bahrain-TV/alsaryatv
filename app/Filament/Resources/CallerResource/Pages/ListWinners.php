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

    protected static ?string $navigationLabel = 'الفائزون';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-trophy';

    protected static \UnitEnum|string|null $navigationGroup = 'إدارة المتصلين';

    protected function getTableQuery(): Builder
    {
        return Caller::query()->where('is_winner', true);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label('الاسم')
                ->sortable()
                ->searchable(),
            TextColumn::make('phone')
                ->label('رقم الهاتف')
                ->searchable(),
            TextColumn::make('cpr')
                ->label('الرقم الشخصي')
                ->searchable(),
            TextColumn::make('hits')
                ->label('عدد المشاركات')
                ->sortable(),
            TextColumn::make('created_at')
                ->label('تاريخ التسجيل')
                ->sortable()
                ->dateTime('Y-m-d H:i:s'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Action::make('toggle_winner')
                ->icon(fn ($record): string => $record->is_winner ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                ->label(fn ($record): string => $record->is_winner ? 'إلغاء الفائز' : 'تحديد كفائز')
                ->color(fn ($record): string => $record->is_winner ? 'success' : 'warning')
                ->action(function ($record): void {
                    $record->update(['is_winner' => ! $record->is_winner]);
                    $this->notify('success', 'تم تحديث حالة الفائز.');
                })
                ->requiresConfirmation(),
        ];
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100];
    }
}
