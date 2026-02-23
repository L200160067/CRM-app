<div>
    <flux:modal name="client-show-modal" class="md:w-[40rem]">
    @if($client)
    <div class="mb-6">
        <flux:heading size="lg">Detail Klien</flux:heading>
        <flux:subheading>Informasi lengkap mengenai entitas klien ini.</flux:subheading>
    </div>

    <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <flux:heading size="sm" class="text-zinc-500">Nama / PIC</flux:heading>
                <flux:text class="font-medium">{{ $client->name }}</flux:text>
            </div>
            <div>
                <flux:heading size="sm" class="text-zinc-500">Perusahaan</flux:heading>
                <flux:text>{{ $client->company_name ?? '-' }}</flux:text>
            </div>
        </div>

        <flux:separator variant="subtle" />

        <div class="grid grid-cols-2 gap-4">
            <div>
                <flux:heading size="sm" class="text-zinc-500">Email</flux:heading>
                <flux:text>{{ $client->email ?? '-' }}</flux:text>
            </div>
            <div>
                <flux:heading size="sm" class="text-zinc-500">Nomor Telepon</flux:heading>
                <flux:text>{{ $client->phone ?? '-' }}</flux:text>
            </div>
        </div>

        <flux:separator variant="subtle" />

        <div>
            <flux:heading size="sm" class="text-zinc-500">Kota</flux:heading>
            <flux:text>{{ $client->city ?? '-' }}</flux:text>
        </div>

        <div>
            <flux:heading size="sm" class="text-zinc-500">Alamat Lengkap</flux:heading>
            <flux:text>{{ $client->address ?? '-' }}</flux:text>
        </div>
        
        <div class="grid grid-cols-2 gap-4 mt-2">
            <div>
                <flux:heading size="sm" class="text-zinc-500">Dibuat Pada</flux:heading>
                <flux:text class="text-sm">{{ $client->created_at->translatedFormat('d F Y, H:i') }}</flux:text>
            </div>
            <div>
                <flux:heading size="sm" class="text-zinc-500">Terakhir Diperbarui</flux:heading>
                <flux:text class="text-sm">{{ $client->updated_at->translatedFormat('d F Y, H:i') }}</flux:text>
            </div>
        </div>
    </div>

    <div class="flex justify-end pt-6">
        <flux:modal.close>
            <flux:button variant="ghost">Tutup</flux:button>
        </flux:modal.close>
    </div>
    @endif
    </flux:modal>
</div>
