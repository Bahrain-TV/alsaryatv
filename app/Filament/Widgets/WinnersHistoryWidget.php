<?php

namespace App\Filament\Widgets;

use App\Models\Caller;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class WinnersHistoryWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = [
        'default' => 1,
        'sm' => 1,
        'md' => 1,
        'lg' => 1,
    ];

    protected ?string $heading = '🏆 سجل الفائزين';

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
                    ->copyMessage('تم نسخ رقم الهاتف')
                    ->tooltip('اضغط لنسخ الرقم')
                    ->toggleable()
                    ->visibleFrom('md'),

                Tables\Columns\TextColumn::make('cpr')
                    ->label('الرقم الشخصي')
                    ->icon('heroicon-m-identification')
                    ->copyable()
                    ->copyMessage('تم نسخ رقم المواطن')
                    ->tooltip('اضغط لنسخ الرقم')
                    ->toggleable()
                    ->visibleFrom('lg'),

                Tables\Columns\BadgeColumn::make('hits')
                    ->label('المشاركات')
                    ->formatStateUsing(fn (int $state): string => "{$state}")
                    ->color('warning')
                    ->icon('heroicon-m-hand-raised'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاريخ الفوز')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->tooltip(fn (Caller $record): string => $record->updated_at->format('l، d F Y H:i:s'))
                    ->description(fn (Caller $record): string => $record->updated_at->diffForHumans())
                    ->toggleable()
                    ->visibleFrom('md'),
            ])
            ->defaultSort('updated_at', 'desc')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->striped()
            ->emptyStateHeading('لا يوجد فائزون')
            ->emptyStateDescription('لم يتم اختيار أي فائز بعد. ابدأ باختيار الفائزين من قائمة المتصلين.')
            ->emptyStateIcon('heroicon-o-trophy');
    }
}
