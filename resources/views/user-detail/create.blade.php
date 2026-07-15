@extends('layouts.master')
@section('container')
@php
$categoryId = auth()->user()->category_id;
$jatimCode = '35';
$jatimName = 'JAWA TIMUR';
$pacitanCode = '3501';
$pacitanName = 'KABUPATEN PACITAN';
@endphp
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ $detail ? 'Edit Biodata' : 'Lengkapi Biodata' }}</h3>
                </div>

                @if(session('success'))
                <div class="alert alert-success" style="margin:16px 16px 0;">
                    {{ session('success') }}
                </div>
                @endif

                <form action="{{ route('user-detail.save') }}" method="POST">
                    @csrf
                    <div class="box-body">

                        @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <h5 style="font-weight:700; border-left:3px solid #1db9a0; padding-left:10px; margin:0 0 16px;">Informasi Komunitas</h5>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Komunitas <span class="text-danger">*</span></label>
                                    <input type="text" name="community_name" class="form-control"
                                        placeholder="Nama Komunitas / Rumah Produksi / Kelompok"
                                        value="{{ old('community_name', $detail->community_name ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Posisi / Jabatan <span class="text-danger">*</span></label>
                                    <input type="text" name="posisi" class="form-control"
                                        placeholder="cth: Ketua, Anggota, Sutradara"
                                        value="{{ old('posisi', $detail->posisi ?? '') }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Username Instagram <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon">@</span>
                                    <input type="text" name="username_ig" class="form-control"
                                        placeholder="username_instagram"
                                        value="{{ old('username_ig', $detail->username_ig ?? '') }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Lahir <span class="text-danger">*</span></label>
                                <input type="date"
                                    name="tanggal_lahir"
                                    class="form-control"
                                    value="{{ old('tanggal_lahir', $detail->tanggal_lahir ?? '') }}"
                                    required>
                            </div>
                        </div>

                        <h5 style="font-weight:700; border-left:3px solid #1db9a0; padding-left:10px; margin:20px 0 16px;">Wilayah</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Provinsi <span class="text-danger">*</span></label>
                                    @if($categoryId == 1)
                                    <select name="provinsi_code" id="provinsi" class="form-control select2" required>
                                        <option value="">Pilih Provinsi</option>
                                        @foreach($provinsi as $p)
                                        <option value="{{ $p->code }}" data-name="{{ $p->name }}"
                                            {{ old('provinsi_code', $detail->provinsi_code ?? '') == $p->code ? 'selected' : '' }}>
                                            {{ $p->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @else
                                    <input type="text" class="form-control" value="{{ $jatimName }}" disabled>
                                    <input type="hidden" name="provinsi_code" id="provinsi" value="{{ $jatimCode }}">
                                    @endif
                                    <input type="hidden" name="provinsi_name" id="provinsi_name"
                                        value="{{ old('provinsi_name', $detail->provinsi_name ?? ($categoryId != 1 ? $jatimName : '')) }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kabupaten / Kota <span class="text-danger">*</span></label>
                                    @if(in_array($categoryId, [3, 4]))
                                    <input type="text" class="form-control" value="{{ $pacitanName }}" disabled>
                                    <input type="hidden" name="kabupaten_code" id="kabupaten" value="{{ $pacitanCode }}">
                                    <input type="hidden" name="kabupaten_name" id="kabupaten_name" value="{{ $pacitanName }}">
                                    @else
                                    <select name="kabupaten_code" id="kabupaten" class="form-control select2" required>
                                        <option value="">Pilih Kabupaten/Kota</option>
                                    </select>
                                    <input type="hidden" name="kabupaten_name" id="kabupaten_name"
                                        value="{{ old('kabupaten_name', $detail->kabupaten_name ?? '') }}">
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kecamatan <span class="text-danger">*</span></label>
                                    <select name="kecamatan_code" id="kecamatan" class="form-control select2" required>
                                        <option value="">Pilih Kecamatan</option>
                                    </select>
                                    <input type="hidden" name="kecamatan_name" id="kecamatan_name"
                                        value="{{ old('kecamatan_name', $detail->kecamatan_name ?? '') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Desa / Kelurahan <span class="text-danger">*</span></label>
                                    <select name="desa_code" id="desa" class="form-control select2" required>
                                        <option value="">Pilih Desa/Kelurahan</option>
                                    </select>
                                    <input type="hidden" name="desa_name" id="desa_name"
                                        value="{{ old('desa_name', $detail->desa_name ?? '') }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea name="alamat_lengkap" class="form-control" rows="3"
                                placeholder="Nama jalan, nomor rumah, RT/RW..." required>{{ old('alamat_lengkap', $detail->alamat_lengkap ?? '') }}</textarea>
                        </div>

                    </div>

                    <div class="box-footer">
                        <a href="{{ route('dashboard') }}" class="btn btn-default">Kembali</a>
                        <button type="submit" class="btn btn-primary">
                            {{ $detail ? 'Perbarui Biodata' : 'Simpan Biodata' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    // Ambil dari PHP dulu, assign ke JS variable dengan cara aman
    const categoryId = parseInt("{{ $categoryId ?? 1 }}");
    const JATIM_CODE = "{{ $jatimCode }}";
    const PACITAN_CODE = "{{ $pacitanCode }}";
    const existingKabupaten = "{{ old('kabupaten_code', $detail->kabupaten_code ?? '') }}";
    const existingKecamatan = "{{ old('kecamatan_code', $detail->kecamatan_code ?? '') }}";
    const existingDesaCode = "{{ old('desa_code', $detail->desa_code ?? '') }}";

    console.log('categoryId:', categoryId);
    console.log('JATIM_CODE:', JATIM_CODE);
    console.log('PACITAN_CODE:', PACITAN_CODE);

    // Helper: refresh Select2 setelah options berubah
    function refreshSelect2(selector) {
        if (typeof $.fn.select2 !== 'undefined') {
            $(selector).trigger('change.select2');
        }
    }

    // Event: provinsi berubah (hanya aktif untuk category 1)
    $('#provinsi').on('change', function() {
        if (categoryId != 1) return;
        const code = $(this).val();
        const name = $(this).find('option:selected').data('name');
        $('#provinsi_name').val(name);
        $('#kabupaten').html('<option value="">Pilih Kabupaten/Kota</option>');
        $('#kecamatan').html('<option value="">Pilih Kecamatan</option>');
        $('#desa').html('<option value="">Pilih Desa/Kelurahan</option>');
        refreshSelect2('#kabupaten');
        refreshSelect2('#kecamatan');
        refreshSelect2('#desa');
        if (!code) return;
        loadKabupaten(code, null);
    });

    // Event: kabupaten berubah
    $('#kabupaten').on('change', function() {
        const code = $(this).val();
        const name = $(this).find('option:selected').text();
        $('#kabupaten_name').val(name);
        $('#kecamatan').html('<option value="">Pilih Kecamatan</option>');
        $('#desa').html('<option value="">Pilih Desa/Kelurahan</option>');
        refreshSelect2('#kecamatan');
        refreshSelect2('#desa');
        if (!code) return;
        loadKecamatan(code, existingKecamatan || null);
    });

    // Event: kecamatan berubah
    $('#kecamatan').on('change', function() {
        const code = $(this).val();
        const name = $(this).find('option:selected').text();
        $('#kecamatan_name').val(name);
        $('#desa').html('<option value="">Pilih Desa/Kelurahan</option>');
        refreshSelect2('#desa');
        if (!code) return;
        loadDesa(code, existingDesaCode || null);
    });

    // Event: desa berubah
    $('#desa').on('change', function() {
        $('#desa_name').val($(this).find('option:selected').text());
    });

    function loadKabupaten(provCode, selectedCode = null) {
        $.get('/api/wilayah/kabupaten/' + provCode, function(data) {
            $('#kabupaten').html('<option value="">Pilih Kabupaten/Kota</option>');
            data.forEach(function(item) {
                const selected = selectedCode && item.code == selectedCode ? 'selected' : '';
                $('#kabupaten').append('<option value="' + item.code + '" ' + selected + '>' + item.name + '</option>');
            });
            refreshSelect2('#kabupaten');
            if (selectedCode) {
                $('#kabupaten_name').val($('#kabupaten option:selected').text());
                $('#kabupaten').trigger('change');
            }
        });
    }

    function loadKecamatan(kabCode, selectedCode = null) {
        $.get('/api/wilayah/kecamatan/' + kabCode, function(data) {
            $('#kecamatan').html('<option value="">Pilih Kecamatan</option>');
            data.forEach(function(item) {
                const selected = selectedCode && item.code == selectedCode ? 'selected' : '';
                $('#kecamatan').append('<option value="' + item.code + '" ' + selected + '>' + item.name + '</option>');
            });
            refreshSelect2('#kecamatan');
            if (selectedCode) {
                $('#kecamatan_name').val($('#kecamatan option:selected').text());
                $('#kecamatan').trigger('change');
            }
        });
    }

    function loadDesa(kecCode, selectedCode = null) {
        $.get('/api/wilayah/desa/' + kecCode, function(data) {
            $('#desa').html('<option value="">Pilih Desa/Kelurahan</option>');
            data.forEach(function(item) {
                const selected = selectedCode && item.code == selectedCode ? 'selected' : '';
                $('#desa').append('<option value="' + item.code + '" ' + selected + '>' + item.name + '</option>');
            });
            refreshSelect2('#desa');
            if (selectedCode) {
                $('#desa_name').val($('#desa option:selected').text());
            }
        });
    }

    // Auto load saat halaman dibuka
    $(document).ready(function() {
        // Init Select2 dulu sebelum auto-load
        $('.select2').select2();

        if (categoryId == 1) {
            const provCode = $('#provinsi').val();
            if (provCode) loadKabupaten(provCode, existingKabupaten || null);

        } else if (categoryId == 2) {
            loadKabupaten(JATIM_CODE, existingKabupaten || null);

        } else {
            // Kategori 3 & 4: kabupaten dikunci Pacitan, langsung load kecamatan
            loadKecamatan(PACITAN_CODE, existingKecamatan || null);
        }
    });
</script>
@endpush