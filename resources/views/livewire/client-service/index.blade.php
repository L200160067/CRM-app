<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <flux:heading size="xl" level="1">Manajemen Layanan</flux:heading>
        </div>
        <div class="flex items-center space-x-4">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                placeholder="Cari layanan, domain, atau klien..." class="w-72" clearable />

            <flux:button wire:click="$dispatchTo('client-service.form', 'load-client-service-form')"
                x-on:click="$flux.modal('client-service-form-modal').show()" variant="primary" icon="plus">
                Tambah Layanan
            </flux:button>
        </div>
    </div>

    <div class="mb-4">
        <flux:pagination :paginator="$this->services" />
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Klien</flux:table.column>
            <flux:table.column>Produk / Domain</flux:table.column>
            <flux:table.column>Siklus Tagihan</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Tanggal Berakhir</flux:table.column>
            <flux:table.column>Aksi</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->services as $service)
                <flux:table.row wire:key="service-{{ $service->id }}">
                    <flux:table.cell>
                        <div class="font-medium">{{ $service->client->name }}</div>
                        <div class="text-sm text-zinc-500">{{ $service->client->company_name ?? '-' }}</div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="font-medium">{{ $service->product->name }}</div>
                        @if ($service->domain_name)
                            <div class="text-sm text-zinc-500">{{ $service->domain_name }}</div>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="text-sm">{{ $service->billing_cycle === 'Monthly' ? 'Bulanan' : 'Tahunan' }}</div>
                    </flux:table.cell>

                    <flux:table.cell>
                        @php
                            $color = match ($service->status) {
                                'Active' => 'green',
                                'Suspended' => 'red',
                                'Expired' => 'zinc',
                                'Pending' => 'yellow',
                                default => 'zinc',
                            };
                            $label = match ($service->status) {
                                'Active' => 'Aktif',
                                'Suspended' => 'Ditangguhkan',
                                'Expired' => 'Kadaluarsa',
                                'Pending' => 'Pending',
                                default => $service->status,
                            };
                        @endphp
                        <flux:badge :color="$color" size="sm" inset="top bottom">{{ $label }}</flux:badge>
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="text-sm">{{ $service->expires_at->format('d M Y') }}</div>
                        @if ($service->expires_at->isPast())
                            <div class="text-xs text-red-500 font-medium">Sudah kadaluarsa</div>
                        @elseif ($service->expires_at->diffInDays(now()) <= 14)
                            <div class="text-xs text-yellow-500 font-medium">Segera berakhir</div>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:dropdown position="bottom" align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />
                            <flux:menu>
                                <flux:menu.item icon="pencil-square"
                                    wire:click="$dispatchTo('client-service.form', 'load-client-service-form', { id: {{ $service->id }} })"
                                    x-on:click="$flux.modal('client-service-form-modal').show()">Edit</flux:menu.item>
                                <flux:menu.separator />
                                <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $service->id }})"
                                    x-on:click="$flux.modal('client-service-delete-modal').show()">Hapus</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="p-0">
                        <div class="flex flex-col items-center justify-center py-20 whitespace-normal">
                            <div
                                class="p-4 rounded-full bg-zinc-100 dark:bg-zinc-800/50 outline outline-1 outline-zinc-200 dark:outline-zinc-700/50 mb-5 text-zinc-400 dark:text-zinc-500">
                                <flux:icon name="server-stack" class="size-8" />
                            </div>
                            <flux:heading size="lg" class="mb-2 dark:text-zinc-200">Belum ada Layanan</flux:heading>
                            <flux:text class="text-zinc-500 dark:text-zinc-400 text-center mb-6 max-w-sm">Anda belum
                                mencatat layanan apapun. Mulai catat domain, server, atau hosting yang Anda kelola untuk
                                klien.</flux:text>
                            <flux:button wire:click="$dispatchTo('client-service.form', 'load-client-service-form')"
                                x-on:click="$flux.modal('client-service-form-modal').show()" variant="primary" icon="plus"
                                class="shadow-sm">Tambah Layanan</flux:button>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <livewire:client-service.form />

    <!-- Modal Konfirmasi Hapus -->
    <flux:modal name="client-service-delete-modal" class="md:w-[32rem]">
        <div class="mb-6">
            <flux:heading size="lg">Konfirmasi Hapus</flux:heading>
            <flux:subheading>Apakah Anda yakin ingin menghapus data layanan ini? Tindakan ini tidak dapat dibatalkan.
            </flux:subheading>
        </div>

        <div class="flex justify-end space-x-2 pt-4">
            <flux:modal.close>
                <flux:button variant="ghost">Batal</flux:button>
            </flux:modal.close>
            <flux:button wire:click="delete" variant="danger" wire:loading.attr="disabled" wire:target="delete">
                <span wire:loading.remove wire:target="delete">Ya, Hapus Layanan</span>
                <span wire:loading wire:target="delete">Memproses...</span>
            </flux:button>
        </div>
    </flux:modal>
</div>