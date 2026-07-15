<section class="max-w-7xl mx-auto px-6 md:px-10 py-24 md:py-28">
    <div class="fade-up">
        <p class="text-purple-400 text-sm md:text-base uppercase tracking-wider font-semibold mb-2">
            APA YANG KAMI TAWARKAN
        </p>
        <h2 class="text-3xl md:text-5xl font-bold text-left border-l-8 border-purple-500 pl-6 tracking-tight">
            Program Highlight
        </h2>
    </div>

    @php
        $programHighlightIcons = ['fa-book', 'fa-film', 'fa-users', 'fa-lightbulb', 'fa-globe'];
        $highlightProgramCategories = collect($programCategories ?? [])->take(3)->values();
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-12 fade-up">
        <div class="group glass-card-light rounded-2xl p-6 flex flex-col items-center text-center gap-4 transition-all duration-500 hover:-translate-y-2 hover:shadow-[0_0_25px_rgba(109,40,217,0.2)] hover:border-purple-500/50">
            <div class="w-14 h-14 rounded-2xl bg-purple-500/15 border border-purple-500/30 flex items-center justify-center text-2xl transition-transform duration-300 group-hover:scale-110">
                <i class="fas fa-trophy text-purple-300"></i>
            </div>
            <div>
                <h3 class="text-white font-bold text-lg mb-2 group-hover:text-purple-300 transition-colors duration-300">
                    Kompetisi
                </h3>
                <p class="text-gray-400 text-sm leading-relaxed">
                    Ruang apresiasi dan kurasi karya film festival.
                </p>
            </div>
            <a href="{{ route('landing.program') }}#competition-section"
                class="mt-auto w-full py-2 rounded-xl border border-purple-500/40 bg-purple-500/10 text-purple-400 text-sm font-semibold hover:bg-purple-500/25 transition-colors duration-200 inline-flex items-center justify-center gap-2">
                Lihat Detail
                <i class="fas fa-arrow-right text-xs"></i>
            </a>
        </div>

        @foreach($highlightProgramCategories as $programCategory)
        <div class="group glass-card-light rounded-2xl p-6 flex flex-col items-center text-center gap-4 transition-all duration-500 hover:-translate-y-2 hover:shadow-[0_0_25px_rgba(109,40,217,0.2)] hover:border-purple-500/50">
            <div class="w-14 h-14 rounded-2xl bg-purple-500/15 border border-purple-500/30 flex items-center justify-center text-2xl transition-transform duration-300 group-hover:scale-110">
                <i class="fa {{ $programHighlightIcons[($loop->index) % count($programHighlightIcons)] }} text-purple-300"></i>
            </div>
            <div>
                <h3 class="text-white font-bold text-lg mb-2 group-hover:text-purple-300 transition-colors duration-300">
                    {{ $programCategory->name }}
                </h3>
                <p class="text-gray-400 text-sm leading-relaxed">
                    {{ \Illuminate\Support\Str::limit($programCategory->resolved_description, 90) }}
                </p>
            </div>
            <a href="{{ route('landing.program') }}#program-category-{{ $programCategory->slug }}"
                class="mt-auto w-full py-2 rounded-xl border border-purple-500/40 bg-purple-500/10 text-purple-400 text-sm font-semibold hover:bg-purple-500/25 transition-colors duration-200 inline-flex items-center justify-center gap-2">
                Lihat Detail
                <i class="fas fa-arrow-right text-xs"></i>
            </a>
        </div>
        @endforeach
    </div>
</section>
