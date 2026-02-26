<?php

/** @var \Tests\TestCase $this */

use App\Actions\Invoices\CalculateInvoiceTotalAction;
use App\Enums\DiscountType;
use App\Enums\InvoiceStatus;
use App\Livewire\Invoice\Index;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;

// --- Authorization ---

test('guests cannot access invoices page', function () {
    $this->withoutVite()->get(route('invoices.index'))->assertRedirect(route('login'));
});

test('staff users can view the invoices page', function () {
    $user = User::factory()->staff()->create();
    $this->withoutVite()->actingAs($user)->get(route('invoices.index'))->assertOk();
});

// --- Invoice Index (Livewire) ---

test('invoices index shows invoice numbers', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();
    Invoice::factory()->create([
        'client_id' => $client->id,
        'created_by' => $admin->id,
        'invoice_number' => 'INV-TEST-0001',
        'status' => InvoiceStatus::Draft,
    ]);

    \Livewire\Livewire::actingAs($admin)
        ->test(Index::class)
        ->assertSeeText('INV-TEST-0001');
});

test('invoices index can be searched by invoice number', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();

    Invoice::factory()->create([
        'client_id' => $client->id,
        'created_by' => $admin->id,
        'invoice_number' => 'INV-2026-0001',
        'status' => InvoiceStatus::Draft,
    ]);
    Invoice::factory()->create([
        'client_id' => $client->id,
        'created_by' => $admin->id,
        'invoice_number' => 'INV-2026-0002',
        'status' => InvoiceStatus::Draft,
    ]);

    \Livewire\Livewire::actingAs($admin)
        ->test(Index::class)
        ->set('search', '0001')
        ->assertSeeText('INV-2026-0001')
        ->assertDontSeeText('INV-2026-0002');
});

// --- Soft Delete (invoice) ---

test('admin can soft delete a draft invoice', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();
    $invoice = Invoice::factory()->create([
        'client_id' => $client->id,
        'created_by' => $admin->id,
        'invoice_number' => 'INV-DEL-0001',
        'status' => InvoiceStatus::Draft,
    ]);

    \Livewire\Livewire::actingAs($admin)
        ->test(Index::class)
        ->call('confirmDelete', $invoice->id)
        ->call('delete');

    $this->assertSoftDeleted('invoices', ['id' => $invoice->id]);
});

test('admin can restore a soft-deleted invoice', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();
    $invoice = Invoice::factory()->create([
        'client_id' => $client->id,
        'created_by' => $admin->id,
        'invoice_number' => 'INV-RST-0001',
        'status' => InvoiceStatus::Draft,
    ]);
    $invoice->delete();

    \Livewire\Livewire::actingAs($admin)
        ->test(Index::class)
        ->call('restore', $invoice->id);

    $this->assertNotSoftDeleted('invoices', ['id' => $invoice->id]);
});

test('staff cannot delete an invoice', function () {
    $staff = User::factory()->staff()->create();
    $client = Client::factory()->create();
    $invoice = Invoice::factory()->create([
        'client_id' => $client->id,
        'created_by' => $staff->id,
        'invoice_number' => 'INV-STF-0001',
        'status' => InvoiceStatus::Draft,
    ]);

    \Livewire\Livewire::actingAs($staff)
        ->test(Index::class)
        ->call('confirmDelete', $invoice->id)
        ->call('delete')
        ->assertForbidden();
});

// --- Business Rule: Paid/Canceled Invoices ---

test('paid invoice cannot be deleted', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();
    $invoice = Invoice::factory()->create([
        'client_id' => $client->id,
        'created_by' => $admin->id,
        'invoice_number' => 'INV-PAID-0001',
        'status' => InvoiceStatus::Paid,
    ]);

    expect(fn () => $invoice->delete())->toThrow(\Exception::class);
    $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
});

// --- CalculateInvoiceTotalAction ---

test('invoice total is calculated correctly with fixed discount', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();
    $invoice = Invoice::create([
        'client_id' => $client->id,
        'created_by' => $admin->id,
        'invoice_number' => 'INV-CALC-001',
        'issue_date' => now(),
        'due_date' => now()->addDays(30),
        'status' => InvoiceStatus::Draft,
        'discount_type' => DiscountType::Fixed,
        'discount' => 50000,
        'tax_rate' => 11,
    ]);

    InvoiceItem::withoutEvents(function () use ($invoice) {
        $invoice->items()->create([
            'item_name' => 'Jasa Desain',
            'quantity' => 2,
            'unit_price' => 500000,
            'total_price' => 1000000,
        ]);
    });

    app(CalculateInvoiceTotalAction::class)->execute($invoice->fresh());
    $invoice->refresh();

    // subtotal=1_000_000, discount=50_000, after_discount=950_000, tax=11%=104_500, grand_total=1_054_500
    expect((float) $invoice->subtotal)->toBe(1000000.0)
        ->and((float) $invoice->discount)->toBe(50000.0)
        ->and((float) $invoice->tax)->toBe(104500.0)
        ->and((float) $invoice->grand_total)->toBe(1054500.0);
});

test('invoice total is calculated correctly with percentage discount', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();
    $invoice = Invoice::create([
        'client_id' => $client->id,
        'created_by' => $admin->id,
        'invoice_number' => 'INV-CALC-002',
        'issue_date' => now(),
        'due_date' => now()->addDays(30),
        'status' => InvoiceStatus::Draft,
        'discount_type' => DiscountType::Percentage,
        'discount_rate' => 10, // 10%
        'tax_rate' => 0,
    ]);

    InvoiceItem::withoutEvents(function () use ($invoice) {
        $invoice->items()->create([
            'item_name' => 'Jasa Konsultasi',
            'quantity' => 1,
            'unit_price' => 1000000,
            'total_price' => 1000000,
        ]);
    });

    app(CalculateInvoiceTotalAction::class)->execute($invoice->fresh());
    $invoice->refresh();

    // subtotal=1_000_000, discount=10%=100_000, after_discount=900_000, tax=0, grand_total=900_000
    expect((float) $invoice->subtotal)->toBe(1000000.0)
        ->and((float) $invoice->discount)->toBe(100000.0)
        ->and((float) $invoice->grand_total)->toBe(900000.0);
});

test('discount cannot exceed subtotal', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();
    $invoice = Invoice::create([
        'client_id' => $client->id,
        'created_by' => $admin->id,
        'invoice_number' => 'INV-CALC-003',
        'issue_date' => now(),
        'due_date' => now()->addDays(30),
        'status' => InvoiceStatus::Draft,
        'discount_type' => DiscountType::Fixed,
        'discount' => 9999999, // More than subtotal
        'tax_rate' => 0,
    ]);

    InvoiceItem::withoutEvents(function () use ($invoice) {
        $invoice->items()->create([
            'item_name' => 'Produk Murah',
            'quantity' => 1,
            'unit_price' => 10000,
            'total_price' => 10000,
        ]);
    });

    app(CalculateInvoiceTotalAction::class)->execute($invoice->fresh());
    $invoice->refresh();

    // Discount should be clamped to subtotal (10_000), grand_total = 0
    expect((float) $invoice->discount)->toBe(10000.0)
        ->and((float) $invoice->grand_total)->toBe(0.0);
});

// --- Print Route ---

test('guests cannot access invoice print page', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();
    $invoice = Invoice::factory()->create([
        'client_id' => $client->id,
        'created_by' => $admin->id,
        'status' => InvoiceStatus::Draft,
    ]);

    $this->withoutVite()
        ->get(route('invoices.print', $invoice))
        ->assertRedirect(route('login'));
});

test('staff can access invoice print page', function () {
    $staff = User::factory()->staff()->create();
    $client = Client::factory()->create();
    $invoice = Invoice::factory()->create([
        'client_id' => $client->id,
        'created_by' => $staff->id,
        'status' => InvoiceStatus::Draft,
    ]);

    $this->withoutVite()
        ->actingAs($staff)
        ->get(route('invoices.print', $invoice))
        ->assertOk();
});

test('invoice print page displays invoice number and client name', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create(['name' => 'PT Contoh Klien']);
    $invoice = Invoice::factory()->create([
        'client_id' => $client->id,
        'created_by' => $admin->id,
        'invoice_number' => 'INV-PRINT-0001',
        'status' => InvoiceStatus::Draft,
    ]);

    $this->withoutVite()
        ->actingAs($admin)
        ->get(route('invoices.print', $invoice))
        ->assertOk()
        ->assertSee('INV-PRINT-0001')
        ->assertSee('PT Contoh Klien');
});
