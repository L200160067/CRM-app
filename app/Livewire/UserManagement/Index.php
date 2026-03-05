<?php

namespace App\Livewire\UserManagement;

use App\Enums\Role;
use App\Models\User;
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

    public ?int $editingUserId = null;

    public ?int $deletingUserId = null;

    public function rules()
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$this->editingUserId],
            'password' => ['nullable', 'string', 'confirmed', Password::defaults()],
            'role' => ['required', 'string', 'in:'.implode(',', array_column(Role::cases(), 'value'))],
        ];

        if (! $this->editingUserId) {
            $rules['password'] = ['required', 'string', 'confirmed', Password::defaults()];
        }

        return $rules;
    }

    public function getRolesProperty()
    {
        return Role::cases();
    }

    public function edit(User $user)
    {
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role->value;
        $this->password = '';
        $this->password_confirmation = '';

        $this->showCreateModal = true;
    }

    public function save()
    {
        $this->authorize('create', User::class);

        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        if (! $user || ! $user->isSuperAdmin()) {
            abort(403);
        }

        $validated = $this->validate();

        if ($this->editingUserId) {
            $targetUser = User::findOrFail($this->editingUserId);

            // SuperAdmin protection from demoting themselves
            if ($targetUser->id === $user->id && $targetUser->role->value !== $validated['role']) {
                session()->flash('error', 'Anda tidak dapat mengubah role akun Anda sendiri.');

                return;
            }

            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => Role::from($validated['role']),
            ];

            if (! empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $targetUser->update($updateData);

            $this->dispatch('user-updated');
            session()->flash('message', 'Pengguna berhasil diperbarui.');
        } else {
            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => Role::from($validated['role']),
            ]);

            $this->dispatch('user-created');
            session()->flash('message', 'Pengguna berhasil ditambahkan.');
        }

        $this->reset(['name', 'email', 'password', 'password_confirmation', 'role', 'editingUserId', 'showCreateModal']);
    }

    public function confirmDelete(User $user)
    {
        $this->deletingUserId = $user->id;
    }

    public function delete()
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        if (! $user || ! $user->isSuperAdmin()) {
            abort(403);
        }

        if ($this->deletingUserId === $user->id) {
            session()->flash('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
            $this->deletingUserId = null;

            return;
        }

        User::findOrFail($this->deletingUserId)->delete();

        $this->deletingUserId = null;
        $this->dispatch('user-deleted');
        session()->flash('message', 'Pengguna berhasil dihapus.');
    }

    public function render()
    {
        return view('livewire.user-management.index', [
            'users' => User::latest()->paginate(10),
            'roles' => $this->roles,
        ]);
    }
}
