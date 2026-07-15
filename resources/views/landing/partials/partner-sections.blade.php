@php
    $collaboratorLogos = [
        'landing/images/collab/col1.png',
        'landing/images/collab/col2.png',
        'landing/images/collab/col3.png',
        'landing/images/collab/col4.png',
        'landing/images/collab/col5.png',
    ];

    $partnerRows = [
        [
            'landing/images/sponsor/ANEKA HARAPAN.png',
            'landing/images/sponsor/Alifa Cake & Bakery.png',
            'landing/images/sponsor/Bonteh.png',
            'landing/images/sponsor/Dagelan Pacitan.png',
            'landing/images/sponsor/Dewan Kesenian Pacitan.png',
            'landing/images/sponsor/Dopamin.png',
            'landing/images/sponsor/Fhillbrew.png',
            'landing/images/sponsor/Horizontal Hotel.png',
            'landing/images/sponsor/Info Cegatan Pacitan.png',
            'landing/images/sponsor/Info Festival.png',
            'landing/images/sponsor/Info event jatim.png',
        ],
        [
            'landing/images/sponsor/Info pacitan.png',
            'landing/images/sponsor/KNPI.png',
            'landing/images/sponsor/Karang Taruna.png',
            'landing/images/sponsor/Kojuwa.png',
            'landing/images/sponsor/Lentera Digital Nusantara.png',
            'landing/images/sponsor/MYPACITAN.png',
            'landing/images/sponsor/PKK.png',
            'landing/images/sponsor/Pacitanku.png',
            'landing/images/sponsor/Sampono Perfumes.png',
            'landing/images/sponsor/Warung makan Karmila.png',
        ],
    ];
@endphp

@once
    @push('styles')
    <style>
        .partner-marquee {
            position: relative;
        }

        .partner-marquee-track {
            display: flex;
            width: max-content;
            gap: 0;
            will-change: transform;
        }

        .partner-marquee-group {
            display: flex;
            flex-shrink: 0;
            gap: 1rem;
            padding-inline: 1rem;
        }

        .partner-marquee-track-forward {
            animation: partner-marquee-left 32s linear infinite;
        }

        .partner-marquee-track-reverse {
            animation: partner-marquee-right 36s linear infinite;
        }

        .partner-marquee:hover .partner-marquee-track {
            animation-play-state: paused;
        }

        .partner-logo-card {
            width: 140px;
            height: 92px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1rem;
            border-radius: 1.5rem;
            background: linear-gradient(135deg, rgba(111, 107, 137, 0.78), rgba(76, 72, 101, 0.68));
            backdrop-filter: blur(14px);
            border: 1px solid rgba(203, 213, 225, 0.14);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
        }

        .partner-logo-card:hover {
            transform: translateY(-2px) scale(1.04);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.1), 0 0 25px rgba(109, 40, 217, 0.28);
            border-color: rgba(203, 213, 225, 0.28);
        }

        .partner-logo-card img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            filter: brightness(1) opacity(0.92);
            transition: filter 0.3s ease;
        }

        .partner-logo-card:hover img {
            filter: brightness(1.05) opacity(1);
        }

        .sponsor-section .glass-card {
            background: linear-gradient(135deg, rgba(45, 43, 66, 0.72), rgba(18, 16, 35, 0.82));
            border: 1px solid rgba(148, 163, 184, 0.18);
            backdrop-filter: blur(14px);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }

        @keyframes partner-marquee-left {
            from {
                transform: translateX(0);
            }

            to {
                transform: translateX(-50%);
            }
        }

        @keyframes partner-marquee-right {
            from {
                transform: translateX(-50%);
            }

            to {
                transform: translateX(0);
            }
        }

        @media (min-width: 768px) {
            .partner-marquee-group {
                gap: 1.25rem;
                padding-inline: 1.25rem;
            }

            .partner-logo-card {
                width: 160px;
                height: 104px;
            }
        }

        @media (min-width: 1024px) {
            .partner-marquee-group {
                gap: 1.5rem;
                padding-inline: 1.5rem;
            }
        }

        @media (max-width: 640px) {
            .partner-logo-card {
                width: 120px;
                height: 84px;
                border-radius: 1.25rem;
            }
        }
    </style>
    @endpush
@endonce

<section class="max-w-7xl mx-auto px-6 md:px-10 py-24 md:py-28 sponsor-section">
    <div class="fade-up">
        <p class="text-purple-400 text-sm md:text-base uppercase tracking-wider font-semibold mb-2">
            OFFICIAL COLLABORATOR
        </p>
        <h2 class="text-3xl md:text-5xl font-bold text-left border-l-8 border-purple-500 pl-6 tracking-tight">
            Our Collaborator
        </h2>
    </div>

    <div class="glass-card mt-12 rounded-3xl p-6 md:p-8 lg:p-10 fade-up transition-all duration-500 hover:shadow-[0_0_35px_rgba(109,40,217,0.2)]">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-6 md:gap-8 items-center justify-items-center">
            @foreach($collaboratorLogos as $logo)
            <div class="w-full flex justify-center p-2">
                <img
                    src="{{ asset($logo) }}"
                    alt="Collaborator Logo"
                    class="max-h-30 w-auto object-contain filter brightness-100 opacity-90 hover:opacity-100 transition-opacity duration-300">
            </div>
            @endforeach
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 md:px-10 py-24 md:py-28 sponsor-section">
    <div class="fade-up">
        <p class="text-purple-400 text-sm md:text-base uppercase tracking-wider font-semibold mb-2">
            OFFICIAL PARTNERS
        </p>
        <h2 class="text-3xl md:text-5xl font-bold text-left border-l-8 border-purple-500 pl-6 tracking-tight">
            Sponsor & Partner
        </h2>
    </div>

    <div class="glass-card mt-12 rounded-3xl p-6 md:p-8 lg:p-10 fade-up transition-all duration-500 hover:shadow-[0_0_35px_rgba(109,40,217,0.2)]">
        @foreach($partnerRows as $partnerRow)
        <div class="{{ $loop->first ? 'mb-8 md:mb-12' : 'mt-8 md:mt-12' }}">
            <div class="partner-marquee relative w-full overflow-hidden rounded-2xl">
                <div class="absolute left-0 top-0 bottom-0 w-12 md:w-20 z-10 pointer-events-none bg-gradient-to-r from-[#0f0f23] to-transparent rounded-l-2xl"></div>
                <div class="absolute right-0 top-0 bottom-0 w-12 md:w-20 z-10 pointer-events-none bg-gradient-to-l from-[#0f0f23] to-transparent rounded-r-2xl"></div>

                <div class="partner-marquee-track {{ $loop->first ? 'partner-marquee-track-forward' : 'partner-marquee-track-reverse' }}">
                    @foreach(range(1, 2) as $duplicate)
                    <div class="partner-marquee-group" aria-label="Sponsor dan partner {{ $loop->parent->first ? 'baris pertama' : 'baris kedua' }}">
                        @foreach($partnerRow as $logo)
                        <div class="partner-logo-card">
                            <img
                                src="{{ asset($logo) }}"
                                alt="{{ pathinfo($logo, PATHINFO_FILENAME) }}"
                                loading="lazy">
                        </div>
                        @endforeach
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach

        <div class="mt-12 pt-8 border-t border-purple-500/20 text-center">
            <p class="text-gray-400 text-sm tracking-wide">
                Supported by amazing partners, institutions, and creative communities.
            </p>
        </div>
    </div>
</section>
