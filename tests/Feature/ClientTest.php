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

test('marketing users can view the clients page', function () {
    $user = User::factory()->marketing()->create();
    $this->withoutVite()->actingAs($user)->get(route('clients.index'))->assertOk();
});

// --- Client Index (Livewire) ---

test('clients index shows list of clients', function () {
    $user = User::factory()->marketing()->create();
    $clients = Client::factory()->count(3)->create();

    \Livewire\Livewire::actingAs($user)
        ->test(Index::class)
        ->assertSeeText($clients->first()->name);
});

test('clients index search filters results', function () {
    $user = User::factory()->marketing()->create();
    $target = Client::factory()->create(['name' => 'Budi Santoso']);
    Client::factory()->create(['name' => 'Andi Wijaya']);

    \Livewire\Livewire::actingAs($user)
        ->test(Index::class)
        ->set('search', 'Budi')
        ->assertSeeText('Budi Santoso')
        ->assertDontSeeText('Andi Wijaya');
});

// --- Create Client ---

test('marketing can create a client', function () {
    $marketing = User::factory()->marketing()->create();

    \Livewire\Livewire::actingAs($marketing)
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

test('admin cannot create a client', function () {
    $admin = User::factory()->admin()->create();

    \Livewire\Livewire::actingAs($admin)
        ->test(Form::class)
        ->call('loadClient')
        ->assertForbidden();
});

test('client name is required', function () {
    $marketing = User::factory()->marketing()->create();

    \Livewire\Livewire::actingAs($marketing)
        ->test(Form::class)
        ->call('loadClient')
        ->set('form.name', '')
        ->call('save')
        ->assertHasErrors(['form.name' => 'required']);
});

// --- Update Client ---

test('marketing can update a client', function () {
    $marketing = User::factory()->marketing()->create();
    $client = Client::factory()->create(['name' => 'Lama']);

    \Livewire\Livewire::actingAs($marketing)
        ->test(Form::class)
        ->call('loadClient', $client->id)
        ->set('form.name', 'Baru')
        ->call('save')
        ->assertDispatched('client-saved');

    $this->assertDatabaseHas('clients', ['id' => $client->id, 'name' => 'Baru']);
});

test('admin cannot update a client', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();

    \Livewire\Livewire::actingAs($admin)
        ->test(Form::class)
        ->call('loadClient', $client->id)
        ->assertForbidden();
});

// --- Delete & Restore (SoftDeletes) ---

test('marketing can soft delete a client', function () {
    $marketing = User::factory()->marketing()->create();
    $client = Client::factory()->create();

    \Livewire\Livewire::actingAs($marketing)
        ->test(Index::class)
        ->call('confirmDelete', $client->id)
        ->call('delete');

    $this->assertSoftDeleted('clients', ['id' => $client->id]);
});

test('admin cannot delete a client', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();

    \Livewire\Livewire::actingAs($admin)
        ->test(Index::class)
        ->call('confirmDelete', $client->id)
        ->call('delete')
        ->assertForbidden();
});

test('marketing can restore a soft-deleted client', function () {
    $marketing = User::factory()->marketing()->create();
    $client = Client::factory()->create();
    $client->delete();

    \Livewire\Livewire::actingAs($marketing)
        ->test(Index::class)
        ->call('restore', $client->id);

    $this->assertNotSoftDeleted('clients', ['id' => $client->id]);
});
