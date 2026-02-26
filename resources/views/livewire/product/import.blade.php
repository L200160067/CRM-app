<div>
    <flux:modal name="product-import-modal" class="md:w-[52rem]">
        <div class="mb-5">
            <flux:heading size="lg">Import Produk dari CSV</flux:heading>
            <flux:subheading>Upload file CSV untuk menambahkan banyak produk sekaligus. Kolom <strong>name</strong> dan
                <strong>default_price</strong> wajib diisi.</flux:subheading>
        </div>

        {{-- Download Template --}}
        <div
            class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 mb-5">
            <div class="text-sm text-zinc-600 dark:text-zinc-400">
                Belum punya template? Download contoh file CSV-nya.
            </div>
            <flux:button wire:click="downloadTemplate" size="sm" icon="arrow-down-tray" variant="ghost">
                Download Template
            </flux:button>
        </div>

        {{-- File Upload --}}
        @if(!$importDone)
            <flux:field class="mb-5">
                <flux:label>Pilih File CSV</flux:label>
                <input type="file" wire:model="csvFile" accept=".csv,text/csv" class="block w-full text-sm text-zinc-700 dark:text-zinc-300
                               file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                               file:text-sm file:font-medium
                               file:bg-zinc-100 file:text-zinc-700
                               dark:file:bg-zinc-700 dark:file:text-zinc-200
                               hover:file:bg-zinc-200 dark:hover:file:bg-zinc-600
                               cursor-pointer" />
                <div wire:loading wire:target="csvFile" class="text-sm text-zinc-500 mt-1">
                    <flux:icon name="arrow-path" class="size-4 inline animate-spin" /> Memproses file...
                </div>
            </flux:field>
        @endif

        {{-- Preview Table --}}
        @if(count($rows) > 0)
            <div class="mb-5">
                <div class="flex items-center justify-between mb-2">
                    <div class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Preview — {{ count($rows) }} baris ditemukan
                    </div>
                    <div class="flex gap-3 text-xs">
                        <span class="text-green-600 dark:text-green-400 font-medium">
                            ✓ {{ collect($rows)->where('valid', true)->count() }} valid
                        </span>
                        <span class="text-red-500 dark:text-red-400 font-medium">
                            ✗ {{ collect($rows)->where('valid', false)->count() }} error
                        </span>
                    </div>
                </div>

                <div
                    class="border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden max-h-72 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-800 sticky top-0">
                            <tr>
                                <th class="p-2 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide">#</th>
                                <th class="p-2 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide">Nama
                                    Produk</th>
                                <th class="p-2 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide">Harga
                                </th>
                                <th class="p-2 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide">
                                    Deskripsi</th>
                                <th class="p-2 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide">Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($rows as $i => $row)
                                <tr wire:key="row-{{ $i }}"
                                    class="{{ $row['valid'] ? 'bg-white dark:bg-zinc-900' : 'bg-red-50 dark:bg-red-950/20' }}">
                                    <td class="p-2 text-zinc-400 text-xs">{{ $row['line'] }}</td>
                                    <td class="p-2 font-medium text-zinc-900 dark:text-zinc-100">
                                        {{ $row['name'] ?: '-' }}
                                    </td>
                                    <td class="p-2 text-zinc-700 dark:text-zinc-300 whitespace-nowrap">
                                        @if($row['default_price'] !== '')
                                            Rp {{ number_format((float) $row['default_price'], 0, ',', '.') }}
                                        @else
                                            <span class="text-zinc-400">-</span>
                                        @endif
                                    </td>
                                    <td class="p-2 text-zinc-500 max-w-xs truncate">
                                        {{ $row['description'] ?: '-' }}
                                    </td>
                                    <td class="p-2">
                                        @if($row['valid'])
                                            <flux:badge color="green" size="sm">Valid</flux:badge>
                                        @else
                                            <div>
                                                <flux:badge color="red" size="sm">Error</flux:badge>
                                                <div class="text-xs text-red-500 mt-1">
                                                    {{ implode(', ', $row['errors']) }}
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Import Done Summary --}}
        @if($importDone)
            <div
                class="flex items-start gap-3 p-4 rounded-lg bg-green-50 dark:bg-green-950/20 border border-green-200 dark:border-green-800 mb-5">
                <flux:icon name="check-circle" class="size-5 text-green-600 dark:text-green-400 shrink-0 mt-0.5" />
                <div>
                    <div class="font-semibold text-green-800 dark:text-green-300">Import selesai</div>
                    <div class="text-sm text-green-700 dark:text-green-400 mt-0.5">
                        <span class="font-medium">{{ $importedCount }}</span> produk berhasil diimport.
                        @if($skippedCount > 0)
                            <span class="text-red-600 dark:text-red-400 ml-2">{{ $skippedCount }} baris dilewati karena
                                error.</span>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Footer Buttons --}}
        <div class="flex justify-between items-center pt-4">
            <div>
                @if(count($rows) > 0 || $importDone)
                    <flux:button wire:click="resetImport" variant="ghost" size="sm" icon="arrow-path">
                        Reset
                    </flux:button>
                @endif
            </div>
            <div class="flex gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost" wire:click="resetImport">Tutup</flux:button>
                </flux:modal.close>

                @if(count($rows) > 0 && !$importDone)
                    @php $validCount = collect($rows)->where('valid', true)->count(); @endphp
                    <flux:button wire:click="processImport" variant="primary" icon="arrow-up-tray"
                        wire:loading.attr="disabled" wire:target="processImport" :disabled="$validCount === 0">
                        <span wire:loading.remove wire:target="processImport">
                            Proses Import ({{ $validCount }} produk)
                        </span>
                        <span wire:loading wire:target="processImport">Mengimport...</span>
                    </flux:button>
                @endif
            </div>
        </div>
    </flux:modal>
</div>