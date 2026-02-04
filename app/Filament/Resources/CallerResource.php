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

            NavigationItem::make('Winners')
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
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),
                    ])->columns(2),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Toggle::make('is_winner')
                            ->label('Is Winner'),
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
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_winner')
                    ->boolean()
                    ->label('Winner'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'warning',
                        'blocked' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'blocked' => 'Blocked',
                    ]),
                Tables\Filters\TernaryFilter::make('is_winner'),
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
                    Tables\Actions\Action::make('selectMultipleRandomWinners')
                        ->label('Select Multiple Random Winners')
                        ->icon('heroicon-o-trophy')
                        ->color('success')
                        ->form([
                            Forms\Components\TextInput::make('count')
                                ->label('Number of Winners')
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
                                $this->notify('warning', 'Not enough eligible callers. Only ' . $eligibleCallers->count() . ' available.');
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
                            
                            $winnerNames = implode(', ', array_map(function ($winner) {
                                return $winner->name . ' (' . $winner->cpr . ')';
                            }, $selectedWinners));
                            
                            $this->notify('success', 'Selected ' . count($selectedWinners) . ' random winners: ' . $winnerNames);
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('selectRandomWinner')
                    ->label('Select Random Winner')
                    ->icon('heroicon-o-trophy')
                    ->color('success')
                    ->action(function (): void {
                        // Use the model method for selecting random winner by CPR
                        $winner = Caller::selectRandomWinnerByCpr();
                        
                        if (!$winner) {
                            $this->notify('warning', 'No eligible callers found for winner selection.');
                            return;
                        }
                        
                        $this->notify('success', 'Random winner selected: ' . $winner->name . ' (CPR: ' . $winner->cpr . ')');
                    })
                    ->requiresConfirmation(),
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
        ];
    }
}
