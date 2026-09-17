@php
    $selectedType = old('type', optional($jury)->type ?? ($type ?? 'juri'));
@endphp
<section class="content">
    <div class="row">
        <div class="col-md-12">
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
                        @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul style="margin-bottom:0;">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Tipe</label>
                                    <select name="type" id="jury-type-select" class="form-control" required>
                                        <option value="juri" {{ $selectedType === 'juri' ? 'selected' : '' }}>Juri</option>
                                        <option value="kurator" {{ $selectedType === 'kurator' ? 'selected' : '' }}>Kurator</option>
                                    </select>
                                    <p class="help-block">Kurator tidak terikat kategori kompetisi tertentu.</p>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Jabatan / Peran</label>
                                    <input type="text" name="title" class="form-control" value="{{ old('title', optional($jury)->title) }}" placeholder="Sutradara / Penulis Skenario">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Nama</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', optional($jury)->name) }}" required>
                                </div>
                            </div>
                            <div class="col-md-12" id="jury-category-group">
                                <div class="form-group">
                                    <label>Kategori Kompetisi</label>
                                    <select name="category_id" class="form-control">
                                        <option value="">Pilih Kategori</option>
                                        @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', optional($jury)->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <p class="help-block">Juri akan ditampilkan pada slider kategori ini di halaman Program.</p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Urutan Tampil</label>
                                    <input type="number" name="sort_order" class="form-control" min="0" value="{{ old('sort_order', optional($jury)->sort_order ?? 0) }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Foto</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                            @if(optional($jury)->photo)
                            <p class="help-block">Foto saat ini: <a href="{{ $jury->photo_url }}" target="_blank">Lihat</a></p>
                            @endif
                        </div>
                        <div class="checkbox">
                            <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', optional($jury)->is_active ?? true) ? 'checked' : '' }}> Aktif</label>
                        </div>
                    </div>
                    <div class="box-footer">
                        <a href="{{ route('juries.index') }}" class="btn btn-default">Kembali</a>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    var typeSelect = document.getElementById('jury-type-select');
    var categoryGroup = document.getElementById('jury-category-group');

    function toggleCategoryField() {
        if (typeSelect.value === 'kurator') {
            categoryGroup.style.display = 'none';
        } else {
            categoryGroup.style.display = '';
        }
    }

    typeSelect.addEventListener('change', toggleCategoryField);
    toggleCategoryField();
})();
</script>