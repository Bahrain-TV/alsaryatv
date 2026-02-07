<?php

namespace App\Livewire\Auth;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Register extends Component
{
    use AuthorizesRequests;

    public function mount(): void
    {
        $this->authorize('create', \App\Models\User::class);
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
