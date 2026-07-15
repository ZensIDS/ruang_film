@extends('layouts.master')
@section('container')
<section class="content">
    <div class="row">
      <!-- left column -->
      <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">Ganti Password</h3>
          </div><!-- /.box-header -->
          <!-- form start -->
          <form action="{{ route('user.changepass.update') }}" method="POST">
            @csrf
            <div class="box-body">
              <div class="form-group">
                <label for="exampleInputEmail1">Password Lama</label>
                <input required type="password" class="form-control" name="old_password" placeholder="Masukkan Password Lama">
                @error('old_password')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
              <div class="form-group">
                <label for="exampleInputEmail1">Password Baru</label>
                <input required type="password" class="form-control" name="new_password" placeholder="Masukkan Password Baru">
                @error('new_password')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
              <div class="form-group">
                <label for="exampleInputEmail1">Ulangi Password Baru</label>
                <input required type="password" class="form-control" name="new_password_confirmation" placeholder="Ulangi Password">
                @error('new_password_confirmation')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
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
