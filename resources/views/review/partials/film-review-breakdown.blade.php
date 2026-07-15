@php
    $reviewStageLabels = $reviewStageLabels ?? \App\Models\ReviewRubric::stageLabels();
    $rubricsByStage = optional($film->category)->rubrics ? $film->category->rubrics->keyBy('stage') : collect();
    $isReviewAdmin = auth()->user()->hasRole(['admin', 'adminsub']);
@endphp

<div style="background:#fff; border:0.5px solid #e0e0e0; border-radius:10px; padding:16px; margin-top:16px;">
    <div style="font-size:13px; font-weight:600; border-left:3px solid #1db9a0; padding-left:10px; margin-bottom:14px;">Rekap Penilaian</div>

    @foreach($reviewStageLabels as $reviewStage => $reviewStageLabel)
    @php
        $reviews = $film->submissionReviews->where('stage', $reviewStage);

        if ($reviewStage === \App\Models\ReviewRubric::STAGE_JURY && !$isReviewAdmin) {
            $reviews = auth()->user()->hasRole('juri')
                ? $reviews->where('reviewer_id', auth()->id())
                : collect();
        }

        $rubric = $rubricsByStage->get($reviewStage);
        $items = $rubric ? $rubric->groups->flatMap(function ($group) {
            return $group->items;
        })->values() : collect();
    @endphp

    <div style="margin-bottom:18px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <strong>{{ $reviewStageLabel }}</strong>
            <span class="text-muted">
                {{ $reviews->count() }} reviewer
                @if($reviews->count())
                / rata-rata {{ number_format((float) $reviews->avg('total_score'), 2) }}
                @endif
            </span>
        </div>

        @if($reviews->count())
        <div class="table-responsive">
            <table class="table table-bordered table-striped" style="margin-bottom:0;">
                <thead>
                    <tr>
                        <th>Reviewer</th>
                        @foreach($items as $item)
                        <th>{{ $item->title }}</th>
                        @endforeach
                        <th>Total</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reviews as $review)
                    @php
                        $scoresByItem = $review->scores->keyBy('review_rubric_item_id');
                    @endphp
                    <tr>
                        <td>{{ $review->reviewer->name ?? 'Reviewer' }}</td>
                        @foreach($items as $item)
                        @php
                            $score = $scoresByItem->get($item->id);
                        @endphp
                        <td>
                            @if($score)
                            <strong>{{ number_format((float) $score->weighted_score, 2) }}</strong><br>
                            <small>Skor {{ number_format((float) $score->score, 2) }} x {{ number_format((float) $score->item_weight, 2) }}</small>
                            @else
                            -
                            @endif
                        </td>
                        @endforeach
                        <td><strong>{{ number_format((float) $review->total_score, 2) }}</strong></td>
                        <td>{{ $review->note ?: '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-muted">-</div>
        @endif
    </div>
    @endforeach
</div>
