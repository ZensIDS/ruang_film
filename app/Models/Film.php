<?php

namespace App\Models;

use App\Support\PublicMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Film extends Model
{
    use HasFactory;

    public const STATUS_AUDIENCE_DEFAULT = 'default';
    public const STATUS_AUDIENCE_PARTICIPANT = 'participant';

    public const CURATION_SUBMITTED = 'submitted';
    public const CURATION_VERIFIED = 'verified';
    public const CURATION_UNDER_REVIEW = 'under_review';
    public const CURATION_DETERMINATION = 'determination';
    public const CURATION_PENDING = self::CURATION_DETERMINATION;
    public const CURATION_APPROVED = 'approved';
    public const CURATION_REJECTED = 'rejected';

    protected $guarded = ['id'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'nominate' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function submissionSetting()
    {
        return $this->belongsTo(SubmissionSetting::class);
    }

    public function juryScores()
    {
        return $this->hasMany(JuryScore::class);
    }

    public function submissionReviews()
    {
        return $this->hasMany(SubmissionReview::class);
    }

    public function curationReviews()
    {
        return $this->submissionReviews()->stage(ReviewRubric::STAGE_CURATION);
    }

    public function juryReviews()
    {
        return $this->submissionReviews()->stage(ReviewRubric::STAGE_JURY);
    }

    public function getDisplayStatusAttribute()
    {
        if ($this->winner_rank) {
            return 'winner';
        }

        return $this->curation_status ?: static::CURATION_SUBMITTED;
    }

    public function getWinnerRankLabelAttribute()
    {
        if (!$this->winner_rank) {
            return null;
        }

<<<<<<< HEAD
        return 'Juara ' . $this->winner_rank;
=======
        return '' . $this->winner_rank;
>>>>>>> 9e8c2069fe474883803df35494add3af52868881
    }

    public function getWinnerRankSortValueAttribute()
    {
        return static::extractWinnerRankValue($this->winner_rank);
    }

    public function getStatusBadgeAttribute()
    {
        return $this->statusBadgeFor();
    }

    public function statusBadgeFor($viewer = null)
    {
        $status = $this->displayStatusFor($viewer);
        $palette = [
            static::CURATION_SUBMITTED => ['color' => '#5c6ac4', 'bg' => '#eef1ff'],
            static::CURATION_VERIFIED => ['color' => '#1d4ed8', 'bg' => '#e8f0ff'],
            static::CURATION_UNDER_REVIEW => ['color' => '#0c7c9f', 'bg' => '#e6f7fb'],
            static::CURATION_DETERMINATION => ['color' => '#b87f00', 'bg' => '#fff8e0'],
            static::CURATION_APPROVED => ['color' => '#198754', 'bg' => '#e6f9ef'],
            static::CURATION_REJECTED => ['color' => '#dc3545', 'bg' => '#fde8e8'],
            'winner' => ['color' => '#6f42c1', 'bg' => '#f0ebff'],
        ];

        return array_merge(
            $palette[$status] ?? ['color' => '#888', 'bg' => '#f5f5f5'],
            ['label' => $this->displayStatusLabelFor($viewer)]
        );
    }

    public function getDisplayStatusLabelAttribute()
    {
        return $this->displayStatusLabelFor();
    }

    public function displayStatusFor($viewer = null)
    {
        $status = $this->display_status;

        if (
            $status === static::CURATION_DETERMINATION
            && $this->statusAudience($viewer) === static::STATUS_AUDIENCE_PARTICIPANT
        ) {
            return static::CURATION_UNDER_REVIEW;
        }

        return $status;
    }

    public function displayStatusLabelFor($viewer = null)
    {
        $status = $this->display_status;

        if ($status === 'winner') {
<<<<<<< HEAD
            return $this->winner_rank_label ?: 'Juara';
=======
            return $this->winner_rank_label ?: '';
>>>>>>> 9e8c2069fe474883803df35494add3af52868881
        }

        $audienceStatus = $this->displayStatusFor($viewer);

        return static::curationStatusLabels($this->statusAudience($viewer))[$audienceStatus] ?? ucfirst($audienceStatus);
    }

    public static function curationStatusLabels($audience = self::STATUS_AUDIENCE_DEFAULT)
    {
        $labels = [
            static::CURATION_SUBMITTED => 'Submitted',
            static::CURATION_VERIFIED => 'Verified',
<<<<<<< HEAD
            static::CURATION_UNDER_REVIEW => 'Dalam Kurasi',
=======
            static::CURATION_UNDER_REVIEW => 'Under Review',
>>>>>>> 9e8c2069fe474883803df35494add3af52868881
            static::CURATION_DETERMINATION => 'Shortlist',
            static::CURATION_APPROVED => 'Official Selection',
            static::CURATION_REJECTED => 'Tidak Lolos',
        ];

        if ($audience === static::STATUS_AUDIENCE_PARTICIPANT) {
<<<<<<< HEAD
            $labels[static::CURATION_DETERMINATION] = 'Dalam Kurasi';
=======
            $labels[static::CURATION_DETERMINATION] = 'Under Review';
>>>>>>> 9e8c2069fe474883803df35494add3af52868881
        }

        return $labels;
    }

    public static function curationStatuses()
    {
        return array_keys(static::curationStatusLabels());
    }

    public function averageScore()
    {
        $juryAverage = $this->averageReviewScore(ReviewRubric::STAGE_JURY);

        if ($juryAverage > 0) {
            return $juryAverage;
        }

        return round((float) $this->juryScores()->avg('score'), 2);
    }

    public function averageReviewScore($stage)
    {
        return round((float) $this->submissionReviews()->stage($stage)->avg('total_score'), 2);
    }

    public function reviewCount($stage)
    {
        return $this->submissionReviews()->stage($stage)->count();
    }

    public static function curatorReviewableStatuses()
    {
        return [
            static::CURATION_VERIFIED,
            static::CURATION_UNDER_REVIEW,
        ];
    }

    public static function processStatuses()
    {
        return [
            static::CURATION_SUBMITTED,
            static::CURATION_VERIFIED,
            static::CURATION_UNDER_REVIEW,
            static::CURATION_DETERMINATION,
        ];
    }

    public static function normalizeWinnerRank($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $numericValue = static::extractWinnerRankValue($value);

        return $numericValue ? (string) $numericValue : null;
    }

    public static function extractWinnerRankValue($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $map = [
            'JUARA 1'        => 1,
            'JUARA 2'        => 2,
            'JUARA 3'        => 3,
            'HARAPAN 1'      => 4,
            'HARAPAN 2'      => 5,
            'HARAPAN 3'      => 6,
            'SPECIAL MENTION' => 7,
        ];

        $normalized = strtoupper(trim((string) $value));

        return $map[$normalized] ?? null;
    }

    public static function sortCollectionByWinnerRank($films)
    {
        return collect($films)
            ->sort(function ($filmA, $filmB) {
<<<<<<< HEAD
                $rankA = $filmA->winner_rank_sort_value;
                $rankB = $filmB->winner_rank_sort_value;

                if (($rankA === null) !== ($rankB === null)) {
                    return ($rankA === null) <=> ($rankB === null);
                }

                if (($rankA ?? PHP_INT_MAX) !== ($rankB ?? PHP_INT_MAX)) {
                    return ($rankA ?? PHP_INT_MAX) <=> ($rankB ?? PHP_INT_MAX);
                }

=======
                $rankA = $filmA->winner_rank_sort_value; // Mengambil angka 1-7 dari extractWinnerRankValue
                $rankB = $filmB->winner_rank_sort_value;

                // 1. Amankan yang MEMILIKI JUARA agar selalu berada di paling atas
                if (($rankA === null) !== ($rankB === null)) {
                    // Jika rankA null (true/1) dan rankB ada (false/0), hasil 1 <=> 0 adalah 1 (A digeser ke bawah)
                    return ($rankA === null) <=> ($rankB === null);
                }

                // 2. Urutkan sesama pemilik juara dari angka TERKECIL ke TERBESAR (Juara 1 [1] -> Juara 2 [2] -> Harapan 1 [4])
                if ($rankA !== null && $rankB !== null) {
                    if ($rankA !== $rankB) {
                        return $rankA <=> $rankB; // Urutan naik (Ascending)
                    }
                }

                // 3. Jika sama-sama TIDAK memiliki juara, urutkan berdasarkan tanggal dibuat (Dari TERBARU ke TERLAMA)
>>>>>>> 9e8c2069fe474883803df35494add3af52868881
                $createdAtA = optional($filmA->created_at)->getTimestamp() ?: 0;
                $createdAtB = optional($filmB->created_at)->getTimestamp() ?: 0;

                if ($createdAtA !== $createdAtB) {
                    return $createdAtB <=> $createdAtA;
                }

<<<<<<< HEAD
                return strcmp((string) $filmA->name, (string) $filmB->name);
            })
            ->values();
=======
                // 4. Jika tanggal sama persis, urutkan berdasarkan abjad nama film
                return strcmp((string) $filmA->name, (string) $filmB->name);
            })
            ->values(); // Reset kunci index array agar berurutan kembali dari 0
>>>>>>> 9e8c2069fe474883803df35494add3af52868881
    }

    public static function syncVerifiedStatusesForClosedPeriods()
    {
        return static::query()
            ->where('curation_status', static::CURATION_VERIFIED)
            ->whereHas('submissionSetting', function ($query) {
                $query->where('close_at', '<', now());
            })
            ->update([
                'status' => static::CURATION_UNDER_REVIEW,
                'curation_status' => static::CURATION_UNDER_REVIEW,
            ]);
    }

    public static function syncSubmittedStatusesForClosedPeriods()
    {
        return static::syncVerifiedStatusesForClosedPeriods();
    }

    public function mediaUrl($path, $fallback = null)
    {
        return PublicMedia::url($path, $fallback);
    }

    public function getPosterUrlAttribute()
    {
        return $this->mediaUrl($this->poster, 'landing/images/user.png');
    }

    public function getKruUrlAttribute()
    {
        return $this->mediaUrl($this->kru);
    }

    public function getOther1UrlAttribute()
    {
        return $this->mediaUrl($this->other_1);
    }

    public function getOther2UrlAttribute()
    {
        return $this->mediaUrl($this->other_2);
    }

    public function getGsmFilesAttribute()
    {
        return collect(json_decode($this->gsm ?? '[]', true))
            ->filter()
            ->values()
            ->all();
    }

    public function getGsmUrlsAttribute()
    {
        return collect($this->gsm_files)
            ->map(function ($path) {
                return $this->mediaUrl($path);
            })
            ->values()
            ->all();
    }

    protected function statusAudience($viewer = null)
    {
        if ($viewer && method_exists($viewer, 'hasRole') && $viewer->hasRole('peserta')) {
            return static::STATUS_AUDIENCE_PARTICIPANT;
        }

        return static::STATUS_AUDIENCE_DEFAULT;
    }
}
