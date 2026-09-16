<section class="max-w-7xl mx-auto px-6 md:px-10 py-24 md:py-28 faq-section">
    <div class="fade-up">
        <p class="text-purple-400 text-sm md:text-base uppercase tracking-wider font-semibold mb-2">
            PERTANYAAN UMUM
        </p>
        <h2 class="text-3xl md:text-5xl font-bold text-left border-l-8 border-purple-500 pl-6 tracking-tight">
            FAQ
        </h2>
    </div>

    <div class="glass-card mt-8 rounded-3xl p-6 md:p-8 fade-up transition-all duration-500 hover:shadow-[0_0_30px_rgba(109,40,217,0.2)]">
        <div class="max-w-3xl">
            <p class="text-gray-300 leading-relaxed text-base md:text-lg">
                Temukan jawaban atas berbagai pertanyaan seputar Festival Film Horor 2026. Panduan lengkap tentang pendaftaran, seleksi, kategori kompetisi, dan pengalaman festival disediakan untuk memudahkan partisipasi Anda.
            </p>
            <p class="text-gray-400 text-sm mt-4">
                Informasi lebih detail tersedia dalam buku panduan resmi festival.
            </p>
        </div>
    </div>

    <div class="glass-card mt-10 rounded-3xl p-6 md:p-8 fade-up transition-all duration-500">
        <div class="space-y-4" id="faqContainer">
            @forelse(($faqs ?? \App\Models\Faq::active()->ordered()->get()) as $faq)
            <div class="faq-item glass-card-light rounded-2xl transition-all duration-300 hover:shadow-[0_0_15px_rgba(109,40,217,0.3)]">
                <button class="faq-question w-full text-left p-5 md:p-6 flex justify-between items-center gap-4 cursor-pointer group" type="button">
                    <span class="text-white font-semibold text-base md:text-lg tracking-wide group-hover:text-purple-300 transition-colors duration-300">
                        {{ $faq->question }}
                    </span>
                    <div class="faq-icon w-8 h-8 rounded-full glass-card-light flex items-center justify-center transition-all duration-300 group-hover:shadow-[0_0_10px_#6D28D9] flex-shrink-0">
                        <i class="fas fa-plus text-purple-400 text-sm transition-all duration-300"></i>
                    </div>
                </button>
                <div class="faq-answer max-h-0 opacity-0 overflow-hidden transition-all duration-500 ease-in-out">
                    <div class="px-5 pb-5 md:px-6 md:pb-6">
                        <div class="border-t border-purple-500/20 pt-4">
                            <p class="text-gray-300 leading-relaxed text-sm md:text-base">
                                {{ $faq->answer }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="glass-card-light rounded-2xl p-8 text-center text-gray-400">
                Pertanyaan umum belum tersedia.
            </div>
            @endforelse
        </div>

        <div class="mt-8 pt-6 border-t border-purple-500/20 text-center">
            <p class="text-gray-400 text-sm tracking-wide">
                <i class="fas fa-envelope mr-2 text-purple-400"></i>
                Masih memiliki pertanyaan lain? Hubungi tim festival melalui kontak resmi kami.
            </p>
        </div>
    </div>
</section>