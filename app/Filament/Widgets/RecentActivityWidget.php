<?php

namespace App\Filament\Widgets;

use App\Models\Caller;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentActivityWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

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
                    ->color('primary'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('الهاتف')
                    ->icon('heroicon-m-phone')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('hits')
                    ->label('المشاركات')
                    ->badge()
                    ->color('info'),

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
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('وقت التسجيل')
                    ->since()
                    ->dateTimeTooltip('Y-m-d H:i:s'),
            ])
            ->paginated(false)
            ->emptyStateHeading('📭 لا توجد تسجيلات حديثة')
            ->emptyStateDescription('لم يتم تسجيل أي متصلين بعد.')
            ->emptyStateIcon('heroicon-o-inbox');
    }
}
