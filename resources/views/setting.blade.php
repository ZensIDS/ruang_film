@extends('layouts.master')
@section('container')
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><b>Setting Submission & Pembayaran</b></h3>
                </div>

                @if(session('success'))
                <div class="alert alert-success" style="margin:16px 16px 0;">
                    {{ session('success') }}
                </div>
                @endif

                <div style="margin:16px 16px 0;">
                    @if(!$setting)
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i>
                        Belum ada periode submission yang dibuat.
                    </div>
                    @elseif($submissionOpen)
                    <div class="alert alert-success">
                        <i class="fa fa-check-circle"></i>
                        <strong>Submission sedang berlangsung.</strong>
                        Periode aktif: <strong>{{ $setting->name }}</strong>,
                        dibuka {{ $setting->open_at->translatedFormat('d F Y, H:i') }} WIB dan
                        ditutup {{ $setting->close_at->translatedFormat('d F Y, H:i') }} WIB.
                    </div>
                    @elseif($isBeforeOpen)
                    <div class="alert alert-info">
                        <i class="fa fa-clock-o"></i>
                        <strong>Periode berikutnya belum dibuka.</strong>
                        Periode <strong>{{ $setting->name }}</strong> akan dibuka
                        {{ $setting->open_at->translatedFormat('d F Y, H:i') }} WIB.
                    </div>
                    @else
                    <div class="alert alert-danger">
                        <i class="fa fa-lock"></i>
                        <strong>Periode terakhir sudah ditutup.</strong>
                        Periode <strong>{{ $setting->name }}</strong> berakhir
                        {{ $setting->close_at->translatedFormat('d F Y, H:i') }} WIB.
                    </div>
                    @endif
                </div>

                <div class="box-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div style="border:1px solid #eee; border-radius:10px; padding:16px; margin-bottom:18px;">
                                <h4 style="margin-top:0;"><b>Tambah Periode Submission</b></h4>
                                <form action="{{ route('settingStore') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @if($errors->periodForm->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach ($errors->periodForm->all() as $error)
                                            <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif
                                    @include('submission-setting.partials.period-fields', ['submissionSettingForm' => null])
                                    <button type="submit" class="btn btn-primary">Simpan Periode</button>
                                </form>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div style="border:1px solid #eee; border-radius:10px; padding:16px; margin-bottom:18px;">
                                <h4 style="margin-top:0;"><b>Setting Batas Waktu Pembayaran</b></h4>
                                <form action="{{ route('settingGeneralUpdate') }}" method="POST">
                                    @csrf
                                    @if($errors->getBag('default')->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach ($errors->getBag('default')->all() as $error)
                                            <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif
                                    <div class="form-group">
                                        <label>Batas Waktu Pembayaran (jam)</label>
                                        <input type="number" name="payment_due_hours" class="form-control"
                                            value="{{ old('payment_due_hours', $paymentDueHours) }}"
                                            min="1" max="168" required>
                                        <p class="help-block">Batas waktu dihitung dari waktu checkout invoice.</p>
                                    </div>
                                    <button type="submit" class="btn btn-success">Simpan Setting Pembayaran</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div style="border:1px solid #eee; border-radius:10px; padding:16px; margin-bottom:18px;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
                            <div>
                                <h4 style="margin:0 0 6px;"><b>Setting Landing Page</b></h4>
                                @if($landingSetting)
                                <p class="text-muted" style="margin:0;">
                                    Menampilkan data untuk periode <strong>{{ $landingSetting->name }}</strong>.
                                </p>
                                @else
                                <p class="text-muted" style="margin:0;">
                                    Buat periode submission terlebih dahulu untuk mulai mengatur landing page.
                                </p>
                                @endif
                            </div>
                            @if($landingSetting)
                            <span class="label label-primary" style="font-size:12px; padding:8px 10px;">
                                {{ $landingSetting->open_at->translatedFormat('d M Y H:i') }} - {{ $landingSetting->close_at->translatedFormat('d M Y H:i') }} WIB
                            </span>
                            @endif
                        </div>

                        @if(!$landingSetting)
                        <div class="alert alert-warning" style="margin-top:16px; margin-bottom:0;">
                            <i class="fa fa-exclamation-triangle"></i>
                            Landing page belum punya data periode untuk ditampilkan.
                        </div>
                        @else
                        <form action="{{ route('settingLandingUpdate', $landingSetting) }}" method="POST" enctype="multipart/form-data" style="margin-top:16px;">
                            @csrf
                            @method('PUT')
                            @if($errors->landingForm->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->landingForm->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                            @include('submission-setting.partials.landing-fields', ['submissionSettingForm' => $landingSetting])
                            <button type="submit" class="btn btn-success">Simpan Setting Landing Page</button>
                        </form>
                        @endif
                    </div>

                    <div style="border-top:1px solid #f0f0f0; padding-top:18px;">
                        <h4><b>Daftar Periode Submission</b></h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Periode</th>
                                        <th>Waktu Buka</th>
                                        <th>Waktu Tutup</th>
                                        <th>Konten Landing</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($submissionPeriods as $period)
                                    @php
                                    $status = now()->between($period->open_at, $period->close_at)
                                        ? ['label' => 'Aktif', 'class' => 'label-success']
                                        : (now()->lt($period->open_at)
                                            ? ['label' => 'Akan Datang', 'class' => 'label-info']
                                            : ['label' => 'Selesai', 'class' => 'label-default']);
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $period->name }}</td>
                                        <td>{{ $period->open_at->translatedFormat('d M Y H:i') }} WIB</td>
                                        <td>{{ $period->close_at->translatedFormat('d M Y H:i') }} WIB</td>
                                        <td>
                                            <small>
                                                Hero: {{ $period->hero_title ? 'Siap' : 'Fallback' }}<br>
                                                Board: {{ count($period->festival_board ?? []) }} orang<br>
                                                Tema: {{ $period->theme_name ?: '-' }}
                                            </small>
                                        </td>
                                        <td><span class="label {{ $status['class'] }}">{{ $status['label'] }}</span></td>
                                        <td>
                                            <a href="{{ route('settingEdit', $period) }}" class="btn btn-warning btn-xs">Edit</a>
                                            <form action="{{ route('settingDestroy', $period) }}" method="POST" style="display:inline-block;"
                                                onsubmit="return confirm('Hapus periode submission ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">Belum ada periode submission.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="box-footer">
                    <a href="{{ route('dashboard') }}" class="btn btn-default">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    (function() {
        const boardList = document.getElementById('festival-board-list');
        const addButton = document.getElementById('add-board-member');
        const template = document.getElementById('festival-board-template');
        const timelineList = document.getElementById('timeline-item-list');
        const addTimelineButton = document.getElementById('add-timeline-item');
        const timelineTemplate = document.getElementById('timeline-item-template');

        if (!boardList || !addButton || !template) {
            return;
        }

        let nextIndex = boardList.querySelectorAll('[data-board-member]').length;

        addButton.addEventListener('click', function() {
            const markup = template.innerHTML.replace(/__INDEX__/g, nextIndex);
            boardList.insertAdjacentHTML('beforeend', markup);
            nextIndex += 1;
        });

        boardList.addEventListener('click', function(event) {
            const removeButton = event.target.closest('[data-remove-board-member]');

            if (!removeButton) {
                return;
            }

            const card = removeButton.closest('[data-board-member]');

            if (card) {
                card.remove();
            }
        });
    })();

    (function() {
        const timelineList = document.getElementById('timeline-item-list');
        const addTimelineButton = document.getElementById('add-timeline-item');
        const timelineTemplate = document.getElementById('timeline-item-template');

        if (!timelineList || !addTimelineButton || !timelineTemplate) {
            return;
        }

        let nextIndex = timelineList.querySelectorAll('[data-timeline-item]').length;

        addTimelineButton.addEventListener('click', function() {
            const markup = timelineTemplate.innerHTML.replace(/__INDEX__/g, nextIndex);
            timelineList.insertAdjacentHTML('beforeend', markup);
            nextIndex += 1;
        });

        timelineList.addEventListener('click', function(event) {
            const removeButton = event.target.closest('[data-remove-timeline-item]');

            if (!removeButton) {
                return;
            }

            const card = removeButton.closest('[data-timeline-item]');

            if (card) {
                card.remove();
            }
        });
    })();
</script>
@endpush
