<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CallerResource\Pages;
use App\Models\Caller;
use Filament\Forms;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CallerResource extends Resource
{
    protected static ?string $model = Caller::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-phone';

    protected static ?string $recordTitleAttribute = 'name';

    protected static \UnitEnum|string|null $navigationGroup = 'إدارة المتصلين';

    protected static ?string $modelLabel = 'متصل';

    protected static ?string $pluralModelLabel = 'المتصلين';

    protected static ?string $navigationLabel = 'المتصلين';

    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make(static::getNavigationLabel())
                ->icon(static::getNavigationIcon())
                ->group(static::getNavigationGroup())
                ->activeIcon(static::getActiveNavigationIcon())
                ->sort(static::getNavigationSort())
                ->badge(static::getNavigationBadge(), static::getNavigationBadgeColor())
                ->url(static::getNavigationUrl()),

            NavigationItem::make('الفائزون')
                ->icon('heroicon-o-trophy')
                ->group(static::getNavigationGroup())
                ->url(static::getUrl('winners'))
                ->sort(static::getNavigationSort() + 1),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('بيانات المتصل')
                    ->description('المعلومات الأساسية للمتصل')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('أدخل الاسم الكامل'),
                        Forms\Components\TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->required()
                            ->maxLength(20)
                            ->placeholder('مثال: 17123456'),
                        Forms\Components\TextInput::make('cpr')
                            ->label('الرقم الشخصي (CPR)')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->placeholder('أدخل الرقم الشخصي'),
                        Forms\Components\TextInput::make('hits')
                            ->label('عدد المشاركات')
                            ->numeric()
                            ->default(0)
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('الحالة')
                    ->schema([
                        Forms\Components\Toggle::make('is_winner')
                            ->label('فائز')
                            ->helperText('تحديد ما إذا كان المتصل فائزاً'),
                        Forms\Components\Select::make('status')
                            ->label('حالة الحساب')
                            ->options([
                                'active' => 'نشط',
                                'inactive' => 'غير نشط',
                                'blocked' => 'محظور',
                            ])
                            ->default('active')
                            ->native(false),
                    ])->columns(2),

                Forms\Components\Textarea::make('notes')
                    ->label('ملاحظات')
                    ->placeholder('أضف ملاحظات إضافية هنا...')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('الهاتف')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('تم نسخ رقم الهاتف'),
                Tables\Columns\TextColumn::make('cpr')
                    ->label('الرقم الشخصي')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('hits')
                    ->label('المشاركات')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\IconColumn::make('is_winner')
                    ->label('فائز')
                    ->boolean()
                    ->trueIcon('heroicon-o-trophy')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
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
                    ->label('تاريخ التسجيل')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'active' => 'نشط',
                        'inactive' => 'غير نشط',
                        'blocked' => 'محظور',
                    ])
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('is_winner')
                    ->label('الفائزون')
                    ->placeholder('الكل')
                    ->trueLabel('الفائزون فقط')
                    ->falseLabel('غير الفائزين'),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('تاريخ التسجيل من')
                            ->placeholder('من')
                            ->native(false),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('تاريخ التسجيل إلى')
                            ->placeholder('إلى')
                            ->native(false),
                    ])
                    ->query(function ($query, array $data): void {
                        $query
                            ->when(
                                $data['created_from'],
                                fn ($query, $date) => $query->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['created_until'],
                                fn ($query, $date) => $query->whereDate('created_at', '<=', $date)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['created_from'] ?? null) {
                            $indicators[] = 'من: ' . \Carbon\Carbon::parse($data['created_from'])->format('Y/m/d');
                        }

                        if ($data['created_until'] ?? null) {
                            $indicators[] = 'إلى: ' . \Carbon\Carbon::parse($data['created_until'])->format('Y/m/d');
                        }

                        return $indicators;
                    }),

                Tables\Filters\Filter::make('hits')
                    ->form([
                        Forms\Components\TextInput::make('hits_from')
                            ->label('المشاركات من')
                            ->numeric()
                            ->placeholder('من'),
                        Forms\Components\TextInput::make('hits_to')
                            ->label('المشاركات إلى')
                            ->numeric()
                            ->placeholder('إلى'),
                    ])
                    ->query(function ($query, array $data): void {
                        $query
                            ->when(
                                $data['hits_from'],
                                fn ($query, $hits) => $query->where('hits', '>=', $hits)
                            )
                            ->when(
                                $data['hits_to'],
                                fn ($query, $hits) => $query->where('hits', '<=', $hits)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['hits_from'] ?? null) {
                            $indicators[] = 'مشاركات من: ' . $data['hits_from'];
                        }

                        if ($data['hits_to'] ?? null) {
                            $indicators[] = 'مشاركات إلى: ' . $data['hits_to'];
                        }

                        return $indicators;
                    }),

                Tables\Filters\TernaryFilter::make('high_participation')
                    ->label('المشاركة العالية')
                    ->placeholder('الكل')
                    ->trueLabel('أكثر من 5 مشاركات')
                    ->falseLabel('أقل من 5 مشاركات')
                    ->queries(
                        true: fn ($query) => $query->where('hits', '>', 5),
                        false: fn ($query) => $query->where('hits', '<=', 5),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\Action::make('toggleWinner')
                    ->label(fn (Caller $record): string => $record->is_winner ? 'إزالة الفوز' : 'تحديد كفائز')
                    ->icon('heroicon-o-trophy')
                    ->color(fn (Caller $record): string => $record->is_winner ? 'warning' : 'success')
                    ->action(function (Caller $record): void {
                        $record->is_winner = ! $record->is_winner;
                        $record->save();
                    })
                    ->requiresConfirmation()
                    ->modalHeading(fn (Caller $record): string => $record->is_winner ? 'إزالة حالة الفوز' : 'تحديد كفائز')
                    ->modalDescription(fn (Caller $record): string => $record->is_winner
                        ? "هل أنت متأكد من إزالة حالة الفوز من {$record->name}؟"
                        : "هل أنت متأكد من تحديد {$record->name} كفائز؟"),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Export Actions
                    Tables\Actions\BulkAction::make('exportCsv')
                        ->label('تصدير CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(function ($records) {
                            return static::exportToCsv($records);
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('exportExcel')
                        ->label('تصدير Excel')
                        ->icon('heroicon-o-table-cells')
                        ->color('success')
                        ->action(function ($records) {
                            return static::exportToExcel($records);
                        })
                        ->deselectRecordsAfterCompletion(),

                    // Status Management
                    Tables\Actions\BulkAction::make('changeStatus')
                        ->label('تغيير الحالة')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('الحالة الجديدة')
                                ->options([
                                    'active' => 'نشط',
                                    'inactive' => 'غير نشط',
                                    'blocked' => 'محظور',
                                ])
                                ->required()
                                ->native(false),
                        ])
                        ->action(function (array $data, $records): void {
                            $records->each(function ($record) use ($data): void {
                                $record->update(['status' => $data['status']]);
                            });
                        })
                        ->requiresConfirmation()
                        ->modalHeading('تغيير حالة المتصلين')
                        ->modalDescription('سيتم تغيير حالة جميع المتصلين المحددين')
                        ->deselectRecordsAfterCompletion(),

                    // Delete Action
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد'),

                    // Winner Selection
                    Tables\Actions\BulkAction::make('selectMultipleRandomWinners')
                        ->label('اختيار فائزين عشوائيين')
                        ->icon('heroicon-o-trophy')
                        ->color('success')
                        ->form([
                            Forms\Components\TextInput::make('count')
                                ->label('عدد الفائزين')
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(10)
                                ->default(3),
                        ])
                        ->action(function (array $data): void {
                            $count = (int) $data['count'];

                            // Get eligible callers
                            $eligibleCallers = Caller::getEligibleCallers();

                            if ($eligibleCallers->count() < $count) {
                                \Filament\Notifications\Notification::make()
                                    ->warning()
                                    ->title('عدد غير كافٍ')
                                    ->body('عدد المتصلين المؤهلين غير كافٍ. يوجد فقط ' . $eligibleCallers->count() . ' متصل.')
                                    ->send();
                                return;
                            }

                            $selectedWinners = [];
                            $selectedCpRs = [];

                            // Select unique winners based on CPR
                            for ($i = 0; $i < $count; $i++) {
                                if ($eligibleCallers->isEmpty()) {
                                    break;
                                }

                                // Filter out callers whose CPR has already been selected
                                $availableCallers = $eligibleCallers->filter(function ($caller) use ($selectedCpRs) {
                                    return ! in_array($caller->cpr, $selectedCpRs);
                                });

                                if ($availableCallers->isEmpty()) {
                                    break;
                                }

                                $winner = $availableCallers->random();
                                $winner->is_winner = true;
                                $winner->save();

                                $selectedWinners[] = $winner;
                                $selectedCpRs[] = $winner->cpr;
                            }

                            $winnerNames = implode('، ', array_map(function ($winner) {
                                return $winner->name.' ('.$winner->cpr.')';
                            }, $selectedWinners));

                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('تم اختيار الفائزين')
                                ->body('تم اختيار ' . count($selectedWinners) . ' فائز: ' . $winnerNames)
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('اختيار فائزين عشوائيين')
                        ->modalDescription('سيتم اختيار فائزين عشوائياً من المتصلين المؤهلين'),

                    // Mark as Winners
                    Tables\Actions\BulkAction::make('markAsWinners')
                        ->label('تحديد كفائزين')
                        ->icon('heroicon-o-trophy')
                        ->color('success')
                        ->action(function ($records): void {
                            $records->each(function ($record): void {
                                $record->update(['is_winner' => true]);
                            });

                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('تم التحديث')
                                ->body('تم تحديد ' . $records->count() . ' متصل كفائزين')
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),

                    // Remove Winner Status
                    Tables\Actions\BulkAction::make('removeWinnerStatus')
                        ->label('إزالة حالة الفوز')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(function ($records): void {
                            $records->each(function ($record): void {
                                $record->update(['is_winner' => false]);
                            });

                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('تم التحديث')
                                ->body('تم إزالة حالة الفوز من ' . $records->count() . ' متصل')
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('exportAll')
                    ->label('تصدير الكل (CSV)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function () {
                        $allCallers = Caller::all();
                        return static::exportToCsv($allCallers);
                    }),

                Tables\Actions\Action::make('exportAllExcel')
                    ->label('تصدير الكل (Excel)')
                    ->icon('heroicon-o-table-cells')
                    ->color('success')
                    ->action(function () {
                        $allCallers = Caller::all();
                        return static::exportToExcel($allCallers);
                    }),

                Tables\Actions\Action::make('selectRandomWinner')
                    ->label('اختيار فائز عشوائي')
                    ->icon('heroicon-o-trophy')
                    ->color('success')
                    ->action(function (): void {
                        // Use the model method for selecting random winner by CPR
                        $winner = Caller::selectRandomWinnerByCpr();

                        if (!$winner) {
                            \Filament\Notifications\Notification::make()
                                ->warning()
                                ->title('لا يوجد متصلين مؤهلين')
                                ->body('لا يوجد متصلين مؤهلين للفوز.')
                                ->send();
                            return;
                        }

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('تم اختيار الفائز')
                            ->body('تم اختيار الفائز: ' . $winner->name . ' (CPR: ' . $winner->cpr . ')')
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('اختيار فائز عشوائي')
                    ->modalDescription('سيتم اختيار فائز واحد عشوائياً من المتصلين المؤهلين')
                    ->modalSubmitActionLabel('اختيار'),
            ])
            ->emptyStateHeading('لا يوجد متصلين')
            ->emptyStateDescription('لم يتم تسجيل أي متصل بعد.')
            ->emptyStateIcon('heroicon-o-phone');
    }

    public static function getRelations(): array
    {
        return [
            // Define relations if needed
        ];
    }

    /**
     * Export callers to CSV format
     */
    protected static function exportToCsv($records): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $fileName = 'callers_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return response()->streamDownload(function () use ($records): void {
            $handle = fopen('php://output', 'w');

            // Add UTF-8 BOM for proper Arabic display in Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Headers
            fputcsv($handle, [
                'الاسم',
                'رقم الهاتف',
                'الرقم الشخصي',
                'المشاركات',
                'فائز',
                'الحالة',
                'تاريخ التسجيل',
                'آخر تحديث',
            ]);

            // Data rows
            foreach ($records as $record) {
                fputcsv($handle, [
                    $record->name,
                    $record->phone,
                    $record->cpr,
                    $record->hits,
                    $record->is_winner ? 'نعم' : 'لا',
                    match($record->status) {
                        'active' => 'نشط',
                        'inactive' => 'غير نشط',
                        'blocked' => 'محظور',
                        default => $record->status,
                    },
                    $record->created_at->format('Y-m-d H:i:s'),
                    $record->updated_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * Export callers to Excel format (HTML table that Excel can open)
     */
    protected static function exportToExcel($records): \Symfony\Component\HttpFoundation\Response
    {
        $fileName = 'callers_' . now()->format('Y-m-d_H-i-s') . '.xls';

        $html = '<!DOCTYPE html>
<html dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <style>
        table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; }
        th { background-color: #f3f4f6; font-weight: bold; border: 1px solid #000; padding: 8px; text-align: right; }
        td { border: 1px solid #000; padding: 8px; text-align: right; }
        .winner { background-color: #d4edda; }
        .active { background-color: #d1ecf1; }
        .blocked { background-color: #f8d7da; }
    </style>
</head>
<body>
    <h2>سجل المتصلين - برنامج السارية</h2>
    <p>تاريخ التصدير: ' . now()->format('Y-m-d H:i:s') . '</p>
    <table>
        <thead>
            <tr>
                <th>الاسم</th>
                <th>رقم الهاتف</th>
                <th>الرقم الشخصي</th>
                <th>المشاركات</th>
                <th>فائز</th>
                <th>الحالة</th>
                <th>تاريخ التسجيل</th>
                <th>آخر تحديث</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($records as $record) {
            $rowClass = $record->is_winner ? 'winner' : ($record->status === 'blocked' ? 'blocked' : ($record->status === 'active' ? 'active' : ''));
            $html .= '<tr class="' . $rowClass . '">
                <td>' . htmlspecialchars($record->name) . '</td>
                <td>' . htmlspecialchars($record->phone) . '</td>
                <td>' . htmlspecialchars($record->cpr) . '</td>
                <td>' . $record->hits . '</td>
                <td>' . ($record->is_winner ? '🏆 نعم' : 'لا') . '</td>
                <td>' . match($record->status) {
                    'active' => 'نشط',
                    'inactive' => 'غير نشط',
                    'blocked' => 'محظور',
                    default => $record->status,
                } . '</td>
                <td>' . $record->created_at->format('Y-m-d H:i:s') . '</td>
                <td>' . $record->updated_at->format('Y-m-d H:i:s') . '</td>
            </tr>';
        }

        $html .= '</tbody>
    </table>
</body>
</html>';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCallers::route('/'),
            'create' => Pages\CreateCaller::route('/create'),
            'edit' => Pages\EditCaller::route('/{record}/edit'),
            'winners' => Pages\ListWinners::route('/winners'),
        ];
    }
}
