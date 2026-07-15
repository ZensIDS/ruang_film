<div class="group merchandise-card transition-all duration-500">
    <div class="glass-card-light rounded-2xl overflow-hidden transition-all duration-500 group-hover:scale-[1.03] group-hover:-translate-y-2 group-hover:shadow-[0_0_25px_#6D28D9] h-full flex flex-col">
        <div class="relative w-full overflow-hidden bg-gradient-to-br from-purple-900/30 to-black/50" style="height:200px;">
            <img src="{{ $merchandise->image ? asset('storage/' . $merchandise->image) : asset('landing/images/merchan/merchan1.jpg') }}"
                alt="{{ $merchandise->name }}"
                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">

            @if($merchandise->category)
            <span class="absolute top-3 left-3 bg-purple-700 text-purple-100 text-[10px] font-semibold px-2 py-1 rounded-full tracking-wide">
                {{ $merchandise->category->name }}
            </span>
            @endif
        </div>

        <div class="p-5 flex flex-col flex-grow gap-3">
            <div>
                <h3 class="text-base font-bold text-white group-hover:text-purple-300 transition-colors duration-300 leading-snug">
                    {{ $merchandise->name }}
                </h3>
                @if($merchandise->summary)
                <p class="text-gray-400 text-sm mt-2">{{ $merchandise->summary }}</p>
                @endif
            </div>

            <div class="flex items-baseline gap-2">
                <span class="text-purple-400 text-lg font-bold">
                    Rp {{ number_format($merchandise->price, 0, ',', '.') }}
                </span>
            </div>

            <div class="text-xs text-gray-400 space-y-1">
                <div>Berat: {{ number_format($merchandise->weight) }} gram</div>
                <div>Stok: {{ $merchandise->qty_stock }}</div>
            </div>

            <div class="flex gap-2 mt-auto">
                @auth
                <form action="{{ route('cart.store', $merchandise) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit"
                        class="w-full h-10 rounded-xl border border-purple-500/40 bg-purple-500/10 text-purple-300 text-sm font-semibold flex items-center justify-center gap-2 hover:bg-purple-500/25 transition-colors duration-200 {{ $merchandise->qty_stock < 1 ? 'opacity-50 cursor-not-allowed' : '' }}"
                        {{ $merchandise->qty_stock < 1 ? 'disabled' : '' }}>
                        <i class="fas fa-shopping-cart text-sm"></i>
                        {{ $merchandise->qty_stock < 1 ? 'Stok Habis' : 'Tambah ke Keranjang' }}
                    </button>
                </form>
                {{-- <a href="{{ route('cart.index') }}" --}}
                    {{-- class="flex-1 h-10 rounded-xl bg-gradient-to-r from-purple-700 to-purple-500 text-white text-sm font-semibold flex items-center justify-center gap-2 hover:opacity-85 transition-opacity duration-200"> --}}
                    {{-- <i class="fas fa-bolt text-xs"></i> --}}
                    {{-- Keranjang --}}
                {{-- </a> --}}
                @else
                <a href="{{ route('login', ['role' => 'umum']) }}"
                    class="flex-1 h-10 rounded-xl bg-gradient-to-r from-purple-700 to-purple-500 text-white text-sm font-semibold flex items-center justify-center gap-2 hover:opacity-85 transition-opacity duration-200">
                    <i class="fas fa-sign-in-alt text-xs"></i>
                    Login untuk Beli
                </a>
                @endauth
            </div>
        </div>
    </div>
</div>
