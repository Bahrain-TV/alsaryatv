<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ObsOverlayVideoResource\Pages;
use App\Models\ObsOverlayVideo;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ObsOverlayVideoResource extends Resource
{
    protected static ?string $model = ObsOverlayVideo::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-video-camera';

    protected static \UnitEnum|string|null $navigationGroup = 'Live Show';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'OBS Overlay Video';

    protected static ?string $pluralModelLabel = 'OBS Overlay Videos';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Video Information')
                    ->schema([
                        Forms\Components\TextInput::make('filename')
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('path')
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('file_size')
                            ->disabled()
                            ->formatStateUsing(fn ($state) => self::formatBytes($state)),
                        Forms\Components\TextInput::make('mime_type')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('recorded_at')
                            ->disabled(),
                    ])->columns(2),
                Section::make('Status & Notes')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'ready' => 'Ready',
                                'archived' => 'Archived',
                                'deleted' => 'Deleted',
                            ])
                            ->native(false),
                        Forms\Components\Textarea::make('notes')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('recorded_at')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->label('Recorded'),
                Tables\Columns\TextColumn::make('filename')
                    ->limit(50)
                    ->copyable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('file_size')
                    ->formatStateUsing(fn ($state) => self::formatBytes($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ready' => 'success',
                        'archived' => 'warning',
                        'deleted' => 'danger',
                    })
                    ->sortable(),
            ])
            ->defaultSort('recorded_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'ready' => 'Ready',
                        'archived' => 'Archived',
                        'deleted' => 'Deleted',
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
            'index' => Pages\ListObsOverlayVideos::route('/'),
            'create' => Pages\CreateObsOverlayVideo::route('/create'),
            'edit' => Pages\EditObsOverlayVideo::route('/{record}/edit'),
        ];
    }

    private static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2).' '.$units[$pow];
    }
}
