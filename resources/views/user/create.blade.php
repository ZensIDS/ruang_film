@extends('layouts.master')
@section('container')
<section class="content">
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- general form elements -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Tambah User</h3>
                </div><!-- /.box-header -->
                <!-- form start -->
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="box-body">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Nama</label>
                            <input required type="text" class="form-control" name="name"
                                placeholder="Masukkan Nama">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Email</label>
                            <input required type="email" class="form-control" name="email"
                                placeholder="Masukkan Email">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Password</label>
                            <input required type="password" class="form-control" name="password"
                                placeholder="Masukkan Password">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Role</label>
                            <select name="role" id="role" class="form-control">
                                <option value="1" selected disabled>Pilih Role User</option>
                                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Superadmin</option>
                                <option value="adminsub" {{ old('role') === 'adminsub' ? 'selected' : '' }}>Admin Submission</option>
                                <option value="adminmerch" {{ old('role') === 'adminmerch' ? 'selected' : '' }}>Admin Merchandise</option>
                                <option value="kurator" {{ old('role') === 'kurator' ? 'selected' : '' }}>Kurator</option>
                                <option value="juri" {{ old('role') === 'juri' ? 'selected' : '' }}>Juri</option>
                            </select>
                        </div>
                        <div class="form-group" id="category-group" style="display:{{ old('role') === 'juri' ? 'block' : 'none' }};">
                            <label for="category_id">Kategori Film Juri</label>
                            <select name="category_id" id="category_id" class="form-control" {{ old('role') === 'juri' ? '' : 'disabled' }}>
                                <option value="">Pilih Kategori Film</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ (string) old('category_id') === (string) $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('category_id')
                            <small class="text-danger">{{ $message }}</small>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role');
        const categoryGroup = document.getElementById('category-group');
        const categorySelect = document.getElementById('category_id');

        if (!roleSelect || !categoryGroup || !categorySelect) {
            return;
        }

        const syncCategoryField = function() {
            const isJury = roleSelect.value === 'juri';

            categoryGroup.style.display = isJury ? 'block' : 'none';
            categorySelect.disabled = !isJury;

            if (!isJury) {
                categorySelect.value = '';
            }
        };

        roleSelect.addEventListener('change', syncCategoryField);
        syncCategoryField();
    });
</script>
@endpush
