<?php

namespace App\Filament\Widgets;

use App\Models\Caller;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentActivityWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = [
        'default' => 1,
        'sm' => 1,
        'md' => 1,
        'lg' => 1,
    ];

    protected static ?string $heading = '📋 النشاط الأخير';

    protected ?string $pollingInterval = '30s';

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
                    ->color('primary')
                    ->size('sm'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('الهاتف')
                    ->icon('heroicon-m-phone')
                    ->size('sm')
                    ->toggleable()
                    ->visibleFrom('md'),

                Tables\Columns\BadgeColumn::make('hits')
                    ->label('المشاركات')
                    ->formatStateUsing(fn (int $state): string => "{$state}")
                    ->color('info')
                    ->icon('heroicon-m-hand-raised'),

                Tables\Columns\IconColumn::make('is_winner')
                    ->label('فائز')
                    ->boolean()
                    ->trueIcon('heroicon-s-trophy')
                    ->trueColor('success')
                    ->falseIcon('heroicon-m-minus-circle')
                    ->falseColor('gray'),

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
                    })
                    ->toggleable()
                    ->visibleFrom('lg'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->since()
                    ->size('sm')
                    ->dateTimeTooltip('Y-m-d H:i:s')
                    ->toggleable()
                    ->visibleFrom('md'),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->striped()
            ->emptyStateHeading('لا توجد تسجيلات حديثة')
            ->emptyStateDescription('لم يتم تسجيل أي متصلين بعد.')
            ->emptyStateIcon('heroicon-o-inbox');
    }
}
