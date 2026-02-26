<div>
    <flux:modal name="invoice-show-modal" class="md:w-[50rem]">
        @if($invoice)
        <!-- Absolute Top Right Status Badge -->
        <div class="absolute top-6 right-6">
            @php
                $color = match($invoice->status->value ?? $invoice->status) {
                    'draft' => 'zinc',
                    'sent' => 'blue',
                    'paid' => 'green',
                    'overdue' => 'red',
                    'canceled' => 'stone',
                    default => 'zinc',
                };
                $label = match($invoice->status->value ?? $invoice->status) {
                    'draft' => 'Draft',
                    'sent' => 'Terkirim',
                    'paid' => 'Lunas',
                    'overdue' => 'Jatuh Tempo',
                    'canceled' => 'Dibatalkan',
                    default => 'Unknown',
                };
            @endphp
            <flux:badge color="{{ $color }}">{{ $label }}</flux:badge>
        </div>

        <!-- Modular Company Header -->
        <div class="mb-5 pb-5 border-b border-zinc-200 dark:border-zinc-700">
            <div class="flex items-start gap-5 mb-5">
                @if(config('company.logo'))
                    <div class="w-24 h-24 shrink-0 rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 flex items-center justify-center">
                        <img src="{{ asset(config('company.logo')) }}" alt="Logo" class="w-full h-full object-contain" onerror="this.outerHTML='<svg class=\'w-8 h-8 text-zinc-400\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\' /></svg>'">
                    </div>
                @else
                    <div class="w-24 h-24 shrink-0 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 flex items-center justify-center">
                        <svg class="w-8 h-8 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                @endif
                <div class="flex flex-col justify-center py-1">
                    <h2 class="font-bold text-2xl text-zinc-900 dark:text-white mb-1">{{ config('company.name') }}</h2>
                    @if(config('company.sub_name'))
                        <div class="text-zinc-700 dark:text-zinc-300 font-medium text-sm">{{ config('company.sub_name') }}</div>
                    @endif
                    <div class="text-sm text-zinc-500 mt-1">
                        {{ config('company.address') }}<br>
                        {{ config('company.postal_code') }} {{ config('company.province') }}
                    </div>
                </div>
            </div>
            
            <div class="text-sm text-zinc-500 flex flex-col gap-1.5">
                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                    <span><span class="font-medium text-zinc-700 dark:text-zinc-300">No. WhatsApp:</span> {{ config('company.phone') }}</span>
                    <span><span class="font-medium text-zinc-700 dark:text-zinc-300">Email:</span> {{ config('company.email') }}</span>
                    {{-- <span class="hidden sm:inline text-zinc-300 dark:text-zinc-600">|</span> --}}
                </div>
                <div>
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">Website:</span> {{ config('company.website') }}
                </div>
            </div>
        </div>

        <!-- Invoice Code -->
        <div class="flex flex-col items-center justify-center mb-10 text-center bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-100 dark:border-zinc-700/50 py-6 rounded-xl">
            <h1 class="text-3xl font-bold tracking-widest text-zinc-900 dark:text-white">{{ $invoice->invoice_number }}</h1>
        </div>

        <div class="grid grid-cols-2 gap-8 mb-8">
            <div>
                <div class="text-xs font-semibold text-zinc-500 uppercase tracking-widest mb-2">Ditagihkan Kepada</div>
                <div class="font-bold text-lg text-zinc-900 dark:text-white mb-1">{{ $invoice->client->name }}</div>
                <div class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $invoice->client->company_name }}</div>
                <div class="text-sm text-zinc-500 mt-1 whitespace-pre-line">{{ $invoice->client->address ?? '-' }}</div>
                <div class="text-sm text-zinc-500 mt-1">{{ $invoice->client->phone ?? '-' }}</div>
            </div>
            
            <div class="space-y-3 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-zinc-500">Tanggal Terbit</span>
                    <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $invoice->issue_date->format('d M Y') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-zinc-500">Jatuh Tempo</span>
                    <span class="font-medium text-red-600 dark:text-red-400">{{ $invoice->due_date->format('d M Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="border rounded-lg overflow-hidden border-zinc-200 dark:border-zinc-700 mb-6">
            <table class="w-full text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-800 text-left">
                    <tr>
                        <th class="p-3 font-medium text-zinc-500">Item</th>
                        <th class="p-3 font-medium text-zinc-500 text-center">Qty</th>
                        <th class="p-3 font-medium text-zinc-500 text-right">Harga</th>
                        <th class="p-3 font-medium text-zinc-500 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach($invoice->items as $item)
                        <tr>
                            <td class="p-3 align-top">
                                <div class="font-medium">{{ $item->item_name }}</div>
                                @if($item->description)
                                    <div class="text-xs text-zinc-500 mt-1">{{ $item->description }}</div>
                                @endif
                            </td>
                            <td class="p-3 align-top text-center whitespace-nowrap">{{ (float) $item->quantity }}</td>
                            <td class="p-3 align-top text-right whitespace-nowrap">Rp {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                            <td class="p-3 align-top text-right font-medium whitespace-nowrap">Rp {{ number_format($item->total_price, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="flex justify-end mb-8">
            <div class="w-1/2 space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-zinc-500">Subtotal</span>
                    <span>Rp {{ number_format($invoice->subtotal, 2, ',', '.') }}</span>
                </div>
                
                @if($invoice->discount > 0)
                    <div class="flex justify-between text-sm text-green-600 dark:text-green-400">
                        <span>Diskon @if($invoice->discount_rate) ({{ (float) $invoice->discount_rate }}%) @endif</span>
                        <span>- Rp {{ number_format($invoice->discount, 2, ',', '.') }}</span>
                    </div>
                @endif
                
                @if($invoice->tax > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-500">Pajak ({{ (float) $invoice->tax_rate }}%)</span>
                        <span>Rp {{ number_format($invoice->tax, 2, ',', '.') }}</span>
                    </div>
                @endif
                
                <div class="flex justify-between text-lg font-bold border-t border-zinc-200 dark:border-zinc-700 pt-3 mt-2">
                    <span>Total Tagihan</span>
                    <span>Rp {{ number_format($invoice->grand_total, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>

        @if($invoice->notes)
            <div class="bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-lg text-sm text-zinc-600 dark:text-zinc-400 whitespace-pre-line">
                <strong>Catatan:</strong><br>
                {{ $invoice->notes }}
            </div>
        @endif

        <div class="flex justify-end pt-6">
            <flux:modal.close>
                <flux:button variant="ghost">Tutup</flux:button>
            </flux:modal.close>
        </div>
        @endif
    </flux:modal>
</div>
