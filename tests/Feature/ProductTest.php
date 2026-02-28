<?php

/** @var \Tests\TestCase $this */

use App\Livewire\Product\Form;
use App\Livewire\Product\Index;
use App\Models\Product;
use App\Models\User;

// --- Authorization ---

test('guests cannot access products page', function () {
    $this->withoutVite()->get(route('products.index'))->assertRedirect(route('login'));
});

test('all authenticated users can view the products page', function () {
    $user = User::factory()->marketing()->create();
    $this->withoutVite()->actingAs($user)->get(route('products.index'))->assertOk();
});

// --- Product Index ---

test('products index shows list of products', function () {
    $user = User::factory()->marketing()->create();
    $product = Product::factory()->create(['name' => 'Jasa Desain Logo']);

    \Livewire\Livewire::actingAs($user)
        ->test(Index::class)
        ->assertSeeText('Jasa Desain Logo');
});

test('products index search filters results', function () {
    $user = User::factory()->marketing()->create();
    Product::factory()->create(['name' => 'Jasa Cetak']);
    Product::factory()->create(['name' => 'Jasa Video']);

    \Livewire\Livewire::actingAs($user)
        ->test(Index::class)
        ->set('search', 'Cetak')
        ->assertSeeText('Jasa Cetak')
        ->assertDontSeeText('Jasa Video');
});

// --- Create Product ---

test('super admin can create a product', function () {
    $superAdmin = User::factory()->superAdmin()->create();

    \Livewire\Livewire::actingAs($superAdmin)
        ->test(Form::class)
        ->call('loadProduct')
        ->set('form.name', 'Jasa Konsultasi')
        ->set('form.default_price', 500000)
        ->call('save')
        ->assertDispatched('product-saved');

    $this->assertDatabaseHas('products', ['name' => 'Jasa Konsultasi', 'default_price' => 500000]);
});

test('admin cannot create a product', function () {
    $admin = User::factory()->admin()->create();

    \Livewire\Livewire::actingAs($admin)
        ->test(Form::class)
        ->call('loadProduct')
        ->assertForbidden();
});

test('product name is required', function () {
    $superAdmin = User::factory()->superAdmin()->create();

    \Livewire\Livewire::actingAs($superAdmin)
        ->test(Form::class)
        ->call('loadProduct')
        ->set('form.name', '')
        ->call('save')
        ->assertHasErrors(['form.name' => 'required']);
});

test('product price must be numeric and non-negative', function () {
    $superAdmin = User::factory()->superAdmin()->create();

    \Livewire\Livewire::actingAs($superAdmin)
        ->test(Form::class)
        ->call('loadProduct')
        ->set('form.name', 'Test Produk')
        ->set('form.default_price', -100)
        ->call('save')
        ->assertHasErrors(['form.default_price' => 'min']);
});

// --- Update Product ---

test('super admin can update a product', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $product = Product::factory()->create(['name' => 'Nama Lama']);

    \Livewire\Livewire::actingAs($superAdmin)
        ->test(Form::class)
        ->call('loadProduct', $product->id)
        ->set('form.name', 'Nama Baru')
        ->call('save')
        ->assertDispatched('product-saved');

    $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Nama Baru']);
});

// --- Delete & Restore ---

test('super admin can soft delete a product', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $product = Product::factory()->create();

    \Livewire\Livewire::actingAs($superAdmin)
        ->test(Index::class)
        ->call('confirmDelete', $product->id)
        ->call('delete');

    $this->assertSoftDeleted('products', ['id' => $product->id]);
});

test('super admin can restore a soft-deleted product', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $product = Product::factory()->create();
    $product->delete();

    \Livewire\Livewire::actingAs($superAdmin)
        ->test(Index::class)
        ->call('restore', $product->id);

    $this->assertNotSoftDeleted('products', ['id' => $product->id]);
});
