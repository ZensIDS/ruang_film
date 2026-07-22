@extends('layouts.master')
@section('container')
@php
    $user = auth()->user();
    $isAdmin = $user->hasRole(['admin', 'adminsub']);
    $canCurate = $user->hasRole('kurator');
    $canJudge = $user->hasRole('juri');
    $statusClasses = [
        \App\Models\Film::CURATION_SUBMITTED => 'default',
        \App\Models\Film::CURATION_VERIFIED => 'info',
        \App\Models\Film::CURATION_PENDING => 'warning',
        \App\Models\Film::CURATION_UNDER_REVIEW => 'primary',
        \App\Models\Film::CURATION_APPROVED => 'primary',
        \App\Models\Film::CURATION_REJECTED => 'danger',
        'winner' => 'success',
    ];
    $currentStageLabel = $stageLabels[$stage] ?? ucfirst($stage);
@endphp
<section class="content-header">
    <h1>Review Submission</h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header">
                    <form method="GET" action="{{ route('review.index') }}" class="form-inline">
                        <div class="form-group">
                            <label>Periode</label>
                            <select name="submission_setting_id" class="form-control" style="margin:0 10px;">
                                <option value="">Semua Periode</option>
                                @foreach($submissionPeriods as $period)
                                <option value="{{ $period->id }}" {{ (string) $selectedSubmissionSettingId === (string) $period->id ? 'selected' : '' }}>
                                    {{ $period->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Kategori</label>
                            @if($canJudge)
                            <input type="hidden" name="category_id" value="{{ $selectedCategoryId }}">
                            <p class="form-control-static" style="margin:0 10px;">{{ optional($user->category)->name ?: '-' }}</p>
                            @else
                            <select name="category_id" class="form-control" style="margin:0 10px;">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ (string) $selectedCategoryId === (string) $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                            @endif
                        </div>
                        @if(!$canJudge)
                        <div class="form-group">
                            <label>Status</label>
                            <select name="curation_status" class="form-control" style="margin:0 10px;">
                                <option value="">Semua Status</option>
                                @foreach($statusLabels as $statusValue => $statusLabel)
                                <option value="{{ $statusValue }}" {{ $selectedCurationStatus === $statusValue ? 'selected' : '' }}>
                                    {{ $statusLabel }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        <input type="hidden" name="curation_status" value="{{ \App\Models\Film::CURATION_APPROVED }}">
                        @endif
                        <input type="hidden" name="stage" value="{{ $stage }}">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </form>

                </div>
                <div class="box-body table-responsive">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width:50px;">No</th>
                                <th>Nama Tim/Komunitas Produksi</th>
                                <th>Judul Film</th>
                                <th>Durasi Film</th>
                                <th>Tautan Film</th>
                                {{-- Rubric items hanya admin --}}
                                @if($isAdmin)
                                    @foreach($rubricItems as $item)
                                        <th>{{ $item->title }}</th>
                                    @endforeach
                                @endif
                                {{-- Total Nilai hanya admin --}}
                                @if($isAdmin)
                                    <th>Total Nilai {{ $currentStageLabel }}</th>
                                @endif
                                <th>Nilai Per Reviewer</th>
                                <th>Status & Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($films as $film)
                            @php
                                $curationReviews = $film->submissionReviews->where('stage', \App\Models\ReviewRubric::STAGE_CURATION);
                                $juryReviews     = $film->submissionReviews->where('stage', \App\Models\ReviewRubric::STAGE_JURY);

                                // Juri hanya lihat nilai miliknya sendiri
                                // Kurator hanya lihat nilai kurasi miliknya sendiri
                                // Admin lihat semua
                                if ($isAdmin) {
                                    $visibleCurationReviews = $curationReviews;
                                    $visibleJuryReviews     = $juryReviews;
                                } elseif ($canJudge) {
                                    $visibleCurationReviews = collect();
                                    $visibleJuryReviews     = $juryReviews->where('reviewer_id', $user->id);
                                } elseif ($canCurate) {
                                    $visibleCurationReviews = $curationReviews->where('reviewer_id', $user->id);
                                    $visibleJuryReviews     = collect();
                                } else {
                                    $visibleCurationReviews = collect();
                                    $visibleJuryReviews     = collect();
                                }

                                $statusClass    = $statusClasses[$film->display_status] ?? 'default';
                                $currentAverage = $stage === \App\Models\ReviewRubric::STAGE_JURY ? $film->jury_average_score : $film->curation_average_score;
                                $currentCount   = $stage === \App\Models\ReviewRubric::STAGE_JURY ? $film->jury_review_count : $film->curation_review_count;
                                $jam   = floor($film->duration / 3600);
                                $menit = floor(($film->duration % 3600) / 60);
                                $sisa  = $film->duration % 60;
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <strong>{{ $film->user->name ?? '-' }}</strong><br>
                                    <small>{{ $film->user->category->name ?? $film->category->name ?? '-' }}</small>
                                </td>
                                <td>
                                    <strong>{{ $film->name }}</strong><br>
                                    <small>{{ $film->sutradara }}</small>
                                </td>
                                <td>{{ sprintf('%02d:%02d:%02d', $jam, $menit, $sisa) }}</td>
                                <td>
                                    <a href="{{ $film->film }}" target="_blank" class="btn btn-default btn-xs">Film</a>
                                    <a href="{{ $film->trailer }}" target="_blank" class="btn btn-default btn-xs">Trailer</a>
                                    <a href="{{ route('film.show', $film) }}" class="btn btn-info btn-xs">Detail</a>
                                </td>

                                {{-- Rubric item cells hanya admin --}}
                                @if($isAdmin)
                                    @foreach($rubricItems as $item)
                                    @php $summary = $film->rubric_item_summaries->get($item->id); @endphp
                                    <td style="min-width:150px;">
                                        @if($summary && $summary['avg_weighted_score'] !== null)
                                            <strong>{{ number_format((float) $summary['avg_weighted_score'], 2) }}</strong><br>
                                            <small>Skor {{ number_format((float) $summary['avg_score'], 2) }}</small>
                                            <div style="margin-top:4px;">
                                                @foreach($summary['reviewers'] as $reviewerScore)
                                                    <div><small>{{ $reviewerScore['reviewer'] }}: {{ number_format((float) $reviewerScore['weighted_score'], 2) }}</small></div>
                                                @endforeach
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    @endforeach
                                @endif

                                {{-- Total Nilai hanya admin --}}
                                @if($isAdmin)
                                <td>
                                    <strong>{{ number_format($currentAverage, 2) }}</strong><br>
                                    <span class="label label-success">{{ $currentCount }} Reviewer</span>
                                </td>
                                @endif

                                {{-- Nilai Per Reviewer — sesuai role --}}
                                <td style="min-width:260px;">
                                    @if($visibleCurationReviews->count())
                                        {{--  <div><strong>Kurator</strong></div>  --}}
                                        @foreach($visibleCurationReviews as $review)
                                        <div style="margin-bottom:4px;">
                                            {{-- Admin lihat nama reviewer, selain itu tidak --}}
                                            @if($isAdmin)
                                                <strong>{{ $review->reviewer->name ?? 'Kurator' }} : {{ number_format((float) $review->total_score, 2) }}</strong>
                                            @else
                                                <strong>Total Nilai : </strong><strong>{{ number_format((float) $review->total_score, 2) }}<br></strong><span class="label label-success">{{ $currentCount }} Reviewer</span>
                                            @endif
                                            @if($review->note)
                                                <br><small class="text-muted">{{ $review->note }}</small>
                                            @endif
                                        </div>
                                        @endforeach
                                    @endif

                                    @if($visibleJuryReviews->count())
                                        <div style="margin-top:8px;"><strong>Juri</strong></div>
                                        @foreach($visibleJuryReviews as $review)
                                        <div style="margin-bottom:4px;">
                                            @if($isAdmin)
                                                <small>{{ $review->reviewer->name ?? 'Juri' }}: {{ number_format((float) $review->total_score, 2) }}</small>
                                            @else
                                                <small>{{ number_format((float) $review->total_score, 2) }}</small>
                                            @endif
                                            @if($review->note)
                                                <br><small class="text-muted">{{ $review->note }}</small>
                                            @endif
                                        </div>
                                        @endforeach
                                    @endif

                                    @if(!$visibleCurationReviews->count() && !$visibleJuryReviews->count())
                                        -
                                    @endif
                                </td>

                                {{-- Status & Aksi --}}
                                <td style="min-width:220px;">
                                    <span class="label label-{{ $statusClass }}">{{ $film->display_status_label }}</span>

                                    <div style="margin-top:8px;">
                                        @if($canCurate && in_array($film->curation_status, \App\Models\Film::curatorReviewableStatuses(), true))
                                            <a href="{{ route('review.score', [$film, \App\Models\ReviewRubric::STAGE_CURATION]) }}" class="btn btn-warning btn-xs" style="margin-bottom:6px;">
                                                Nilai Kurasi
                                            </a>
                                        @endif

                                        @if($canJudge && $film->curation_status === \App\Models\Film::CURATION_APPROVED && (int) $film->category_id === (int) $user->category_id)
                                            <a href="{{ route('review.score', [$film, \App\Models\ReviewRubric::STAGE_JURY]) }}" class="btn btn-success btn-xs" style="margin-bottom:6px;">
                                                Nilai Juri
                                            </a>
                                        @endif
                                    </div>

                                    @if($isAdmin)
                                    <form action="{{ route('review.status', $film) }}" method="POST" style="margin-top:8px;">
                                        @csrf
                                        @method('PATCH')
                                        <div class="input-group input-group-sm">
                                            <select name="curation_status" class="form-control">
                                                @foreach($statusLabels as $statusValue => $statusLabel)
                                                <option value="{{ $statusValue }}" {{ $film->curation_status === $statusValue ? 'selected' : '' }}>
                                                    {{ $statusLabel }}
                                                </option>
                                                @endforeach
                                            </select>
                                            <span class="input-group-btn">
                                                <button type="submit" class="btn btn-default btn-flat">Ubah</button>
                                            </span>
                                        </div>
                                    </form>

                                    @if($film->curation_status === \App\Models\Film::CURATION_APPROVED)
                                    <form action="{{ route('review.winner-rank', $film) }}" method="POST" style="margin-top:8px;">
                                        @csrf
                                        @method('PATCH')
                                        <div class="input-group input-group-sm">
                                            <select name="winner_rank" class="form-control">
                                                <option value="" disabled {{ old('winner_rank', $film->winner_rank) === null ? 'selected' : '' }}>-- Pilih Juara --</option>
                                                <option value="JUARA 1"        {{ old('winner_rank', $film->winner_rank) == 'JUARA 1'        ? 'selected' : '' }}>JUARA 1</option>
                                                <option value="JUARA 2"        {{ old('winner_rank', $film->winner_rank) == 'JUARA 2'        ? 'selected' : '' }}>JUARA 2</option>
                                                <option value="JUARA 3"        {{ old('winner_rank', $film->winner_rank) == 'JUARA 3'        ? 'selected' : '' }}>JUARA 3</option>
                                                <option value="HARAPAN 1"      {{ old('winner_rank', $film->winner_rank) == 'HARAPAN 1'      ? 'selected' : '' }}>HARAPAN 1</option>
                                                <option value="HARAPAN 2"      {{ old('winner_rank', $film->winner_rank) == 'HARAPAN 2'      ? 'selected' : '' }}>HARAPAN 2</option>
                                                <option value="HARAPAN 3"      {{ old('winner_rank', $film->winner_rank) == 'HARAPAN 3'      ? 'selected' : '' }}>HARAPAN 3</option>
                                                <option value="SPECIAL MENTION" {{ old('winner_rank', $film->winner_rank) == 'SPECIAL MENTION' ? 'selected' : '' }}>SPECIAL MENTION</option>
                                            </select>
                                            <span class="input-group-btn">
                                                <button type="submit" class="btn btn-primary btn-flat">Simpan</button>
                                            </span>
                                        </div>
                                    </form>
                                    @endif
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
