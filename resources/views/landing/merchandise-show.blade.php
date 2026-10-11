@extends('layouts.landing.master')
@section('main')
@include('landing.partials.merchandise-picker')
@php
    $soldOut = $merchandise->qty_stock < 1;
    $pickerData = $merchandise->pickerData();
    $pickerJson = json_encode($pickerData);
@endphp
<main class="relative z-10 pb-32 md:pb-20">
    <section class="max-w-6xl mx-auto px-6 md:px-10 pt-10 md:pt-16">
        <nav class="flex flex-wrap items-center gap-2 text-sm text-gray-500" aria-label="Breadcrumb">
            <a href="/" class="hover:text-purple-400 transition">Home</a>
            <span class="text-gray-600">/</span>
            <a href="{{ route('merchandise') }}" class="hover:text-purple-400 transition">Merchandise</a>
            <span class="text-gray-600">/</span>
            <span class="text-purple-400 font-semibold truncate max-w-[14rem]">{{ $merchandise->name }}</span>
        </nav>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12 mt-6">
            {{-- Gambar --}}
            <div class="relative w-full aspect-square overflow-hidden rounded-3xl border border-purple-500/20 bg-gradient-to-br from-purple-900/30 to-black/50">
                <img id="merch-main-image"
                    src="{{ $merchandise->imageUrl() }}"
                    alt="{{ $merchandise->name }}"
                    class="w-full h-full object-cover {{ $soldOut ? 'grayscale brightness-50' : '' }}">

                @if($soldOut)
                <div class="absolute inset-0 z-10 flex items-center justify-center bg-black/50 pointer-events-none">
                    <span class="-rotate-12 border-4 border-red-500 text-red-500 text-3xl md:text-4xl font-extrabold tracking-[0.25em] px-6 py-2 rounded-lg uppercase bg-black/40 shadow-lg">
                        Sold Out
                    </span>
                </div>
                @endif

                @if($merchandise->category)
                <span class="absolute top-4 left-4 z-20 bg-purple-700 text-purple-100 text-xs font-semibold px-3 py-1 rounded-full tracking-wide">
                    {{ $merchandise->category->name }}
                </span>
                @endif
            </div>

            {{-- Info --}}
            <div class="flex flex-col">
                <p class="text-purple-400 text-sm uppercase tracking-wider font-semibold mb-2">Official Merchandise</p>
                <h1 class="text-3xl md:text-4xl font-bold text-white tracking-tight leading-tight">{{ $merchandise->name }}</h1>

                <p class="mt-4 text-3xl font-bold text-purple-300">
                    Rp {{ number_format($merchandise->price, 0, ',', '.') }}
                </p>

                @if($merchandise->summary)
                <p class="mt-3 text-gray-300">{{ $merchandise->summary }}</p>
                @endif

                <dl class="mt-5 grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                        <dt class="text-gray-500 text-xs uppercase tracking-wider">Berat</dt>
                        <dd class="text-white font-semibold mt-1">{{ number_format($merchandise->weight) }} gram</dd>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                        <dt class="text-gray-500 text-xs uppercase tracking-wider">Total Stok</dt>
                        <dd class="font-semibold mt-1 {{ $soldOut ? 'text-red-400' : 'text-white' }}">
                            {{ $soldOut ? 'Habis' : $merchandise->qty_stock }}
                        </dd>
                    </div>
                </dl>

                {{-- Picker (desktop) --}}
                <div class="hidden md:block mt-6 glass-card rounded-2xl p-5"
                    data-merch-inline
                    data-product="{{ $pickerJson }}"></div>
            </div>
        </div>

        @if($merchandise->description)
        <div class="mt-10 glass-card rounded-3xl p-6 md:p-8">
            <h2 class="text-xl font-semibold text-white mb-4">Deskripsi Produk</h2>
            <div class="text-gray-300 leading-relaxed whitespace-pre-line">{{ $merchandise->description }}</div>
        </div>
        @endif

        @if($relatedMerchandises->isNotEmpty())
        <div class="mt-14">
            <h2 class="text-2xl font-bold border-l-8 border-purple-500 pl-5 tracking-tight mb-6">Produk Lainnya</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedMerchandises as $related)
                @include('landing.partials.merchandise-card', ['merchandise' => $related])
                @endforeach
            </div>
        </div>
        @endif
    </section>

    {{-- Bar aksi menempel di bawah (mobile) --}}
    <div class="md:hidden fixed bottom-0 inset-x-0 z-40 border-t border-purple-500/20 bg-[#0f0f23]/95 backdrop-blur-xl px-4 pt-3"
        style="padding-bottom: calc(0.75rem + env(safe-area-inset-bottom, 0px));">
        <div class="flex items-center gap-3">
            <div class="min-w-0">
                <p class="text-[11px] uppercase tracking-wider text-gray-500">Harga</p>
                <p class="text-lg font-bold text-purple-300 leading-tight">Rp {{ number_format($merchandise->price, 0, ',', '.') }}</p>
            </div>
            @if($soldOut)
            <button type="button" disabled
                class="flex-1 h-12 rounded-xl bg-white/5 border border-white/10 text-gray-400 text-sm font-semibold cursor-not-allowed">
                Stok Habis
            </button>
            @else
            <button type="button"
                data-merch-open
                data-product="{{ $pickerJson }}"
                data-detail-url="{{ $merchandise->detailUrl() }}"
                class="flex-1 h-12 rounded-xl bg-gradient-to-r from-purple-700 to-purple-500 text-white text-sm font-semibold flex items-center justify-center gap-2 active:scale-[0.98] transition">
                <i class="fas fa-cart-plus"></i>
                Masukkan Keranjang
            </button>
            @endif
        </div>
    </div>
</main>
@endsection