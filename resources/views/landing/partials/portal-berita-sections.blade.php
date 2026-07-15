@php
    $portalSectionBackground = $landingSetting
        ? $landingSetting->mediaUrl(
            $landingSetting->theme_image ?: $landingSetting->hero_image,
            'landing/images/BACKGROUND FFH 2026.png',
        )
        : asset('landing/images/BACKGROUND FFH 2026.png');
@endphp

<section class="relative px-6 py-20 overflow-hidden bg-cover bg-center"
    style="background-image: url({{ $portalSectionBackground }});">
    <div class="absolute inset-0 bg-black/70"></div>
    <div class="absolute inset-0 premium-glow opacity-40"></div>
    <div class="bg-glow-abstract"></div>

    <div class="relative max-w-7xl w-full mx-auto">
        <div id="portal-list" class="glass-card rounded-3xl p-6 md:p-8 lg:p-10">
            @if (($portalPrograms ?? collect())->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 lg:gap-8">
                    @foreach ($portalPrograms as $program)
                        @include('landing.partials.program-card', [
                            'program' => $program,
                            'showProgramCategory' => true,
                        ])
                    @endforeach
                </div>

                <div class="mt-10 flex items-center justify-between gap-4 flex-wrap text-sm text-gray-400">
                    <div>
                        Menampilkan {{ $portalPrograms->firstItem() }} - {{ $portalPrograms->lastItem() }} dari
                        {{ $portalPrograms->total() }} berita
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        @if ($portalPrograms->onFirstPage())
                            <span
                                class="px-4 py-2 rounded-xl border border-white/10 bg-white/5 text-gray-500">Sebelumnya</span>
                        @else
                            <a href="{{ $portalPrograms->previousPageUrl() }}"
                                class="px-4 py-2 rounded-xl border border-purple-500/20 bg-white/5 text-gray-200 hover:text-purple-300">Sebelumnya</a>
                        @endif

                        @foreach ($portalPrograms->getUrlRange(max(1, $portalPrograms->currentPage() - 1), min($portalPrograms->lastPage(), $portalPrograms->currentPage() + 1)) as $page => $url)
                            <a href="{{ $url }}"
                                class="px-4 py-2 rounded-xl border {{ $page === $portalPrograms->currentPage() ? 'border-purple-500/40 bg-purple-500/20 text-purple-200' : 'border-white/10 bg-white/5 text-gray-300' }}">
                                {{ $page }}
                            </a>
                        @endforeach

                        @if ($portalPrograms->hasMorePages())
                            <a href="{{ $portalPrograms->nextPageUrl() }}"
                                class="px-4 py-2 rounded-xl border border-purple-500/20 bg-white/5 text-gray-200 hover:text-purple-300">Selanjutnya</a>
                        @else
                            <span
                                class="px-4 py-2 rounded-xl border border-white/10 bg-white/5 text-gray-500">Selanjutnya</span>
                        @endif
                    </div>
                </div>
            @else
                <div class="glass-card-light rounded-2xl p-8 text-center text-gray-400">
                    Berita belum tersedia.
                </div>
            @endif
        </div>
    </div>
</section>
