@extends('layouts.master')
@section('container')
<section class="content-header">
    <h1>Kategori Merchandise</h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <a href="{{ route('merchandise-categories.create') }}" class="btn btn-success">Tambah</a>
                </div>
                <div class="box-body table-responsive">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $category->name }}</td>
                                <td>{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                                <td>
                                    <a href="{{ route('merchandise-categories.edit', $category) }}" class="btn btn-warning btn-xs">Edit</a>
                                    <form action="{{ route('merchandise-categories.destroy', $category) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Hapus kategori ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
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
