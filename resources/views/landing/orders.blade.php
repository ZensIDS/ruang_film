@extends('layouts.landing.master')
@section('main')
<main class="relative z-10">
    <section class="max-w-6xl mx-auto px-6 md:px-10 py-16">
        <div class="mb-8">
            <p class="text-purple-400 text-sm uppercase tracking-wider font-semibold mb-2">INVOICE</p>
            <h1 class="text-3xl md:text-5xl font-bold border-l-8 border-purple-500 pl-6 tracking-tight">Riwayat Pesanan</h1>
        </div>

        <div class="glass-card rounded-3xl p-6 md:p-8">
            @if($orders->isEmpty())
            <div class="text-center text-gray-300 py-12">Belum ada invoice merchandise.</div>
            @else
            <div class="space-y-4">
                @foreach($orders as $order)
                <div class="rounded-2xl border border-white/10 bg-white/5 p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <div class="text-white font-semibold">{{ $order->invoice_number }}</div>
                        <div class="text-sm text-gray-400 mt-1">{{ $order->created_at->translatedFormat('d F Y H:i') }} WIB</div>
                        <div class="text-sm text-gray-400 mt-1">Total: Rp {{ number_format($order->total, 0, ',', '.') }}</div>
                        <div class="text-sm text-gray-400 mt-1">Metode: {{ $order->deliveryMethodLabel() }}</div>
                        <div class="text-sm text-gray-400 mt-1">
                            {{ $order->isSelfPickup() ? 'Pengambilan: Self pickup di basecamp' : 'Pengiriman: ' . $order->shippingStatusText() }}
                        </div>
                        @unless($order->isSelfPickup())
                        <div class="text-sm text-gray-400 mt-1">Resi: {{ $order->shipping_airway_bill ?? '-' }}</div>
                        @endunless
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold border border-purple-500/30 bg-purple-500/10 text-purple-200">
                            {{ strtoupper(str_replace('_', ' ', $order->status)) }}
                        </span>
                        <a href="{{ route('orders.show', $order) }}" class="h-10 px-5 rounded-xl bg-gradient-to-r from-purple-700 to-purple-500 text-white text-sm font-semibold flex items-center">
                            Detail
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </section>
</main>
@endsection
