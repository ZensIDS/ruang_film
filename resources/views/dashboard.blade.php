@extends('layouts.master')
@section('container')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        Dashboard
        <small>Ruang Film Pacitan</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">{{-- Header + Deadline --}}
    <div class="row" style="margin-bottom: 16px;">
        <div class="col-md-12">
            @php
            $setting = \App\Models\SubmissionSetting::current();
            $submissionOpen = \App\Models\SubmissionSetting::isOpen();
            @endphp

            @if($setting)
            @if($submissionOpen)
            {{-- Sudah open, tampilkan kapan close --}}
            <div style="background:#fff8e6; border:1px solid #ffe0a0; border-radius:10px; padding:14px 16px; display:flex; align-items:center; gap:12px;">
                <div style="width:44px;height:44px;border-radius:10px;background:#fff0c0;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;">📅</div>
                <div>
                    <div style="font-size:11px;color:#a07000;font-weight:600;">Batas Akhir Submission</div>
                    <div style="font-size:16px;font-weight:700;color:#7a5000;">
                        {{ $setting->close_at->translatedFormat('d F Y') }}
                    </div>
                    <div style="font-size:11px;color:#b08000;">
                        {{ $setting->close_at->format('H:i') }} WIB
                    </div>
                    @if(auth()->user()->role == 'peserta')
                    <a href="{{ route('film.create') }}" style="display:inline-block;margin-top:6px;background:#e6a800;color:#fff;border-radius:6px;padding:4px 12px;font-size:11px;font-weight:600;text-decoration:none;">
                        + Buat Submission →
                    </a>
                    @endif

                    <div style="margin-top:8px;">
                        <div style="font-size:11px;color:#a07000;font-weight:600;margin-bottom:5px;">Ditutup dalam:</div>
                        <div id="countdown-close" style="display:flex;gap:6px;">
                            <div style="background:#fff0c0;border-radius:6px;padding:4px 8px;text-align:center;min-width:44px;">
                                <div id="cc-days" style="font-size:16px;font-weight:700;color:#7a5000;">--</div>
                                <div style="font-size:9px;color:#a07000;font-weight:600;">HARI</div>
                            </div>
                            <div style="background:#fff0c0;border-radius:6px;padding:4px 8px;text-align:center;min-width:44px;">
                                <div id="cc-hours" style="font-size:16px;font-weight:700;color:#7a5000;">--</div>
                                <div style="font-size:9px;color:#a07000;font-weight:600;">JAM</div>
                            </div>
                            <div style="background:#fff0c0;border-radius:6px;padding:4px 8px;text-align:center;min-width:44px;">
                                <div id="cc-minutes" style="font-size:16px;font-weight:700;color:#7a5000;">--</div>
                                <div style="font-size:9px;color:#a07000;font-weight:600;">MENIT</div>
                            </div>
                            <div style="background:#fff0c0;border-radius:6px;padding:4px 8px;text-align:center;min-width:44px;">
                                <div id="cc-seconds" style="font-size:16px;font-weight:700;color:#7a5000;">--</div>
                                <div style="font-size:9px;color:#a07000;font-weight:600;">DETIK</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @else
            {{-- Belum open atau sudah lewat, tampilkan kapan open --}}
            <div style="position:relative; background:#f0f4ff; border:1px solid #c5d3f5; border-radius:10px; padding:14px 16px; display:flex; align-items:center; gap:12px;">
                <div style="width:44px;height:44px;border-radius:10px;background:#dce6ff;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;">🔒</div>
                <div>
                    @if(now()->lessThan($setting->open_at))
                    {{-- Belum dibuka --}}
                    <div style="font-size:11px;color:#3a5bbf;font-weight:600;">Submission Belum Dibuka</div>
                    <div style="font-size:16px;font-weight:700;color:#1a3a8f;">
                        {{ $setting->open_at->translatedFormat('d F Y') }}
                    </div>
                    <div style="font-size:11px;color:#3a5bbf;">
                        {{ $setting->open_at->format('H:i') }} WIB
                    </div>
                    <div style="margin-top:8px;">
                        <div style="font-size:11px;color:#3a5bbf;font-weight:600;margin-bottom:5px;">Dibuka dalam:</div>
                        <div id="countdown-open" style="display:flex;gap:6px;">
                            <div style="background:#dce6ff;border-radius:6px;padding:4px 8px;text-align:center;min-width:44px;">
                                <div id="cd-days" style="font-size:16px;font-weight:700;color:#1a3a8f;">--</div>
                                <div style="font-size:9px;color:#3a5bbf;font-weight:600;">HARI</div>
                            </div>
                            <div style="background:#dce6ff;border-radius:6px;padding:4px 8px;text-align:center;min-width:44px;">
                                <div id="cd-hours" style="font-size:16px;font-weight:700;color:#1a3a8f;">--</div>
                                <div style="font-size:9px;color:#3a5bbf;font-weight:600;">JAM</div>
                            </div>
                            <div style="background:#dce6ff;border-radius:6px;padding:4px 8px;text-align:center;min-width:44px;">
                                <div id="cd-minutes" style="font-size:16px;font-weight:700;color:#1a3a8f;">--</div>
                                <div style="font-size:9px;color:#3a5bbf;font-weight:600;">MENIT</div>
                            </div>
                            <div style="background:#dce6ff;border-radius:6px;padding:4px 8px;text-align:center;min-width:44px;">
                                <div id="cd-seconds" style="font-size:16px;font-weight:700;color:#1a3a8f;">--</div>
                                <div style="font-size:9px;color:#3a5bbf;font-weight:600;">DETIK</div>
                            </div>
                        </div>
                    </div>
                    @else
                    {{-- Sudah ditutup --}}
                    <div style="font-size:11px;color:#a03030;font-weight:600;">Submission Telah Ditutup</div>
                    <div style="font-size:13px;color:#7a1a1a;margin-top:2px;">
                        Periode submission untuk saat ini sudah berakhir.
                    </div>

                    @if(auth()->user()->role == 'peserta' && isset($approvedFilmTitles))
                    @if($approvedFilmTitles->isNotEmpty())
                    @php
                    $quotedTitles = $approvedFilmTitles->map(fn($t) => '"' . $t . '"')->implode(', ');
                    @endphp
                    <div style="margin-top:10px;background:#e6f9ef;border:1px solid #b7ecd1;border-radius:8px;padding:10px 12px;font-size:13px;color:#1a7a45;line-height:1.5;">
                        🎉 Selamat! Film Kamu dengan judul {{ $quotedTitles }} Lolos Official Selection FFH 2026.
                    </div>
                    @elseif($totalFilm > 0)
                    <div style="margin-top:10px;background:#f5f0fb;border:1px solid #ddd0f0;border-radius:8px;padding:10px 12px;font-size:13px;color:#6b4faa;line-height:1.5;">
                        Mohon Maaf, Film Kamu Belum Lolos &ldquo;Official Selection&rdquo;<br>
                        Terima kasih telah berpartisipasi dalam Festival Film Horor 2026.
                    </div>
                    @endif
                    @endif

                    @if(auth()->user()->role == 'peserta' && isset($approvedFilmTitles) && $approvedFilmTitles->isNotEmpty())
                    <a href="{{ route('rsvp.timeline') }}"
                        style="margin-top:12px; background:#1a7a45; color:#fff; border-radius:8px; padding:7px 16px; font-size:12px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:6px; box-shadow:0 2px 6px rgba(26,122,69,0.3);">
                        <i class="fa fa-calendar-check-o"></i> Konfirmasi Kehadiran Di FFH 2026
                    </a>
                    @endif
                </div>
            </div>
            @endif
            @endif
            @else
            {{-- Setting belum diatur oleh admin --}}
            <div style="background:#f5f5f5; border:1px solid #e0e0e0; border-radius:10px; padding:14px 16px; display:flex; align-items:center; gap:12px;">
                <div style="width:44px;height:44px;border-radius:10px;background:#ebebeb;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;">⏳</div>
                <div>
                    <div style="font-size:11px;color:#888;font-weight:600;">Jadwal Submission</div>
                    <div style="font-size:14px;font-weight:600;color:#aaa;">Belum ditentukan</div>
                    <div style="font-size:11px;color:#aaa;">Pantau terus untuk informasi terbaru.</div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Stat Cards --}}
    @if(auth()->user()->role != 'peserta')
    <div class="row" style="margin-bottom: 16px;">
        <div class="col-xs-6 col-md-4">
            <div style="background:#fff;border:0.5px solid #e0e0e0;border-radius:10px;padding:14px 16px;display:flex;align-items:center;gap:12px;">
                <div style="width:44px;height:44px;border-radius:10px;background:#fff7e0;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">🎬</div>
                <div>
                    <div style="font-size:22px;font-weight:700;color:#b87f00;line-height:1;">{{ $totalFilm }}</div>
                    <div style="font-size:14px;color:#888;"><b>Total Submission</b></div>
                    <div style="font-size:12px;color:#aaa;">film terdaftar</div>
                </div>
            </div>
        </div>
        <div class="col-xs-6 col-md-4">
            <div style="background:#fff;border:0.5px solid #e0e0e0;border-radius:10px;padding:14px 16px;display:flex;align-items:center;gap:12px;">
                <div style="width:44px;height:44px;border-radius:10px;background:#e8f4fd;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">🕐</div>
                <div>
                    <div style="font-size:22px;font-weight:700;color:#1a6fa8;line-height:1;">{{ $dalamProses }}</div>
                    <div style="font-size:14px;color:#888;"><b>Dalam Proses</b></div>
                    <div style="font-size:12px;color:#aaa;">sedang direview</div>
                </div>
            </div>
        </div>
        <div class="col-xs-6 col-md-4">
            <div style="background:#fff;border:0.5px solid #e0e0e0;border-radius:10px;padding:14px 16px;display:flex;align-items:center;gap:12px;">
                <div style="width:44px;height:44px;border-radius:10px;background:#e6f9ef;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">✅</div>
                <div>
                    <div style="font-size:22px;font-weight:700;color:#1a7a45;line-height:1;">{{ $officialSelection }}</div>
                    <div style="font-size:14px;color:#888;"><b>Official Selection</b></div>
                    <div style="font-size:12px;color:#aaa;">film terpilih</div>
                </div>
            </div>
        </div>
    </div>
    <div class="row" style="margin-bottom: 16px;">
        <div class="col-xs-6 col-md-6">
            <div style="background:#fff;border:0.5px solid #e0e0e0;border-radius:10px;padding:14px 16px;display:flex;align-items:center;gap:12px;">
                <div style="width:44px;height:44px;border-radius:10px;background:#f5f0fb;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">📄</div>
                <div>
                    <div style="font-size:22px;font-weight:700;color:#6b4faa;line-height:1;">{{ $ditolak }}</div>
                    <div style="font-size:14px;color:#888;"><b>Tidak Lolos</b></div>
                    <div style="font-size:12px;color:#aaa;">film tidak terpilih</div>
                </div>
            </div>
        </div>
        {{-- Stat download --}}
        <div class="col-xs-6 col-md-6">
            <div style="background:#fff;border:0.5px solid #e0e0e0;border-radius:10px;padding:14px 16px;display:flex;align-items:center;gap:12px;">
                <div style="width:44px;height:44px;border-radius:10px;background:#e8f4fd;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">📥</div>
                <div>
                    <div style="font-size:22px;font-weight:700;color:#1a6fa8;line-height:1;">{{ $totalDownload }}</div>
                    <div style="font-size:14px;color:#888;">Download E-Katalog</div>
                    <div style="font-size:12px;color:#aaa;">+{{ $downloadHariIni }} hari ini</div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="row" style="margin-bottom: 16px;">
        <div class="col-xs-6 col-md-3">
            <div style="background:#fff;border:0.5px solid #e0e0e0;border-radius:10px;padding:14px 16px;display:flex;align-items:center;gap:12px;">
                <div style="width:44px;height:44px;border-radius:10px;background:#fff7e0;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">🎬</div>
                <div>
                    <div style="font-size:22px;font-weight:700;color:#b87f00;line-height:1;">{{ $totalFilm }}</div>
                    <div style="font-size:14px;color:#888;"><b>Total Submission</b></div>
                    <div style="font-size:12px;color:#aaa;">film terdaftar</div>
                </div>
            </div>
        </div>
        <div class="col-xs-6 col-md-3">
            <div style="background:#fff;border:0.5px solid #e0e0e0;border-radius:10px;padding:14px 16px;display:flex;align-items:center;gap:12px;">
                <div style="width:44px;height:44px;border-radius:10px;background:#e8f4fd;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">🕐</div>
                <div>
                    <div style="font-size:22px;font-weight:700;color:#1a6fa8;line-height:1;">{{ $dalamProses }}</div>
                    <div style="font-size:14px;color:#888;"><b>Dalam Proses</b></div>
                    <div style="font-size:12px;color:#aaa;">sedang direview</div>
                </div>
            </div>
        </div>
        <div class="col-xs-6 col-md-3">
            <div style="background:#fff;border:0.5px solid #e0e0e0;border-radius:10px;padding:14px 16px;display:flex;align-items:center;gap:12px;">
                <div style="width:44px;height:44px;border-radius:10px;background:#e6f9ef;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">✅</div>
                <div>
                    <div style="font-size:22px;font-weight:700;color:#1a7a45;line-height:1;">{{ $officialSelection }}</div>
                    <div style="font-size:14px;color:#888;"><b>Official Selection</b></div>
                    <div style="font-size:12px;color:#aaa;">film terpilih</div>
                </div>
            </div>
        </div>
        <div class="col-xs-6 col-md-3">
            <div style="background:#fff;border:0.5px solid #e0e0e0;border-radius:10px;padding:14px 16px;display:flex;align-items:center;gap:12px;">
                <div style="width:44px;height:44px;border-radius:10px;background:#f5f0fb;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">📄</div>
                <div>
                    <div style="font-size:22px;font-weight:700;color:#6b4faa;line-height:1;">{{ $ditolak }}</div>
                    <div style="font-size:14px;color:#888;"><b>Tidak Lolos</b></div>
                    <div style="font-size:12px;color:#aaa;">film tidak terpilih</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Tabel Submission --}}
    <div class="row">
        <div class="col-md-12">
            <div style="background:#fff;border:0.5px solid #e0e0e0;border-radius:10px;overflow:hidden;margin-bottom:16px;">
                <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 18px;border-bottom:0.5px solid #f0f0f0;">
                    <span style="font-weight:700;font-size:15px;">Data Submission</span>
                    @php $submissionOpen = \App\Models\SubmissionSetting::isOpen(); @endphp
                    @if(auth()->user()->role == 'peserta')
                    @if($submissionOpen)
                    <a href="{{ route('film.create') }}"
                        style="background:#fff;border:1.5px solid #e6a800;color:#b87f00;border-radius:8px;padding:6px 14px;font-size:12px;font-weight:600;text-decoration:none;">
                        + Buat Submission Baru
                    </a>
                    @else
                    <span style="background:#f5f5f5;border:1.5px solid #ddd;color:#aaa;border-radius:8px;padding:6px 14px;font-size:12px;font-weight:600;cursor:not-allowed;">
                        <i class="fa fa-lock"></i> Submission Ditutup
                    </span>
                    @endif
                    @endif
                </div>

                @if(!auth()->user()->hasRole(['peserta', 'juri']))
                {{-- Filter Kategori --}}
                <div style="padding:12px 18px; border-bottom:0.5px solid #f0f0f0; display:flex; align-items:center; gap:10px;">
                    <label style="font-size:12px; color:#888; font-weight:600; white-space:nowrap;">Filter Kategori:</label>
                    <select id="filter-kategori"
                        style="padding:5px 10px; border:1px solid #ddd; border-radius:6px; font-size:12px; color:#555; outline:none; background:#fff; cursor:pointer;">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="table-responsive" style="overflow-x:auto;">
                    <table id="tabel-submission" class="table table-bordered table-striped" style="margin:0;font-size:13px;">
                        <thead style="background:#fafafa;">
                            <tr>
                                <th class="text-center" style="color:#888;font-size:11px;text-transform:uppercase;letter-spacing:.5px;font-weight:600;border-bottom:0.5px solid #efefef;">No</th>
                                <th class="text-center" style="color:#888;font-size:11px;text-transform:uppercase;letter-spacing:.5px;font-weight:600;border-bottom:0.5px solid #efefef;">Judul Film</th>
                                <th class="text-center" style="color:#888;font-size:11px;text-transform:uppercase;letter-spacing:.5px;font-weight:600;border-bottom:0.5px solid #efefef;">Kategori</th>
                                <th class="text-center" style="color:#888;font-size:11px;text-transform:uppercase;letter-spacing:.5px;font-weight:600;border-bottom:0.5px solid #efefef;">Durasi</th>
                                <th class="text-center" style="color:#888;font-size:11px;text-transform:uppercase;letter-spacing:.5px;font-weight:600;border-bottom:0.5px solid #efefef;">Tanggal Submit</th>
                                <th class="text-center" style="color:#888;font-size:11px;text-transform:uppercase;letter-spacing:.5px;font-weight:600;border-bottom:0.5px solid #efefef;">Status</th>
                                <th class="text-center" style="color:#888;font-size:11px;text-transform:uppercase;letter-spacing:.5px;font-weight:600;border-bottom:0.5px solid #efefef;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Baris diisi lewat AJAX (server-side DataTables), lihat script di bawah --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</section><!-- /.content -->
@endsection
@push('scripts')
<script src="https://code.highcharts.com/highcharts.js"></script>
<script>
    const targetOpen = new Date("{{ $setting->open_at->toIso8601String() }}").getTime();

    const timer = setInterval(function() {
        const now = new Date().getTime();
        const diff = targetOpen - now;

        if (diff <= 0) {
            clearInterval(timer);
            document.getElementById('countdown-open').innerHTML =
                '<div style="background:#d1fae5;color:#065f46;border-radius:6px;padding:6px 12px;font-size:12px;font-weight:600;">✓ Submission sudah dibuka! Silakan refresh halaman.</div>';
            return;
        }

        document.getElementById('cd-days').innerText = String(Math.floor(diff / (1000 * 60 * 60 * 24))).padStart(2, '0');
        document.getElementById('cd-hours').innerText = String(Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, '0');
        document.getElementById('cd-minutes').innerText = String(Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0');
        document.getElementById('cd-seconds').innerText = String(Math.floor((diff % (1000 * 60)) / 1000)).padStart(2, '0');
    }, 1000);
</script>

<script>
    const targetClose = new Date("{{ $setting->close_at->toIso8601String() }}").getTime();

    const timerClose = setInterval(function() {
        const now = new Date().getTime();
        const diff = targetClose - now;

        if (diff <= 0) {
            clearInterval(timerClose);
            document.getElementById('countdown-close').innerHTML =
                '<div style="background:#fde8e8;color:#a03030;border-radius:6px;padding:6px 12px;font-size:12px;font-weight:600;">🔒 Submission telah ditutup. Silakan refresh halaman.</div>';
            return;
        }

        document.getElementById('cc-days').innerText = String(Math.floor(diff / (1000 * 60 * 60 * 24))).padStart(2, '0');
        document.getElementById('cc-hours').innerText = String(Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, '0');
        document.getElementById('cc-minutes').innerText = String(Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0');
        document.getElementById('cc-seconds').innerText = String(Math.floor((diff % (1000 * 60)) / 1000)).padStart(2, '0');
    }, 1000);
</script>

<script>
    $(document).ready(function() {
        let categoryNameFilter = '';

        const table = $('#tabel-submission').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('dashboard.data') }}",
                data: function(d) {
                    d.category_name = categoryNameFilter;
                }
            },
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 data",
                infoFiltered: "(difilter dari _MAX_ total data)",
                zeroRecords: "Tidak ada data yang cocok",
                emptyTable: "Belum ada submission",
                processing: "Memuat data...",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya",
                },
            },
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            order: [
                [4, 'desc']
            ], // sort by tanggal submit
            columns: [{
                    data: 'no',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'judul',
                    orderable: false
                },
                {
                    data: 'kategori',
                    orderable: true
                },
                {
                    data: 'durasi',
                    orderable: true
                },
                {
                    data: 'tanggal',
                    orderable: true
                },
                {
                    data: 'status',
                    orderable: false
                },
                {
                    data: 'aksi',
                    orderable: false,
                    searchable: false
                },
            ],
        });

        // Filter berdasarkan kolom Kategori
        $('#filter-kategori').on('change', function() {
            categoryNameFilter = $(this).val();
            table.draw();
        });
    });
</script>
@endpush
@push('styles')
<style>
    #tabel-submission_wrapper .dataTables_length,
    #tabel-submission_wrapper .dataTables_filter {
        margin-bottom: 16px;
        padding: 0 4px;
    }

    #tabel-submission_wrapper .dataTables_info,
    #tabel-submission_wrapper .dataTables_paginate {
        margin-top: 16px;
        padding: 0 4px;
    }

    #tabel-submission_wrapper .dataTables_paginate {
        padding-bottom: 4px;
    }
</style>
@endpush