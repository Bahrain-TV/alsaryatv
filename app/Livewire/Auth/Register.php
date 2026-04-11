<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Register extends Component
{
    use AuthorizesRequests;

    public function mount(): void
    {
        $this->authorize('create', User::class);
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
