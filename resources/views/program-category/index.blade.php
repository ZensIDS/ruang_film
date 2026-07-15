@extends('layouts.master')
@section('container')
    <section class="content-header">
        <h1>Data Kategori Program</h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <a href="{{ route('program-categories.create') }}" class="btn btn-md bg-green">Tambah</a>
                    </div>
                    <div class="box-body table-responsive">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <td>No</td>
                                    <td>Nama</td>
                                    <td>Slug</td>
                                    <td>Urutan</td>
                                    <td>Status</td>
                                    <td>Aksi</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($categories as $category)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $category->name }}</td>
                                        <td>{{ $category->slug }}</td>
                                        <td>{{ $category->sort_order }}</td>
                                        <td>
                                            <span class="label {{ $category->is_active ? 'label-success' : 'label-default' }}">
                                                {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a class="btn btn-warning" href="{{ route('program-categories.edit', $category->id) }}">Edit</a>
                                            <form action="{{ route('program-categories.destroy', $category->id) }}" method="post" style="display:inline;">
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
