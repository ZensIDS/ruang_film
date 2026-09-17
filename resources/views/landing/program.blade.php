@extends('layouts.landing.master')

@section('main')
@php
    $landingSetting = $activeLandingSetting ?? $setting ?? null;
@endphp

@push('styles')
<style>
    .jury-mystery-pulse {
        animation: juryMysteryPulse 2.4s ease-in-out infinite;
    }

    @keyframes juryMysteryPulse {
        0%, 100% {
            opacity: 0.55;
            transform: scale(1);
            filter: drop-shadow(0 0 6px rgba(168, 85, 247, 0.35));
        }
        50% {
            opacity: 1;
            transform: scale(1.08);
            filter: drop-shadow(0 0 18px rgba(168, 85, 247, 0.65));
        }
    }

    .jury-mystery-card {
        position: relative;
    }

    .jury-mystery-card::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 1rem;
        border: 1px solid rgba(168, 85, 247, 0.15);
        pointer-events: none;
        animation: juryMysteryBorder 2.4s ease-in-out infinite;
    }

    @keyframes juryMysteryBorder {
        0%, 100% {
            border-color: rgba(168, 85, 247, 0.15);
        }
        50% {
            border-color: rgba(168, 85, 247, 0.45);
        }
    }
</style>
@endpush

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

    <div class="js-accordion-item">
        <div class="flex justify-center pb-10 md:pb-14">
            <button
                type="button"
                class="js-accordion-toggle group inline-flex items-center gap-3 glass-card-light px-6 py-3 rounded-full border border-purple-500/30 cursor-pointer transition-all duration-300 hover:shadow-[0_0_15px_rgba(109,40,217,0.4)]"
                aria-expanded="false"
                aria-controls="program-competition-details"
                aria-label="Lihat detail kompetisi film">
                {{-- <span class="js-accordion-label text-purple-300 font-semibold text-sm md:text-base group-hover:text-purple-200 transition-colors duration-300">
                    Lihat Timeline, Kategori &amp; Juri Kompetisi
                </span> --}}
                <div class="js-accordion-icon">
                    <i class="fas fa-chevron-down text-purple-400 text-sm transition-all duration-300 group-hover:text-purple-300"></i>
                </div>
            </button>
        </div>

        <div id="program-competition-details" class="js-accordion-panel max-h-0 opacity-0 overflow-hidden transition-all duration-500 ease-in-out">
            <x-program-accordion
                id="competition-section"
                eyebrow="FILM COMPETITION"
                title="Kompetisi Film"
                subtitle="Timeline, kategori kompetisi, dan tim juri FFH 2026 dalam satu alur lengkap.">

                <div class="mb-4">
                    <h3 class="text-xl md:text-2xl font-bold text-white border-l-4 border-purple-500 pl-4 mb-6">
                        Timeline Kompetisi Film
                    </h3>
                    @include('layouts.landing.timeline-kompetisi-film', ['timelineItems' => $timelineItems, 'hideHeader' => true])
                </div>

                <div class="mt-12 pt-10 border-t border-purple-500/20">
                    <h3 class="text-xl md:text-2xl font-bold text-white border-l-4 border-purple-500 pl-4 mb-6">
                        Kompetisi Film
                    </h3>
                    @include('layouts.landing.kompetisi-film', [
                        'competitionCategories' => $competitionCategories,
                        'showCompetitionSubmittedStat' => false,
                        'hideHeader' => true,
                    ])
                </div>

                <div class="mt-12 pt-10 border-t border-purple-500/20">
                    <h3 class="text-xl md:text-2xl font-bold text-white border-l-4 border-purple-500 pl-4 mb-8">
                        Juri
                    </h3>

                    @forelse(($juryCategories ?? collect()) as $juryCategory)
                        <div class="jury-category-row {{ !$loop->last ? 'mb-10' : '' }}">
                            <div class="flex items-center justify-between gap-4 mb-4">
                                <h4 class="text-lg md:text-xl font-semibold text-purple-300">
                                    {{ $juryCategory->name }}
                                </h4>
                                <div class="hidden md:flex items-center gap-2 flex-shrink-0">
                                    <button type="button" class="jury-slider-prev w-9 h-9 rounded-full glass-card-light flex items-center justify-center border border-purple-500/30 cursor-pointer transition-all duration-300 hover:shadow-[0_0_10px_rgba(109,40,217,0.4)]" aria-label="Sebelumnya">
                                        <i class="fas fa-chevron-left text-purple-400 text-xs"></i>
                                    </button>
                                    <button type="button" class="jury-slider-next w-9 h-9 rounded-full glass-card-light flex items-center justify-center border border-purple-500/30 cursor-pointer transition-all duration-300 hover:shadow-[0_0_10px_rgba(109,40,217,0.4)]" aria-label="Berikutnya">
                                        <i class="fas fa-chevron-right text-purple-400 text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="jury-slider flex gap-5 overflow-x-auto pb-3 snap-x snap-mandatory scroll-smooth" style="scrollbar-width: thin;">
                                @forelse($juryCategory->juries as $jury)
                                <div class="board-card glass-card-light rounded-2xl overflow-hidden transition-all text-center duration-300 group flex-shrink-0 snap-start w-[80%] sm:w-[45%] md:w-[calc((100%-40px)/3)]">
                                    <div class="overflow-hidden relative">
                                        <img
                                            src="{{ $jury->photo_url }}"
                                            alt="{{ $jury->name }}"
                                            class="w-full h-64 object-cover transition duration-500 group-hover:scale-110" />
                                    </div>
                                    <div class="p-5 space-y-1">
                                        <h3 class="text-base md:text-lg font-bold tracking-tight text-white">
                                            {{ strtoupper($jury->name) }}
                                        </h3>
                                        @if($jury->title)
                                        <p class="text-purple-300 text-xs md:text-sm uppercase tracking-wider font-semibold">
                                            {{ $jury->title }}
                                        </p>
                                        @endif
                                    </div>
                                </div>
                                @empty
                                    @for($i = 0; $i < 3; $i++)
                                    <div class="jury-mystery-card glass-card-light rounded-2xl overflow-hidden transition-all text-center duration-300 group flex-shrink-0 snap-start w-[80%] sm:w-[45%] md:w-[calc((100%-40px)/3)] relative">
                                        <div class="relative h-64 overflow-hidden bg-gradient-to-br from-purple-950 via-black to-purple-900 flex items-center justify-center">
                                            <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(circle at 50% 30%, rgba(168,85,247,0.5), transparent 60%);"></div>
                                            <i class="fas fa-user-secret text-purple-400/70 text-5xl jury-mystery-pulse"></i>
                                            <div class="absolute inset-0 shadow-[inset_0_0_40px_rgba(0,0,0,0.6)]"></div>
                                        </div>
                                        <div class="p-5 space-y-1">
                                            <h3 class="text-base md:text-lg font-bold tracking-tight text-purple-200/90">
                                                JURI
                                            </h3>
                                            <p class="text-gray-400 text-xs md:text-sm uppercase tracking-wider font-semibold">
                                                Cooming Soon
                                            </p>
                                        </div>
                                    </div>
                                    @endfor
                                @endforelse
                            </div>
                        </div>
                    @empty
                        <div class="glass-card-light rounded-2xl p-8 text-center text-gray-400">
                            Data juri belum tersedia.
                        </div>
                    @endforelse
                </div>

                <div class="mt-12 pt-10 border-t border-purple-500/20">
                    <h3 class="text-xl md:text-2xl font-bold text-white border-l-4 border-purple-500 pl-4 mb-8">
                        Kurator
                    </h3>

                    <div class="flex items-center justify-end gap-4 mb-4">
                        <div class="hidden md:flex items-center gap-2 flex-shrink-0">
                            <button type="button" class="curator-slider-prev w-9 h-9 rounded-full glass-card-light flex items-center justify-center border border-purple-500/30 cursor-pointer transition-all duration-300 hover:shadow-[0_0_10px_rgba(109,40,217,0.4)]" aria-label="Sebelumnya">
                                <i class="fas fa-chevron-left text-purple-400 text-xs"></i>
                            </button>
                            <button type="button" class="curator-slider-next w-9 h-9 rounded-full glass-card-light flex items-center justify-center border border-purple-500/30 cursor-pointer transition-all duration-300 hover:shadow-[0_0_10px_rgba(109,40,217,0.4)]" aria-label="Berikutnya">
                                <i class="fas fa-chevron-right text-purple-400 text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <div class="curator-slider flex gap-5 overflow-x-auto pb-3 snap-x snap-mandatory scroll-smooth" style="scrollbar-width: thin;">
                        @forelse(($curators ?? collect()) as $curator)
                        <div class="board-card glass-card-light rounded-2xl overflow-hidden transition-all text-center duration-300 group flex-shrink-0 snap-start w-[80%] sm:w-[45%] md:w-[calc((100%-40px)/3)]">
                            <div class="overflow-hidden relative">
                                <img
                                    src="{{ $curator->photo_url }}"
                                    alt="{{ $curator->name }}"
                                    class="w-full h-64 object-cover transition duration-500 group-hover:scale-110" />
                            </div>
                            <div class="p-5 space-y-1">
                                <h3 class="text-base md:text-lg font-bold tracking-tight text-white">
                                    {{ strtoupper($curator->name) }}
                                </h3>
                                @if($curator->title)
                                <p class="text-purple-300 text-xs md:text-sm uppercase tracking-wider font-semibold">
                                    {{ $curator->title }}
                                </p>
                                @endif
                            </div>
                        </div>
                        @empty
                            @for($i = 0; $i < 3; $i++)
                            <div class="jury-mystery-card glass-card-light rounded-2xl overflow-hidden transition-all text-center duration-300 group flex-shrink-0 snap-start w-[80%] sm:w-[45%] md:w-[calc((100%-40px)/3)] relative">
                                <div class="relative h-64 overflow-hidden bg-gradient-to-br from-purple-950 via-black to-purple-900 flex items-center justify-center">
                                    <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(circle at 50% 30%, rgba(168,85,247,0.5), transparent 60%);"></div>
                                    <i class="fas fa-user-secret text-purple-400/70 text-5xl jury-mystery-pulse"></i>
                                    <div class="absolute inset-0 shadow-[inset_0_0_40px_rgba(0,0,0,0.6)]"></div>
                                </div>
                                <div class="p-5 space-y-1">
                                    <h3 class="text-base md:text-lg font-bold tracking-tight text-purple-200/90">
                                        KURATOR
                                    </h3>
                                    <p class="text-gray-400 text-xs md:text-sm uppercase tracking-wider font-semibold">
                                        Cooming Soon
                                    </p>
                                </div>
                            </div>
                            @endfor
                        @endforelse
                    </div>
                </div>
            </x-program-accordion>
        </div>
    </div>

    @include('landing.partials.program-space-sections', ['landingSetting' => $landingSetting])

    @include('landing.partials.program-faq')
</main>

@once
    <script>
        (function () {
            function setIconState(toggle, isOpen) {
                var iconWrap = toggle.querySelector('.js-accordion-icon');
                if (!iconWrap) return;

                var icon = iconWrap.querySelector('i');

                if (icon && (icon.classList.contains('fa-plus') || icon.classList.contains('fa-minus'))) {
                    icon.classList.toggle('fa-plus', !isOpen);
                    icon.classList.toggle('fa-minus', isOpen);
                    return;
                }

                // ikon chevron -> putar 180deg saat terbuka
                iconWrap.classList.toggle('rotate-180', isOpen);
            }

            function setLabelState(toggle, isOpen) {
                var label = toggle.querySelector('.js-accordion-label');
                if (!label) return;

                if (!label.dataset.openText) {
                    label.dataset.closedText = label.textContent.trim();
                    label.dataset.openText = 'Tutup Timeline, Kategori & Juri Kompetisi';
                }

                label.textContent = isOpen ? label.dataset.openText : label.dataset.closedText;
            }

            function closePanel(item, toggle, panel) {
                panel.style.maxHeight = '0px';
                panel.classList.add('opacity-0');
                panel.classList.remove('opacity-100');
                item.classList.remove('is-open');
                toggle.setAttribute('aria-expanded', 'false');
                setIconState(toggle, false);
                setLabelState(toggle, false);
            }

            function openPanel(item, toggle, panel) {
                panel.style.maxHeight = panel.scrollHeight + 'px';
                panel.classList.remove('opacity-0');
                panel.classList.add('opacity-100');
                item.classList.add('is-open');
                toggle.setAttribute('aria-expanded', 'true');
                setIconState(toggle, true);
                setLabelState(toggle, true);
            }

            document.addEventListener('click', function (e) {
                var toggle = e.target.closest('.js-accordion-toggle');
                if (!toggle) return;

                var item = toggle.closest('.js-accordion-item');
                if (!item) return;

                var panel = item.querySelector('.js-accordion-panel');
                if (!panel) return;

                if (item.classList.contains('is-open')) {
                    closePanel(item, toggle, panel);
                } else {
                    openPanel(item, toggle, panel);
                }
            });

            // Jaga tinggi panel yang sedang terbuka tetap pas saat ukuran layar/isi berubah
            window.addEventListener('resize', function () {
                document.querySelectorAll('.js-accordion-item.is-open .js-accordion-panel').forEach(function (panel) {
                    panel.style.maxHeight = panel.scrollHeight + 'px';
                });
            });

            // Slider tombol prev/next untuk tiap baris kategori juri
            document.addEventListener('click', function (e) {
                var prevBtn = e.target.closest('.jury-slider-prev');
                var nextBtn = e.target.closest('.jury-slider-next');
                var btn = prevBtn || nextBtn;
                if (!btn) return;

                var row = btn.closest('.jury-category-row');
                if (!row) return;

                var slider = row.querySelector('.jury-slider');
                if (!slider) return;

                var scrollAmount = slider.clientWidth * 0.8;
                slider.scrollBy({
                    left: prevBtn ? -scrollAmount : scrollAmount,
                    behavior: 'smooth',
                });
            });

            // Slider tombol prev/next untuk baris kurator
            document.addEventListener('click', function (e) {
                var prevBtn = e.target.closest('.curator-slider-prev');
                var nextBtn = e.target.closest('.curator-slider-next');
                var btn = prevBtn || nextBtn;
                if (!btn) return;

                var slider = document.querySelector('.curator-slider');
                if (!slider) return;

                var scrollAmount = slider.clientWidth * 0.8;
                slider.scrollBy({
                    left: prevBtn ? -scrollAmount : scrollAmount,
                    behavior: 'smooth',
                });
            });
        })();
    </script>
@endonce
@endsection