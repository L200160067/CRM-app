<?php

namespace App\Livewire\UserManagement;

use App\Models\User;
use App\Enums\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $role = '';
    public $showCreateModal = false;

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'string', 'in:' . implode(',', array_column(Role::cases(), 'value'))],
        ];
    }
    
    public function getRolesProperty()
    {
        return Role::cases();
    }

    public function save()
    {
        $this->authorize('create', User::class); // will handle via simple check below for now

        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        if (! $user || ! $user->isSuperAdmin()) {
            abort(403);
        }

        $validated = $this->validate();

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => Role::from($validated['role']),
        ]);

        $this->reset(['name', 'email', 'password', 'password_confirmation', 'role', 'showCreateModal']);
        $this->dispatch('user-created');
        
        session()->flash('message', 'User successfully created.');
    }

    public function render()
    {
        return view('livewire.user-management.index', [
            'users' => User::latest()->paginate(10),
            'roles' => $this->roles
        ]);
    }
}
