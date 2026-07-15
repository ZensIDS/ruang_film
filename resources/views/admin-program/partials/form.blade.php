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
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kategori Program</label>
                                    <select name="program_category_id" class="form-control" required>
                                        <option value="">Pilih Kategori</option>
                                        @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('program_category_id', optional($program)->program_category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Judul Program</label>
                                    <input type="text" name="title" class="form-control" value="{{ old('title', optional($program)->title) }}" required>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Slug</label>
                                    <input type="text" name="slug" class="form-control" value="{{ old('slug', optional($program)->slug) }}" placeholder="biarkan kosong untuk generate otomatis">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Urutan Tampil</label>
                                    <input type="number" name="sort_order" class="form-control" min="0" value="{{ old('sort_order', optional($program)->sort_order ?? 0) }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Ringkasan</label>
                            <textarea name="summary" class="form-control" rows="3">{{ old('summary', optional($program)->summary) }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Konten Detail</label>
                            <textarea id="program-content-editor" name="content" class="form-control" rows="10">{{ old('content', optional($program)->content) }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Poster / Thumbnail</label>
                            <input type="file" name="poster" class="form-control" accept="image/*">
                            @if(optional($program)->poster)
                            <p class="help-block">Poster saat ini: <a href="{{ $program->poster_url }}" target="_blank">Lihat</a></p>
                            @endif
                        </div>
                        <div class="checkbox">
                            <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', optional($program)->is_active ?? true) ? 'checked' : '' }}> Aktif</label>
                        </div>
                    </div>
                    <div class="box-footer">
                        <a href="{{ route('admin-programs.index') }}" class="btn btn-default">Kembali</a>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
    if (typeof CKEDITOR !== 'undefined' && document.getElementById('program-content-editor')) {
        CKEDITOR.replace('program-content-editor');
    }
</script>
@endpush
