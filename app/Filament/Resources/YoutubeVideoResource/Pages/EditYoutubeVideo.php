<?php

namespace App\Filament\Resources\YoutubeVideoResource\Pages;

use App\Filament\Resources\YoutubeVideoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditYoutubeVideo extends EditRecord
{
    protected static string $resource = YoutubeVideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
