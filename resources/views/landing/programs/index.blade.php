@extends('layouts.landing.master')
@section('main')
<main class="relative z-10">
    <section class="max-w-7xl mx-auto px-6 md:px-10 pt-16 pb-8 fade-up">
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('landing.home') }}" class="text-gray-500 hover:text-purple-400 text-sm transition">Home</a>
            <span class="text-gray-600">/</span>
            <a href="{{ route('landing.program') }}" class="text-gray-500 hover:text-purple-400 text-sm transition">Program</a>
            <span class="text-gray-600">/</span>
            <span class="text-purple-400 text-sm font-semibold">Semua Program</span>
        </div>
        <p class="text-purple-400 text-sm md:text-base uppercase tracking-wider font-semibold mb-2">
            FESTIVAL PROGRAMS
        </p>
        <h1 class="text-3xl md:text-5xl font-bold border-l-8 border-purple-500 pl-6 tracking-tight">
            {{ optional($selectedProgramCategory)->name ? 'Program ' . optional($selectedProgramCategory)->name : 'Semua Program' }}
        </h1>
        <p class="text-gray-400 text-sm md:text-base mt-4 pl-6 max-w-2xl">
            Jelajahi seluruh program festival berdasarkan kategori yang dipilih dari dashboard admin.
        </p>
    </section>

    <section class="max-w-7xl mx-auto px-6 md:px-10 py-6 fade-up">
        <form method="GET" action="{{ route('programs.index') }}"
            class="glass-card rounded-2xl p-4 md:p-6 flex flex-col lg:flex-row gap-4 lg:items-end lg:justify-between">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 flex-1">
                <div>
                    <label class="block text-xs uppercase tracking-wider text-gray-400 mb-2">Kategori Program</label>
                    <select name="category"
                        class="landing-native-select w-full px-4 py-3 rounded-xl bg-white/5 border border-purple-500/20 text-white text-sm focus:outline-none focus:border-purple-500/50 transition">
                        <option value="">Semua kategori</option>
                        @foreach($programCategories as $category)
                        <option value="{{ $category->slug }}" {{ ($filters['category'] ?? '') === $category->slug ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-3 items-end">
                    <button type="submit"
                        class="px-5 py-3 rounded-xl bg-gradient-to-r from-purple-700 to-purple-500 text-white text-sm font-semibold">
                        Terapkan
                    </button>
                    <a href="{{ route('programs.index') }}"
                        class="px-5 py-3 rounded-xl border border-white/10 bg-white/5 text-gray-300 text-sm font-semibold">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </section>

    <section class="max-w-7xl mx-auto px-6 md:px-10 py-4 pb-28">
        @if($programs->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6 fade-up">
            @foreach($programs as $program)
                @include('landing.partials.program-card', ['program' => $program])
            @endforeach
        </div>

        <div class="mt-10 flex items-center justify-between gap-4 flex-wrap text-sm text-gray-400">
            <div>
                Menampilkan {{ $programs->firstItem() }} - {{ $programs->lastItem() }} dari {{ $programs->total() }} program
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                @if($programs->onFirstPage())
                <span class="px-4 py-2 rounded-xl border border-white/10 bg-white/5 text-gray-500">Sebelumnya</span>
                @else
                <a href="{{ $programs->previousPageUrl() }}" class="px-4 py-2 rounded-xl border border-purple-500/20 bg-white/5 text-gray-200 hover:text-purple-300">Sebelumnya</a>
                @endif

                @foreach($programs->getUrlRange(max(1, $programs->currentPage() - 1), min($programs->lastPage(), $programs->currentPage() + 1)) as $page => $url)
                <a href="{{ $url }}"
                    class="px-4 py-2 rounded-xl border {{ $page === $programs->currentPage() ? 'border-purple-500/40 bg-purple-500/20 text-purple-200' : 'border-white/10 bg-white/5 text-gray-300' }}">
                    {{ $page }}
                </a>
                @endforeach

                @if($programs->hasMorePages())
                <a href="{{ $programs->nextPageUrl() }}" class="px-4 py-2 rounded-xl border border-purple-500/20 bg-white/5 text-gray-200 hover:text-purple-300">Selanjutnya</a>
                @else
                <span class="px-4 py-2 rounded-xl border border-white/10 bg-white/5 text-gray-500">Selanjutnya</span>
                @endif
            </div>
        </div>
        @else
        <div class="text-center py-20">
            <div class="text-5xl mb-4">🎬</div>
            <p class="text-gray-400 text-lg">Tidak ada program ditemukan.</p>
        </div>
        @endif
    </section>
</main>
@endsection
