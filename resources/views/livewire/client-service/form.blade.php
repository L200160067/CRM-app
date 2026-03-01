<div>
    <flux:modal name="client-service-form-modal" class="md:w-[42rem]">
        <div class="mb-6">
            <flux:heading size="lg">{{ $mode === 'create' ? 'Tambah Layanan' : 'Edit Layanan' }}</flux:heading>
            <flux:subheading>Catat layanan domain, server, atau hosting yang diberikan kepada klien.</flux:subheading>
        </div>

        <form wire:submit="save" class="space-y-4">
            <flux:select wire:model="form.client_id" label="Klien *">
                <option value="" disabled selected>Pilih klien...</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}" wire:key="client-{{ $client->id }}">
                        {{ $client->name }}{{ $client->company_name ? ' — ' . $client->company_name : '' }}
                    </option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="form.product_id" label="Produk / Layanan *">
                <option value="" disabled selected>Pilih produk...</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" wire:key="product-{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </flux:select>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model="form.domain_name" label="Nama Domain"
                    placeholder="contoh: namadomain.com (opsional)" />
                    
                <flux:input type="number" step="1000" min="0" wire:model="form.recurring_price" label="Harga Langganan (Rp)" 
                    placeholder="Otomatis dari produk..." />
            </div>

            <flux:radio.group wire:model="form.status" label="Status" variant="segmented">
                @foreach(\App\Enums\ServiceStatus::cases() as $status)
                    <flux:radio value="{{ $status->value }}" label="{{ $status->label() }}" />
                @endforeach
            </flux:radio.group>

            <flux:radio.group wire:model.live="form.billing_cycle" label="Siklus Tagihan" variant="segmented"
                class="max-w-fit">
                @foreach(\App\Enums\ServiceBillingCycle::cases() as $cycle)
                    <flux:radio value="{{ $cycle->value }}" label="{{ $cycle->label() }}" />
                @endforeach
            </flux:radio.group>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input type="date" wire:model.live="form.started_at" label="Tanggal Mulai *" />
                <flux:input type="date" wire:model="form.expires_at" label="Tanggal Berakhir *" readonly 
                    description="Otomatis dihitung mesin, bisa disesuaikan manual." />
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