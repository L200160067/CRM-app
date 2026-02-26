<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        /* ── Reset & Base ── */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            color: #1a1a1a;
            background: #fff;
            padding: 32px 40px;
            max-width: 800px;
            margin: 0 auto;
        }

        /* ── Header ── */
        .header {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            padding-bottom: 16px;
            border-bottom: 2px solid #1a1a1a;
            margin-bottom: 16px;
        }

        .header-logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .header-info h1 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .header-info .sub-name {
            font-size: 11px;
            font-weight: 600;
            color: #444;
            margin-bottom: 4px;
        }

        .header-info .contact {
            font-size: 10.5px;
            color: #555;
            line-height: 1.5;
        }

        /* ── Invoice Title & Number ── */
        .invoice-title {
            text-align: center;
            margin: 20px 0 4px;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .invoice-number {
            text-align: center;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 3px;
            margin-bottom: 20px;
        }

        /* ── Billing Info ── */
        .billing-grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 24px;
        }

        .billing-to {
            flex: 1;
        }

        .billing-dates {
            min-width: 200px;
        }

        .label-small {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #777;
            margin-bottom: 4px;
        }

        .billing-to .client-name {
            font-weight: 700;
            font-size: 13px;
            margin-bottom: 2px;
        }

        .billing-to .client-company {
            font-weight: 600;
            color: #333;
            margin-bottom: 3px;
        }

        .billing-to .client-detail {
            color: #555;
            font-size: 11px;
            line-height: 1.5;
        }

        .date-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 11px;
        }

        .date-row .date-label {
            color: #666;
        }

        .date-row .date-value {
            font-weight: 600;
        }

        .date-row .date-due {
            color: #c0392b;
            font-weight: 700;
        }

        /* ── Items Table ── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        thead tr {
            background: #1a1a1a;
            color: #fff;
        }

        thead th {
            padding: 8px 10px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        thead th:first-child {
            text-align: left;
        }

        thead th.center {
            text-align: center;
        }

        thead th.right {
            text-align: right;
        }

        tbody tr {
            border-bottom: 1px solid #e5e5e5;
        }

        tbody tr:last-child {
            border-bottom: 2px solid #1a1a1a;
        }

        tbody td {
            padding: 8px 10px;
            vertical-align: top;
            font-size: 11.5px;
        }

        tbody td.center {
            text-align: center;
        }

        tbody td.right {
            text-align: right;
            white-space: nowrap;
        }

        .item-desc {
            font-size: 10px;
            color: #777;
            margin-top: 2px;
        }

        /* ── Totals ── */
        .totals-wrap {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 24px;
        }

        .totals-box {
            width: 52%;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 11.5px;
        }

        .total-row.discount {
            color: #27ae60;
        }

        .total-row.grand {
            border-top: 2px solid #1a1a1a;
            margin-top: 6px;
            padding-top: 8px;
            font-size: 14px;
            font-weight: 800;
        }

        /* ── Bottom Section: Notes + Transfer + Signature ── */
        .bottom-section {
            display: flex;
            gap: 24px;
            margin-top: 8px;
            align-items: flex-start;
        }

        .bottom-left {
            flex: 1;
        }

        .bottom-right {
            min-width: 200px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Notes */
        .section-title {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #777;
            margin-bottom: 6px;
        }

        .notes-list {
            list-style: none;
            padding: 0;
        }

        .notes-list li {
            font-size: 10.5px;
            color: #444;
            padding: 2px 0;
            padding-left: 12px;
            position: relative;
            line-height: 1.4;
        }

        .notes-list li::before {
            content: '-';
            position: absolute;
            left: 0;
            color: #888;
        }

        /* Bank Transfer */
        .bank-box {
            background: #f8f8f8;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 10px 14px;
            margin-top: 14px;
        }

        .bank-item {
            margin-bottom: 8px;
        }

        .bank-item:last-child {
            margin-bottom: 0;
        }

        .bank-label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #888;
            margin-bottom: 2px;
        }

        .bank-name {
            font-weight: 700;
            font-size: 11px;
        }

        .bank-number {
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #1a1a1a;
        }

        .bank-bank {
            font-size: 10.5px;
            color: #555;
        }

        /* Signature */
        .signature-label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #777;
            margin-bottom: 6px;
        }

        .signature-img {
            width: 120px;
            height: auto;
            object-fit: contain;
        }

        .signature-line {
            margin-top: 4px;
            border-top: 1px solid #333;
            width: 120px;
            text-align: center;
            font-size: 10px;
            padding-top: 3px;
            color: #333;
        }

        /* ── Print Media ── */
        @media print {
            body {
                padding: 20px 28px;
            }

            @page {
                margin: 15mm 10mm;
            }
        }
    </style>
</head>

<body>

    {{-- ── Company Header ── --}}
    <div class="header">
        @if(config('company.logo'))
            <img class="header-logo" src="{{ asset(config('company.logo')) }}" alt="{{ config('company.name') }}"
                onerror="this.remove()">
        @endif
        <div class="header-info">
            <h1>{{ config('company.name') }}</h1>
            @if(config('company.sub_name'))
                <div class="sub-name">{{ config('company.sub_name') }}</div>
            @endif
            <div class="contact">
                {{ config('company.address') }}<br>
                @if(config('company.phone'))
                    WA: {{ config('company.phone') }}
                @endif
                @if(config('company.email'))
                    &nbsp;|&nbsp; Email: {{ config('company.email') }}
                @endif
                @if(config('company.website'))
                    &nbsp;|&nbsp; {{ config('company.website') }}
                @endif
            </div>
        </div>
    </div>

    {{-- ── Invoice Title ── --}}
    <div class="invoice-title">Invoice</div>
    <div class="invoice-number">{{ $invoice->invoice_number }}</div>

    {{-- ── Billing Info ── --}}
    <div class="billing-grid">
        <div class="billing-to">
            <div class="label-small">Ditagihkan Kepada</div>
            <div class="client-name">{{ $invoice->client->name }}</div>
            @if($invoice->client->company_name)
                <div class="client-company">{{ $invoice->client->company_name }}</div>
            @endif
            @if($invoice->client->address)
                <div class="client-detail">{{ $invoice->client->address }}</div>
            @endif
            @if($invoice->client->phone)
                <div class="client-detail">{{ $invoice->client->phone }}</div>
            @endif
        </div>

        <div class="billing-dates">
            <div class="label-small">Detail Invoice</div>
            <div class="date-row">
                <span class="date-label">Tanggal Terbit</span>
                <span class="date-value">{{ $invoice->issue_date->format('d M Y') }}</span>
            </div>
            <div class="date-row">
                <span class="date-label">Jatuh Tempo</span>
                <span class="date-due">{{ $invoice->due_date->format('d M Y') }}</span>
            </div>
        </div>
    </div>

    {{-- ── Items Table ── --}}
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="center">Qty</th>
                <th class="right">Harga Satuan</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>
                        <div style="font-weight:600">{{ $item->item_name }}</div>
                        @if($item->description)
                            <div class="item-desc">{{ $item->description }}</div>
                        @endif
                    </td>
                    <td class="center">{{ (float) $item->quantity }}</td>
                    <td class="right">Rp {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                    <td class="right" style="font-weight:700">Rp {{ number_format($item->total_price, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── Totals ── --}}
    <div class="totals-wrap">
        <div class="totals-box">
            <div class="total-row">
                <span style="color:#666">Subtotal</span>
                <span>Rp {{ number_format($invoice->subtotal, 2, ',', '.') }}</span>
            </div>

            @if($invoice->discount > 0)
                <div class="total-row discount">
                    <span>Diskon @if($invoice->discount_rate)({{ (float) $invoice->discount_rate }}%)@endif</span>
                    <span>- Rp {{ number_format($invoice->discount, 2, ',', '.') }}</span>
                </div>
            @endif

            @if($invoice->tax > 0)
                <div class="total-row">
                    <span style="color:#666">Pajak ({{ (float) $invoice->tax_rate }}%)</span>
                    <span>Rp {{ number_format($invoice->tax, 2, ',', '.') }}</span>
                </div>
            @endif

            <div class="total-row grand">
                <span>Total Tagihan</span>
                <span>Rp {{ number_format($invoice->grand_total, 2, ',', '.') }}</span>
            </div>
        </div>
    </div>

    {{-- ── Bottom: Notes / Bank Transfer / Signature ── --}}
    <div class="bottom-section">

        {{-- Left: Catatan + Bank Transfer --}}
        <div class="bottom-left">

            {{-- Catatan dari config --}}
            @if(count(config('company.invoice_notes', [])) > 0)
                <div class="section-title">Catatan</div>
                <ul class="notes-list">
                    @foreach(config('company.invoice_notes') as $note)
                        <li>{{ $note }}</li>
                    @endforeach
                </ul>
                {{-- Catatan tambahan dari invoice --}}
                @if($invoice->notes)
                    <ul class="notes-list" style="margin-top:4px">
                        <li>{{ $invoice->notes }}</li>
                    </ul>
                @endif
            @elseif($invoice->notes)
                <div class="section-title">Catatan</div>
                <ul class="notes-list">
                    <li>{{ $invoice->notes }}</li>
                </ul>
            @endif

            {{-- Bank Transfer --}}
            @if(count(config('company.bank_accounts', [])) > 0)
                <div class="bank-box">
                    <div class="section-title" style="margin-bottom:8px">Transfer Ke</div>
                    @foreach(config('company.bank_accounts') as $account)
                        <div class="bank-item">
                            <div class="bank-label">A/n</div>
                            <div class="bank-name">{{ $account['name'] }}</div>
                            <div class="bank-number">{{ $account['number'] }}</div>
                            <div class="bank-bank">{{ $account['bank'] }}</div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>

        {{-- Right: Signature --}}
        <div class="bottom-right">
            <div class="signature-label">Hormat Kami,</div>
            @if(config('company.signature'))
                <img class="signature-img" src="{{ asset(config('company.signature')) }}" alt="Tanda Tangan">
            @else
                <div style="height:80px"></div>
            @endif
            <div class="signature-line">{{ config('company.name') }}</div>
        </div>

    </div>

    <script>
        window.onload = function () {
            window.print();
        };
    </script>

</body>

</html>