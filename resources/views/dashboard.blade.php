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
            <div style="background:#f0f4ff; border:1px solid #c5d3f5; border-radius:10px; padding:14px 16px; display:flex; align-items:center; gap:12px;">
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
                    <div style="font-size:16px;font-weight:700;color:#7a1a1a;">
                        {{ $setting->close_at->translatedFormat('d F Y') }}
                    </div>
                    <div style="font-size:11px;color:#a03030;">
                        {{ $setting->close_at->format('H:i') }} WIB
                    </div>
                    <div style="margin-top:6px;background:#fde8e8;color:#a03030;border-radius:6px;padding:4px 12px;font-size:11px;font-weight:600;display:inline-block;">
                        Ditutup {{ $setting->close_at->diffForHumans() }}
                    </div>
                    @endif
                </div>
            </div>
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
                            @forelse($submissions as $item)
                            <tr>
                                <td class="text-center" style="vertical-align:middle;">{{ $loop->iteration }}</td>

                                <td style="vertical-align:middle;">
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        @if($item->poster)
                                        <img src="{{ $item->poster_url }}"
                                            style="width:80px;height:104px;border-radius:5px;object-fit:cover;flex-shrink:0;">
                                        @else
                                        <div style="width:80px;height:104px;border-radius:5px;background:#ddd;display:flex;align-items:center;justify-content:center;font-size:10px;color:#999;flex-shrink:0;">N/A</div>
                                        @endif
                                        <div>
                                            <div style="font-weight:600;">{{ $item->name }}</div>
                                            <div style="color:#888;font-size:11px;">Peserta: {{ $item->user->name ?? '-' }}</div>
                                            <div style="color:#aaa;font-size:11px;">Sutradara: {{ $item->sutradara }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td style="vertical-align:middle;">{{ $item->category->name ?? '-' }}</td>

                                <td style="vertical-align:middle;">
                                    @php
                                    $jam = floor($item->duration / 3600);
                                    $menit = floor(($item->duration % 3600) / 60);
                                    $sisa = $item->duration % 60;
                                    @endphp
                                    {{ sprintf('%02d:%02d:%02d', $jam, $menit, $sisa) }}
                                </td>

                                <td style="vertical-align:middle;" data-order="{{ optional($item->created_at)->timestamp ?? 0 }}">
                                    <div>{{ \Carbon\Carbon::parse($item->created_at)->format('d M Y') }}</div>
                                    <div style="color:#aaa;font-size:11px;">{{ \Carbon\Carbon::parse($item->created_at)->format('H:i') }} WIB</div>
                                </td>

                                <td style="vertical-align:middle;">
                                    @php $s = $item->statusBadgeFor(auth()->user()); @endphp
                                    <span style="background:{{ $s['bg'] }};color:{{ $s['color'] }};padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;white-space:nowrap;">
                                        {{ $s['label'] }}
                                    </span>
                                </td>

                                <td style="vertical-align:middle;">
                                    <a href="{{ route('film.show', $item->id) }}"
                                        style="border:1px solid #ddd;background:#fff;color:#555;border-radius:6px;padding:5px 11px;font-size:12px;text-decoration:none;display:inline-flex;align-items:center;gap:3px;">
                                        Lihat Detail ›
                                    </a>
                                </td>
                            </tr>
                            @empty
                            @endforelse
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
        const table = $('#tabel-submission').DataTable({
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 data",
                infoFiltered: "(difilter dari _MAX_ total data)",
                zeroRecords: "Tidak ada data yang cocok",
                emptyTable: "Belum ada submission",
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
            columnDefs: [{
                orderable: false,
                targets: [0, 1, 6]
            }, ],
        });

        table.on('order.dt search.dt draw.dt', function() {
            const start = table.page.info().start;

            table.column(0, {
                search: 'applied',
                order: 'applied',
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = start + i + 1;
            });
        });

        table.draw(false);

        // Filter berdasarkan kolom Kategori (index 2)
        $('#filter-kategori').on('change', function() {
            const val = $(this).val();
            table.column(2).search(val).draw();

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
