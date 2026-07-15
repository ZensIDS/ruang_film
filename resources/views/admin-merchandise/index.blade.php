@extends('layouts.master')
@section('container')
<section class="content-header">
    <h1>Data Merchandise</h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <a href="{{ route('admin-merchandises.create') }}" class="btn btn-success">Tambah</a>
                </div>
                <div class="box-body table-responsive">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Berat</th>
                                <th>Stok</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($merchandises as $merchandise)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $merchandise->name }}</td>
                                <td>{{ $merchandise->category->name ?? '-' }}</td>
                                <td>@currency($merchandise->price)</td>
                                <td>{{ number_format($merchandise->weight) }} gr</td>
                                <td>{{ $merchandise->qty_stock }}</td>
                                <td>{{ $merchandise->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                                <td>
                                    <a href="{{ route('admin-merchandises.edit', $merchandise) }}" class="btn btn-warning btn-xs">Edit</a>
                                    <form action="{{ route('admin-merchandises.destroy', $merchandise) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Hapus merchandise ini?')">
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
