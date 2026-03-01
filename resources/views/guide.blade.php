<x-layouts::app :title="__('Panduan Sistem')">
    <div class="space-y-6">
        <div>
            <flux:heading size="xl" level="1">Panduan Sistem M-One Solution</flux:heading>
            <flux:subheading size="lg" class="mb-6">Panduan operasional yang disesuaikan dengan wewenang (Hak Akses) Anda.</flux:subheading>
        </div>

        <flux:separator variant="subtle" />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Data Akun -->
            <flux:card class="space-y-2">
                <flux:heading size="lg">Profil Peran Anda</flux:heading>
                <flux:subheading>Detail peran dalam kebijakan sistem.</flux:subheading>
                
                <ul class="mt-4 space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                    <li><strong>Nama:</strong> {{ auth()->user()->name }}</li>
                    <li><strong>Email:</strong> {{ auth()->user()->email }}</li>
                    <li>
                        <strong>Role Saat Ini:</strong> 
                        <flux:badge color="blue" class="ml-1">{{ ucfirst(auth()->user()->role->value) }}</flux:badge>
                    </li>
                </ul>
            </flux:card>

            <!-- Izin Global -->
            <flux:card class="space-y-2">
                <flux:heading size="lg">Ikhtisar Kapabilitas Pribadi</flux:heading>
                <flux:subheading>Ringkasan wewenang teknis utama.</flux:subheading>
                
                <ul class="mt-4 space-y-2 text-sm text-zinc-600 dark:text-zinc-400 list-disc pl-4">
                    @if (auth()->user()->isSuperAdmin())
                        <li>Anda adalah <strong class="text-zinc-900 dark:text-zinc-100">Super Admin (Pemilik Sistem)</strong>.</li>
                        <li>Anda memiliki akses absolut tak terbatas pada seluruh modul CRM.</li>
                        <li>Anda berwenang mengelola data keuangan, menghapus entitas secara permanen (*Force Delete*), dan mengatur semua produk.</li>
                    @elseif (auth()->user()->isAdmin())
                        <li>Anda adalah <strong class="text-zinc-900 dark:text-zinc-100">Admin Billing & Keuangan</strong>.</li>
                        <li>Fokus utama Anda adalah menerbitkan tagihan (Invoice) dan mengatur operasional penagihan klien.</li>
                        <li>Tampilan Dashboard Anda disaring dari metrik pendapatan total demi fokus performa.</li>
                    @elseif (auth()->user()->isMarketing())
                        <li>Anda adalah personel <strong class="text-zinc-900 dark:text-zinc-100">Marketing & Sales</strong>.</li>
                        <li>Fokus utama Anda adalah prospek klien baru dan negosiasi.</li>
                        <li>Anda tidak memiliki akses ke tagihan (*Invoice*) perusahaan.</li>
                    @elseif (auth()->user()->isServerManager())
                        <li>Anda adalah <strong class="text-zinc-900 dark:text-zinc-100">Tim IT & Server Manager</strong>.</li>
                        <li>Fokus utama Anda adalah operasional teknis layanan, infrastruktur jaringan, dan status hosting klien.</li>
                        <li>Anda dilarang melihat daftar produk, harga, dan seluruh manajemen finansial perusahaan.</li>
                    @endif
                </ul>
            </flux:card>
        </div>

        <flux:heading size="lg" class="mt-8 mb-4">Cakupan Izin Akses Tiap Modul</flux:heading>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <!-- MODUL CLIENTS -->
            <flux:card>
                <div class="flex items-center gap-2 mb-2">
                    <flux:icon.users variant="outline" class="size-5 text-zinc-500" />
                    <flux:heading>Klien (Clients)</flux:heading>
                </div>
                <ul class="list-disc pl-5 text-sm text-zinc-600 dark:text-zinc-400 space-y-1">
                    @if (auth()->user()->isSuperAdmin())
                        <li>Melihat daftar dan rincian kontak klien</li>
                        <li>Menambah, Mengedit data klien</li>
                        <li class="text-red-600 dark:text-red-400 font-medium">Menghapus dan Force-Delete klien</li>
                    @elseif (auth()->user()->isAdmin() || auth()->user()->isMarketing())
                        <li>Melihat daftar dan rincian kontak klien</li>
                        <li>Menambah Klien Baru (Lead Generation / Penagihan)</li>
                        <li>Mengedit Data Klien</li>
                        <li class="text-orange-600 dark:text-orange-400 text-xs mt-2">Izin Ditolak: Menghapus Data Klien</li>
                    @elseif (auth()->user()->isServerManager())
                        <li>Melihat daftar klien (Sebagai referensi kepemilikan domain/server saja)</li>
                        <li class="text-orange-600 dark:text-orange-400 text-xs mt-2">Izin Ditolak: Tambah/Edit/Hapus Data Klien</li>
                    @endif
                </ul>
            </flux:card>

            <!-- MODUL PRODUCTS -->
            @if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isMarketing())
            <flux:card>
                <div class="flex items-center gap-2 mb-2">
                    <flux:icon.squares-2x2 variant="outline" class="size-5 text-zinc-500" />
                    <flux:heading>Katalog Produk (Products)</flux:heading>
                </div>
                <ul class="list-disc pl-5 text-sm text-zinc-600 dark:text-zinc-400 space-y-1">
                    @if (auth()->user()->isSuperAdmin())
                        <li>Menambah Produk Jasa / Retainer Baru</li>
                        <li>Memperbarui Harga Master</li>
                        <li class="text-red-600 dark:text-red-400 font-medium">Menghapus layanan dari edaran bisnis</li>
                    @else
                        <li>HANYA LIHAT (Read-Only)</li>
                        <li>Digunakan murni sebagai fungsi Katalog saat membuat penawaran atau tagihan masuk.</li>
                        <li class="text-orange-600 dark:text-orange-400 text-xs mt-2">Izin Ditolak: Merubah Nilai Master Harga Produk</li>
                    @endif
                </ul>
            </flux:card>
            @endif

            <!-- MODUL INVOICES -->
            @if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
            <flux:card>
                <div class="flex items-center gap-2 mb-2">
                    <flux:icon.document-text variant="outline" class="size-5 text-zinc-500" />
                    <flux:heading>Penagihan (Invoices)</flux:heading>
                </div>
                <ul class="list-disc pl-5 text-sm text-zinc-600 dark:text-zinc-400 space-y-1">
                    @if (auth()->user()->isSuperAdmin())
                        <li>Akses ke seluruh rekapan piutang dan pemasukan utama</li>
                        <li>Membuat, Merubah, Mengirim status Tagihan</li>
                        <li>*Soft-Delete* Tagihan</li>
                        <li class="text-red-600 dark:text-red-400 font-medium">*Force Delete* Rekam Tagihan</li>
                    @else
                        <li>Fokus penuh pada operasional tagihan jatuh tempo dan yang dikirim.</li>
                        <li>Membuat, Merubah, Mengirim status Tagihan</li>
                        <li>*Soft-Delete* Tagihan (ke tempat Sampah)</li>
                        <li class="text-orange-600 dark:text-orange-400 text-xs mt-2">Izin Ditolak: Force-Delete Rekam Penagihan Permanen</li>
                    @endif
                </ul>
            </flux:card>
            @endif

            <!-- MODUL SERVICES -->
            <flux:card>
                <div class="flex items-center gap-2 mb-2">
                    <flux:icon.server-stack variant="outline" class="size-5 text-zinc-500" />
                    <flux:heading>Infrastruktur Layanan (Services)</flux:heading>
                </div>
                <ul class="list-disc pl-5 text-sm text-zinc-600 dark:text-zinc-400 space-y-1">
                    @if (auth()->user()->isSuperAdmin())
                        <li>Akses kontrol manajemen utuh (CRUD) seluruh siklus hidup layanan dan tenggat waktunya.</li>
                    @elseif (auth()->user()->isServerManager())
                        <li>Akses visibilitas kapan sebuah Cpanel/Domain/Hosting Klien perlu diperpanjang.</li>
                        <li>Akses melakukan _Update_ status (e.g., Active -> Suspended jika batas waktu berlalu).</li>
                        <li class="text-orange-600 dark:text-orange-400 text-xs mt-2">Izin Ditolak: Membuat entitas layanan baru atau menghapus permanen</li>
                    @else
                        <li>Akses hanya Lihat (_Read-Only_) kapan siklus pelayanan seorang klien berakhir.</li>
                        <li class="text-orange-600 dark:text-orange-400 text-xs mt-2">Izin Ditolak: Tambah/Edit/Hapus Data Teknis Layanan</li>
                    @endif
                </ul>
            </flux:card>

        </div>

    </div>
</x-layouts::app>
