@extends('layouts.landing.master')

@section('main')
@php
    $landingSetting = $activeLandingSetting ?? $setting ?? null;
    $heroTitle = $landingSetting?->hero_title ?? "FESTIVAL FILM\nHOROR 2026";
    $heroDescription = $landingSetting?->hero_description ?? 'Horor tidak selalu hadir sebagai sosok yang menakutkan. Ia hidup dalam ingatan, kepercayaan, trauma, dan pengalaman yang diwariskan dari generasi ke generasi.';
    $aboutTitle = $landingSetting?->about_title ?? 'Festival Film Horor 2026';
    $aboutDescription = $landingSetting?->about_description ?? 'Festival Film Horor (FFH) merupakan apresiasi, edukasi, ruang dan pengembangan ekosistem sinema horor di Indonesia. Festival ini hadir untuk membuka ruang diskusi yang lebih luas mengenai horor sebagai medium yang merekam pengalaman manusia, ingatan kolektif, budaya lokal, hingga berbagai persoalan sosial yang hidup di tengah masyarakat.';
    $aboutSecondary = $landingSetting?->about_description_secondary ?? 'Melalui pemutaran film, kompetisi, diskusi, pameran, lokakarya, dan program publik lainnya, FFH berupaya mempertemukan pembuat film, akademisi, komunitas, pelajar, serta publik dalam satu ruang yang inklusif dan kolaboratif.';
    $themeTitle = $landingSetting?->theme_title ?? 'Tema Festival Film Horor 2026';
    $themeName = $landingSetting?->theme_name ?? 'INDIGO';
    $themeQuote = $landingSetting?->theme_quote ?? '"Melihat yang tak terlihat. Membaca yang terlupakan. Membangun yang akan datang."';
    $themeParagraphs = preg_split("/(\r\n|\n|\r){2,}/", trim($landingSetting?->theme_description ?? ''));
    $lastYearTitle = $landingSetting?->last_year_title ?? 'A New Horror Experience';
    $lastYearDescription = $landingSetting?->last_year_description ?? 'Festival Film Horor Pacitan 2025 menjadi langkah awal hadirnya platform horor berbasis kelokalan di Indonesia.';
    $lastYearCatalogHref = route('download.ekatalog');
    $lastYearCatalogLabel = $landingSetting?->last_year_catalog_label ?? 'Download Katalog Festival';
    $hasClosedLandingPeriod = $landingSetting && !$submissionOpen && !$isBeforeOpen;
    $showLandingProgramSections = !$hasClosedLandingPeriod;
    $hasManualFeaturedFilms = collect($landingSetting?->last_year_featured_film_ids ?: [])->filter()->isNotEmpty();
    $featuredFilmCount = collect($lastYearFilms ?? [])->count();
    $winnerCategoryCount = collect($winnerGroups ?? [])->count();
    $winnerFilmCount = collect($winnerGroups ?? [])->sum(function ($group) {
        return collect(data_get($group, 'films', []))->count();
    });
@endphp

<main class="relative z-10">
    <section
        id="home"
        class="relative min-h-[90vh] flex items-center justify-center px-6 py-20 overflow-hidden bg-cover bg-center"
        style="background-image: url({{ $landingSetting ? $landingSetting->mediaUrl($landingSetting->hero_image, 'landing/images/BACKGROUND FFH 2026.png') : asset('landing/images/BACKGROUND FFH 2026.png') }});">
        <div class="absolute inset-0 bg-black/60"></div>
        <div class="absolute inset-0 premium-glow opacity-40"></div>
        <div class="bg-glow-abstract"></div>

        <div class="relative max-w-6xl w-full mx-auto fade-up">
            <div class="glass-card p-8 md:p-12 lg:p-16 text-center shadow-2xl border border-purple-400/30">
                <div class="space-y-6 md:space-y-8">
                    <h1 class="text-5xl md:text-7xl lg:text-8xl font-black uppercase tracking-tighter leading-[1.1] bg-gradient-to-r from-white via-purple-200 to-purple-300 bg-clip-text text-transparent drop-shadow-2xl">
                        {!! nl2br(e($heroTitle)) !!}
                    </h1>
                    <p class="text-gray-300 text-base md:text-xl max-w-2xl mx-auto font-light leading-relaxed">
                        {{ $heroDescription }}
                    </p>

                    <div>
                        @if($submissionOpen)
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="{{ route('register', ['role' => 'peserta']) }}"
                                class="btn-gradient px-8 md:px-10 py-3 md:py-4 rounded-full text-white font-semibold text-base md:text-lg transition-all duration-300 hover:scale-105 hover:shadow-[0_0_25px_#8B5CF6] inline-flex items-center gap-3 group">
                                <i class="fas fa-upload text-white group-hover:translate-y-[-2px] transition-transform duration-300"></i>
                                Submit Film Sekarang
                            </a>
                            <a href="#competition-section"
                                class="btn-gradient px-8 md:px-10 py-3 md:py-4 rounded-full text-white font-semibold text-base justify-center md:text-lg transition-all duration-300 hover:scale-105 hover:shadow-[0_0_25px_#8B5CF6] inline-flex items-center gap-3 group">
                                <i class="fas fa-download text-white group-hover:translate-y-[-2px] transition-transform duration-300"></i>
                                Unduh Juknis
                            </a>
                        </div>
                        @if($landingSetting)
                        <p class="text-gray-300 text-xs mt-4"><b>DITUTUP DALAM</b></p>
                        <div id="countdown-close" class="flex gap-3 mt-2 justify-center">
                            @foreach(['cc-days' => 'HARI', 'cc-hours' => 'JAM', 'cc-minutes' => 'MENIT', 'cc-seconds' => 'DETIK'] as $id => $label)
                            <div class="text-center">
                                <div id="{{ $id }}" class="text-white font-bold text-xl bg-white/10 rounded-lg px-3 py-2 min-w-[48px]">--</div>
                                <div class="text-gray-500 text-[10px] mt-1">{{ $label }}</div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                        @else
                        <button
                            disabled
                            class="px-8 md:px-10 py-3 md:py-4 rounded-full text-gray-400 font-semibold text-base md:text-lg inline-flex items-center gap-3 cursor-not-allowed bg-white/5 border border-white/10">
                            <i class="fas fa-lock text-gray-500"></i>
                            Submission Ditutup
                        </button>
                        @if($landingSetting && now()->lessThan($landingSetting->open_at))
                        <p class="text-gray-300 text-xs mt-4"><b>DIBUKA DALAM</b></p>
                        <div id="countdown-open" class="flex gap-3 mt-2 justify-center">
                            @foreach(['co-days' => 'HARI', 'co-hours' => 'JAM', 'co-minutes' => 'MENIT', 'co-seconds' => 'DETIK'] as $id => $label)
                            <div class="text-center">
                                <div id="{{ $id }}" class="text-white font-bold text-xl bg-white/10 rounded-lg px-3 py-2 min-w-[48px]">--</div>
                                <div class="text-gray-500 text-[10px] mt-1">{{ $label }}</div>
                            </div>
                            @endforeach
                        </div>
                        @elseif($landingSetting)
                        <p class="text-gray-500 text-xs mt-4">
                            *Pendaftaran telah ditutup sejak {{ $landingSetting->close_at->translatedFormat('d F Y') }} pukul {{ $landingSetting->close_at->format('H:i') }} WIB
                        </p>
                        @else
                        <p class="text-gray-500 text-xs mt-4">*Jadwal pendaftaran belum ditentukan</p>
                        @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-6 md:px-10 py-24 md:py-32">
        <div class="fade-up">
            <h2 class="text-3xl md:text-5xl font-bold text-left border-l-8 border-purple-500 pl-6 mb-14 tracking-tight">
                Tentang Festival
            </h2>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-start">
            <div class="fade-up rounded-3xl overflow-hidden shadow-2xl shadow-purple-900/30 transition-all duration-500 hover:-translate-y-2 hover:shadow-purple-600/50 group">
                <img
                    src="{{ $landingSetting ? $landingSetting->mediaUrl($landingSetting->about_image, 'landing/images/Fest.jpg') : asset('landing/images/Fest.jpg') }}"
                    alt="{{ $aboutTitle }}"
                    class="w-full h-[600px] object-cover transition duration-700 group-hover:scale-105" />
            </div>
            <div class="fade-up space-y-6">
                <h3 class="text-2xl md:text-3xl font-semibold text-purple-300 tracking-wide">
                    {{ $aboutTitle }}
                </h3>
                <p style="text-align: justify;" class="text-gray-300 leading-relaxed text-base md:text-lg">
                    {{ $aboutDescription }}
                </p>
                <p style="text-align: justify;" class="text-gray-300 leading-relaxed text-base md:text-lg">
                    {{ $aboutSecondary }}
                </p>
                <div class="pt-4">
                    <div class="flex items-center space-x-2 text-purple-300 text-sm font-medium">
                        <span class="w-8 h-[2px] bg-purple-400"></span>
                        <span>{{ $landingSetting?->hashtag ?? '#FestivalFilmHoror2026' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if(($boardMembers ?? collect())->isNotEmpty())
    <section class="max-w-7xl mx-auto px-6 md:px-10 py-24 md:py-28">
        <div class="fade-up">
            <h2 class="text-3xl md:text-5xl font-bold text-left border-l-8 border-purple-500 pl-6 mb-16 tracking-tight">
                Festival Board
            </h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-10">
            @foreach($boardMembers as $member)
            <div class="board-card glass-card-light rounded-2xl overflow-hidden transition-all duration-300 group">
                <div class="overflow-hidden">
                    <img
                        src="{{ $landingSetting->mediaUrl(data_get($member, 'image'), 'landing/images/user.png') }}"
                        alt="{{ data_get($member, 'name') }}"
                        class="w-full h-120 object-cover transition duration-500 group-hover:scale-110" />
                </div>
                <div class="p-6 space-y-3">
                    <h3 class="text-2xl font-bold tracking-tight text-white">
                        {{ strtoupper(data_get($member, 'name')) }}
                    </h3>
                    <p class="text-purple-300 text-sm uppercase tracking-wider font-semibold">
                        {{ data_get($member, 'title') }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif

    <section class="max-w-7xl mx-auto px-6 md:px-10 py-24 md:py-28 awards-carousel-section">
        <div class="flex justify-between items-end mb-8 fade-up">
            <div>
                <p class="text-purple-400 text-sm md:text-base uppercase tracking-wider font-semibold mb-2">
                    EXPLORE THE FESTIVAL
                </p>
                <h2 class="text-3xl md:text-5xl font-bold text-left border-l-8 border-purple-500 pl-6 tracking-tight">
                    What Happened Last Year
                </h2>
            </div>
            <div class="flex gap-3">
                <button
                    id="prevAwardBtn"
                    class="w-12 h-12 rounded-full glass-card-light flex items-center justify-center text-purple-300 hover:text-white hover:bg-purple-600/50 transition-all duration-300 cursor-pointer group">
                    <i class="fas fa-chevron-left text-xl"></i>
                </button>
                <button
                    id="nextAwardBtn"
                    class="w-12 h-12 rounded-full glass-card-light flex items-center justify-center text-purple-300 hover:text-white hover:bg-purple-600/50 transition-all duration-300 cursor-pointer group">
                    <i class="fas fa-chevron-right text-xl"></i>
                </button>
            </div>
        </div>

        <div class="relative overflow-hidden">
            <div id="awardsCarouselTrack" class="flex">
                <div class="w-full flex-shrink-0 px-2 award-slide">
                    <div class="glass-card p-6 md:p-8 lg:p-10 rounded-3xl transition-all duration-500 fade-up">
                        <div class="flex flex-col lg:flex-row gap-8 lg:gap-10">
                            <div class="lg:w-[60%] w-full">
                                <h3 class="text-white text-xl font-semibold tracking-wide mb-5 flex items-center gap-2">
                                    <i class="fas fa-clapperboard text-purple-400"></i>
                                    Featured Films
                                </h3>

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                                    @forelse($lastYearFilms as $film)
                                    <div class="film-card group transition-all duration-300">
                                        <div class="relative rounded-2xl overflow-hidden glass-card-light transition-all duration-300 group-hover:scale-[1.02] group-hover:shadow-[0_0_20px_#6D28D9]">
                                            <div class="relative w-full h-72 overflow-hidden bg-gradient-to-b from-purple-900/30 to-black/50">
                                                <img
                                                    src="{{ $film->poster_url }}"
                                                    alt="{{ $film->name }}"
                                                    class="absolute w-full h-full object-cover transition-opacity duration-700 ease-in-out" />
                                                <div class="absolute inset-x-0 bottom-0 p-4 bg-gradient-to-t from-black/90 via-black/60 to-transparent">
                                                    <h4 class="font-bold text-white text-base truncate">{{ $film->name }}</h4>
                                                    <p class="text-purple-300 text-xs">{{ optional($film->category)->name }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="sm:col-span-2 lg:col-span-3 glass-card-light rounded-2xl p-8 text-center text-gray-400">
                                        Belum ada film periode sebelumnya yang bisa ditampilkan.
                                    </div>
                                    @endforelse
                                </div>
                            </div>

                            <div class="lg:w-[40%] w-full space-y-6">
                                <div>
                                    <h3 class="text-2xl md:text-3xl font-bold text-white mb-4">
                                        {{ $lastYearTitle }}
                                    </h3>
                                    <p style="text-align: justify;" class="text-gray-300 leading-relaxed text-sm md:text-base mb-4">
                                        {{ $lastYearDescription }}
                                    </p>
                                    @if($hasManualFeaturedFilms)
                                    <p class="text-purple-300 text-sm">
                                        Pilihan film disusun manual dari panel admin.
                                    </p>
                                    @elseif($completedSubmissionPeriod)
                                    <p class="text-purple-300 text-sm">
                                        Periode referensi: {{ $completedSubmissionPeriod->display_name }}
                                    </p>
                                    @endif
                                </div>

                                <div class="pt-4">
                                    <a href="{{ $lastYearCatalogHref }}"
                                        class="btn-gradient inline-flex items-center gap-2 px-6 py-3 rounded-full text-white font-semibold transition-all duration-300 hover:gap-3">
                                        {{ $lastYearCatalogLabel }}
                                        <i class="fas fa-arrow-right text-sm"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mt-12 pt-6 border-t border-purple-500/20">
                            @foreach($festivalStats as $stat)
                            <div class="glass-card-light rounded-2xl p-6 text-center transition-all duration-300 hover:shadow-[0_0_15px_#6D28D9] hover:scale-[1.02]">
                                <div class="text-4xl md:text-5xl font-black bg-gradient-to-r from-purple-400 to-fuchsia-400 bg-clip-text text-transparent mb-2">
                                    <span class="counter" data-target="{{ $stat['value'] }}">0</span>{{ $stat['suffix'] }}
                                </div>
                                <p class="text-gray-300 text-sm uppercase tracking-wide font-medium">
                                    {{ $stat['label'] }}
                                </p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                @forelse($winnerGroups as $group)
                <div class="w-full flex-shrink-0 px-2 award-slide">
                    <div class="glass-card p-6 md:p-8 lg:p-10 rounded-3xl transition-all duration-500">
                        <div>
                            <h2 class="text-3xl md:text-5xl font-bold text-white mb-2">
                                Film Winner
                            </h2>
                            <p class="text-purple-400 text-sm md:text-base uppercase tracking-wider font-semibold mb-10">
                                {{ $group['category']->name }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($group['films'] as $film)
                            <div class="group transition-all duration-300">
                                <div class="rounded-2xl overflow-hidden glass-card-light transition-all duration-300 group-hover:scale-[1.03] group-hover:shadow-[0_0_25px_#6D28D9]">
                                    <img
                                        src="{{ $film->poster_url }}"
                                        alt="{{ $film->name }}"
                                        class="w-full h-80 object-cover" />
                                    <div class="p-5 space-y-2">
                                        <p class="text-yellow-400 text-xs font-bold uppercase tracking-wider">
                                            {{ strtoupper($film->winner_rank) }}
                                        </p>
                                        <h3 class="text-xl font-bold text-white">
                                            {{ $film->name }}
                                        </h3>
                                        <p class="text-gray-400 text-sm">
                                            Produser: {{ $film->produser }}
                                        </p>
                                        <p class="text-gray-400 text-sm">
                                            Sutradara: {{ $film->sutradara }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @empty
                <div class="w-full flex-shrink-0 px-2 award-slide">
                    <div class="glass-card p-8 rounded-3xl text-center text-gray-400">
                        Data pemenang periode sebelumnya belum tersedia.
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </section>

    @include('landing.partials.home-winners-section', [
        'winnerGroups' => $winnerGroups,
        'winnerSubmissionPeriod' => $winnerSubmissionPeriod ?? null,
    ])

    @if($showLandingProgramSections)
    <section class="max-w-7xl mx-auto px-6 md:px-10 py-24 md:py-28 indigo-program-section">
        <div class="fade-up">
            <p class="text-purple-400 text-sm md:text-base uppercase tracking-wider font-semibold mb-2">
                SPECIAL FEATURE PROGRAM
            </p>
            <h2 class="text-3xl md:text-5xl font-bold text-left border-l-8 border-purple-500 pl-6 tracking-tight">
                {{ $themeName }}
            </h2>
        </div>

        <div class="glass-card mt-12 overflow-hidden rounded-3xl transition-all duration-500 indigo-card-hover fade-up">
            <div class="p-6 md:p-8 lg:p-10 border-b border-purple-500/20">
                <h3 class="text-3xl md:text-5xl font-bold text-white text-center tracking-tight">
                    {{ $themeTitle }}
                </h3>
            </div>
            <div class="relative w-full h-[350px] md:h-[420px] lg:h-[450px] overflow-hidden rounded-t-3xl">
                <img
                    src="{{ $landingSetting ? $landingSetting->mediaUrl($landingSetting->theme_image, 'landing/images/BACKGROUND FFH 2026.png') : asset('landing/images/BACKGROUND FFH 2026.png') }}"
                    alt="{{ $themeName }}"
                    class="absolute w-full h-full object-cover object-center" />
                <div class="absolute inset-0 banner-gradient-overlay"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-purple-900/30 via-transparent to-black/50"></div>
                <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-6 z-10">
                    <h1 class="text-6xl md:text-8xl lg:text-9xl font-black uppercase tracking-wider bg-gradient-to-r from-white via-purple-200 to-purple-300 bg-clip-text text-transparent drop-shadow-2xl">
                        {{ $themeName }}
                    </h1>
                    <p class="text-purple-200 text-base md:text-xl font-light tracking-wide mt-4 backdrop-blur-sm px-4 py-1 rounded-full bg-black/20">
                        {{ $themeQuote }}
                    </p>
                </div>
            </div>

            <div class="p-6 md:p-10 lg:p-12">
                <div class="max-w-4xl mx-auto space-y-6 md:space-y-7 text-gray-300 leading-relaxed text-base md:text-lg">
                    @forelse(array_filter($themeParagraphs) as $paragraph)
                    <p style="text-align: justify !important;">{{ $paragraph }}</p>
                    @empty
                    <p style="text-align: justify !important;">
                        Tema festival akan muncul di sini setelah admin melengkapi deskripsi periode submission.
                    </p>
                    @endforelse
                </div>

                <div class="mt-10 pt-6 border-t border-purple-500/20 flex justify-start items-center gap-3 text-sm text-purple-300">
                    <i class="fas fa-eye text-purple-400"></i>
                    <span class="tracking-wide">KOMPETISI FILM · WORKSHOP · FILM SCREENING · PAGELARAN BUDAYA · PASAR HANTU · PLESIR KIDUL · HOROR IMMERSIVE EXPERIENCE</span>
                    <div class="flex-1"></div>
                    <i class="fas fa-ghost text-purple-400/60"></i>
                </div>

            </div>
        </div>
    </section>

    @include('layouts.landing.timeline-kompetisi-film', ['timelineItems' => $timelineItems])
    @include('layouts.landing.kompetisi-film', [
        'competitionCategories' => $competitionCategories,
        'showCompetitionSubmittedStat' => true,
    ])
    @include('landing.partials.home-merchandise-section', ['featuredMerchandises' => $featuredMerchandises])
    {{-- Tambahan --}}
    @include('landing.partials.home-portal-section', ['featuredPortalPrograms' => $featuredPortalPrograms])
    {{-- Batas Tambahan --}}
    @elseif($hasClosedLandingPeriod)
    @include('landing.partials.program-highlight-section')
    @endif

    <section class="max-w-7xl mx-auto px-6 md:px-10 py-24 md:py-28 tourism-section">
        <div class="fade-up">
            <p class="text-purple-400 text-sm md:text-base uppercase tracking-wider font-semibold mb-2">
                PACITAN TOURISM
            </p>
            <h2 class="text-3xl md:text-5xl font-bold text-left border-l-8 border-purple-500 pl-6 tracking-tight">
                Wisata Pacitan
            </h2>
        </div>

        <div class="glass-card mt-12 rounded-3xl p-6 md:p-8 lg:p-10 fade-up transition-all duration-500 hover:shadow-[0_0_30px_rgba(109,40,217,0.2)]">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-7">
                @foreach(range(1, 6) as $tourismIndex)
                <div class="group tourism-card transition-all duration-500">
                    <div class="glass-card-light rounded-2xl overflow-hidden transition-all duration-500 group-hover:scale-[1.03] group-hover:-translate-y-2 group-hover:shadow-[0_0_25px_#6D28D9]">
                        <div class="relative w-full aspect-[4/3] overflow-hidden bg-gradient-to-br from-purple-900/30 to-black/50">
                            <img
                                src="{{ asset('landing/images/wisata/wisata' . $tourismIndex . '.jpg') }}"
                                alt="Wisata Pacitan {{ $tourismIndex }}"
                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-black/20 opacity-60 group-hover:opacity-40 transition-opacity duration-500"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-6 md:px-10 py-24 md:py-28 cta-registration-section">
        <div class="glass-card rounded-3xl p-6 md:p-8 lg:p-12 fade-up transition-all duration-500 hover:shadow-[0_0_35px_rgba(109,40,217,0.3)]">
            <div class="flex flex-col lg:flex-row gap-8 lg:gap-12 items-center lg:items-stretch">
                <div class="lg:w-[65%] w-full space-y-4 text-center lg:text-left">
                    <p class="text-purple-400 text-sm md:text-base uppercase tracking-wider font-semibold">
                        FESTIVAL EXPERIENCE
                    </p>
                    <h2 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white leading-tight [text-shadow:0_0_15px_rgba(139,92,246,0.3)]">
                        Ayo ke
                        <span class="bg-gradient-to-r from-purple-400 to-fuchsia-400 bg-clip-text text-transparent">Pacitan!</span>
                    </h2>
                    <p class="text-gray-300 text-base md:text-lg leading-relaxed max-w-xl lg:max-w-none">
                        Jelajahi keindahan alam Pacitan, nikmati kompetisi film, belanja merchandise resmi, dan rasakan pengalaman festival yang tak terlupakan bersama FFH.
                    </p>
                    <div class="w-20 h-[2px] bg-gradient-to-r from-purple-500 to-transparent mx-auto lg:mx-0 mt-4"></div>
                </div>

                <div class="lg:w-[35%] w-full flex flex-col items-center justify-center">
                    <button
                        class="registration-btn group relative btn-gradient px-8 md:px-10 py-4 md:py-5 rounded-full text-white font-bold text-base md:text-lg transition-all duration-300 hover:scale-105 hover:shadow-[0_0_30px_#8B5CF6] inline-flex items-center gap-3 overflow-hidden cursor-pointer"
                        type="button">
                        <i class="fas fa-ticket-alt text-white group-hover:hidden transition-all duration-300"></i>
                        <i class="fas fa-lock hidden group-hover:inline-block text-white transition-all duration-300"></i>
                        <span class="font-semibold tracking-wide">Coming Soon</span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    @include('landing.partials.partner-sections')
</main>
@endsection
