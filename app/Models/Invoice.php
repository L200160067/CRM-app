<?php

namespace App\Models;

use App\Enums\DiscountType;
use App\Enums\InvoiceStatus;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'created_by',
        'invoice_number',
        'issue_date',
        'due_date',
        'status',
        'subtotal',
        'tax_rate',
        'tax',
        'discount_type',
        'discount_rate',
        'discount',
        'grand_total',
        'notes',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'status' => InvoiceStatus::class,
            'discount_type' => DiscountType::class,
            'subtotal' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax' => 'decimal:2',
            'discount_rate' => 'decimal:2',
            'discount' => 'decimal:2',
            'grand_total' => 'decimal:2',
        ];
    }

    protected static function booted()
    {
        static::updating(function (Invoice $invoice) {
            // Jika invoice sudah dibayar atau dibatalkan, cegah perubahan data krusial
            if (in_array($invoice->getOriginal('status'), [InvoiceStatus::Paid, InvoiceStatus::Canceled])) {
                $changedColumns = array_keys($invoice->getDirty());
                // Hanya notes dan updated_at (karena timestamp otomatis berubah) yang boleh lolos
                $allowedChanges = ['notes', 'updated_at'];

                // Jika ada kolom di luar allowedChanges yang diubah, tolak!
                if (count(array_diff($changedColumns, $allowedChanges)) > 0) {
                    throw new Exception('Invoice yang sudah lunas atau dibatalkan tidak boleh diubah dokumennya.');
                }
            }
        });

        static::deleting(function (Invoice $invoice) {
            if (in_array($invoice->status, [InvoiceStatus::Paid, InvoiceStatus::Canceled])) {
                throw new Exception('Dokumen invoice legal yang sudah diproses tidak boleh dihapus.');
            }
        });
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
