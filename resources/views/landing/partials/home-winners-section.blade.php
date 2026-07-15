<section class="max-w-7xl mx-auto px-6 md:px-10 py-24 md:py-28 winners-showcase-section">
    <div class="fade-up">
        <p class="text-purple-400 text-sm md:text-base uppercase tracking-wider font-semibold mb-2">
            LATEST CLOSED COMPETITION
        </p>
        <h2 class="text-3xl md:text-5xl font-bold text-left border-l-8 border-purple-500 pl-6 tracking-tight">
            Kategori Pemenang
        </h2>
        @if($winnerSubmissionPeriod)
        <p class="text-yellow-300 text-lg md:text-2xl font-semibold mt-4">
            {{ $winnerSubmissionPeriod->display_name }}
        </p>
        @endif
    </div>

    @if(($winnerGroups ?? collect())->isNotEmpty())
    <div class="mt-12 space-y-8 md:space-y-10">
        @foreach($winnerGroups as $group)
        <div class="glass-card rounded-[32px] p-6 md:p-8 lg:p-10 fade-up border border-white/5 winner-swiper-shell">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-3">
                    <p class="text-purple-300 text-xs md:text-sm uppercase tracking-[0.3em] font-semibold">
                        Winner Category
                    </p>
                    <h3 class="text-2xl md:text-4xl font-bold text-white tracking-tight">
                        {{ $group['category']->name }}
                    </h3>
                </div>

                <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-center">
                    @if($winnerSubmissionPeriod)
                    <div class="inline-flex items-center rounded-full border border-purple-500/20 bg-white/5 px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.28em] text-gray-200">
                        {{ $winnerSubmissionPeriod->display_name }}
                    </div>
                    @endif

                    <div class="flex items-center gap-2" data-winner-nav>
                        <button
                            type="button"
                            aria-label="Pemenang sebelumnya"
                            data-winner-prev
                            class="winner-swiper-button inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/10 bg-white/[0.04] text-gray-200 transition-all duration-300 hover:border-purple-400/50 hover:bg-white/[0.08] hover:text-white">
                            <i class="fas fa-arrow-left text-sm"></i>
                        </button>
                        <button
                            type="button"
                            aria-label="Pemenang berikutnya"
                            data-winner-next
                            class="winner-swiper-button inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/10 bg-white/[0.04] text-gray-200 transition-all duration-300 hover:border-purple-400/50 hover:bg-white/[0.08] hover:text-white">
                            <i class="fas fa-arrow-right text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-8 winner-swiper-root" data-winner-swiper-root>
                <div class="swiper winner-swiper overflow-hidden" data-winner-swiper>
                    <div class="swiper-wrapper">
                        @foreach($group['films'] as $film)
                        <div class="swiper-slide h-auto">
                            <article class="h-full">
                                <div class="h-full overflow-hidden rounded-[28px] border border-white/10 bg-[linear-gradient(180deg,rgba(255,255,255,0.08),rgba(255,255,255,0.03))] shadow-[0_18px_45px_rgba(4,4,10,0.35)] backdrop-blur-xl transition-all duration-500 hover:-translate-y-2 hover:border-purple-400/40 hover:shadow-[0_24px_50px_rgba(109,40,217,0.28)]">
                                    <div class="relative aspect-[4/5] overflow-hidden bg-gradient-to-b from-purple-950/30 to-black/60">
                                        <img
                                            src="{{ $film->poster_url }}"
                                            alt="{{ $film->name }}"
                                            draggable="false"
                                            class="h-full w-full object-cover transition-transform duration-700 hover:scale-105" />
                                        <div class="absolute inset-0 bg-gradient-to-t from-[#07060b] via-[#07060b]/35 to-transparent"></div>
                                        <div class="absolute left-5 top-5 rounded-full border border-white/15 bg-black/35 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.24em] text-white backdrop-blur-md">
                                            {{ $film->winner_rank_label }}
                                        </div>
                                        <div class="absolute inset-x-0 bottom-0 p-5">
                                            <p class="text-[11px] uppercase tracking-[0.28em] text-white/60">
                                                {{ $group['category']->name }}
                                            </p>
                                            <h4 class="mt-2 text-2xl font-bold leading-tight text-white">
                                                {{ $film->name }}
                                            </h4>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3 p-5">
                                        <div class="rounded-2xl border border-white/5 bg-white/[0.03] p-4">
                                            <p class="text-[10px] uppercase tracking-[0.24em] text-gray-500">Director</p>
                                            <p class="mt-2 text-sm leading-relaxed text-gray-200">{{ $film->sutradara }}</p>
                                        </div>
                                        <div class="rounded-2xl border border-white/5 bg-white/[0.03] p-4">
                                            <p class="text-[10px] uppercase tracking-[0.24em] text-gray-500">Producer</p>
                                            <p class="mt-2 text-sm leading-relaxed text-gray-200">{{ $film->produser }}</p>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-center">
                    <div class="winner-swiper-pagination" data-winner-pagination></div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="glass-card mt-12 rounded-3xl p-8 text-center text-gray-400 fade-up">
        Data pemenang untuk periode submission terbaru yang sudah ditutup belum tersedia.
    </div>
    @endif
</section>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.css" />
<style>
    .winner-swiper-shell {
        background:
            radial-gradient(circle at top left, rgba(109, 40, 217, 0.18), transparent 34%),
            linear-gradient(180deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.015));
    }

    .winner-swiper .swiper-wrapper {
        align-items: stretch;
    }

    .winner-swiper .swiper-slide {
        height: auto;
    }

    .winner-swiper {
        user-select: none;
    }

    .winner-swiper-button.swiper-button-disabled {
        opacity: 0.35;
        cursor: not-allowed;
    }

    .winner-swiper-pagination {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .winner-swiper-pagination .swiper-pagination-bullet {
        width: 8px;
        height: 8px;
        margin: 0 !important;
        background: rgba(255, 255, 255, 0.22);
        opacity: 1;
        transition: all 0.3s ease;
    }

    .winner-swiper-pagination .swiper-pagination-bullet-active {
        width: 28px;
        border-radius: 9999px;
        background: linear-gradient(90deg, #a855f7, #c084fc);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-winner-swiper-root]').forEach(function(root) {
            const container = root.querySelector('[data-winner-swiper]');
            const nextButton = root.parentElement.querySelector('[data-winner-next]');
            const prevButton = root.parentElement.querySelector('[data-winner-prev]');
            const pagination = root.querySelector('[data-winner-pagination]');

            if (!container || !nextButton || !prevButton || !pagination || typeof Swiper === 'undefined') {
                return;
            }

            new Swiper(container, {
                speed: 700,
                grabCursor: true,
                watchOverflow: true,
                observer: true,
                observeParents: true,
                slidesPerView: 1.08,
                spaceBetween: 18,
                navigation: {
                    nextEl: nextButton,
                    prevEl: prevButton,
                },
                pagination: {
                    el: pagination,
                    clickable: true,
                },
                breakpoints: {
                    640: {
                        slidesPerView: 1.4,
                        spaceBetween: 20,
                    },
                    768: {
                        slidesPerView: 2,
                        spaceBetween: 24,
                    },
                    1280: {
                        slidesPerView: 3,
                        spaceBetween: 24,
                    },
                },
            });
        });
    });
</script>
@endpush
