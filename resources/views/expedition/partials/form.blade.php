<section class="content">
    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ $title }}</h3>
                </div>
                <form action="{{ $action }}" method="POST">
                    @csrf
                    @if($method !== 'POST')
                    @method($method)
                    @endif
                    <div class="box-body">
                        <div class="form-group">
                            <label>Nama Expedisi</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', optional($expedition)->name) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Kode RajaOngkir</label>
                            <input type="text" name="external_code" class="form-control" value="{{ old('external_code', optional($expedition)->external_code) }}" required>
                            <p class="help-block">Contoh: <code>jne</code>, <code>jnt</code>, <code>sicepat</code>.</p>
                        </div>
                        <div class="form-group">
                            <label>Nama Layanan</label>
                            <input type="text" name="service_name" class="form-control" value="{{ old('service_name', optional($expedition)->service_name) }}">
                        </div>
                        <div class="form-group">
                            <label>Biaya Ongkir</label>
                            <input type="number" name="fee" class="form-control" min="0" step="0.01" value="{{ old('fee', optional($expedition)->fee) }}" required>
                        </div>
                        <div class="checkbox">
                            <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', optional($expedition)->is_active ?? true) ? 'checked' : '' }}> Aktif</label>
                        </div>
                    </div>
                    <div class="box-footer">
                        <a href="{{ route('expeditions.index') }}" class="btn btn-default">Kembali</a>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
