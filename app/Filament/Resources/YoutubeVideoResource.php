<?php

namespace App\Filament\Resources;

use App\Filament\Resources\YoutubeVideoResource\Pages;
use App\Models\YoutubeVideo;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class YoutubeVideoResource extends Resource
{
    protected static ?string $model = YoutubeVideo::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-video-camera';

    protected static \UnitEnum|string|null $navigationGroup = 'Live Show';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'YouTube Video';

    protected static ?string $pluralModelLabel = 'YouTube Videos';

    protected static ?string $navigationLabel = 'YouTube Videos';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Video Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('youtube_url')
                            ->required()
                            ->url()
                            ->placeholder('https://www.youtube.com/watch?v=... or https://youtu.be/...')
                            ->columnSpanFull()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $youtubeId = YoutubeVideo::extractYoutubeId($state);
                                    $set('youtube_id', $youtubeId);
                                }
                            }),
                        Forms\Components\TextInput::make('youtube_id')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generated from URL'),
                    ])->columns(1),

                Section::make('Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_enabled')
                            ->label('Enabled')
                            ->default(true),
                        Forms\Components\Toggle::make('is_live_stream')
                            ->label('Live Stream')
                            ->helperText('Enable for live streams (autoplay enabled)'),
                        Forms\Components\DateTimePicker::make('scheduled_at')
                            ->label('Schedule Start')
                            ->helperText('Leave empty to show immediately when enabled'),
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expiration Date')
                            ->helperText('Leave empty for no expiration'),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->limit(50)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BooleanColumn::make('is_enabled')
                    ->label('Enabled'),
                Tables\Columns\BooleanColumn::make('is_live_stream')
                    ->label('Live'),
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->dateTime('M j, Y H:i')
                    ->placeholder('Immediate')
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime('M j, Y H:i')
                    ->placeholder('Never')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_enabled')
                    ->label('Status')
                    ->boolean()
                    ->trueLabel('Enabled only')
                    ->falseLabel('Disabled only')
                    ->native(false),
                Tables\Filters\TernaryFilter::make('is_live_stream')
                    ->label('Type')
                    ->boolean()
                    ->trueLabel('Live streams only')
                    ->falseLabel('Videos only')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListYoutubeVideos::route('/'),
            'create' => Pages\CreateYoutubeVideo::route('/create'),
            'edit' => Pages\EditYoutubeVideo::route('/{record}/edit'),
        ];
    }
}