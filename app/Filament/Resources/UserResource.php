<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $recordTitleAttribute = 'name';

    protected static \UnitEnum|string|null $navigationGroup = 'إدارة المستخدمين';

    protected static ?string $modelLabel = 'مستخدم';

    protected static ?string $pluralModelLabel = 'المستخدمين';

    protected static ?string $navigationLabel = 'المستخدمين';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('بيانات المستخدم')
                    ->description('المعلومات الأساسية للمستخدم')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('أدخل الاسم الكامل'),
                        Forms\Components\TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('example@domain.com'),
                        Forms\Components\TextInput::make('password')
                            ->label('كلمة المرور')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->placeholder('أدخل كلمة المرور')
                            ->helperText('اتركها فارغة للحفاظ على كلمة المرور الحالية'),
                    ])->columns(2),
                Forms\Components\Section::make('الصلاحيات')
                    ->schema([
                        Forms\Components\Toggle::make('is_admin')
                            ->label('مدير النظام')
                            ->helperText('منح صلاحيات المدير الكاملة'),
                        Forms\Components\Select::make('role')
                            ->label('الدور')
                            ->options([
                                'user' => 'مستخدم',
                                'editor' => 'محرر',
                                'manager' => 'مدير',
                            ])
                            ->default('user')
                            ->native(false),
                    ])->columns(2),
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
                Tables\Columns\TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('تم نسخ البريد الإلكتروني'),
                Tables\Columns\IconColumn::make('is_admin')
                    ->label('مدير')
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-user')
                    ->trueColor('success')
                    ->falseColor('gray'),
                Tables\Columns\TextColumn::make('role')
                    ->label('الدور')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'user' => 'مستخدم',
                        'editor' => 'محرر',
                        'manager' => 'مدير',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'manager' => 'success',
                        'editor' => 'info',
                        'user' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_admin')
                    ->label('المديرون')
                    ->placeholder('الكل')
                    ->trueLabel('المديرون فقط')
                    ->falseLabel('غير المديرين'),
                Tables\Filters\SelectFilter::make('role')
                    ->label('الدور')
                    ->options([
                        'user' => 'مستخدم',
                        'editor' => 'محرر',
                        'manager' => 'مدير',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                ]),
            ])
            ->emptyStateHeading('لا يوجد مستخدمين')
            ->emptyStateDescription('لم يتم إنشاء أي مستخدم بعد.')
            ->emptyStateIcon('heroicon-o-users');
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
