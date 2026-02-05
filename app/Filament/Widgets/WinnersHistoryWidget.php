<?php

namespace App\Filament\Widgets;

use App\Models\Caller;
use Filament\Support\Enums\IconSize;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class WinnersHistoryWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = '🏆 سجل الفائزين';

    protected static ?string $pollingInterval = '60s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Caller::query()
                    ->where('is_winner', true)
                    ->latest('updated_at')
            )
            ->columns([
                Tables\Columns\IconColumn::make('is_winner')
                    ->label('')
                    ->icon('heroicon-s-trophy')
                    ->color('success')
                    ->size(IconSize::Large),

                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->weight('bold')
                    ->size('lg')
                    ->color('success'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('الهاتف')
                    ->icon('heroicon-m-phone')
                    ->copyable()
                    ->copyMessage('تم نسخ رقم الهاتف'),

                Tables\Columns\TextColumn::make('cpr')
                    ->label('الرقم الشخصي')
                    ->icon('heroicon-m-identification')
                    ->copyable()
                    ->copyMessage('تم نسخ CPR'),

                Tables\Columns\TextColumn::make('hits')
                    ->label('المشاركات')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'نشط',
                        'inactive' => 'غير نشط',
                        'blocked' => 'محظور',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'warning',
                        'blocked' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاريخ الفوز')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->description(fn (Caller $record): string => $record->updated_at->diffForHumans()),
            ])
            ->defaultSort('updated_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('removeWinner')
                    ->label('إزالة الفوز')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Caller $record): void {
                        $record->is_winner = false;
                        $record->save();
                    }),
            ])
            ->emptyStateHeading('لا يوجد فائزون')
            ->emptyStateDescription('لم يتم اختيار أي فائز بعد.')
            ->emptyStateIcon('heroicon-o-trophy');
    }
}
