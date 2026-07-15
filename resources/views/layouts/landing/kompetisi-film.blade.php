@php
    $showCompetitionSubmittedStat = $showCompetitionSubmittedStat ?? false;
@endphp

<!-- ================================================== -->
<!-- SECTION: KOMPETISI FILM -->
<!-- ================================================== -->
<section id="competition-section" class="max-w-7xl mx-auto px-6 md:px-10 py-24 md:py-28 competition-section">
    <div class="fade-up">
        <p class="text-purple-400 text-sm md:text-base uppercase tracking-wider font-semibold mb-2">
            FILM COMPETITION
        </p>
        <h2 class="text-3xl md:text-5xl font-bold text-left border-l-8 border-purple-500 pl-6 tracking-tight">
            Kompetisi Film
        </h2>
    </div>

    <div class="glass-card mt-12 rounded-3xl p-6 md:p-8 lg:p-10 fade-up transition-all duration-500 hover:shadow-[0_0_30px_rgba(109,40,217,0.2)]">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            @forelse(($competitionCategories ?? collect()) as $category)
            <div class="group competition-card transition-all duration-500">
                <div class="glass-card-light rounded-2xl overflow-hidden transition-all duration-500 group-hover:scale-[1.03] group-hover:-translate-y-2 group-hover:shadow-[0_0_25px_#6D28D9] h-full flex flex-col">
                    <div class="relative w-full h-56 overflow-hidden bg-gradient-to-br from-purple-900/50 to-black/50">
                        <img
                            src="{{ $category->image_url }}"
                            alt="{{ $category->name }}"
                            class="w-full h-full object-cover object-top transition-transform duration-700 group-hover:scale-110" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent"></div>
                        <div class="absolute top-4 right-4 bg-purple-600/80 backdrop-blur-sm px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider">
                            {{ $category->name }}
                        </div>
                    </div>
                    <div class="p-6 flex flex-col flex-grow">
                        <h3 class="text-xl md:text-2xl font-bold text-white mb-2 group-hover:text-purple-300 transition-colors duration-300">
                            {{ $category->name }}
                        </h3>
                        <p style="text-align: justify;" class="text-gray-300 text-sm leading-relaxed mb-6">
                            {!! nl2br(e($category->resolved_summary)) !!}
                        </p>
                        <div class="mt-auto">
                            <a href="{{ $category->resolved_detail_route !== '#' ? url($category->resolved_detail_route) : '#' }}"
                                class="competition-btn inline-flex items-center gap-2 text-purple-400 font-semibold text-sm group-hover:text-purple-300 transition-all duration-300 group-hover:gap-3 {{ $category->resolved_detail_route === '#' ? 'pointer-events-none opacity-50' : '' }}">
                                Lihat Detail <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="lg:col-span-3 glass-card-light rounded-2xl p-8 text-center text-gray-400">
                Kategori kompetisi belum tersedia.
            </div>
            @endforelse
        </div>

        @if($showCompetitionSubmittedStat)
        <div class="mt-12 pt-10 border-t border-purple-500/20">
            <div class="text-center space-y-4">
                <div class="text-5xl md:text-7xl font-black bg-gradient-to-r from-purple-400 via-fuchsia-400 to-purple-300 bg-clip-text text-transparent">
                    <span class="counter counter-value" data-target="{{ $competitionFilmSubmittedStatValue ?? 0 }}">{{ $competitionFilmSubmittedStatValue ?? 0 }}</span>+
                </div>
                <p class="text-gray-300 text-sm md:text-base uppercase tracking-wider font-medium">
                    Film Submitted
                </p>
                <div class="flex justify-center mt-6">
                    <div class="w-20 h-[2px] bg-gradient-to-r from-transparent via-purple-500 to-transparent"></div>
                </div>
            </div>
        </div>
        @endif
    </div>
</section>
