@extends('layouts.master')
@section('container')
<section class="content">
    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Edit Kategori Merchandise</h3>
                </div>
                <form action="{{ route('merchandise-categories.update', $category) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="box-body">
                        <div class="form-group">
                            <label>Nama</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $category->description) }}</textarea>
                        </div>
                        <div class="checkbox">
                            <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}> Aktif</label>
                        </div>
                    </div>
                    <div class="box-footer">
                        <a href="{{ route('merchandise-categories.index') }}" class="btn btn-default">Kembali</a>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
