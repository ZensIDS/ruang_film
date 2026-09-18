<!doctype html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Guide Book — Festival Film Horor 2026</title>
    <link rel="icon" href="{{ asset('img/logo.png') }}" type="image/png">

    <!-- Tailwind CSS v4 (sama seperti landing utama, biar konsisten) -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,600;14..32,700;14..32,800&family=Space+Grotesk:wght@400;500;600;700&display=swap"
        rel="stylesheet" />

    <!-- pdf.js — merender tiap halaman PDF jadi gambar -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

    <!-- page-flip — efek buka buku dari kumpulan gambar halaman -->
    <script src="https://unpkg.com/page-flip/dist/js/page-flip.browser.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at top, #1a0b2e 0%, #0a0410 60%, #050208 100%);
            color: #f5f3ff;
            min-height: 100vh;
        }

        .font-display { font-family: 'Space Grotesk', sans-serif; }

        .topbar {
            background: linear-gradient(180deg, rgba(24, 12, 40, 0.92), rgba(16, 8, 28, 0.85));
            border-bottom: 1px solid rgba(139, 92, 246, 0.25);
            backdrop-filter: blur(8px);
        }

        .btn-ghost {
            border: 1px solid rgba(139, 92, 246, 0.35);
            transition: all .2s ease;
        }
        .btn-ghost:hover {
            border-color: rgba(168, 85, 247, 0.7);
            background: rgba(139, 92, 246, 0.12);
        }

        .btn-gradient {
            background: linear-gradient(135deg, #8B5CF6, #6D28D9);
            transition: all .2s ease;
        }
        .btn-gradient:hover {
            box-shadow: 0 0 20px rgba(139, 92, 246, 0.5);
            transform: translateY(-1px);
        }

        #flipbook-wrapper {
            perspective: 2200px;
        }

        #flipbook .page {
            background-color: #fff;
            overflow: hidden;
        }
        #flipbook .page img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
        }

        .nav-btn {
            width: 44px;
            height: 44px;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(139, 92, 246, 0.35);
            transition: all .2s ease;
        }
        .nav-btn:hover:not(:disabled) {
            background: rgba(139, 92, 246, 0.18);
            border-color: rgba(168, 85, 247, 0.7);
        }
        .nav-btn:disabled {
            opacity: .35;
            cursor: not-allowed;
        }

        .spinner {
            width: 46px;
            height: 46px;
            border-radius: 9999px;
            border: 4px solid rgba(139, 92, 246, 0.25);
            border-top-color: #a855f7;
            animation: spin 0.9s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>

<body class="flex flex-col min-h-screen">

    <!-- Top bar -->
    <header class="topbar sticky top-0 z-20">
        <div class="max-w-7xl mx-auto px-4 md:px-6 py-3 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('landing.home') }}" class="btn-ghost rounded-full w-10 h-10 flex items-center justify-center flex-shrink-0" title="Kembali ke Beranda">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div class="min-w-0">
                    <h1 class="font-display font-bold text-base md:text-lg truncate">Guide Book</h1>
                    <p class="text-xs text-gray-400 truncate">Festival Film Horor 2026</p>
                </div>
            </div>
            <a href="{{ $pdfUrl }}" download="{{ $pdfName }}"
                class="btn-gradient text-white text-sm font-semibold px-4 md:px-5 py-2.5 rounded-full inline-flex items-center gap-2 flex-shrink-0">
                <i class="fas fa-download"></i>
                <span class="hidden sm:inline">Download PDF</span>
            </a>
        </div>
    </header>

    <!-- Viewer -->
    <main class="flex-1 flex items-center justify-center px-4 py-6">
        <div id="viewer-shell" class="w-[85vw] h-[85vh] max-w-[1400px] mx-auto flex flex-col items-center justify-center gap-5">

            <div id="loading-state" class="flex flex-col items-center gap-4 text-center">
                <div class="spinner"></div>
                <p id="loading-text" class="text-sm text-gray-300">Menyiapkan halaman buku...</p>
            </div>

            <div id="error-state" class="hidden flex-col items-center gap-3 text-center max-w-md">
                <i class="fas fa-triangle-exclamation text-3xl text-purple-400"></i>
                <p class="text-sm text-gray-300">
                    Gagal memuat pratinjau flipbook. Silakan unduh PDF-nya langsung lewat tombol di atas.
                </p>
            </div>

            <div id="flipbook-wrapper" class="hidden w-full h-full flex flex-col items-center justify-center gap-5">
                <div id="flipbook" class="mx-auto"></div>

                <div class="flex items-center gap-4">
                    <button id="btn-prev" class="nav-btn" title="Halaman sebelumnya">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <span class="text-sm text-gray-300 font-medium" id="page-indicator">- / -</span>
                    <button id="btn-next" class="nav-btn" title="Halaman berikutnya">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <p class="text-xs text-gray-500 text-center max-w-sm">
                    Klik/geser tepi halaman untuk membuka lembar, seperti buku fisik.
                </p>
            </div>
        </div>
    </main>

    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc =
            'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        const PDF_URL = @json($pdfUrl);

        const loadingState    = document.getElementById('loading-state');
        const loadingText     = document.getElementById('loading-text');
        const errorState      = document.getElementById('error-state');
        const flipbookWrapper = document.getElementById('flipbook-wrapper');
        const flipbookEl      = document.getElementById('flipbook');
        const btnPrev         = document.getElementById('btn-prev');
        const btnNext         = document.getElementById('btn-next');
        const pageIndicator   = document.getElementById('page-indicator');

        async function renderPdfToImages(url) {
            const pdf = await pdfjsLib.getDocument(url).promise;
            const images = [];

            // Skala dasar 3.0 sudah tajam untuk layar normal (DPR 1), tapi di
            // layar retina/HP (DPR 2-3) gambar itu ikut di-stretch lagi oleh
            // browser sehingga jadi blur. Kalikan dengan devicePixelRatio biar
            // resolusi gambar tetap tajam di layar high-DPI, dibatasi maksimal
            // 2x supaya ukuran canvas & memori browser tidak meledak.
            const dpr = Math.min(window.devicePixelRatio || 1, 2);
            const renderScale = 3.0 * dpr;

            for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                loadingText.textContent = `Memuat halaman ${pageNumber} dari ${pdf.numPages}...`;

                const page = await pdf.getPage(pageNumber);
                const viewport = page.getViewport({ scale: renderScale });

                const canvas = document.createElement('canvas');
                canvas.width = viewport.width;
                canvas.height = viewport.height;

                const ctx = canvas.getContext('2d');
                ctx.imageSmoothingEnabled = true;
                ctx.imageSmoothingQuality = 'high';

                await page.render({
                    canvasContext: ctx,
                    viewport,
                }).promise;

                images.push(canvas.toDataURL('image/jpeg', 0.92));
            }

            return { images, sampleViewport: await pdf.getPage(1).then(p => p.getViewport({ scale: 1 })) };
        }

        function initFlipbook({ images, sampleViewport }) {
            try {
                // Gunakan rasio dari viewport asli PDF langsung tanpa menunggu Image.onload
                const ratio = sampleViewport.width / sampleViewport.height;

                const shell = document.getElementById('viewer-shell');
                const availableHeight = Math.max(shell.clientHeight - 120, 360);
                const bookHeight = Math.min(availableHeight, 720); 
                const singlePageWidth = Math.round(bookHeight * ratio);

                // PENTING: loadFromImages() StPageFlip menggambar tiap halaman ke
                // <canvas> INTERNAL seukuran width/height config di atas (dalam CSS
                // px, tidak dikali device pixel ratio) -- jadi walau gambar sumber
                // kita sudah tajam, hasil akhirnya tetap digambar ulang ke canvas
                // beresolusi rendah lalu di-stretch browser => blur, terlepas dari
                // seberapa tinggi resolusi gambar sumbernya.
                //
                // Solusinya: pakai loadFromHtml() dengan elemen <img> asli. Mode
                // HTML ini membiarkan browser sendiri yang melakukan scaling
                // gambar (native image scaling), yang jauh lebih tajam dibanding
                // digambar ulang lewat canvas internal library.
                images.forEach((src, index) => {
                    const pageEl = document.createElement('div');
                    pageEl.className = 'page';
                    if (index === 0 || index === images.length - 1) {
                        pageEl.dataset.density = 'hard';
                    }

                    const img = document.createElement('img');
                    img.src = src;
                    img.alt = `Halaman ${index + 1}`;
                    img.draggable = false;

                    pageEl.appendChild(img);
                    flipbookEl.appendChild(pageEl);
                });

                // Inisialisasi PageFlip
                const pageFlip = new St.PageFlip(flipbookEl, {
                    width: singlePageWidth,
                    height: bookHeight,
                    size: 'fixed',
                    showCover: true,
                    maxShadowOpacity: 0.5,
                    mobileScrollSupport: false,
                });

                // Muat halaman dari elemen HTML yang baru dibuat (bukan
                // loadFromImages) supaya browser yang menangani scaling gambar.
                pageFlip.loadFromHtml(flipbookEl.querySelectorAll('.page'));

                function updateIndicator() {
                    pageIndicator.textContent = `${pageFlip.getCurrentPageIndex() + 1} / ${images.length}`;
                }

                pageFlip.on('flip', updateIndicator);
                updateIndicator();

                btnPrev.onclick = () => pageFlip.flipPrev();
                btnNext.onclick = () => pageFlip.flipNext();

                // Tampilkan flipbook dan sembunyikan loading
                loadingState.classList.add('hidden');
                flipbookWrapper.classList.remove('hidden');
                flipbookWrapper.classList.add('flex');
            } catch (err) {
                console.error('Gagal inisialisasi Flipbook:', err);
                loadingState.classList.add('hidden');
                errorState.classList.remove('hidden');
                errorState.classList.add('flex');
            }
        }

        // Jalankan proses
        renderPdfToImages(PDF_URL)
            .then(initFlipbook)
            .catch(function (error) {
                console.error('Gagal memuat guide book:', error);
                loadingState.classList.add('hidden');
                errorState.classList.remove('hidden');
                errorState.classList.add('flex');
            });
    </script>
</body>

</html>