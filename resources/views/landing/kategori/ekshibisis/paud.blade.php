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
                    Syarat & Ketentuan
                </h2>
            </div>

            <!-- SYARAT & KETENTUAN LENGKAP (Langsung ditampilkan) -->
            <div class="fade-up mt-10">
                <div
                    class="glass-card rounded-3xl p-6 md:p-8 lg:p-10 transition-all duration-500">
                    <!-- SECTION A - Syarat Peserta -->
                    <div
                        class="mb-8 hover-card-glow glass-card-light rounded-2xl p-6 transition-all duration-300">
                        <div class="flex items-center gap-3 mb-5">
                            <div
                                class="w-10 h-10 rounded-xl bg-purple-600/20 flex items-center justify-center">
                                <i class="fas fa-users text-purple-400 text-lg"></i>
                            </div>
                            <h3 class="text-xl md:text-2xl font-bold text-white">
                                Syarat Peserta
                            </h3>
                        </div>
                        <ul class="space-y-3 text-gray-300 text-sm md:text-base">
                            <li class="flex items-start gap-3">
                                <i
                                    class="fas fa-check-circle text-purple-400 mt-1 text-sm"></i><span>Peserta merupakan pelajar aktif (SD-SMP) yang berdomisili
                                    dan bersekolah diseluruh Kabupaten Pacitan. Dibuktikan
                                    dengan scan/foto kartu pelajar dan surat keterangan dari
                                    sekolah pada pengumpulan daftar tim produksi.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i
                                    class="fas fa-check-circle text-purple-400 mt-1 text-sm"></i><span>Setiap sekolah boleh mengirimkan lebih dari satu
                                    karya.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i
                                    class="fas fa-check-circle text-purple-400 mt-1 text-sm"></i><span>Tahun produksi film minimal tahun 2023.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i
                                    class="fas fa-check-circle text-purple-400 mt-1 text-sm"></i><span>Karya film yang dikirim harus merupakan karya orisinal
                                    peserta, tidak mengandung unsur SARA, pornografi, ujaran
                                    kebencian, dan tidak melanggar hak cipta.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i
                                    class="fas fa-check-circle text-purple-400 mt-1 text-sm"></i><span>Tema film bersifat bebas, selama tetap relevan dan selaras
                                    dengan Festival Film Horor 2026.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i
                                    class="fas fa-check-circle text-purple-400 mt-1 text-sm"></i><span>Peserta wajib melampirkan surat pernyataan orisinalitas
                                    karya yang ditandatangani di atas materai oleh salah satu
                                    anggota tim atau pendamping.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i
                                    class="fas fa-check-circle text-purple-400 mt-1 text-sm"></i><span>Akun media sosial peserta tidak boleh di-private selama
                                    periode seleksi dan penjurian.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i
                                    class="fas fa-check-circle text-purple-400 mt-1 text-sm"></i><span>Peserta bersedia mengikuti proses seleksi dan penjurian
                                    sesuai jadwal yang ditentukan panitia FFH 2026.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i
                                    class="fas fa-check-circle text-purple-400 mt-1 text-sm"></i><span>Dengan mendaftar, peserta menyatakan setuju bahwa karya
                                    yang dikirimkan dapat digunakan oleh panitia untuk
                                    kepentingan edukasi dan publikasi non-komersial.</span>
                            </li>
                        </ul>
                    </div>

                    <!-- SECTION B - Ketentuan Umum Film -->
                    <div
                        class="mb-8 hover-card-glow glass-card-light rounded-2xl p-6 transition-all duration-300">
                        <div class="flex items-center gap-3 mb-5">
                            <div
                                class="w-10 h-10 rounded-xl bg-purple-600/20 flex items-center justify-center">
                                <i class="fas fa-film text-purple-400 text-lg"></i>
                            </div>
                            <h3 class="text-xl md:text-2xl font-bold text-white">
                                Ketentuan Umum Film
                            </h3>
                        </div>
                        <ul class="space-y-3 text-gray-300 text-sm md:text-base">
                            <li class="flex items-start gap-3">
                                <i class="fas fa-clock text-purple-400 mt-1 text-sm"></i><span>Durasi film maksimal 30 menit termasuk opening dan credit
                                    title.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-calendar text-purple-400 mt-1 text-sm"></i><span>Film diproduksi pada tahun 2023 ke atas.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-award text-purple-400 mt-1 text-sm"></i><span>Film yang sudah memenangkan Festival Film Horor 2025 tidak
                                    dapat diikutsertakan kembali.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-ghost text-purple-400 mt-1 text-sm"></i><span>Genre film wajib bertema horor.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-language text-purple-400 mt-1 text-sm"></i><span>Film boleh menggunakan bahasa Indonesia maupun bahasa
                                    daerah.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i
                                    class="fas fa-closed-captioning text-purple-400 mt-1 text-sm"></i><span>Jika menggunakan bahasa daerah wajib menyertakan subtitle
                                    bahasa Indonesia.</span>
                            </li>
                        </ul>
                    </div>

                    <!-- SECTION C - Spesifikasi Film -->
                    <div
                        class="mb-8 hover-card-glow glass-card-light rounded-2xl p-6 transition-all duration-300">
                        <div class="flex items-center gap-3 mb-5">
                            <div
                                class="w-10 h-10 rounded-xl bg-purple-600/20 flex items-center justify-center">
                                <i class="fas fa-sliders-h text-purple-400 text-lg"></i>
                            </div>
                            <h3 class="text-xl md:text-2xl font-bold text-white">
                                Spesifikasi Film
                            </h3>
                        </div>
                        <ul class="space-y-2 text-gray-300 text-sm md:text-base">
                            <li>
                                <i
                                    class="fas fa-check-circle text-purple-400 mr-2 text-xs"></i>
                                Resolusi minimal HD (1280 x 720 px) dan maksimal Full HD (1920
                                x 1080 px)
                            </li>
                            <li>
                                <i
                                    class="fas fa-check-circle text-purple-400 mr-2 text-xs"></i>
                                Format file: .MP4 atau .MOV
                            </li>
                            <li>
                                <i
                                    class="fas fa-check-circle text-purple-400 mr-2 text-xs"></i>
                                Frame rate bebas, disarankan 24-30 fps
                            </li>
                        </ul>
                    </div>

                    <!-- SECTION D - Berkas Wajib -->
                    <div
                        class="mb-8 hover-card-glow glass-card-light rounded-2xl p-6 transition-all duration-300">
                        <div class="flex items-center gap-3 mb-5">
                            <div
                                class="w-10 h-10 rounded-xl bg-purple-600/20 flex items-center justify-center">
                                <i class="fas fa-folder-open text-purple-400 text-lg"></i>
                            </div>
                            <h3 class="text-xl md:text-2xl font-bold text-white">
                                Berkas Yang Wajib Dikumpulkan
                            </h3>
                        </div>
                        <ul class="space-y-3 text-gray-300 text-sm md:text-base">
                            <li class="flex items-start gap-3">
                                <i class="fas fa-file-alt text-purple-400 mt-1 text-sm"></i>
                                <span>Sinopsis film singkat maksimal 300 kata</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-video text-purple-400 mt-1 text-sm"></i>
                                <span>Trailer film (maksimal 2 menit)</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-image text-purple-400 mt-1 text-sm"></i>
                                <span>Poster film JPEG/PNG minimal 1500x2000 px</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-camera text-purple-400 mt-1 text-sm"></i>
                                <span>Minimal 5 foto proses produksi film atau cuplikan film
                                    (still image)</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-users text-purple-400 mt-1 text-sm"></i>
                                <span>Data singkat tim produksi, meliputi:
                                    <br />
                                    - Produser <br />
                                    - Sutradara <br />
                                    - Kru Produksi
                                </span>
                            </li>
                        </ul>
                    </div>

                    <!-- SECTION E - Catatan Tambahan -->
                    <div
                        class="hover-card-glow glass-card-light rounded-2xl p-6 transition-all duration-300">
                        <div class="flex items-center gap-3 mb-5">
                            <div
                                class="w-10 h-10 rounded-xl bg-purple-600/20 flex items-center justify-center">
                                <i
                                    class="fas fa-exclamation-triangle text-purple-400 text-lg"></i>
                            </div>
                            <h3 class="text-xl md:text-2xl font-bold text-white">
                                Catatan Tambahan
                            </h3>
                        </div>
                        <ul class="space-y-2 text-gray-300 text-sm md:text-base">
                            <li>
                                <i
                                    class="fas fa-check-circle text-purple-400 mr-2 text-xs"></i>
                                Panitia berhak mendiskualifikasi peserta apabila data atau
                                karya yang dikirim tidak sesuai ketentuan.
                            </li>
                            <li>
                                <i
                                    class="fas fa-check-circle text-purple-400 mr-2 text-xs"></i>
                                Dengan mengirimkan karya, peserta dianggap menyetujui seluruh
                                peraturan Festival Film Horor 2026.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- ACTION BUTTON SECTION -->
            <div class="mt-12 text-center fade-up">
                @php
                $setting = \App\Models\SubmissionSetting::current();
                $submissionOpen = \App\Models\SubmissionSetting::isOpen();
                $isBeforeOpen = $setting && now()->lessThan($setting->open_at);
                @endphp

                <div class="glass-card-light rounded-3xl p-6 md:p-8">
                    <div class="flex flex-col sm:flex-row gap-5 justify-center items-center">
                        <!-- Tombol 1: Unduh Petunjuk Teknis -->
                        <a href="{{ asset('landing/pdf/ffh-sdsmp.pdf') }}" download=" Petunjuk_Teknis_FFH_SD_SMP.pdf"
                            class="btn-glass px-8 md:px-10 py-3 md:py-4 rounded-full text-white font-semibold text-base md:text-lg transition-all duration-300 inline-flex items-center gap-3 group">
                            <i class="fas fa-download group-hover:-translate-y-1 transition-transform duration-300"></i>
                            Unduh Petunjuk Teknis
                        </a>

                        <!-- Tombol 2: Submit Sekarang -->
                        @if($submissionOpen)
                        <a href="{{ route('register') }}"
                            class="btn-gradient px-8 md:px-10 py-3 md:py-4 rounded-full text-white font-semibold text-base md:text-lg transition-all duration-300 inline-flex items-center gap-3 group">
                            <i class="fas fa-paper-plane group-hover:translate-x-1 transition-transform duration-300"></i>
                            Submit Sekarang
                        </a>
                        @elseif($isBeforeOpen)
                        <button disabled
                            class="px-8 md:px-10 py-3 md:py-4 rounded-full text-gray-400 font-semibold text-base md:text-lg inline-flex items-center gap-3 cursor-not-allowed bg-white/5 border border-white/10">
                            <i class="fas fa-clock text-gray-500"></i>
                            Belum Dibuka
                        </button>
                        @else
                        <a href="#"
                            class="px-8 md:px-10 py-3 md:py-4 rounded-full text-gray-300 font-semibold text-base md:text-lg inline-flex items-center gap-3 bg-white/5 border border-white/10 hover:bg-white/10 transition-all duration-300">
                            <i class="fas fa-lock text-gray-400"></i>
                            Submission Ditutup
                        </a>
                        @endif
                    </div>

                    <p class="text-gray-300 text-xs mt-5 text-center">
                        @if($setting)
                        @if($submissionOpen)
                        *Pendaftaran ditutup {{ $setting->close_at->translatedFormat('d F Y') }} pukul {{ $setting->close_at->format('H:i') }} WIB
                        @elseif($isBeforeOpen)
                        *Pendaftaran dibuka {{ $setting->open_at->translatedFormat('d F Y') }} pukul {{ $setting->open_at->format('H:i') }} WIB
                        @else
                        *Pendaftaran telah ditutup sejak {{ $setting->close_at->translatedFormat('d F Y') }} pukul {{ $setting->close_at->format('H:i') }} WIB
                        @endif
                        @else
                        *Jadwal pendaftaran belum ditentukan
                        @endif
                    </p>
                </div>
            </div>
        </section>
    </main>
@endsection