@php
    $submissionSettingForm = $submissionSettingForm ?? null;
    $boardMembers = collect(old('festival_board', optional($submissionSettingForm)->festival_board ?: []));
    $selectedFeaturedFilmIds = collect(old(
        'last_year_featured_film_ids',
        optional($submissionSettingForm)->last_year_featured_film_ids ?: ($defaultFeaturedFilmIds ?? [])
    ))->filter()->map(function ($filmId) {
        return (int) $filmId;
    })->values();
    $timelineItems = collect(old(
        'timeline_items',
        optional($submissionSettingForm)->timeline_items ?: ($defaultTimelineItems ?? [])
    ));
    $statDefaults = $defaultLastYearStats ?? [
        'last_year_stat_film_submitted' => 0,
        'last_year_stat_special_films' => 0,
        'last_year_stat_audience' => 0,
        'last_year_stat_participants' => 0,
    ];

    if ($boardMembers->isEmpty()) {
        $boardMembers = collect([
            ['name' => '', 'title' => '', 'image' => null],
        ]);
    }

    if ($timelineItems->isEmpty()) {
        $timelineItems = collect([
            ['period' => '', 'title' => '', 'description' => '', 'icon' => 'fas fa-inbox'],
        ]);
    }
@endphp

<div class="alert alert-info" style="margin-bottom:18px;">
    <i class="fa fa-info-circle"></i>
    Data di bawah ini akan tampil di halaman landing untuk periode yang sedang dipakai saat ini.
</div>

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
<div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:14px;">
    <h4 style="margin:0;"><b>Festival Board</b></h4>
    <button type="button" class="btn btn-success btn-sm" id="add-board-member">
        <i class="fa fa-plus"></i> Tambah Board
    </button>
</div>
<div id="festival-board-list">
    @foreach($boardMembers as $slot => $member)
    <div class="board-member-item" data-board-member style="border:1px solid #eee; border-radius:10px; padding:14px; margin-bottom:16px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <strong>Board Member</strong>
            <button type="button" class="btn btn-danger btn-xs" data-remove-board-member>
                <i class="fa fa-trash"></i> Hapus
            </button>
        </div>
        <div class="row">
            <div class="col-md-5">
                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" name="festival_board[{{ $slot }}][name]" class="form-control"
                        value="{{ old('festival_board.' . $slot . '.name', data_get($member, 'name')) }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Jabatan / Peran</label>
                    <input type="text" name="festival_board[{{ $slot }}][title]" class="form-control"
                        value="{{ old('festival_board.' . $slot . '.title', data_get($member, 'title')) }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Foto</label>
                    <input type="file" name="festival_board_images[{{ $slot }}]" class="form-control" accept="image/*">
                    @if(data_get($member, 'image') && $submissionSettingForm)
                    <p class="help-block">Foto saat ini: <a href="{{ $submissionSettingForm->mediaUrl(data_get($member, 'image')) }}" target="_blank">Lihat</a></p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<template id="festival-board-template">
    <div class="board-member-item" data-board-member style="border:1px solid #eee; border-radius:10px; padding:14px; margin-bottom:16px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <strong>Board Member</strong>
            <button type="button" class="btn btn-danger btn-xs" data-remove-board-member>
                <i class="fa fa-trash"></i> Hapus
            </button>
        </div>
        <div class="row">
            <div class="col-md-5">
                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" name="festival_board[__INDEX__][name]" class="form-control">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Jabatan / Peran</label>
                    <input type="text" name="festival_board[__INDEX__][title]" class="form-control">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Foto</label>
                    <input type="file" name="festival_board_images[__INDEX__]" class="form-control" accept="image/*">
                </div>
            </div>
        </div>
    </div>
</template>

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
            <label>File Katalog (PDF)</label>
            <input type="file" name="last_year_catalog_file" class="form-control" accept="application/pdf">
            @if(optional($submissionSettingForm)->last_year_catalog_file)
            <p class="help-block">File saat ini: <a href="{{ route('download.ekatalog') }}" target="_blank">Unduh katalog aktif</a></p>
            @elseif(optional($submissionSettingForm)->last_year_catalog_url)
            <p class="help-block">Fallback lama: <a href="{{ optional($submissionSettingForm)->last_year_catalog_url }}" target="_blank">Buka tautan katalog lama</a></p>
            @endif
        </div>
    </div>
</div>

<div class="form-group">
    <label>Film Pilihan untuk Featured Films</label>
    <select name="last_year_featured_film_ids[]" class="form-control select2" multiple size="8" data-placeholder="Pilih film unggulan" style="width: 100%;">
        @foreach(($availableFeaturedFilms ?? collect()) as $film)
        <option value="{{ $film->id }}" {{ $selectedFeaturedFilmIds->contains($film->id) ? 'selected' : '' }}>
            {{ $film->name }}{{ $film->category ? ' - ' . $film->category->name : '' }}{{ $film->submissionSetting ? ' (' . $film->submissionSetting->name . ')' : '' }}
        </option>
        @endforeach
    </select>
    <p class="help-block">Urutan pilihan mengikuti urutan yang disimpan browser. Pilih film yang ingin tampil pada section "Featured Films".</p>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Film Submitted</label>
            <input type="number" min="0" name="last_year_stat_film_submitted" class="form-control"
                value="{{ old('last_year_stat_film_submitted', optional($submissionSettingForm)->last_year_stat_film_submitted ?? $statDefaults['last_year_stat_film_submitted']) }}">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Special Films</label>
            <input type="number" min="0" name="last_year_stat_special_films" class="form-control"
                value="{{ old('last_year_stat_special_films', optional($submissionSettingForm)->last_year_stat_special_films ?? $statDefaults['last_year_stat_special_films']) }}">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Audience</label>
            <input type="number" min="0" name="last_year_stat_audience" class="form-control"
                value="{{ old('last_year_stat_audience', optional($submissionSettingForm)->last_year_stat_audience ?? $statDefaults['last_year_stat_audience']) }}">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Participants</label>
            <input type="number" min="0" name="last_year_stat_participants" class="form-control"
                value="{{ old('last_year_stat_participants', optional($submissionSettingForm)->last_year_stat_participants ?? $statDefaults['last_year_stat_participants']) }}">
        </div>
    </div>
</div>

<hr>
<div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:14px;">
    <h4 style="margin:0;"><b>Timeline Kompetisi Film</b></h4>
    <button type="button" class="btn btn-success btn-sm" id="add-timeline-item">
        <i class="fa fa-plus"></i> Tambah Timeline
    </button>
</div>
<div id="timeline-item-list">
    @foreach($timelineItems as $slot => $item)
    <div class="timeline-item-card" data-timeline-item style="border:1px solid #eee; border-radius:10px; padding:14px; margin-bottom:16px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <strong>Timeline Item</strong>
            <button type="button" class="btn btn-danger btn-xs" data-remove-timeline-item>
                <i class="fa fa-trash"></i> Hapus
            </button>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Periode</label>
                    <input type="text" name="timeline_items[{{ $slot }}][period]" class="form-control"
                        value="{{ old('timeline_items.' . $slot . '.period', data_get($item, 'period')) }}"
                        placeholder="8 Juni - 6 Agustus 2026">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Judul</label>
                    <input type="text" name="timeline_items[{{ $slot }}][title]" class="form-control"
                        value="{{ old('timeline_items.' . $slot . '.title', data_get($item, 'title')) }}"
                        placeholder="Open Submission">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Icon Class</label>
                    <input type="text" name="timeline_items[{{ $slot }}][icon]" class="form-control"
                        value="{{ old('timeline_items.' . $slot . '.icon', data_get($item, 'icon', 'fas fa-inbox')) }}"
                        placeholder="fas fa-inbox">
                </div>
            </div>
        </div>
        <div class="form-group" style="margin-bottom:0;">
            <label>Deskripsi</label>
            <textarea name="timeline_items[{{ $slot }}][description]" class="form-control" rows="3"
                placeholder="Deskripsi singkat timeline">{{ old('timeline_items.' . $slot . '.description', data_get($item, 'description')) }}</textarea>
        </div>
    </div>
    @endforeach
</div>

<template id="timeline-item-template">
    <div class="timeline-item-card" data-timeline-item style="border:1px solid #eee; border-radius:10px; padding:14px; margin-bottom:16px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <strong>Timeline Item</strong>
            <button type="button" class="btn btn-danger btn-xs" data-remove-timeline-item>
                <i class="fa fa-trash"></i> Hapus
            </button>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Periode</label>
                    <input type="text" name="timeline_items[__INDEX__][period]" class="form-control" placeholder="8 Juni - 6 Agustus 2026">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Judul</label>
                    <input type="text" name="timeline_items[__INDEX__][title]" class="form-control" placeholder="Open Submission">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Icon Class</label>
                    <input type="text" name="timeline_items[__INDEX__][icon]" class="form-control" placeholder="fas fa-inbox">
                </div>
            </div>
        </div>
        <div class="form-group" style="margin-bottom:0;">
            <label>Deskripsi</label>
            <textarea name="timeline_items[__INDEX__][description]" class="form-control" rows="3" placeholder="Deskripsi singkat timeline"></textarea>
        </div>
    </div>
</template>
