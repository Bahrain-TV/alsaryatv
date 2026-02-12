<?php

namespace App\Filament\Widgets;

use App\Models\Caller;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class WinnersHistoryWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '60s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Caller::query()
                    ->where('is_winner', true)
                    ->latest('updated_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->weight('bold')
                    ->size('lg')
                    ->color('success')
                    ->formatStateUsing(fn (string $state): string => "🎉 {$state}"),

                Tables\Columns\TextColumn::make('phone')
                    ->label('الهاتف')
                    ->icon('heroicon-m-phone')
                    ->copyable()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('cpr')
                    ->label('الرقم الشخصي')
                    ->icon('heroicon-m-identification')
                    ->copyable()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('hits')
                    ->label('المشاركات')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('وقت التتويج')
                    ->since()
                    ->dateTimeTooltip('Y-m-d H:i')
                    ->color('success')
                    ->description(fn (Caller $record): string => $record->updated_at->format('H:i')),
            ])
            ->defaultSort('updated_at', 'desc')
            ->paginated([5, 10, 25])
            ->emptyStateHeading('🏜️ لا يوجد فائزون')
            ->emptyStateDescription('لم يتم اختيار أي فائز بعد. ابدأ باختيار الفائزين من قائمة المتصلين.')
            ->emptyStateIcon('heroicon-o-trophy');
    }
}
