@extends('layouts.master')
@section('container')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        Data Peserta
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    @if(Auth::user()->role === 'admin')
                    <a href="{{ route('users.create.author') }}" class="btn btn-md bg-green">Tambah Peserta</a>
                    @endif
                    <a href="{{ route('users.export-peserta') }}" class="btn btn-md bg-blue">
                        <i class="fa fa-file-excel-o"></i> Export Excel
                    </a>
                </div><!-- /.box-header -->
                <div class="box-body table-responsive">
                    <table id="tabel-peserta" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <td>No</td>
                                <td>Nama</td>
                                <td>No Whatsapp</td>
                                <td>Email</td>
                                <td>Role</td>
                                <td>Aksi</td>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Baris diisi lewat AJAX (server-side DataTables), lihat script di bawah --}}
                        </tbody>
                    </table>
                </div><!-- /.box-body -->
            </div><!-- /.box -->
        </div><!-- /.col -->
    </div><!-- /.row -->
</section><!-- /.content -->
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#tabel-peserta').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('users.index.author.data') }}",
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 data",
                infoFiltered: "(difilter dari _MAX_ total data)",
                zeroRecords: "Tidak ada data yang cocok",
                emptyTable: "Belum ada peserta",
                processing: "Memuat data...",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya",
                },
            },
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            order: [
                [1, 'asc']
            ],
            columns: [{
                    data: 'no',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'name'
                },
                {
                    data: 'no_hp'
                },
                {
                    data: 'email'
                },
                {
                    data: 'role'
                },
                {
                    data: 'aksi',
                    orderable: false,
                    searchable: false
                },
            ],
        });
    });
</script>
@endpush