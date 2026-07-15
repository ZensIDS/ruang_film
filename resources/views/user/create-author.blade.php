@extends('layouts.master')
@section('container')
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Tambah Data Peserta</h3>
                </div>
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="role" value="peserta">

                    <div class="box-body">
                        @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        <div class="form-group">
                            <label>Nama</label>
                            <input required type="text" class="form-control" name="name"
                                value="{{ old('name') }}" placeholder="Masukkan Nama Peserta">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input required type="email" class="form-control" name="email"
                                value="{{ old('email') }}" placeholder="Masukkan Email">
                        </div>
                        <div class="form-group">
                            <label>No Whatsapp</label>
                            <input required type="text" class="form-control" name="no_hp"
                                value="{{ old('no_hp') }}" placeholder="Masukkan No Whatsapp">
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input required type="password" class="form-control" name="password"
                                placeholder="Masukkan Password">
                        </div>
                    </div>

                    <div class="box-footer">
                        <a href="{{ route('users.index.author') }}" class="btn btn-default">Kembali</a>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
