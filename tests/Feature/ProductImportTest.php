<?php

/** @var \Tests\TestCase $this */

use App\Livewire\Product\Import;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

// --- Authorization ---

test('staff cannot import products', function () {
    $staff = User::factory()->staff()->create();

    Livewire::actingAs($staff)
        ->test(Import::class)
        ->call('processImport')
        ->assertForbidden();
});

// --- CSV Parsing & Preview ---

test('uploading a valid product csv parses rows correctly', function () {
    $admin = User::factory()->superAdmin()->create();

    $csv = "name,description,default_price\n"
         ."Jasa Desain Web,Pembuatan website,2500000\n"
         ."Jasa Maintenance,,750000\n";

    $file = UploadedFile::fake()->createWithContent('products.csv', $csv);

    $component = Livewire::actingAs($admin)
        ->test(Import::class)
        ->set('csvFile', $file);

    $rows = $component->get('rows');

    expect($rows)->toHaveCount(2)
        ->and($rows[0]['name'])->toBe('Jasa Desain Web')
        ->and($rows[0]['default_price'])->toBe('2500000')
        ->and($rows[0]['valid'])->toBeTrue()
        ->and($rows[1]['name'])->toBe('Jasa Maintenance')
        ->and($rows[1]['valid'])->toBeTrue();
});

test('row with missing name is marked invalid', function () {
    $admin = User::factory()->superAdmin()->create();

    $csv = "name,default_price\n"
         .",500000\n";

    $file = UploadedFile::fake()->createWithContent('products.csv', $csv);

    $component = Livewire::actingAs($admin)
        ->test(Import::class)
        ->set('csvFile', $file);

    $rows = $component->get('rows');

    expect($rows[0]['valid'])->toBeFalse()
        ->and($rows[0]['errors'])->not->toBeEmpty();
});

test('row with missing price is marked invalid', function () {
    $admin = User::factory()->superAdmin()->create();

    $csv = "name,default_price\n"
         ."Produk Tanpa Harga,\n";

    $file = UploadedFile::fake()->createWithContent('products.csv', $csv);

    $component = Livewire::actingAs($admin)
        ->test(Import::class)
        ->set('csvFile', $file);

    $rows = $component->get('rows');

    expect($rows[0]['valid'])->toBeFalse();
});

test('row with non-numeric price is marked invalid', function () {
    $admin = User::factory()->superAdmin()->create();

    $csv = "name,default_price\n"
         ."Produk Harga Salah,dua juta\n";

    $file = UploadedFile::fake()->createWithContent('products.csv', $csv);

    $component = Livewire::actingAs($admin)
        ->test(Import::class)
        ->set('csvFile', $file);

    expect($component->get('rows.0.valid'))->toBeFalse();
});

// --- Process Import ---

test('admin can import valid product csv rows into database', function () {
    $admin = User::factory()->superAdmin()->create();

    $csv = "name,description,default_price\n"
         ."Produk Import A,Deskripsi A,1000000\n"
         ."Produk Import B,,500000\n";

    $file = UploadedFile::fake()->createWithContent('products.csv', $csv);

    Livewire::actingAs($admin)
        ->test(Import::class)
        ->set('csvFile', $file)
        ->call('processImport')
        ->assertDispatched('product-saved');

    $this->assertDatabaseHas('products', ['name' => 'Produk Import A', 'default_price' => 1000000]);
    $this->assertDatabaseHas('products', ['name' => 'Produk Import B']);
});

test('invalid product rows are skipped during import', function () {
    $admin = User::factory()->superAdmin()->create();

    $csv = "name,default_price\n"
         ."Produk Valid,300000\n"
         .",,\n"
         .",bukan-angka\n";

    $file = UploadedFile::fake()->createWithContent('products.csv', $csv);

    $component = Livewire::actingAs($admin)
        ->test(Import::class)
        ->set('csvFile', $file)
        ->call('processImport');

    expect($component->get('importedCount'))->toBe(1)
        ->and($component->get('skippedCount'))->toBeGreaterThanOrEqual(1);

    $this->assertDatabaseHas('products', ['name' => 'Produk Valid']);
});

test('product import resets state after completion', function () {
    $admin = User::factory()->superAdmin()->create();

    $csv = "name,default_price\nProduk Reset,100000\n";
    $file = UploadedFile::fake()->createWithContent('products.csv', $csv);

    $component = Livewire::actingAs($admin)
        ->test(Import::class)
        ->set('csvFile', $file)
        ->call('processImport');

    expect($component->get('rows'))->toBeEmpty()
        ->and($component->get('importDone'))->toBeTrue();
});
