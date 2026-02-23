<div>
    <flux:modal name="client-form-modal" class="md:w-[40rem]">
    <div class="mb-6">
        <flux:heading size="lg">{{ $mode === 'create' ? 'Tambah Klien' : 'Edit Klien' }}</flux:heading>
        <flux:subheading>Pastikan data yang dimasukkan akurat untuk keperluan invoice.</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-4">
        <flux:input wire:model="form.name" label="Nama Klien / PIC *" placeholder="John Doe" />
        <flux:input wire:model="form.company_name" label="Nama Perusahaan" placeholder="PT M-One Solution" />
        
        <div class="grid grid-cols-2 gap-4">
            <flux:input wire:model="form.email" label="Email" type="email" />
            <flux:input wire:model="form.phone" label="Nomor Telepon" />
        </div>

        <flux:input wire:model="form.city" label="Kota" />
        <flux:textarea wire:model="form.address" label="Alamat Lengkap" />

        <div class="flex justify-end space-x-2 pt-4">
            <flux:modal.close>
                <flux:button variant="ghost">Batal</flux:button>
            </flux:modal.close>
            <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">Simpan Data</span>
                <span wire:loading wire:target="save">Menyimpan...</span>
            </flux:button>
        </div>
    </form>
    </flux:modal>
</div>
