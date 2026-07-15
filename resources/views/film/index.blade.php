@extends('layouts.master')
@section('container')
@php
    $isSubmissionAdmin = auth()->user()->hasRole(['admin', 'adminsub']);
@endphp
<section class="content-header">
    <h1>Submission</h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
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

                {{-- Filter --}}
                <div style="padding:10px 15px; border-bottom:1px solid #f0f0f0; display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                    @if($isSubmissionAdmin)
                    <form method="GET" action="{{ route('film.index') }}" class="form-inline">
                        <div class="form-group">
                            <label>Periode</label>
                            <select name="submission_setting_id" class="form-control" style="margin:0 10px;">
                                <option value="">Semua Periode</option>
                                @foreach($submissionPeriods as $period)
                                <option value="{{ $period->id }}" {{ (string) $selectedSubmissionSettingId === (string) $period->id ? 'selected' : '' }}>
                                    {{ $period->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Kategori</label>
                            <select name="category_id" class="form-control" style="margin:0 10px;">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ (string) $selectedCategoryId === (string) $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="curation_status" class="form-control" style="margin:0 10px;">
                                <option value="">Semua Status</option>
                                @foreach($statusLabels as $statusValue => $statusLabel)
                                <option value="{{ $statusValue }}" {{ $selectedCurationStatus === $statusValue ? 'selected' : '' }}>
                                    {{ $statusLabel }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('film.index') }}" class="btn btn-default" style="margin-left:8px;">Reset</a>
                    </form>
                    @else
                    <label style="font-size:12px; color:#888; font-weight:600; white-space:nowrap; margin:0;">
                        Filter Kategori:
                    </label>
                    <select id="filter-kategori"
                        style="padding:5px 10px; border:1px solid #ddd; border-radius:6px; font-size:12px; color:#555; outline:none; background:#fff; cursor:pointer;">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @endif
                </div>

                <div class="box-body table-responsive">
                    <table id="example4" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Judul Film</th>
                                <th>Kategori</th>
                                <th>Durasi</th>
                                <th>Tanggal Submit</th>
                                <th>Status</th>
                                <th>Peserta</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($films as $index => $film)
                            <tr>
                                <td>{{ $index + 1 }}</td>

                                {{-- Judul Film + Poster --}}
                                <td>
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        @if($film->poster)
                                        <img src="{{ $film->poster_url }}"
                                            style="width:72px; height:96px; object-fit:cover; border-radius:4px; flex-shrink:0; border:1px solid #ddd;">
                                        @else
                                        <div style="width:36px; height:48px; background:#eee; border-radius:4px; display:flex; align-items:center; justify-content:center; font-size:10px; color:#aaa; flex-shrink:0;">
                                            N/A
                                        </div>
                                        @endif
                                        <div>
                                            <div style="font-weight:600;">{{ $film->name }}</div>
                                            @if($film->sutradara)
                                            <small class="text-muted">Sutradara : {{ $film->sutradara }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Kategori --}}
                                <td style="vertical-align: middle;">{{ $film->category->name ?? '-' }}</td>

                                {{-- Durasi HH:MM:SS --}}
                                <td style="vertical-align: middle;">
                                    @php
                                    $detik = $film->duration;
                                    $jam = floor($detik / 3600);
                                    $menit = floor(($detik % 3600) / 60);
                                    $sisa = $detik % 60;
                                    @endphp
                                    {{ sprintf('%02d:%02d:%02d', $jam, $menit, $sisa) }}
                                </td style="vertical-align: middle;">

                                {{-- Tanggal Submit --}}
                                <td style="vertical-align: middle;" data-order="{{ optional($film->created_at)->timestamp ?? 0 }}">
                                    {{ \Carbon\Carbon::parse($film->created_at)->format('d M Y') }}<br>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($film->created_at)->format('H:i') }} WIB</small>
                                </td>

                                {{-- Status --}}
                                <td style="vertical-align: middle;">
                                    @php $s = $film->statusBadgeFor(auth()->user()); @endphp
                                    <span style="background:{{ $s['bg'] }}; color:{{ $s['color'] }}; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; white-space:nowrap;">
                                        {{ $s['label'] }}
                                    </span>
                                </td>

                                {{-- Peserta --}}
                                <td style="vertical-align: middle;">{{ $film->user->name ?? '-' }}</td>

                                {{-- Aksi --}}
                                <td style="vertical-align: middle;">
                                    <a href="{{ route('film.show', $film->id) }}"
                                        class="btn btn-info btn-xs" title="Detail">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="{{ route('film.edit', $film->id) }}"
                                        class="btn btn-warning btn-xs" title="Edit">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    <form action="{{ route('film.destroy', $film->id) }}" method="POST"
                                        style="display:inline-block;"
                                        onsubmit="return confirm('Yakin ingin menghapus film ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs" title="Hapus">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
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
</section>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const table = $('#example4').DataTable({
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
            ],
            columnDefs: [{
                orderable: false,
                targets: [0, 1, 7]
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

        @if(!$isSubmissionAdmin)
        $('#filter-kategori').on('change', function() {
            const val = $(this).val();
            table.column(2).search(val).draw();
        });
        @endif
    });
</script>
@endpush
