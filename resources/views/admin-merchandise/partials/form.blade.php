@php
    $hasVariants = (bool) old('has_variants', optional($merchandise)->has_variants ?? false);
    $variantRows = old('variants');

    if ($variantRows === null) {
        $variantRows = $merchandise
            ? $merchandise->variants()->where('is_active', true)->get()->map(function ($variant) {
                return ['id' => $variant->id, 'name' => $variant->name, 'qty_stock' => $variant->qty_stock];
            })->all()
            : [];
    }

    $variantErrors = collect($errors->get('variants'))
        ->merge(collect($errors->get('variants.*'))->flatten())
        ->unique()
        ->values();
@endphp
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
                            <div class="col-md-4" id="simple-stock-group">
                                <div class="form-group {{ $errors->has('qty_stock') ? 'has-error' : '' }}">
                                    <label>Qty Stock</label>
                                    <input type="number" name="qty_stock" id="qty-stock-input" class="form-control" min="0" value="{{ old('qty_stock', optional($merchandise)->qty_stock) }}" {{ $hasVariants ? 'disabled' : 'required' }}>
                                    @error('qty_stock')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Ringkasan</label>
                                    <input type="text" name="summary" class="form-control" value="{{ old('summary', optional($merchandise)->summary) }}">
                                </div>
                            </div>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="has_variants" id="has-variants-toggle" value="1" {{ $hasVariants ? 'checked' : '' }}>
                                <strong>Produk memiliki varian</strong> (mis. ukuran kaos S &ndash; XXXL, tiap varian punya stok sendiri)
                            </label>
                        </div>

                        <div id="variant-panel" class="box box-solid" style="border:1px solid #d2d6de; margin-top:10px; {{ $hasVariants ? '' : 'display:none;' }}">
                            <div class="box-header with-border">
                                <h3 class="box-title" style="font-size:15px;">Varian &amp; Stok</h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-default btn-sm" id="preset-sizes">Isi ukuran kaos (S &ndash; XXXL)</button>
                                    <button type="button" class="btn btn-primary btn-sm" id="add-variant">+ Tambah varian</button>
                                </div>
                            </div>
                            <div class="box-body">
                                @if($variantErrors->isNotEmpty())
                                <div class="alert alert-danger" style="margin-bottom:10px;">
                                    @foreach($variantErrors as $variantError)
                                    <div>{{ $variantError }}</div>
                                    @endforeach
                                </div>
                                @endif
                                <p class="help-block" style="margin-top:0;">
                                    Harga, diskon, dan berat berlaku sama untuk semua varian. Total stok produk dihitung otomatis dari jumlah stok semua varian.
                                    Varian yang pernah dipesan tidak dihapus permanen, hanya disembunyikan dari pembeli.
                                </p>
                                <div class="table-responsive">
                                    <table class="table table-bordered" style="margin-bottom:0;">
                                        <thead>
                                            <tr>
                                                <th>Nama Varian</th>
                                                <th style="width:160px;">Stok</th>
                                                <th style="width:90px;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="variant-rows"></tbody>
                                        <tfoot>
                                            <tr>
                                                <th class="text-right">Total stok</th>
                                                <th id="variant-total">0</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <p id="variant-empty" class="text-muted" style="margin:10px 0 0;">Belum ada varian. Klik "Isi ukuran kaos" atau "Tambah varian".</p>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top:15px;">
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
<script>
(function () {
    var initialRows = @json($variantRows);
    var tbody = document.getElementById('variant-rows');
    var toggle = document.getElementById('has-variants-toggle');
    var panel = document.getElementById('variant-panel');
    var simpleGroup = document.getElementById('simple-stock-group');
    var simpleInput = document.getElementById('qty-stock-input');
    var totalEl = document.getElementById('variant-total');
    var emptyEl = document.getElementById('variant-empty');
    var counter = 0;
    var sizePreset = ['S', 'M', 'L', 'XL', 'XXL', 'XXXL'];

    function updateTotal() {
        var total = 0;
        tbody.querySelectorAll('input[data-role="stock"]').forEach(function (input) {
            total += parseInt(input.value, 10) || 0;
        });
        totalEl.textContent = total;
        emptyEl.style.display = tbody.children.length ? 'none' : '';
    }

    function addRow(data) {
        data = data || {};
        var index = counter++;
        var tr = document.createElement('tr');

        var tdName = document.createElement('td');
        var nameInput = document.createElement('input');
        nameInput.type = 'text';
        nameInput.name = 'variants[' + index + '][name]';
        nameInput.className = 'form-control input-sm';
        nameInput.maxLength = 50;
        nameInput.required = true;
        nameInput.placeholder = 'Contoh: M';
        nameInput.value = data.name || '';
        tdName.appendChild(nameInput);

        if (data.id) {
            var idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'variants[' + index + '][id]';
            idInput.value = data.id;
            tdName.appendChild(idInput);
        }

        var tdStock = document.createElement('td');
        var stockInput = document.createElement('input');
        stockInput.type = 'number';
        stockInput.name = 'variants[' + index + '][qty_stock]';
        stockInput.className = 'form-control input-sm';
        stockInput.min = 0;
        stockInput.required = true;
        stockInput.setAttribute('data-role', 'stock');
        stockInput.value = (data.qty_stock !== undefined && data.qty_stock !== null) ? data.qty_stock : 0;
        stockInput.addEventListener('input', updateTotal);
        tdStock.appendChild(stockInput);

        var tdAction = document.createElement('td');
        var removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-danger btn-xs';
        removeBtn.textContent = 'Hapus';
        removeBtn.addEventListener('click', function () {
            tr.remove();
            updateTotal();
        });
        tdAction.appendChild(removeBtn);

        tr.appendChild(tdName);
        tr.appendChild(tdStock);
        tr.appendChild(tdAction);
        tbody.appendChild(tr);
        updateTotal();

        return nameInput;
    }

    function applyToggle() {
        var on = toggle.checked;

        panel.style.display = on ? '' : 'none';
        simpleGroup.style.display = on ? 'none' : '';
        simpleInput.disabled = on;
        simpleInput.required = !on;

        panel.querySelectorAll('input').forEach(function (input) {
            input.disabled = !on;
        });
    }

    document.getElementById('add-variant').addEventListener('click', function () {
        addRow().focus();
    });

    document.getElementById('preset-sizes').addEventListener('click', function () {
        var existing = [];
        tbody.querySelectorAll('input[name$="[name]"]').forEach(function (input) {
            existing.push(input.value.trim().toLowerCase());
        });

        sizePreset.forEach(function (size) {
            if (existing.indexOf(size.toLowerCase()) === -1) {
                addRow({ name: size, qty_stock: 0 });
            }
        });
    });

    toggle.addEventListener('change', applyToggle);

    (Array.isArray(initialRows) ? initialRows : Object.values(initialRows || {})).forEach(addRow);
    applyToggle();
})();
</script>