<?php
namespace App\Http\Controllers;

use App\Models\Film;
use App\Models\Merchandise;
use App\Models\MerchandiseCategory;
use App\Models\ProgramCategory;
use App\Models\Program;
use App\Models\SubmissionSetting;
use App\Models\User;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function home()
    {
        $featuredMerchandises = Merchandise::with('category')
            ->active()
            ->latest()
            ->take(6)
            ->get();

        return view('landing.index', array_merge(
            $this->buildLandingData(),
            ['featuredMerchandises' => $featuredMerchandises]
        ));
    }

    public function program()
    {
        return view('landing.program', $this->buildLandingData());
    }

    // Tambahan
    public function portal(Request $request)
    {
        $setting = SubmissionSetting::current();

        $portalPrograms = Program::with('category')
            ->active()
            ->whereHas('category', function ($query) {
                $query->active();
            })
            ->ordered()
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

        $juryMembers = User::with('category')
            ->where('role', 'juri')
            ->orderBy('name')
            ->get();

        $fallbackLastYearFilms = $this->fallbackLastYearFilms($completedPeriod);
        $lastYearFilms         = $this->buildFeaturedLastYearFilms($setting, $fallbackLastYearFilms);
        $winnerGroups          = collect();
        $derivedStats          = $this->derivedLastYearStats($completedPeriod);
        $festivalStats         = $this->buildFestivalStats($setting, $derivedStats);

        if ($completedPeriod) {
            $winnerFilms = Film::sortCollectionByWinnerRank(
                Film::with(['category', 'user.detail'])
                    ->where('submission_setting_id', $completedPeriod->id)
                    ->whereNotNull('winner_rank')
                    ->get()
            )
                ->groupBy(function ($film) {
                    return optional($film->category)->name ?: 'Kategori Lainnya';
                });

            $winnerGroups = $winnerFilms
                ->map(function ($films, $categoryName) {
                    return [
                        'category' => (object) ['name' => $categoryName],
                        'films'    => Film::sortCollectionByWinnerRank($films),
                    ];
                })
                ->values();
        }

        return [
            'activeLandingSetting'              => $setting,
            'competitionCategories'             => $competitionCategories,
            'programCategories'                 => $programCategories,
            'juryMembers'                       => $juryMembers,
            'timelineItems'                     => $this->buildTimelineItems($setting),
            'boardMembers'                      => collect(optional($setting)->festival_board ?: [])->filter(function ($member) {
                return filled(data_get($member, 'name')) || filled(data_get($member, 'title'));
            })->values(),
            'completedSubmissionPeriod'         => $completedPeriod,
            'winnerSubmissionPeriod'            => $completedPeriod,
            'lastYearFilms'                     => $lastYearFilms,
            'winnerGroups'                      => $winnerGroups,
            'festivalStats'                     => $festivalStats,
            'competitionFilmSubmittedStatValue' => (int) data_get($festivalStats->first(), 'value', 0),
        ];
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
                'resolved_summary'      => "Kompetisi film horor bagi :\n- Organisasi (PKK - PAUD & TK, dan Karang Taruna) & Komunitas Lokal Pacitan\n- Pelajar SD - SMP Pacitan",
                'image_url'             => asset('landing/images/kategori/EKSIBISI.png'),
                'resolved_detail_route' => '/ekshibisi',
            ],
        ]);
    }

    protected function buildProgramCategories()
    {
        return ProgramCategory::active()
            ->with(['programs' => function ($query) {
                $query->active()->ordered();
            }])
            ->ordered()
            ->get();
    }
}
