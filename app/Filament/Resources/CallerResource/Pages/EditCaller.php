<?php

namespace App\Filament\Resources\CallerResource\Pages;

use App\Filament\Resources\CallerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditCaller extends EditRecord
{
    protected static string $resource = CallerResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];

        $user = Auth::user();

        // Super admins can force delete
        if ($user?->isSuperAdmin()) {
            $actions[] = Actions\ForceDeleteAction::make();
        }

        // All admins can soft delete (regular delete)
        if ($user?->isAdmin()) {
            $actions[] = Actions\DeleteAction::make();
        }

        // Super admins can restore
        if ($user?->isSuperAdmin()) {
            $actions[] = Actions\RestoreAction::make();
        }

        return $actions;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
