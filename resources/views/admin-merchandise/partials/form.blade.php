<section class="content">
    <div class="row">
        <div class="col-md-10">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ $title }}</h3>
                </div>
                <form action="{{ $action }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @if($method !== 'POST')
                    @method($method)
                    @endif
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kategori</label>
                                    <select name="merchandise_category_id" class="form-control" required>
                                        <option value="">Pilih Kategori</option>
                                        @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('merchandise_category_id', optional($merchandise)->merchandise_category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Produk</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', optional($merchandise)->name) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Harga</label>
                                    <input type="number" name="price" class="form-control" min="0" step="0.01" value="{{ old('price', optional($merchandise)->price) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Diskon (disembunyikan di client)</label>
                                    <input type="number" name="discount_price" class="form-control" min="0" step="0.01" value="{{ old('discount_price', optional($merchandise)->discount_price) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Berat (gram)</label>
                                    <input type="number" name="weight" class="form-control" min="0" value="{{ old('weight', optional($merchandise)->weight) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Qty Stock</label>
                                    <input type="number" name="qty_stock" class="form-control" min="0" value="{{ old('qty_stock', optional($merchandise)->qty_stock) }}" required>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Ringkasan</label>
                                    <input type="text" name="summary" class="form-control" value="{{ old('summary', optional($merchandise)->summary) }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea name="description" class="form-control" rows="4">{{ old('description', optional($merchandise)->description) }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Gambar</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            @if(optional($merchandise)->image)
                            <p class="help-block">Gambar saat ini: <a href="{{ asset('storage/' . $merchandise->image) }}" target="_blank">Lihat</a></p>
                            @endif
                        </div>
                        <div class="checkbox">
                            <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', optional($merchandise)->is_active ?? true) ? 'checked' : '' }}> Aktif</label>
                        </div>
                    </div>
                    <div class="box-footer">
                        <a href="{{ route('admin-merchandises.index') }}" class="btn btn-default">Kembali</a>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
