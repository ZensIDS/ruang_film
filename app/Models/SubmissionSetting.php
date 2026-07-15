<?php

namespace App\Models;

use App\Support\PublicMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class SubmissionSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'hero_title',
        'hero_description',
        'hero_image',
        'about_title',
        'about_description',
        'about_description_secondary',
        'about_image',
        'hashtag',
        'theme_title',
        'theme_name',
        'theme_quote',
        'theme_description',
        'theme_image',
        'festival_board',
        'last_year_title',
        'last_year_description',
        'last_year_catalog_label',
        'last_year_catalog_url',
        'last_year_featured_film_ids',
        'last_year_catalog_file',
        'last_year_stat_film_submitted',
        'last_year_stat_special_films',
        'last_year_stat_audience',
        'last_year_stat_participants',
        'timeline_items',
        'open_at',
        'close_at',
    ];

    protected $casts = [
        'open_at' => 'datetime',
        'close_at' => 'datetime',
        'festival_board' => 'array',
        'last_year_featured_film_ids' => 'array',
        'timeline_items' => 'array',
    ];

    public function films()
    {
        return $this->hasMany(Film::class);
    }

    public function getDisplayNameAttribute()
    {
        return $this->name ?: 'Periode ' . optional($this->open_at)->format('F Y');
    }

    public function scopeActive($query)
    {
        $now = now();

        return $query->where('open_at', '<=', $now)
            ->where('close_at', '>=', $now);
    }

    public static function current()
    {
        return static::active()->orderBy('open_at')->first()
            ?: static::where('open_at', '>', now())->orderBy('open_at')->first()
            ?: static::orderByDesc('close_at')->first();
    }

    public static function currentActive()
    {
        return static::active()->orderBy('open_at')->first();
    }

    public static function isOpen()
    {
        return static::currentActive() !== null;
    }

    public static function overlaps($openAt, $closeAt, $ignoreId = null)
    {
        return static::when($ignoreId, function ($query) use ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        })->where(function ($query) use ($openAt, $closeAt) {
            $query->whereBetween('open_at', [$openAt, $closeAt])
                ->orWhereBetween('close_at', [$openAt, $closeAt])
                ->orWhere(function ($inner) use ($openAt, $closeAt) {
                    $inner->where('open_at', '<=', $openAt)
                        ->where('close_at', '>=', $closeAt);
                });
        })->exists();
    }

    public static function resolveForDate($date)
    {
        return static::where('open_at', '<=', $date)
            ->where('close_at', '>=', $date)
            ->orderBy('open_at')
            ->first();
    }

    public function mediaUrl($path, $fallback = null)
    {
        return PublicMedia::url($path, $fallback);
    }

    public static function defaultTimelineItems($openAt = null, $closeAt = null, $displayName = null)
    {
        $openAt = $openAt instanceof Carbon ? $openAt->copy() : ($openAt ? Carbon::parse($openAt) : null);
        $closeAt = $closeAt instanceof Carbon ? $closeAt->copy() : ($closeAt ? Carbon::parse($closeAt) : null);
        $displayName = $displayName ?: 'periode submission ini';

        if (!$openAt || !$closeAt) {
            return [
                [
                    'period' => 'Tanggal menyesuaikan setting',
                    'title' => 'Open Submission',
                    'description' => 'Publikasi dan penjaringan karya film untuk ' . $displayName . '.',
                    'icon' => 'fas fa-inbox',
                ],
                [
                    'period' => 'Tanggal menyesuaikan setting',
                    'title' => 'Kurasi & Seleksi',
                    'description' => 'Kurator memeriksa kelengkapan dan kualitas karya dari tiap kategori.',
                    'icon' => 'fas fa-search',
                ],
                [
                    'period' => 'Tanggal menyesuaikan setting',
                    'title' => 'Official Selection',
                    'description' => 'Pengumuman karya yang lolos ke tahap penjurian.',
                    'icon' => 'fas fa-trophy',
                ],
                [
                    'period' => 'Tanggal menyesuaikan setting',
                    'title' => 'Proses Penjurian',
                    'description' => 'Juri memberi nilai dan menentukan peringkat terbaik di periode ini.',
                    'icon' => 'fas fa-film',
                ],
                [
                    'period' => 'Tanggal menyesuaikan setting',
                    'title' => 'Awarding Festival',
                    'description' => 'Pengumuman pemenang dan perayaan karya terbaik festival.',
                    'icon' => 'fas fa-crown',
                ],
            ];
        }

        $reviewStart = $closeAt->copy()->addDay();
        $reviewEnd = $closeAt->copy()->addDays(7);
        $officialSelection = $closeAt->copy()->addDays(8);
        $juryStart = $closeAt->copy()->addDays(9);
        $juryEnd = $closeAt->copy()->addDays(20);
        $awardingStart = $closeAt->copy()->addDays(22);
        $awardingEnd = $closeAt->copy()->addDays(25);

        return [
            [
                'period' => $openAt->translatedFormat('d M Y') . ' - ' . $closeAt->translatedFormat('d M Y'),
                'title' => 'Open Submission',
                'description' => 'Publikasi dan penjaringan karya film untuk ' . $displayName . '.',
                'icon' => 'fas fa-inbox',
            ],
            [
                'period' => $reviewStart->translatedFormat('d M Y') . ' - ' . $reviewEnd->translatedFormat('d M Y'),
                'title' => 'Kurasi & Seleksi',
                'description' => 'Kurator memeriksa kelengkapan dan kualitas karya dari tiap kategori.',
                'icon' => 'fas fa-search',
            ],
            [
                'period' => $officialSelection->translatedFormat('d M Y'),
                'title' => 'Official Selection',
                'description' => 'Pengumuman karya yang lolos ke tahap penjurian.',
                'icon' => 'fas fa-trophy',
            ],
            [
                'period' => $juryStart->translatedFormat('d M Y') . ' - ' . $juryEnd->translatedFormat('d M Y'),
                'title' => 'Proses Penjurian',
                'description' => 'Juri memberi nilai dan menentukan peringkat terbaik di periode ini.',
                'icon' => 'fas fa-film',
            ],
            [
                'period' => $awardingStart->translatedFormat('d M Y') . ' - ' . $awardingEnd->translatedFormat('d M Y'),
                'title' => 'Awarding ' . $closeAt->format('Y'),
                'description' => 'Pengumuman pemenang dan perayaan karya terbaik festival.',
                'icon' => 'fas fa-crown',
            ],
        ];
    }
}
