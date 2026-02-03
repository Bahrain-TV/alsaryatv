<?php

namespace App\Filament\Widgets;

use App\Models\Caller;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LiveCallersUpdateWidget extends TableWidget
{
    // Set the widget to take up more space
    protected int|string|array $columnSpan = 'full';

    // Poll for updates every 10 seconds (10000ms)
    protected static ?string $pollingInterval = '10s';

    // Widget header
    protected static ?string $heading = 'Live Caller Updates';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Get the most recent callers, ordered by created_at
                Caller::query()->latest('created_at')->limit(20)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('category.name')
                //     ->label('Category')
                // ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            // Add a badge with the count of new callers today
            ->headerActions([
                Tables\Actions\Action::make('new_today')
                    ->label(fn () => 'New Today: '.Caller::whereDate('created_at', today())->count())
                    ->button()
                    ->color('success'),
            ]);
    }
}
