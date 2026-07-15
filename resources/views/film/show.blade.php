@extends('layouts.master')
@section('container')
@php
$detail = $film->user->detail;
$viewer = auth()->user();
$participantViewer = $viewer && $viewer->hasRole('peserta');
$displayStatus = $film->display_status;
$s = $film->statusBadgeFor($viewer);

if ($participantViewer) {
    $timelineSteps = [
        1 => 'Submitted',
        2 => 'Verified',
        3 => 'Under Review',
        4 => $film->winner_rank_label ?: ($displayStatus === \App\Models\Film::CURATION_REJECTED ? 'Tidak Lolos' : 'Official Selection'),
    ];
    $currentStepMap = [
        \App\Models\Film::CURATION_SUBMITTED => 1,
        \App\Models\Film::CURATION_VERIFIED => 2,
        \App\Models\Film::CURATION_UNDER_REVIEW => 3,
        \App\Models\Film::CURATION_DETERMINATION => 3,
        \App\Models\Film::CURATION_APPROVED => 4,
        \App\Models\Film::CURATION_REJECTED => 4,
        'winner' => 4,
    ];
} else {
    $timelineSteps = [
        1 => 'Submitted',
        2 => 'Verified',
        3 => 'Under Review',
        4 => 'Shortlist',
        5 => $film->winner_rank_label ?: ($displayStatus === \App\Models\Film::CURATION_REJECTED ? 'Tidak Lolos' : 'Official Selection'),
    ];
    $currentStepMap = [
        \App\Models\Film::CURATION_SUBMITTED => 1,
        \App\Models\Film::CURATION_VERIFIED => 2,
        \App\Models\Film::CURATION_UNDER_REVIEW => 3,
        \App\Models\Film::CURATION_DETERMINATION => 4,
        \App\Models\Film::CURATION_APPROVED => 5,
        \App\Models\Film::CURATION_REJECTED => 5,
        'winner' => 5,
    ];
}

$currentStep = $currentStepMap[$displayStatus] ?? 1;
@endphp
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><b>Detail Film</b></h3>
                    <div class="box-tools pull-right">
                        <a href="javascript:void(0);" onclick="history.back()" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="box-body">

                    <div style="display:grid; grid-template-columns: 2fr 1fr; gap:16px;">

                        {{-- ======== KOLOM KIRI ======== --}}
                        <div style="display:flex; flex-direction:column; gap:14px;">

                            {{-- Header Film --}}
                            <div style="background:#fff; border:0.5px solid #e0e0e0; border-radius:10px; padding:16px; display:flex; gap:16px; align-items:flex-start;">
                                <div style="width:80px; height:110px; border-radius:8px; overflow:hidden; flex-shrink:0; border:0.5px solid #e0e0e0;">
                                    @if($film->poster)
                                    <img src="{{ $film->poster_url }}" style="width:100%; height:100%; object-fit:cover;">
                                    @else
                                    <div style="width:100%; height:100%; background:#f5f5f5; display:flex; align-items:center; justify-content:center; font-size:11px; color:#aaa;">No Poster</div>
                                    @endif
                                </div>
                                <div style="flex:1; min-width:0;">
                                    <div style="font-size:20px; font-weight:600; color:#222; margin-bottom:4px;">{{ $film->name }}</div>
                                    <div style="font-size:13px; color:#888; margin-bottom:10px;">
                                        Kategori : {{ $film->category->name ?? '-' }}
                                        @if($film->submissionSetting)
                                        <span style="margin-left:8px;">• Periode: {{ $film->submissionSetting->name }}</span>
                                        @endif
                                    </div>
                                    <div style="margin-bottom:10px;">
                                        <span style="background:{{ $s['bg'] }}; color:{{ $s['color'] }}; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600;">
                                            {{ $s['label'] }}
                                        </span>
                                        <span style="font-size:12px; color:#888; margin-left:6px;">
                                            <i class="fa fa-clock-o"></i>
                                            Disubmit {{ \Carbon\Carbon::parse($film->created_at)->translatedFormat('d F Y, H:i') }} WIB
                                        </span>
                                    </div>
                                    <div style="display:flex; flex-wrap:wrap; font-size:12px; color:#666; gap:4px 12px;">
                                        <span style="flex-basis:100%; flex-shrink:0;" class="film-meta-item">
                                            <i class="fa fa-user"></i> Sutradara: <strong>{{ $film->sutradara }}</strong>
                                        </span>
                                        @php
                                        $jam = floor($film->duration / 3600);
                                        $menit = floor(($film->duration % 3600) / 60);
                                        $sisa = $film->duration % 60;
                                        @endphp
                                        <span class="film-meta-sep hidden-xs">·</span>
                                        <span style="flex-basis:100%; flex-shrink:0;" class="film-meta-item">
                                            <i class="fa fa-clock-o"></i> Durasi: <strong>{{ sprintf('%02d:%02d:%02d', $jam, $menit, $sisa) }}</strong>
                                        </span>
                                        <span class="film-meta-sep hidden-xs">·</span>
                                        <span style="flex-basis:100%; flex-shrink:0;" class="film-meta-item">
                                            Subtitle: <strong>{{ $film->subtitle }}</strong>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- ======== KOLOM KANAN (tampil di mobile, sebelum Informasi Peserta) ======== --}}
                            <div class="visible-xs visible-sm" style="display:flex; flex-direction:column; gap:14px;">
                                {{-- Poster --}}
                                <div style="background:#fff; border:0.5px solid #e0e0e0; border-radius:10px; overflow:hidden;">
                                    @if($film->poster)
                                    <img src="{{ $film->poster_url }}" style="width:100%; display:block; object-fit:cover;">
                                    @else
                                    <div style="width:100%; aspect-ratio:2/3; background:#f5f5f5; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:6px;">
                                        <i class="fa fa-image" style="font-size:28px; color:#ccc;"></i>
                                        <span style="font-size:12px; color:#aaa;">Tidak ada poster</span>
                                    </div>
                                    @endif
                                </div>

                                {{-- Status Timeline --}}
                                <div style="background:#fff; border:0.5px solid #e0e0e0; border-radius:10px; padding:16px;">
                                    <div style="font-size:13px; font-weight:600; border-left:3px solid #1db9a0; padding-left:10px; margin-bottom:14px;">Status submission</div>
                                    <div style="display:flex; flex-direction:column; gap:8px;">
                                        @foreach($timelineSteps as $step => $label)
                                        @php
                                        $isActive = $currentStep == $step;
                                        $isPast = $currentStep > $step;
                                        $opacity = ($isActive || $isPast) ? '1' : '0.35';
                                        $dotColor = $isActive ? '#e6a800' : ($isPast ? '#1db9a0' : '#ccc');
                                        @endphp
                                        <div style="display:flex; align-items:center; gap:8px; font-size:12px; opacity:{{ $opacity }};">
                                            <div style="width:8px; height:8px; border-radius:50%; background:{{ $dotColor }}; flex-shrink:0;"></div>
                                            <span style="color:#333; {{ $isActive ? 'font-weight:600;' : '' }}">{{ $label }}</span>
                                            @if($isActive)
                                            <span style="margin-left:auto; font-size:11px; color:#888;">Saat ini</span>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            {{-- ======== END KOLOM KANAN MOBILE ======== --}}

                            {{-- Informasi Detail User --}}
                            <div style="background:#fff; border:0.5px solid #e0e0e0; border-radius:10px; padding:16px; margin-top:15px;">

                                <div style="font-size:13px; font-weight:600; border-left:3px solid #1db9a0; padding-left:10px; margin-bottom:14px;">
                                    Informasi Peserta
                                </div>

                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">

                                    <div>
                                        <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px;">
                                            Peserta
                                        </div>
                                        <div style="font-size:14px; font-weight:500;">
                                            {{ $film->user->name }}
                                        </div>
                                    </div>

                                    <div>
                                        <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px;">
                                            Email
                                        </div>
                                        <div style="font-size:14px; font-weight:500;">
                                            {{ $film->user->email }}
                                        </div>
                                    </div>

                                    <div>
                                        <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px;">
                                            No WhatsApp
                                        </div>
                                        <div style="font-size:14px; font-weight:500;">
                                            @php
                                            $noWa = preg_replace('/[^0-9]/', '', $film->user->no_hp);
                                            if (str_starts_with($noWa, '0')) {
                                            $noWa = '62' . substr($noWa, 1);
                                            }
                                            @endphp
                                            <a href="https://wa.me/{{ $noWa }}" target="_blank"
                                                style="color:#25d366; text-decoration:none; display:inline-flex; align-items:center; gap:5px;">
                                                <i class="fa fa-whatsapp"></i>
                                                {{ $film->user->no_hp }}
                                            </a>
                                        </div>
                                    </div>

                                    <div>
                                        <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px;">
                                            Tanggal Lahir
                                        </div>
                                        <div style="font-size:14px; font-weight:500;">
                                            {{ optional($detail)->tanggal_lahir ? \Carbon\Carbon::parse($detail->tanggal_lahir)->translatedFormat('d F Y') : '-' }}
                                        </div>
                                    </div>

                                    <div>
                                        <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px;">
                                            Nama Komunitas
                                        </div>
                                        <div style="font-size:14px; font-weight:500;">
                                            {{ optional($detail)->community_name ?? '-' }}
                                        </div>
                                    </div>

                                    <div>
                                        <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px;">
                                            Posisi / Jabatan
                                        </div>
                                        <div style="font-size:14px; font-weight:500;">
                                            {{ optional($detail)->posisi ?? '-' }}
                                        </div>
                                    </div>

                                    <div>
                                        <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px;">
                                            Username Instagram
                                        </div>
                                        <div style="font-size:14px; font-weight:500;">
                                            {{ optional($detail)->username_ig ? '@'.$detail->username_ig : '-' }}
                                        </div>
                                    </div>

                                </div>

                                <hr style="border:none; border-top:0.5px solid #f0f0f0; margin:14px 0;">

                                <div style="font-size:13px; font-weight:600; border-left:3px solid #1db9a0; padding-left:10px; margin-bottom:14px;">
                                    Alamat
                                </div>

                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">

                                    <div>
                                        <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px;">
                                            Provinsi
                                        </div>
                                        <div style="font-size:14px; font-weight:500;">
                                            {{ optional($detail)->provinsi_name ?? '-' }}
                                        </div>
                                    </div>

                                    <div>
                                        <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px;">
                                            Kabupaten / Kota
                                        </div>
                                        <div style="font-size:14px; font-weight:500;">
                                            {{ optional($detail)->kabupaten_name ?? '-' }}
                                        </div>
                                    </div>

                                    <div>
                                        <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px;">
                                            Kecamatan
                                        </div>
                                        <div style="font-size:14px; font-weight:500;">
                                            {{ optional($detail)->kecamatan_name ?? '-' }}
                                        </div>
                                    </div>

                                    <div>
                                        <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px;">
                                            Desa / Kelurahan
                                        </div>
                                        <div style="font-size:14px; font-weight:500;">
                                            {{ optional($detail)->desa_name ?? '-' }}
                                        </div>
                                    </div>

                                </div>

                                <hr style="border:none; border-top:0.5px solid #f0f0f0; margin:14px 0;">

                                <div>
                                    <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.5px; margin-bottom:8px;">
                                        Alamat Lengkap
                                    </div>

                                    <div style="font-size:13px; color:#444; line-height:1.7; padding:10px 12px; background:#f8f9fa; border-radius:8px;">
                                        {{ optional($detail)->alamat_lengkap ?? '-' }}
                                    </div>
                                </div>

                            </div>

                            {{-- Informasi Film --}}
                            <div style="background:#fff; border:0.5px solid #e0e0e0; border-radius:10px; padding:16px;">
                                <div style="font-size:13px; font-weight:600; border-left:3px solid #1db9a0; padding-left:10px; margin-bottom:14px;">Informasi film</div>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                                    <div>
                                        <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px;">Judul film</div>
                                        <div style="font-size:14px; font-weight:500;">{{ $film->name }}</div>
                                    </div>
                                    <div>
                                        <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px;">Kategori</div>
                                        <div style="font-size:14px; font-weight:500;">{{ $film->category->name ?? '-' }}</div>
                                    </div>
                                    <div>
                                        <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px;">Durasi</div>
                                        <div style="font-size:14px; font-weight:500;">{{ sprintf('%02d:%02d:%02d', $jam, $menit, $sisa) }}</div>
                                    </div>
                                    <div>
                                        <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px;">Tahun produksi</div>
                                        <div style="font-size:14px; font-weight:500;">{{ $film->tahun_produksi }}</div>
                                    </div>
                                    <div>
                                        <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px;">Subtitle</div>
                                        <div style="font-size:14px; font-weight:500;">{{ $film->subtitle }}</div>
                                    </div>
                                </div>
                                <hr style="border:none; border-top:0.5px solid #f0f0f0; margin:14px 0;">
                                <div>
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                        <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.5px;">Sinopsis</div>

                                        <button type="button" onclick="downloadSinopsisPDF()" class="btn btn-default btn-xs" style="font-size: 11px; padding: 2px 8px;">
                                            <i class="fa fa-file-pdf-o"></i> Download .pdf
                                        </button>
                                    </div>

                                    <div id="sinopsisText" style="text-align:justify;font-size:13px; color:#444; line-height:1.7; padding:10px 12px; background:#f8f9fa; border-radius:8px;">
                                        {{ $film->sinopsis }}
                                    </div>
                                </div>
                            </div>

                            {{-- Kru Film --}}
                            <div style="background:#fff; border:0.5px solid #e0e0e0; border-radius:10px; padding:16px;">
                                <div style="font-size:13px; font-weight:600; border-left:3px solid #1db9a0; padding-left:10px; margin-bottom:14px;">Kru film</div>
                                <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:14px;">
                                    <div>
                                        <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px;">Sutradara</div>
                                        <div style="font-size:14px; font-weight:500;">{{ $film->sutradara }}</div>
                                    </div>
                                    <div>
                                        <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px;">Produser</div>
                                        <div style="font-size:14px; font-weight:500;">{{ $film->produser }}</div>
                                    </div>
                                    <div>
                                        <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px;">Penulis skenario</div>
                                        <div style="font-size:14px; font-weight:500;">{{ $film->penulis ?? '—' }}</div>
                                    </div>
                                    <div>
                                        <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px;">Daftar Kru Film</div>
                                        <div style="font-size:14px; font-weight:500;"><a href="{{ $film->kru_url }}" target="_blank"
                                                style="font-size:13px; color:#0d6efd; display:inline-flex; align-items:center; gap:4px; text-decoration:none;">
                                                <i class="fa fa-file"></i> Buka Daftar Kru <i class="fa fa-external-link" style="font-size:11px;"></i>
                                            </a></div>
                                    </div>
                                </div>
                            </div>

                            {{-- File & Tautan --}}
                            <div style="background:#fff; border:0.5px solid #e0e0e0; border-radius:10px; padding:16px;">
                                <div style="font-size:13px; font-weight:600; border-left:3px solid #1db9a0; padding-left:10px; margin-bottom:14px;">File & tautan</div>
                                {{-- GSM Multiple --}}
                                <div style="margin-bottom:14px;">
                                    @php
                                    $gsmFiles = $film->gsm_files;
                                    $gsmUrls = $film->gsm_urls;
                                    @endphp
                                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                                        <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.5px;">
                                            Grab Still Photo ({{ count($gsmFiles) }} file)
                                        </div>
                                        @if(count($gsmFiles) > 0)
                                        <a href="{{ route('film.gsm.download', $film->id) }}"
                                            style="display:inline-flex; align-items:center; gap:5px; padding:4px 10px; background:#f0faf7; border:0.5px solid #1db9a0; border-radius:6px; font-size:11px; font-weight:600; color:#0a7a5e; text-decoration:none;">
                                            <i class="fa fa-download"></i> Download Semua (.zip)
                                        </a>
                                        @endif
                                    </div>

                                <div style="display:grid; grid-template-columns:repeat(5, 1fr); gap:6px;">
                                        @foreach($gsmFiles as $i => $path)
                                        <a href="{{ $gsmUrls[$i] ?? $film->mediaUrl($path) }}" target="_blank"
                                            style="display:block; aspect-ratio:1/1; border-radius:8px; overflow:hidden; border:0.5px solid #e0e0e0; position:relative; background:#f5f5f5;">
                                            <img src="{{ $gsmUrls[$i] ?? $film->mediaUrl($path) }}"
                                                style="width:100%; height:100%; object-fit:cover; display:block;">
                                            <div style="position:absolute; bottom:0; left:0; right:0; background:rgba(0,0,0,0.45); color:#fff; font-size:10px; text-align:center; padding:3px 0;">
                                                GSM {{ $i + 1 }}
                                            </div>
                                        </a>
                                        @endforeach

                                        @for($j = count($gsmFiles); $j < 5; $j++)
                                            <div style="aspect-ratio:1/1; border-radius:8px; border:0.5px dashed #ddd; background:#fafafa; display:flex; align-items:center; justify-content:center;">
                                            <i class="fa fa-image" style="font-size:18px; color:#ddd;"></i>
                                    </div>
                                    @endfor
                                    </div>
                                </div>

                            @if($film->other_2)
                            <div style="margin-bottom:14px;">
                                <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.5px; margin-bottom:8px;">
                                    Screenshot Bukti Unggah / Repost
                                </div>
                                @php
                                $other2IsImage = \Illuminate\Support\Str::endsWith(strtolower((string) $film->other_2), ['.jpg', '.jpeg', '.png', '.gif', '.webp']);
                                @endphp
                                @if($other2IsImage)
                                <a href="{{ $film->other_2_url }}" target="_blank"
                                    style="display:inline-block; border-radius:8px; overflow:hidden; border:0.5px solid #e0e0e0; background:#f5f5f5;">
                                    <img src="{{ $film->other_2_url }}" style="width:220px; max-width:100%; display:block; object-fit:cover;">
                                </a>
                                @else
                                <a href="{{ $film->other_2_url }}" target="_blank"
                                    style="display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border:0.5px solid #e0e0e0; border-radius:8px; background:#fafafa; font-size:13px; color:#333; text-decoration:none;">
                                    <i class="fa fa-file-image-o" style="color:#1db9a0;"></i>
                                    Lihat file
                                    <i class="fa fa-download" style="margin-left:auto; color:#aaa;"></i>
                                </a>
                                @endif
                            </div>
                            @endif

                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                                <div>
                                    <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.5px; margin-bottom:6px;">Link trailer</div>
                                    <a href="{{ $film->trailer }}" target="_blank"
                                        style="font-size:13px; color:#0d6efd; display:inline-flex; align-items:center; gap:4px; text-decoration:none;">
                                        <i class="fa fa-play-circle"></i> Buka Trailer <i class="fa fa-external-link" style="font-size:11px;"></i>
                                    </a>
                                </div>
                                <div>
                                    <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.5px; margin-bottom:6px;">Link file film</div>
                                    <a href="{{ $film->film }}" target="_blank"
                                        style="font-size:13px; color:#0d6efd; display:inline-flex; align-items:center; gap:4px; text-decoration:none;">
                                        <i class="fa fa-film"></i> Buka File Film <i class="fa fa-external-link" style="font-size:11px;"></i>
                                    </a>
                                </div>
                            </div>

                            {{-- Other (Press Kit / Surat Rekomendasi) --}}
                                    @if($film->other_1)
                            <hr style="border:none; border-top:0.5px solid #f0f0f0; margin:14px 0;">
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                @if($film->other_1)
                                <div>
                                    <div style="font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.5px; margin-bottom:8px;">
                                        @if($film->category_id == 1) Press Kit
                                        @elseif(in_array($film->category_id, [2,4])) Surat Rekomendasi Sekolah
                                        @endif
                                    </div>
                                    <a href="{{ $film->other_1_url }}" target="_blank"
                                        style="display:flex; align-items:center; gap:8px; padding:8px 12px; border:0.5px solid #e0e0e0; border-radius:8px; background:#fafafa; font-size:13px; color:#333; text-decoration:none;">
                                        <i class="fa fa-file" style="color:#1db9a0;"></i>
                                        Lihat file
                                        <i class="fa fa-download" style="margin-left:auto; color:#aaa;"></i>
                                    </a>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>

                    </div>

                    {{-- ======== KOLOM KANAN (hanya tampil di desktop) ======== --}}
                    <div class="hidden-xs hidden-sm" style="display:flex; flex-direction:column; gap:14px;">

                        {{-- Poster --}}
                        <div style="background:#fff; border:0.5px solid #e0e0e0; border-radius:10px; overflow:hidden;">
                            @if($film->poster)
                            <img src="{{ $film->poster_url }}" style="width:100%; display:block; object-fit:cover;">
                            @else
                            <div style="width:100%; aspect-ratio:2/3; background:#f5f5f5; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:6px;">
                                <i class="fa fa-image" style="font-size:28px; color:#ccc;"></i>
                                <span style="font-size:12px; color:#aaa;">Tidak ada poster</span>
                            </div>
                            @endif
                        </div>

                        {{-- Status Timeline --}}
                        <div style="background:#fff; border:0.5px solid #e0e0e0; border-radius:10px; padding:16px;">
                            <div style="font-size:13px; font-weight:600; border-left:3px solid #1db9a0; padding-left:10px; margin-bottom:14px;">Status submission</div>
                            <div style="display:flex; flex-direction:column; gap:8px;">
                                @foreach($timelineSteps as $step => $label)
                                @php
                                $isActive = $currentStep == $step;
                                $isPast = $currentStep > $step;
                                $opacity = ($isActive || $isPast) ? '1' : '0.35';
                                $dotColor = $isActive ? '#e6a800' : ($isPast ? '#1db9a0' : '#ccc');
                                @endphp
                                <div style="display:flex; align-items:center; gap:8px; font-size:12px; opacity:{{ $opacity }};">
                                    <div style="width:8px; height:8px; border-radius:50%; background:{{ $dotColor }}; flex-shrink:0;"></div>
                                    <span style="color:#333; {{ $isActive ? 'font-weight:600;' : '' }}">{{ $label }}</span>
                                    @if($isActive)
                                    <span style="margin-left:auto; font-size:11px; color:#888;">Saat ini</span>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>

                @if(auth()->user()->hasRole(['admin', 'adminsub', 'kurator', 'juri']))
                @include('review.partials.film-review-breakdown', ['film' => $film, 'reviewStageLabels' => $reviewStageLabels ?? \App\Models\ReviewRubric::stageLabels()])
                @endif

            </div><!-- /.box-body -->
        </div>
    </div>
    </div>
</section>
@endsection
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
    function downloadSinopsisPDF() {
        // 1. Inisialisasi jsPDF (Ukuran A4, satuan milimeter)
        const {
            jsPDF
        } = window.jspdf;
        const doc = new jsPDF({
            orientation: 'portrait',
            unit: 'mm',
            format: 'a4'
        });

        // 2. Ambil teks sinopsis murni dan nama film
        const sinopsisText = document.getElementById('sinopsisText').innerText;
        const judulFilm = "{{ $film->name ?? 'Detail Film' }}";
        const namaFile = "{{ Str::slug($film->name ?? 'sinopsis-film') }}";

        // 3. Atur Font untuk Judul
        doc.setFont("helvetica", "bold");
        doc.setFontSize(16);
        doc.setTextColor(45, 27, 105); // Warna Ungu (#2D1B69) seperti tema kamu
        doc.text(`Sinopsis Film: ${judulFilm}`, 15, 20); // Posisi X: 15mm, Y: 20mm

        // 4. Atur Font untuk Isi Teks Sinopsis
        doc.setFont("helvetica", "normal");
        doc.setFontSize(11);
        doc.setTextColor(68, 68, 68); // Warna Abu Gelap (#444)

        // 5. FITUR PENTING: Bungkus teks otomatis agar tidak nabrak keluar kertas A4
        // Lebar kertas A4 adalah 210mm. Dikurangi margin kiri-kanan (15mm * 2 = 30mm), sisa lebar teks = 180mm.
        const splitText = doc.splitTextToSize(sinopsisText, 180);

        // 6. Cetak teks yang sudah rapi tersebut ke PDF
        doc.text(splitText, 15, 32); // Posisi X: 15mm, Y: 32mm (di bawah judul)

        // 7. Simpan file PDF secara instan
        doc.save(`${namaFile}-sinopsis.pdf`);
    }
</script>
@endpush
@push('styles')
<style>
    @media (max-width: 768px) {
        .box-body>div[style*="grid-template-columns: 2fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
    }

    /* Desktop: semua dalam satu baris */
    @media (min-width: 769px) {
        .film-meta-item {
            flex-basis: auto !important;
        }

        .film-meta-sep {
            display: inline !important;
        }
    }

    /* Mobile: masing-masing baris baru, titik separator hilang */
    @media (max-width: 768px) {
        .film-meta-item {
            flex-basis: 100% !important;
        }
    }
</style>
@endpush
