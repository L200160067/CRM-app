<div>
    <flux:modal name="product-form-modal" class="md:w-[40rem]">
        <div class="mb-6">
            <flux:heading size="lg">{{ $mode === 'create' ? 'Tambah Produk' : 'Edit Produk' }}</flux:heading>
            <flux:subheading>Masukan detail terkait jasa/produk yang Anda sediakan.</flux:subheading>
        </div>

        <form wire:submit="save" class="space-y-4">
            <flux:input wire:model="form.name" label="Nama Produk / Layanan *" placeholder="Misal: Jasa Setup Sever" />
            
            <flux:input wire:model="form.default_price" label="Harga Dasar (Rp) *" type="number" step="1000" placeholder="0" />

            <flux:textarea wire:model="form.description" label="Deskripsi (Opsional)" placeholder="Penjelasan singkat mengenai apa yang Anda cover terkait produk ini." />

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
