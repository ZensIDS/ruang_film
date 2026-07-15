@extends('layouts.master')
@section('container')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Data Kategori Film
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <a href="{{ route('categories.create') }}" class="btn btn-md bg-green">Tambah</a>
                    </div><!-- /.box-header -->
                    <div class="box-body table-responsive">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <td>No</td>
                                    <td>Nama Kategori</td>
                                    <td>Slug</td>
                                    <td>Route Detail</td>
                                    <td>Urutan</td>
                                    <td>Status</td>
                                    <td>Aksi</td>
                                </tr>
                            </thead>
                            @foreach ($categories as $key)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $key->name }}</td>
                                    <td>{{ $key->slug ?: '-' }}</td>
                                    <td>{{ $key->detail_route ?: '-' }}</td>
                                    <td>{{ $key->sort_order ?? 0 }}</td>
                                    <td>
                                        <span class="label {{ ($key->is_active ?? true) ? 'label-success' : 'label-default' }}">
                                            {{ ($key->is_active ?? true) ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td>
                                        {{-- <a class="btn btn-info" href="{{ route('users.show', $key->id) }}">Show</a> --}}
                                        <a class="btn btn-warning" href="{{ route('categories.edit', $key->id) }}">Edit</a>
                                        <form action="{{ route('categories.destroy', $key->id) }}" method="post"
                                            style="display: inline;">
                                            @method('delete')
                                            @csrf
                                            <button class="btn btn-danger border-0 "
                                                onclick="return confirm('Are you sure?')">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div><!-- /.col -->
        </div><!-- /.row -->
    </section><!-- /.content -->
@endsection
