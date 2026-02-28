<div>
    <flux:modal name="client-service-form-modal" class="md:w-[42rem]">
        <div class="mb-6">
            <flux:heading size="lg">{{ $mode === 'create' ? 'Tambah Layanan' : 'Edit Layanan' }}</flux:heading>
            <flux:subheading>Catat layanan domain, server, atau hosting yang diberikan kepada klien.</flux:subheading>
        </div>

        <form wire:submit="save" class="space-y-4">
            <flux:select wire:model="form.client_id" label="Klien *" placeholder="Pilih klien...">
                @foreach ($clients as $client)
                    <flux:select.option value="{{ $client->id }}">
                        {{ $client->name }}{{ $client->company_name ? ' — ' . $client->company_name : '' }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model="form.product_id" label="Produk / Layanan *" placeholder="Pilih produk...">
                @foreach ($products as $product)
                    <flux:select.option value="{{ $product->id }}">{{ $product->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input wire:model="form.domain_name" label="Nama Domain"
                placeholder="contoh: namadomain.com (opsional)" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:radio.group wire:model="form.status" label="Status" variant="segmented">
                    <flux:radio value="Active" label="Aktif" />
                    <flux:radio value="Pending" label="Pending" />
                    <flux:radio value="Suspended" label="Ditangguhkan" />
                    <flux:radio value="Expired" label="Kadaluarsa" />
                </flux:radio.group>

                <flux:radio.group wire:model="form.billing_cycle" label="Siklus Tagihan" variant="segmented">
                    <flux:radio value="Monthly" label="Bulanan" />
                    <flux:radio value="Yearly" label="Tahunan" />
                </flux:radio.group>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input type="date" wire:model="form.started_at" label="Tanggal Mulai *" />
                <flux:input type="date" wire:model="form.expires_at" label="Tanggal Berakhir *" />
            </div>

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