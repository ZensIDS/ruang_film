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
                            <label>Nama Pemilik Rekening</label>
                            <input type="text" name="rek_name" class="form-control" value="{{ old('rek_name', optional($bankAccount)->rek_name) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Nama Bank</label>
                            <input type="text" name="rek_bank_name" class="form-control" value="{{ old('rek_bank_name', optional($bankAccount)->rek_bank_name) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Nomor Rekening</label>
                            <input type="text" name="rek_bank_no" class="form-control" value="{{ old('rek_bank_no', optional($bankAccount)->rek_bank_no) }}" required>
                        </div>
                        <div class="checkbox">
                            <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', optional($bankAccount)->is_active ?? true) ? 'checked' : '' }}> Aktif</label>
                        </div>
                    </div>
                    <div class="box-footer">
                        <a href="{{ route('bank-accounts.index') }}" class="btn btn-default">Kembali</a>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
