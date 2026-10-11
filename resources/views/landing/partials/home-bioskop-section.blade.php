@php
    $hariPendek  = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
    $bulanPendek = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $hariPanjang = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $today       = now()->toDateString();

    // Kelompokkan jadwal per tanggal: [tanggal => [[bioskop, sessions], ...]]
    $byDate = [];
    foreach (($bioskops ?? collect()) as $bioskop) {
        foreach (($bioskop->schedules ?? []) as $schedule) {
            $date = $schedule['date'] ?? null;
            if (!$date || $date < $today || empty($schedule['sessions'])) {
                continue;
            }
            $byDate[$date][] = ['bioskop' => $bioskop, 'sessions' => $schedule['sessions']];
        }
    }
    ksort($byDate);
@endphp

@if (!empty($byDate))
<section class="max-w-7xl mx-auto px-6 md:px-10 pt-16 md:pt-20 pb-4 bioskop-section" data-bioskop-section>
    <div class="fade-up">
        <p class="text-purple-400 text-xs uppercase tracking-[0.2em] font-semibold mb-1">Tayang Gratis</p>
        <h2 class="text-2xl md:text-3xl font-bold border-l-4 border-purple-500 pl-4 tracking-tight">Bioskop Gratis</h2>
    </div>

    {{-- Tab tanggal --}}
    <div class="bioskop-scroll fade-up mt-5 flex gap-2 overflow-x-auto -mx-6 px-6 md:mx-0 md:px-0" role="tablist">
        @foreach (array_keys($byDate) as $date)
        @php $c = \Carbon\Carbon::parse($date); @endphp
        <button type="button" role="tab" data-bioskop-tab="{{ $date }}"
            class="bioskop-tab shrink-0 rounded-xl border px-4 py-2 text-left transition-all duration-200
                {{ $loop->first ? 'is-active' : 'border-white/10 bg-white/5 text-gray-300 hover:border-purple-400/50' }}">
            <span class="block text-[10px] uppercase tracking-wider opacity-70">{{ $hariPendek[$c->dayOfWeek] }}</span>
            <span class="block text-sm font-bold leading-tight">{{ $c->day }} {{ $bulanPendek[$c->month - 1] }}</span>
        </button>
        @endforeach
    </div>

    {{-- Panel per tanggal --}}
    @foreach ($byDate as $date => $items)
    @php $c = \Carbon\Carbon::parse($date); @endphp
    <div data-bioskop-panel="{{ $date }}" class="{{ $loop->first ? '' : 'hidden' }}">
        <p class="mt-4 mb-3 text-xs text-gray-400">
            <i class="far fa-calendar text-purple-400 mr-1"></i>
            {{ $hariPanjang[$c->dayOfWeek] }}, {{ $c->day }} {{ $bulanPendek[$c->month - 1] }} {{ $c->year }}
            &middot; {{ count($items) }} bioskop
        </p>

        <div class="bioskop-scroll flex gap-4 overflow-x-auto snap-x snap-mandatory pb-3 -mx-6 px-6 md:mx-0 md:px-0">
            @foreach ($items as $item)
            @php $bioskop = $item['bioskop']; @endphp
            <article class="snap-start shrink-0 grow basis-[250px] max-w-[340px] rounded-2xl border border-purple-400/25 bg-white/[0.04] backdrop-blur p-4 transition-all duration-300 hover:-translate-y-1 hover:border-purple-400/60 hover:shadow-[0_0_24px_rgba(139,92,246,0.25)]">
                <h3 class="text-base font-bold leading-snug text-white">{{ $bioskop->name }}</h3>

                @if ($bioskop->venue || $bioskop->address)
                <p class="mt-1.5 text-xs text-gray-400 leading-relaxed">
                    <i class="fas fa-location-dot text-purple-400 mr-1"></i>
                    {{ $bioskop->venue }}@if ($bioskop->venue && $bioskop->address) &middot; @endif{{ $bioskop->address }}
                </p>
                @endif

                <ul class="mt-3 space-y-1.5">
                    @foreach ($item['sessions'] as $session)
                    @php $isFull = ($session['status'] ?? '') === 'full'; @endphp
                    <li class="flex items-center gap-2 text-sm rounded-lg bg-white/5 px-2.5 py-1.5 {{ $isFull ? 'opacity-70' : '' }}">
                        <span class="font-semibold text-white">{{ $session['label'] }}</span>
                        <span class="text-gray-300 tabular-nums">{{ str_replace(':', '.', $session['time']) }}</span>
                        <span class="ml-auto inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-[11px] font-semibold
                            {{ $isFull ? 'bg-red-500/15 text-red-300' : 'bg-emerald-500/15 text-emerald-300' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $isFull ? 'bg-red-400' : 'bg-emerald-400 animate-pulse' }}"></span>
                            {{ $isFull ? 'Full' : 'Available' }}
                        </span>
                    </li>
                    @endforeach
                </ul>
            </article>
            @endforeach
        </div>
    </div>
    @endforeach
</section>

<style>
    .bioskop-scroll { scrollbar-width: none; }
    .bioskop-scroll::-webkit-scrollbar { display: none; }
    .bioskop-tab.is-active {
        color: #fff;
        border-color: rgba(167, 139, 250, .9);
        background: linear-gradient(135deg, #7c3aed, #a855f7);
        box-shadow: 0 0 18px rgba(139, 92, 246, .45);
    }
</style>

<script>
(function () {
    var root = document.querySelector('[data-bioskop-section]');
    if (!root) return;
    var tabs = root.querySelectorAll('[data-bioskop-tab]');
    var panels = root.querySelectorAll('[data-bioskop-panel]');

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            var key = tab.getAttribute('data-bioskop-tab');
            tabs.forEach(function (t) {
                var on = t === tab;
                t.classList.toggle('is-active', on);
                t.classList.toggle('border-white/10', !on);
                t.classList.toggle('bg-white/5', !on);
                t.classList.toggle('text-gray-300', !on);
                t.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            panels.forEach(function (p) {
                p.classList.toggle('hidden', p.getAttribute('data-bioskop-panel') !== key);
            });
        });
    });
})();
</script>
@endif