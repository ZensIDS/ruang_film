@extends('layouts.master')
@section('container')
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><b>Edit Submission</b></h3>
                </div>
                <form id="taskForm" action="{{ route('film.update', $film->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
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

                        <input type="hidden" name="user_id" value="{{ $film->user_id }}">

                        {{-- Informasi Film --}}
                        <h5 style="font-weight:700; border-left:3px solid #1db9a0; padding-left:10px; margin:0 0 16px;">Informasi Film</h5>

                        @if(in_array(auth()->user()->role, ['admin', 'adminsub']))
                        <div class="form-group">
                            <label>Kategori <span class="text-danger">*</span></label>
                            <select name="category_id" id="category_id" class="form-control" required>
                                <option value="" disabled>Pilih Kategori</option>
                                @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('category_id', $film->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        <input type="hidden" name="category_id" value="{{ $film->category_id }}">
                        <div class="form-group">
                            <label>Kategori</label>
                            <input type="text" class="form-control" value="{{ $film->category->name ?? '-' }}" readonly>
                        </div>
                        @endif

                        <div class="form-group">
                            <label>Judul Film <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                placeholder="Masukkan judul film"
                                value="{{ old('name', $film->name) }}" required>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Subtitle <span class="text-danger">*</span></label>
                                    <div style="margin-top: 6px;">
                                        <div class="radio" style="display:inline-block; margin-right:20px;">
                                            <label style="font-weight:normal; cursor:pointer;">
                                                <input type="radio" name="subtitle" value="Ya"
                                                    {{ old('subtitle', $film->subtitle) == 'Ya' ? 'checked' : '' }} required> Ya
                                            </label>
                                        </div>
                                        <div class="radio" style="display:inline-block;">
                                            <label style="font-weight:normal; cursor:pointer;">
                                                <input type="radio" name="subtitle" value="Tidak"
                                                    {{ old('subtitle', $film->subtitle) == 'Tidak' ? 'checked' : '' }} required> Tidak
                                            </label>
                                        </div>
                                    </div>
                                    @error('subtitle')
                                    <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Durasi <span class="text-danger">*</span></label>
                                    @php
                                    $durH = floor($film->duration / 3600);
                                    $durM = floor(($film->duration % 3600) / 60);
                                    $durS = $film->duration % 60;
                                    @endphp
                                    <div style="display:flex; align-items:center; gap:6px;">
                                        <input type="number" id="dur_h" min="0" max="99"
                                            class="form-control" style="width:70px; text-align:center; font-weight:600;"
                                            value="{{ old('dur_h', $durH) }}">
                                        <span style="font-size:18px; color:#888;">:</span>
                                        <input type="number" id="dur_m" min="0" max="59"
                                            class="form-control" style="width:70px; text-align:center; font-weight:600;"
                                            value="{{ old('dur_m', $durM) }}">
                                        <span style="font-size:18px; color:#888;">:</span>
                                        <input type="number" id="dur_s" min="0" max="59"
                                            class="form-control" style="width:70px; text-align:center; font-weight:600;"
                                            value="{{ old('dur_s', $durS) }}">
                                    </div>
                                    <input type="hidden" name="duration" id="duration_seconds"
                                        value="{{ old('duration', $film->duration) }}">
                                    <small class="text-muted">Format: jam : menit : detik</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tahun Produksi <span class="text-danger">*</span></label>
                                    <input type="number" name="tahun_produksi" class="form-control"
                                        placeholder="2024" min="1900" max="2099"
                                        value="{{ old('tahun_produksi', $film->tahun_produksi) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Sinopsis <span class="text-danger">*</span></label>
                            <textarea name="sinopsis" class="form-control" rows="4"
                                placeholder="Tuliskan sinopsis film..." required>{{ old('sinopsis', $film->sinopsis) }}</textarea>
                        </div>

                        {{-- Kru Film --}}
                        <h5 style="font-weight:700; border-left:3px solid #1db9a0; padding-left:10px; margin:20px 0 16px;">Kru Film</h5>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Sutradara <span class="text-danger">*</span></label>
                                    <input type="text" name="sutradara" class="form-control"
                                        placeholder="Nama sutradara"
                                        value="{{ old('sutradara', $film->sutradara) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Produser <span class="text-danger">*</span></label>
                                    <input type="text" name="produser" class="form-control"
                                        placeholder="Nama produser"
                                        value="{{ old('produser', $film->produser) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Penulis Skenario</label>
                                    <input type="text" name="penulis" class="form-control"
                                        placeholder="Nama penulis (opsional)"
                                        value="{{ old('penulis', $film->penulis) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Daftar Kru Film</label>
                                    @if($film->kru)
                                    <div style="margin-bottom:8px;">
                                        <a href="{{ $film->kru_url }}" target="_blank"
                                            style="font-size:12px; color:#0d6efd;">
                                            <i class="fa fa-file"></i> File kru saat ini — klik untuk lihat
                                        </a>
                                        <span style="font-size:11px; color:#888; margin-left:6px;">Upload baru untuk mengganti.</span>
                                    </div>
                                    @endif
                                    <input type="file" name="kru" class="form-control">
                                    <p class="text-danger">File Maks 5MB. Kosongkan jika tidak ingin mengganti.</p>
                                </div>
                            </div>
                        </div>

                        {{-- File & Tautan --}}
                        <h5 style="font-weight:700; border-left:3px solid #1db9a0; padding-left:10px; margin:20px 0 16px;">File & Tautan</h5>

                        {{-- GSM --}}
                        <div class="row">
                            <div class="col-md-6">
                                {{-- Poster --}}
                                <div class="form-group">
                                    <label>Poster Film</label>
                                    @if($film->poster)
                                    <div style="margin-bottom:8px;">
                                        <img src="{{ $film->poster_url }}"
                                            style="height:100px; border-radius:6px; border:0.5px solid #ddd; object-fit:cover;">
                                        <div style="font-size:11px; color:red; margin-top:4px;">Poster saat ini. Upload baru untuk mengganti.</div>
                                    </div>
                                    @endif
                                    <input type="file" name="poster" class="form-control" accept="image/*">
                                    <p class="text-danger">Format: JPG, PNG. Maks 5MB. Kosongkan jika tidak ingin mengganti.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Grab Still Photo</label>
                                    @php
                                    $gsmFiles = $film->gsm_files;
                                    $gsmUrls = $film->gsm_urls;
                                    @endphp
                                    @if(count($gsmFiles) > 0)
                                    <div style="display:grid; grid-template-columns:repeat(5,1fr); gap:4px; margin-bottom:8px;">
                                        @foreach($gsmFiles as $i => $path)
                                        <div style="aspect-ratio:1/1; border-radius:6px; overflow:hidden; border:0.5px solid #ddd; position:relative;">
                                            <img src="{{ $gsmUrls[$i] ?? $film->mediaUrl($path) }}"
                                                style="width:100%; height:100%; object-fit:cover;">
                                            <div style="position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,0.45);color:#fff;font-size:9px;text-align:center;padding:2px 0;">
                                                {{ $i + 1 }}
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    <div style="font-size:11px; color:red; margin-bottom:6px;">Grab Still Photo saat ini ({{ count($gsmFiles) }} file). Upload baru untuk mengganti semua.</div>
                                    @endif
                                    <input type="file" name="gsm[]" class="form-control" id="gsm_input" multiple>
                                    <p class="text-danger">Kosongkan jika tidak ingin mengganti.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Screenshot Bukti Unggah / Repost</label>
                                    @php
                                    $other2IsImage = \Illuminate\Support\Str::endsWith(strtolower((string) $film->other_2), ['.jpg', '.jpeg', '.png', '.gif', '.webp']);
                                    @endphp
                                    @if($film->other_2)
                                    <div style="margin-bottom:8px;">
                                        @if($other2IsImage)
                                        <a href="{{ $film->other_2_url }}" target="_blank" style="display:inline-block;">
                                            <img src="{{ $film->other_2_url }}"
                                                style="height:100px; border-radius:6px; border:0.5px solid #ddd; object-fit:cover;">
                                        </a>
                                        <div style="font-size:11px; color:red; margin-top:4px;">File saat ini. Upload baru untuk mengganti.</div>
                                        @else
                                        <a href="{{ $film->other_2_url }}" target="_blank"
                                            style="font-size:12px; color:#0d6efd; display:inline-flex; align-items:center; gap:5px;">
                                            <i class="fa fa-file"></i> File saat ini — klik untuk lihat
                                        </a>
                                        <span style="font-size:11px; color:#888; margin-left:6px;">Upload baru untuk mengganti.</span>
                                        @endif
                                    </div>
                                    @endif
                                    <input type="file" name="other_2" class="form-control" accept="image/*,.pdf">
                                    <p class="text-danger">Upload bukti unggah/repost. Kosongkan jika tidak ingin mengganti.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Link Trailer <span class="text-danger">*</span></label>
                                    <input type="url" name="trailer" class="form-control"
                                        placeholder="Masukkan Link Trailer Film"
                                        value="{{ old('trailer', $film->trailer) }}" required>
                                    <p class="text-danger">Masukkan link Google Drive. Pastikan Akses Link Sudah Dibuka.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>File Film <span class="text-danger">*</span></label>
                                    <input type="url" name="film" class="form-control"
                                        placeholder="Masukkan Link Film"
                                        value="{{ old('film', $film->film) }}" required>
                                    <p class="text-danger">Masukkan link Google Drive. Pastikan Akses Link Sudah Dibuka.</p>
                                </div>
                            </div>
                        </div>

                        {{-- Field Dinamis berdasarkan kategori --}}
                        <div id="dynamic_field"
                            style="display:{{ in_array($film->category_id, [1,2,4]) ? 'block' : 'none' }}; background:#f0faf7; border:1px dashed #1db9a0; border-radius:8px; padding:14px 16px; margin-bottom:16px;">
                            <label id="dynamic_label" style="font-weight:600; color:#0a7a5e;">
                                @if($film->category_id == 1) Press Kit (Opsional)
                                @elseif(in_array($film->category_id, [2,4])) Surat Rekomendasi Sekolah
                                @endif
                            </label>
                            @if($film->other_1)
                            <div style="margin:6px 0 8px;">
                                <a href="{{ $film->other_1_url }}" target="_blank"
                                    style="font-size:12px; color:#0a7a5e;">
                                    <i class="fa fa-file"></i> File saat ini — klik untuk lihat
                                </a>
                                <span style="font-size:11px; color:#888; margin-left:6px;">Upload baru untuk mengganti.</span>
                            </div>
                            @endif
                            <input type="file" name="other_1" id="dynamic_input" class="form-control" style="margin-top:6px;">
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-info btn-block" style="border-radius:8px; font-weight:600;">
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>

                    <div class="box-footer">
                        <a href="{{ route('film.index') }}" class="btn btn-default">Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
@push('scripts')
<script>
    // ── Durasi ──
    function hitungDetik() {
        const h = parseInt(document.getElementById('dur_h').value) || 0;
        const m = parseInt(document.getElementById('dur_m').value) || 0;
        const s = parseInt(document.getElementById('dur_s').value) || 0;
        document.getElementById('duration_seconds').value = (h * 3600) + (m * 60) + s;
    }
    ['dur_h', 'dur_m', 'dur_s'].forEach(id => {
        document.getElementById(id).addEventListener('input', hitungDetik);
    });

    // ── Field dinamis ──
    const categoryMap = {
        1: 'Press Kit',
        2: 'Surat Rekomendasi Sekolah',
        4: 'Surat Rekomendasi Sekolah'
    };

    const categorySelect = document.getElementById('category_id');
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            const catId = parseInt(this.value);
            const wrapper = document.getElementById('dynamic_field');
            const label = document.getElementById('dynamic_label');
            const input = document.getElementById('dynamic_input');

            if (categoryMap[catId]) {
                label.innerHTML = categoryMap[catId] + ' <span class="text-danger">*</span>';
                input.required = true;
                wrapper.style.display = 'block';
            } else {
                wrapper.style.display = 'none';
                input.required = false;
                input.value = '';
            }
        });
    }

    // ── Validasi submit ──
    document.querySelector('form').addEventListener('submit', function(e) {
        hitungDetik();
        const dur = parseInt(document.getElementById('duration_seconds').value);
        if (!dur || dur <= 0) {
            e.preventDefault();
            alert('Durasi film wajib diisi.');
        }
    });
</script>
@endpush
