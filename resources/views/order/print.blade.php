<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Penjualan</title>
    <style>
        /* Catatan: template ini dirender oleh dompdf (barryvdh/laravel-dompdf),
           bukan browser biasa -- jadi sengaja tidak pakai flexbox / position:
           sticky / box-shadow, karena dukungan CSS dompdf terbatas. Layout
           kolom dibuat pakai <table>, bukan flex. */
        @page {
            margin: 16mm 14mm;
        }
        body {
            font-family: "DejaVu Sans", sans-serif;
            color: #222;
            font-size: 12px;
        }
        .sheet {
            page-break-after: always;
        }
        .sheet:last-child {
            page-break-after: auto;
        }
        .header-table {
            width: 100%;
            border-bottom: 3px solid #333;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }
        .header-table td { vertical-align: top; }
        .header-title {
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 4px;
        }
        .header-right {
            text-align: right;
            font-size: 11px;
            color: #555;
        }
        .status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            color: #fff;
        }
        .status-paid { background: #00a65a; }
        .status-waiting_verification { background: #f39c12; }
        .status-expired, .status-payment_rejected { background: #dd4b39; }
        .status-other { background: #00c0ef; }

        .info-table {
            width: 100%;
            margin-bottom: 16px;
        }
        .info-table td {
            vertical-align: top;
            width: 33.33%;
            padding-right: 10px;
        }
        .info-table h3 {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #888;
            margin: 0 0 6px;
            border-bottom: 1px solid #eee;
            padding-bottom: 4px;
        }
        .info-table p { margin: 3px 0; line-height: 1.5; }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        table.items th, table.items td {
            border: 1px solid #ccc;
            padding: 6px 8px;
        }
        table.items th {
            background: #f2f2f2;
            text-align: left;
        }
        table.items td.num, table.items th.num { text-align: right; }

        table.totals {
            width: 260px;
            margin-left: 340px;
        }
        table.totals td { padding: 4px 8px; }
        table.totals td.label { color: #555; }
        table.totals td.value { text-align: right; }
        table.totals tr.grand td {
            border-top: 2px solid #333;
            font-weight: bold;
            font-size: 14px;
            padding-top: 8px;
        }

        .notes {
            margin-top: 14px;
            font-size: 11px;
            color: #555;
        }
        .footer-note {
            margin-top: 22px;
            font-size: 10px;
            color: #999;
        }
    </style>
</head>
<body>
    @forelse($orders as $order)
    @php
        $statusClass = in_array($order->status, ['paid', 'waiting_verification', 'expired', 'payment_rejected'])
            ? 'status-' . $order->status
            : 'status-other';
    @endphp
    <div class="sheet">
        <table class="header-table">
            <tr>
                <td>
                    <div class="header-title">Detail Penjualan</div>
                    <div>No. Invoice: <strong>{{ $order->invoice_number }}</strong></div>
                </td>
                <td class="header-right">
                    <span class="status {{ $statusClass }}">{{ strtoupper(str_replace('_', ' ', $order->status)) }}</span>
                    <br>
                    <span>Dicetak: {{ now()->translatedFormat('d F Y H:i') }} WIB</span>
                </td>
            </tr>
        </table>

        <table class="info-table">
            <tr>
                <td>
                    <h3>Pembeli</h3>
                    <p><strong>{{ $order->recipient_name }}</strong></p>
                    <p>{{ $order->recipient_phone }}</p>
                    <p>{{ $order->user->email ?? '-' }}</p>
                </td>
                <td>
                    <h3>Pengiriman</h3>
                    <p><strong>Metode:</strong> {{ $order->deliveryMethodLabel() }}</p>
                    @if($order->isSelfPickup())
                    <p>Pesanan diambil sendiri di basecamp.</p>
                    @else
                    <p><strong>Alamat:</strong><br>{{ $order->full_address }}</p>
                    @endif
                </td>
                <td>
                    <h3>Invoice</h3>
                    <p><strong>Batas Bayar:</strong><br>{{ optional($order->payment_due_at)->translatedFormat('d F Y H:i') ?? '-' }} WIB</p>
                    @if(!$order->isSelfPickup())
                    <p><strong>No. Resi:</strong> {{ $order->shipping_airway_bill ?? '-' }}</p>
                    @endif
                </td>
            </tr>
        </table>

        <table class="items">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th class="num">Qty</th>
                    <th class="num">Berat</th>
                    <th class="num">Harga</th>
                    <th class="num">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->merchandise_name }}</td>
                    <td class="num">{{ $item->quantity }}</td>
                    <td class="num">{{ number_format($item->weight) }} gr</td>
                    <td class="num">@currency($item->unit_price)</td>
                    <td class="num">@currency($item->subtotal)</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals">
            <tr>
                <td class="label">Subtotal</td>
                <td class="value">@currency($order->subtotal)</td>
            </tr>
            <tr>
                <td class="label">Ongkir</td>
                <td class="value">
                    @if($order->isSelfPickup())
                        Gratis
                    @else
                        @currency($order->shipping_fee)
                    @endif
                </td>
            </tr>
            <tr class="grand">
                <td class="label">Total</td>
                <td class="value">@currency($order->total)</td>
            </tr>
        </table>

        @if($order->notes)
        <div class="notes"><strong>Catatan:</strong> {{ $order->notes }}</div>
        @endif

        <div class="footer-note">Dokumen ini adalah rekap detail penjualan internal, bukan resi pengiriman.</div>
    </div>
    @empty
    <div class="sheet">Tidak ada data untuk dicetak.</div>
    @endforelse
</body>
</html>