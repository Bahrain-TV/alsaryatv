<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];

        $user = Auth::user();

        // Super admins can force delete
        if ($user?->isSuperAdmin()) {
            $actions[] = Actions\ForceDeleteAction::make();
        }

        // Admins can soft delete
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
