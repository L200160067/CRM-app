<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl" level="1">Manajemen Pengguna</flux:heading>
        </div>
        <div class="flex items-center space-x-4">
            <flux:button x-on:click="$flux.modal('user-create-modal').show()" variant="primary" icon="user-plus">
                Tambah Pengguna
            </flux:button>
        </div>
    </div>

    <!-- Alert Messages (Livewire Dispatched) -->
    @if (session()->has('message'))
        <flux:toast variant="success" icon="check-circle" dismissible class="mb-4">
            {{ session('message') }}
        </flux:toast>
    @endif

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Nama Pengguna</flux:table.column>
            <flux:table.column>Email</flux:table.column>
            <flux:table.column>Role</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($users as $user)
                <flux:table.row wire:key="user-{{ $user->id }}">
                    <flux:table.cell>
                        <div class="flex items-center gap-3">
                            <flux:avatar src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=7F9CF5&background=EBF4FF" size="sm" />
                            <div class="font-medium">{{ $user->name }}</div>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="text-sm">{{ $user->email }}</div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" color="zinc">{{ $user->role->label() ?? 'Unknown' }}</flux:badge>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="3" class="p-0">
                        <div class="flex flex-col items-center justify-center py-20 whitespace-normal">
                             <div class="p-4 rounded-full bg-zinc-100 dark:bg-zinc-800/50 outline outline-1 outline-zinc-200 dark:outline-zinc-700/50 mb-5 text-zinc-400 dark:text-zinc-500">
                                <flux:icon name="users" class="size-8" />
                            </div>
                            <flux:heading size="lg" class="mb-2 dark:text-zinc-200">Belum ada Pengguna</flux:heading>
                            <flux:text class="max-w-sm mb-6 text-center text-zinc-500 dark:text-zinc-400">Silakan tambahkan pengguna baru.</flux:text>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <div class="mt-4">
        {{ $users->links() }}
    </div>

    <!-- Create User Modal -->
    <flux:modal name="user-create-modal" class="md:w-[32rem]">
        <form wire:submit="save">
            <div class="mb-6">
                <flux:heading size="lg">Tambah Pengguna Baru</flux:heading>
                <flux:subheading>Daftarkan akun staf atau Admin melalui panel ini.</flux:subheading>
            </div>

            <div class="space-y-6">
                <flux:input wire:model="name" label="Nama Lengkap" placeholder="Masukkan nama" required />
                
                <flux:input wire:model="email" type="email" label="Alamat Email" placeholder="email@contoh.com" required />
                
                <flux:select wire:model="role" label="Role / Peran" placeholder="Pilih hak akses..." required>
                    @foreach($roles as $roleOption)
                        <flux:select.option value="{{ $roleOption->value }}">{{ $roleOption->label() }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="password" type="password" label="Kata Sandi" required viewable />
                <flux:input wire:model="password_confirmation" type="password" label="Konfirmasi Kata Sandi" required viewable />
            </div>

            <div class="flex justify-end pt-4 mt-6 space-x-2 border-t border-zinc-200 dark:border-zinc-700">
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Simpan Pengguna</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
