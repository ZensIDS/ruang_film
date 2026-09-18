@extends('layouts.master')
@section('container')
@php
    $isAdminEditing = $isAdminEditing ?? false;
    $stageReviewers = $stageReviewers ?? collect();
    $existingScores = optional($review)->scores ? $review->scores->keyBy('review_rubric_item_id') : collect();
@endphp
<section class="content-header">
    <h1>
        {{ $stageLabel }}: {{ $film->name }}
        @if($isAdminEditing)
        <small>— sebagai {{ optional($review)->reviewer->name ?? 'Reviewer' }}</small>
        @endif
    </h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-12">

            @if($isAdminEditing)
            <div class="box box-solid box-default">
                <div class="box-body">
                    <form method="GET" action="{{ route('review.score', [$film, $stage]) }}" class="form-inline">
                        <label style="margin-right:8px;">Edit penilaian milik:</label>
                        <div class="input-group input-group-sm" style="min-width:260px;">
                            <select name="reviewer_id" class="form-control" onchange="this.form.submit()">
                                @forelse($stageReviewers as $reviewerOption)
                                <option value="{{ $reviewerOption->id }}" {{ (int) $reviewerId === (int) $reviewerOption->id ? 'selected' : '' }}>
                                    {{ $reviewerOption->name }}
                                </option>
                                @empty
                                <option value="">Belum ada reviewer</option>
                                @endforelse
                            </select>
                        </div>
                    </form>
                    <p class="text-muted" style="margin-top:8px; margin-bottom:0;">
                        Sebagai superadmin, perubahan di bawah ini akan menimpa penilaian yang sudah dikirim reviewer terkait.
                    </p>
                </div>
            </div>
            @endif

            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ $film->category->name ?? '-' }} / {{ $film->submissionSetting->name ?? '-' }}</h3>
                </div>
                <form method="POST" action="{{ route('review.score.update', [$film, $stage]) }}">
                    @csrf
                    @method('PATCH')
                    @if($isAdminEditing)
                    <input type="hidden" name="reviewer_id" value="{{ $reviewerId }}">
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

                        @foreach($rubric->groups as $group)
                        <div style="border:1px solid #d2d6de; padding:15px; margin-bottom:15px;">
                            <h4 style="margin-top:0;">
                                {{ $group->title }}
                                @if($group->weight !== null)
                                <small>Bobot {{ number_format((float) $group->weight, 2) }}</small>
                                @endif
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th style="width:24%;">Sub-Aspek</th>
                                            <th>Indikator</th>
                                            <th style="width:12%;">Bobot</th>
                                            <th style="width:14%;">Skor 1-10</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($group->items as $item)
                                        @php
                                            $savedScore = optional($existingScores->get($item->id))->score;
                                            $displayScore = filled($savedScore) ? (string) (int) round($savedScore) : null;
                                        @endphp
                                        <tr>
                                            <td><strong>{{ $item->title }}</strong></td>
                                            <td>{{ $item->description ?: '-' }}</td>
                                            <td>{{ number_format((float) $item->weight, 2) }}</td>
                                            <td>
                                                <input
                                                    type="text"
                                                    class="form-control js-score-input"
                                                    name="scores[{{ $item->id }}]"
                                                    inputmode="numeric"
                                                    pattern="^(10|[1-9])$"
                                                    maxlength="2"
                                                    title="Nilai harus berupa angka bulat 1 sampai 10."
                                                    autocomplete="off"
                                                    required
                                                    value="{{ old('scores.' . $item->id, $displayScore) }}"
                                                >
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endforeach

                        <div class="form-group">
                            <label>Catatan</label>
                            <textarea name="note" class="form-control" rows="4">{{ old('note', optional($review)->note) }}</textarea>
                        </div>
                    </div>
                    <div class="box-footer">
                        <a href="{{ $isAdminEditing ? route('film.show', $film) : route('review.index', ['submission_setting_id' => $film->submission_setting_id, 'category_id' => $film->category_id, 'stage' => $stage]) }}" class="btn btn-default">Kembali</a>
                        <button type="submit" class="btn btn-primary">Simpan Penilaian</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.js-score-input').forEach(function(input) {
        var validateScoreInput = function(field) {
            field.value = field.value.replace(/\D+/g, '').slice(0, 2);

            if (!field.value) {
                field.setCustomValidity('');
                return;
            }

            var numeric = parseInt(field.value, 10);

            if (Number.isNaN(numeric) || numeric < 1 || numeric > 10) {
                field.setCustomValidity('Nilai harus berupa angka bulat 1 sampai 10.');
                return;
            }

            field.setCustomValidity('');
        };

        input.addEventListener('input', function() {
            validateScoreInput(this);
        });

        input.addEventListener('blur', function() {
            validateScoreInput(this);

            if (!this.checkValidity()) {
                this.reportValidity();
            }
        });

        validateScoreInput(input);
    });
</script>
@endpush