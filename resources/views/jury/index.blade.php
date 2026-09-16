@extends('layouts.master')
@section('container')
    <section class="content-header">
        <h1>Data Juri</h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <a href="{{ route('juries.create') }}" class="btn btn-md bg-green">Tambah</a>
                    </div>
                    <div class="box-body table-responsive">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <td>No</td>
                                    <td>Foto</td>
                                    <td>Nama</td>
                                    <td>Jabatan</td>
                                    <td>Kategori</td>
                                    <td>Urutan</td>
                                    <td>Status</td>
                                    <td>Aksi</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($juries as $jury)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td><img src="{{ $jury->photo_url }}" alt="{{ $jury->name }}" style="width:48px;height:48px;object-fit:cover;border-radius:8px;"></td>
                                        <td>{{ $jury->name }}</td>
                                        <td>{{ $jury->title }}</td>
                                        <td>{{ optional($jury->category)->name }}</td>
                                        <td>{{ $jury->sort_order }}</td>
                                        <td>
                                            <span class="label {{ $jury->is_active ? 'label-success' : 'label-default' }}">
                                                {{ $jury->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a class="btn btn-warning" href="{{ route('juries.edit', $jury->id) }}">Edit</a>
                                            <form action="{{ route('juries.destroy', $jury->id) }}" method="post" style="display:inline;">
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