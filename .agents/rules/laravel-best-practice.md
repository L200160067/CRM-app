---
trigger: model_decision
description: Laravel best practices for coding standards, Eloquent, controllers, validation, security, and architecture. Load when writing or reviewing any Laravel PHP code.
---

# Laravel Best Practice (CWP-Compatible)

> Rules ini sudah disesuaikan untuk CWP Shared Hosting.
> Beberapa fitur Laravel standar DINONAKTIFKAN karena constraint server.
> Lihat `.agent/rules/cwp-constraints.md` untuk daftar lengkap larangan.

---

## PHP Standard

```php
<?php

declare(strict_types=1); // wajib di semua file class
```

- Gunakan PHP 8.2+ features: typed properties, match expression, named arguments, readonly
- Ikuti PSR-12 coding standard
- Gunakan type hints di semua method signature
- Nama direktori: lowercase dengan dash (`app/Http/Controllers`)
- Nama class: PascalCase. Nama method: camelCase. Nama kolom DB: snake_case

---

## Controller — Skinny

Controller hanya boleh:
1. Menerima request
2. Memanggil Service/Action
3. Return response

```php
// SALAH — logic bisnis di controller
class OrderController extends Controller
{
    public function store(Request $request)
    {
        $total = 0;
        foreach ($request->items as $item) {
            $total += $item['price'] * $item['qty'];
        }
        $order = Order::create([...]);
        // kirim notifikasi, update stok, dll
    }
}

// BENAR — delegate ke Service
class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $this->orderService->create($request->validated());
        return redirect()->route('orders.index')->with('success', 'Order dibuat.');
    }
}
```

---

## Validation — Selalu Form Request

Jangan validasi di controller langsung. Buat Form Request terpisah.

```php
// Buat: app/Http/Requests/StoreProductRequest.php
class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // atau cek policy
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'price'       => ['required', 'numeric', 'min:0'],
            'image'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'category_id' => ['required', 'exists:categories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'        => 'Nama produk wajib diisi.',
            'category_id.exists'   => 'Kategori tidak valid.',
        ];
    }
}
```

---

## Eloquent — Standar Wajib

### Mass Assignment
```php
// Model — selalu definisikan $fillable
class Product extends Model
{
    protected $fillable = ['name', 'price', 'image', 'category_id', 'is_active'];

    // JANGAN gunakan $guarded = [] di produksi
}
```

### Relationships
```php
class Order extends Model
{
    // Definisikan return type
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
```

### Scopes — Untuk Query Berulang
```php
class Product extends Model
{
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }
}

// Penggunaan:
Product::active()->byCategory(3)->paginate(20);
```

### Accessors & Mutators (Laravel 9+)
```php
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->image ? asset($this->image) : asset('images/placeholder.jpg'),
        );
    }

    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn (int $value) => $value / 100,          // simpan dalam sen
            set: fn (float $value) => (int) ($value * 100),
        );
    }
}
```

---

## Query — Selalu Eager Load & Paginate

```php
// SALAH — N+1 problem
$orders = Order::all();
foreach ($orders as $order) {
    echo $order->user->name;  // query per baris
}

// BENAR
$orders = Order::with(['user', 'items.product'])
    ->latest()
    ->paginate(20);

// BENAR — select kolom yang perlu saja
$products = Product::active()
    ->select(['id', 'name', 'price', 'image'])
    ->with('category:id,name')
    ->paginate(20);
```

---

## Service Class — Untuk Business Logic

```
app/
└── Services/
    ├── OrderService.php
    ├── ProductService.php
    └── AttendanceService.php
```

```php
class OrderService
{
    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $order = Order::create([
                'user_id' => auth()->id(),
                'total'   => $this->calculateTotal($data['items']),
                'status'  => 'pending',
            ]);

            foreach ($data['items'] as $item) {
                $order->items()->create($item);
            }

            return $order;
        });
    }

    private function calculateTotal(array $items): int
    {
        return collect($items)->sum(fn ($item) => $item['price'] * $item['qty']);
    }
}
```

---

## Database Transaction

Selalu gunakan transaction untuk operasi yang melibatkan lebih dari satu tabel:

```php
DB::transaction(function () {
    // semua query di sini atomic
    // jika satu gagal, semua di-rollback otomatis
});
```

---

## Caching — File Driver (CWP Safe)

CWP tidak punya Redis/Memcached. Gunakan file cache driver.

```php
// Cache query yang berat — max 1 jam
$categories = Cache::remember('categories.all', 3600, function () {
    return Category::orderBy('name')->get(['id', 'name']);
});

// Invalidate cache saat data berubah
Cache::forget('categories.all');

// Cache per user
$key = 'user.' . auth()->id() . '.dashboard';
$data = Cache::remember($key, 1800, fn () => $this->getDashboardData());
```

---

## Error Handling

```php
// Custom exception
class InsufficientStockException extends \RuntimeException
{
    public function __construct(string $productName)
    {
        parent::__construct("Stok {$productName} tidak mencukupi.");
    }
}

// Gunakan di service
if ($product->stock < $qty) {
    throw new InsufficientStockException($product->name);
}

// Handle di Handler atau Controller
try {
    $this->orderService->create($data);
} catch (InsufficientStockException $e) {
    return back()->withErrors(['stock' => $e->getMessage()]);
}
```

---

## Security — Standar Wajib

```php
// 1. Selalu gunakan $fillable, bukan $guarded = []
// 2. Selalu validasi input via Form Request
// 3. Gunakan Policy untuk authorization

class ProductPolicy
{
    public function update(User $user, Product $product): bool
    {
        return $user->id === $product->user_id || $user->isAdmin();
    }
}

// Di controller:
$this->authorize('update', $product);

// 4. Hindari raw query — jika terpaksa, selalu binding:
DB::select('SELECT * FROM products WHERE id = ?', [$id]); // BENAR
DB::select("SELECT * FROM products WHERE id = $id");       // SALAH — SQL injection
```

---

## Routing — Konvensi

```php
// web.php — gunakan resource route
Route::resource('products', ProductController::class);

// Kelompokkan dengan middleware
Route::middleware(['auth'])->group(function () {
    Route::resource('orders', OrderController::class);
    Route::resource('attendance', AttendanceController::class);
});

// Named route — selalu gunakan nama, bukan URL hardcoded
route('products.show', $product);   // BENAR
'/products/' . $product->id;        // SALAH
```

---

## Blade — Konvensi

```blade
{{-- Output — auto-escape XSS --}}
{{ $product->name }}

{{-- HTML mentah — hanya jika benar-benar perlu --}}
{!! $product->description_html !!}

{{-- Component --}}
<x-alert type="success" :message="$message" />

{{-- Loop dengan empty state --}}
@forelse($products as $product)
    <div>{{ $product->name }}</div>
@empty
    <p>Belum ada produk.</p>
@endforelse
```

---

## Struktur Direktori yang Dianjurkan

```
app/
├── Http/
│   ├── Controllers/        # Skinny — hanya request/response
│   ├── Requests/           # Form Request per fitur
│   └── Middleware/
├── Models/                 # Eloquent models
├── Services/               # Business logic
├── Exceptions/             # Custom exceptions
└── Helpers/                # Global helper functions (daftarkan di composer.json)

resources/views/
├── components/             # Blade components
├── layouts/                # Layout templates
└── pages/                  # Halaman per fitur
```

---

## ❌ DINONAKTIFKAN untuk Server CWP ini

Fitur Laravel berikut **tidak boleh digunakan** karena server constraint:

| Fitur | Alasan | Alternatif |
|---|---|---|
| Queue / Jobs | Tidak ada worker | Compute on read / lazy |
| Task Scheduling | Tidak ada cron | Trigger manual via request |
| Storage::disk('public') | storage:link gagal | `public/uploads/` + `asset()` |
| Redis / Memcached cache | Tidak tersedia | `CACHE_DRIVER=file` |
| php artisan migrate (server) | Tidak ada SSH | Export SQL → phpMyAdmin |
| config:cache / route:cache | Break produksi | Jangan di-cache |
