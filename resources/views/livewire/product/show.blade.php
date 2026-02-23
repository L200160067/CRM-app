<div>
    <flux:modal name="product-show-modal" class="md:w-[40rem]">
        @if($product)
        <div class="mb-6">
            <flux:heading size="lg">Detail Produk</flux:heading>
            <flux:subheading>Rincian lengkap item produk atau layanan.</flux:subheading>
        </div>

        <div class="space-y-4">
            <div>
                <flux:heading size="sm" class="text-zinc-500">Nama Produk</flux:heading>
                <flux:text class="font-medium">{{ $product->name }}</flux:text>
            </div>

            <flux:separator variant="subtle" />

            <div>
                <flux:heading size="sm" class="text-zinc-500">Harga Default / Patokan</flux:heading>
                <flux:text class="text-xl font-bold">Rp {{ number_format($product->default_price, 2, ',', '.') }}</flux:text>
            </div>

            <flux:separator variant="subtle" />

            <div>
                <flux:heading size="sm" class="text-zinc-500">Deskripsi Barang / Jasa</flux:heading>
                <flux:text>{{ $product->description ?? 'Tidak ada penjelasan lebih lanjut disediakan untuk produk ini.' }}</flux:text>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mt-2">
                <div>
                    <flux:heading size="sm" class="text-zinc-500">Dibuat Pada</flux:heading>
                    <flux:text class="text-sm">{{ $product->created_at->translatedFormat('d F Y, H:i') }}</flux:text>
                </div>
                <div>
                    <flux:heading size="sm" class="text-zinc-500">Terakhir Diperbarui</flux:heading>
                    <flux:text class="text-sm">{{ $product->updated_at->translatedFormat('d F Y, H:i') }}</flux:text>
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
