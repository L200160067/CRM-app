<div>
    <div class="mb-6">
        <flux:heading size="xl" level="1">Dashboard</flux:heading>
        <flux:text class="text-zinc-500 dark:text-zinc-400 mt-1">
            @if (auth()->user()->isSuperAdmin())
                Ringkasan kinerja bisnis Anda
            @elseif (auth()->user()->isAdmin())
                Ringkasan operasional harian Anda
            @elseif (auth()->user()->isMarketing())
                Selamat datang, Tim Marketing
            @else
                Ringkasan layanan aktif Anda
            @endif
        </flux:text>
    </div>

    {{-- =============================================
    SUPER ADMIN: Full financial dashboard
    ============================================= --}}
    @if (auth()->user()->isSuperAdmin())

        {{-- Stat Cards --}}
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4 mb-6">
            {{-- Total Pendapatan --}}
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5">
                <div class="flex items-center justify-between mb-3">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Pendapatan</flux:text>
                    <div class="p-2 rounded-lg bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400">
                        <flux:icon name="banknotes" class="size-5" />
                    </div>
                </div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                    Rp {{ number_format($this->totalRevenue, 0, ',', '.') }}
                </div>
                <flux:text class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">dari semua invoice lunas</flux:text>
            </div>

            {{-- Outstanding --}}
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5">
                <div class="flex items-center justify-between mb-3">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Piutang Beredar</flux:text>
                    <div class="p-2 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                        <flux:icon name="clock" class="size-5" />
                    </div>
                </div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                    Rp {{ number_format($this->outstanding, 0, ',', '.') }}
                </div>
                <flux:text class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">invoice terkirim & jatuh tempo</flux:text>
            </div>

            {{-- Overdue --}}
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5">
                <div class="flex items-center justify-between mb-3">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Jatuh Tempo</flux:text>
                    <div class="p-2 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                        <flux:icon name="exclamation-triangle" class="size-5" />
                    </div>
                </div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                    {{ $this->overdueCount }}
                </div>
                <flux:text class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">
                    invoice belum dibayar & melewati tempo
                </flux:text>
            </div>

            {{-- Klien Aktif --}}
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5">
                <div class="flex items-center justify-between mb-3">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Klien Aktif</flux:text>
                    <div class="p-2 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                        <flux:icon name="users" class="size-5" />
                    </div>
                </div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                    {{ $this->totalClients }}
                </div>
                <flux:text class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">
                    {{ $this->totalProducts }} produk terdaftar
                </flux:text>
            </div>
        </div>

        {{-- Bottom Row: Status Breakdown + Recent Invoices --}}
        <div class="grid gap-4 lg:grid-cols-5">

            {{-- Invoice per Status --}}
            <div class="lg:col-span-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5">
                <flux:heading size="base" class="mb-4">Invoice per Status</flux:heading>

                @php
                    $statuses = [
                        ['value' => 'draft', 'label' => 'Draft', 'color' => 'zinc'],
                        ['value' => 'sent', 'label' => 'Terkirim', 'color' => 'blue'],
                        ['value' => 'paid', 'label' => 'Lunas', 'color' => 'green'],
                        ['value' => 'overdue', 'label' => 'Jatuh Tempo', 'color' => 'red'],
                        ['value' => 'canceled', 'label' => 'Dibatalkan', 'color' => 'stone'],
                    ];
                @endphp

                <ul class="space-y-3">
                    @foreach ($statuses as $statusItem)
                        <li>
                            <a href="{{ route('invoices.index', ['status' => $statusItem['value']]) }}"
                                class="flex items-center justify-between group rounded-lg px-3 py-2 hover:bg-zinc-50 dark:hover:bg-zinc-800/60 transition-colors">
                                <div class="flex items-center gap-2">
                                    <flux:badge color="{{ $statusItem['color'] }}" size="sm">{{ $statusItem['label'] }}
                                    </flux:badge>
                                </div>
                                <span class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">
                                    {{ $this->invoicesByStatus[$statusItem['value']] ?? 0 }}
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Invoice Terbaru --}}
            <div class="lg:col-span-3 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5">
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="base">Invoice Terbaru</flux:heading>
                    <flux:button href="{{ route('invoices.index') }}" variant="ghost" size="sm" icon-trailing="arrow-right">
                        Lihat Semua
                    </flux:button>
                </div>

                @if ($this->recentInvoices->isEmpty())
                    <div class="flex flex-col items-center justify-center py-10 text-center">
                        <div class="p-3 rounded-full bg-zinc-100 dark:bg-zinc-800/50 mb-3 text-zinc-400 dark:text-zinc-500">
                            <flux:icon name="document-text" class="size-7" />
                        </div>
                        <flux:text class="text-zinc-400 dark:text-zinc-500">Belum ada invoice</flux:text>
                    </div>
                @else
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Invoice / Klien</flux:table.column>
                            <flux:table.column>Status</flux:table.column>
                            <flux:table.column>Total</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach ($this->recentInvoices as $invoice)
                                <flux:table.row wire:key="recent-{{ $invoice->id }}">
                                    <flux:table.cell>
                                        <div class="font-medium text-indigo-600 dark:text-indigo-400 text-sm">
                                            {{ $invoice->invoice_number }}
                                        </div>
                                        <div class="text-xs text-zinc-500">
                                            {{ $invoice->client?->name ?? '-' }}
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        @php
                                            $color = match ($invoice->status->value) {
                                                'draft' => 'zinc',
                                                'sent' => 'blue',
                                                'paid' => 'green',
                                                'overdue' => 'red',
                                                'canceled' => 'stone',
                                                default => 'zinc',
                                            };
                                            $label = match ($invoice->status->value) {
                                                'draft' => 'Draft',
                                                'sent' => 'Terkirim',
                                                'paid' => 'Lunas',
                                                'overdue' => 'Jatuh Tempo',
                                                'canceled' => 'Dibatalkan',
                                                default => 'Unknown',
                                            };
                                        @endphp
                                        <flux:badge color="{{ $color }}" size="sm">{{ $label }}</flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <div class="text-sm font-semibold">
                                            Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                @endif
            </div>
        </div>

    {{-- =============================================
    ADMIN: Operational dashboard
    ============================================= --}}
    @elseif (auth()->user()->isAdmin())

        {{-- Stat Cards --}}
        <div class="grid gap-4 md:grid-cols-3 mb-6">
            {{-- Draft Invoices --}}
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5">
                <div class="flex items-center justify-between mb-3">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Perlu Dikirim</flux:text>
                    <div class="p-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400">
                        <flux:icon name="document-text" class="size-5" />
                    </div>
                </div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                    {{ $this->draftInvoicesCount }} <span class="text-base font-normal text-zinc-500">Dokumen</span>
                </div>
                <flux:text class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">Status: Draft</flux:text>
            </div>

            {{-- Overdue Clients --}}
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5">
                <div class="flex items-center justify-between mb-3">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Perlu Tindak Lanjut</flux:text>
                    <div class="p-2 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                        <flux:icon name="phone" class="size-5" />
                    </div>
                </div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                    {{ $this->overdueClientsCount }} <span class="text-base font-normal text-zinc-500">Klien</span>
                </div>
                <flux:text class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">Status: Jatuh Tempo</flux:text>
            </div>

            {{-- Sent Invoices --}}
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5">
                <div class="flex items-center justify-between mb-3">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Menunggu Pembayaran</flux:text>
                    <div class="p-2 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                        <flux:icon name="clock" class="size-5" />
                    </div>
                </div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                    {{ $this->sentInvoicesCount }} <span class="text-base font-normal text-zinc-500">Dokumen</span>
                </div>
                <flux:text class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">Status: Terkirim</flux:text>
            </div>
        </div>

        {{-- Bottom Row: Status Breakdown + Recent Invoices --}}
        <div class="grid gap-4 lg:grid-cols-5">

            {{-- Invoice per Status --}}
            <div class="lg:col-span-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5">
                <flux:heading size="base" class="mb-4">Invoice per Status</flux:heading>

                @php
                    $statuses = [
                        ['value' => 'draft', 'label' => 'Draft', 'color' => 'zinc'],
                        ['value' => 'sent', 'label' => 'Terkirim', 'color' => 'blue'],
                        ['value' => 'paid', 'label' => 'Lunas', 'color' => 'green'],
                        ['value' => 'overdue', 'label' => 'Jatuh Tempo', 'color' => 'red'],
                        ['value' => 'canceled', 'label' => 'Dibatalkan', 'color' => 'stone'],
                    ];
                @endphp

                <ul class="space-y-3">
                    @foreach ($statuses as $statusItem)
                        <li>
                            <a href="{{ route('invoices.index', ['status' => $statusItem['value']]) }}"
                                class="flex items-center justify-between group rounded-lg px-3 py-2 hover:bg-zinc-50 dark:hover:bg-zinc-800/60 transition-colors">
                                <div class="flex items-center gap-2">
                                    <flux:badge color="{{ $statusItem['color'] }}" size="sm">{{ $statusItem['label'] }}
                                    </flux:badge>
                                </div>
                                <span class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">
                                    {{ $this->invoicesByStatus[$statusItem['value']] ?? 0 }}
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Invoice Terbaru --}}
            <div class="lg:col-span-3 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5">
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="base">Invoice Terbaru</flux:heading>
                    <flux:button href="{{ route('invoices.index') }}" variant="ghost" size="sm" icon-trailing="arrow-right">
                        Lihat Semua
                    </flux:button>
                </div>

                @if ($this->recentInvoices->isEmpty())
                    <div class="flex flex-col items-center justify-center py-10 text-center">
                        <div class="p-3 rounded-full bg-zinc-100 dark:bg-zinc-800/50 mb-3 text-zinc-400 dark:text-zinc-500">
                            <flux:icon name="document-text" class="size-7" />
                        </div>
                        <flux:text class="text-zinc-400 dark:text-zinc-500">Belum ada invoice</flux:text>
                    </div>
                @else
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Invoice / Klien</flux:table.column>
                            <flux:table.column>Status</flux:table.column>
                            <flux:table.column>Tenggat Waktu</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach ($this->recentInvoices as $invoice)
                                <flux:table.row wire:key="recent-{{ $invoice->id }}">
                                    <flux:table.cell>
                                        <div class="font-medium text-indigo-600 dark:text-indigo-400 text-sm">
                                            {{ $invoice->invoice_number }}
                                        </div>
                                        <div class="text-xs text-zinc-500">
                                            {{ $invoice->client?->name ?? '-' }}
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        @php
                                            $color = match ($invoice->status->value) {
                                                'draft' => 'zinc',
                                                'sent' => 'blue',
                                                'paid' => 'green',
                                                'overdue' => 'red',
                                                'canceled' => 'stone',
                                                default => 'zinc',
                                            };
                                            $label = match ($invoice->status->value) {
                                                'draft' => 'Draft',
                                                'sent' => 'Terkirim',
                                                'paid' => 'Lunas',
                                                'overdue' => 'Jatuh Tempo',
                                                'canceled' => 'Dibatalkan',
                                                default => 'Unknown',
                                            };
                                        @endphp
                                        <flux:badge color="{{ $color }}" size="sm">{{ $label }}</flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <div class="text-sm">
                                            {{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '-' }}
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                @endif
            </div>
        </div>

        {{-- =============================================
        MARKETING: Client-focused, no financial data
        ============================================= --}}
    @elseif (auth()->user()->isMarketing())

        {{-- Stat Cards --}}
        <div class="grid gap-4 md:grid-cols-2 mb-6">
            {{-- Target: Klien Baru Bulan Ini --}}
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5">
                <div class="flex items-center justify-between mb-3">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Klien Baru Bulan Ini</flux:text>
                    <div class="p-2 rounded-lg bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400">
                        <flux:icon name="user-plus" class="size-5" />
                    </div>
                </div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                    {{ $this->newClientsThisMonthCount }} <span class="text-base font-normal text-zinc-500">Klien</span>
                </div>
                <flux:text class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">Target Sales & Marketing</flux:text>
            </div>

            {{-- Total Klien --}}
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5">
                <div class="flex items-center justify-between mb-3">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Klien Terdaftar</flux:text>
                    <div class="p-2 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                        <flux:icon name="users" class="size-5" />
                    </div>
                </div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                    {{ $this->totalClients }}
                </div>
                <flux:text class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">Semua data klien di sistem</flux:text>
            </div>
        </div>

        {{-- Klien Terbaru --}}
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5">
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="base">Klien Terbaru</flux:heading>
                <flux:button href="{{ route('clients.index') }}" variant="ghost" size="sm" icon-trailing="arrow-right">
                    Lihat Semua
                </flux:button>
            </div>

            @if ($this->recentClients->isEmpty())
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <div class="p-3 rounded-full bg-zinc-100 dark:bg-zinc-800/50 mb-3 text-zinc-400 dark:text-zinc-500">
                        <flux:icon name="users" class="size-7" />
                    </div>
                    <flux:text class="text-zinc-400 dark:text-zinc-500">Belum ada klien</flux:text>
                </div>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Nama</flux:table.column>
                        <flux:table.column>Email</flux:table.column>
                        <flux:table.column>Telepon</flux:table.column>
                        <flux:table.column>Bergabung</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->recentClients as $client)
                            <flux:table.row wire:key="client-{{ $client->id }}">
                                <flux:table.cell>
                                    <div class="font-medium text-sm text-zinc-900 dark:text-zinc-100">
                                        {{ $client->name }}
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:text class="text-sm">{{ $client->email ?? '-' }}</flux:text>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:text class="text-sm">{{ $client->phone ?? '-' }}</flux:text>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:text class="text-sm text-zinc-500">
                                        {{ $client->created_at->format('d M Y') }}
                                    </flux:text>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif
        </div>

        {{-- =============================================
        SERVER MANAGER: Service-focused, no financial data
        ============================================= --}}
    @else

        {{-- Stat Cards (Hanya Layanan untuk Server Manager) --}}
        <div class="mb-6">

            {{-- Total Layanan Aktif --}}
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5">
                <div class="flex items-center justify-between mb-3">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Layanan</flux:text>
                    <div class="p-2 rounded-lg bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400">
                        <flux:icon name="server-stack" class="size-5" />
                    </div>
                </div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                    {{ $this->totalServices }}
                </div>
                <flux:text class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">layanan klien aktif</flux:text>
            </div>
        </div>

        {{-- Layanan Segera Expired --}}
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5">
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="base">Layanan Akan Expired (30 Hari)</flux:heading>
                <flux:button href="{{ route('client-services.index') }}" variant="ghost" size="sm"
                    icon-trailing="arrow-right">
                    Lihat Semua
                </flux:button>
            </div>

            @if ($this->expiringSoonServices->isEmpty())
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <div class="p-3 rounded-full bg-zinc-100 dark:bg-zinc-800/50 mb-3 text-zinc-400 dark:text-zinc-500">
                        <flux:icon name="server-stack" class="size-7" />
                    </div>
                    <flux:text class="text-zinc-400 dark:text-zinc-500">Tidak ada layanan yang akan expired dalam 30 hari
                    </flux:text>
                </div>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Klien</flux:table.column>
                        <flux:table.column>Produk / Domain</flux:table.column>
                        <flux:table.column>Expired</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->expiringSoonServices as $service)
                            <flux:table.row wire:key="service-{{ $service->id }}">
                                <flux:table.cell>
                                    <div class="font-medium text-sm text-zinc-900 dark:text-zinc-100">
                                        {{ $service->client?->name ?? '-' }}
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="text-sm">{{ $service->product?->name ?? '-' }}</div>
                                    @if ($service->domain_name)
                                        <div class="text-xs text-zinc-400">{{ $service->domain_name }}</div>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    @php
                                        $daysLeft = now()->diffInDays($service->expires_at, false);
                                        $urgencyColor = $daysLeft <= 7 ? 'red' : ($daysLeft <= 14 ? 'amber' : 'zinc');
                                    @endphp
                                    <flux:badge color="{{ $urgencyColor }}" size="sm">
                                        {{ $service->expires_at->format('d M Y') }}
                                    </flux:badge>
                                    <div class="text-xs text-zinc-400 mt-1">{{ $daysLeft }} hari lagi</div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif
        </div>

    @endif
</div>