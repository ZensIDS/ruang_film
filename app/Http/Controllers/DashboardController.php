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
        } elseif ($user->hasRole(['adminmerch'])) {
            return redirect(route('admin.orders.index'));
        } elseif ($user->hasRole(['viewer'])) {
            return redirect(route('film.index'));
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

        // Tabel submission sekarang diambil lewat AJAX (server-side DataTables) di data(),
        // supaya halaman dashboard tidak perlu me-load seluruh submission sekaligus.

        // Pengumuman terbaru (kosong dulu, nanti sesuaikan modelnya)
        $pengumuman = collect();

        // Pesan terbaru (kosong dulu, nanti sesuaikan modelnya)
        $pesan = collect();

        return view('dashboard', compact(
            'totalFilm',
            'dalamProses',
            'officialSelection',
            'ditolak',
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

        // Tabel submission sekarang diambil lewat AJAX (server-side DataTables) di data(),
        // supaya halaman dashboard tidak perlu me-load seluruh submission sekaligus.

        $pengumuman = collect();
        $pesan      = collect();

        return view('dashboard', compact(
            'totalFilm',
            'dalamProses',
            'officialSelection',
            'ditolak',
            'winner',
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

    /**
     * Endpoint AJAX untuk DataTables server-side processing di tabel submission dashboard.
     * Menerapkan filter role/period yang sama seperti dashboardAdmin()/dashboardPeserta(),
     * tapi hanya mengambil baris yang sedang ditampilkan (bukan semua data sekaligus).
     */
    public function data(\Illuminate\Http\Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole('peserta')) {
            $query = Film::query()->where('user_id', $user->id);
        } else {
            $query = $this->dashboardFilmQuery($user);

            $activePeriod = SubmissionSetting::current();
            if ($activePeriod) {
                $query->where('submission_setting_id', $activePeriod->id);
            }

            if ($user->hasRole('juri')) {
                $query->where('curation_status', Film::CURATION_APPROVED);
            } elseif ($user->hasRole('kurator')) {
                $query->whereIn('curation_status', [
                    Film::CURATION_VERIFIED,
                    Film::CURATION_UNDER_REVIEW,
                ]);
            }
        }

        $query->with(['user:id,name', 'category:id,name']);

        $recordsTotal = (clone $query)->count();

        $search = trim((string) $request->input('search.value'));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sutradara', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $categoryName = trim((string) $request->input('category_name'));
        if ($categoryName !== '') {
            $query->whereHas('category', function ($cq) use ($categoryName) {
                $cq->where('name', $categoryName);
            });
        }

        $recordsFiltered = (clone $query)->count();

        $orderColumnMap = [
            2 => 'category_id',
            3 => 'duration',
            4 => 'created_at',
        ];
        $orderColumnIndex = (int) $request->input('order.0.column', 4);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderColumn = $orderColumnMap[$orderColumnIndex] ?? 'created_at';
        $query->orderBy($orderColumn, $orderDir)->orderByDesc('id');

        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        if ($length > 0) {
            $query->skip($start)->take($length);
        }

        $films = $query->get();
        $viewer = auth()->user();

        $data = $films->values()->map(function (Film $film, $i) use ($start, $viewer) {
            $s = $film->statusBadgeFor($viewer);

            $detik = (int) $film->duration;
            $duration = sprintf('%02d:%02d:%02d', floor($detik / 3600), floor(($detik % 3600) / 60), $detik % 60);

            $posterHtml = $film->poster
                ? '<img src="' . e($film->poster_url) . '" style="width:80px;height:104px;border-radius:5px;object-fit:cover;flex-shrink:0;">'
                : '<div style="width:80px;height:104px;border-radius:5px;background:#ddd;display:flex;align-items:center;justify-content:center;font-size:10px;color:#999;flex-shrink:0;">N/A</div>';

            $judul = '<div style="display:flex;align-items:center;gap:10px;">' . $posterHtml
                . '<div><div style="font-weight:600;">' . e($film->name) . '</div>'
                . '<div style="color:#888;font-size:11px;">Peserta: ' . e($film->user->name ?? '-') . '</div>'
                . '<div style="color:#aaa;font-size:11px;">Sutradara: ' . e($film->sutradara) . '</div></div></div>';

            $createdAt = optional($film->created_at);
            $tanggal = $createdAt->timestamp
                ? '<div>' . $createdAt->format('d M Y') . '</div><div style="color:#aaa;font-size:11px;">' . $createdAt->format('H:i') . ' WIB</div>'
                : '-';

            $status = '<span style="background:' . $s['bg'] . ';color:' . $s['color'] . ';padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;white-space:nowrap;">'
                . e($s['label']) . '</span>';

            $aksi = '<a href="' . route('film.show', $film->id) . '" style="border:1px solid #ddd;background:#fff;color:#555;border-radius:6px;padding:5px 11px;font-size:12px;text-decoration:none;display:inline-flex;align-items:center;gap:3px;">Lihat Detail &rsaquo;</a>';

            return [
                'DT_RowId' => 'submission-' . $film->id,
                'no' => $start + $i + 1,
                'judul' => $judul,
                'kategori' => $film->category->name ?? '-',
                'durasi' => $duration,
                'tanggal' => $tanggal,
                'status' => $status,
                'aksi' => $aksi,
            ];
        });

        return response()->json([
            'draw' => (int) $request->input('draw', 1),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }
}