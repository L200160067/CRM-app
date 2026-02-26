<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <flux:heading size="xl" level="1">Manajemen Klien</flux:heading>
        </div>
        <div class="flex items-center space-x-4">
            <flux:radio.group wire:model.live="status" variant="segmented" class="max-w-fit">
                <flux:radio value="active" label="Aktif" />
                <flux:radio value="trashed" label="Sampah" />
            </flux:radio.group>

            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Cari klien..."
                class="w-64" clearable />

            @if($status === 'active')
                <flux:button x-on:click="$flux.modal('client-import-modal').show()" icon="arrow-up-tray">Import CSV
                </flux:button>
                <flux:button wire:click="$dispatchTo('client.form', 'load-client-form')"
                    x-on:click="$flux.modal('client-form-modal').show()" variant="primary" icon="user-plus">Tambah Klien
                </flux:button>
            @endif
        </div>
    </div>

    <div class="mb-4">
        <flux:pagination :paginator="$this->clients" />
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Nama / Perusahaan</flux:table.column>
            <flux:table.column>Kontak</flux:table.column>
            <flux:table.column>Aksi</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->clients as $client)
                <flux:table.row wire:key="client-{{ $client->id }}">
                    <flux:table.cell>
                        <div class="font-medium">{{ $client->name }}</div>
                        <div class="text-sm text-zinc-500">{{ $client->company_name ?? '-' }}</div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="text-sm">{{ $client->email ?? '-' }}</div>
                        <div class="text-sm text-zinc-500">{{ $client->phone ?? '-' }}</div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:dropdown position="bottom" align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />
                            <flux:menu>
                                @if($status === 'active')
                                    <flux:menu.item icon="eye"
                                        wire:click="$dispatchTo('client.show', 'load-client-show', { id: {{ $client->id }} })"
                                        x-on:click="$flux.modal('client-show-modal').show()">Lihat</flux:menu.item>
                                    <flux:menu.item icon="pencil-square"
                                        wire:click="$dispatchTo('client.form', 'load-client-form', { id: {{ $client->id }} })"
                                        x-on:click="$flux.modal('client-form-modal').show()">Edit</flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $client->id }})"
                                        x-on:click="$flux.modal('client-delete-modal').show()">Hapus</flux:menu.item>
                                @else
                                    <flux:menu.item icon="arrow-path" wire:click="restore({{ $client->id }})">Pulihkan
                                    </flux:menu.item>
                                    <flux:menu.item icon="trash" variant="danger"
                                        wire:click="confirmForceDelete({{ $client->id }})"
                                        x-on:click="$flux.modal('client-force-delete-modal').show()">Hapus Permanen
                                    </flux:menu.item>
                                @endif
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="3" class="p-0">
                        <div class="flex flex-col items-center justify-center py-20 whitespace-normal">
                            @if($status === 'active')
                                <div
                                    class="p-4 rounded-full bg-zinc-100 dark:bg-zinc-800/50 outline outline-1 outline-zinc-200 dark:outline-zinc-700/50 mb-5 text-zinc-400 dark:text-zinc-500">
                                    <flux:icon name="users" class="size-8" />
                                </div>
                                <flux:heading size="lg" class="mb-2 dark:text-zinc-200">Belum ada Klien</flux:heading>
                                <flux:text class="text-zinc-500 dark:text-zinc-400 text-center mb-6 max-w-sm">Anda belum
                                    menambahkan data klien satupun. Tambahkan klien pertama Anda untuk mulai mengatur data
                                    penagihan.</flux:text>
                                <flux:button wire:click="$dispatchTo('client.form', 'load-client-form')"
                                    x-on:click="$flux.modal('client-form-modal').show()" variant="primary" icon="user-plus"
                                    class="shadow-sm">Tambah Klien</flux:button>
                            @else
                                <div
                                    class="p-4 rounded-full bg-zinc-100 dark:bg-zinc-800/50 outline outline-1 outline-zinc-200 dark:outline-zinc-700/50 mb-5 text-zinc-400 dark:text-zinc-500">
                                    <flux:icon name="archive-box" class="size-8" />
                                </div>
                                <flux:heading size="lg" class="mb-2 dark:text-zinc-200">Tong Sampah Kosong</flux:heading>
                                <flux:text class="text-zinc-500 dark:text-zinc-400 text-center max-w-sm">Data klien yang Anda
                                    hapus sementara (Soft Delete) akan disembunyikan dengan aman di sini sebelum kelak
                                    diputuskan untuk dihapus permanen atau dipulihkan.</flux:text>
                            @endif
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <livewire:client.form />
    <livewire:client.show />
    <livewire:client.import />

    <!-- Delete Confirmation Modal -->
    <flux:modal name="client-delete-modal" class="md:w-[32rem]">
        <div class="mb-6">
            <flux:heading size="lg">Konfirmasi Hapus</flux:heading>
            <flux:subheading>Apakah Anda yakin ingin menghapus data klien ini? Klien akan dipindahkan ke Sampah.
            </flux:subheading>
        </div>

        <div class="flex justify-end space-x-2 pt-4">
            <flux:modal.close>
                <flux:button variant="ghost">Batal</flux:button>
            </flux:modal.close>
            <flux:button wire:click="delete" variant="danger" wire:loading.attr="disabled" wire:target="delete">
                <span wire:loading.remove wire:target="delete">Ya, Hapus Klien</span>
                <span wire:loading wire:target="delete">Memproses...</span>
            </flux:button>
        </div>
    </flux:modal>

    <!-- Force Delete Confirmation Modal -->
    <flux:modal name="client-force-delete-modal" class="md:w-[32rem]">
        <div class="mb-6">
            <flux:heading size="lg">Hapus Permanen</flux:heading>
            <flux:subheading>Tindakan ini tidak dapat dibatalkan. Klien beserta seluruh data terkait akan dihapus secara
                permanen dari database.</flux:subheading>
        </div>

        <div class="flex justify-end space-x-2 pt-4">
            <flux:modal.close>
                <flux:button variant="ghost">Batal</flux:button>
            </flux:modal.close>
            <flux:button wire:click="forceDelete" variant="danger" wire:loading.attr="disabled"
                wire:target="forceDelete">
                <span wire:loading.remove wire:target="forceDelete">Ya, Hapus Permanen</span>
                <span wire:loading wire:target="forceDelete">Memproses...</span>
            </flux:button>
        </div>
    </flux:modal>
</div>