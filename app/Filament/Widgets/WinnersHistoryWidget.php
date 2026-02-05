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

    protected static ?string $heading = '🏆 سجل الفائزين والمكافآت';

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
                Tables\Columns\TextColumn::make('name')
                    ->label('👤 الاسم')
                    ->searchable()
                    ->weight('bold')
                    ->size('lg')
                    ->color('success')
                    ->formatStateUsing(fn (string $state): string => "🎉 {$state}"),

                Tables\Columns\TextColumn::make('phone')
                    ->label('📱 الهاتف')
                    ->icon('heroicon-m-phone')
                    ->copyable()
                    ->copyMessage('تم نسخ رقم الهاتف')
                    ->tooltip('اضغط لنسخ الرقم'),

                Tables\Columns\TextColumn::make('cpr')
                    ->label('🆔 الرقم الشخصي')
                    ->icon('heroicon-m-identification')
                    ->copyable()
                    ->copyMessage('تم نسخ رقم المواطن')
                    ->tooltip('اضغط لنسخ الرقم'),

                Tables\Columns\BadgeColumn::make('hits')
                    ->label('👋 المشاركات')
                    ->formatStateUsing(fn (int $state): string => "{$state} مشاركة")
                    ->color('warning')
                    ->icon('heroicon-m-hand-raised'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('📊 الحالة')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => '✅ نشط',
                        'inactive' => '⏸️ غير نشط',
                        'blocked' => '🚫 محظور',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'warning',
                        'blocked' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('🕐 تاريخ الفوز')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->tooltip(fn (Caller $record): string => $record->updated_at->format('l، d F Y H:i:s'))
                    ->description(fn (Caller $record): string => $record->updated_at->diffForHumans()),
            ])
            ->defaultSort('updated_at', 'desc')
            ->paginated([5, 10, 25])
            ->actions([
                Tables\Actions\Action::make('removeWinner')
                    ->label('إزالة الفوز')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->confirmationHeading('إزالة حالة الفوز')
                    ->confirmationDescription('هل تريد بالفعل إزالة حالة الفوز من هذا المتصل؟')
                    ->action(function (Caller $record): void {
                        $record->is_winner = false;
                        $record->save();
                    })
                    ->successNotificationTitle('تم إزالة الفوز بنجاح'),
            ])
            ->emptyStateHeading('🏜️ لا يوجد فائزون')
            ->emptyStateDescription('لم يتم اختيار أي فائز بعد. ابدأ باختيار الفائزين من قائمة المتصلين.')
            ->emptyStateIcon('heroicon-o-trophy');
    }
}
