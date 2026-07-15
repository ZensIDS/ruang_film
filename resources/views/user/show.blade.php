@extends('layouts.master')
@section('container')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        Data <strong>{{ $users->name }}</strong>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><b>Detail Peserta</b></h3>
                    <div class="box-tools pull-right">
                        <a href="javascript:void(0);" onclick="history.back()" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="box-body">

                    <div style="display:flex; flex-direction:column; gap:15px;">

                        <div style="background:#fff; border:0.5px solid #e0e0e0; border-radius:10px; padding:16px;">

                            <div style="font-size:13px; font-weight:600; border-left:3px solid #1db9a0; padding-left:10px; margin-bottom:14px;">
                                Informasi Peserta
                            </div>

                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">

                                <div>
                                    <div style="font-size:11px; color:#888; text-transform:uppercase;">
                                        Peserta
                                    </div>
                                    <div style="font-size:14px; font-weight:500;">
                                        {{ $users->name }}
                                    </div>
                                </div>

                                <div>
                                    <div style="font-size:11px; color:#888; text-transform:uppercase;">
                                        Email
                                    </div>
                                    <div style="font-size:14px; font-weight:500;">
                                        {{ $users->email }}
                                    </div>
                                </div>

                                <div>
                                    <div style="font-size:11px; color:#888; text-transform:uppercase;">
                                        No WhatsApp
                                    </div>
                                    <div style="font-size:14px; font-weight:500;">
                                        {{ $users->no_hp }}
                                    </div>
                                </div>

                                <div>
                                    <div style="font-size:11px; color:#888; text-transform:uppercase;">
                                        Tanggal Lahir
                                    </div>
                                    <div style="font-size:14px; font-weight:500;">
                                        {{ optional($detail)->tanggal_lahir ? \Carbon\Carbon::parse($detail->tanggal_lahir)->translatedFormat('d F Y') : '-' }}
                                    </div>
                                </div>

                                <div>
                                    <div style="font-size:11px; color:#888; text-transform:uppercase;">
                                        Nama Komunitas
                                    </div>
                                    <div style="font-size:14px; font-weight:500;">
                                        {{ optional($detail)->community_name ?? '-' }}
                                    </div>
                                </div>

                                <div>
                                    <div style="font-size:11px; color:#888; text-transform:uppercase;">
                                        Posisi / Jabatan
                                    </div>
                                    <div style="font-size:14px; font-weight:500;">
                                        {{ optional($detail)->posisi ?? '-' }}
                                    </div>
                                </div>

                                <div>
                                    <div style="font-size:11px; color:#888; text-transform:uppercase;">
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
                                    <div style="font-size:11px; color:#888; text-transform:uppercase;">
                                        Provinsi
                                    </div>
                                    <div style="font-size:14px; font-weight:500;">
                                        {{ optional($detail)->provinsi_name ?? '-' }}
                                    </div>
                                </div>

                                <div>
                                    <div style="font-size:11px; color:#888; text-transform:uppercase;">
                                        Kabupaten / Kota
                                    </div>
                                    <div style="font-size:14px; font-weight:500;">
                                        {{ optional($detail)->kabupaten_name ?? '-' }}
                                    </div>
                                </div>

                                <div>
                                    <div style="font-size:11px; color:#888; text-transform:uppercase;">
                                        Kecamatan
                                    </div>
                                    <div style="font-size:14px; font-weight:500;">
                                        {{ optional($detail)->kecamatan_name ?? '-' }}
                                    </div>
                                </div>

                                <div>
                                    <div style="font-size:11px; color:#888; text-transform:uppercase;">
                                        Desa / Kelurahan
                                    </div>
                                    <div style="font-size:14px; font-weight:500;">
                                        {{ optional($detail)->desa_name ?? '-' }}
                                    </div>
                                </div>

                            </div>

                            <hr style="border:none; border-top:0.5px solid #f0f0f0; margin:14px 0;">

                            <div>
                                <div style="font-size:11px; color:#888; text-transform:uppercase; margin-bottom:8px;">
                                    Alamat Lengkap
                                </div>

                                <div style="font-size:13px; color:#444; line-height:1.7; padding:10px 12px; background:#f8f9fa; border-radius:8px;">
                                    {{ optional($detail)->alamat_lengkap ?? '-' }}
                                </div>
                            </div>

                        </div>

                        <div style="background:#fff; border:0.5px solid #e0e0e0; border-radius:10px; padding:16px;">

                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                                <div style="font-size:13px; font-weight:600; border-left:3px solid #1db9a0; padding-left:10px;">
                                    Submission Film
                                </div>

                                <span class="label label-primary">
                                    {{ $films->count() }} Submission
                                </span>
                            </div>

                            <div class="table-responsive">
                                <table id="tabel-film-user" class="table table-bordered table-striped">

                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Judul Film</th>
                                            <th>Kategori</th>
                                            <th>Durasi</th>
                                            <th>Tanggal Submit</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        @foreach($films as $film)

                                        <tr>

                                            <td>{{ $loop->iteration }}</td>

                                            {{-- Judul Film + Poster --}}
                                            <td>
                                                <div style="display:flex; align-items:center; gap:10px;">

                                                    @if($film->poster)
                                                    <img src="{{ $film->poster_url }}"
                                                        style="
                                                            width:72px;
                                                            height:96px;
                                                            object-fit:cover;
                                                            border-radius:4px;
                                                            flex-shrink:0;
                                                            border:1px solid #ddd;
                                                        ">
                                                    @else
                                                    <div style="
                                                        width:72px;
                                                        height:96px;
                                                        background:#eee;
                                                        border-radius:4px;
                                                        display:flex;
                                                        align-items:center;
                                                        justify-content:center;
                                                        font-size:10px;
                                                        color:#aaa;
                                                        flex-shrink:0;
                                                        border:1px solid #ddd;
                                                    ">
                                                        N/A
                                                    </div>
                                                    @endif

                                                    <div>
                                                        <div style="font-weight:600;">
                                                            {{ $film->name }}
                                                        </div>

                                                        @if($film->sutradara)
                                                        <small class="text-muted">
                                                            Sutradara : {{ $film->sutradara }}
                                                        </small>
                                                        @endif
                                                    </div>

                                                </div>
                                            </td>

                                            <td style="vertical-align: middle; text-align:center;">
                                                {{ $film->category->name ?? '-' }}
                                            </td>

                                            <td style="vertical-align: middle; text-align:center;">

                                                @php
                                                $jam = floor($film->duration / 3600);
                                                $menit = floor(($film->duration % 3600) / 60);
                                                $detik = $film->duration % 60;
                                                @endphp

                                                {{ sprintf('%02d:%02d:%02d', $jam, $menit, $detik) }}

                                            </td>

                                            <td style="vertical-align: middle; text-align:center;">
                                                {{ $film->created_at->format('d M Y') }}
                                            </td>

                                            {{-- Status --}}
                                            <td style="vertical-align: middle; text-align:center;">
                                                @php $s = $film->status_badge; @endphp

                                                <span style="
                                                    background:{{ $s['bg'] }};
                                                    color:{{ $s['color'] }};
                                                    padding:3px 10px;
                                                    border-radius:20px;
                                                    font-size:11px;
                                                    font-weight:600;
                                                    white-space:nowrap;
                                                ">
                                                    {{ $s['label'] }}
                                                </span>
                                            </td>

                                            <td style="vertical-align: middle; text-align:center;">
                                                <a href="{{ route('film.show', $film->id) }}"
                                                    class="btn btn-xs btn-primary">
                                                    Detail
                                                </a>
                                            </td>

                                        </tr>

                                        @endforeach

                                    </tbody>

                                </table>
                            </div>

                        </div>

                    </div>

                </div>
            </div><!-- /.box -->
        </div><!-- /.col -->
    </div><!-- /.row -->
</section><!-- /.content -->
@endsection

@push('scripts')
<script>
    $(document).ready(function() {

        $('#tabel-film-user').DataTable({
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 data",
                infoFiltered: "(difilter dari _MAX_ total data)",
                zeroRecords: "Tidak ada data yang cocok",
                emptyTable: "Peserta belum pernah mengirim film",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                }
            },
            pageLength: 10
        });

    });
</script>
@endpush
