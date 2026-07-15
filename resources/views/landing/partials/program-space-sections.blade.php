@php
    $programSectionBackground = $landingSetting
        ? $landingSetting->mediaUrl($landingSetting->theme_image ?: $landingSetting->hero_image, 'landing/images/BACKGROUND FFH 2026.png')
        : asset('landing/images/BACKGROUND FFH 2026.png');
@endphp

@forelse(($programCategories ?? collect()) as $programCategory)
<section
    id="program-category-{{ $programCategory->slug }}"
    class="relative min-h-[90vh] flex items-center justify-center px-6 py-20 overflow-hidden bg-cover bg-center"
    style="background-image: url({{ $programSectionBackground }});">
    <div class="absolute inset-0 bg-black/70"></div>
    <div class="absolute inset-0 premium-glow opacity-40"></div>
    <div class="bg-glow-abstract"></div>

    <div class="relative max-w-7xl w-full mx-auto">
        <div class="relative max-w-6xl w-full mx-auto fade-up">
            <div class="glass-card p-8 md:p-12 lg:p-16 text-center shadow-2xl border border-purple-400/30">
                <div class="space-y-6 md:space-y-8">
                    <h2 class="text-5xl md:text-7xl lg:text-8xl font-black uppercase tracking-tighter leading-[1.1] bg-gradient-to-r from-white via-purple-200 to-purple-300 bg-clip-text text-transparent drop-shadow-2xl">
                        {{ $programCategory->name }}
                    </h2>
                    <p class="text-gray-300 text-base md:text-xl max-w-2xl mx-auto font-light leading-relaxed">
                        {{ $programCategory->resolved_description }}
                    </p>
                </div>
            </div>

            @if($programCategory->programs->take(3)->isNotEmpty())
            <div class="mt-10 flex justify-center">
                <a
                    href="#program-list-{{ $programCategory->slug }}"
                    class="group w-14 h-14 rounded-full glass-card-light flex items-center justify-center border border-purple-500/30"
                    aria-label="Lihat program {{ strtolower($programCategory->name) }}">
                    <i class="fas fa-chevron-down text-purple-400 text-xl animate-bounce group-hover:text-purple-300 transition-all duration-300"></i>
                </a>
            </div>
            @endif
        </div>

        <div id="program-list-{{ $programCategory->slug }}" class="mt-10 glass-card rounded-3xl p-6 md:p-8 lg:p-10 fade-up">
            @if($programCategory->programs->take(3)->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 lg:gap-8">
                @foreach($programCategory->programs->take(3) as $program)
                    @include('landing.partials.program-card', [
                        'program' => $program,
                        'showProgramCategory' => false,
                    ])
                @endforeach
            </div>
            <div class="mt-10 flex justify-center md:justify-end">
                <a href="{{ route('programs.index', ['category' => $programCategory->slug]) }}"
                    class="btn-gradient inline-flex items-center gap-2 px-6 py-3 rounded-full text-white font-semibold transition-all duration-300 hover:gap-3">
                    Lihat Semua
                    <i class="fas fa-arrow-right text-sm"></i>
                </a>
            </div>
            @else
            <div class="glass-card-light rounded-2xl p-8 text-center text-gray-400">
                Program untuk kategori ini belum tersedia.
            </div>
            @endif
        </div>
    </div>
</section>
@empty
<section class="max-w-7xl mx-auto px-6 md:px-10 py-16 md:py-20">
    <div class="glass-card rounded-3xl p-8 text-center text-gray-400 fade-up">
        Kategori program belum tersedia.
    </div>
</section>
@endforelse
