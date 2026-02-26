<?php

/** @var \Tests\TestCase $this */

use App\Livewire\Client\Import;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

// --- Authorization ---

test('staff cannot import clients', function () {
    $staff = User::factory()->staff()->create();

    Livewire::actingAs($staff)
        ->test(Import::class)
        ->call('processImport')
        ->assertForbidden();
});

// --- CSV Parsing & Preview ---

test('uploading a valid csv parses rows correctly', function () {
    $admin = User::factory()->admin()->create();

    $csv = "name,company_name,email,phone,address,city\n"
         ."Budi Santoso,PT Budi,budi@example.com,08111,Jl. A,Jakarta\n"
         ."Ani Wulandari,,ani@example.com,,,\n";

    $file = UploadedFile::fake()->createWithContent('clients.csv', $csv);

    $component = Livewire::actingAs($admin)
        ->test(Import::class)
        ->set('csvFile', $file);

    $rows = $component->get('rows');

    expect($rows)->toHaveCount(2)
        ->and($rows[0]['name'])->toBe('Budi Santoso')
        ->and($rows[0]['valid'])->toBeTrue()
        ->and($rows[1]['name'])->toBe('Ani Wulandari')
        ->and($rows[1]['valid'])->toBeTrue();
});

test('row with missing name is marked invalid', function () {
    $admin = User::factory()->admin()->create();

    $csv = "name,company_name,email\n"
         .",PT Tanpa Nama,tanpanama@example.com\n";

    $file = UploadedFile::fake()->createWithContent('clients.csv', $csv);

    $component = Livewire::actingAs($admin)
        ->test(Import::class)
        ->set('csvFile', $file);

    $rows = $component->get('rows');

    expect($rows[0]['valid'])->toBeFalse()
        ->and($rows[0]['errors'])->not->toBeEmpty();
});

test('row with duplicate email is marked invalid', function () {
    $admin = User::factory()->admin()->create();
    Client::factory()->create(['email' => 'existing@example.com']);

    $csv = "name,email\n"
         ."Klien Baru,existing@example.com\n";

    $file = UploadedFile::fake()->createWithContent('clients.csv', $csv);

    $component = Livewire::actingAs($admin)
        ->test(Import::class)
        ->set('csvFile', $file);

    $rows = $component->get('rows');

    expect($rows[0]['valid'])->toBeFalse();
});

// --- Process Import ---

test('admin can import valid csv rows into database', function () {
    $admin = User::factory()->admin()->create();

    $csv = "name,company_name,email,phone,city\n"
         ."Klien Import A,PT A,kliena@test.com,08100,Surabaya\n"
         ."Klien Import B,PT B,klienb@test.com,08200,Bandung\n";

    $file = UploadedFile::fake()->createWithContent('clients.csv', $csv);

    Livewire::actingAs($admin)
        ->test(Import::class)
        ->set('csvFile', $file)
        ->call('processImport')
        ->assertDispatched('client-saved');

    $this->assertDatabaseHas('clients', ['name' => 'Klien Import A', 'email' => 'kliena@test.com']);
    $this->assertDatabaseHas('clients', ['name' => 'Klien Import B', 'email' => 'klienb@test.com']);
});

test('invalid rows are skipped during import', function () {
    $admin = User::factory()->admin()->create();

    $csv = "name,email\n"
         ."Klien Valid,valid@test.com\n"
         .",,\n"            // empty - name required
         .",invalid-email\n"; // no name + invalid email

    $file = UploadedFile::fake()->createWithContent('clients.csv', $csv);

    $component = Livewire::actingAs($admin)
        ->test(Import::class)
        ->set('csvFile', $file)
        ->call('processImport');

    expect($component->get('importedCount'))->toBe(1)
        ->and($component->get('skippedCount'))->toBeGreaterThanOrEqual(1);

    $this->assertDatabaseHas('clients', ['name' => 'Klien Valid']);
});

test('import resets state after completion', function () {
    $admin = User::factory()->admin()->create();

    $csv = "name\nKlien Reset\n";
    $file = UploadedFile::fake()->createWithContent('clients.csv', $csv);

    $component = Livewire::actingAs($admin)
        ->test(Import::class)
        ->set('csvFile', $file)
        ->call('processImport');

    expect($component->get('rows'))->toBeEmpty()
        ->and($component->get('importDone'))->toBeTrue();
});
