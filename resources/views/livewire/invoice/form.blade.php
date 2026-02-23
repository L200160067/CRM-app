<div>
    <flux:modal name="invoice-form-modal" class="md:w-[60rem]">
        <div class="mb-6">
            <flux:heading size="lg">{{ $mode === 'create' ? 'Buat Invoice Baru' : 'Edit Invoice' }}</flux:heading>
            <flux:subheading>Isi detail penagihan dan tambahkan produk/layanan di bawah ini.</flux:subheading>
        </div>

        <form wire:submit="save" class="space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <flux:select wire:model="form.client_id" label="Pilih Klien *">
                    <option value="">-- Pilih --</option>
                    @foreach($this->clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }} {{ $client->company_name ? "({$client->company_name})" : '' }}</option>
                    @endforeach
                </flux:select>
                
                <flux:input wire:model="form.invoice_number" label="Nomor Invoice" placeholder="Otomatis Dihasilkan" disabled />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="form.issue_date" type="date" label="Tanggal Terbit *" />
                <flux:input wire:model="form.due_date" type="date" label="Jatuh Tempo *" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:select wire:model="form.status" label="Status Invoice *">
                    <option value="draft">Draft</option>
                    <option value="sent">Terkirim</option>
                    <option value="paid">Lunas</option>
                    <option value="overdue">Jatuh Tempo</option>
                    <option value="canceled">Dibatalkan</option>
                </flux:select>
            </div>

            <flux:separator variant="subtle" />

            <!-- ALPINE REPEATER START -->
            <div 
                x-data="invoiceRepeater(
                    @entangle('form.items'),
                    {{ $this->products->toJson() }}
                )"
                class="space-y-4"
            >
                <div class="flex items-center justify-between">
                    <flux:heading size="md">Item Tagihan</flux:heading>
                    <flux:button type="button" size="sm" variant="subtle" icon="plus" @click="addItem()">Tambah Baris</flux:button>
                </div>

                <div class="border rounded-lg overflow-hidden border-zinc-200 dark:border-zinc-700">
                    <table class="w-full text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-800 text-left">
                            <tr>
                                <th class="p-3 font-medium text-zinc-500 w-[45%]">Produk / Layanan</th>
                                <th class="p-3 font-medium text-zinc-500 w-24">Kuantitas</th>
                                <th class="p-3 font-medium text-zinc-500 w-32">Harga Satuan</th>
                                <th class="p-3 font-medium text-zinc-500 text-right">Subtotal</th>
                                <th class="p-3 font-medium text-zinc-500 w-12 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            {{-- State if Empty Array --}}
                            <tr x-show="items.length === 0">
                                <td colspan="5" class="p-6 text-center text-zinc-500">
                                    Belum ada item tagihan yang ditambahkan.
                                </td>
                            </tr>
                            
                            {{-- Alpine Loop --}}
                            <template x-for="(item, index) in items" :key="index">
                                <tr>
                                    <td class="p-2 align-top">
                                        <div class="space-y-2">
                                            <flux:select x-model="item.product_id" @change="onProductSelect(index)">
                                                <option value="">Kustom (Ketik Manual)</option>
                                                <template x-for="prod in productsList" :key="prod.id">
                                                    <option :value="prod.id" x-text="prod.name"></option>
                                                </template>
                                            </flux:select>
                                            <flux:input x-model="item.item_name" placeholder="Nama Layanan/Produk" required />
                                            <flux:textarea x-model="item.description" placeholder="Deskripsi tambahan (opsional)" rows="1" />
                                        </div>
                                    </td>
                                    <td class="p-2 align-top">
                                        <flux:input x-model.number="item.quantity" type="number" step="0.01" min="0.01" class="min-w-[4rem]" required />
                                    </td>
                                    <td class="p-2 align-top">
                                        <flux:input x-model.number="item.unit_price" type="number" step="100" min="0" class="min-w-[8rem]" required />
                                    </td>
                                    <td class="p-2 align-top text-right pt-4 font-semibold text-zinc-700 dark:text-zinc-300 whitespace-nowrap">
                                        Rp <span x-text="formatCurrency(item.quantity * item.unit_price)"></span>
                                    </td>
                                    <td class="p-2 align-top text-center pt-3">
                                        <flux:button type="button" variant="ghost" icon="trash" size="sm" @click="removeItem(index)" class="text-red-500 hover:text-red-700" />
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <td colspan="3" class="p-3 text-right font-medium text-zinc-500">Total Item Tagihan:</td>
                                <td colspan="2" class="p-3 text-right font-bold text-lg">
                                    Rp <span x-text="formatCurrency(calculateSubtotal())"></span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Livewire generic Items iteration error messages --}}
                @error('form.items')
                    <div class="text-sm text-red-500 dark:text-red-400 mt-1">{{ $message }}</div>
                @enderror
                @error('form.items.*.item_name')
                    <div class="text-sm text-red-500 dark:text-red-400 mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- ALPINE REPEATER SCRIPT -->
            <script>
                document.addEventListener('alpine:init', () => {
                    Alpine.data('invoiceRepeater', (entangledItems, productsList) => ({
                        items: entangledItems,
                        productsList: productsList,
                        
                        init() {
                            // First render happens automatically via entangle
                        },

                        addItem() {
                            this.items.push({
                                id: null,
                                product_id: '',
                                item_name: '',
                                quantity: 1,
                                unit_price: 0,
                                description: ''
                            });
                        },

                        removeItem(index) {
                            this.items.splice(index, 1);
                        },

                        onProductSelect(index) {
                            const item = this.items[index];
                            if (item.product_id) {
                                // Find product details in our cached list
                                const product = this.productsList.find(p => p.id == item.product_id);
                                if (product) {
                                    item.item_name = product.name;
                                    item.unit_price = product.default_price;
                                    item.description = product.description;
                                }
                            }
                        },

                        calculateSubtotal() {
                            return this.items.reduce((total, item) => {
                                const q = parseFloat(item.quantity) || 0;
                                const p = parseFloat(item.unit_price) || 0;
                                return total + (q * p);
                            }, 0);
                        },

                        formatCurrency(value) {
                            return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(value);
                        }
                    }));
                });
            </script>
            <!-- ALPINE REPEATER END -->

            <flux:separator variant="subtle" />

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-4">
                    <flux:textarea wire:model="form.notes" label="Catatan Tambahan" rows="3" placeholder="Terima kasih atas kepercayaan Anda..." />
                </div>
                
                <div class="space-y-4 bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <flux:heading size="md">Pengaturan Penagihan</flux:heading>
                    
                    <flux:select wire:model.live="form.discount_type" label="Tipe Diskon">
                        <option value="fixed">Nominal Valid (Rp)</option>
                        <option value="percentage">Persentase (%)</option>
                    </flux:select>

                    <div class="grid grid-cols-2 gap-2">
                        @if($form->discount_type === 'percentage')
                            <flux:input wire:model="form.discount_rate" label="Diskon (%)" type="number" step="0.01" min="0" max="100" />
                        @else
                            <flux:input wire:model="form.discount" label="Diskon (Rp)" type="number" step="1000" min="0" />
                        @endif
                        
                        <flux:input wire:model="form.tax_rate" label="Pajak (%)" type="number" step="0.01" min="0" max="100" />
                    </div>
                    <div class="text-xs text-zinc-500 mt-2">
                        *Catatan: Kalkulasi tagihan akhir (Pajak + Diskon) akan dihitung otomatis oleh server setelah Invoice disimpan. Total akan terlihat di halaman Detail.
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-2 pt-4">
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">Simpan Invoice</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
