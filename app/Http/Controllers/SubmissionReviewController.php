<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Film;
use App\Models\ReviewRubric;
use App\Models\SubmissionReview;
use App\Models\SubmissionSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubmissionReviewController extends Controller
{
    public function index(Request $request)
    {
        $this->syncClosedSubmissionStatuses();

        $user      = auth()->user();
        $isAdmin   = $user->hasRole(['admin', 'adminsub']);
        $canCurate = $user->hasRole('kurator');
        $canJudge  = $user->hasRole('juri');

        $stage                       = $this->resolveStage($request->input('stage'));
        $selectedSubmissionSettingId = $this->resolveSubmissionSettingId($request);
        $selectedCategoryId          = $this->resolveCategoryId($request);
        $selectedCurationStatus      = $this->resolveCurationStatus($request);
        $statusLabels                = Film::curationStatusLabels();

        // Admin filter official selection → paksa stage jury
        if ($isAdmin && $selectedCurationStatus === Film::CURATION_APPROVED) {
            $stage = ReviewRubric::STAGE_JURY;
        }

        $displayRubric = $this->displayRubric($selectedCategoryId, $stage);

        $query = Film::with([
            'user.category',
            'category.rubrics.groups.items',
            'submissionSetting',
            'juryScores',
            'submissionReviews.reviewer',
            'submissionReviews.scores',
        ]);

        if ($selectedSubmissionSettingId) {
            $query->where('submission_setting_id', $selectedSubmissionSettingId);
        }

        if ($selectedCategoryId) {
            $query->where('category_id', $selectedCategoryId);
        }

        if ($canJudge) {
            if ($user->category_id) {
                $query->where('category_id', $user->category_id);
            } else {
                $query->whereRaw('1 = 0');
            }
            $query->where('curation_status', Film::CURATION_APPROVED);
        } elseif ($canCurate) {
            $reviewableStatuses = Film::curatorReviewableStatuses();

            if ($selectedCurationStatus && in_array($selectedCurationStatus, $reviewableStatuses, true)) {
                $query->where('curation_status', $selectedCurationStatus);
            } else {
                $selectedCurationStatus = null;
                $query->whereIn('curation_status', $reviewableStatuses);
            }

            $statusLabels = array_intersect_key($statusLabels, array_flip($reviewableStatuses));
        } elseif ($selectedCurationStatus) {
            $query->where('curation_status', $selectedCurationStatus);
        }

        $films = $this->sortFilmsByStage(
            $this->attachReviewMetrics($query->latest()->get(), $displayRubric, $stage),
            $stage
        );

        return view('review.index', [
            'title'                       => 'Review Submission',
            'films'                       => $films,
            'submissionPeriods'           => SubmissionSetting::orderByDesc('open_at')->get(),
            'categories'                  => Category::orderBy('sort_order')->orderBy('name')->get(),
            'statusLabels'                => $statusLabels,
            'selectedSubmissionSettingId' => $selectedSubmissionSettingId,
            'selectedCategoryId'          => $selectedCategoryId,
            'selectedCurationStatus'      => $selectedCurationStatus,
            'stage'                       => $stage,
            'stageLabels'                 => ReviewRubric::stageLabels(),
            'displayRubric'               => $displayRubric,
            'rubricItems'                 => $this->rubricItems($displayRubric),
            'isAdmin'                     => $isAdmin,
            'canCurate'                   => $canCurate,
            'canJudge'                    => $canJudge,
        ]);
    }

    public function startCuration(Request $request)
    {
        abort_unless(auth()->user()->hasRole(['admin', 'adminsub']), 403);

        $this->syncClosedSubmissionStatuses();

        $validated = $request->validate([
            'submission_setting_id' => 'required|exists:submission_settings,id',
            'category_id'           => 'nullable|exists:categories,id',
        ]);

        $period = SubmissionSetting::findOrFail($validated['submission_setting_id']);

        if ($period->close_at && $period->close_at->isFuture()) {
            return back()->with('warning', 'Penentuan hanya bisa dimulai setelah periode submission ditutup.');
        }

        $query = Film::where('submission_setting_id', $period->id)
            ->where('curation_status', Film::CURATION_UNDER_REVIEW);

        if (! empty($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        }

        $count = $query->update([
            'status'          => Film::CURATION_DETERMINATION,
            'curation_status' => Film::CURATION_DETERMINATION,
        ]);

        return back()->with('success', $count . ' film dipindahkan ke status Shortlist.');
    }

    public function setOfficialSelection(Request $request)
    {
        abort_unless(auth()->user()->hasRole(['admin', 'adminsub']), 403);

        $this->syncClosedSubmissionStatuses();

        $validated = $request->validate([
            'submission_setting_id' => 'required|exists:submission_settings,id',
            'category_id'           => 'required|exists:categories,id',
            'film_ids'              => 'nullable|array',
            'film_ids.*'            => 'integer|exists:films,id',
        ]);

        $eligibleIds = Film::where('submission_setting_id', $validated['submission_setting_id'])
            ->where('category_id', $validated['category_id'])
            ->whereIn('curation_status', [Film::CURATION_DETERMINATION, Film::CURATION_APPROVED])
            ->pluck('id');

        $selectedIds = collect($validated['film_ids'] ?? [])
            ->map(function ($id) {
                return (int) $id;
            })
            ->intersect($eligibleIds)
            ->values();

        Film::whereIn('id', $selectedIds)->update([
            'status'          => Film::CURATION_APPROVED,
            'curation_status' => Film::CURATION_APPROVED,
        ]);

        Film::whereIn('id', $eligibleIds->diff($selectedIds))->update([
            'status'          => Film::CURATION_REJECTED,
            'curation_status' => Film::CURATION_REJECTED,
            'winner_rank'     => null,
        ]);

        return back()->with('success', 'Official Selection berhasil diperbarui.');
    }

    public function updateCurationStatus(Request $request, Film $film)
    {
        abort_unless(auth()->user()->hasRole(['admin', 'adminsub']), 403);

        $validated = $request->validate([
            'curation_status' => 'required|string|in:' . implode(',', Film::curationStatuses()),
        ]);

        $attributes = [
            'status'          => $validated['curation_status'],
            'curation_status' => $validated['curation_status'],
        ];

        if ($validated['curation_status'] !== Film::CURATION_APPROVED) {
            $attributes['winner_rank'] = null;
        }

        $film->update($attributes);

        return back()->with('success', 'Status film berhasil diperbarui.');
    }

    public function score(Film $film, $stage)
    {
        $this->syncClosedSubmissionStatuses();

        $stage = $this->resolveStage($stage);
        $this->authorizeScoringRole($stage);

        if ($message = $this->scoreBlockReason($film, $stage)) {
            return redirect()->route('review.index')->with('warning', $message);
        }

        $rubric = $this->resolveRubric($film, $stage);

        if (! $rubric) {
            return redirect()->route('review.index')->with('warning', 'Rubrik penilaian kategori ini belum tersedia.');
        }

        $review = SubmissionReview::with('scores')
            ->where('film_id', $film->id)
            ->where('reviewer_id', auth()->id())
            ->where('stage', $stage)
            ->first();

        return view('review.score', [
            'title'      => 'Form Penilaian',
            'film'       => $film->loadMissing(['category', 'submissionSetting']),
            'stage'      => $stage,
            'stageLabel' => ReviewRubric::stageLabels()[$stage] ?? ucfirst($stage),
            'rubric'     => $rubric,
            'review'     => $review,
        ]);
    }

    public function storeScore(Request $request, Film $film, $stage)
    {
        $this->syncClosedSubmissionStatuses();

        $stage = $this->resolveStage($stage);
        $this->authorizeScoringRole($stage);

        if ($message = $this->scoreBlockReason($film, $stage)) {
            return back()->with('warning', $message);
        }

        $rubric = $this->resolveRubric($film, $stage);

        if (! $rubric) {
            return back()->with('warning', 'Rubrik penilaian kategori ini belum tersedia.');
        }

        $items = $rubric->groups->flatMap(function ($group) {
            return $group->items;
        })->values();

        $allowedScoreKeys = $items->pluck('id')->map(function ($id) {
            return (string) $id;
        })->all();

        $scoreInput         = $request->input('scores', []);
        $submittedScoreKeys = array_map('strval', array_keys(is_array($scoreInput) ? $scoreInput : []));
        $unknownScoreKeys   = array_diff($submittedScoreKeys, $allowedScoreKeys);

        if ($unknownScoreKeys) {
            throw ValidationException::withMessages([
                'scores' => 'Item rubrik tidak valid.',
            ]);
        }

        $rules = [
            'note'   => 'nullable|string',
            'scores' => 'required|array',
        ];

        foreach ($allowedScoreKeys as $itemId) {
            $rules['scores.' . $itemId] = 'required|integer|min:1|max:10';
        }

        $validated = $request->validate($rules, [
            'scores.*.integer' => 'Nilai penilaian harus berupa angka bulat tanpa desimal.',
            'scores.*.min'     => 'Nilai penilaian minimal 1.',
            'scores.*.max'     => 'Nilai penilaian maksimal 10.',
        ]);
        $totalScore = 0;

        DB::transaction(function () use ($film, $stage, $rubric, $items, $validated, &$totalScore) {
            $review = SubmissionReview::updateOrCreate(
                [
                    'film_id'     => $film->id,
                    'reviewer_id' => auth()->id(),
                    'stage'       => $stage,
                ],
                [
                    'review_rubric_id' => $rubric->id,
                    'note'             => $validated['note'] ?? null,
                    'submitted_at'     => now(),
                ]
            );

            $review->scores()->delete();

            foreach ($items as $item) {
                $score          = (int) $validated['scores'][$item->id];
                $weight         = (float) $item->weight;
                $weightedScore  = round($score * $weight, 2);
                $totalScore    += $weightedScore;

                $review->scores()->create([
                    'review_rubric_item_id' => $item->id,
                    'item_title'            => $item->title,
                    'item_weight'           => $weight,
                    'score'                 => $score,
                    'weighted_score'        => $weightedScore,
                ]);
            }

            $review->update(['total_score' => $totalScore]);
        });

        return redirect()
            ->route('review.index', [
                'submission_setting_id' => $film->submission_setting_id,
                'category_id'           => $film->category_id,
                'stage'                 => $stage,
            ])
            ->with('success', 'Penilaian berhasil disimpan. Total nilai: ' . number_format($totalScore, 2));
    }

    public function updateWinnerRank(Request $request, Film $film)
    {
        abort_unless(auth()->user()->hasRole(['admin', 'adminsub']), 403);

        if ($film->curation_status !== Film::CURATION_APPROVED) {
            return back()->with('warning', 'Peringkat hanya bisa ditetapkan untuk film Official Selection.');
        }

        // 1. Validasi request
        $validated = $request->validate([
            'winner_rank' => 'nullable|string',
        ]);

        // 2. Ambil nilai string murni dari array hasil validasi
        $winnerRank = $validated['winner_rank'] ?? null;

        if ($winnerRank) {
            $exists = Film::where('submission_setting_id', $film->submission_setting_id)
                ->where('category_id', $film->category_id)
                ->where('id', '!=', $film->id)
                ->where('winner_rank', $winnerRank)
                ->exists();

            if ($exists) {
                return back()->withErrors([
                    'winner_rank' => 'Peringkat tersebut sudah dipakai di periode dan kategori yang sama.',
                ]);
            }
        }

        // 3. Update dengan string murni
        $film->update(['winner_rank' => $winnerRank]);

        return back()->with('success', 'Peringkat pemenang berhasil diperbarui.');
    }

    // public function updateNominate(Request $request, Film $film)
    // {
    //     abort_unless(auth()->user()->hasRole(['admin', 'adminsub']), 403);

    //     if ($film->curation_status !== Film::CURATION_APPROVED) {
    //         return back()->with('warning', 'Nominasi hanya bisa ditetapkan untuk film Official Selection.');
    //     }

    //     $validated = $request->validate([
    //         'nominate' => 'nullable|string',
    //     ]);

    //     $newNominate = $validated['nominate'] ?? null;

    //     if ($newNominate) {
    //         // Cegah duplikat di film yang sama
    //         $existing = $film->nominate ?? [];
    //         if (in_array($newNominate, $existing)) {
    //             return back()->withErrors([
    //                 'nominate' => 'Nominasi tersebut sudah ditambahkan untuk film ini.',
    //             ]);
    //         }

    //         // Cegah duplikat antar film dalam periode + kategori yang sama
    //         $existsElsewhere = Film::where('submission_setting_id', $film->submission_setting_id)
    //             ->where('category_id', $film->category_id)
    //             ->where('id', '!=', $film->id)
    //             ->whereJsonContains('nominate', $newNominate)
    //             ->exists();

    //         if ($existsElsewhere) {
    //             return back()->withErrors([
    //                 'nominate' => 'Nominasi tersebut sudah dipakai oleh film lain di periode dan kategori yang sama.',
    //             ]);
    //         }

    //         $existing[] = $newNominate;
    //         $film->update(['nominate' => $existing]);
    //     }

    //     return back()->with('success', 'Nominasi berhasil diperbarui.');
    // }

    protected function resolveStage($stage)
    {
        if (auth()->user()->hasRole('kurator')) {
            return ReviewRubric::STAGE_CURATION;
        }

        if (auth()->user()->hasRole('juri')) {
            return ReviewRubric::STAGE_JURY;
        }

        if (! $stage) {
            return ReviewRubric::STAGE_CURATION;
        }

        abort_unless(in_array($stage, ReviewRubric::stages(), true), 404);

        return $stage;
    }

    protected function resolveSubmissionSettingId(Request $request)
    {
        if ($request->query->has('submission_setting_id')) {
            return $request->input('submission_setting_id') ?: null;
        }

        return optional(SubmissionSetting::current())->getKey();
    }

    protected function resolveCategoryId(Request $request)
    {
        if (auth()->user()->hasRole('juri')) {
            return auth()->user()->category_id ?: null;
        }

        return $request->input('category_id') ?: null;
    }

    protected function resolveCurationStatus(Request $request)
    {
        if (auth()->user()->hasRole('juri')) {
            return Film::CURATION_APPROVED;
        }

        if ($request->query->has('curation_status')) {
            return $request->input('curation_status') ?: null;
        }

        return null;
    }

    protected function authorizeScoringRole($stage)
    {
        if ($stage === ReviewRubric::STAGE_CURATION) {
            abort_unless(auth()->user()->hasRole('kurator'), 403);
            return;
        }

        abort_unless(auth()->user()->hasRole('juri'), 403);
    }

    protected function scoreBlockReason(Film $film, $stage)
    {
        if (
            $stage === ReviewRubric::STAGE_CURATION
            && ! in_array($film->curation_status, Film::curatorReviewableStatuses(), true)
        ) {
            return 'Kurator hanya dapat menilai film yang masih berstatus Verified atau Under Review.';
        }

        if ($stage === ReviewRubric::STAGE_JURY && $film->curation_status !== Film::CURATION_APPROVED) {
            return 'Hanya film Official Selection yang dapat dinilai juri.';
        }

        if ($stage === ReviewRubric::STAGE_JURY && ! auth()->user()->category_id) {
            return 'Akun juri ini belum memiliki kategori penilaian.';
        }

        if (
            $stage === ReviewRubric::STAGE_JURY
            && (int) $film->category_id !== (int) auth()->user()->category_id
        ) {
            return 'Juri hanya dapat menilai film Official Selection sesuai kategori yang ditugaskan.';
        }

        return null;
    }

    protected function resolveRubric(Film $film, $stage)
    {
        $film->loadMissing('category');

        if (! $film->category) {
            return null;
        }

        return $film->category->activeRubric($stage);
    }

    protected function displayRubric($categoryId, $stage)
    {
        if (! $categoryId) {
            return null;
        }

        $category = Category::find($categoryId);

        return $category ? $category->activeRubric($stage) : null;
    }

    protected function rubricItems(ReviewRubric $rubric = null)
    {
        if (! $rubric) {
            return collect();
        }

        return $rubric->groups->flatMap(function ($group) {
            return $group->items;
        })->values();
    }

    protected function attachReviewMetrics($films, ReviewRubric $displayRubric = null, $stage = null)
    {
        $rubricItems = $this->rubricItems($displayRubric);

        return $films->map(function ($film) use ($rubricItems, $stage) {
            $curationReviews = $film->submissionReviews->where('stage', ReviewRubric::STAGE_CURATION);
            $juryReviews     = $film->submissionReviews->where('stage', ReviewRubric::STAGE_JURY);

            $film->curation_average_score = round((float) $curationReviews->sum('total_score'), 2);
            $film->curation_review_count  = $curationReviews->count();
            $film->jury_average_score     = $juryReviews->count()
                ? round((float) $juryReviews->sum('total_score'), 2)
                : round((float) $film->juryScores->sum('score'), 2);
            $film->jury_review_count     = $juryReviews->count() ?: $film->juryScores->count();
            $film->rubric_item_summaries = $this->itemSummaries($film, $stage, $rubricItems);

            return $film;
        });
    }

    protected function itemSummaries(Film $film, $stage, $items)
    {
        if (! $stage || $items->isEmpty()) {
            return collect();
        }

        $stageReviews = $film->submissionReviews->where('stage', $stage);

        return $items->mapWithKeys(function ($item) use ($stageReviews) {
            $reviewerScores = $stageReviews->map(function ($review) use ($item) {
                $score = $review->scores->firstWhere('review_rubric_item_id', $item->id);

                if (! $score) {
                    return null;
                }

                return [
                    'reviewer'       => optional($review->reviewer)->name ?: 'Reviewer',
                    'score'          => (float) $score->score,
                    'weighted_score' => (float) $score->weighted_score,
                    'total_score'    => (float) $review->total_score,
                ];
            })->filter()->values();

            return [
                $item->id => [
                    'avg_score'          => $reviewerScores->avg('score'),
                    'avg_weighted_score' => $reviewerScores->avg('weighted_score'),
                    'reviewers'          => $reviewerScores,
                ],
            ];
        });
    }

    protected function sortFilmsByStage($films, $stage)
    {
        $scoreKey = $stage === ReviewRubric::STAGE_JURY
            ? 'jury_average_score'
            : 'curation_average_score';

        return $films->sortByDesc(function ($film) use ($scoreKey) {
            return $film->{$scoreKey};
        })->values();
    }
}
