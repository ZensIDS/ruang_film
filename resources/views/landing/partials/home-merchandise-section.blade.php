<section class="max-w-7xl mx-auto px-6 md:px-10 py-24 md:py-28 merchandise-section">
    <div class="fade-up">
        <p class="text-purple-400 text-sm md:text-base uppercase tracking-wider font-semibold mb-2">
            OFFICIAL MERCHANDISE
        </p>
        <h2 class="text-3xl md:text-5xl font-bold text-left border-l-8 border-purple-500 pl-6 tracking-tight">
            Merchandise
        </h2>
    </div>

    <div class="glass-card mt-12 rounded-3xl p-6 md:p-8 lg:p-10 fade-up transition-all duration-500 hover:shadow-[0_0_30px_rgba(109,40,217,0.2)]">
        @if($featuredMerchandises->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-7">
            @foreach($featuredMerchandises as $merchandise)
            @include('landing.partials.merchandise-card', ['merchandise' => $merchandise])
            @endforeach
        </div>
        @else
        <div class="rounded-2xl border border-white/10 bg-white/5 px-6 py-12 text-center text-gray-400">
            Merchandise belum tersedia.
        </div>
        @endif

        <div class="mt-12 pt-10 border-t border-purple-500/20">
            <div class="text-center">
                <a href="{{ route('merchandise') }}"
                    class="btn-gradient px-8 md:px-12 py-3 md:py-4 rounded-full text-white font-semibold text-base md:text-lg transition-all duration-300 hover:scale-105 hover:shadow-[0_0_25px_#8B5CF6] inline-flex items-center gap-3 group">
                    <i class="fas fa-store text-white group-hover:translate-x-[-2px] transition-transform duration-300"></i>
                    Lihat Semua
                    <i class="fas fa-arrow-right text-sm group-hover:translate-x-1 transition-transform duration-300"></i>
                </a>

                <p class="text-gray-500 text-xs mt-5">
                    *Stok merchandise diperbarui otomatis dari dashboard admin.
                </p>
            </div>
        </div>
    </div>
</section>
