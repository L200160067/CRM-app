<div>
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <flux:heading size="xl" level="1">Manajemen Pengguna</flux:heading>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <flux:button x-on:click="$flux.modal('user-create-modal').show()" variant="primary" icon="user-plus">
                Tambah Pengguna
            </flux:button>
        </div>
    </div>

    <!-- Alert Messages (Livewire Dispatched) -->
    @if (session()->has('message'))
        <div
            class="mb-4 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-medium px-4 py-3 flex items-center gap-2">
            <flux:icon.check-circle class="size-5" />
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div
            class="mb-4 rounded-lg bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-medium px-4 py-3 flex items-center gap-2">
            <flux:icon.x-circle class="size-5" />
            {{ session('error') }}
        </div>
    @endif

    <div class="overflow-x-auto -mx-3 px-3 sm:mx-0 sm:px-0">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Nama Pengguna</flux:table.column>
                <flux:table.column>Email</flux:table.column>
                <flux:table.column>Role</flux:table.column>
                <flux:table.column>Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($users as $user)
                    <flux:table.row wire:key="user-{{ $user->id }}">
                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                <flux:avatar
                                    src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=7F9CF5&background=EBF4FF"
                                    size="sm" />
                                <div class="font-medium">{{ $user->name }}</div>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="text-sm">{{ $user->email }}</div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="zinc">{{ $user->role->label() ?? 'Unknown' }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />
                                <flux:menu>
                                    <flux:menu.item wire:click="edit({{ $user->id }})" icon="pencil-square">Edit
                                    </flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item wire:click="confirmDelete({{ $user->id }})" icon="trash"
                                        variant="danger">
                                        Hapus</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4" class="p-0">
                            <div class="flex flex-col items-center justify-center py-20 whitespace-normal">
                                <div
                                    class="p-4 rounded-full bg-zinc-100 dark:bg-zinc-800/50 outline outline-1 outline-zinc-200 dark:outline-zinc-700/50 mb-5 text-zinc-400 dark:text-zinc-500">
                                    <flux:icon name="users" class="size-8" />
                                </div>
                                <flux:heading size="lg" class="mb-2 dark:text-zinc-200">Belum ada Pengguna</flux:heading>
                                <flux:text class="max-w-sm mb-6 text-center text-zinc-500 dark:text-zinc-400">Silakan
                                    tambahkan
                                    pengguna baru.</flux:text>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>

    <!-- Create / Edit User Modal -->
    <flux:modal name="user-create-modal" class="w-full md:w-[32rem]" wire:model="showCreateModal">
        <form wire:submit="save">
            <div class="mb-6">
                <flux:heading size="lg">{{ $editingUserId ? 'Edit Pengguna' : 'Tambah Pengguna Baru' }}</flux:heading>
                <flux:subheading>
                    {{ $editingUserId ? 'Perbarui informasi akun pengguna ini.' : 'Daftarkan akun staf atau Admin melalui panel ini.' }}
                </flux:subheading>
            </div>

            <div class="space-y-6">
                <flux:input wire:model="name" label="Nama Lengkap" placeholder="Masukkan nama" required />

                <flux:input wire:model="email" type="email" label="Alamat Email" placeholder="email@contoh.com"
                    required />

                <flux:select wire:model="role" label="Role / Peran" placeholder="Pilih hak akses..." required>
                    @foreach($roles as $roleOption)
                        <flux:select.option value="{{ $roleOption->value }}">{{ $roleOption->label() }}</flux:select.option>
                    @endforeach
                </flux:select>

                <div class="space-y-4">
                    <flux:input wire:model="password" type="password" label="Kata Sandi" :required="!$editingUserId"
                        viewable />
                    @if($editingUserId)
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Kosongkan jika Anda tidak ingin mengubah
                            kata sandi saat ini.</p>
                    @endif
                </div>

                <flux:input wire:model="password_confirmation" type="password" label="Konfirmasi Kata Sandi"
                    :required="!$editingUserId" viewable />
            </div>

            <div class="flex justify-end pt-4 mt-6 space-x-2 border-t border-zinc-200 dark:border-zinc-700">
                <flux:modal.close>
                    <flux:button variant="ghost" wire:click="$set('editingUserId', null)">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Simpan Pengguna</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Delete Confirmation Modal -->
    <flux:modal name="user-delete-modal" class="w-full md:min-w-[22rem]" wire:model="deletingUserId">
        <form wire:submit="delete">
            <div class="mb-6">
                <flux:heading size="lg">Hapus Pengguna</flux:heading>
                <flux:subheading>Apakah Anda yakin ingin menghapus pengguna ini secara permanen?</flux:subheading>
            </div>

            <div class="flex gap-2 mt-4">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="danger">Hapus</flux:button>
            </div>
        </form>
    </flux:modal>
</div>