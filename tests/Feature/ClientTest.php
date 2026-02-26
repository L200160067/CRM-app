<?php

/** @var \Tests\TestCase $this */

use App\Livewire\Client\Form;
use App\Livewire\Client\Index;
use App\Models\Client;
use App\Models\User;

// --- Authorization ---

test('guests cannot access clients page', function () {
    $this->withoutVite()->get(route('clients.index'))->assertRedirect(route('login'));
});

test('staff users can view the clients page', function () {
    $user = User::factory()->staff()->create();
    $this->withoutVite()->actingAs($user)->get(route('clients.index'))->assertOk();
});

// --- Client Index (Livewire) ---

test('clients index shows list of clients', function () {
    $user = User::factory()->staff()->create();
    $clients = Client::factory()->count(3)->create();

    \Livewire\Livewire::actingAs($user)
        ->test(Index::class)
        ->assertSeeText($clients->first()->name);
});

test('clients index search filters results', function () {
    $user = User::factory()->staff()->create();
    $target = Client::factory()->create(['name' => 'Budi Santoso']);
    Client::factory()->create(['name' => 'Andi Wijaya']);

    \Livewire\Livewire::actingAs($user)
        ->test(Index::class)
        ->set('search', 'Budi')
        ->assertSeeText('Budi Santoso')
        ->assertDontSeeText('Andi Wijaya');
});

// --- Create Client ---

test('admin can create a client', function () {
    $admin = User::factory()->admin()->create();

    \Livewire\Livewire::actingAs($admin)
        ->test(Form::class)
        ->call('loadClient')
        ->set('form.name', 'PT Maju Jaya')
        ->set('form.email', 'contact@majujaya.id')
        ->set('form.phone', '08123456789')
        ->set('form.city', 'Jakarta')
        ->call('save')
        ->assertDispatched('client-saved');

    $this->assertDatabaseHas('clients', ['name' => 'PT Maju Jaya', 'email' => 'contact@majujaya.id']);
});

test('staff cannot create a client', function () {
    $staff = User::factory()->staff()->create();

    \Livewire\Livewire::actingAs($staff)
        ->test(Form::class)
        ->call('loadClient')
        ->assertForbidden();
});

test('client name is required', function () {
    $admin = User::factory()->admin()->create();

    \Livewire\Livewire::actingAs($admin)
        ->test(Form::class)
        ->call('loadClient')
        ->set('form.name', '')
        ->call('save')
        ->assertHasErrors(['form.name' => 'required']);
});

// --- Update Client ---

test('admin can update a client', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create(['name' => 'Lama']);

    \Livewire\Livewire::actingAs($admin)
        ->test(Form::class)
        ->call('loadClient', $client->id)
        ->set('form.name', 'Baru')
        ->call('save')
        ->assertDispatched('client-saved');

    $this->assertDatabaseHas('clients', ['id' => $client->id, 'name' => 'Baru']);
});

test('staff cannot update a client', function () {
    $staff = User::factory()->staff()->create();
    $client = Client::factory()->create();

    \Livewire\Livewire::actingAs($staff)
        ->test(Form::class)
        ->call('loadClient', $client->id)
        ->assertForbidden();
});

// --- Delete & Restore (SoftDeletes) ---

test('admin can soft delete a client', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();

    \Livewire\Livewire::actingAs($admin)
        ->test(Index::class)
        ->call('confirmDelete', $client->id)
        ->call('delete');

    $this->assertSoftDeleted('clients', ['id' => $client->id]);
});

test('staff cannot delete a client', function () {
    $staff = User::factory()->staff()->create();
    $client = Client::factory()->create();

    \Livewire\Livewire::actingAs($staff)
        ->test(Index::class)
        ->call('confirmDelete', $client->id)
        ->call('delete')
        ->assertForbidden();
});

test('admin can restore a soft-deleted client', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();
    $client->delete();

    \Livewire\Livewire::actingAs($admin)
        ->test(Index::class)
        ->call('restore', $client->id);

    $this->assertNotSoftDeleted('clients', ['id' => $client->id]);
});
