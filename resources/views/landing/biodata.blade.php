@extends('layouts.landing.master')

@section('main')
@php
    $isGeneralBuyer = $user->role === 'umum';
    $categoryId = $user->category_id;
    $jatimCode = '35';
    $jatimName = 'JAWA TIMUR';
    $pacitanCode = '3501';
    $pacitanName = 'KABUPATEN PACITAN';
    $selectedProvinceCode = old('provinsi_code', optional($detail)->provinsi_code ?: (!$isGeneralBuyer && $categoryId != 1 ? $jatimCode : ''));
    $selectedProvinceName = old('provinsi_name', optional($detail)->provinsi_name ?: (!$isGeneralBuyer && $categoryId != 1 ? $jatimName : ''));
    $defaultKabupatenCode = !$isGeneralBuyer && in_array($categoryId, [3, 4]) && (string) $selectedProvinceCode === $jatimCode
        ? $pacitanCode
        : '';
    $defaultKabupatenName = $defaultKabupatenCode ? $pacitanName : '';
    $selectedKabupatenCode = old('kabupaten_code', optional($detail)->kabupaten_code ?: $defaultKabupatenCode);
    $selectedKabupatenName = old('kabupaten_name', optional($detail)->kabupaten_name ?: $defaultKabupatenName);
    $hasSelectedProvinceOption = $selectedProvinceCode
        ? collect($provinsi ?? [])->contains(function ($province) use ($selectedProvinceCode) {
            return (string) $province->code === (string) $selectedProvinceCode;
        })
        : false;
    $savedDestinationLabel = collect([
        optional($detail)->desa_name,
        optional($detail)->kecamatan_name,
        optional($detail)->kabupaten_name,
        optional($detail)->provinsi_name,
    ])->filter()->implode(', ');
@endphp

<main class="relative z-10">
    <section class="max-w-4xl mx-auto px-6 md:px-10 py-16">
        <div class="flex items-center gap-2 mb-6 text-sm">
            <a href="{{ route('landing.home') }}" class="text-gray-500 hover:text-purple-400 transition">Home</a>
            <span class="text-gray-600">/</span>
            <span class="text-purple-400 font-semibold">{{ $title }}</span>
        </div>

        <div class="mb-8">
            <p class="text-purple-400 text-sm uppercase tracking-wider font-semibold mb-2">
                {{ $isGeneralBuyer ? 'SHOPPING PROFILE' : 'SUBMISSION PROFILE' }}
            </p>
            <h1 class="text-3xl md:text-5xl font-bold border-l-8 border-purple-500 pl-6 tracking-tight">
                {{ $detail ? 'Perbarui Biodata' : 'Lengkapi Biodata' }}
            </h1>
            <p class="text-gray-400 text-sm md:text-base mt-4 pl-6 max-w-2xl">
                {{ $isGeneralBuyer
                    ? 'Lengkapi biodata pengiriman agar checkout merchandise bisa diproses tanpa kembali ke halaman dashboard.'
                    : 'Lengkapi biodata peserta sebelum mengirim submission film.' }}
            </p>
        </div>

        <div class="glass-card rounded-3xl p-6 md:p-8">
            @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/30 text-green-400 text-sm">
                {{ session('success') }}
            </div>
            @endif

            @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-300 text-sm">
                <div class="font-semibold mb-2">Terdapat kesalahan pada form:</div>
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('user-detail.save') }}" method="POST">
                @csrf

                @if($isGeneralBuyer)
                <div class="section-divider">Informasi Akun</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5">
                    <div class="field-group">
                        <label class="field-label">Nama Lengkap <span class="text-red-400">*</span></label>
                        <div class="input-icon-wrap">
                            <i class="fas fa-user"></i>
                            <input type="text" name="name" class="field-input"
                                value="{{ old('name', $user->name) }}" required>
                        </div>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Email <span class="text-red-400">*</span></label>
                        <div class="input-icon-wrap">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" class="field-input"
                                value="{{ old('email', $user->email) }}" required>
                        </div>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label">Nomor WhatsApp <span class="text-red-400">*</span></label>
                    <div class="input-icon-wrap">
                        <i class="fab fa-whatsapp"></i>
                        <input type="text" name="no_hp" class="field-input"
                            value="{{ old('no_hp', $user->no_hp) }}" required>
                    </div>
                </div>
                @else
                <div class="section-divider">Informasi Peserta</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5">
                    <div class="field-group">
                        <label class="field-label">Nama Komunitas <span class="text-red-400">*</span></label>
                        <input type="text" name="community_name" class="field-input"
                            placeholder="Nama Komunitas / Rumah Produksi / Kelompok"
                            value="{{ old('community_name', $detail->community_name ?? '') }}" required>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Posisi / Jabatan <span class="text-red-400">*</span></label>
                        <input type="text" name="posisi" class="field-input"
                            placeholder="cth: Ketua, Anggota, Sutradara"
                            value="{{ old('posisi', $detail->posisi ?? '') }}" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5">
                    <div class="field-group">
                        <label class="field-label">Username Instagram <span class="text-red-400">*</span></label>
                        <div class="input-icon-wrap">
                            <i class="fab fa-instagram"></i>
                            <input type="text" name="username_ig" class="field-input"
                                placeholder="username_instagram"
                                value="{{ old('username_ig', $detail->username_ig ?? '') }}" required>
                        </div>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Tanggal Lahir <span class="text-red-400">*</span></label>
                        <input type="date" name="tanggal_lahir" class="field-input"
                            value="{{ old('tanggal_lahir', optional($detail->tanggal_lahir ?? null)->format('Y-m-d')) }}" required>
                    </div>
                </div>
                @endif

                <div class="section-divider">Alamat</div>
                @if($isGeneralBuyer)
                <div class="field-group">
                    <label class="field-label">Alamat Lengkap <span class="text-red-400">*</span></label>
                    <textarea name="alamat_lengkap" class="field-input" rows="4"
                        placeholder="Nama jalan, nomor rumah, RT/RW, patokan..." required>{{ old('alamat_lengkap', $detail->alamat_lengkap ?? '') }}</textarea>
                </div>

                <div class="field-group">
                    <label class="field-label">Tujuan Pengiriman <span class="text-red-400">*</span></label>
                    <div class="flex flex-col gap-3 md:flex-row">
                        <input
                            type="text"
                            id="destination_keyword"
                            class="field-input flex-1"
                            placeholder="Cari kecamatan, kelurahan, kabupaten, atau kode pos"
                            value="{{ old('shipping_destination_label', $savedDestinationLabel) }}">
                        <button
                            type="button"
                            id="search-destination"
                            class="h-11 px-5 rounded-xl bg-white/10 border border-white/10 text-white text-sm font-semibold hover:bg-white/15 transition">
                            Cari Tujuan
                        </button>
                    </div>

                    <input type="hidden" name="shipping_destination_id" id="shipping_destination_id" value="{{ old('shipping_destination_id') }}">
                    <input type="hidden" name="shipping_destination_label" id="shipping_destination_label" value="{{ old('shipping_destination_label', $savedDestinationLabel) }}">
                    <input type="hidden" name="provinsi_name" id="provinsi_name" value="{{ old('provinsi_name', $detail->provinsi_name ?? '') }}">
                    <input type="hidden" name="kabupaten_name" id="kabupaten_name" value="{{ old('kabupaten_name', $detail->kabupaten_name ?? '') }}">
                    <input type="hidden" name="kecamatan_name" id="kecamatan_name" value="{{ old('kecamatan_name', $detail->kecamatan_name ?? '') }}">
                    <input type="hidden" name="desa_name" id="desa_name" value="{{ old('desa_name', $detail->desa_name ?? '') }}">

                    <div id="destination-feedback" class="mt-3 hidden rounded-2xl px-4 py-3 text-sm"></div>
                    <div id="selected-destination" class="mt-3 hidden rounded-2xl border border-green-500/20 bg-green-500/5 px-4 py-4">
                        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                            <div>
                                <div class="text-xs uppercase tracking-[0.2em] text-green-300">Tujuan dipilih</div>
                                <div id="selected-destination-label" class="mt-1 text-sm text-white"></div>
                            </div>
                            <button type="button" id="clear-destination" class="text-sm text-green-200 hover:text-white transition">
                                Ganti tujuan
                            </button>
                        </div>
                    </div>
                    <div id="destination-results" class="mt-3 hidden space-y-3"></div>
                </div>
                @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5">
                    <div class="field-group">
                        <label class="field-label">Provinsi <span class="text-red-400">*</span></label>
                        <select name="provinsi_code" id="provinsi" class="field-input" required>
                            <option value="">Pilih Provinsi</option>
                            @foreach($provinsi as $p)
                            <option value="{{ $p->code }}" data-name="{{ $p->name }}"
                                {{ $selectedProvinceCode == $p->code ? 'selected' : '' }}>
                                {{ $p->name }}
                            </option>
                            @endforeach
                            @if($selectedProvinceCode && $selectedProvinceName && !$hasSelectedProvinceOption)
                            <option value="{{ $selectedProvinceCode }}" data-name="{{ $selectedProvinceName }}" selected>
                                {{ $selectedProvinceName }}
                            </option>
                            @endif
                        </select>
                        <input type="hidden" name="provinsi_name" id="provinsi_name"
                            value="{{ $selectedProvinceName }}">
                    </div>

                    <div class="field-group">
                        <label class="field-label">Kabupaten / Kota <span class="text-red-400">*</span></label>
                        <select name="kabupaten_code" id="kabupaten" class="field-input" required>
                            <option value="">Pilih Kabupaten/Kota</option>
                        </select>
                        <input type="hidden" name="kabupaten_name" id="kabupaten_name"
                            value="{{ $selectedKabupatenName }}">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5">
                    <div class="field-group">
                        <label class="field-label">Kecamatan <span class="text-red-400">*</span></label>
                        <select name="kecamatan_code" id="kecamatan" class="field-input" required>
                            <option value="">Pilih Kecamatan</option>
                        </select>
                        <input type="hidden" name="kecamatan_name" id="kecamatan_name"
                            value="{{ old('kecamatan_name', $detail->kecamatan_name ?? '') }}">
                    </div>

                    <div class="field-group">
                        <label class="field-label">Desa / Kelurahan <span class="text-red-400">*</span></label>
                        <select name="desa_code" id="desa" class="field-input" required>
                            <option value="">Pilih Desa/Kelurahan</option>
                        </select>
                        <input type="hidden" name="desa_name" id="desa_name"
                            value="{{ old('desa_name', $detail->desa_name ?? '') }}">
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label">Alamat Lengkap <span class="text-red-400">*</span></label>
                    <textarea name="alamat_lengkap" class="field-input" rows="4"
                        placeholder="Nama jalan, nomor rumah, RT/RW, patokan..." required>{{ old('alamat_lengkap', $detail->alamat_lengkap ?? '') }}</textarea>
                </div>
                @endif

                <div class="flex flex-col md:flex-row gap-3 pt-2">
                    <button type="button"
                        onclick="document.getElementById('biodata-logout-form').submit();"
                        class="px-5 py-3 rounded-xl border border-white/10 bg-white/5 text-gray-300 text-center font-semibold">
                        Logout
                    </button>
                    <button type="submit" class="btn-submit">
                        Simpan Biodata
                    </button>
                </div>
            </form>

            <form id="biodata-logout-form" action="{{ url('/logout') }}" method="POST" class="hidden">
                @csrf
            </form>
        </div>
    </section>
</main>
@endsection

@push('scripts')
<script>
    const categoryId = @json((int) ($categoryId ?? 1));
    const isGeneralBuyer = @json($isGeneralBuyer);
    const JATIM_CODE = @json($jatimCode);
    const JATIM_NAME = @json($jatimName);
    const PACITAN_CODE = @json($pacitanCode);
    const PACITAN_NAME = @json($pacitanName);
    const existingKabupaten = @json($selectedKabupatenCode);
    const existingKabupatenName = @json($selectedKabupatenName);
    const existingKecamatan = @json(old('kecamatan_code', $detail->kecamatan_code ?? ''));
    const existingKecamatanName = @json(old('kecamatan_name', $detail->kecamatan_name ?? ''));
    const existingDesaCode = @json(old('desa_code', $detail->desa_code ?? ''));
    const existingDesaName = @json(old('desa_name', $detail->desa_name ?? ''));
    const destinationKeywordInput = document.getElementById('destination_keyword');
    const searchDestinationButton = document.getElementById('search-destination');
    const destinationResults = document.getElementById('destination-results');
    const destinationFeedback = document.getElementById('destination-feedback');
    const selectedDestinationBox = document.getElementById('selected-destination');
    const selectedDestinationLabel = document.getElementById('selected-destination-label');
    const clearDestinationButton = document.getElementById('clear-destination');
    const shippingDestinationIdInput = document.getElementById('shipping_destination_id');
    const shippingDestinationLabelInput = document.getElementById('shipping_destination_label');
    const provinceNameInput = document.getElementById('provinsi_name');
    const cityNameInput = document.getElementById('kabupaten_name');
    const districtNameInput = document.getElementById('kecamatan_name');
    const villageNameInput = document.getElementById('desa_name');
    const destinationStorageKey = "shipping_destination_{{ $user->id }}";

    function syncLocationNameInput(selectId, inputId) {
        const select = document.getElementById(selectId);
        const input = document.getElementById(inputId);

        if (!select || !input) {
            return;
        }

        const selectedOption = select.options[select.selectedIndex];
        input.value = selectedOption ? selectedOption.textContent || '' : '';
    }

    async function restoreParticipantLocation() {
        if (isGeneralBuyer) {
            return;
        }

        const provinceSelect = document.getElementById('provinsi');
        if (!provinceSelect) {
            return;
        }

        const selectedProvinceCode = provinceSelect.value || (categoryId !== 1 ? JATIM_CODE : '');
        if (selectedProvinceCode && !provinceSelect.value) {
            provinceSelect.value = selectedProvinceCode;
        }

        if (!selectedProvinceCode) {
            return;
        }

        if (provinceNameInput && !provinceNameInput.value && categoryId !== 1) {
            provinceNameInput.value = JATIM_NAME;
        }
        syncLocationNameInput('provinsi', 'provinsi_name');

        const selectedKabupatenCode = existingKabupaten || (([3, 4].includes(categoryId) && selectedProvinceCode === JATIM_CODE) ? PACITAN_CODE : '');
        const selectedKabupatenName = existingKabupatenName || (([3, 4].includes(categoryId) && selectedProvinceCode === JATIM_CODE) ? PACITAN_NAME : '');

        await loadKabupaten(selectedProvinceCode, {
            selectedCode: selectedKabupatenCode,
            selectedLabel: selectedKabupatenName,
            triggerChange: false,
        });
        syncLocationNameInput('kabupaten', 'kabupaten_name');

        if (!selectedKabupatenCode) {
            return;
        }

        await loadKecamatan(selectedKabupatenCode, {
            selectedCode: existingKecamatan,
            selectedLabel: existingKecamatanName,
            triggerChange: false,
        });
        syncLocationNameInput('kecamatan', 'kecamatan_name');

        if (!existingKecamatan) {
            return;
        }

        await loadDesa(existingKecamatan, {
            selectedCode: existingDesaCode,
            selectedLabel: existingDesaName,
            triggerChange: false,
        });
        syncLocationNameInput('desa', 'desa_name');
    }

    function hideDestinationFeedback() {
        if (destinationFeedback) {
            destinationFeedback.classList.add('hidden');
        }
    }

    function showDestinationFeedback(message, type = 'info') {
        if (!destinationFeedback) {
            return;
        }

        const classes = {
            info: 'border border-white/10 bg-white/5 text-gray-200',
            error: 'border border-red-500/30 bg-red-500/10 text-red-200',
            success: 'border border-green-500/30 bg-green-500/10 text-green-200',
            warning: 'border border-yellow-500/30 bg-yellow-500/10 text-yellow-200',
        };

        destinationFeedback.className = 'mt-3 rounded-2xl px-4 py-3 text-sm ' + (classes[type] || classes.info);
        destinationFeedback.textContent = message;
        destinationFeedback.classList.remove('hidden');
    }

    function hideDestinationResults() {
        if (!destinationResults) {
            return;
        }

        destinationResults.innerHTML = '';
        destinationResults.classList.add('hidden');
    }

    function persistSelectedDestination(destination) {
        if (!isGeneralBuyer || !window.localStorage) {
            return;
        }

        try {
            window.localStorage.setItem(destinationStorageKey, JSON.stringify(destination));
        } catch (error) {
        }
    }

    function removePersistedDestination() {
        if (!isGeneralBuyer || !window.localStorage) {
            return;
        }

        try {
            window.localStorage.removeItem(destinationStorageKey);
        } catch (error) {
        }
    }

    function buildDestinationPayload(destination) {
        return {
            id: String(destination.id || ''),
            label: String(destination.label || ''),
            province: String(destination.province || ''),
            city: String(destination.city || ''),
            district: String(destination.district || ''),
            subdistrict: String(destination.subdistrict || destination.district || ''),
            village: String(destination.village || ''),
        };
    }

    function applySelectedDestination(destination, options = {}) {
        if (!isGeneralBuyer || !destinationKeywordInput) {
            return;
        }

        const payload = buildDestinationPayload(destination);

        destinationKeywordInput.value = payload.label;
        shippingDestinationIdInput.value = payload.id;
        shippingDestinationLabelInput.value = payload.label;
        provinceNameInput.value = payload.province;
        cityNameInput.value = payload.city;
        districtNameInput.value = payload.subdistrict;
        villageNameInput.value = payload.village;

        if (selectedDestinationBox && selectedDestinationLabel) {
            selectedDestinationLabel.textContent = payload.label;
            selectedDestinationBox.classList.remove('hidden');
        }

        hideDestinationResults();
        hideDestinationFeedback();

        if (options.persist !== false) {
            persistSelectedDestination(payload);
        }
    }

    function clearSelectedDestination(preserveKeyword = false) {
        if (!isGeneralBuyer) {
            return;
        }

        shippingDestinationIdInput.value = '';
        shippingDestinationLabelInput.value = '';
        provinceNameInput.value = '';
        cityNameInput.value = '';
        districtNameInput.value = '';
        villageNameInput.value = '';

        if (!preserveKeyword && destinationKeywordInput) {
            destinationKeywordInput.value = '';
        }

        if (selectedDestinationBox) {
            selectedDestinationBox.classList.add('hidden');
        }

        hideDestinationResults();
        removePersistedDestination();
    }

    function renderDestinationResults(results) {
        if (!destinationResults) {
            return;
        }

        destinationResults.innerHTML = '';

        results.forEach(function(item) {
            const button = document.createElement('button');
            const title = document.createElement('div');
            const meta = document.createElement('div');

            button.type = 'button';
            button.className = 'w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-4 text-left transition hover:border-purple-400/40 hover:bg-white/10';
            button.dataset.id = item.id || '';
            button.dataset.label = item.label || '';
            button.dataset.province = item.province || '';
            button.dataset.city = item.city || '';
            button.dataset.district = item.district || '';
            button.dataset.subdistrict = item.subdistrict || '';
            button.dataset.village = item.village || '';

            title.className = 'text-white font-semibold';
            title.textContent = item.label || '-';
            meta.className = 'mt-1 text-xs text-gray-400';
            meta.textContent = [item.village || '-', item.subdistrict || item.district || '-', item.city || '-'].join(' · ');

            button.appendChild(title);
            button.appendChild(meta);
            destinationResults.appendChild(button);
        });

        destinationResults.classList.remove('hidden');
    }

    async function searchDestinations() {
        if (!isGeneralBuyer || !destinationKeywordInput || !searchDestinationButton) {
            return;
        }

        const keyword = destinationKeywordInput.value.trim();

        if (!keyword) {
            showDestinationFeedback('Masukkan kata kunci tujuan terlebih dahulu.', 'warning');
            hideDestinationResults();
            return;
        }

        searchDestinationButton.setAttribute('disabled', 'disabled');
        searchDestinationButton.textContent = 'Mencari...';
        hideDestinationFeedback();
        hideDestinationResults();

        try {
            const response = await fetch("{{ route('checkout.destination-search') }}?keyword=" + encodeURIComponent(keyword), {
                headers: {
                    'Accept': 'application/json',
                },
            });
            const payload = await response.json();

            if (!response.ok) {
                throw new Error(payload.message || 'Gagal mencari tujuan pengiriman.');
            }

            const results = payload.data || [];

            if (!results.length) {
                showDestinationFeedback('Tujuan pengiriman tidak ditemukan. Coba kata kunci lain.', 'warning');
                return;
            }

            renderDestinationResults(results);
        } catch (error) {
            showDestinationFeedback(error.message || 'Gagal mencari tujuan pengiriman.', 'error');
        } finally {
            searchDestinationButton.removeAttribute('disabled');
            searchDestinationButton.textContent = 'Cari Tujuan';
        }
    }

    function restoreSelectedDestination() {
        if (!isGeneralBuyer || !destinationKeywordInput) {
            return;
        }

        if (shippingDestinationIdInput && shippingDestinationIdInput.value) {
            applySelectedDestination({
                id: shippingDestinationIdInput.value,
                label: shippingDestinationLabelInput.value,
                province: provinceNameInput.value,
                city: cityNameInput.value,
                subdistrict: districtNameInput.value,
                village: villageNameInput.value,
            }, {
                persist: true,
            });

            return;
        }

        if (window.localStorage) {
            try {
                const raw = window.localStorage.getItem(destinationStorageKey);

                if (raw) {
                    const destination = JSON.parse(raw);

                    if (destination && destination.id) {
                        applySelectedDestination(destination, {
                            persist: false,
                        });

                        return;
                    }
                }
            } catch (error) {
            }
        }

        if (shippingDestinationLabelInput.value) {
            destinationKeywordInput.value = shippingDestinationLabelInput.value;

            if (selectedDestinationBox && selectedDestinationLabel) {
                selectedDestinationLabel.textContent = shippingDestinationLabelInput.value;
                selectedDestinationBox.classList.remove('hidden');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (isGeneralBuyer) {
            restoreSelectedDestination();

            if (destinationKeywordInput) {
                destinationKeywordInput.addEventListener('input', function() {
                    if (shippingDestinationIdInput.value && this.value.trim() !== shippingDestinationLabelInput.value) {
                        clearSelectedDestination(true);
                    }
                });

                destinationKeywordInput.addEventListener('keydown', function(event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        searchDestinations();
                    }
                });
            }

            if (searchDestinationButton) {
                searchDestinationButton.addEventListener('click', function() {
                    searchDestinations();
                });
            }

            if (clearDestinationButton) {
                clearDestinationButton.addEventListener('click', function() {
                    clearSelectedDestination();
                    hideDestinationFeedback();
                });
            }

            if (destinationResults) {
                destinationResults.addEventListener('click', function(event) {
                    const button = event.target.closest('button[data-id]');

                    if (!button) {
                        return;
                    }

                    applySelectedDestination({
                        id: button.dataset.id,
                        label: button.dataset.label,
                        province: button.dataset.province,
                        city: button.dataset.city,
                        district: button.dataset.district,
                        subdistrict: button.dataset.subdistrict,
                        village: button.dataset.village,
                    });
                });
            }

            return;
        }

        restoreParticipantLocation().catch(function() {
            // Keep the form usable even if some location datasets are incomplete.
        });
    });
</script>
@endpush
