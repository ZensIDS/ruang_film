@extends('layouts.landing.master')
@section('main')
<main class="relative z-10">
    <section class="max-w-xl mx-auto px-6 md:px-10 py-16">
        <p class="text-purple-400 text-sm uppercase tracking-wider font-semibold mb-2">PESANAN</p>
        <h1 class="text-3xl md:text-4xl font-bold border-l-8 border-purple-500 pl-6 tracking-tight">Cek Pesanan</h1>
        <p class="mt-4 text-gray-400 text-sm">
            Masukkan nomor invoice dan nomor WhatsApp yang Anda pakai saat memesan untuk membuka kembali invoice, status pembayaran, dan upload bukti transfer.
        </p>

        <form action="{{ route('orders.guest.lookup.submit') }}" method="POST" class="mt-8 glass-card rounded-3xl p-6 md:p-8 space-y-5">
            @csrf
            <div>
                <label for="invoice_number" class="text-sm text-gray-300">Nomor Invoice</label>
                <input type="text" id="invoice_number" name="invoice_number" value="{{ old('invoice_number') }}"
                    placeholder="INV-RF-20261011-1234" required autocomplete="off"
                    class="mt-2 w-full rounded-2xl bg-white/5 border border-purple-500/20 px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500/50">
            </div>
            <div>
                <label for="no_hp" class="text-sm text-gray-300">Nomor WhatsApp</label>
                <input type="tel" id="no_hp" name="no_hp" inputmode="numeric" placeholder="08xxxxxxxxxx" required autocomplete="tel"
                    class="mt-2 w-full rounded-2xl bg-white/5 border border-purple-500/20 px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500/50">
            </div>

            @if($errors->any())
            <div class="rounded-2xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-red-200 text-sm" role="alert">
                {{ $errors->first() }}
            </div>
            @endif

            <button type="submit"
                class="w-full h-12 rounded-xl bg-gradient-to-r from-purple-700 to-purple-500 text-white text-sm font-semibold">
                Cari Pesanan
            </button>
        </form>
    </section>
</main>
@endsection