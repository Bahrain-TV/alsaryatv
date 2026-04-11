<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CallerResource\Pages;
use App\Models\Caller;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Navigation\NavigationItem;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
                Section::make('بيانات المتصل')
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

                Section::make('الحالة')
                    ->schema([
                        Forms\Components\Toggle::make('is_winner')
                            ->label('فائز')
                            ->helperText('تحديد ما إذا كان المتصل فائزاً (يتم يدوياً)'),
                        Forms\Components\Toggle::make('is_selected')
                            ->label('مُختار')
                            ->helperText('تم اختياره عشوائياً (لن يظهر في السحب مجدداً)')
                            ->disabled(),
                        Select::make('status')
                            ->label('حالة الحساب')
                            ->options([
                                'active' => 'نشط',
                                'inactive' => 'غير نشط',
                                'selected' => 'مُختار',
                                'blocked' => 'محظور',
                            ])
                            ->default('active')
                            ->native(false),
                    ])->columns(3),

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
                    ->copyMessage('تم نسخ رقم الهاتف')
                    ->toggleable()
                    ->visibleFrom('md'),
                Tables\Columns\TextColumn::make('cpr')
                    ->label('الرقم الشخصي')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->visibleFrom('lg'),
                Tables\Columns\TextColumn::make('hits')
                    ->label('المشاركات')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\IconColumn::make('is_selected')
                    ->label('مُختار')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-minus-circle')
                    ->trueColor('warning')
                    ->falseColor('gray'),
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
                        'selected' => 'مُختار',
                        'blocked' => 'محظور',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'warning',
                        'selected' => 'info',
                        'blocked' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable()
                    ->visibleFrom('md'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable()
                    ->visibleFrom('lg'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->striped()
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'active' => 'نشط',
                        'inactive' => 'غير نشط',
                        'selected' => 'مُختار',
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
                            $indicators[] = 'من: '.Carbon::parse($data['created_from'])->format('Y/m/d');
                        }

                        if ($data['created_until'] ?? null) {
                            $indicators[] = 'إلى: '.Carbon::parse($data['created_until'])->format('Y/m/d');
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
                            $indicators[] = 'مشاركات من: '.$data['hits_from'];
                        }

                        if ($data['hits_to'] ?? null) {
                            $indicators[] = 'مشاركات إلى: '.$data['hits_to'];
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
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    // Export Actions
                    BulkAction::make('exportCsv')
                        ->label('تصدير CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(function ($records) {
                            return static::exportToCsv($records);
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('exportExcel')
                        ->label('تصدير Excel')
                        ->icon('heroicon-o-table-cells')
                        ->color('success')
                        ->action(function ($records) {
                            return static::exportToExcel($records);
                        })
                        ->deselectRecordsAfterCompletion(),

                    // Status Management
                    BulkAction::make('changeStatus')
                        ->label('تغيير الحالة')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->form([
                            Select::make('status')
                                ->label('الحالة الجديدة')
                                ->options([
                                    'active' => 'نشط',
                                    'inactive' => 'غير نشط',
                                    'selected' => 'مُختار',
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
                    DeleteBulkAction::make()
                        ->label('حذف المحدد'),

                    // Mark Selected as Winners (manual confirmation)
                    BulkAction::make('markAsWinners')
                        ->label('تأكيد كفائزين')
                        ->icon('heroicon-o-trophy')
                        ->color('success')
                        ->action(function ($records): void {
                            $records->each(function ($record): void {
                                $record->update([
                                    'is_winner' => true,
                                    'is_selected' => true, // also ensure they're marked selected
                                ]);
                            });

                            Notification::make()
                                ->success()
                                ->title('تم التحديث')
                                ->body('تم تأكيد '.$records->count().' متصل كفائزين')
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('تأكيد الفائزين')
                        ->modalDescription('سيتم تأكيد المتصلين المحددين كفائزين نهائيين.')
                        ->deselectRecordsAfterCompletion(),

                    // Remove Winner Status (keeps selected)
                    BulkAction::make('removeWinnerStatus')
                        ->label('إزالة حالة الفوز')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(function ($records): void {
                            $records->each(function ($record): void {
                                $record->update(['is_winner' => false]);
                            });

                            Notification::make()
                                ->success()
                                ->title('تم التحديث')
                                ->body('تم إزالة حالة الفوز من '.$records->count().' متصل (لا يزالون مُختارين)')
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),

                    // Reset Selection (allow them back into draws)
                    BulkAction::make('resetSelection')
                        ->label('إعادة إلى السحب')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('gray')
                        ->action(function ($records): void {
                            $records->each(function ($record): void {
                                $record->update([
                                    'is_selected' => false,
                                    'is_winner' => false,
                                    'status' => 'active',
                                ]);
                            });

                            Notification::make()
                                ->success()
                                ->title('تم التحديث')
                                ->body('تم إعادة '.$records->count().' متصل إلى قائمة السحب')
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('إعادة إلى السحب')
                        ->modalDescription('سيتم إزالة حالة الاختيار والفوز وإعادتهم كمتصلين نشطين مؤهلين للسحب.')
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->headerActions([
                Action::make('exportAll')
                    ->label('تصدير الكل (CSV)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function () {
                        $allCallers = Caller::all();

                        return static::exportToCsv($allCallers);
                    }),

                Action::make('exportAllExcel')
                    ->label('تصدير الكل (Excel)')
                    ->icon('heroicon-o-table-cells')
                    ->color('success')
                    ->action(function () {
                        $allCallers = Caller::all();

                        return static::exportToExcel($allCallers);
                    }),

                Action::make('selectRandomWinner')
                    ->label('اختيار مُختار عشوائي')
                    ->icon('heroicon-o-sparkles')
                    ->color('info')
                    ->action(function (): void {
                        // Use the model method for selecting random caller by CPR
                        $selected = Caller::selectRandomWinnerByCpr();

                        if (! $selected) {
                            $this->notify('warning', 'لا يوجد متصلين مؤهلين للاختيار.');

                            return;
                        }

                        $this->notify('success', 'تم اختيار: '.$selected->name.' (CPR: '.$selected->cpr.') — يمكنك تأكيده كفائز يدوياً.');
                    })
                    ->requiresConfirmation()
                    ->modalHeading('اختيار مُختار عشوائي')
                    ->modalDescription('سيتم اختيار متصل واحد عشوائياً وتغيير حالته إلى "مُختار". لن يتم تحديده كفائز — يجب تأكيد ذلك يدوياً.')
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
    protected static function exportToCsv($records): StreamedResponse
    {
        $fileName = 'callers_'.now()->format('Y-m-d_H-i-s').'.csv';

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
                'مُختار',
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
                    $record->is_selected ? 'نعم' : 'لا',
                    $record->is_winner ? 'نعم' : 'لا',
                    match ($record->status) {
                        'active' => 'نشط',
                        'inactive' => 'غير نشط',
                        'selected' => 'مُختار',
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
    protected static function exportToExcel($records): Response
    {
        $fileName = 'callers_'.now()->format('Y-m-d_H-i-s').'.xls';

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
    <p>تاريخ التصدير: '.now()->format('Y-m-d H:i:s').'</p>
    <table>
        <thead>
            <tr>
                <th>الاسم</th>
                <th>رقم الهاتف</th>
                <th>الرقم الشخصي</th>
                <th>المشاركات</th>
                <th>مُختار</th>
                <th>فائز</th>
                <th>الحالة</th>
                <th>تاريخ التسجيل</th>
                <th>آخر تحديث</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($records as $record) {
            $rowClass = $record->is_winner ? 'winner' : ($record->is_selected ? 'selected' : ($record->status === 'blocked' ? 'blocked' : ($record->status === 'active' ? 'active' : '')));
            $html .= '<tr class="'.$rowClass.'">
                <td>'.htmlspecialchars($record->name).'</td>
                <td>'.htmlspecialchars($record->phone).'</td>
                <td>'.htmlspecialchars($record->cpr).'</td>
                <td>'.$record->hits.'</td>
                <td>'.($record->is_selected ? '✅ نعم' : 'لا').'</td>
                <td>'.($record->is_winner ? '🏆 نعم' : 'لا').'</td>
                <td>'.match ($record->status) {
                'active' => 'نشط',
                'inactive' => 'غير نشط',
                'selected' => 'مُختار',
                'blocked' => 'محظور',
                default => $record->status,
            }.'</td>
                <td>'.$record->created_at->format('Y-m-d H:i:s').'</td>
                <td>'.$record->updated_at->format('Y-m-d H:i:s').'</td>
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
