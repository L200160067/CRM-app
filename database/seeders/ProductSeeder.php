<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Pembuatan Website Company Profile',
                'description' => 'Jasa merancang dan membangun website Company Profile lengkap dengan CMS dan optimasi performa dasar. Cocok untuk perusahaan baru yang ingin tampil profesional.',
                'default_price' => 5000000.00,
            ],
            [
                'name' => 'Pembuatan Website E-Commerce',
                'description' => 'Sistem toko online komprehensif dengan manajemen inventaris, integrasi payment gateway lokal (Midtrans/Xendit), dan dukungan SEO.',
                'default_price' => 15000000.00,
            ],
            [
                'name' => 'Pembuatan Aplikasi Custom (ERP/CRM)',
                'description' => 'Membangun sistem informasi kustom skala Enterprise berbasis web untuk efisiensi alur bisnis dan manajemen operasional internal perusahaan.',
                'default_price' => 35000000.00,
            ],
            [
                'name' => 'Pengembangan Aplikasi Mobile (Android & iOS)',
                'description' => 'Aplikasi mobile cross-platform native/hybrid. Termasuk integrasi ke server push notification, geolocation, dan API backend.',
                'default_price' => 45000000.00,
            ],
            [
                'name' => 'UI/UX Design & Prototyping',
                'description' => 'Riset pengguna (UX Research), perancangan wireframe, hingga High-Fidelity Prototype menggunakan Figma. Termasuk pembuatan interaksi dan aset grafis.',
                'default_price' => 8000000.00,
            ],
            [
                'name' => 'Jasa Maintenance Server & Hosting (Tahunan)',
                'description' => 'Monitoring uptime server 24/7, manajemen otomatisasi backup harian, instalasi SSL, dan update patch keamanan pada cloud VPS Linux.',
                'default_price' => 6000000.00,
            ],
            [
                'name' => 'Integrasi API & Sistem Eksternal',
                'description' => 'Implementasi koneksi servis REST/SOAP pihak ketiga (seperti WhatsApp Gateway, sistem perpajakan e-Faktur, dll) ke dalam aplikasi eksisting klien.',
                'default_price' => 3500000.00,
            ],
            [
                'name' => 'Konsultasi IT & Arsitektur Sistem (Per Jam)',
                'description' => 'Sesi briefing, code-review teknikal, dan perancangan topologi infrastruktur cloud / optimasi performa database bersama Principal Engineer.',
                'default_price' => 750000.00,
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
