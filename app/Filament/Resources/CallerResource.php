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
                    ]),
                Tables\Filters\TernaryFilter::make('is_winner')
                    ->label('الفائزون')
                    ->placeholder('الكل')
                    ->trueLabel('الفائزون فقط')
                    ->falseLabel('غير الفائزين'),
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
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                    Tables\Actions\Action::make('selectMultipleRandomWinners')
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
                                $this->notify('warning', 'عدد المتصلين المؤهلين غير كافٍ. يوجد فقط ' . $eligibleCallers->count() . ' متصل.');
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
                                    return !in_array($caller->cpr, $selectedCpRs);
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
                                return $winner->name . ' (' . $winner->cpr . ')';
                            }, $selectedWinners));

                            $this->notify('success', 'تم اختيار ' . count($selectedWinners) . ' فائز: ' . $winnerNames);
                        })
                        ->requiresConfirmation()
                        ->modalHeading('اختيار فائزين عشوائيين')
                        ->modalDescription('سيتم اختيار فائزين عشوائياً من المتصلين المؤهلين'),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('selectRandomWinner')
                    ->label('اختيار فائز عشوائي')
                    ->icon('heroicon-o-trophy')
                    ->color('success')
                    ->action(function (): void {
                        // Use the model method for selecting random winner by CPR
                        $winner = Caller::selectRandomWinnerByCpr();

                        if (!$winner) {
                            $this->notify('warning', 'لا يوجد متصلين مؤهلين للفوز.');
                            return;
                        }

                        $this->notify('success', 'تم اختيار الفائز: ' . $winner->name . ' (CPR: ' . $winner->cpr . ')');
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
