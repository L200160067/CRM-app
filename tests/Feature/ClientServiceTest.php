<?php

use App\Livewire\ClientService\Form;
use App\Livewire\ClientService\Index;
use App\Models\Client;
use App\Models\ClientService;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

uses()->group('client-services');

beforeEach(function () {
    $this->user = User::factory()->serverManager()->create();
    $this->client = Client::factory()->create();
    $this->product = Product::factory()->create();
});

test('client services index page can be rendered', function () {
    ClientService::factory()->count(3)->create();

    $this->actingAs($this->user)
        ->get('/client-services')
        ->assertStatus(200)
        ->assertSeeLivewire(Index::class);
});

test('a new client service can be created via form modal', function () {
    Livewire::actingAs($this->user)
        ->test(Form::class)
        ->call('loadClientService')
        ->set('form.client_id', $this->client->id)
        ->set('form.product_id', $this->product->id)
        ->set('form.domain_name', 'example.com')
        ->set('form.status', 'Active')
        ->set('form.billing_cycle', 'Yearly')
        ->set('form.started_at', '2026-01-01')
        ->set('form.expires_at', '2027-01-01')
        ->call('save')
        ->assertDispatched('client-service-saved');

    $this->assertDatabaseHas('client_services', [
        'client_id' => $this->client->id,
        'product_id' => $this->product->id,
        'domain_name' => 'example.com',
        'status' => 'Active',
    ]);
});

test('client service can be updated via form modal', function () {
    $service = ClientService::factory()->create([
        'status' => 'Active',
        'domain_name' => 'old.com',
    ]);

    Livewire::actingAs($this->user)
        ->test(Form::class)
        ->call('loadClientService', $service->id)
        ->set('form.domain_name', 'new-domain.com')
        ->set('form.status', 'Suspended')
        ->call('save')
        ->assertDispatched('client-service-saved');

    $this->assertDatabaseHas('client_services', [
        'id' => $service->id,
        'domain_name' => 'new-domain.com',
        'status' => 'Suspended',
    ]);
});

test('client service can be deleted via index component', function () {
    $service = ClientService::factory()->create();

    Livewire::actingAs($this->user)
        ->test(Index::class)
        ->call('confirmDelete', $service->id)
        ->call('delete');

    $this->assertDatabaseMissing('client_services', [
        'id' => $service->id,
    ]);
});

test('client service validation fails when required fields are missing', function () {
    Livewire::actingAs($this->user)
        ->test(Form::class)
        ->call('loadClientService')
        ->set('form.client_id', null)
        ->set('form.product_id', null)
        ->set('form.started_at', 'not-a-date')
        ->set('form.expires_at', 'not-a-date')
        ->call('save')
        ->assertHasErrors(['form.client_id', 'form.product_id', 'form.started_at', 'form.expires_at']);
});
