@props([
    'eyebrow' => null,
    'title' => null,
    'subtitle' => null,
])

<section {{ $attributes->merge(['class' => 'max-w-7xl mx-auto px-6 md:px-10 py-16 md:py-20']) }}>
    @if ($eyebrow || $title || $subtitle)
        <div class="fade-up mb-8 md:mb-10">
            @if ($eyebrow)
                <p class="text-purple-400 text-sm md:text-base uppercase tracking-wider font-semibold mb-2">
                    {{ $eyebrow }}
                </p>
            @endif

            @if ($title)
                <h2 class="text-3xl md:text-5xl font-bold text-left border-l-8 border-purple-500 pl-6 tracking-tight">
                    {{ $title }}
                </h2>
            @endif

            @if ($subtitle)
                <p class="text-gray-300 text-base md:text-lg mt-4 max-w-3xl leading-relaxed">
                    {{ $subtitle }}
                </p>
            @endif
        </div>
    @endif

    <div class="glass-card rounded-3xl p-6 md:p-8 fade-up transition-all duration-500 hover:shadow-[0_0_30px_rgba(109,40,217,0.2)]">
        {{ $slot }}
    </div>
</section>