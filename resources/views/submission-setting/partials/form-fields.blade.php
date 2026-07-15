@php
    $submissionSettingForm = $submissionSettingForm ?? null;
    $boardMembers = collect(old('festival_board', optional($submissionSettingForm)->festival_board ?: []))->values();
@endphp

<div class="form-group">
    <label>Nama Periode</label>
    <input type="text" name="name" class="form-control"
        value="{{ old('name', optional($submissionSettingForm)->name) }}"
        placeholder="Contoh: Submission Juli 2026" required>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Waktu Buka</label>
            <input type="datetime-local" name="open_at" class="form-control"
                value="{{ old('open_at', optional(optional($submissionSettingForm)->open_at)->format('Y-m-d\TH:i')) }}" required>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Waktu Tutup</label>
            <input type="datetime-local" name="close_at" class="form-control"
                value="{{ old('close_at', optional(optional($submissionSettingForm)->close_at)->format('Y-m-d\TH:i')) }}" required>
        </div>
    </div>
</div>

<hr>
<h4><b>Hero Landing</b></h4>
<div class="form-group">
    <label>Judul Hero</label>
    <textarea name="hero_title" class="form-control" rows="2" placeholder="Gunakan Enter untuk pindah baris">{{ old('hero_title', optional($submissionSettingForm)->hero_title) }}</textarea>
</div>
<div class="form-group">
    <label>Deskripsi Hero</label>
    <textarea name="hero_description" class="form-control" rows="3">{{ old('hero_description', optional($submissionSettingForm)->hero_description) }}</textarea>
</div>
<div class="form-group">
    <label>Background Hero</label>
    <input type="file" name="hero_image" class="form-control" accept="image/*">
    @if(optional($submissionSettingForm)->hero_image)
    <p class="help-block">Gambar saat ini: <a href="{{ $submissionSettingForm->mediaUrl($submissionSettingForm->hero_image) }}" target="_blank">Lihat</a></p>
    @endif
</div>

<hr>
<h4><b>Tentang Festival</b></h4>
<div class="form-group">
    <label>Judul Tentang Festival</label>
    <input type="text" name="about_title" class="form-control"
        value="{{ old('about_title', optional($submissionSettingForm)->about_title) }}">
</div>
<div class="form-group">
    <label>Deskripsi Tentang Festival</label>
    <textarea name="about_description" class="form-control" rows="4">{{ old('about_description', optional($submissionSettingForm)->about_description) }}</textarea>
</div>
<div class="form-group">
    <label>Deskripsi Kedua Tentang Festival</label>
    <textarea name="about_description_secondary" class="form-control" rows="4">{{ old('about_description_secondary', optional($submissionSettingForm)->about_description_secondary) }}</textarea>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Hashtag</label>
            <input type="text" name="hashtag" class="form-control"
                value="{{ old('hashtag', optional($submissionSettingForm)->hashtag) }}"
                placeholder="#FestivalFilmHoror2026">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Gambar Tentang Festival</label>
            <input type="file" name="about_image" class="form-control" accept="image/*">
            @if(optional($submissionSettingForm)->about_image)
            <p class="help-block">Gambar saat ini: <a href="{{ $submissionSettingForm->mediaUrl($submissionSettingForm->about_image) }}" target="_blank">Lihat</a></p>
            @endif
        </div>
    </div>
</div>

<hr>
<h4><b>Tema Festival</b></h4>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Judul Section Tema</label>
            <input type="text" name="theme_title" class="form-control"
                value="{{ old('theme_title', optional($submissionSettingForm)->theme_title) }}"
                placeholder="Tema Festival Film Horor 2026">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Nama Tema</label>
            <input type="text" name="theme_name" class="form-control"
                value="{{ old('theme_name', optional($submissionSettingForm)->theme_name) }}"
                placeholder="INDIGO">
        </div>
    </div>
</div>
<div class="form-group">
    <label>Quote Tema</label>
    <textarea name="theme_quote" class="form-control" rows="2">{{ old('theme_quote', optional($submissionSettingForm)->theme_quote) }}</textarea>
</div>
<div class="form-group">
    <label>Deskripsi Tema</label>
    <textarea name="theme_description" class="form-control" rows="8" placeholder="Pisahkan paragraf dengan baris kosong">{{ old('theme_description', optional($submissionSettingForm)->theme_description) }}</textarea>
</div>
<div class="form-group">
    <label>Gambar Tema</label>
    <input type="file" name="theme_image" class="form-control" accept="image/*">
    @if(optional($submissionSettingForm)->theme_image)
    <p class="help-block">Gambar saat ini: <a href="{{ $submissionSettingForm->mediaUrl($submissionSettingForm->theme_image) }}" target="_blank">Lihat</a></p>
    @endif
</div>

<hr>
<h4><b>Festival Board</b></h4>
<div class="row">
    @foreach(range(0, 2) as $slot)
    @php $member = $boardMembers->get($slot, []); @endphp
    <div class="col-md-4">
        <div style="border:1px solid #eee; border-radius:10px; padding:14px; margin-bottom:16px;">
            <div class="form-group">
                <label>Nama Board {{ $slot + 1 }}</label>
                <input type="text" name="festival_board[{{ $slot }}][name]" class="form-control"
                    value="{{ old('festival_board.' . $slot . '.name', data_get($member, 'name')) }}">
            </div>
            <div class="form-group">
                <label>Jabatan / Peran</label>
                <input type="text" name="festival_board[{{ $slot }}][title]" class="form-control"
                    value="{{ old('festival_board.' . $slot . '.title', data_get($member, 'title')) }}">
            </div>
            <div class="form-group">
                <label>Foto</label>
                <input type="file" name="festival_board_images[{{ $slot }}]" class="form-control" accept="image/*">
                @if(data_get($member, 'image') && $submissionSettingForm)
                <p class="help-block">Foto saat ini: <a href="{{ $submissionSettingForm->mediaUrl(data_get($member, 'image')) }}" target="_blank">Lihat</a></p>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

<hr>
<h4><b>What Happened Last Year</b></h4>
<div class="form-group">
    <label>Judul Highlight</label>
    <input type="text" name="last_year_title" class="form-control"
        value="{{ old('last_year_title', optional($submissionSettingForm)->last_year_title) }}">
</div>
<div class="form-group">
    <label>Deskripsi Highlight</label>
    <textarea name="last_year_description" class="form-control" rows="4">{{ old('last_year_description', optional($submissionSettingForm)->last_year_description) }}</textarea>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Label Tombol Katalog</label>
            <input type="text" name="last_year_catalog_label" class="form-control"
                value="{{ old('last_year_catalog_label', optional($submissionSettingForm)->last_year_catalog_label) }}"
                placeholder="Download Katalog Festival">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>URL / Path Katalog</label>
            <input type="text" name="last_year_catalog_url" class="form-control"
                value="{{ old('last_year_catalog_url', optional($submissionSettingForm)->last_year_catalog_url) }}"
                placeholder="/download/ekatalog atau https://...">
        </div>
    </div>
</div>
