@extends('layouts.master')
@section('container')
<section class="content">
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- general form elements -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Tambah Kategori Film</h3>
                </div><!-- /.box-header -->
                <!-- form start -->
                <form action="{{ route('categories.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="box-body">
                        @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul style="margin-bottom:0;">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        <div class="form-group">
                            <label>Nama Kategori</label>
                            <input required type="text" class="form-control" name="name" value="{{ old('name') }}"
                                placeholder="Masukkan Nama Kategori">
                        </div>
                        <div class="form-group">
                            <label>Slug</label>
                            <input type="text" class="form-control" name="slug" value="{{ old('slug') }}"
                                placeholder="umum-nasional">
                        </div>
                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Ringkasan Landing Page</label>
                            <textarea name="landing_summary" class="form-control" rows="3">{{ old('landing_summary') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Route Detail</label>
                            <input type="text" class="form-control" name="detail_route" value="{{ old('detail_route') }}"
                                placeholder="/umum">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Urutan Tampil</label>
                                    <input type="number" class="form-control" name="sort_order" value="{{ old('sort_order', 0) }}" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group" style="padding-top:25px;">
                                    <label>
                                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                                        Aktif ditampilkan
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Gambar Kategori</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                        </div>
                        @include('category.partials.rubric-fields')
                    </div><!-- /.box-body -->

                    <div class="box-footer">
                        <a href="{{ route('categories.index') }}" class="btn btn-default">Kembali</a>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div><!-- /.box -->
        </div>
    </div>
</section>
@endsection
