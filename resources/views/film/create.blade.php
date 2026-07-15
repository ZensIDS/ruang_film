@extends('layouts.master')
@section('container')
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><b>Tambah Submission</b></h3>
                </div>
                <form id="taskForm" action="{{ route('film.store') }}" method="POST" enctype="multipart/form-data">
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
                        {{-- Informasi Film --}}
                        <h5 style="font-weight:700; border-left:3px solid #1db9a0; padding-left:10px; margin:0 0 16px;">Informasi Film</h5>

                        @if(isset($activeSetting))
                        <div class="alert alert-info">
                            Submission aktif: <strong>{{ $activeSetting->name }}</strong><br>
                            Kategori peserta: <strong>{{ auth()->user()->category->name ?? '-' }}</strong>
                        </div>
                        @endif

                        <div class="form-group">
                            <label>Judul Film <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Masukkan judul film" required>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Subtitle <span class="text-danger">*</span></label>
                                    <div style="margin-top: 6px;">
                                        <div class="radio" style="display:inline-block; margin-right:20px;">
                                            <label style="font-weight:normal; cursor:pointer;">
                                                <input type="radio" name="subtitle" value="Ya" required> Ya
                                            </label>
                                        </div>
                                        <div class="radio" style="display:inline-block;">
                                            <label style="font-weight:normal; cursor:pointer;">
                                                <input type="radio" name="subtitle" value="Tidak" required> Tidak
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
                                    <div style="display:flex; align-items:center; gap:6px;">
                                        <input type="number" id="dur_h" min="0" max="99" placeholder="00"
                                            class="form-control" style="width:70px; text-align:center; font-weight:600;">
                                        <span style="font-size:18px; color:#888;">:</span>
                                        <input type="number" id="dur_m" min="0" max="59" placeholder="00"
                                            class="form-control" style="width:70px; text-align:center; font-weight:600;">
                                        <span style="font-size:18px; color:#888;">:</span>
                                        <input type="number" id="dur_s" min="0" max="59" placeholder="00"
                                            class="form-control" style="width:70px; text-align:center; font-weight:600;">
                                    </div>
                                    {{-- Field hidden yang benar-benar dikirim ke server --}}
                                    <input type="hidden" name="duration" id="duration_seconds">
                                    <small class="text-danger">Format: jam : menit : detik</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tahun Produksi <span class="text-danger">*</span></label>
                                    <input type="number" name="tahun_produksi" class="form-control"
                                        placeholder="Tahun Produksi" min="2023" max="2099" required>
                                    <p class="text-danger">Tahun Produksi Minimal Tahun 2023.</p>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Sinopsis <span class="text-danger">*</span></label>
                            <textarea name="sinopsis" class="form-control" rows="4"
                                placeholder="Tuliskan sinopsis film..." required></textarea>
                        </div>

                        {{-- Kru Film --}}
                        <h5 style="font-weight:700; border-left:3px solid #1db9a0; padding-left:10px; margin:20px 0 16px;">Kru Film</h5>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Sutradara <span class="text-danger">*</span></label>
                                    <input type="text" name="sutradara" class="form-control"
                                        placeholder="Nama sutradara" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Produser <span class="text-danger">*</span></label>
                                    <input type="text" name="produser" class="form-control"
                                        placeholder="Nama produser" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Penulis Skenario <span class="text-danger">*</span></label>
                                    <input type="text" name="penulis" class="form-control"
                                        placeholder="Nama penulis" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Daftar Kru Film <span class="text-danger">*</span></label>
                                    <input type="file" name="kru" class="form-control" required>
                                    <p class="text-danger">File Maks 5MB.</p>
                                </div>
                            </div>
                        </div>



                        {{-- File & Tautan --}}
                        <h5 style="font-weight:700; border-left:3px solid #1db9a0; padding-left:10px; margin:20px 0 16px;">File & Tautan</h5>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Poster Film <span class="text-danger">*</span></label>
                                    <input type="file" name="poster" class="form-control" accept="image/*" required>
                                    <p class="text-danger">Format: JPG, PNG. Maks 5MB.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Grab Still Photo <span class="text-danger">*</span></label>
                                    <input type="file" name="gsm[]" class="form-control" id="gsm_input" multiple required>
                                    <p class="text-danger">Bisa upload lebih dari 1 file. Tahan Ctrl (Windows) atau Cmd (Mac) untuk pilih beberapa file sekaligus.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Screenshot Bukti Unggah / Repost</label>
                                    <input type="file" name="other_2" class="form-control" accept="image/*,.pdf">
                                    <p class="text-danger">Upload bukti unggah/repost. Maks 10MB.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Link Trailer <span class="text-danger">*</span></label>
                                    <input type="url" name="trailer" class="form-control"
                                        placeholder="Masukkan Link Trailer Film" required>
                                    <p class="text-danger">Masukkan link Google Drive. Pastikan Akses Link Sudah Dibuka.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>File Film <span class="text-danger">*</span></label>
                                    <input type="url" name="film" class="form-control"
                                        placeholder="Masukkan Link Film" required>
                                    <p class="text-danger">Masukkan link Google Drive. Pastikan Akses Link Sudah Dibuka.</p>
                                </div>
                            </div>
                        </div>

                        {{-- Field Dinamis berdasarkan kategori user --}}
                        @php
                        $userCategoryId = auth()->user()->category_id;
                        $dynamicLabel = match((int)$userCategoryId) {
                        1 => 'Press Kit (Opsional)',
                        2, 4 => 'Surat Rekomendasi Sekolah',
                        default => null
                        };
                        @endphp

                        @if($dynamicLabel)
                        <div style="background:#f0faf7; border:1px dashed #1db9a0; border-radius:8px; padding:14px 16px; margin-bottom:16px;">
                            <label style="font-weight:600; color:#0a7a5e;">{{ $dynamicLabel }}</label>
                            <input type="file" name="other_1" id="dynamic_input" class="form-control" style="margin-top:6px;">
                        </div>
                        @endif

                        <div class="form-group">
                            @php $submissionOpen = \App\Models\SubmissionSetting::isOpen(); @endphp

                            @if($submissionOpen)
                            <button type="submit" class="btn btn-info btn-block" style="border-radius:8px; font-weight:600;">
                                Kirim Submission
                            </button>
                            @else
                            <button type="button" class="btn btn-default btn-block" disabled
                                style="border-radius:8px; font-weight:600; cursor:not-allowed;">
                                <i class="fa fa-lock"></i> Submission Ditutup
                            </button>
                            @endif
                        </div>
                    </div>

                    <div class="box-footer">
                        <a href="{{ route('film.index') }}" class="btn btn-default">Kembali</a>
                        <!-- <button type="submit" class="btn btn-primary">Simpan</button> -->
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
@push('scripts')
<script>
    // ── Durasi: tampilkan HH:MM:SS → simpan sebagai total detik ──
    function hitungDetik() {
        const h = parseInt(document.getElementById('dur_h').value) || 0;
        const m = parseInt(document.getElementById('dur_m').value) || 0;
        const s = parseInt(document.getElementById('dur_s').value) || 0;
        document.getElementById('duration_seconds').value = (h * 3600) + (m * 60) + s;
    }
    ['dur_h', 'dur_m', 'dur_s'].forEach(id => {
        document.getElementById(id).addEventListener('input', hitungDetik);
    });

    // ── Pastikan duration_seconds terisi sebelum form disubmit ──
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
