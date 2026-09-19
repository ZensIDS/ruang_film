<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Film;
use App\Models\ReviewRubric;
use App\Models\SubmissionReview;
use App\Models\SubmissionSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubmissionReviewController extends Controller
{
    public function index(Request $request)
    {
        $this->syncClosedSubmissionStatuses();

        $data = $this->buildReviewData($request);

        return view('review.index', array_merge($data, [
            'title'             => 'Review Submission',
            'submissionPeriods' => SubmissionSetting::orderByDesc('open_at')->get(),
            'categories'        => Category::orderBy('sort_order')->orderBy('name')->get(),
        ]));
    }

    /**
     * Endpoint AJAX untuk filter & pencarian di halaman Review Submission.
     *
     * Sengaja dipisah dari index(): hanya mengembalikan potongan HTML tabel
     * (partial), bukan halaman penuh, supaya filter periode/kategori/status
     * maupun pencarian tidak perlu reload seluruh halaman — cukup ganti isi
     * tabelnya saja lewat JavaScript (lihat resources/views/review/index.blade.php).
     */
    public function search(Request $request)
    {
        $this->syncClosedSubmissionStatuses();

        $data = $this->buildReviewData($request);

        return view('review.partials.table', $data);
    }

    /**
     * Bangun data (films terpaginasi + metadata) berdasarkan filter &
     * pencarian dari request. Dipakai bareng oleh index() (render halaman
     * penuh) dan search() (render partial untuk AJAX), supaya logikanya
     * cuma ada di satu tempat.
     */
    protected function buildReviewData(Request $request): array
    {
        $user      = auth()->user();
        $isAdmin   = $user->hasRole(['admin', 'adminsub']);
        $canCurate = $user->hasRole('kurator');
        $canJudge  = $user->hasRole('juri');

        $stage                       = $this->resolveStage($request->input('stage'));
        $selectedSubmissionSettingId = $this->resolveSubmissionSettingId($request);
        $selectedCategoryId          = $this->resolveCategoryId($request);
        $selectedCurationStatus      = $this->resolveCurationStatus($request);
        $search                      = $this->resolveSearch($request);
        $statusLabels                = Film::curationStatusLabels();

        // Admin filter official selection → paksa stage jury
        if ($isAdmin && $selectedCurationStatus === Film::CURATION_APPROVED) {
            $stage = ReviewRubric::STAGE_JURY;
        }

        $displayRubric = $this->displayRubric($selectedCategoryId, $stage);

        // Urutan (siapa yang ditampilkan lebih dulu) dihitung LANGSUNG di database lewat
        // subquery skor, bukan di PHP setelah semua film ter-load — supaya urutannya tetap
        // benar walau data sudah dipaginasi (halaman per halaman), bukan diambil sekaligus.
        if ($stage === ReviewRubric::STAGE_JURY) {
            $query = Film::query()->selectRaw(
                'films.*, COALESCE(
                    (SELECT SUM(sr.total_score) FROM submission_reviews sr WHERE sr.film_id = films.id AND sr.stage = ?),
                    (SELECT SUM(js.score) FROM jury_scores js WHERE js.film_id = films.id),
                    0
                ) as review_sort_score',
                [ReviewRubric::STAGE_JURY]
            );
        } else {
            $query = Film::query()->selectRaw(
                'films.*, COALESCE(
                    (SELECT SUM(sr.total_score) FROM submission_reviews sr WHERE sr.film_id = films.id AND sr.stage = ?),
                    0
                ) as review_sort_score',
                [ReviewRubric::STAGE_CURATION]
            );
        }

        if ($selectedSubmissionSettingId) {
            $query->where('films.submission_setting_id', $selectedSubmissionSettingId);
        }

        if ($selectedCategoryId) {
            $query->where('films.category_id', $selectedCategoryId);
        }

        if ($search) {
            // Ditaruh paling akhir dari filter kolom biasa: filter periode/
            // kategori/status di atas jalan dulu sebagai kondisi WHERE atas
            // primary/foreign key film (yang sudah pasti punya index bawaan),
            // baru query di-JOIN untuk kebutuhan pencarian. Dengan begitu
            // JOIN & LIKE hanya "melihat" baris yang sudah dipersempit oleh
            // filter-filter tadi, bukan seluruh tabel films.
            $this->applySearch($query, $search);
        }

        if ($canJudge) {
            if ($user->category_id) {
                $query->where('films.category_id', $user->category_id);
            } else {
                $query->whereRaw('1 = 0');
            }
            $query->where('films.curation_status', Film::CURATION_APPROVED);
        } elseif ($canCurate) {
            $reviewableStatuses = Film::curatorReviewableStatuses();

            if ($selectedCurationStatus && in_array($selectedCurationStatus, $reviewableStatuses, true)) {
                $query->where('films.curation_status', $selectedCurationStatus);
            } else {
                $selectedCurationStatus = null;
                $query->whereIn('films.curation_status', $reviewableStatuses);
            }

            $statusLabels = array_intersect_key($statusLabels, array_flip($reviewableStatuses));
        } elseif ($selectedCurationStatus) {
            $query->where('films.curation_status', $selectedCurationStatus);
        }

        $films = $query
            ->orderByDesc('review_sort_score')
            ->orderByDesc('films.created_at')
            ->orderByDesc('films.id')
            ->paginate(25)
            ->withQueryString();

        // Relasi berat cuma di-load untuk film yang benar-benar tampil di halaman ini
        // (maksimal 25), bukan untuk seluruh data submission.
        $films->getCollection()->load([
            'user.category',
            'category.rubrics.groups.items',
            'submissionSetting',
            'juryScores',
            'submissionReviews.reviewer',
            'submissionReviews.scores',
        ]);

        $films->setCollection(
            $this->attachReviewMetrics($films->getCollection(), $displayRubric, $stage)
        );

        return [
            'films'                       => $films,
            'statusLabels'                => $statusLabels,
            'selectedSubmissionSettingId' => $selectedSubmissionSettingId,
            'selectedCategoryId'          => $selectedCategoryId,
            'selectedCurationStatus'      => $selectedCurationStatus,
            'search'                      => $search,
            'stage'                       => $stage,
            'stageLabels'                 => ReviewRubric::stageLabels(),
            'displayRubric'               => $displayRubric,
            'rubricItems'                 => $this->rubricItems($displayRubric),
            'isAdmin'                     => $isAdmin,
            'canCurate'                   => $canCurate,
            'canJudge'                    => $canJudge,
        ];
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

    /**
     * Bulk update status kurasi: admin mencentang beberapa film di tabel
     * Review Submission lalu mengubah statusnya sekaligus.
     *
     * Aturannya sengaja disamakan dengan updateCurationStatus() (ubah per
     * baris): kolom `status` & `curation_status` diisi bersamaan, dan
     * `winner_rank` dikosongkan kalau status baru bukan Official Selection.
     * Endpoint per baris tetap dipakai apa adanya, tidak diubah.
     */
    public function bulkUpdateCurationStatus(Request $request)
    {
        abort_unless(auth()->user()->hasRole(['admin', 'adminsub']), 403);

        $validated = $request->validate([
            'curation_status' => 'required|string|in:' . implode(',', Film::curationStatuses()),
            'film_ids'        => 'required|array|min:1|max:1000',
            'film_ids.*'      => 'integer',
        ], [
            'film_ids.required' => 'Pilih minimal satu film terlebih dahulu.',
            'film_ids.min'      => 'Pilih minimal satu film terlebih dahulu.',
            'film_ids.max'      => 'Maksimal 1000 film sekali proses.',
        ]);

        $attributes = [
            'status'          => $validated['curation_status'],
            'curation_status' => $validated['curation_status'],
        ];

        if ($validated['curation_status'] !== Film::CURATION_APPROVED) {
            $attributes['winner_rank'] = null;
        }

        $count = Film::whereIn('id', $validated['film_ids'])->update($attributes);

        $statusLabel = Film::curationStatusLabels()[$validated['curation_status']];
        $message     = $count . ' film berhasil diubah statusnya menjadi ' . $statusLabel . '.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'count'   => $count,
            ]);
        }

        return back()->with('success', $message);
    }

    public function score(Request $request, Film $film, $stage)
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

        $isAdmin = auth()->user()->hasRole(['admin', 'adminsub']);

        // Semua penilai (reviewer) yang sudah pernah menilai film ini pada stage
        // ini — dipakai superadmin untuk memilih/berpindah penilaian siapa yang
        // mau diedit.
        $stageReviewers = $isAdmin
            ? SubmissionReview::with('reviewer')
                ->where('film_id', $film->id)
                ->where('stage', $stage)
                ->orderBy('id')
                ->get()
                ->pluck('reviewer')
                ->filter()
                ->unique('id')
                ->values()
            : collect();

        $reviewerId = $this->resolveReviewerId($request, $film, $stage, $isAdmin, $stageReviewers);

        if ($isAdmin && ! $reviewerId) {
            return redirect()
                ->route('film.show', $film)
                ->with('warning', 'Belum ada penilaian ' . ($stage === ReviewRubric::STAGE_CURATION ? 'kurator' : 'juri') . ' untuk film ini yang bisa diedit.');
        }

        $review = SubmissionReview::with(['scores', 'reviewer'])
            ->where('film_id', $film->id)
            ->where('reviewer_id', $reviewerId)
            ->where('stage', $stage)
            ->first();

        return view('review.score', [
            'title'          => 'Form Penilaian',
            'film'           => $film->loadMissing(['category', 'submissionSetting']),
            'stage'          => $stage,
            'stageLabel'     => ReviewRubric::stageLabels()[$stage] ?? ucfirst($stage),
            'rubric'         => $rubric,
            'review'         => $review,
            'isAdminEditing' => $isAdmin,
            'reviewerId'     => $reviewerId,
            'stageReviewers' => $stageReviewers,
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

        $isAdmin = auth()->user()->hasRole(['admin', 'adminsub']);

        if ($isAdmin) {
            $reviewerId = $this->resolveReviewerIdForAdminStore($request, $stage);

            if (! $reviewerId) {
                return back()->with('warning', 'Reviewer tujuan penilaian tidak valid.');
            }
        } else {
            $reviewerId = auth()->id();
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

        DB::transaction(function () use ($film, $stage, $rubric, $items, $validated, $reviewerId, &$totalScore) {
            $review = SubmissionReview::updateOrCreate(
                [
                    'film_id'     => $film->id,
                    'reviewer_id' => $reviewerId,
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

        // Superadmin membuka form ini dari halaman Detail Film (Rekap Penilaian),
        // jadi setelah simpan langsung dikembalikan ke sana lagi — tidak perlu
        // bolak-balik ke daftar Review Submission. Kurator/juri tetap seperti
        // semula, kembali ke daftar Review Submission.
        if ($isAdmin) {
            return redirect()
                ->route('film.show', $film)
                ->with('success', 'Penilaian berhasil disimpan. Total nilai: ' . number_format($totalScore, 2));
        }

        return redirect()
            ->route('review.index', [
                'submission_setting_id' => $film->submission_setting_id,
                'category_id'           => $film->category_id,
                'stage'                 => $stage,
            ])
            ->with('success', 'Penilaian berhasil disimpan. Total nilai: ' . number_format($totalScore, 2));
    }

    /**
     * Tentukan reviewer_id yang penilaiannya sedang dibuka pada form GET.
     * - Kurator/juri: selalu penilaian miliknya sendiri.
     * - Superadmin: dari query string ?reviewer_id=..., divalidasi harus
     *   reviewer yang memang sudah punya penilaian untuk film+stage ini.
     *   Kalau tidak dikirim/tidak valid, jatuh ke reviewer pertama yang ada.
     */
    protected function resolveReviewerId(Request $request, Film $film, $stage, bool $isAdmin, $stageReviewers)
    {
        if (! $isAdmin) {
            return auth()->id();
        }

        $requested = $request->input('reviewer_id');

        if ($requested && $stageReviewers->contains('id', (int) $requested)) {
            return (int) $requested;
        }

        return optional($stageReviewers->first())->id;
    }

    /**
     * Tentukan reviewer_id tujuan saat superadmin menyimpan hasil edit.
     * Wajib dikirim lewat field tersembunyi `reviewer_id` di form, dan harus
     * mengarah ke akun yang benar-benar berperan kurator (stage kurasi) atau
     * juri (stage penjurian) — supaya superadmin tidak bisa menulis skor atas
     * nama akun dengan role yang salah.
     */
    protected function resolveReviewerIdForAdminStore(Request $request, $stage)
    {
        $reviewerId = (int) $request->input('reviewer_id');

        if (! $reviewerId) {
            return null;
        }

        $reviewer = User::find($reviewerId);

        if (! $reviewer) {
            return null;
        }

        $expectedRole = $stage === ReviewRubric::STAGE_CURATION ? 'kurator' : 'juri';

        if (! $reviewer->hasRole($expectedRole)) {
            return null;
        }

        return $reviewer->id;
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

    protected function resolveSearch(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        return $search !== '' ? $search : null;
    }

    /**
     * Terapkan pencarian ke query film — tanpa perlu index/kolom tambahan
     * di database.
     *
     * Supaya tetap ringan walau datanya sudah banyak:
     * - JOIN ke `users` & `user_details` dipakai (bukan whereHas/orWhereHas).
     *   whereHas menghasilkan subquery EXISTS terpisah untuk tiap relasi,
     *   jadi kalau di-OR beberapa relasi sekaligus, MySQL harus mengeksekusi
     *   subquery berkorelasi itu untuk tiap baris kandidat. JOIN cukup sekali
     *   dieksekusi sebagai satu operasi gabungan, jauh lebih murah untuk
     *   tabel besar.
     * - JOIN baru ditambahkan kalau memang ada kata kunci (tidak membebani
     *   query normal ketika user tidak sedang mencari).
     * - Filter periode/kategori/status (kolom yang sudah punya index bawaan
     *   seperti primary/foreign key) tetap dijalankan sebagai kondisi WHERE
     *   biasa, jadi MySQL query planner bisa mempersempit baris lewat kondisi
     *   itu dulu sebelum mengevaluasi LIKE pada hasil JOIN.
     * - Kata kunci minimal 2 karakter, biar tidak memicu scan besar untuk
     *   input 1 huruf yang hasilnya nyaris seluruh tabel.
     */
    protected function applySearch($query, string $search): void
    {
        if (mb_strlen($search) < 2) {
            return;
        }

        $like = '%' . addcslashes($search, '%_\\') . '%';

        $query
            ->leftJoin('users', 'users.id', '=', 'films.user_id')
            ->leftJoin('user_details', 'user_details.user_id', '=', 'users.id')
            ->where(function ($q) use ($like) {
                $q->where('films.name', 'like', $like)
                    ->orWhere('films.sutradara', 'like', $like)
                    ->orWhere('films.produser', 'like', $like)
                    ->orWhere('users.name', 'like', $like)
                    ->orWhere('user_details.community_name', 'like', $like);
            });
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
        $user = auth()->user();

        // Superadmin boleh membuka & mengedit form penilaian kurasi maupun
        // penjurian kapan saja, tanpa terikat role kurator/juri.
        if ($user->hasRole(['admin', 'adminsub'])) {
            return;
        }

        if ($stage === ReviewRubric::STAGE_CURATION) {
            abort_unless($user->hasRole('kurator'), 403);
            return;
        }

        abort_unless($user->hasRole('juri'), 403);
    }

    protected function scoreBlockReason(Film $film, $stage)
    {
        // Superadmin melakukan penyesuaian nilai, bukan penilaian awal — jadi
        // tidak dibatasi oleh status kurasi film seperti kurator/juri biasa.
        if (auth()->user()->hasRole(['admin', 'adminsub'])) {
            return null;
        }

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

}