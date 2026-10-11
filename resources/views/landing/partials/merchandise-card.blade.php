@include('landing.partials.merchandise-picker')
@php
    $soldOut = $merchandise->qty_stock < 1;
    $detailUrl = $merchandise->detailUrl();
    $activeVariants = $merchandise->has_variants ? $merchandise->activeVariants : collect();
@endphp
<div class="group merchandise-card transition-all duration-500">
    <div class="glass-card-light rounded-2xl overflow-hidden transition-all duration-500 group-hover:scale-[1.03] group-hover:-translate-y-2 group-hover:shadow-[0_0_25px_#6D28D9] h-full flex flex-col">
        <div class="relative w-full overflow-hidden bg-gradient-to-br from-purple-900/30 to-black/50" style="height:200px;">
            <a href="{{ $detailUrl }}" class="block w-full h-full" aria-label="Lihat {{ $merchandise->name }}">
                <img src="{{ $merchandise->imageUrl() }}"
                    alt="{{ $merchandise->name }}"
                    loading="lazy"
                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110 {{ $soldOut ? 'grayscale brightness-50' : '' }}">
            </a>

            @if($soldOut)
            <div class="absolute inset-0 z-10 flex items-center justify-center bg-black/50 pointer-events-none">
                <span class="-rotate-12 border-4 border-red-500 text-red-500 text-2xl font-extrabold tracking-[0.25em] px-5 py-1 rounded-lg uppercase bg-black/40 shadow-lg">
                    Sold Out
                </span>
            </div>
            @endif

            @if($merchandise->category)
            <span class="absolute top-3 left-3 z-20 bg-purple-700 text-purple-100 text-[10px] font-semibold px-2 py-1 rounded-full tracking-wide pointer-events-none">
                {{ $merchandise->category->name }}
            </span>
            @endif
        </div>

        <div class="p-5 flex flex-col flex-grow gap-3">
            <div>
                <a href="{{ $detailUrl }}" class="block">
                    <h3 class="text-base font-bold text-white group-hover:text-purple-300 transition-colors duration-300 leading-snug">
                        {{ $merchandise->name }}
                    </h3>
                </a>
                @if($merchandise->summary)
                <p class="text-gray-400 text-sm mt-2">{{ $merchandise->summary }}</p>
                @endif
            </div>

            <div class="flex items-baseline gap-2">
                <span class="text-purple-400 text-lg font-bold">
                    Rp {{ number_format($merchandise->price, 0, ',', '.') }}
                </span>
            </div>

            @if($activeVariants->isNotEmpty())
            <div class="flex flex-wrap gap-1.5" aria-label="Varian tersedia">
                @foreach($activeVariants as $variant)
                <span class="px-2 py-0.5 rounded-md border text-[11px] font-semibold {{ $variant->qty_stock < 1 ? 'border-white/10 text-gray-600 line-through' : 'border-purple-500/30 text-purple-200' }}">
                    {{ $variant->name }}
                </span>
                @endforeach
            </div>
            @endif

            <div class="text-xs text-gray-400 space-y-1">
                <div>Berat: {{ number_format($merchandise->weight) }} gram</div>
                <div>Stok: {{ $merchandise->qty_stock }}</div>
            </div>

            <div class="flex gap-2 mt-auto">
                @if($soldOut)
                <button type="button" disabled
                    class="flex-1 h-10 rounded-xl border border-purple-500/40 bg-purple-500/10 text-purple-300 text-sm font-semibold flex items-center justify-center gap-2 opacity-50 cursor-not-allowed">
                    <i class="fas fa-shopping-cart text-sm"></i>
                    Stok Habis
                </button>
                @else
                <button type="button"
                    data-merch-open
                    data-product="{{ json_encode($merchandise->pickerData()) }}"
                    data-detail-url="{{ $detailUrl }}"
                    class="flex-1 h-10 rounded-xl border border-purple-500/40 bg-purple-500/10 text-purple-300 text-sm font-semibold flex items-center justify-center gap-2 hover:bg-purple-500/25 active:scale-[0.98] transition">
                    <i class="fas fa-cart-plus text-sm"></i>
                    Tambah ke Keranjang
                </button>
                @endif
            </div>
        </div>
    </div>
</div>