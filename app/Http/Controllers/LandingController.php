<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Faq;
use App\Models\Film;
use App\Models\Jury;
use App\Models\Merchandise;
use App\Models\MerchandiseCategory;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\SubmissionSetting;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    /**
     * Halaman guide book: menampilkan file PDF panduan (public/landing/pdf/guide.pdf)
     * sebagai flipbook (efek buka-buku), sekaligus tetap menyediakan tombol
     * download untuk file PDF aslinya.
     */
    public function guideBook()
    {
        $path = public_path('landing/pdf/guide.pdf');

        abort_unless(file_exists($path), 404, 'File guide book belum tersedia.');

        return view('landing.guide-book', [
            'pdfUrl'  => asset('landing/pdf/guide.pdf'),
            'pdfName' => 'Guide Book.pdf',
        ]);
    }

    public function home()
    {
        $featuredMerchandises = Merchandise::with('category')
            ->active()
            ->latest()
            ->take(6)
            ->get();

        // Tambahan
        $featuredPortalPrograms = Program::with('category')
            ->active()
            ->whereHas('category', function ($query) {
                $query->active()->whereNotIn('id', [1, 2, 3]);
            })
            ->latest()
            ->take(3)
            ->get();

        return view('landing.index', array_merge(
            $this->buildLandingData(),
            [
                'featuredMerchandises'   => $featuredMerchandises,
                'featuredPortalPrograms' => $featuredPortalPrograms,
            ]
        ));
    }

    public function program()
    {
        return view('landing.program', $this->buildLandingData());
    }

    /**
     * Halaman "Konfirmasi Kehadiran" — dituju dari tombol di dashboard saat submission
     * sudah ditutup. Isinya timeline kegiatan, diambil dari periode submission terbaru
     * (SubmissionSetting::current()), sama seperti yang dipakai di halaman landing.
     */
    public function rsvpTimeline()
    {
        $setting = SubmissionSetting::current();

        return view('landing.rsvp-timeline', [
            'landingSetting' => $setting,
            'timelineItems'  => $this->buildTimelineItems($setting),
        ]);
    }

    // Tambahan
    public function portal(Request $request)
    {
        $setting = SubmissionSetting::current();

        $portalPrograms = Program::with('category')
            ->active()
            ->whereHas('category', function ($query) {
                $query->active()->whereNotIn('id', [1, 2, 3]);
            })
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('landing.portal', [
            'activeLandingSetting' => $setting,
            'portalPrograms'       => $portalPrograms,
        ]);
    }
    // Batas Tambahan

    public function merchandise(Request $request)
    {
        $query = Merchandise::with('category')->active();

        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->where(function ($inner) use ($search) {
                $inner->where('name', 'like', '%' . $search . '%')
                    ->orWhere('summary', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('category', function ($inner) use ($request) {
                $inner->where('slug', $request->category);
            });
        }

        if ($request->sort === 'price-asc') {
            $query->orderBy('price');
        } elseif ($request->sort === 'price-desc') {
            $query->orderByDesc('price');
        } else {
            $query->latest();
        }

        $merchandises = $query->paginate(12)->withQueryString();

        return view('landing.merchandise', [
            'merchandises'          => $merchandises,
            'merchandiseCategories' => MerchandiseCategory::where('is_active', true)->orderBy('name')->get(),
            'filters'               => $request->only(['q', 'category', 'sort']),
        ]);
    }

    protected function buildLandingData()
    {
        $this->syncClosedSubmissionStatuses();

        $setting               = SubmissionSetting::current();
        $completedPeriod       = $this->completedPeriod();
        $competitionCategories = $this->buildCompetitionCategories();
        $programCategories     = $this->buildProgramCategories();

        // Juri yang tampil di landing dikelompokkan per kategori kompetisi.
        // Datanya dikelola lewat menu "Juri" (bukan akun login role juri).
        // Kategori yang belum diisi datanya tetap tampil (fallback "misterius" di view).
        $juryCategories = Category::with(['juries' => function ($query) {
                $query->active()->ordered();
            }])
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Kurator tampil di landing tanpa dikelompokkan per kategori.
        $curators = Jury::kurator()->active()->ordered()->get();

        $faqs = Faq::active()->ordered()->get();

        $fallbackLastYearFilms = $this->fallbackLastYearFilms($completedPeriod);
        $lastYearFilms         = $this->buildFeaturedLastYearFilms($setting, $fallbackLastYearFilms);
        $derivedStats          = $this->derivedLastYearStats($completedPeriod);
        $festivalStats         = $this->buildFestivalStats($setting, $derivedStats);

        // Bagian "LATEST CLOSED COMPETITION":
        // 1) Kalau kompetisi (periode) yang baru saja ditutup sudah punya data JUARA (winner_rank),
        //    tampilkan juara periode terbaru itu (aturan lama, prioritas paling tinggi).
        // 2) Kalau belum ada juara tapi sudah ada film yang lolos Official Selection,
        //    tampilkan Official Selection dari periode terbaru itu.
        // 3) Kalau belum ada juara maupun Official Selection sama sekali di periode terbaru,
        //    fallback tampilkan juara dari periode sebelumnya yang terakhir punya data juara.
        $winnerGroups           = collect();
        $winnerSubmissionPeriod = null;
        $winnerDisplayMode      = null; // 'winner' | 'official_selection'

        if ($completedPeriod) {
            if ($this->periodHasWinners($completedPeriod)) {
                $winnerSubmissionPeriod = $completedPeriod;
                $winnerDisplayMode      = 'winner';
                $winnerGroups           = $this->buildWinnerGroups($completedPeriod, 'winner');
            } elseif ($this->periodHasOfficialSelection($completedPeriod)) {
                $winnerSubmissionPeriod = $completedPeriod;
                $winnerDisplayMode      = 'official_selection';
                $winnerGroups           = $this->buildWinnerGroups($completedPeriod, 'official_selection');
            } else {
                $previousWinnerPeriod = $this->latestPreviousPeriodWithWinners($completedPeriod);

                if ($previousWinnerPeriod) {
                    $winnerSubmissionPeriod = $previousWinnerPeriod;
                    $winnerDisplayMode      = 'winner';
                    $winnerGroups           = $this->buildWinnerGroups($previousWinnerPeriod, 'winner');
                }
            }
        }

        return [
            'activeLandingSetting'              => $setting,
            'competitionCategories'             => $competitionCategories,
            'programCategories'                 => $programCategories,
            'juryCategories'                    => $juryCategories,
            'curators'                           => $curators,
            'faqs'                              => $faqs,
            'timelineItems'                     => $this->buildTimelineItems($setting),
            'boardMembers'                      => collect(optional($setting)->festival_board ?: [])->filter(function ($member) {
                return filled(data_get($member, 'name')) || filled(data_get($member, 'title'));
            })->values(),
            'completedSubmissionPeriod'         => $completedPeriod,
            'winnerSubmissionPeriod'            => $winnerSubmissionPeriod,
            'winnerDisplayMode'                 => $winnerDisplayMode,
            'lastYearFilms'                     => $lastYearFilms,
            'winnerGroups'                      => $winnerGroups,
            'festivalStats'                     => $festivalStats,
            'competitionFilmSubmittedStatValue' => (int) data_get($festivalStats->first(), 'value', 0),
        ];
    }

    /**
     * Cek apakah periode submission tertentu sudah punya film dengan winner_rank terisi.
     */
    protected function periodHasWinners(SubmissionSetting $period)
    {
        return Film::where('submission_setting_id', $period->id)
            ->whereNotNull('winner_rank')
            ->exists();
    }

    /**
     * Cek apakah periode submission tertentu sudah punya film Official Selection (approved).
     */
    protected function periodHasOfficialSelection(SubmissionSetting $period)
    {
        return Film::where('submission_setting_id', $period->id)
            ->where('curation_status', Film::CURATION_APPROVED)
            ->exists();
    }

    /**
     * Cari periode submission sebelum $period yang terakhir kali sudah punya data juara.
     */
    protected function latestPreviousPeriodWithWinners(SubmissionSetting $period)
    {
        return SubmissionSetting::where('close_at', '<', $period->close_at)
            ->orderByDesc('close_at')
            ->get()
            ->first(function ($candidate) {
                return $this->periodHasWinners($candidate);
            });
    }

    /**
     * Bangun grup film (per kategori) untuk ditampilkan di section "LATEST CLOSED COMPETITION".
     * $mode: 'winner' -> hanya film dengan winner_rank, diurutkan berdasar ranking.
     *        'official_selection' -> hanya film dengan curation_status approved.
     */
    protected function buildWinnerGroups(SubmissionSetting $period, $mode)
    {
        $query = Film::with(['category', 'user.detail'])
            ->where('submission_setting_id', $period->id);

        if ($mode === 'official_selection') {
            $query->where('curation_status', Film::CURATION_APPROVED);
            $films = $query->get()->sortByDesc('created_at');
        } else {
            $query->whereNotNull('winner_rank');
            $films = Film::sortCollectionByWinnerRank($query->get());
        }

        return $films
            ->groupBy(function ($film) {
                return optional($film->category)->name ?: 'Kategori Lainnya';
            })
            ->map(function ($groupedFilms, $categoryName) use ($mode) {
                return [
                    'category' => (object) ['name' => $categoryName],
                    'films'    => $mode === 'winner'
                        ? Film::sortCollectionByWinnerRank($groupedFilms)
                        : $groupedFilms->values(),
                ];
            })
            ->values();
    }

    protected function buildTimelineItems(SubmissionSetting $setting = null)
    {
        if (! $setting) {
            return collect();
        }

        return collect($setting->timeline_items ?: SubmissionSetting::defaultTimelineItems(
            $setting->open_at,
            $setting->close_at,
            $setting->display_name
        ));
    }

    protected function completedPeriod()
    {
        return SubmissionSetting::where('close_at', '<', now())
            ->orderByDesc('close_at')
            ->first();
    }

    protected function fallbackLastYearFilms(SubmissionSetting $completedPeriod = null)
    {
        if (! $completedPeriod) {
            return collect();
        }

        return Film::sortCollectionByWinnerRank(
            Film::with(['category', 'user.detail'])
                ->where('submission_setting_id', $completedPeriod->id)
                ->whereNotNull('poster')
                ->get()
        )->take(6)->values();
    }

    protected function buildFeaturedLastYearFilms(SubmissionSetting $setting = null, $fallbackLastYearFilms = null)
    {
        $fallbackLastYearFilms = $fallbackLastYearFilms ?: collect();
        $filmIds               = collect(optional($setting)->last_year_featured_film_ids ?: [])
            ->filter()
            ->map(function ($filmId) {
                return (int) $filmId;
            })
            ->values();

        if ($filmIds->isEmpty()) {
            return $fallbackLastYearFilms;
        }

        $order = $filmIds->flip();

        return Film::with(['category', 'user.detail'])
            ->whereIn('id', $filmIds->all())
            ->get()
            ->sortBy(function ($film) use ($order) {
                return $order->get($film->id, PHP_INT_MAX);
            })
            ->take(6)
            ->values();
    }

    protected function derivedLastYearStats(SubmissionSetting $completedPeriod = null)
    {
        if (! $completedPeriod) {
            return [
                'film_submitted' => 0,
                'special_films'  => 0,
                'audience'       => 0,
                'participants'   => 0,
            ];
        }

        return [
            'film_submitted' => Film::where('submission_setting_id', $completedPeriod->id)->count(),
            'special_films'  => Film::where('submission_setting_id', $completedPeriod->id)
                ->whereIn('curation_status', [Film::CURATION_APPROVED, Film::CURATION_REJECTED])
                ->count(),
            'audience'       => 0,
            'participants'   => Film::where('submission_setting_id', $completedPeriod->id)
                ->distinct()
                ->count('user_id'),
        ];
    }

    protected function buildFestivalStats(SubmissionSetting $setting = null, array $derivedStats = [])
    {
        return collect([
            [
                'label'  => 'Film Submitted',
                'value'  => optional($setting)->last_year_stat_film_submitted ?? ($derivedStats['film_submitted'] ?? 0),
                'suffix' => '',
            ],
            [
                'label'  => 'Special Films',
                'value'  => optional($setting)->last_year_stat_special_films ?? ($derivedStats['special_films'] ?? 0),
                'suffix' => '+',
            ],
            [
                'label'  => 'Audience',
                'value'  => optional($setting)->last_year_stat_audience ?? ($derivedStats['audience'] ?? 0),
                'suffix' => '+',
            ],
            [
                'label'  => 'Participants',
                'value'  => optional($setting)->last_year_stat_participants ?? ($derivedStats['participants'] ?? 0),
                'suffix' => '',
            ],
        ]);
    }

    protected function buildCompetitionCategories()
    {
        return collect([
            (object) [
                'name'                  => 'Umum Nasional',
                'resolved_summary'      => 'Kompetisi film horor terbuka bagi sineas Indonesia dari berbagai latar belakang.',
                'image_url'             => asset('landing/images/kategori/UMUM.png'),
                'resolved_detail_route' => '/umum',
            ],
            (object) [
                'name'                  => 'Pelajar Se - Jawa Timur',
                'resolved_summary'      => 'Kompetisi film horor bagi pelajar SMA/SMK wilayah provinsi Jawa Timur.',
                'image_url'             => asset('landing/images/kategori/PELAJAR REGIONAL.png'),
                'resolved_detail_route' => '/pelajar',
            ],
            (object) [
                'name'                  => 'Ekshibisi Lokal Pacitan',
                'resolved_summary'      => "Kompetisi film horor bagi :\n- Organisasi PKK, Karang Taruna & Komunitas Lokal Pacitan\n- Pelajar SMP se-Kabupaten Pacitan",
                'image_url'             => asset('landing/images/kategori/EKSIBISI.png'),
                'resolved_detail_route' => '/ekshibisi',
            ],
        ]);
    }

    protected function buildProgramCategories()
    {
        return ProgramCategory::active()
            ->whereIn('id', [1, 2, 3])
            ->with(['programs' => function ($query) {
                $query->active()->ordered();
            }])
            ->ordered()
            ->get();
    }
}