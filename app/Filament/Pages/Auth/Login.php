<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;

class Login extends BaseLogin
{
    public function getHeading(): string
    {
        return 'Welcome to Al Sarya TV';
    }

    public function getSubheading(): string
    {
        return 'Please log in to access the admin panel';
    }

    // Revert to using the default view
    // public function getView(): string
    // {
    //     return parent::getView();
    // }

    // Remove the problematic getLogo method
    // public function getLogo(): string
    // {
    //     return '';
    // }
}
