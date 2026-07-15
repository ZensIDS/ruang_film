@extends('layouts.landing.master')
@section('main')
@php
    $detail = $user->detail;
    $subtotal = $cart->subtotal();
    $savedDestinationLabel = collect([
        optional($detail)->desa_name,
        optional($detail)->kecamatan_name,
        optional($detail)->kabupaten_name,
        optional($detail)->provinsi_name,
    ])->filter()->implode(', ');

    // Teks tujuan yang dipakai untuk deteksi "pacitan":
    // - general buyer: label tujuan hasil pencarian destinasi
    // - non general buyer: alamat tersimpan di biodata
    $destinationTextForPacitanCheck = $isGeneralBuyer
        ? $savedDestinationLabel
        : (optional($detail)->formatted_address ?? '');

    $isPacitanDestination = str_contains(strtolower($destinationTextForPacitanCheck), 'pacitan');
@endphp
<main class="relative z-10">
    <section class="max-w-6xl mx-auto px-6 md:px-10 py-16">
        <div class="mb-8">
            <p class="text-purple-400 text-sm uppercase tracking-wider font-semibold mb-2">CHECKOUT</p>
            <h1 class="text-3xl md:text-5xl font-bold border-l-8 border-purple-500 pl-6 tracking-tight">Checkout Merchandise</h1>
        </div>

        <form action="{{ route('checkout.store') }}" method="POST" id="checkout-form" class="grid grid-cols-1 lg:grid-cols-[1.4fr_1fr] gap-8">
            @csrf
            <input type="hidden" name="selected_shipping_option" id="selected_shipping_option" value="{{ old('selected_shipping_option') }}">

            <div class="space-y-8">
                <div class="glass-card rounded-3xl p-6 md:p-8">
                    <h2 class="text-xl font-semibold text-white">{{ $isGeneralBuyer ? 'Biodata Pengiriman' : 'Alamat Pengiriman' }}</h2>

                    @if($isGeneralBuyer)
                    <div class="mt-5 space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="field-group !mb-0">
                                <label class="field-label">Nama Lengkap</label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-user"></i>
                                    <input type="text" name="name" class="field-input" value="{{ old('name', $user->name) }}" required>
                                </div>
                            </div>
                            <div class="field-group !mb-0">
                                <label class="field-label">Email</label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-envelope"></i>
                                    <input type="email" name="email" class="field-input" value="{{ old('email', $user->email) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="field-group !mb-0">
                            <label class="field-label">Nomor WhatsApp</label>
                            <div class="input-icon-wrap">
                                <i class="fab fa-whatsapp"></i>
                                <input type="text" name="no_hp" class="field-input" value="{{ old('no_hp', $user->no_hp) }}" required>
                            </div>
                        </div>

                        <div class="field-group !mb-0">
                            <label class="field-label">Alamat Lengkap</label>
                            <textarea name="alamat_lengkap" class="field-input" rows="3" required>{{ old('alamat_lengkap', $detail->alamat_lengkap ?? '') }}</textarea>
                        </div>

                        <div class="field-group !mb-0">
                            <label class="field-label">Tujuan Pengiriman</label>
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

                            @error('shipping_destination_id')
                            <div class="mt-3 rounded-2xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                                {{ $message }}
                            </div>
                            @enderror

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

                        <div class="pt-2">
                            <a href="{{ route('user-detail.index') }}" class="text-purple-300 text-sm hover:text-purple-200">Kelola biodata pembeli di halaman khusus</a>
                        </div>
                    </div>
                    @else
                    <div class="mt-5 space-y-3 text-sm text-gray-300">
                        <div><b>Nama:</b> {{ $user->name }}</div>
                        <div><b>Email:</b> {{ $user->email }}</div>
                        <div><b>WhatsApp:</b> {{ $user->no_hp }}</div>
                        <div><b>Alamat:</b> {{ $user->detail->formatted_address }}</div>
                    </div>
                    <input type="hidden" id="fixed-destination-text" value="{{ $user->detail->formatted_address }}">
                    <div class="mt-5">
                        <a href="{{ route('user-detail.index') }}" class="text-purple-300 text-sm hover:text-purple-200">Perbarui biodata pengiriman</a>
                    </div>
                    @endif
                </div>

                <div class="glass-card rounded-3xl p-6 md:p-8">
                    <h2 class="text-xl font-semibold text-white">Pengiriman</h2>
                    <div class="mt-5 space-y-4">
                        <div>
                            <label class="text-sm text-gray-300">Kode Pos</label>
                            <input type="text" name="postal_code" id="postal_code" class="mt-2 w-full rounded-2xl bg-white/5 border border-purple-500/20 px-4 py-3 text-white"
                                value="{{ old('postal_code') }}" placeholder="Opsional">
                        </div>
                        <div>
                            <label class="text-sm text-gray-300">Catatan Pesanan</label>
                            <textarea name="notes" rows="3" class="mt-2 w-full rounded-2xl bg-white/5 border border-purple-500/20 px-4 py-3 text-white"
                                placeholder="Opsional">{{ old('notes') }}</textarea>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                <div>
                                    <div class="text-white font-semibold">Metode Pengiriman</div>
                                    <p class="text-sm text-gray-400">Pilih Metode Pengiriman</p>
                                </div>
                                <div class="flex flex-col gap-3 md:items-end">
                                    <button type="button" id="load-shipping-options"
                                        class="h-11 px-5 rounded-xl bg-gradient-to-r from-purple-700 to-purple-500 text-white text-sm font-semibold flex items-center justify-center gap-2 hover:opacity-90 transition {{ $isPacitanDestination ? 'hidden' : '' }}">
                                        Cek Ongkir
                                    </button>
                                    <button type="button" id="select-self-pickup"
                                        class="h-11 px-5 rounded-xl border border-white/15 bg-white/5 text-white text-sm font-semibold flex items-center justify-center gap-2 hover:bg-white/10 transition">
                                        Ambil di Basecamp
                                    </button>
                                    <button type="button" id="select-sent-pacitan"
                                        class="h-11 px-5 rounded-xl border border-white/15 bg-white/5 text-white text-sm font-semibold flex items-center justify-center gap-2 hover:bg-white/10 transition {{ $isPacitanDestination ? '' : 'hidden' }}">
                                        Pengiriman Area Pacitan
                                    </button>
                                </div>
                            </div>

                            @error('selected_shipping_option')
                            <div class="mt-4 rounded-2xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                                {{ $message }}
                            </div>
                            @enderror

                            <div id="shipping-feedback" class="mt-4 hidden rounded-2xl px-4 py-3 text-sm"></div>
                            <div id="shipping-options" class="mt-4 space-y-4"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="glass-card rounded-3xl p-6 md:p-8 h-fit">
                <h2 class="text-xl font-semibold text-white">Ringkasan Pesanan</h2>
                <div class="mt-6 space-y-4">
                    @foreach($cart->items as $item)
                    <div class="flex items-start justify-between gap-4 text-sm">
                        <div>
                            <div class="text-white font-medium">{{ $item->merchandise->name }}</div>
                            <div class="text-gray-400">{{ $item->quantity }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}</div>
                        </div>
                        <div class="text-purple-300 font-semibold">
                            Rp {{ number_format($item->subtotal(), 0, ',', '.') }}
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="mt-6 pt-6 border-t border-white/10 space-y-3 text-sm text-gray-300">
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span id="summary-subtotal"
                            data-subtotal="{{ (int) $subtotal }}"
                            data-total-weight="{{ $cart->items->sum(fn($item) => ($item->merchandise->weight ?? 0) * $item->quantity) }}">
                            Rp {{ number_format($subtotal, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span>Ongkir</span>
                        <span id="summary-shipping">Belum dipilih</span>
                    </div>
                    <div class="flex justify-between text-white font-semibold">
                        <span>Total</span>
                        <span id="summary-total">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div id="summary-shipping-label" class="hidden rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-xs text-gray-300"></div>
                </div>
                <button type="submit" id="submit-checkout"
                    class="mt-8 w-full h-11 rounded-xl bg-gradient-to-r from-purple-700 to-purple-500 text-white text-sm font-semibold flex items-center justify-center gap-2 hover:opacity-90 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled>
                    Buat Invoice
                </button>
            </div>
        </form>
    </section>
</main>
@endsection

@push('scripts')
<script>
    const checkoutForm = document.getElementById('checkout-form');
    const shippingButton = document.getElementById('load-shipping-options');
    const selfPickupButton = document.getElementById('select-self-pickup');
    const sentPacitanButton = document.getElementById('select-sent-pacitan');
    const shippingOptionsContainer = document.getElementById('shipping-options');
    const shippingFeedback = document.getElementById('shipping-feedback');
    const selectedShippingOptionInput = document.getElementById('selected_shipping_option');
    const submitCheckoutButton = document.getElementById('submit-checkout');
    const summarySubtotal = document.getElementById('summary-subtotal');
    const summaryShipping = document.getElementById('summary-shipping');
    const summaryTotal = document.getElementById('summary-total');
    const summaryShippingLabel = document.getElementById('summary-shipping-label');
    const oldSelectedShippingOption = "{{ old('selected_shipping_option') }}";
    const checkoutSubtotal = Number(summarySubtotal.dataset.subtotal || 0);
    const isGeneralBuyer = @json($isGeneralBuyer);
    const selfPickupOption = @json($selfPickupOption);
    const selfPickupLabel = @json($selfPickupLabel);

    // Pacitan punya dua sub-opsi (express & reguler), jadi dikirim sebagai
    // dua constant terpisah dari controller — sama pola dengan self pickup.
    const sentPacitanExpressOption = @json($sentPacitanExpressOption);
    const sentPacitanRegularOption = @json($sentPacitanRegularOption);
    const sentPacitanLabel = @json($sentPacitanLabel);

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
    const postalCodeInput = document.getElementById('postal_code');
    const fixedDestinationTextInput = document.getElementById('fixed-destination-text');
    const destinationStorageKey = "shipping_destination_{{ $user->id }}";
    const totalWeightGram = Number(summarySubtotal.dataset.totalWeight || 0);
    const totalWeightKg   = totalWeightGram / 1000;

    function formatRupiah(value) {
        return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
    }

    function isSelfPickupSelected() {
        return selectedShippingOptionInput.value === selfPickupOption;
    }

    function isSentPacitanSelected() {
        return selectedShippingOptionInput.value === sentPacitanExpressOption
            || selectedShippingOptionInput.value === sentPacitanRegularOption;
    }

    function currentDestinationText() {
        if (isGeneralBuyer) {
            return shippingDestinationLabelInput.value || destinationKeywordInput?.value || '';
        }

        return fixedDestinationTextInput ? fixedDestinationTextInput.value : '';
    }

    // Tampilkan tombol "Pengiriman Area Pacitan" & sembunyikan "Cek Ongkir"
    // hanya ketika teks tujuan mengandung kata "pacitan", dan sebaliknya.
    function syncShippingMethodAvailability() {
        const pacitanDetected = currentDestinationText().toLowerCase().includes('pacitan');

        if (shippingButton) {
            shippingButton.classList.toggle('hidden', pacitanDetected);
        }
        if (sentPacitanButton) {
            sentPacitanButton.classList.toggle('hidden', !pacitanDetected);
        }

        // Kalau tujuan berubah kategori, opsi ongkir yang sudah dipilih
        // sebelumnya (dari kategori lain) jadi tidak valid lagi.
        if (pacitanDetected && !isSentPacitanSelected() && !isSelfPickupSelected()) {
            clearShippingOptions('Periksa ulang ongkir');
        } else if (!pacitanDetected && isSentPacitanSelected()) {
            clearShippingOptions('Periksa ulang ongkir');
        }

        return pacitanDetected;
    }

    function resetShippingSummary(message = 'Belum dipilih') {
        selectedShippingOptionInput.value = '';
        summaryShipping.textContent = message;
        summaryTotal.textContent = formatRupiah(checkoutSubtotal);
        summaryShippingLabel.classList.add('hidden');
        summaryShippingLabel.textContent = '';
        submitCheckoutButton.setAttribute('disabled', 'disabled');
    }

    function showShippingFeedback(message, type = 'info') {
        const classes = {
            info: 'border border-white/10 bg-white/5 text-gray-200',
            error: 'border border-red-500/30 bg-red-500/10 text-red-200',
            success: 'border border-green-500/30 bg-green-500/10 text-green-200',
        };

        shippingFeedback.className = 'mt-4 rounded-2xl px-4 py-3 text-sm ' + (classes[type] || classes.info);
        shippingFeedback.textContent = message;
        shippingFeedback.classList.remove('hidden');
    }

    function clearShippingOptions(message = 'Belum dipilih') {
        shippingOptionsContainer.innerHTML = '';
        shippingFeedback.classList.add('hidden');
        resetShippingSummary(message);
    }

    function applySelectedShipping(option) {
        selectedShippingOptionInput.value = option.quoteId;
        summaryShipping.textContent = Number(option.price || 0) === 0 ? 'Gratis' : formatRupiah(option.price);
        summaryTotal.textContent = formatRupiah(checkoutSubtotal + Number(option.price));
        summaryShippingLabel.textContent = option.courierName + ' - ' + option.serviceName + (option.etd ? ' · Estimasi ' + option.etd + ' hari' : '');
        summaryShippingLabel.classList.remove('hidden');
        submitCheckoutButton.removeAttribute('disabled');
    }

    function renderSelfPickupOption() {
        shippingOptionsContainer.innerHTML = `
            <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-4">
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <div class="text-white font-semibold">${selfPickupLabel}</div>
                        <div class="text-xs text-emerald-100/80">Pesanan diambil sendiri, tanpa ongkir dan tanpa kurir.</div>
                    </div>
                    <div class="text-left md:text-right">
                        <div class="text-emerald-200 font-semibold">Gratis</div>
                        <div class="text-xs text-emerald-100/80">Self pickup</div>
                    </div>
                </div>
            </div>
        `;
    }

    function selectSelfPickup() {
        renderSelfPickupOption();
        applySelectedShipping({
            quoteId: selfPickupOption,
            courierName: selfPickupLabel,
            serviceName: 'Self Pickup',
            price: 0,
            etd: '',
        });
        showShippingFeedback('Metode ambil di basecamp dipilih. Ongkir tidak dikenakan.', 'success');
    }

    function renderSentPacitanOptions() {
        const expressPrice  = Math.ceil(totalWeightKg) * 10000;
        const regularPrice  = Math.ceil(totalWeightKg) * 7000;

        shippingOptionsContainer.innerHTML = `
            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                <div class="text-white font-semibold mb-4">${sentPacitanLabel}</div>
                <div class="space-y-3">
                    <label class="block cursor-pointer rounded-2xl border border-white/10 bg-black/10 px-4 py-4 transition hover:border-purple-400/40">
                        <div class="flex items-start gap-3">
                            <input type="radio" name="shipping_option_picker"
                                value="${sentPacitanExpressOption}"
                                data-quote-id="${sentPacitanExpressOption}"
                                data-courier-name="${sentPacitanLabel}"
                                data-service-name="Express"
                                data-price="${expressPrice}"
                                data-etd="1"
                                class="mt-1">
                            <div class="flex-1">
                                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                                    <div>
                                        <div class="text-white font-semibold">Express</div>
                                        <div class="text-xs text-gray-400">Rp 10.000/kg · ${totalWeightKg.toFixed(2)} kg</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-purple-300 font-semibold">${formatRupiah(expressPrice)}</div>
                                        <div class="text-xs text-gray-400">Estimasi H+1</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </label>

                    <label class="block cursor-pointer rounded-2xl border border-white/10 bg-black/10 px-4 py-4 transition hover:border-purple-400/40">
                        <div class="flex items-start gap-3">
                            <input type="radio" name="shipping_option_picker"
                                value="${sentPacitanRegularOption}"
                                data-quote-id="${sentPacitanRegularOption}"
                                data-courier-name="${sentPacitanLabel}"
                                data-service-name="Reguler"
                                data-price="${regularPrice}"
                                data-etd="2"
                                class="mt-1">
                            <div class="flex-1">
                                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                                    <div>
                                        <div class="text-white font-semibold">Reguler</div>
                                        <div class="text-xs text-gray-400">Rp 7.000/kg · ${totalWeightKg.toFixed(2)} kg</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-purple-300 font-semibold">${formatRupiah(regularPrice)}</div>
                                        <div class="text-xs text-gray-400">Estimasi H+2</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>
        `;

        // Attach event listener ke radio
        const radios = shippingOptionsContainer.querySelectorAll('input[name="shipping_option_picker"]');
        radios.forEach(function(radio) {
            radio.addEventListener('change', function() {
                applySelectedShipping({
                    quoteId:     this.dataset.quoteId,
                    courierName: this.dataset.courierName,
                    serviceName: this.dataset.serviceName,
                    price:       Number(this.dataset.price || 0),
                    etd:         this.dataset.etd || '',
                });
            });
        });

        // Auto-pilih express sebagai default
        radios[0].checked = true;
        radios[0].dispatchEvent(new Event('change'));
    }

    function selectSentPacitan() {
        renderSentPacitanOptions();
        showShippingFeedback('Metode pengiriman area Pacitan dipilih.', 'success');
    }

    function renderShippingOptions(groups) {
        shippingOptionsContainer.innerHTML = '';
        resetShippingSummary();

        groups.forEach(function(group, groupIndex) {
            const wrapper = document.createElement('div');
            wrapper.className = 'rounded-2xl border border-white/10 bg-white/5 p-4';

            const optionsHtml = group.options.map(function(option, optionIndex) {
                const optionId = 'shipping-option-' + groupIndex + '-' + optionIndex;

                return `
                    <label for="${optionId}" class="block cursor-pointer rounded-2xl border border-white/10 bg-black/10 px-4 py-4 transition hover:border-purple-400/40">
                        <div class="flex items-start gap-3">
                            <input
                                type="radio"
                                id="${optionId}"
                                name="shipping_option_picker"
                                value="${option.quote_id}"
                                data-quote-id="${option.quote_id}"
                                data-courier-name="${option.courier_name}"
                                data-service-name="${option.service_name}"
                                data-price="${option.price}"
                                data-etd="${option.etd || ''}"
                                class="mt-1"
                            >
                            <div class="flex-1">
                                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                                    <div>
                                        <div class="text-white font-semibold">${option.service_name}</div>
                                        <div class="text-xs text-gray-400">${option.description || option.service_name}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-purple-300 font-semibold">${formatRupiah(option.price)}</div>
                                        <div class="text-xs text-gray-400">${option.etd ? 'Estimasi ' + option.etd + ' hari' : 'Estimasi mengikuti kurir'}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </label>
                `;
            }).join('');

            wrapper.innerHTML = `
                <div class="flex items-center justify-between gap-4">
                    <div class="text-white font-semibold">${group.courier_name}</div>
                    <div class="text-xs uppercase tracking-[0.2em] text-gray-400">${group.options.length} layanan</div>
                </div>
                <div class="mt-4 space-y-3">${optionsHtml}</div>
            `;

            shippingOptionsContainer.appendChild(wrapper);
        });

        const radios = shippingOptionsContainer.querySelectorAll('input[name="shipping_option_picker"]');
        radios.forEach(function(radio) {
            radio.addEventListener('change', function() {
                applySelectedShipping({
                    quoteId: this.dataset.quoteId,
                    courierName: this.dataset.courierName,
                    serviceName: this.dataset.serviceName,
                    price: Number(this.dataset.price || 0),
                    etd: this.dataset.etd || '',
                });
            });
        });

        const targetQuoteId = selectedShippingOptionInput.value || oldSelectedShippingOption;
        const checkedRadio = Array.from(radios).find(function(radio) {
            return radio.value === targetQuoteId;
        }) || radios[0];

        if (checkedRadio) {
            checkedRadio.checked = true;
            checkedRadio.dispatchEvent(new Event('change'));
        }
    }

    function buildShippingPayload() {
        return new URLSearchParams(new FormData(checkoutForm));
    }

    async function loadShippingOptions() {
        shippingButton.setAttribute('disabled', 'disabled');
        shippingButton.textContent = 'Memuat...';
        showShippingFeedback('Mengambil pilihan ongkir terbaru...', 'info');
        resetShippingSummary();

        try {
            const response = await fetch("{{ route('checkout.shipping-options') }}", {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                },
                body: buildShippingPayload(),
            });

            const payload = await response.json();

            if (!response.ok) {
                const message = payload.message || 'Gagal memuat opsi pengiriman.';
                throw new Error(message);
            }

            const groups = payload.data?.groups || [];

            if (!groups.length) {
                throw new Error('Tidak ada layanan pengiriman yang tersedia untuk alamat ini.');
            }

            renderShippingOptions(groups);
            showShippingFeedback('Opsi pengiriman berhasil diperbarui.', 'success');
        } catch (error) {
            clearShippingOptions();
            showShippingFeedback(error.message || 'Gagal memuat opsi pengiriman.', 'error');
        } finally {
            shippingButton.removeAttribute('disabled');
            shippingButton.textContent = 'Cek Ongkir';
        }
    }

    function attachShippingReset(selector) {
        document.querySelectorAll(selector).forEach(function(element) {
            element.addEventListener('change', function() {
                if (isSelfPickupSelected() || isSentPacitanSelected()) {
                    return;
                }

                clearShippingOptions('Periksa ulang ongkir');
            });
            element.addEventListener('input', function() {
                if (isSelfPickupSelected() || isSentPacitanSelected()) {
                    return;
                }

                clearShippingOptions('Periksa ulang ongkir');
            });
        });
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
            zip_code: String(destination.zip_code || ''),
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

        if (payload.zip_code && (!postalCodeInput.value.trim() || options.forcePostalCode)) {
            postalCodeInput.value = payload.zip_code;
        }

        if (selectedDestinationLabel && selectedDestinationBox) {
            selectedDestinationLabel.textContent = payload.label;
            selectedDestinationBox.classList.remove('hidden');
        }

        hideDestinationResults();
        hideDestinationFeedback();

        if (options.persist !== false) {
            persistSelectedDestination(payload);
        }

        syncShippingMethodAvailability();
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
        syncShippingMethodAvailability();
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
            button.dataset.zipCode = item.zip_code || '';

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
        if (!isGeneralBuyer) {
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
                zip_code: postalCodeInput.value,
            }, {
                persist: true,
            });

            return;
        }

        if (!window.localStorage) {
            return;
        }

        try {
            const raw = window.localStorage.getItem(destinationStorageKey);

            if (!raw) {
                return;
            }

            const destination = JSON.parse(raw);

            if (destination && destination.id) {
                applySelectedDestination(destination, {
                    persist: false,
                    forcePostalCode: !postalCodeInput.value.trim(),
                });
            }
        } catch (error) {
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        syncShippingMethodAvailability();

        attachShippingReset('#postal_code, textarea[name="alamat_lengkap"]');

        if (isGeneralBuyer) {
            attachShippingReset('input[name="name"], input[name="email"], input[name="no_hp"]');
            restoreSelectedDestination();

            if (destinationKeywordInput) {
                destinationKeywordInput.addEventListener('input', function() {
                    if (shippingDestinationIdInput.value && this.value.trim() !== shippingDestinationLabelInput.value) {
                        clearSelectedDestination(true);
                    }

                    syncShippingMethodAvailability();
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
                        zip_code: button.dataset.zipCode,
                    }, {
                        forcePostalCode: !postalCodeInput.value.trim(),
                    });
                });
            }

            if (oldSelectedShippingOption === selfPickupOption) {
                selectSelfPickup();
            } else if (oldSelectedShippingOption === sentPacitanExpressOption || oldSelectedShippingOption === sentPacitanRegularOption) {
                selectSentPacitan();
            } else if (oldSelectedShippingOption && shippingDestinationIdInput.value) {
                setTimeout(loadShippingOptions, 250);
            }
        }

        if (!isGeneralBuyer) {
            if (oldSelectedShippingOption === selfPickupOption) {
                selectSelfPickup();
            } else if (oldSelectedShippingOption === sentPacitanExpressOption || oldSelectedShippingOption === sentPacitanRegularOption) {
                selectSentPacitan();
            } else {
                setTimeout(loadShippingOptions, 250);
            }
        }

        if (sentPacitanButton) {
            sentPacitanButton.addEventListener('click', function() {
                selectSentPacitan();
            });
        }

        // Auto-select radio yang sesuai kalau ini hasil re-render setelah validation error
        if (oldSelectedShippingOption === sentPacitanExpressOption || oldSelectedShippingOption === sentPacitanRegularOption) {
            const targetRadio = shippingOptionsContainer.querySelector(`input[value="${oldSelectedShippingOption}"]`);
            if (targetRadio) {
                targetRadio.checked = true;
                targetRadio.dispatchEvent(new Event('change'));
            }
        }
    });

    shippingButton.addEventListener('click', function() {
        loadShippingOptions();
    });

    selfPickupButton.addEventListener('click', function() {
        selectSelfPickup();
    });
</script>
@endpush
