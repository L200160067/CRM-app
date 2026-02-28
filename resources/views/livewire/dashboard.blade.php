<div>
    <div class="mb-6">
        <flux:heading size="xl" level="1">Dashboard</flux:heading>
        <flux:text class="text-zinc-500 dark:text-zinc-400 mt-1">Ringkasan kinerja bisnis Anda</flux:text>
    </div>

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
                {{ $this->overdueCount === 1 ? 'invoice' : 'invoice' }} belum dibayar & melewati tempo
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
                    ['value' => 'draft',    'label' => 'Draft',       'color' => 'zinc'],
                    ['value' => 'sent',     'label' => 'Terkirim',    'color' => 'blue'],
                    ['value' => 'paid',     'label' => 'Lunas',       'color' => 'green'],
                    ['value' => 'overdue',  'label' => 'Jatuh Tempo', 'color' => 'red'],
                    ['value' => 'canceled', 'label' => 'Dibatalkan',  'color' => 'stone'],
                ];
            @endphp

            <ul class="space-y-3">
                @foreach ($statuses as $statusItem)
                    <li>
                        <a href="{{ route('invoices.index', ['status' => $statusItem['value']]) }}"
                           class="flex items-center justify-between group rounded-lg px-3 py-2 hover:bg-zinc-50 dark:hover:bg-zinc-800/60 transition-colors">
                            <div class="flex items-center gap-2">
                                <flux:badge color="{{ $statusItem['color'] }}" size="sm">{{ $statusItem['label'] }}</flux:badge>
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
                                        $color = match($invoice->status->value) {
                                            'draft'    => 'zinc',
                                            'sent'     => 'blue',
                                            'paid'     => 'green',
                                            'overdue'  => 'red',
                                            'canceled' => 'stone',
                                            default    => 'zinc',
                                        };
                                        $label = match($invoice->status->value) {
                                            'draft'    => 'Draft',
                                            'sent'     => 'Terkirim',
                                            'paid'     => 'Lunas',
                                            'overdue'  => 'Jatuh Tempo',
                                            'canceled' => 'Dibatalkan',
                                            default    => 'Unknown',
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
</div>
