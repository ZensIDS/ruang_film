@extends('layouts.landing.master')

@section('main')
@php
    $heroImage = $landingSetting
        ? $landingSetting->mediaUrl($landingSetting->hero_image, 'landing/images/BACKGROUND FFH 2026.png')
        : asset('landing/images/BACKGROUND FFH 2026.png');
@endphp

<main class="relative z-10">
    {{-- HERO --}}
    <section
        class="relative min-h-[70vh] flex items-center justify-center px-6 py-24 overflow-hidden bg-cover bg-center"
        style="background-image: url('{{ $heroImage }}');">
        <div class="absolute inset-0 bg-black/60"></div>
        <div class="absolute inset-0 premium-glow opacity-40"></div>
        <div class="bg-glow-abstract"></div>

        <div class="relative max-w-4xl w-full mx-auto text-center fade-up">
            <div class="inline-flex items-center gap-2 bg-purple-600/20 border border-purple-400/40 backdrop-blur-sm rounded-full px-5 py-2 mb-6">
                <i class="fas fa-calendar-check text-purple-300"></i>
                <span class="text-purple-200 text-xs md:text-sm font-semibold uppercase tracking-widest">
                    Konfirmasi Kehadiran
                </span>
            </div>
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-black uppercase tracking-tighter leading-[1.05] bg-gradient-to-r from-white via-purple-200 to-purple-300 bg-clip-text text-transparent drop-shadow-2xl">
                Festival Film Horor 2026
            </h1>
            <p class="text-gray-300 text-base md:text-xl max-w-2xl mx-auto font-light leading-relaxed mt-6">
            Submission karya sudah ditutup — terima kasih untuk semua yang sudah berpartisipasi. 
            Di bawah ini adalah rangkaian agenda kegiatan FFH 2026 sampai malam puncak penghargaan. 
            Bagi peserta yang lolos, silakan dapat mengisi form konfirmasi kehadiran di bawah ini.
            </p>
        </div>
    </section>

    {{-- TIMELINE --}}
    @include('layouts.landing.timeline-kompetisi-film', ['timelineItems' => $timelineItems])

    {{-- CLOSING / KEMBALI --}}
    <section class="max-w-4xl mx-auto px-6 md:px-10 pb-24 md:pb-28">
        <div class="glass-card rounded-2xl p-8 md:p-12 text-center border border-purple-400/30 fade-up">
            <i class="fas fa-clapperboard text-purple-400 text-3xl mb-4"></i>
            <h2 class="text-2xl md:text-3xl font-bold text-white mb-3">Sampai Jumpa di FFH 2026!</h2>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ auth()->check() ? route('dashboard') : route('landing.home') }}"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-purple-600 to-purple-500 hover:from-purple-500 hover:to-purple-400 text-white font-semibold px-6 py-3 rounded-full transition shadow-lg shadow-purple-900/40">
                    <i class="fas fa-arrow-left"></i>
                    {{ auth()->check() ? 'Kembali ke Dashboard' : 'Kembali ke Beranda' }}
                </a>
                <a href="#" target="_blank"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-green-600 to-green-500 hover:from-green-500 hover:to-green-400 text-white font-semibold px-6 py-3 rounded-full transition shadow-lg shadow-green-900/40">
                    <i class="fas fa-calendar-check"></i>
                    Konfirmasi Kehadiran
                </a>
            </div>
        </div>
    </section>
</main>
@endsection