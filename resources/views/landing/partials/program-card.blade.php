@php
    $showProgramCategory = $showProgramCategory ?? true;
@endphp

<div class="group transition-all duration-500">
    <div class="glass-card-light rounded-2xl overflow-hidden transition-all duration-500 group-hover:scale-[1.02] group-hover:-translate-y-2 group-hover:shadow-[0_0_25px_#6D28D9] h-full flex flex-col">
        <div class="relative w-full h-64 overflow-hidden bg-gradient-to-br from-purple-900/50 to-black/60">
            <img
                src="{{ $program->poster_url }}"
                alt="{{ $program->title }}"
                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-black/20"></div>
            @if($showProgramCategory)
            <div class="absolute top-4 right-4 bg-purple-600/80 backdrop-blur-sm px-3 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider">
                {{ optional($program->category)->name ?: 'Program Festival' }}
            </div>
            @endif
        </div>
        <div class="p-6 flex flex-col flex-grow">
            <h3 class="text-xl md:text-2xl font-bold text-white mb-3 group-hover:text-purple-300 transition-colors duration-300">
                {{ $program->title }}
            </h3>
            <p class="text-gray-300 text-sm leading-relaxed mb-6">
                {{ \Illuminate\Support\Str::limit(strip_tags((string) $program->summary), 140) ?: 'Detail program akan segera diperbarui.' }}
            </p>
            <div class="mt-auto">
                <a href="{{ route('programs.show', ['program' => $program->slug]) }}"
                    class="inline-flex items-center gap-2 text-purple-400 font-semibold text-sm group-hover:text-purple-300 transition-all duration-300 group-hover:gap-3">
                    Lihat Detail <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
    </div>
</div>
