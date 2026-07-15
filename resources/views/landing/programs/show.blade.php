@extends('layouts.landing.master')

@push('styles')
<style>
    .program-rich-content h1,
    .program-rich-content h2,
    .program-rich-content h3,
    .program-rich-content h4,
    .program-rich-content h5,
    .program-rich-content h6 {
        color: #f3e8ff;
        font-weight: 700;
        margin: 1.5rem 0 0.85rem;
    }

    .program-rich-content p,
    .program-rich-content ul,
    .program-rich-content ol,
    .program-rich-content blockquote {
        color: #d1d5db;
        line-height: 1.8;
        margin-bottom: 1rem;
    }

    .program-rich-content ul,
    .program-rich-content ol {
        padding-left: 1.25rem;
    }

    .program-rich-content a {
        color: #c084fc;
        text-decoration: underline;
    }

    .program-rich-content img {
        max-width: 100%;
        border-radius: 1rem;
        margin: 1.5rem 0;
    }
</style>
@endpush

@section('main')
<main class="relative z-10">
    <section
        class="relative min-h-[75vh] flex items-end px-6 py-16 md:py-20 overflow-hidden bg-cover bg-center"
        style="background-image: url({{ $program->poster_url }});">
        <div class="absolute inset-0 bg-black/70"></div>
        <div class="absolute inset-0 premium-glow opacity-40"></div>
        <div class="relative max-w-7xl w-full mx-auto fade-up">
            <div class="glass-card p-8 md:p-12 lg:p-16 shadow-2xl border border-purple-400/30">
                <div class="space-y-5">
                    <div class="flex items-center gap-3 text-sm text-gray-400">
                        <a href="{{ route('landing.home') }}" class="hover:text-purple-300 transition">Home</a>
                        <span>/</span>
                        <a href="{{ route('landing.program') }}" class="hover:text-purple-300 transition">Program</a>
                        <span>/</span>
                        <a href="{{ route('programs.index', ['category' => optional($program->category)->slug]) }}" class="hover:text-purple-300 transition">
                            {{ optional($program->category)->name ?: 'Program Festival' }}
                        </a>
                    </div>
                    <p class="text-purple-400 text-sm md:text-base uppercase tracking-wider font-semibold">
                        {{ optional($program->category)->name ?: 'PROGRAM FESTIVAL' }}
                    </p>
                    <h1 class="text-4xl md:text-6xl lg:text-7xl font-black uppercase tracking-tighter leading-[1.1] bg-gradient-to-r from-white via-purple-200 to-purple-300 bg-clip-text text-transparent drop-shadow-2xl">
                        {{ $program->title }}
                    </h1>
                    @if($program->summary)
                    <p class="text-gray-300 text-base md:text-xl max-w-3xl font-light leading-relaxed">
                        {{ $program->summary }}
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-6 md:px-10 py-24 md:py-28 competition-detail-section">
        <div class="fade-up">
            <p class="text-purple-400 text-sm md:text-base uppercase tracking-wider font-semibold mb-2">
                DETAIL PROGRAM
            </p>
            <h2 class="text-3xl md:text-5xl font-bold text-left border-l-8 border-purple-500 pl-6 tracking-tight">
                Informasi Program
            </h2>
        </div>

        <div class="glass-card rounded-3xl p-6 md:p-8 lg:p-10 mt-6 transition-all duration-500 hover:shadow-[0_0_30px_rgba(109,40,217,0.2)] fade-up">
            <div class="flex justify-between items-start mb-6">
                <div class="w-12 h-12 rounded-2xl bg-purple-600/20 flex items-center justify-center border border-purple-500/30">
                    <i class="fas fa-film text-purple-400 text-2xl"></i>
                </div>
                <div class="w-16 h-[2px] bg-gradient-to-r from-purple-500 to-transparent"></div>
            </div>

            <div class="space-y-6">
                @if($program->summary)
                <div class="glass-card-light rounded-2xl p-5 text-gray-300 leading-relaxed">
                    {{ $program->summary }}
                </div>
                @endif

                <div class="program-rich-content text-base md:text-lg">
                    {!! $program->content ?: '<p>Konten program akan segera diperbarui.</p>' !!}
                </div>
            </div>
        </div>
    </section>

    @if($relatedPrograms->isNotEmpty())
    <section class="max-w-7xl mx-auto px-6 md:px-10 pb-24 md:pb-28">
        <div class="fade-up">
            <p class="text-purple-400 text-sm md:text-base uppercase tracking-wider font-semibold mb-2">
                PROGRAM TERKAIT
            </p>
            <h2 class="text-3xl md:text-5xl font-bold text-left border-l-8 border-purple-500 pl-6 tracking-tight">
                Jelajahi Program Lainnya
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 lg:gap-8 mt-10 fade-up">
            @foreach($relatedPrograms as $relatedProgram)
                @include('landing.partials.program-card', ['program' => $relatedProgram])
            @endforeach
        </div>
    </section>
    @endif
</main>
@endsection
