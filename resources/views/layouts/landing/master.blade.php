<!doctype html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Festival Film Horor 2026</title>
    <link rel="icon" href="{{ asset('img/logo.png') }}" type="image/png">

    <!-- Tailwind CSS v4 -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <!-- Font Awesome Icons (untuk sentuhan premium) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />

    <!-- Google Fonts: Inter & Space Grotesk untuk nuansa cinematic -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,600;14..32,700;14..32,800&family=Space+Grotesk:wght@400;500;600;700&display=swap"
        rel="stylesheet" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('landing/css/style.css') }}" />

        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        /* Select2 dark theme override */
        .select2-container--default .select2-selection--single {
            background: linear-gradient(180deg, rgba(24, 12, 40, 0.92), rgba(16, 8, 28, 0.96));
            border: 1px solid rgba(139, 92, 246, 0.28);
            border-radius: 16px;
            min-height: 52px;
            display: flex;
            align-items: center;
            padding: 0 16px;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
            transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
        }

        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: rgba(168, 85, 247, 0.55);
            box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.12);
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #f5f3ff;
            font-size: 14px;
            line-height: 1.4;
            min-height: 50px;
            display: flex;
            align-items: center;
            padding: 0 28px 0 0;
        }

        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #8b7aa8;
        }

        .select2-container--default .select2-selection--single .select2-selection__clear {
            color: #c4b5fd;
            margin-right: 10px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 50px;
            right: 14px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #7c3aed transparent transparent transparent;
        }

        .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent #7c3aed transparent;
        }

        .select2-dropdown {
            background: #12091f;
            border: 1px solid rgba(139, 92, 246, 0.35);
            border-radius: 16px;
            overflow: hidden;
            padding: 8px;
            margin-top: 8px;
            box-shadow: 0 24px 48px rgba(3, 2, 9, 0.45);
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(139, 92, 246, 0.22);
            border-radius: 10px;
            color: #f5f3ff;
            padding: 10px 12px;
            outline: none;
        }

        .select2-container--default .select2-results > .select2-results__options {
            max-height: 320px;
        }

        .select2-container--default .select2-results__option {
            background: transparent;
            color: #ddd6fe;
            font-size: 13px;
            padding: 10px 12px;
            border-radius: 10px;
            margin-top: 2px;
            transition: background .15s ease, color .15s ease;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected],
        .select2-container--default .select2-results__option--selectable:hover {
            background: rgba(139, 92, 246, 0.24);
            color: #f8f5ff;
        }

        .select2-container--default .select2-results__option--selected,
        .select2-container--default .select2-results__option[aria-selected=true] {
            background: rgba(91, 33, 182, 0.42);
            color: #f5f3ff;
        }

        .select2-container--default .select2-results__option--selected.select2-results__option--highlighted[aria-selected],
        .select2-container--default .select2-results__option--highlighted[aria-selected=true] {
            background: rgba(109, 40, 217, 0.52);
            color: #ffffff;
        }

        .select2-container {
            width: 100% !important;
        }

        .field-group {
            margin-bottom: 20px;
        }

        .field-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #c4b5fd;
            margin-bottom: 6px;
            letter-spacing: .3px;
        }

        .field-input {
            width: 100%;
            padding: 10px 14px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(139, 92, 246, 0.25);
            border-radius: 12px;
            color: #f1f0ff;
            font-size: 14px;
            outline: none;
            transition: border-color .2s, background .2s;
            box-sizing: border-box;
        }

        .field-input:focus {
            border-color: rgba(139, 92, 246, 0.6);
            background: rgba(255, 255, 255, 0.08);
        }

        .field-input::placeholder {
            color: #6b7280;
        }

        .field-input:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        .landing-native-select {
            color-scheme: dark;
        }

        .landing-native-select option,
        .landing-native-select optgroup {
            background: #140b22;
            color: #f5f3ff;
        }

        .landing-native-select option:checked {
            background: #4c1d95;
            color: #ffffff;
        }

        .field-input option {
            background: #1a0a2e;
            color: #f1f0ff;
        }

        .section-divider {
            font-size: 13px;
            font-weight: 700;
            color: #a78bfa;
            border-left: 3px solid #7c3aed;
            padding-left: 12px;
            margin: 24px 0 16px;
        }

        .btn-submit {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #7c3aed, #9333ea);
            border: none;
            border-radius: 14px;
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity .2s, transform .2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit:hover {
            opacity: .88;
            transform: translateY(-1px);
        }

        .input-icon-wrap {
            position: relative;
        }

        .input-icon-wrap i {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #7c3aed;
            font-size: 13px;
        }

        .input-icon-wrap .field-input {
            padding-left: 36px;
        }

        .error-msg {
            color: #f87171;
            font-size: 12px;
            margin-top: 4px;
        }
    </style>
    @stack('styles')
</head>

<body class="text-white overflow-x-hidden">
    @php
        $cartBadgeCount = $landingCartCount > 99 ? '99+' : $landingCartCount;
    @endphp
    <!-- ================================================== -->
    <!-- BACKGROUBD BG -->
    <!-- ================================================== -->
    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden">
        <div class="absolute top-[-20%] left-[-10%] w-[60%] h-[60%] bg-purple-700/20 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-0 right-0 w-[70%] h-[50%] bg-violet-800/20 blur-[130px]"></div>
    </div>

    <!-- ================================================== -->
    <!-- NAVBAR STICKY dengan glassmorphism -->
    <!-- ================================================== -->
    <nav
        class="sticky top-0 z-50 w-full transition-all duration-300 backdrop-blur-xl bg-[#0f0f23]/70 border-b border-purple-500/20 shadow-md">
        <div class="max-w-7xl mx-auto px-6 md:px-10 py-4 flex justify-between items-center">
            <!-- Logo -->
            <a href="{{ route('landing.home') }}" class="flex items-center">
                <img src="{{ asset('landing/images/RUANG FILM - GREEN.png') }}" alt="Festival Ruang Film Horor 2026"
                    class="h-12 md:h-14 w-auto object-contain transition duration-300 hover:scale-105" />
            </a>
            <!-- Menu kanan (Desktop) -->
            <div class="hidden md:flex items-center space-x-8 text-sm font-medium">
                <a href="/" class="nav-link {{ request()->routeIs('landing.home') ? 'text-purple-300' : 'text-gray-200' }} font-semibold hover:text-purple-300 transition">Home</a>
                <a href="/program" class="nav-link {{ request()->is('program') ? 'text-purple-300' : 'text-gray-200' }} hover:text-purple-300 transition">Program</a>
                <a href="/portal" class="nav-link {{ request()->is('portal') ? 'text-purple-300' : 'text-gray-200' }} hover:text-purple-300 transition">Berita</a>
                <a href="/merchandise" class="nav-link {{ request()->is('merchandise') ? 'text-purple-300' : 'text-gray-200' }} hover:text-purple-300 transition">Merchandise</a>
                {{-- Keranjang --}}
                <a href="{{ auth()->check() ? route('cart.index') : route('login') }}" class="relative inline-flex text-gray-200 hover:text-purple-300 transition" title="Keranjang belanja">
                    <i class="fas fa-shopping-cart text-lg"></i>
                    <span id="cart-count"
                        class="absolute -top-2.5 -right-3 min-w-[18px] h-[18px] px-1 bg-red-500 text-white text-[10px] leading-none font-bold rounded-full flex items-center justify-center shadow-[0_0_0_2px_rgba(15,15,35,0.95)] {{ $landingCartCount > 0 ? '' : 'hidden' }}">
                        {{ $cartBadgeCount }}
                    </span>
                </a>
                @if(auth()->check())
                <a href="{{ route('orders.index') }}" class="nav-link text-gray-200 hover:text-purple-300 transition">Invoice</a>
                @if(auth()->user()->role === 'umum')
                <a href="{{ route('user-detail.index') }}" class="nav-link text-gray-200 hover:text-purple-300 transition">Biodata</a>
                @else
                <a href="{{ route('dashboard') }}"
                    class="btn-gradient px-5 py-2 rounded-full text-white text-sm font-semibold tracking-wide shadow-lg transition-all">Dashboard</a>
                @endif
                @else
                <a href="{{ route('login') }}"
                    class="btn-gradient px-5 py-2 rounded-full text-white text-sm font-semibold tracking-wide shadow-lg transition-all">Login</a>
                @endif
            </div>
            <!-- Mobile menu icon + dropdown sederhana (responsive) -->
            <div class="md:hidden flex items-center gap-4">
                {{-- Keranjang mobile --}}
                <a href="{{ auth()->check() ? route('cart.index') : route('login') }}" class="relative inline-flex text-gray-200 hover:text-purple-300 transition" title="Keranjang">
                    <i class="fas fa-shopping-cart text-lg"></i>
                    <span id="cart-count-mobile"
                        class="absolute -top-2.5 -right-3 min-w-[18px] h-[18px] px-1 bg-red-500 text-white text-[10px] leading-none font-bold rounded-full flex items-center justify-center shadow-[0_0_0_2px_rgba(15,15,35,0.95)] {{ $landingCartCount > 0 ? '' : 'hidden' }}">
                        {{ $cartBadgeCount }}
                    </span>
                </a>
                <button id="mobile-menu-btn" class="text-purple-300 text-2xl focus:outline-none">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
        <!-- Mobile dropdown menu (hidden by default) -->
        <div id="mobile-menu"
            class="md:hidden hidden flex-col bg-[#0f0f23]/90 backdrop-blur-xl border-t border-purple-500/20 px-6 pb-5 space-y-4 text-base font-medium">
            <a href="/" class="nav-link block py-2 text-gray-200 hover:text-purple-300">Home</a>
            <a href="/program" class="nav-link block py-2 text-gray-200 hover:text-purple-300">Program</a>
            <a href="/portal" class="nav-link block py-2 text-gray-200 hover:text-purple-300">Berita</a>
            <a href="{{ route('merchandise') }}" class="nav-link block py-2 text-gray-200 hover:text-purple-300">Merchandise</a>
            @if(auth()->check())
            <a href="{{ route('orders.index') }}" class="nav-link block py-2 text-gray-200 hover:text-purple-300">Invoice</a>
            @if(auth()->user()->role === 'umum')
            <a href="{{ route('user-detail.index') }}" class="nav-link block py-2 text-gray-200 hover:text-purple-300">Biodata</a>
            @else
            <a href="{{ route('dashboard') }}"
                class="btn-gradient inline-block text-center px-4 py-2 rounded-full text-white font-semibold">Dashboard</a>
            @endif
            @else
            <a href="{{ route('login') }}"
                class="btn-gradient inline-block text-center px-4 py-2 rounded-full text-white font-semibold">Login</a>
            @endif
        </div>
    </nav>

    @if(session('success') || session('warning') || session('error'))
    <div class="max-w-7xl mx-auto px-6 md:px-10 pt-6 relative z-20">
        @if(session('success'))
        <div class="rounded-2xl border border-green-500/30 bg-green-500/10 text-green-300 px-5 py-4 mb-3">
            {{ session('success') }}
        </div>
        @endif
        @if(session('warning'))
        <div class="rounded-2xl border border-yellow-500/30 bg-yellow-500/10 text-yellow-200 px-5 py-4 mb-3">
            {{ session('warning') }}
        </div>
        @endif
        @if(session('error'))
        <div class="rounded-2xl border border-red-500/30 bg-red-500/10 text-red-300 px-5 py-4 mb-3">
            {{ session('error') }}
        </div>
        @endif
    </div>
    @endif

    @yield('main')
    @include('layouts.landing.footer')
    <!-- Custom JS -->
    <script src="{{ asset('landing/js/script.js') }}"></script>
    <!-- Vanilla JavaScript: Smooth Scroll, Mobile Menu, Intersection Observer (fade-up) -->
    <script src="{{ asset('landing/js/vanila1.js') }}"></script>
    <!-- Footer -->
    <script src="{{ asset('landing/js/footer.js') }}"></script>
    <!-- Select2 -->
    <script>
        // Init Select2
        function initSelect2(selector, options = {}) {
            const element = $(selector);

            if (!element.length || element.prop('tagName') !== 'SELECT') {
                return;
            }

            if (element.hasClass('select2-hidden-accessible')) {
                element.select2('destroy');
            }

            const placeholder = options.placeholder || '';
            const allowClear = options.allowClear === true;
            const minimumResultsForSearch = options.minimumResultsForSearch ?? 0;

            element.select2({
                placeholder: placeholder,
                allowClear: allowClear,
                minimumResultsForSearch: minimumResultsForSearch,
                dropdownParent: $('body'),
                width: '100%'
            });
        }

        $(document).ready(function() {
            $('.landing-native-select').each(function() {
                initSelect2(this, {
                    minimumResultsForSearch: Infinity,
                });
            });

            initSelect2('#provinsi', {
                placeholder: 'Pilih Provinsi',
                allowClear: true,
            });
            initSelect2('#kabupaten', {
                placeholder: 'Pilih Kabupaten/Kota',
                allowClear: true,
            });
            initSelect2('#kecamatan', {
                placeholder: 'Pilih Kecamatan',
                allowClear: true,
            });
            initSelect2('#desa', {
                placeholder: 'Pilih Desa/Kelurahan',
                allowClear: true,
            });
        });

        function normalizeLocationLoadOptions(selectedCodeOrOptions = null) {
            if (selectedCodeOrOptions && typeof selectedCodeOrOptions === 'object' && !Array.isArray(selectedCodeOrOptions)) {
                return {
                    selectedCode: selectedCodeOrOptions.selectedCode || null,
                    selectedLabel: selectedCodeOrOptions.selectedLabel || '',
                    triggerChange: selectedCodeOrOptions.triggerChange === true,
                };
            }

            return {
                selectedCode: selectedCodeOrOptions || null,
                selectedLabel: '',
                triggerChange: Boolean(selectedCodeOrOptions),
            };
        }

        function populateLocationSelect(selector, placeholder, items, selectedCodeOrOptions = null) {
            const element = $(selector);
            const options = normalizeLocationLoadOptions(selectedCodeOrOptions);

            if (!element.length || element.prop('tagName') !== 'SELECT') {
                return;
            }

            element.empty().append(
                $('<option></option>').val('').text(placeholder)
            );

            let hasSelectedOption = false;

            (Array.isArray(items) ? items : []).forEach(function(item) {
                const optionCode = String(item.code || '');
                const isSelected = options.selectedCode && optionCode === String(options.selectedCode);

                if (isSelected) {
                    hasSelectedOption = true;
                }

                element.append(
                    $('<option></option>')
                        .val(optionCode)
                        .text(item.name || '')
                        .prop('selected', isSelected)
                );
            });

            if (options.selectedCode && !hasSelectedOption && options.selectedLabel) {
                element.append(
                    $('<option></option>')
                        .val(String(options.selectedCode))
                        .text(options.selectedLabel)
                        .prop('selected', true)
                );
                hasSelectedOption = true;
            }

            element.val(hasSelectedOption ? String(options.selectedCode) : '');
            element.trigger('change.select2');

            if (options.triggerChange && hasSelectedOption) {
                element.trigger('change');
            }
        }

        function loadKabupaten(provCode, selectedCodeOrOptions = null) {
            const options = normalizeLocationLoadOptions(selectedCodeOrOptions);

            if (!provCode) {
                populateLocationSelect('#kabupaten', 'Pilih Kabupaten/Kota', [], options);
                return Promise.resolve([]);
            }

            return fetch('/api/wilayah/kabupaten/' + provCode, {
                headers: {
                    'Accept': 'application/json',
                },
            })
                .then(res => res.ok ? res.json() : [])
                .then(data => {
                    populateLocationSelect('#kabupaten', 'Pilih Kabupaten/Kota', data, options);
                    return data;
                })
                .catch(() => {
                    populateLocationSelect('#kabupaten', 'Pilih Kabupaten/Kota', [], options);
                    return [];
                });
        }

        function loadKecamatan(kabCode, selectedCodeOrOptions = null) {
            const options = normalizeLocationLoadOptions(selectedCodeOrOptions);

            if (!kabCode) {
                populateLocationSelect('#kecamatan', 'Pilih Kecamatan', [], options);
                return Promise.resolve([]);
            }

            return fetch('/api/wilayah/kecamatan/' + kabCode, {
                headers: {
                    'Accept': 'application/json',
                },
            })
                .then(res => res.ok ? res.json() : [])
                .then(data => {
                    populateLocationSelect('#kecamatan', 'Pilih Kecamatan', data, options);
                    return data;
                })
                .catch(() => {
                    populateLocationSelect('#kecamatan', 'Pilih Kecamatan', [], options);
                    return [];
                });
        }

        function loadDesa(kecCode, selectedCodeOrOptions = null) {
            const options = normalizeLocationLoadOptions(selectedCodeOrOptions);

            if (!kecCode) {
                populateLocationSelect('#desa', 'Pilih Desa/Kelurahan', [], options);
                return Promise.resolve([]);
            }

            return fetch('/api/wilayah/desa/' + kecCode, {
                headers: {
                    'Accept': 'application/json',
                },
            })
                .then(res => res.ok ? res.json() : [])
                .then(data => {
                    populateLocationSelect('#desa', 'Pilih Desa/Kelurahan', data, options);
                    return data;
                })
                .catch(() => {
                    populateLocationSelect('#desa', 'Pilih Desa/Kelurahan', [], options);
                    return [];
                });
        }

        $('#provinsi').on('change', function() {
            const code = $(this).val();
            const name = $(this).find('option:selected').data('name');
            $('#provinsi_name').val(name || '');
            $('#kabupaten_name').val('');
            $('#kecamatan_name').val('');
            $('#desa_name').val('');
            $('#kabupaten').empty().append('<option value="">Pilih Kabupaten/Kota</option>').trigger('change.select2');
            $('#kecamatan').empty().append('<option value="">Pilih Kecamatan</option>').trigger('change.select2');
            $('#desa').empty().append('<option value="">Pilih Desa/Kelurahan</option>').trigger('change.select2');
            if (!code) return;
            loadKabupaten(code, null);
        });

        $('#kabupaten').on('change', function() {
            const code = $(this).val();
            $('#kabupaten_name').val($(this).find('option:selected').text());
            $('#kecamatan_name').val('');
            $('#desa_name').val('');
            $('#kecamatan').empty().append('<option value="">Pilih Kecamatan</option>').trigger('change.select2');
            $('#desa').empty().append('<option value="">Pilih Desa/Kelurahan</option>').trigger('change.select2');
            if (!code) return;
            loadKecamatan(code, null);
        });

        $('#kecamatan').on('change', function() {
            const code = $(this).val();
            $('#kecamatan_name').val($(this).find('option:selected').text());
            $('#desa_name').val('');
            $('#desa').empty().append('<option value="">Pilih Desa/Kelurahan</option>').trigger('change.select2');
            if (!code) return;
            loadDesa(code, null);
        });

        $('#desa').on('change', function() {
            $('#desa_name').val($(this).find('option:selected').text());
        });
    </script>

    @php
    $isBeforeOpen = $setting && now()->lessThan($setting->open_at);
    @endphp

    <script>
        @if($submissionOpen && $setting)
        const countdownClose = document.getElementById('countdown-close');
        if (countdownClose) {
            const targetClose = new Date("{{ $setting->close_at->toIso8601String() }}").getTime();
            const timerClose = setInterval(function() {
                const diff = targetClose - new Date().getTime();
                if (diff <= 0) {
                    clearInterval(timerClose);
                    countdownClose.innerHTML =
                        '<p class="text-yellow-400 text-sm">Submission telah ditutup. Silakan refresh halaman.</p>';
                    return;
                }
                const days = document.getElementById('cc-days');
                const hours = document.getElementById('cc-hours');
                const minutes = document.getElementById('cc-minutes');
                const seconds = document.getElementById('cc-seconds');
                if (days && hours && minutes && seconds) {
                    days.innerText = String(Math.floor(diff / 86400000)).padStart(2, '0');
                    hours.innerText = String(Math.floor((diff % 86400000) / 3600000)).padStart(2, '0');
                    minutes.innerText = String(Math.floor((diff % 3600000) / 60000)).padStart(2, '0');
                    seconds.innerText = String(Math.floor((diff % 60000) / 1000)).padStart(2, '0');
                }
            }, 1000);
        }

        @elseif($isBeforeOpen && $setting)
        const countdownOpen = document.getElementById('countdown-open');
        if (countdownOpen) {
            const targetOpen = new Date("{{ $setting->open_at->toIso8601String() }}").getTime();
            const timerOpen = setInterval(function() {
                const diff = targetOpen - new Date().getTime();
                if (diff <= 0) {
                    clearInterval(timerOpen);
                    countdownOpen.innerHTML =
                        '<p class="text-green-400 text-sm">Submission sudah dibuka! Silakan refresh halaman.</p>';
                    return;
                }
                const days = document.getElementById('co-days');
                const hours = document.getElementById('co-hours');
                const minutes = document.getElementById('co-minutes');
                const seconds = document.getElementById('co-seconds');
                if (days && hours && minutes && seconds) {
                    days.innerText = String(Math.floor(diff / 86400000)).padStart(2, '0');
                    hours.innerText = String(Math.floor((diff % 86400000) / 3600000)).padStart(2, '0');
                    minutes.innerText = String(Math.floor((diff % 3600000) / 60000)).padStart(2, '0');
                    seconds.innerText = String(Math.floor((diff % 60000) / 1000)).padStart(2, '0');
                }
            }, 1000);
        }
        @endif
    </script>
    @stack('scripts')
</body>

</html>
