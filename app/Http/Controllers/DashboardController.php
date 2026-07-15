<?php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\DownloadLog;
use App\Models\Film;
use App\Models\SubmissionSetting;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isGeneralBuyer()) {
            return redirect()->route('orders.index')
                ->with('warning', 'Akun umum menggunakan halaman landing untuk belanja dan mengelola pesanan.');
        }

        $this->syncClosedSubmissionStatuses();

        if ($user->hasRole('peserta')) {
            return $this->dashboardPeserta();
        } elseif ($user->hasRole(['admin', 'adminsub', 'kurator', 'juri'])) {
            return $this->dashboardAdmin();
        }

        return view('dashboard');
    }

    private function dashboardPeserta()
    {
        $title  = 'Dashboard';
        $userId = Auth::id();

        // Stat cards
        $totalFilm   = Film::where('user_id', $userId)->count();
        $dalamProses = Film::where('user_id', $userId)
            ->whereIn('curation_status', Film::processStatuses())
            ->count();
        $officialSelection = Film::where('user_id', $userId)
            ->where('curation_status', Film::CURATION_APPROVED)
            ->count();
        $ditolak = Film::where('user_id', $userId)
            ->where('curation_status', Film::CURATION_REJECTED)
            ->count();

        // Tabel submission
        $submissions = Film::with(['user'])
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        // Pengumuman terbaru (kosong dulu, nanti sesuaikan modelnya)
        $pengumuman = collect();

        // Pesan terbaru (kosong dulu, nanti sesuaikan modelnya)
        $pesan = collect();

        return view('dashboard', compact(
            'totalFilm',
            'dalamProses',
            'officialSelection',
            'ditolak',
            'submissions',
            'pengumuman',
            'pesan',
            'title'
        ));
    }

    private function dashboardAdmin()
    {
        $title = 'Dashboard';
        $user  = Auth::user();

        // Ambil periode yang sedang aktif
        $activePeriod = SubmissionSetting::current();

        $filmQuery = $this->dashboardFilmQuery($user);

        // Filter periode aktif — berlaku untuk SEMUA role termasuk admin
        if ($activePeriod) {
            $filmQuery->where('submission_setting_id', $activePeriod->id);
        }

        // Filter status berdasarkan role
        if ($user->hasRole('juri')) {
            $filmQuery->where('curation_status', Film::CURATION_APPROVED);
        } elseif ($user->hasRole('kurator')) {
            $filmQuery->whereIn('curation_status', [
                Film::CURATION_VERIFIED,
                Film::CURATION_UNDER_REVIEW,
            ]);
        }

        // Category filter untuk juri
        $categoryQuery = Category::query()->orderBy('name');
        if ($user->hasRole('juri')) {
            if ($user->category_id) {
                $categoryQuery->whereKey($user->category_id);
            } else {
                $categoryQuery->whereRaw('1 = 0');
            }
        }
        $categories = $categoryQuery->get();

        // Hitung stats
        $totalFilm         = (clone $filmQuery)->count();
        $dalamProses       = (clone $filmQuery)->whereIn('curation_status', Film::processStatuses())->count();
        $officialSelection = (clone $filmQuery)->where('curation_status', Film::CURATION_APPROVED)->count();
        $ditolak           = (clone $filmQuery)->where('curation_status', Film::CURATION_REJECTED)->count();
        $winner            = (clone $filmQuery)->whereNotNull('winner_rank')->count();

        $totalDownload   = DownloadLog::where('file', 'ekatalog-2025.pdf')->count();
        $downloadHariIni = DownloadLog::where('file', 'ekatalog-2025.pdf')
            ->whereDate('created_at', today())
            ->count();

        $submissions = (clone $filmQuery)
            ->with(['user.category', 'category', 'submissionSetting'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $pengumuman = collect();
        $pesan      = collect();

        return view('dashboard', compact(
            'totalFilm',
            'dalamProses',
            'officialSelection',
            'ditolak',
            'winner',
            'submissions',
            'pengumuman',
            'pesan',
            'title',
            'totalDownload',
            'downloadHariIni',
            'categories',
            'activePeriod',
        ));
    }

    private function dashboardFilmQuery($user)
    {
        $query = Film::query();

        if ($user->hasRole('juri')) {
            if ($user->category_id) {
                $query->where('category_id', $user->category_id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return $query;
    }
}
