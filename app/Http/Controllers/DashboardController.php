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
        } elseif ($user->hasRole(['admin', 'adminsub', 'adminprog', 'kurator', 'juri'])) {
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

        // Judul-judul film milik peserta yang lolos Official Selection,
        // dipakai untuk kartu pengumuman hasil kurasi di dashboard peserta.
        $approvedFilmTitles = Film::where('user_id', $userId)
            ->where('curation_status', Film::CURATION_APPROVED)
            ->pluck('name');

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
            'approvedFilmTitles',
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

            // Tombol Download Template & Upload Surat Orisinalitas Karya,
            // hanya untuk peserta pemilik film yang statusnya Official Selection (approved).
            if ($film->curation_status === Film::CURATION_APPROVED
                && $viewer
                && $viewer->hasRole('peserta')
                && (int) $film->user_id === (int) $viewer->id
            ) {
                $modalId = 'modal-surat-' . $film->id;
                $hasSurat = (bool) $film->originality_letter;

                $status .= '<div style="margin-top:8px;display:flex;flex-direction:column;gap:6px;align-items:stretch;max-width:190px;">';

                $status .= '<a href="#" target="_blank" style="border:1px solid #1a6fa8;background:#fff;color:#1a6fa8;border-radius:6px;padding:5px 10px;font-size:11px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:5px;white-space:nowrap;">'
                    . '<i class="fa fa-download"></i> Download Template</a>';

                if ($hasSurat) {
                    $status .= '<a href="' . e($film->originality_letter_url) . '" target="_blank" style="border:1px solid #1a7a45;background:#e6f9ef;color:#1a7a45;border-radius:6px;padding:5px 10px;font-size:11px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:5px;white-space:nowrap;">'
                        . '<i class="fa fa-check-circle"></i> Lihat Surat</a>';
                }

                $status .= '<button type="button" data-toggle="modal" data-target="#' . $modalId . '" style="border:1px solid #b87f00;background:#fff8e6;color:#b87f00;border-radius:6px;padding:5px 10px;font-size:11px;font-weight:600;white-space:nowrap;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:5px;">'
                    . '<i class="fa fa-upload"></i> ' . ($hasSurat ? 'Ganti Surat' : 'Upload Surat') . '</button>';

                $status .= '</div>';

                // Modal upload (Bootstrap 3, sudah responsive untuk mobile secara default)
                $status .= '<div class="modal fade" id="' . $modalId . '" tabindex="-1" role="dialog" aria-labelledby="' . $modalId . '-label">'
                    . '<div class="modal-dialog" role="document" style="margin:10vh auto;max-width:420px;width:92%;">'
                    . '<div class="modal-content" style="border-radius:10px;overflow:hidden;">'
                    . '<div class="modal-header" style="background:#fff8e6;border-bottom:1px solid #ffe0a0;padding:14px 18px;">'
                    . '<button type="button" class="close" data-dismiss="modal" aria-label="Close" style="font-size:22px;">&times;</button>'
                    . '<h4 class="modal-title" id="' . $modalId . '-label" style="font-size:15px;font-weight:700;color:#7a5000;margin:0;">'
                    . ($hasSurat ? 'Ganti' : 'Upload') . ' Surat Orisinalitas Karya</h4>'
                    . '</div>'
                    . '<form action="' . route('film.originality-letter.store', $film->id) . '" method="POST" enctype="multipart/form-data">'
                    . '<input type="hidden" name="_token" value="' . csrf_token() . '">'
                    . '<div class="modal-body" style="padding:18px;">'
                    . '<p style="font-size:12px;color:#888;margin-bottom:6px;">Film: <b>' . e($film->name) . '</b></p>'
                    . ($hasSurat ? '<p style="font-size:12px;margin-bottom:12px;"><a href="' . e($film->originality_letter_url) . '" target="_blank" style="color:#1a7a45;font-weight:600;"><i class="fa fa-file-text-o"></i> Lihat file yang sudah diupload</a></p>' : '')
                    . '<label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Pilih File (PDF/JPG/PNG, maks 6MB)</label>'
                    . '<input type="file" name="originality_letter" accept=".pdf,.jpg,.jpeg,.png" required style="width:100%;font-size:12px;padding:6px 0;">'
                    . '</div>'
                    . '<div class="modal-footer" style="padding:12px 18px;border-top:1px solid #f0f0f0;display:flex;gap:8px;justify-content:flex-end;">'
                    . '<button type="button" data-dismiss="modal" style="border:1px solid #ddd;background:#fff;color:#555;border-radius:6px;padding:7px 16px;font-size:12px;font-weight:600;cursor:pointer;">Batal</button>'
                    . '<button type="submit" style="border:none;background:#e6a800;color:#fff;border-radius:6px;padding:7px 16px;font-size:12px;font-weight:600;cursor:pointer;">Simpan</button>'
                    . '</div>'
                    . '</form>'
                    . '</div></div></div>';
            }

            $aksi = '<a href="' . route('film.show', $film->id) . '" style="border:1px solid #ddd;background:#fff;color:#555;border-radius:6px;padding:5px 11px;font-size:12px;text-decoration:none;display:inline-flex;align-items:center;gap:3px;"> Detail</a>';

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