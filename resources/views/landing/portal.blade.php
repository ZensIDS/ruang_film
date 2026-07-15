@extends('layouts.landing.master')

@section('main')
    @php
        $landingSetting = $activeLandingSetting ?? ($setting ?? null);
    @endphp

    <main class="relative z-10">
        <section id="program"
            class="relative min-h-[90vh] flex items-center justify-center px-6 py-20 overflow-hidden bg-cover bg-center"
            style="background-image: url({{ $landingSetting ? $landingSetting->mediaUrl($landingSetting->hero_image, 'landing/images/BACKGROUND FFH 2026.png') : asset('landing/images/BACKGROUND FFH 2026.png') }});">
            <div class="absolute inset-0 bg-black/50"></div>
            <div class="absolute inset-0 premium-glow opacity-40"></div>
            <div class="bg-glow-abstract"></div>
            <div class="relative max-w-6xl w-full mx-auto fade-up">
                <div class="glass-card p-8 md:p-12 lg:p-16 text-center shadow-2xl border border-purple-400/30">
                    <div class="space-y-6 md:space-y-8">
                        <h1
                            class="text-5xl md:text-7xl lg:text-8xl font-black uppercase tracking-tighter leading-[1.1] bg-gradient-to-r from-white via-purple-200 to-purple-300 bg-clip-text text-transparent drop-shadow-2xl">
                            PORTAL BERITA
                        </h1>
                        <p class="text-gray-300 text-base md:text-xl max-w-2xl mx-auto font-light leading-relaxed">
                            Kumpulan berita dan informasi seputar program Festival Film Horor.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        @include('landing.partials.portal-berita-sections', [
            'landingSetting' => $landingSetting,
            'portalPrograms' => $portalPrograms,
        ])
    </main>
@endsection
