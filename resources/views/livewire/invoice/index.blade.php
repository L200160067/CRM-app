<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <flux:heading size="xl" level="1">Manajemen Invoice</flux:heading>
        </div>
        <div class="flex items-center space-x-4">
            <flux:select wire:model.live="status" class="w-40">
                <option value="all">Semua</option>
                <option value="draft">Draft</option>
                <option value="sent">Terkirim</option>
                <option value="paid">Lunas</option>
                <option value="overdue">Jatuh Tempo</option>
                <option value="canceled">Dibatalkan</option>
                <option value="trashed">Sampah</option>
            </flux:select>
            
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Cari nomor/klien..." class="w-64" clearable />

            @if($status !== 'trashed')
                <flux:button wire:click="$dispatchTo('invoice.form', 'load-invoice-form')" x-on:click="$flux.modal('invoice-form-modal').show()" variant="primary" icon="document-plus">Buat Invoice</flux:button>
            @endif
        </div>
    </div>

    <div class="mb-4">
        <flux:pagination :paginator="$this->invoices" />
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Invoice / Tanggal</flux:table.column>
            <flux:table.column>Klien</flux:table.column>
            <flux:table.column>Pembuat</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Total Tagihan</flux:table.column>
            <flux:table.column>Aksi</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->invoices as $invoice)
                <flux:table.row wire:key="invoice-{{ $invoice->id }}">
                    <flux:table.cell>
                        <div class="font-bold text-indigo-600 dark:text-indigo-400">{{ $invoice->invoice_number }}</div>
                        <div class="text-xs text-zinc-500">Terbit: {{ $invoice->issue_date->format('d M Y') }}</div>
                        <div class="text-xs text-zinc-500">Tempo: {{ $invoice->due_date->format('d M Y') }}</div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="font-medium">{{ $invoice->client->name }}</div>
                        <div class="text-sm text-zinc-500">{{ $invoice->client->company_name ?? '-' }}</div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $invoice->creator->name ?? 'System' }}</div>
                    </flux:table.cell>
                    <flux:table.cell>
                        @php
                            $color = match($invoice->status->value ?? $invoice->status) {
                                'draft' => 'zinc',
                                'sent' => 'blue',
                                'paid' => 'green',
                                'overdue' => 'red',
                                'canceled' => 'stone',
                                default => 'zinc',
                            };
                            $label = match($invoice->status->value ?? $invoice->status) {
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
                        <div class="text-sm font-semibold">Rp {{ number_format($invoice->grand_total, 2, ',', '.') }}</div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:dropdown position="bottom" align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />
                            <flux:menu>
                                @if($status !== 'trashed')
                                    <flux:menu.item icon="eye" wire:click="$dispatchTo('invoice.show', 'load-invoice-show', { id: {{ $invoice->id }} })" x-on:click="$flux.modal('invoice-show-modal').show()">Lihat Detail</flux:menu.item>
                                    @if(!in_array($invoice->status->value ?? $invoice->status, ['paid', 'canceled']))
                                        <flux:menu.item icon="pencil-square" wire:click="$dispatchTo('invoice.form', 'load-invoice-form', { id: {{ $invoice->id }} })" x-on:click="$flux.modal('invoice-form-modal').show()">Edit</flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $invoice->id }})" x-on:click="$flux.modal('invoice-delete-modal').show()">Hapus</flux:menu.item>
                                    @endif
                                @else
                                    <flux:menu.item icon="arrow-path" wire:click="restore({{ $invoice->id }})">Pulihkan</flux:menu.item>
                                    <flux:menu.item icon="trash" variant="danger" wire:click="confirmForceDelete({{ $invoice->id }})" x-on:click="$flux.modal('invoice-force-delete-modal').show()">Hapus Permanen</flux:menu.item>
                                @endif
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="p-0">
                        <div class="flex flex-col items-center justify-center py-20 whitespace-normal">
                            @if($status !== 'trashed')
                                <div class="p-4 rounded-full bg-zinc-100 dark:bg-zinc-800/50 outline outline-1 outline-zinc-200 dark:outline-zinc-700/50 mb-5 text-zinc-400 dark:text-zinc-500">
                                    <flux:icon name="document-text" class="size-8" />
                                </div>
                                <flux:heading size="lg" class="mb-2 dark:text-zinc-200">Belum ada Invoice</flux:heading>
                                <flux:text class="text-zinc-500 dark:text-zinc-400 text-center mb-6 max-w-sm">Data tagihan tidak ditemukan. Buat invoice baru untuk menagih klien Anda.</flux:text>
                                <flux:button wire:click="$dispatchTo('invoice.form', 'load-invoice-form')" x-on:click="$flux.modal('invoice-form-modal').show()" variant="primary" icon="document-plus" class="shadow-sm">Buat Invoice</flux:button>
                            @else
                                <div class="p-4 rounded-full bg-zinc-100 dark:bg-zinc-800/50 outline outline-1 outline-zinc-200 dark:outline-zinc-700/50 mb-5 text-zinc-400 dark:text-zinc-500">
                                    <flux:icon name="archive-box" class="size-8" />
                                </div>
                                <flux:heading size="lg" class="mb-2 dark:text-zinc-200">Tong Sampah Kosong</flux:heading>
                                <flux:text class="text-zinc-500 dark:text-zinc-400 text-center max-w-sm">Data invoice yang dibuang akan disembunyikan di sini.</flux:text>
                            @endif
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <livewire:invoice.form />
    <livewire:invoice.show />

    <!-- Delete Confirmation Modal -->
    <flux:modal name="invoice-delete-modal" class="md:w-[32rem]">
        <div class="mb-6">
            <flux:heading size="lg">Konfirmasi Hapus</flux:heading>
            <flux:subheading>Invoice akan dipindahkan ke Sampah. Dokumen yang Lunas atau Dibatalkan tidak akan bisa dihapus.</flux:subheading>
        </div>

        <div class="flex justify-end space-x-2 pt-4">
            <flux:modal.close>
                <flux:button variant="ghost">Batal</flux:button>
            </flux:modal.close>
            <flux:button wire:click="delete" variant="danger" wire:loading.attr="disabled" wire:target="delete">
                <span wire:loading.remove wire:target="delete">Ya, Hapus Tagihan</span>
                <span wire:loading wire:target="delete">Memproses...</span>
            </flux:button>
        </div>
    </flux:modal>

    <!-- Force Delete Confirmation Modal -->
    <flux:modal name="invoice-force-delete-modal" class="md:w-[32rem]">
        <div class="mb-6">
            <flux:heading size="lg">Hapus Permanen</flux:heading>
            <flux:subheading>Tindakan ini tidak dapat dibatalkan. Riwayat invoice ini akan menghilang total.</flux:subheading>
        </div>

        <div class="flex justify-end space-x-2 pt-4">
            <flux:modal.close>
                <flux:button variant="ghost">Batal</flux:button>
            </flux:modal.close>
            <flux:button wire:click="forceDelete" variant="danger" wire:loading.attr="disabled" wire:target="forceDelete">
                <span wire:loading.remove wire:target="forceDelete">Ya, Hapus Permanen</span>
                <span wire:loading wire:target="forceDelete">Memproses...</span>
            </flux:button>
        </div>
    </flux:modal>
</div>
