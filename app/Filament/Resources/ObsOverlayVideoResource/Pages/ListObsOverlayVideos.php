<?php

namespace App\Filament\Resources\ObsOverlayVideoResource\Pages;

use App\Filament\Resources\ObsOverlayVideoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListObsOverlayVideos extends ListRecords
{
    protected static string $resource = ObsOverlayVideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
