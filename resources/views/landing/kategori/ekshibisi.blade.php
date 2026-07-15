@extends('layouts.landing.master')
@section('main')
    <main class="relative z-10">
        <!-- ================================================== -->
        <!-- SECTION 1: HERO (90vh) -->
        <!-- ================================================== -->
        <section
            id="program"
            class="relative min-h-[90vh] flex items-center justify-center px-6 py-20 overflow-hidden bg-cover bg-center"
            style="
          background-image: url({{ asset('landing/images/BACKGROUND FFH 2026.png') }}); ">
            <!-- Background blur abstract -->
            <div class="absolute inset-0 bg-black/50"></div>
            <div class="absolute inset-0 premium-glow opacity-40"></div>
            <div class="bg-glow-abstract"></div>
            <div class="relative max-w-6xl w-full mx-auto fade-up">
                <!-- Banner glassmorphism card besar -->
                <div
                    class="glass-card p-8 md:p-12 lg:p-16 text-center shadow-2xl border border-purple-400/30">
                    <div class="space-y-6 md:space-y-8">
                        <p
                            class="text-gray-300 text-base md:text-xl max-w-2xl mx-auto font-light leading-relaxed">
                            Kompetisi Film Kategori Ekshibisi Lokal Pacitan
                        </p>
                        <h1
                            class="text-5xl md:text-7xl lg:text-8xl font-black uppercase tracking-tighter leading-[1.1] bg-gradient-to-r from-white via-purple-200 to-purple-300 bg-clip-text text-transparent drop-shadow-2xl">
                            Piala Tengkorak <br />
                            Gunung Karst
                        </h1>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================================================== -->
        <!-- SECTION DETAIL KATEGORI KOMPETISI -->
        <!-- ================================================== -->
        <section
            class="max-w-7xl mx-auto px-6 md:px-10 py-24 md:py-28 competition-detail-section">
            <!-- HEADER SECTION -->
            <div class="fade-up">
                <p
                    class="text-purple-400 text-sm md:text-base uppercase tracking-wider font-semibold mb-2">
                    KOMPETISI FILM KATEGORI EKHSIBISI LOKAL PACITAN
                </p>
                <h2
                    class="text-3xl md:text-5xl font-bold text-left border-l-8 border-purple-500 pl-6 tracking-tight">
                    Informasi Kompetisi
                </h2>
            </div>

            <!-- TAB NAVIGATION -->
            <div class="mt-10 fade-up">
                <div
                    class="inline-flex flex-wrap gap-3 p-1.5 glass-card-light rounded-full">
                    <button
                        class="tab-btn px-6 md:px-8 py-2.5 md:py-3 rounded-full text-sm md:text-base font-semibold transition-all duration-300 active"
                        data-tab="deskripsi">
                        <i class="fas fa-align-left mr-2"></i>Deskripsi
                    </button>
                </div>
            </div>

            <!-- TAB CONTENT 1: DESKRIPSI -->
            <div class="tab-pane active fade-up" id="deskripsi-tab">
                <div
                    class="glass-card rounded-3xl p-6 md:p-8 lg:p-10 mt-6 transition-all duration-500 hover:shadow-[0_0_30px_rgba(109,40,217,0.2)]">
                    <!-- Icon dekoratif -->
                    <div class="flex justify-between items-start mb-6">
                        <div
                            class="w-12 h-12 rounded-2xl bg-purple-600/20 flex items-center justify-center border border-purple-500/30">
                            <i class="fas fa-trophy text-purple-400 text-2xl"></i>
                        </div>
                        <div
                            class="w-16 h-[2px] bg-gradient-to-r from-purple-500 to-transparent"></div>
                    </div>

                    <!-- Deskripsi utama -->
                    <div
                        class="space-y-6 text-gray-300 leading-relaxed text-base md:text-lg">
                        <p class="text-xl md:text-2xl font-semibold text-purple-300 mb-4">
                            Piala Tengkorak Gunung Karst
                        </p>
                        <p>
                            Piala Tengkorak Gunung Karst merepresentasikan fondasi awal
                            dalam ekosistem FFH 2026 yang identik dengan kategori eksibisi,
                            yaitu anak-anak dan komunitas lokal Pacitan yang masih berada
                            dalam fase eksplorasi dunia perfilman. la menjadi simbol
                            kedekatan dengan tanah lokal Pacitan, tempat ide-ide pertama
                            lahir dari lingkungan sehari-hari, komunitas, dan proses belajar
                            yang masih terbuka.
                        </p>
                        <p>
                            Piala Tengkorak Gunung Karst terinsipirasi dari alam pegunungan
                            Karst di Pacitan yang terbentuk dari batu kapur selama ribuan
                            tahun sebagai simbol ruang tumbuh awal pengetahuan, kreativitas,
                            dan ekspresi budaya lokal.
                        </p>
                        <p>
                            Kategori Eksibisi ini terbagi menjadi dua sub kategori: <br />
                            - Pelajar (Jenjang SD, SMP se-Kabupaten Pacitan) <br />
                            - Organisasi (PKK PAUD & TK, dan Karang Taruna Pacitan) &
                            Komunitas Lokal Pacitan <br />
                            Kategori ini berfungsi sebagau fondasi awal sebelum memasuki
                            level kompetisi yang lebih tinggi dalam FFH 2026, menekankan
                            partisipasi dalam eksplorasi, bukan kompetisi yang ketat,
                            sehingga komposisi juri dirancang lebih inklusif dan
                            representatif.
                        </p>
                    </div>

                    <!-- Garis pemisah dekoratif -->
                    <div
                        class="mt-8 pt-6 border-t border-purple-500/20 flex items-center gap-3">
                        <i class="fas fa-skull text-purple-400/50 text-sm"></i>
                        <span class="text-gray-500 text-xs tracking-wide">#RuangFilmHoror2026</span>
                    </div>
                </div>
            </div>

            <!-- ACTION BUTTON SECTION -->
            <div class="mt-12 text-center fade-up">
                <div class="glass-card-light rounded-3xl p-6 md:p-8">

                    <!-- Grid 2 Kolom untuk Kategori Pendaftaran -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                        <!-- CARD 1: Kategori Organisasi -->
                        <a
                            href="/ekshibisi/organisasi"
                            class="registration-card group block transition-all duration-500">
                            <div
                                class="glass-card-light rounded-2xl p-6 md:p-8 transition-all duration-500 group-hover:-translate-y-2 group-hover:shadow-[0_0_30px_rgba(109,40,217,0.4)] h-full">
                                <!-- Icon -->
                                <div
                                    class="w-16 h-16 rounded-2xl bg-purple-600/20 flex items-center justify-center border border-purple-500/30 mb-5 group-hover:scale-110 transition-transform duration-300 group-hover:border-purple-400">
                                    <i class="fas fa-users text-purple-400 text-3xl"></i>
                                </div>

                                <!-- Badge Kategori -->
                                <div class="inline-block mb-3">
                                    <span
                                        class="text-[11px] font-bold uppercase tracking-wider bg-purple-600/30 text-purple-300 px-3 py-1 rounded-full border border-purple-500/30">
                                        KATEGORI KHUSUS
                                    </span>
                                </div>

                                <!-- Judul -->
                                <h3
                                    class="text-2xl md:text-3xl font-bold text-white mb-3 group-hover:text-purple-300 transition-colors duration-300">
                                    Kategori Organisasi
                                </h3>

                                <!-- Deskripsi -->
                                <p
                                    class="text-gray-300 text-sm md:text-base leading-relaxed mb-6">
                                    PKK, PAUD & TK, Karang Taruna Pacitan, dan komunitas lokal
                                    yang berada di wilayah Kabupaten Pacitan.
                                </p>

                                <!-- Tombol Daftar -->
                                <div
                                    class="inline-flex items-center gap-2 text-purple-400 font-semibold text-sm md:text-base group-hover:text-purple-300 transition-all duration-300 group-hover:gap-3">
                                    Daftar Sekarang
                                    <i
                                        class="fas fa-arrow-right text-xs group-hover:translate-x-1 transition-transform duration-300"></i>
                                </div>
                            </div>
                        </a>

                        <!-- CARD 2: Kategori Pelajar PAUD – SMP Pacitan -->
                        <a
                            href="/ekshibisi/paud"
                            class="registration-card group block transition-all duration-500">
                            <div
                                class="glass-card-light rounded-2xl p-6 md:p-8 transition-all duration-500 group-hover:-translate-y-2 group-hover:shadow-[0_0_30px_rgba(109,40,217,0.4)] h-full">
                                <!-- Icon -->
                                <div
                                    class="w-16 h-16 rounded-2xl bg-purple-600/20 flex items-center justify-center border border-purple-500/30 mb-5 group-hover:scale-110 transition-transform duration-300 group-hover:border-purple-400">
                                    <i class="fas fa-school text-purple-400 text-3xl"></i>
                                </div>

                                <!-- Badge Kategori -->
                                <div class="inline-block mb-3">
                                    <span
                                        class="text-[11px] font-bold uppercase tracking-wider bg-purple-600/30 text-purple-300 px-3 py-1 rounded-full border border-purple-500/30">
                                        KATEGORI PELAJAR
                                    </span>
                                </div>

                                <!-- Judul -->
                                <h3
                                    class="text-2xl md:text-3xl font-bold text-white mb-3 group-hover:text-purple-300 transition-colors duration-300">
                                    Kategori Pelajar SD – SMP Pacitan
                                </h3>

                                <!-- Deskripsi -->
                                <p
                                    class="text-gray-300 text-sm md:text-base leading-relaxed mb-6">
                                    Terbuka bagi peserta didik SD dan SMP yang berada
                                    di wilayah Kabupaten Pacitan.
                                </p>

                                <!-- Tombol Daftar -->
                                <div
                                    class="inline-flex items-center gap-2 text-purple-400 font-semibold text-sm md:text-base group-hover:text-purple-300 transition-all duration-300 group-hover:gap-3">
                                    Daftar Sekarang
                                    <i
                                        class="fas fa-arrow-right text-xs group-hover:translate-x-1 transition-transform duration-300"></i>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Footer Note -->
                    <!-- <div class="mt-8 pt-6 border-t border-purple-500/20">
                        <p class="text-gray-500 text-xs md:text-sm">
                            <i class="fas fa-clock mr-2 text-purple-400/70"></i>
                            *Pendaftaran seluruh kategori ditutup pada 28 Februari 2026
                            pukul 23.59 WIB.
                        </p>
                    </div> -->
                </div>
            </div>
        </section>
    </main>
@endsection