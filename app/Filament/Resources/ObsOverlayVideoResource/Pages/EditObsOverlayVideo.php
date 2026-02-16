<?php

namespace App\Filament\Resources\ObsOverlayVideoResource\Pages;

use App\Filament\Resources\ObsOverlayVideoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditObsOverlayVideo extends EditRecord
{
    protected static string $resource = ObsOverlayVideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
