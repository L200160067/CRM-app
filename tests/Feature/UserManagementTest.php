<?php

use App\Enums\Role;
use App\Livewire\UserManagement\Index;
use App\Models\User;
use Livewire\Livewire;

test('super admin can view user management page', function () {
    $superAdmin = User::factory()->create(['role' => Role::SuperAdmin]);

    $this->actingAs($superAdmin)
        ->get(route('users.index'))
        ->assertOk()
        ->assertSeeLivewire(Index::class);
});

test('non super admin cannot view user management page', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $marketing = User::factory()->create(['role' => Role::Marketing]);

    $this->actingAs($admin)->get(route('users.index'))->assertForbidden();
    $this->actingAs($marketing)->get(route('users.index'))->assertForbidden();
});

test('super admin can create a user', function () {
    $superAdmin = User::factory()->create(['role' => Role::SuperAdmin]);

    Livewire::actingAs($superAdmin)
        ->test(Index::class)
        ->set('name', 'New Staff')
        ->set('email', 'staff@example.com')
        ->set('role', Role::Admin->value)
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('user-created');

    $this->assertDatabaseHas('users', [
        'email' => 'staff@example.com',
        'role' => Role::Admin->value,
    ]);
});

test('super admin can update a user', function () {
    $superAdmin = User::factory()->create(['role' => Role::SuperAdmin]);
    $user = User::factory()->create(['role' => Role::Marketing]);

    Livewire::actingAs($superAdmin)
        ->test(Index::class)
        ->call('edit', $user)
        ->set('name', 'Updated Name')
        ->set('role', Role::Admin->value)
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('user-updated');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name',
        'role' => Role::Admin->value,
    ]);
});

test('super admin cannot change their own role', function () {
    $superAdmin = User::factory()->create(['role' => Role::SuperAdmin]);

    Livewire::actingAs($superAdmin)
        ->test(Index::class)
        ->call('edit', $superAdmin)
        ->set('role', Role::Admin->value)
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('Anda tidak dapat mengubah role akun Anda sendiri.');
        
    $this->assertTrue($superAdmin->fresh()->isSuperAdmin());
});

test('super admin can delete a user', function () {
    $superAdmin = User::factory()->create(['role' => Role::SuperAdmin]);
    $user = User::factory()->create();

    Livewire::actingAs($superAdmin)
        ->test(Index::class)
        ->call('confirmDelete', $user)
        ->call('delete')
        ->assertDispatched('user-deleted');

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);
});

test('super admin cannot delete themselves', function () {
    $superAdmin = User::factory()->create(['role' => Role::SuperAdmin]);

    Livewire::actingAs($superAdmin)
        ->test(Index::class)
        ->call('confirmDelete', $superAdmin)
        ->call('delete')
        ->assertHasNoErrors()
        ->assertSee('Anda tidak dapat menghapus akun Anda sendiri.');

    $this->assertDatabaseHas('users', [
        'id' => $superAdmin->id,
    ]);
});

test('non super admin cannot perform user management actions', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $targetUser = User::factory()->create();

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->set('name', 'Hack')
        ->set('email', 'hack@mail.com')
        ->set('role', Role::Marketing->value)
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('save')
        ->assertForbidden();

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->call('confirmDelete', $targetUser)
        ->call('delete')
        ->assertForbidden();
});
