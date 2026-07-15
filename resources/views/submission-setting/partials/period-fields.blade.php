@php
    $submissionSettingForm = $submissionSettingForm ?? null;
    $openAtDate = old('open_at_date', optional(optional($submissionSettingForm)->open_at)->format('Y-m-d'));
    $openAtTime = old('open_at_time', optional(optional($submissionSettingForm)->open_at)->format('H:i'));
    $closeAtDate = old('close_at_date', optional(optional($submissionSettingForm)->close_at)->format('Y-m-d'));
    $closeAtTime = old('close_at_time', optional(optional($submissionSettingForm)->close_at)->format('H:i'));
@endphp

<div class="form-group">
    <label>Nama Periode</label>
    <input type="text" name="name" class="form-control"
        value="{{ old('name', optional($submissionSettingForm)->name) }}"
        placeholder="Contoh: Submission Juli 2026" required>
</div>

<div class="row">
    <div class="col-md-6">
        <label>Waktu Buka</label>
        <div class="row">
            <div class="col-xs-7">
                <div class="form-group">
                    <input type="date" name="open_at_date" class="form-control"
                        value="{{ $openAtDate }}" required>
                </div>
            </div>
            <div class="col-xs-5">
                <div class="form-group">
                    <input type="time" name="open_at_time" class="form-control"
                        value="{{ $openAtTime }}" required>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <label>Waktu Tutup</label>
        <div class="row">
            <div class="col-xs-7">
                <div class="form-group">
                    <input type="date" name="close_at_date" class="form-control"
                        value="{{ $closeAtDate }}" required>
                </div>
            </div>
            <div class="col-xs-5">
                <div class="form-group">
                    <input type="time" name="close_at_time" class="form-control"
                        value="{{ $closeAtTime }}" required>
                </div>
            </div>
        </div>
    </div>
</div>
