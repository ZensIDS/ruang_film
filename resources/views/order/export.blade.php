<table>
    <tr>
        <td colspan="2"><strong>Data Invoice Merchandise</strong></td>
    </tr>
    <tr>
        <td>Periode</td>
        <td>
            @if($startDate && $endDate)
                {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }}
                s/d
                {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}
            @else
                Semua Data
            @endif
        </td>
    </tr>
    <tr><td></td><td></td></tr>

    {{-- Rekap jumlah transaksi per status --}}
    <tr>
        <td colspan="2"><strong>Rekap Status Transaksi</strong></td>
    </tr>
    <tr>
        <td>Paid</td>
        <td>{{ $stats['paid_count'] }}</td>
    </tr>
    <tr>
        <td>Waiting Verification</td>
        <td>{{ $stats['waiting_verification_count'] }}</td>
    </tr>
    <tr>
        <td>Expired</td>
        <td>{{ $stats['expired_count'] }}</td>
    </tr>
    <tr>
        <td>Rejected</td>
        <td>{{ $stats['rejected_count'] }}</td>
    </tr>
    <tr><td></td><td></td></tr>

    {{-- Rekap pendapatan --}}
    <tr>
        <td colspan="2"><strong>Rekap Pendapatan</strong></td>
    </tr>
    <tr>
        <td>Pendapatan Kotor</td>
        <td>{{ number_format($stats['gross_revenue'], 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td>Total Ongkir</td>
        <td>{{ number_format($stats['shipping_total'], 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td>Pendapatan Bersih</td>
        <td>{{ number_format($stats['net_revenue'], 0, ',', '.') }}</td>
    </tr>
    <tr><td></td><td></td></tr>
    <tr><td></td><td></td></tr>

    {{-- Tabel data order --}}
    <tr>
        <th>No</th>
        <th>Invoice</th>
        <th>Pembeli</th>
        <th>Total</th>
        <th>Status</th>
        <th>Batas Bayar</th>
    </tr>
    @foreach($orders as $order)
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $order->invoice_number }}</td>
        <td>{{ $order->user->name ?? '-' }}</td>
        <td>{{ number_format($order->total, 0, ',', '.') }}</td>
        <td>{{ strtoupper(str_replace('_', ' ', $order->status)) }}</td>
        <td>{{ optional($order->payment_due_at)->translatedFormat('d M Y H:i') ?? '-' }}</td>
    </tr>
    @endforeach
</table>
