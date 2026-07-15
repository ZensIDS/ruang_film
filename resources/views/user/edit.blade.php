@extends('layouts.master')
@section('container')
<section class="content">
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- general form elements -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Edit User</h3>
                </div><!-- /.box-header -->
                <!-- form start -->
                <form action="{{ route('users.update', $users->id) }}" method="POST">
                    @method('PUT')
                    @csrf
                    <div class="box-body">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Nama</label>
                            <input required type="text" class="form-control" value="{{ old('name', $users->name) }}"
                                name="name" placeholder="Masukkan Nama">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Email</label>
                            <input required type="email" class="form-control"
                                value="{{ old('email', $users->email) }}" name="email" placeholder="Masukkan Email">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">No Whatsapp</label>
                            <input required type="no_hp" class="form-control"
                                value="{{ old('no_hp', $users->no_hp) }}" name="no_hp" placeholder="Masukkan No Whatsapp">
                        </div>
                        @if($users->role === 'juri')
                        <div class="form-group">
                            <label for="category_id">Kategori Film Juri</label>
                            <select name="category_id" id="category_id" class="form-control" required>
                                <option value="">Pilih Kategori Film</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ (string) old('category_id', $users->category_id) === (string) $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('category_id')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        @endif
                        <input type="hidden" name="role" value="{{ $users->role }}">
                    </div><!-- /.box-body -->

                    <div class="box-footer">
                        <a href="javascript:history.back()" class="btn btn-default">Kembali</a>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div><!-- /.box -->
        </div>
    </div>
</section>
@endsection
