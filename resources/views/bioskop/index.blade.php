@extends('layouts.master')
@section('container')
    <section class="content-header">
        <h1>Bioskop Gratis</h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <a href="{{ route('bioskops.create') }}" class="btn btn-md bg-green">Tambah</a>
                    </div>
                    <div class="box-body table-responsive">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <td>No</td>
                                    <td>Nama Bioskop</td>
                                    <td>Lokasi</td>
                                    <td>Jadwal</td>
                                    <td>Urutan</td>
                                    <td>Status</td>
                                    <td>Aksi</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bioskops as $bioskop)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $bioskop->name }}</td>
                                        <td>{{ $bioskop->venue }}</td>
                                        <td>{{ $bioskop->schedule_summary }}</td>
                                        <td>{{ $bioskop->sort_order }}</td>
                                        <td>
                                            <span class="label {{ $bioskop->is_active ? 'label-success' : 'label-default' }}">
                                                {{ $bioskop->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a class="btn btn-warning" href="{{ route('bioskops.edit', $bioskop->id) }}">Edit</a>
                                            <form action="{{ route('bioskops.destroy', $bioskop->id) }}" method="post" style="display:inline;">
                                                @method('delete')
                                                @csrf
                                                <button class="btn btn-danger border-0" onclick="return confirm('Are you sure?')">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection