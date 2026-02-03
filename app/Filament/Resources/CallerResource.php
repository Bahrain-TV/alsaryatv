<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CallerResource\Pages;
use App\Models\Caller;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CallerResource extends Resource
{
    protected static ?string $model = Caller::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-phone';

    protected static ?string $recordTitleAttribute = 'name';

    protected static \UnitEnum|string|null $navigationGroup = 'Caller Management';

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

            // NavigationItem::make('Winners')
            //     ->icon('heroicon-o-trophy')
            //     ->group(static::getNavigationGroup())
            //     ->url(static::getUrl('winners'))
            //     ->sort(static::getNavigationSort() + 1),

            NavigationItem::make('Families')
                ->icon('heroicon-o-user-group')
                ->group(static::getNavigationGroup())
                ->url(static::getUrl('families'))
                ->sort(static::getNavigationSort() + 2),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Caller Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('cpr')
                            ->maxLength(20),
                        Forms\Components\TextInput::make('ip_address')
                            ->maxLength(45),
                    ])->columns(2),

                Forms\Components\Section::make('Status Information')
                    ->schema([
                        Forms\Components\Toggle::make('is_family')
                            ->label('Family Member'),
                        Forms\Components\Toggle::make('is_winner')
                            ->label('Is Winner'),
                        Forms\Components\TextInput::make('hits')
                            ->numeric()
                            ->default(0),
                        Forms\Components\DateTimePicker::make('last_hit'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'blocked' => 'Blocked',
                            ])
                            ->default('active'),
                    ])->columns(2),

                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cpr')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_family')
                    ->boolean()
                    ->label('Family'),
                Tables\Columns\IconColumn::make('is_winner')
                    ->boolean(),
                Tables\Columns\TextColumn::make('hits')
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_hit')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'warning',
                        'blocked' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'blocked' => 'Blocked',
                    ]),
                Tables\Filters\TernaryFilter::make('is_winner'),
                Tables\Filters\TernaryFilter::make('is_family'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('toggleWinner')
                    ->label(fn (Caller $record): string => $record->is_winner ? 'Remove Winner Status' : 'Mark as Winner')
                    ->icon('heroicon-o-trophy')
                    ->color(fn (Caller $record): string => $record->is_winner ? 'warning' : 'success')
                    ->action(function (Caller $record): void {
                        $record->is_winner = ! $record->is_winner;
                        $record->save();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'families' => Pages\ListFamilies::route('/families'),
        ];
    }
}
