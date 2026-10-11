@php
    $defaultSessions = [
        ['label' => 'Sesi 1', 'time' => '13:00', 'status' => 'available'],
        ['label' => 'Sesi 2', 'time' => '15:30', 'status' => 'available'],
        ['label' => 'Sesi 3', 'time' => '19:00', 'status' => 'available'],
    ];
    $schedules = old('schedules', $bioskop?->schedules ?: [
        ['date' => now()->toDateString(), 'sessions' => $defaultSessions],
    ]);
    $schedules = array_values($schedules);
@endphp

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
                <label>Nama Bioskop</label>
                <input required type="text" class="form-control" name="name" value="{{ old('name', $bioskop?->name) }}" placeholder="Contoh: Bioskop Tengkorak Mbah Sayem">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Nama Tempat</label>
                <input type="text" class="form-control" name="venue" value="{{ old('venue', $bioskop?->venue) }}" placeholder="Contoh: Gedung Karya Dharma">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Alamat</label>
                <input type="text" class="form-control" name="address" value="{{ old('address', $bioskop?->address) }}" placeholder="Contoh: Jl. Ahmad Yani No. 1">
            </div>
        </div>
    </div>

    <hr style="margin-top:0;">

    <div class="form-group">
        <label>Jadwal Tayang <small class="text-muted">(tiap tanggal punya sesi sendiri)</small></label>

        <div id="schedule-wrap">
            @foreach ($schedules as $d => $schedule)
            <div class="date-block" data-d="{{ $d }}" style="border:1px solid #d2d6de;border-radius:4px;padding:12px;margin-bottom:12px;background:#fafafa;">
                <div class="row" style="margin-bottom:10px;">
                    <div class="col-xs-8 col-md-4">
                        <input required type="date" class="form-control" name="schedules[{{ $d }}][date]" value="{{ $schedule['date'] ?? '' }}">
                    </div>
                    <div class="col-xs-4 col-md-3">
                        <button type="button" class="btn btn-danger btn-remove-date">Hapus Tanggal</button>
                    </div>
                </div>

                <div class="sessions" data-next="{{ count($schedule['sessions'] ?? []) }}">
                    @foreach (($schedule['sessions'] ?? []) as $s => $session)
                    <div class="row session-row" style="margin-bottom:8px;">
                        <div class="col-xs-4 col-md-3">
                            <input required type="text" class="form-control" name="schedules[{{ $d }}][sessions][{{ $s }}][label]" value="{{ $session['label'] ?? '' }}" placeholder="Sesi 1" maxlength="30">
                        </div>
                        <div class="col-xs-4 col-md-2">
                            <input required type="time" class="form-control" name="schedules[{{ $d }}][sessions][{{ $s }}][time]" value="{{ $session['time'] ?? '' }}">
                        </div>
                        <div class="col-xs-4 col-md-3">
                            <select class="form-control" name="schedules[{{ $d }}][sessions][{{ $s }}][status]">
                                <option value="available" {{ ($session['status'] ?? 'available') === 'available' ? 'selected' : '' }}>Available</option>
                                <option value="full" {{ ($session['status'] ?? '') === 'full' ? 'selected' : '' }}>Full</option>
                            </select>
                        </div>
                        <div class="col-xs-12 col-md-2">
                            <button type="button" class="btn btn-default btn-remove-session">Hapus</button>
                        </div>
                    </div>
                    @endforeach
                </div>

                <button type="button" class="btn btn-default btn-sm btn-add-session">+ Tambah Sesi</button>
            </div>
            @endforeach
        </div>

        <button type="button" id="btn-add-date" class="btn btn-info btn-sm">+ Tambah Tanggal</button>
        <p class="help-block">Tanggal baru otomatis menyalin sesi dari tanggal terakhir (status di-reset ke Available).</p>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Urutan Tampil</label>
                <input type="number" class="form-control" name="sort_order" value="{{ old('sort_order', $bioskop?->sort_order ?? 0) }}" min="0">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group" style="padding-top:25px;">
                <label>
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $bioskop?->is_active ?? true) ? 'checked' : '' }}>
                    Aktif ditampilkan di beranda
                </label>
            </div>
        </div>
    </div>
</div>

<div class="box-footer">
    <a href="{{ route('bioskops.index') }}" class="btn btn-default">Kembali</a>
    <button type="submit" class="btn btn-primary">Simpan</button>
</div>

@push('scripts')
<script>
(function () {
    var wrap = document.getElementById('schedule-wrap');
    var nextDate = wrap.querySelectorAll('.date-block').length;
    // index tanggal harus unik walau ada blok yang dihapus
    wrap.querySelectorAll('.date-block').forEach(function (b) { nextDate = Math.max(nextDate, parseInt(b.dataset.d, 10) + 1); });

    function esc(v) { return String(v).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;'); }

    function sessionHtml(d, s, label, time, status) {
        var n = 'schedules[' + d + '][sessions][' + s + ']';
        return '<div class="row session-row" style="margin-bottom:8px;">' +
            '<div class="col-xs-4 col-md-3"><input required type="text" class="form-control" name="' + n + '[label]" value="' + esc(label) + '" placeholder="Sesi 1" maxlength="30"></div>' +
            '<div class="col-xs-4 col-md-2"><input required type="time" class="form-control" name="' + n + '[time]" value="' + esc(time) + '"></div>' +
            '<div class="col-xs-4 col-md-3"><select class="form-control" name="' + n + '[status]">' +
                '<option value="available"' + (status !== 'full' ? ' selected' : '') + '>Available</option>' +
                '<option value="full"' + (status === 'full' ? ' selected' : '') + '>Full</option></select></div>' +
            '<div class="col-xs-12 col-md-2"><button type="button" class="btn btn-default btn-remove-session">Hapus</button></div>' +
        '</div>';
    }

    function nextDay(value) {
        if (!value) return '';
        var dt = new Date(value + 'T00:00:00');
        dt.setDate(dt.getDate() + 1);
        var m = ('0' + (dt.getMonth() + 1)).slice(-2), day = ('0' + dt.getDate()).slice(-2);
        return dt.getFullYear() + '-' + m + '-' + day;
    }

    document.getElementById('btn-add-date').addEventListener('click', function () {
        var blocks = wrap.querySelectorAll('.date-block');
        var last = blocks[blocks.length - 1];
        var d = nextDate++;
        var date = last ? nextDay(last.querySelector('input[type=date]').value) : '';

        var rows = '', s = 0;
        var src = last ? last.querySelectorAll('.session-row') : [];
        if (src.length) {
            src.forEach(function (row) {
                var inputs = row.querySelectorAll('input');
                rows += sessionHtml(d, s++, inputs[0].value, inputs[1].value, 'available');
            });
        } else {
            rows = sessionHtml(d, s++, 'Sesi 1', '13:00', 'available');
        }

        var block = document.createElement('div');
        block.className = 'date-block';
        block.dataset.d = d;
        block.style.cssText = 'border:1px solid #d2d6de;border-radius:4px;padding:12px;margin-bottom:12px;background:#fafafa;';
        block.innerHTML =
            '<div class="row" style="margin-bottom:10px;">' +
                '<div class="col-xs-8 col-md-4"><input required type="date" class="form-control" name="schedules[' + d + '][date]" value="' + date + '"></div>' +
                '<div class="col-xs-4 col-md-3"><button type="button" class="btn btn-danger btn-remove-date">Hapus Tanggal</button></div>' +
            '</div>' +
            '<div class="sessions" data-next="' + s + '">' + rows + '</div>' +
            '<button type="button" class="btn btn-default btn-sm btn-add-session">+ Tambah Sesi</button>';
        wrap.appendChild(block);
    });

    wrap.addEventListener('click', function (e) {
        var t = e.target;
        var block = t.closest('.date-block');
        if (!block) return;

        if (t.classList.contains('btn-add-session')) {
            var box = block.querySelector('.sessions');
            var s = parseInt(box.dataset.next, 10);
            box.dataset.next = s + 1;
            box.insertAdjacentHTML('beforeend', sessionHtml(block.dataset.d, s, 'Sesi ' + (box.children.length + 1), '', 'available'));
        } else if (t.classList.contains('btn-remove-session')) {
            if (block.querySelectorAll('.session-row').length <= 1) { alert('Setiap tanggal minimal punya satu sesi.'); return; }
            t.closest('.session-row').remove();
        } else if (t.classList.contains('btn-remove-date')) {
            if (wrap.querySelectorAll('.date-block').length <= 1) { alert('Minimal satu tanggal tayang.'); return; }
            block.remove();
        }
    });
})();
</script>
@endpush