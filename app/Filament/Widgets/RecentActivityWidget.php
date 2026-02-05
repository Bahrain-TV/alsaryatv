<?php

namespace App\Filament\Widgets;

use App\Models\Caller;
use Filament\Support\Enums\IconSize;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentActivityWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = [
        'sm' => 1,
        'md' => 1,
        'lg' => 2,
    ];

    protected static ?string $heading = 'آخر التسجيلات';

    protected static ?string $pollingInterval = '30s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Caller::query()
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('الهاتف')
                    ->icon('heroicon-m-phone')
                    ->iconSize(IconSize::Small),

                Tables\Columns\TextColumn::make('hits')
                    ->label('المشاركات')
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('is_winner')
                    ->label('فائز')
                    ->boolean()
                    ->trueIcon('heroicon-o-trophy')
                    ->trueColor('success')
                    ->falseIcon('heroicon-o-minus')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->since()
                    ->dateTimeTooltip('Y-m-d H:i:s'),
            ])
            ->paginated(false);
    }
}
