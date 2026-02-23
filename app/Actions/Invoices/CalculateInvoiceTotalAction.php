<?php

namespace App\Actions\Invoices;

use App\Enums\DiscountType;
use App\Models\Invoice;

class CalculateInvoiceTotalAction
{
    public function execute(Invoice $invoice): void
    {
        // 1. Rekalkulasi Subtotal dari sum item total_price
        $subtotal = $invoice->items()->sum('total_price') ?? 0;
        $invoice->subtotal = $subtotal;

        // 2. Kalkulasi Diskon yang Aman
        $discountAmount = 0;

        if ($invoice->discount_type === DiscountType::Percentage) {
            $discountRate = $invoice->discount_rate ?? 0;
            $discountAmount = ($subtotal * $discountRate) / 100;
        } elseif ($invoice->discount_type === DiscountType::Fixed) {
            // Abaikan discount_rate (karena limit 999.99).
            // Ambil nominal langsung dari input diskon yang dimasukkan admin.
            $discountAmount = $invoice->discount ?? 0;

            // Pastikan database bersih dari sisa persentase jika tipe diubah ke fixed
            $invoice->discount_rate = null;
        }

        // Validasi: Diskon tidak boleh melebih nominal tagihan (subtotal)
        $discountAmount = min($discountAmount, $subtotal);
        $invoice->discount = $discountAmount;
        $subtotalAfterDiscount = $subtotal - $discountAmount;

        // 3. Kalkulasi Pajak Dasar
        $taxRate = $invoice->tax_rate ?? 0;
        $taxAmount = ($subtotalAfterDiscount * $taxRate) / 100;
        $invoice->tax = $taxAmount;

        // 4. Perhitungan Granular Grand Total
        $invoice->grand_total = $subtotalAfterDiscount + $taxAmount;

        // 5. Simpan Diam-Diam Tanpa Memacu Sinkronisasi "updated_at" yang Membebani
        // Jika perlu timestamp diperbarui, gunakan ->save() standar.
        $invoice->saveQuietly();
    }
}
