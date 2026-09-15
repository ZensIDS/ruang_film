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

                        {{-- Export Excel: otomatis bawa filter yang sedang aktif di URL --}}
                        <a href="{{ route('film.export', request()->only('submission_setting_id', 'category_id', 'curation_status')) }}"
                        class="btn btn-success" style="margin-left:8px;">
                            <i class="fa fa-file-excel-o"></i> Export Excel
                        </a>
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

                    <a href="{{ route('film.export') }}" class="btn btn-success" style="margin-left:auto;">
                        <i class="fa fa-file-excel-o"></i> Export Excel
                    </a>
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
                            {{-- Baris diisi lewat AJAX (server-side DataTables), lihat script di bawah --}}
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
        let categoryNameFilter = '';

        const table = $('#example4').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('film.data') }}",
                data: function(d) {
                    d.submission_setting_id = "{{ $selectedSubmissionSettingId }}";
                    d.category_id = "{{ $selectedCategoryId }}";
                    d.curation_status = "{{ $selectedCurationStatus }}";
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
            ],
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
                    data: 'peserta',
                    orderable: false
                },
                {
                    data: 'aksi',
                    orderable: false,
                    searchable: false
                },
            ],
        });

        @if(!$isSubmissionAdmin)
        $('#filter-kategori').on('change', function() {
            categoryNameFilter = $(this).val();
            table.draw();
        });
        @endif
    });
</script>
@endpush