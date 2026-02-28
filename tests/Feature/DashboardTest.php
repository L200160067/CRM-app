<?php

/** @var \Tests\TestCase $this */

use App\Enums\InvoiceStatus;
use App\Livewire\Dashboard;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('dashboard shows total revenue from paid invoices', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();

    Invoice::factory()->create([
        'client_id'   => $client->id,
        'created_by'  => $admin->id,
        'status'      => InvoiceStatus::Paid,
        'grand_total' => 1_000_000,
    ]);

    Invoice::factory()->create([
        'client_id'   => $client->id,
        'created_by'  => $admin->id,
        'status'      => InvoiceStatus::Draft,
        'grand_total' => 500_000,
    ]);

    \Livewire\Livewire::actingAs($admin)
        ->test(Dashboard::class)
        ->assertSeeText('1.000.000');
});

test('dashboard shows total active clients count', function () {
    $admin = User::factory()->admin()->create();
    Client::factory()->count(3)->create();

    \Livewire\Livewire::actingAs($admin)
        ->test(Dashboard::class)
        ->assertSeeText('3');
});

test('dashboard shows five most recent invoices', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();

    Invoice::factory()->count(6)->create([
        'client_id'  => $client->id,
        'created_by' => $admin->id,
        'status'     => InvoiceStatus::Draft,
    ]);

    $component = \Livewire\Livewire::actingAs($admin)->test(Dashboard::class);

    expect($component->instance()->recentInvoices)->toHaveCount(5);
});

test('dashboard overdue count only includes overdue invoices', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();

    Invoice::factory()->create([
        'client_id'  => $client->id,
        'created_by' => $admin->id,
        'status'     => InvoiceStatus::Overdue,
    ]);

    Invoice::factory()->create([
        'client_id'  => $client->id,
        'created_by' => $admin->id,
        'status'     => InvoiceStatus::Paid,
    ]);

    $component = \Livewire\Livewire::actingAs($admin)->test(Dashboard::class);

    expect($component->instance()->overdueCount)->toBe(1);
});
