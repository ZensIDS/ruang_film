<!-- ================================================== -->
<!-- SECTION: TIMELINE KOMPETISI FILM -->
<!-- ================================================== -->
<section class="max-w-7xl mx-auto px-6 md:px-10 py-24 md:py-28 timeline-section">
    <div class="fade-up">
        <p class="text-purple-400 text-sm md:text-base uppercase tracking-wider font-semibold mb-2">
            COMPETITION JOURNEY
        </p>
        <h2 class="text-3xl md:text-5xl font-bold text-left border-l-8 border-purple-500 pl-6 tracking-tight">
            Timeline Kompetisi Film
        </h2>
    </div>

    <div class="relative mt-16 md:mt-20 fade-up">
        <div class="relative grid grid-cols-1 md:grid-cols-5 gap-8 md:gap-4 lg:gap-6 z-10">
            @forelse(($timelineItems ?? collect()) as $item)
            <div class="timeline-card-wrapper relative fade-up" style="transition-delay: {{ number_format(($loop->iteration * 0.1), 1) }}s">
                <div class="hidden md:flex absolute -top-10 left-1/2 transform -translate-x-1/2 w-4 h-4 rounded-full bg-purple-500 shadow-[0_0_12px_#8B5CF6] z-20"></div>
                <div class="glass-card-light rounded-2xl p-6 transition-all duration-300 hover:scale-[1.03] hover:shadow-[0_0_25px_#6D28D9] group h-full">
                    <div class="space-y-4">
                        <div class="text-center md:text-left">
                            <p class="text-purple-300 text-xs md:text-sm font-semibold uppercase tracking-widest">
                                {{ $item['period'] }}
                            </p>
                        </div>
                        <div class="border-t border-purple-500/20 my-2"></div>
                        <h3 class="text-xl md:text-2xl font-bold text-white text-center md:text-left">
                            {{ $item['title'] }}
                        </h3>
                        <p class="text-gray-300 text-sm leading-relaxed text-center md:text-left">
                            {{ $item['description'] }}
                        </p>
                        <div class="flex justify-center md:justify-start pt-2">
                            <i class="{{ $item['icon'] }} text-purple-400/50 text-lg group-hover:text-purple-400 transition-colors duration-300"></i>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="md:col-span-5 glass-card-light rounded-2xl p-8 text-center text-gray-400">
                Timeline akan tampil setelah admin membuat periode submission.
            </div>
            @endforelse
        </div>

        <div class="md:hidden absolute left-6 top-0 bottom-0 w-[2px] bg-gradient-to-b from-purple-500/20 via-purple-500/60 to-purple-500/20"></div>
    </div>
</section>
