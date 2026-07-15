@extends('layouts.master')
@section('container')
@php
    $effectiveSourceLabels = [
        'laravolt_auto' => 'Laravolt Auto-Match',
        'rajaongkir_backup' => 'RajaOngkir Backup Override',
        'env_fallback' => '.env Fallback',
    ];
@endphp
<section class="content-header">
    <h1>Data Expedisi</h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <a href="{{ route('expeditions.create') }}" class="btn btn-success">Tambah</a>
                </div>
                <div class="box-body table-responsive">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Kode RajaOngkir</th>
                                <th>Layanan</th>
                                <th>Biaya</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expeditions as $expedition)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $expedition->name }}</td>
                                <td><code>{{ $expedition->external_code ?? '-' }}</code></td>
                                <td>{{ $expedition->service_name ?? '-' }}</td>
                                <td>@currency($expedition->fee)</td>
                                <td>{{ $expedition->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                                <td>
                                    <a href="{{ route('expeditions.edit', $expedition) }}" class="btn btn-warning btn-xs">Edit</a>
                                    <form action="{{ route('expeditions.destroy', $expedition) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Hapus expedisi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><b>Origin Aktif</b></h3>
                </div>
                <div class="box-body">
                    <p><b>Sumber Aktif:</b> {{ $effectiveSourceLabels[$originSettings['effective']['source']] ?? $originSettings['effective']['source'] }}</p>
                    <p><b>ID RajaOngkir Aktif:</b> <code>{{ $originSettings['effective']['destination_id'] ?: '-' }}</code></p>

                    <hr>
                    <h4><b>Laravolt Primary</b></h4>
                    <p><b>Kecamatan:</b> {{ $originSettings['laravolt']['district_name'] ?: '-' }}</p>
                    <p><b>Kode Kecamatan:</b> <code>{{ $originSettings['laravolt']['district_code'] ?: '-' }}</code></p>
                    <p><b>Kab/Kota:</b> {{ $originSettings['laravolt']['city_name'] ?: '-' }}</p>
                    <p><b>Kode Kab/Kota:</b> <code>{{ $originSettings['laravolt']['city_code'] ?: '-' }}</code></p>
                    <p><b>Provinsi:</b> {{ $originSettings['laravolt']['province_name'] ?: '-' }}</p>
                    <p><b>Kode Provinsi:</b> <code>{{ $originSettings['laravolt']['province_code'] ?: '-' }}</code></p>
                    <p><b>Auto ID RajaOngkir:</b> <code>{{ $originSettings['laravolt']['auto_destination_id'] ?: '-' }}</code></p>
                    <p><b>Auto Label RajaOngkir:</b> {{ $originSettings['laravolt']['auto_destination_label'] ?: '-' }}</p>

                    <hr>
                    <h4><b>RajaOngkir Backup</b></h4>
                    <p><b>ID Manual:</b> <code>{{ $originSettings['rajaongkir_backup']['destination_id'] ?: '-' }}</code></p>
                    <p><b>Label Manual:</b> {{ $originSettings['rajaongkir_backup']['label'] ?: '-' }}</p>

                    <hr>
                    <h4><b>.env Fallback</b></h4>
                    <p><b>RAJAONGKIR_ORIGIN_DISTRICT_ID:</b> <code>{{ $originSettings['env']['legacy_district_id'] ?: '-' }}</code></p>
                    <p><b>Fallback Destination ID:</b> <code>{{ $originSettings['env']['fallback_destination_id'] ?: '-' }}</code></p>
                    <p class="text-muted" style="margin-bottom:0;">Jika app setting kosong, checkout akan pakai fallback dari <code>.env</code>.</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><b>Set Origin Dari Laravolt</b></h3>
                </div>
                <div class="box-body">
                    <p class="text-muted">Primary origin. Cari kecamatan/subdistrict, lalu simpan. Sistem akan coba auto-match ke RajaOngkir dengan aman.</p>

                    <div class="form-group">
                        <label>Cari Kecamatan / Kab / Provinsi</label>
                        <div class="input-group">
                            <input type="text" id="laravolt-origin-keyword" class="form-control" placeholder="Contoh: Pacitan, Bandung Kidul">
                            <span class="input-group-btn">
                                <button type="button" id="search-laravolt-origin" class="btn btn-success">Cari</button>
                            </span>
                        </div>
                    </div>

                    <div id="laravolt-origin-feedback" class="alert alert-info" style="display:none;"></div>
                    <div id="laravolt-origin-results" class="table-responsive" style="display:none;">
                        <table class="table table-bordered table-condensed">
                            <thead>
                                <tr>
                                    <th>Kecamatan</th>
                                    <th>ID Laravolt</th>
                                    <th>Kab/Kota</th>
                                    <th>Provinsi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <form action="{{ route('expeditions.origin.laravolt') }}" method="POST" id="laravolt-origin-form" style="margin-top:16px;">
                        @csrf
                        <input type="hidden" name="district_code" id="laravolt_selected_district_code">
                        <div class="well well-sm" id="laravolt-origin-selected" style="display:none; margin-bottom:12px;">
                            <b>Terpilih:</b> <span id="laravolt-origin-selected-label"></span>
                        </div>
                        <button type="submit" class="btn btn-success btn-block" id="save-laravolt-origin" disabled>Simpan Origin Laravolt</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><b>Set Backup RajaOngkir</b></h3>
                </div>
                <div class="box-body">
                    <p class="text-muted">Backup/manual override. Pakai ini kalau auto-match Laravolt belum ketemu atau Anda mau pakai ID RajaOngkir tertentu.</p>

                    <div class="form-group">
                        <label>Cari Destinasi RajaOngkir</label>
                        <div class="input-group">
                            <input type="text" id="rajaongkir-origin-keyword" class="form-control" placeholder="Contoh: Pacitan Pacitan Jawa Timur">
                            <span class="input-group-btn">
                                <button type="button" id="search-rajaongkir-origin" class="btn btn-warning">Cari</button>
                            </span>
                        </div>
                    </div>

                    <div id="rajaongkir-origin-feedback" class="alert alert-info" style="display:none;"></div>
                    <div id="rajaongkir-origin-results" class="table-responsive" style="display:none;">
                        <table class="table table-bordered table-condensed">
                            <thead>
                                <tr>
                                    <th>Label</th>
                                    <th>ID RajaOngkir</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <form action="{{ route('expeditions.origin.rajaongkir') }}" method="POST" id="rajaongkir-origin-form" style="margin-top:16px;">
                        @csrf
                        <input type="hidden" name="destination_id" id="rajaongkir_selected_destination_id">
                        <input type="hidden" name="destination_label" id="rajaongkir_selected_destination_label">
                        <div class="well well-sm" id="rajaongkir-origin-selected" style="display:none; margin-bottom:12px;">
                            <b>Terpilih:</b> <span id="rajaongkir-origin-selected-label"></span>
                        </div>
                        <button type="submit" class="btn btn-warning btn-block" id="save-rajaongkir-origin" disabled>Simpan Backup RajaOngkir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    (function() {
        const laravoltSearchButton = document.getElementById('search-laravolt-origin');
        const laravoltKeywordInput = document.getElementById('laravolt-origin-keyword');
        const laravoltFeedback = document.getElementById('laravolt-origin-feedback');
        const laravoltResults = document.getElementById('laravolt-origin-results');
        const laravoltResultsBody = laravoltResults.querySelector('tbody');
        const laravoltSelectedCode = document.getElementById('laravolt_selected_district_code');
        const laravoltSelectedBox = document.getElementById('laravolt-origin-selected');
        const laravoltSelectedLabel = document.getElementById('laravolt-origin-selected-label');
        const saveLaravoltButton = document.getElementById('save-laravolt-origin');

        const rajaSearchButton = document.getElementById('search-rajaongkir-origin');
        const rajaKeywordInput = document.getElementById('rajaongkir-origin-keyword');
        const rajaFeedback = document.getElementById('rajaongkir-origin-feedback');
        const rajaResults = document.getElementById('rajaongkir-origin-results');
        const rajaResultsBody = rajaResults.querySelector('tbody');
        const rajaSelectedId = document.getElementById('rajaongkir_selected_destination_id');
        const rajaSelectedLabelInput = document.getElementById('rajaongkir_selected_destination_label');
        const rajaSelectedBox = document.getElementById('rajaongkir-origin-selected');
        const rajaSelectedLabel = document.getElementById('rajaongkir-origin-selected-label');
        const saveRajaButton = document.getElementById('save-rajaongkir-origin');

        function showFeedback(element, message, level) {
            element.className = 'alert alert-' + (level || 'info');
            element.textContent = message;
            element.style.display = 'block';
        }

        function hideFeedback(element) {
            element.style.display = 'none';
            element.textContent = '';
        }

        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        async function loadLaravoltResults() {
            const keyword = laravoltKeywordInput.value.trim();

            if (!keyword) {
                showFeedback(laravoltFeedback, 'Masukkan kata kunci Laravolt terlebih dahulu.', 'warning');
                return;
            }

            laravoltSearchButton.disabled = true;
            laravoltSearchButton.textContent = 'Mencari...';
            hideFeedback(laravoltFeedback);
            laravoltResults.style.display = 'none';
            laravoltResultsBody.innerHTML = '';

            try {
                const response = await fetch("{{ route('expeditions.origin.laravolt-search') }}?keyword=" + encodeURIComponent(keyword), {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload.message || 'Gagal mencari data Laravolt.');
                }

                if (!payload.data || !payload.data.length) {
                    showFeedback(laravoltFeedback, 'Data Laravolt tidak ditemukan.', 'warning');
                    return;
                }

                laravoltResultsBody.innerHTML = payload.data.map(function(item) {
                    const label = [item.district_name, item.city_name, item.province_name].filter(Boolean).join(', ');

                    return `
                        <tr>
                            <td>${escapeHtml(item.district_name)}</td>
                            <td><code>${escapeHtml(item.district_code)}</code></td>
                            <td>${escapeHtml(item.city_name)}</td>
                            <td>${escapeHtml(item.province_name)}</td>
                            <td>
                                <button
                                    type="button"
                                    class="btn btn-success btn-xs choose-laravolt-origin"
                                    data-code="${escapeHtml(item.district_code)}"
                                    data-label="${escapeHtml(label)}"
                                >Pilih</button>
                            </td>
                        </tr>
                    `;
                }).join('');

                laravoltResults.style.display = 'block';
            } catch (error) {
                showFeedback(laravoltFeedback, error.message || 'Gagal mencari data Laravolt.', 'danger');
            } finally {
                laravoltSearchButton.disabled = false;
                laravoltSearchButton.textContent = 'Cari';
            }
        }

        async function loadRajaResults() {
            const keyword = rajaKeywordInput.value.trim();

            if (!keyword) {
                showFeedback(rajaFeedback, 'Masukkan kata kunci RajaOngkir terlebih dahulu.', 'warning');
                return;
            }

            rajaSearchButton.disabled = true;
            rajaSearchButton.textContent = 'Mencari...';
            hideFeedback(rajaFeedback);
            rajaResults.style.display = 'none';
            rajaResultsBody.innerHTML = '';

            try {
                const response = await fetch("{{ route('expeditions.origin.rajaongkir-search') }}?keyword=" + encodeURIComponent(keyword), {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload.message || 'Gagal mencari destinasi RajaOngkir.');
                }

                if (!payload.data || !payload.data.length) {
                    showFeedback(rajaFeedback, 'Destinasi RajaOngkir tidak ditemukan.', 'warning');
                    return;
                }

                rajaResultsBody.innerHTML = payload.data.map(function(item) {
                    return `
                        <tr>
                            <td>${escapeHtml(item.label)}</td>
                            <td><code>${escapeHtml(item.id)}</code></td>
                            <td>
                                <button
                                    type="button"
                                    class="btn btn-warning btn-xs choose-raja-origin"
                                    data-id="${escapeHtml(item.id)}"
                                    data-label="${escapeHtml(item.label)}"
                                >Pilih</button>
                            </td>
                        </tr>
                    `;
                }).join('');

                rajaResults.style.display = 'block';
            } catch (error) {
                showFeedback(rajaFeedback, error.message || 'Gagal mencari destinasi RajaOngkir.', 'danger');
            } finally {
                rajaSearchButton.disabled = false;
                rajaSearchButton.textContent = 'Cari';
            }
        }

        laravoltSearchButton.addEventListener('click', loadLaravoltResults);
        rajaSearchButton.addEventListener('click', loadRajaResults);

        laravoltKeywordInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                loadLaravoltResults();
            }
        });

        rajaKeywordInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                loadRajaResults();
            }
        });

        laravoltResultsBody.addEventListener('click', function(event) {
            const button = event.target.closest('.choose-laravolt-origin');

            if (!button) {
                return;
            }

            laravoltSelectedCode.value = button.dataset.code || '';
            laravoltSelectedLabel.textContent = button.dataset.label || '';
            laravoltSelectedBox.style.display = 'block';
            saveLaravoltButton.disabled = !laravoltSelectedCode.value;
            hideFeedback(laravoltFeedback);
        });

        rajaResultsBody.addEventListener('click', function(event) {
            const button = event.target.closest('.choose-raja-origin');

            if (!button) {
                return;
            }

            rajaSelectedId.value = button.dataset.id || '';
            rajaSelectedLabelInput.value = button.dataset.label || '';
            rajaSelectedLabel.textContent = (button.dataset.label || '') + ' (ID: ' + (button.dataset.id || '-') + ')';
            rajaSelectedBox.style.display = 'block';
            saveRajaButton.disabled = !rajaSelectedId.value;
            hideFeedback(rajaFeedback);
        });
    })();
</script>
@endpush
