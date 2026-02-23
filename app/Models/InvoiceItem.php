<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $touches = ['invoice'];

    protected $fillable = [
        'invoice_id',
        'product_id',
        'item_name',
        'quantity',
        'unit_price',
        'description',
    ];

    protected static function booted()
    {
        $checkInvoiceState = function (InvoiceItem $item) {
            // Tarik relasi invoice untuk mengecek statusnya
            if ($item->invoice && in_array($item->invoice->status, [InvoiceStatus::Paid, InvoiceStatus::Canceled])) {
                throw new Exception('Tidak dapat mengubah item pada invoice yang sudah lunas atau dibatalkan.');
            }
        };

        static::creating($checkInvoiceState);
        static::updating($checkInvoiceState);
        static::deleting($checkInvoiceState);

        static::saving(function (InvoiceItem $item) {
            // Paksa kalkulasi matematika di level model sebelum masuk database
            $item->total_price = $item->quantity * $item->unit_price;
        });

        $recalculateInvoice = function (InvoiceItem $item) {
            if ($item->invoice) {
                app(\App\Actions\Invoices\CalculateInvoiceTotalAction::class)->execute($item->invoice);
            }
        };

        // Setelah data berhasil disimpan ke DB, picu Action untuk menghitung total induknya
        static::saved($recalculateInvoice);
        static::deleted($recalculateInvoice);
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
