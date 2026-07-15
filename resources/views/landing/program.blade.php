@extends('layouts.landing.master')

@section('main')
@php
    $landingSetting = $activeLandingSetting ?? $setting ?? null;
@endphp

<main class="relative z-10">
    <section
        id="program"
        class="relative min-h-[90vh] flex items-center justify-center px-6 py-20 overflow-hidden bg-cover bg-center"
        style="background-image: url({{ $landingSetting ? $landingSetting->mediaUrl($landingSetting->hero_image, 'landing/images/BACKGROUND FFH 2026.png') : asset('landing/images/BACKGROUND FFH 2026.png') }});">
        <div class="absolute inset-0 bg-black/50"></div>
        <div class="absolute inset-0 premium-glow opacity-40"></div>
        <div class="bg-glow-abstract"></div>
        <div class="relative max-w-6xl w-full mx-auto fade-up">
            <div class="glass-card p-8 md:p-12 lg:p-16 text-center shadow-2xl border border-purple-400/30">
                <div class="space-y-6 md:space-y-8">
                    <h1 class="text-5xl md:text-7xl lg:text-8xl font-black uppercase tracking-tighter leading-[1.1] bg-gradient-to-r from-white via-purple-200 to-purple-300 bg-clip-text text-transparent drop-shadow-2xl">
                        KOMPETISI FILM
                    </h1>
                    <p class="text-gray-300 text-base md:text-xl max-w-2xl mx-auto font-light leading-relaxed">
                        Timeline kompetisi, kategori festival, dan tim penilai disiapkan untuk membantu peserta membaca keseluruhan alur program FFH 2026.
                    </p>
                </div>
            </div>
        </div>
    </section>

    @include('layouts.landing.timeline-kompetisi-film', ['timelineItems' => $timelineItems])
    @include('layouts.landing.kompetisi-film', [
        'competitionCategories' => $competitionCategories,
        'showCompetitionSubmittedStat' => false,
    ])

    <section class="max-w-7xl mx-auto px-6 md:px-10 py-24 md:py-28">
        <div class="fade-up">
            <h2 class="text-3xl md:text-5xl font-bold text-left border-l-8 border-purple-500 pl-6 mb-16 tracking-tight">
                Juri
            </h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-10">
            @forelse(collect($juryMembers ?? [])->take(3) as $jury)
            <div class="board-card glass-card-light rounded-2xl overflow-hidden transition-all text-center duration-300 group">
                <div class="overflow-hidden relative">
                    <img
                        src="{{ asset('landing/images/user.png') }}"
                        alt="{{ $jury->name }}"
                        class="w-full h-120 object-cover transition duration-500 group-hover:scale-110" />
                    <div class="absolute top-4 right-4 bg-purple-600/80 backdrop-blur-sm px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider">
                        {{ $jury->category->name ?? 'Lintas Kategori' }}
                    </div>
                </div>
                <div class="p-6 space-y-3">
                    <h3 class="text-2xl font-bold tracking-tight text-white">
                        {{ strtoupper($jury->name) }}
                    </h3>
                    <p class="text-purple-300 text-sm uppercase tracking-wider font-semibold">
                        {{ $jury->category->name ?? 'Juri Festival' }}
                    </p>
                </div>
            </div>
            @empty
            <div class="lg:col-span-3 glass-card-light rounded-2xl p-8 text-center text-gray-400">
                User dengan role juri belum tersedia.
            </div>
            @endforelse
        </div>
    </section>

    @include('landing.partials.program-space-sections', ['landingSetting' => $landingSetting])

    @include('landing.partials.program-faq')
</main>
@endsection
