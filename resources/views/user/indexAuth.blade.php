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
                </div><!-- /.box-header -->
                <div class="box-body table-responsive">
                    <table id="example1" class="table table-bordered table-striped">
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
                        @foreach ($users as $key)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $key->name }}</td>
                            <td>{{ $key->no_hp }}</td>
                            <td>{{ $key->email }}</td>
                            <td>{{ strtoupper($key->role) }}</td>
                            <td>
                                <a class="btn btn-info" href="{{ route('users.show', $key->id) }}">Show</a>
                                @if (Auth::user()->role != 'adminsub')
                                <a class="btn btn-warning" href="{{ route('users.edit', $key->id) }}">Edit</a>
                                <form action="{{ route('users.destroy', $key->id) }}" method="post"
                                    style="display: inline;">
                                    @method('delete')
                                    @csrf
                                    <button class="btn btn-danger border-0 "
                                        onclick="return confirm('Are you sure?')">Hapus</button>
                                </form>
                                @endif
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
