<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Film;
use App\Models\ReviewRubric;
use App\Models\SubmissionSetting;
use App\Models\UserDetail;
use App\Support\PosterThumbnail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Exports\FilmsExport;
use Maatwebsite\Excel\Facades\Excel;

class FilmController extends Controller
{
    public function index(Request $request)
    {
        if ($redirect = $this->redirectGeneralBuyerAway()) {
            return $redirect;
        }

        $this->syncClosedSubmissionStatuses();

        if (auth()->user()->hasRole(['kurator', 'juri'])) {
            return redirect()->route('review.index');
        }

        $title = 'Submission';
        $categories = Category::orderBy('sort_order')->orderBy('name')->get();
        $submissionPeriods = collect();
        $statusLabels = Film::curationStatusLabels();
        $selectedSubmissionSettingId = null;
        $selectedCategoryId = null;
        $selectedCurationStatus = null;

        if (auth()->user()->hasRole(['admin', 'adminsub', 'viewer'])) {
            $submissionPeriods = SubmissionSetting::orderByDesc('open_at')->get();
        }

        // Data baris tabel sekarang diambil lewat AJAX (server-side DataTables) di method data(),
        // supaya halaman ini tidak perlu me-load ribuan film sekaligus.
        $selectedSubmissionSettingId = $this->resolveSubmissionSettingId($request);
        $selectedCategoryId = $this->resolveCategoryId($request);
        $selectedCurationStatus = $this->resolveCurationStatus($request);

        return view('film.index', compact(
            'title',
            'categories',
            'submissionPeriods',
            'statusLabels',
            'selectedSubmissionSettingId',
            'selectedCategoryId',
            'selectedCurationStatus'
        ));
    }

    public function create()
    {
        if ($redirect = $this->redirectGeneralBuyerAway()) {
            return $redirect;
        }

        $activeSetting = SubmissionSetting::currentActive();

        if (!$activeSetting) {
            return redirect()->back()->with('warning', 'Submission Telah Ditutup');
        }
        $detail = UserDetail::where('user_id', auth()->id())->first();

        if (!$detail) {
            return redirect()->route('user-detail.index')
                ->with('warning', 'Silakan lengkapi biodata terlebih dahulu sebelum membuat submission.');
        }

        $title = 'Tambah Submission';
        $categories = Category::orderBy('sort_order')->orderBy('name')->get();

        return view('film.create', compact('categories', 'title', 'activeSetting'));
    }

    public function store(Request $request)
    {
        if ($redirect = $this->redirectGeneralBuyerAway()) {
            return $redirect;
        }

        $activeSetting = SubmissionSetting::currentActive();

        if (!$activeSetting) {
            return redirect()->route('film.index')
                ->with('warning', 'Tidak ada periode submission yang aktif.');
        }

        $request->validate([
            'name'           => 'required|string|max:255',
            'duration'       => 'required|integer|min:1|max:1800',
            'tahun_produksi' => 'required|digits:4',
            'subtitle'       => 'required|in:Ya,Tidak',
            'sinopsis'       => 'required|string',
            'sutradara'      => 'required|string|max:255',
            'produser'       => 'required|string|max:255',
            'penulis'        => 'nullable|string|max:255',
            'kru'         => 'required|file|max:6024',
            'poster'         => 'required|image|mimes:jpg,jpeg,png|max:6024',
            'gsm'            => 'required|array|min:1',
            'gsm.*'          => 'required|file|max:30240',
            'trailer'        => 'required|url',
            'film'           => 'required|url',
            'other_1'        => 'nullable|file|max:10240',
            'other_2'        => 'nullable|file|max:10240',
        ], [
            'name.required'           => 'Judul film wajib diisi.',
            'duration.required'       => 'Durasi wajib diisi.',
            'duration.min'            => 'Durasi tidak valid.',
            'duration.max'            => 'Durasi film maksimal 30 menit.',
            'tahun_produksi.required' => 'Tahun produksi wajib diisi.',
            'tahun_produksi.digits'   => 'Tahun produksi harus 4 digit.',
            'subtitle.required'       => 'Subtitle wajib dipilih.',
            'subtitle.in'             => 'Pilihan subtitle tidak valid.',
            'sinopsis.required'       => 'Sinopsis wajib diisi.',
            'sutradara.required'      => 'Nama sutradara wajib diisi.',
            'produser.required'       => 'Nama produser wajib diisi.',
            'kru.required'            => 'Daftar Kru wajib diupload.',
            'kru.max'                 => 'Ukuran Daftar Kru maksimal 5MB.',
            'poster.required'         => 'Poster film wajib diupload.',
            'poster.image'            => 'Poster harus berupa gambar.',
            'poster.mimes'            => 'Poster harus berformat JPG atau PNG.',
            'poster.max'              => 'Ukuran poster maksimal 5MB.',
            'gsm.required'            => 'File GSM wajib diupload.',
            'gsm.min'                 => 'Minimal 1 file GSM wajib diupload.',
            'gsm.*.max'               => 'Ukuran tiap file GSM maksimal 25MB.',
            'trailer.required'        => 'Link trailer wajib diisi.',
            'trailer.url'             => 'Link trailer harus berupa URL yang valid.',
            'film.required'           => 'Link file film wajib diisi.',
            'film.url'                => 'Link film harus berupa URL yang valid.',
            'other_1.max'             => 'Ukuran file tambahan maksimal 10MB.',
            'other_2.max'             => 'Ukuran screenshot bukti unggah/repost maksimal 10MB.',
        ]);

        // Upload kru
        $kruPath = $request->file('kru')->store('kru', 'public');

        // Upload Poster
        $posterPath = $request->file('poster')->store('posters', 'public');
        PosterThumbnail::make($posterPath); // thumbnail kecil untuk tabel

        // Upload GSM (multiple)
        $gsmPaths = [];
        foreach ($request->file('gsm') as $file) {
            $gsmPaths[] = $file->store('gsm', 'public');
        }

        // Upload other_1 jika ada (tergantung kategori)
        $other1Path = null;
        if ($request->hasFile('other_1')) {
            $other1Path = $request->file('other_1')->store('other', 'public');
        }

        $other2Path = null;
        if ($request->hasFile('other_2')) {
            $other2Path = $request->file('other_2')->store('other', 'public');
        }

        Film::create([
            'user_id'        => auth()->id(),
            'submission_setting_id' => $activeSetting->id,
            'category_id'    => auth()->user()->category_id,
            'name'           => $request->name,
            'duration'       => $request->duration,
            'tahun_produksi' => $request->tahun_produksi,
            'subtitle'       => $request->subtitle,
            'sinopsis'       => $request->sinopsis,
            'sutradara'      => $request->sutradara,
            'produser'       => $request->produser,
            'penulis'        => $request->penulis,
            'kru'            => $kruPath,
            'poster'         => $posterPath,
            'gsm'            => json_encode($gsmPaths),
            'trailer'        => $request->trailer,
            'film'           => $request->film,
            'other_1'        => $other1Path,
            'other_2'        => $other2Path,
            'status'         => Film::CURATION_SUBMITTED,
            'curation_status' => Film::CURATION_SUBMITTED,
        ]);

        return redirect()->route('film.index')
            ->with('success', 'Submission film berhasil dikirim! Kami akan mereview film Anda.');
    }

    public function show($id)
    {
        if ($redirect = $this->redirectGeneralBuyerAway()) {
            return $redirect;
        }

        $this->syncClosedSubmissionStatuses();

        $title = 'Detail Submission';
        $film = Film::with([
            'user.category',
            'user.detail',
            'category.rubrics.groups.items',
            'submissionSetting',
            'juryScores.jury',
            'submissionReviews.reviewer',
            'submissionReviews.scores',
        ])->findOrFail($id);
        $reviewStageLabels = ReviewRubric::stageLabels();

        return view('film.show', compact('film', 'title', 'reviewStageLabels'));
    }

    public function edit($id)
    {
        if ($redirect = $this->redirectGeneralBuyerAway()) {
            return $redirect;
        }

        if (auth()->user()->hasRole(['admin', 'adminsub'])) {
            $film = Film::findOrFail($id);
        } else {
            $film = Film::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();
        }

        $title = 'Edit Submission';
        $categories = \App\Models\Category::orderBy('sort_order')->orderBy('name')->get();
        return view('film.edit', compact('film', 'categories', 'title'));
    }

    public function update(Request $request, $id)
    {
        if ($redirect = $this->redirectGeneralBuyerAway()) {
            return $redirect;
        }

        if (auth()->user()->hasRole(['admin', 'adminsub'])) {
            $film = Film::findOrFail($id);
        } else {
            $film = Film::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();
        }

        $request->validate([
            'name'           => 'required|string|max:255',
            'duration'       => 'required|integer|min:1|max:1800',
            'tahun_produksi' => 'required|digits:4',
            'subtitle'       => 'required|in:Ya,Tidak',
            'sinopsis'       => 'required|string',
            'sutradara'      => 'required|string|max:255',
            'produser'       => 'required|string|max:255',
            'penulis'        => 'nullable|string|max:255',
            'kru'            => 'nullable|file|max:6024',
            'poster'         => 'nullable|image|mimes:jpg,jpeg,png|max:6024',
            'gsm'            => 'nullable|array',
            'gsm.*'          => 'nullable|file|max:30240',
            'trailer'        => 'required|url',
            'film'           => 'required|url',
            'other_1'        => 'nullable|file|max:10240',
            'other_2'        => 'nullable|file|max:10240',
            'category_id'    => 'nullable|exists:categories,id',
        ], [
            'name.required'           => 'Judul film wajib diisi.',
            'duration.required'       => 'Durasi wajib diisi.',
            'duration.min'            => 'Durasi tidak valid.',
            'duration.max'            => 'Durasi film maksimal 30 menit.',
            'tahun_produksi.required' => 'Tahun produksi wajib diisi.',
            'tahun_produksi.digits'   => 'Tahun produksi harus 4 digit.',
            'subtitle.required'       => 'Subtitle wajib dipilih.',
            'subtitle.in'             => 'Pilihan subtitle tidak valid.',
            'sinopsis.required'       => 'Sinopsis wajib diisi.',
            'sutradara.required'      => 'Nama sutradara wajib diisi.',
            'produser.required'       => 'Nama produser wajib diisi.',
            'kru.max'                 => 'Ukuran file kru maksimal 5MB.',
            'poster.image'            => 'Poster harus berupa gambar.',
            'poster.mimes'            => 'Poster harus berformat JPG atau PNG.',
            'poster.max'              => 'Ukuran poster maksimal 5MB.',
            'gsm.*.max'               => 'Ukuran tiap file GSM maksimal 25MB.',
            'trailer.required'        => 'Link trailer wajib diisi.',
            'trailer.url'             => 'Link trailer harus berupa URL yang valid.',
            'film.required'           => 'Link file film wajib diisi.',
            'film.url'                => 'Link film harus berupa URL yang valid.',
            'other_1.max'             => 'Ukuran file tambahan maksimal 10MB.',
            'other_2.max'             => 'Ukuran screenshot bukti unggah/repost maksimal 10MB.',
        ]);

        $data = [
            'name'           => $request->name,
            'duration'       => $request->duration,
            'tahun_produksi' => $request->tahun_produksi,
            'subtitle'       => $request->subtitle,
            'sinopsis'       => $request->sinopsis,
            'sutradara'      => $request->sutradara,
            'produser'       => $request->produser,
            'penulis'        => $request->penulis,
            'trailer'        => $request->trailer,
            'film'           => $request->film,
            'category_id'    => $request->category_id ?: $film->category_id ?: $film->user->category_id,
        ];

        // Ganti kru jika ada upload baru
        if ($request->hasFile('kru')) {
            if ($film->kru) {
                Storage::disk('public')->delete($film->kru);
            }
            $data['kru'] = $request->file('kru')->store('kru', 'public');
        }

        // Ganti poster jika ada upload baru
        if ($request->hasFile('poster')) {
            Storage::disk('public')->delete($film->poster);
            PosterThumbnail::delete($film->poster);
            $data['poster'] = $request->file('poster')->store('posters', 'public');
            PosterThumbnail::make($data['poster']);
        }

        // Ganti GSM jika ada upload baru
        if ($request->hasFile('gsm')) {
            foreach (json_decode($film->gsm ?? '[]') as $oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
            $gsmPaths = [];
            foreach ($request->file('gsm') as $file) {
                $gsmPaths[] = $file->store('gsm', 'public');
            }
            $data['gsm'] = json_encode($gsmPaths);
        }

        // Ganti other_1 jika ada upload baru
        if ($request->hasFile('other_1')) {
            if ($film->other_1) {
                Storage::disk('public')->delete($film->other_1);
            }
            $data['other_1'] = $request->file('other_1')->store('other', 'public');
        }

        if ($request->hasFile('other_2')) {
            if ($film->other_2) {
                Storage::disk('public')->delete($film->other_2);
            }
            $data['other_2'] = $request->file('other_2')->store('other', 'public');
        }

        $film->update($data);

        return redirect()->route('film.index')
            ->with('success', 'Submission film berhasil diperbarui.');
    }

    public function destroy($id)
    {
        if ($redirect = $this->redirectGeneralBuyerAway()) {
            return $redirect;
        }

        if (auth()->user()->hasRole(['admin', 'adminsub'])) {
            $film = Film::findOrFail($id);
        } else {
            $film = Film::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();
        }

        // Hapus semua file terkait
        if ($film->poster) {
            Storage::disk('public')->delete($film->poster);
            PosterThumbnail::delete($film->poster);
        }

        if ($film->kru) {
            Storage::disk('public')->delete($film->kru);
        }

        foreach (json_decode($film->gsm ?? '[]') as $path) {
            Storage::disk('public')->delete($path);
        }

        if ($film->other_1) {
            Storage::disk('public')->delete($film->other_1);
        }

        if ($film->other_2) {
            Storage::disk('public')->delete($film->other_2);
        }

        $film->delete();

        return redirect()->route('film.index')
            ->with('success', 'Submission film berhasil dihapus.');
    }

    /**
     * Upload / ganti file Surat Orisinalitas Karya.
     * Hanya bisa dilakukan oleh peserta pemilik film, dan hanya untuk
     * film yang statusnya sudah Official Selection (approved).
     * Relasi 1 film : 1 surat, disimpan langsung di kolom films.originality_letter.
     */
    public function uploadOriginalityLetter(Request $request, $film)
    {
        $film = Film::findOrFail($film);

        abort_unless(auth()->check() && (int) $film->user_id === (int) auth()->id(), 403);
        abort_unless($film->curation_status === Film::CURATION_APPROVED, 403, 'Upload hanya untuk film Official Selection.');

        $request->validate([
            'originality_letter' => 'required|file|mimes:pdf,jpg,jpeg,png|max:6144',
        ], [
            'originality_letter.required' => 'File Surat Orisinalitas Karya wajib diupload.',
            'originality_letter.file'     => 'File tidak valid.',
            'originality_letter.mimes'    => 'File harus berformat PDF, JPG, atau PNG.',
            'originality_letter.max'      => 'Ukuran file maksimal 6MB.',
        ]);

        if ($film->originality_letter) {
            Storage::disk('public')->delete($film->originality_letter);
        }

        $film->originality_letter = $request->file('originality_letter')->store('originality-letters', 'public');
        $film->originality_letter_uploaded_at = now();
        $film->save();

        return back()->with('success', 'Surat Orisinalitas Karya berhasil diupload.');
    }

    public function downloadGsm($id)
    {
        if ($redirect = $this->redirectGeneralBuyerAway()) {
            return $redirect;
        }

        $film     = Film::findOrFail($id);
        $gsmFiles = json_decode($film->gsm, true) ?? [];

        if (empty($gsmFiles)) {
            return back()->with('error', 'Tidak ada file GSM.');
        }

        $zipName = 'GrabStill_' . Str::slug($film->name) . '_' . $film->id . '.zip';
        $zipPath = storage_path('app/temp/' . $zipName);

        // Buat folder temp jika belum ada
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        // Buat zip
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Gagal membuat file zip.');
        }

        foreach ($gsmFiles as $i => $path) {
            $filePath = storage_path('app/public/' . $path);
            if (file_exists($filePath)) {
                $ext      = pathinfo($filePath, PATHINFO_EXTENSION);
                $fileName = 'GSM_' . ($i + 1) . '.' . $ext;
                $zip->addFile($filePath, $fileName);
            }
        }

        $zip->close();

        // Download lalu hapus file temp
        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }

    protected function redirectGeneralBuyerAway()
    {
        if (auth()->check() && auth()->user()->isGeneralBuyer()) {
            return redirect()->route('merchandise')
                ->with('warning', 'Akun umum tidak dapat mengakses halaman submission film.');
        }

        return null;
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
        return $request->input('category_id') ?: null;
    }

    protected function resolveCurationStatus(Request $request)
    {
        if ($request->query->has('curation_status')) {
            return $request->input('curation_status') ?: null;
        }

        return null;
    }

    public function exportExcel(Request $request)
    {
        [$query] = $this->filteredFilmsQuery($request);
        $films = $query->with(['user.category', 'user.detail', 'submissionSetting', 'submissionReviews', 'juryScores'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $fileName = 'data-submission-film_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new FilmsExport($films), $fileName);
    }

    /**
     * Ambil data film + filter yang sedang aktif, dalam bentuk Query Builder (belum di-execute).
     * Dipakai bareng oleh data() (AJAX DataTables) dan exportExcel() supaya hasilnya selalu sinkron.
     *
     * @return array{0: \Illuminate\Database\Eloquent\Builder, 1: ?int, 2: ?int, 3: ?string}
     */
    private function filteredFilmsQuery(Request $request): array
    {
        $selectedSubmissionSettingId = null;
        $selectedCategoryId = null;
        $selectedCurationStatus = null;

        // Kolom-kolom yang dibutuhkan di tabel saja (bukan select *) supaya query lebih ringan,
        // relasi berat (rubrics, scores, dll) sengaja TIDAK dipakai di sini karena tabel ini
        // hanya menampilkan judul/kategori/durasi/status/peserta.
        $query = Film::query()->with([
            'user:id,name',
            'category:id,name',
        ]);

        if (auth()->user()->hasRole(['admin', 'adminsub', 'viewer'])) {
            $selectedSubmissionSettingId = $this->resolveSubmissionSettingId($request);
            $selectedCategoryId = $this->resolveCategoryId($request);
            $selectedCurationStatus = $this->resolveCurationStatus($request);

            if ($selectedSubmissionSettingId) {
                $query->where('submission_setting_id', $selectedSubmissionSettingId);
            }

            if ($selectedCategoryId) {
                $query->where('category_id', $selectedCategoryId);
            }

            if ($selectedCurationStatus) {
                $query->where('curation_status', $selectedCurationStatus);
            }
        } else {
            $query->where('user_id', auth()->id());
        }

        return [$query, $selectedSubmissionSettingId, $selectedCategoryId, $selectedCurationStatus];
    }

    /**
     * Endpoint AJAX untuk DataTables server-side processing.
     * Hanya mengambil & mengirim baris yang sedang ditampilkan di halaman (pageLength),
     * bukan seluruh data submission sekaligus.
     */
    public function data(Request $request)
    {
        if ($redirect = $this->redirectGeneralBuyerAway()) {
            abort(403);
        }

        [$query] = $this->filteredFilmsQuery($request);

        $recordsTotal = (clone $query)->count();

        // Pencarian global (judul film / nama peserta / nama sutradara)
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

        // Filter kategori khusus untuk peserta (dropdown "Filter Kategori" di sisi client)
        $categoryName = trim((string) $request->input('category_name'));
        if ($categoryName !== '') {
            $query->whereHas('category', function ($cq) use ($categoryName) {
                $cq->where('name', $categoryName);
            });
        }

        $recordsFiltered = (clone $query)->count();

        // Sorting: kolom yang bisa di-order sesuai definisi columnDefs di Blade
        $orderColumnMap = [
            2 => 'category_id', // urutan by nama kategori didekati lewat category_id, cukup untuk UX
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
        $canManage = $viewer->role !== 'viewer';

        $data = $films->values()->map(function (Film $film, $i) use ($start, $viewer, $canManage) {
            $s = $film->statusBadgeFor($viewer);

            $detik = (int) $film->duration;
            $duration = sprintf('%02d:%02d:%02d', floor($detik / 3600), floor(($detik % 3600) / 60), $detik % 60);

            // Kotak skeleton (shimmer) + gambar thumbnail yang di-lazy-load.
            // Class "is-loaded" ditambahkan lewat JS di index.blade.php saat gambar selesai dimuat.
            $posterHtml = $film->poster
                ? '<div class="poster-skel"><img class="poster-img" src="' . e($film->poster_thumb_url) . '" width="72" height="96" loading="lazy" decoding="async" alt=""></div>'
                : '<div style="width:36px;height:48px;background:#eee;border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:10px;color:#aaa;flex-shrink:0;">N/A</div>';

            $sutradara = $film->sutradara
                ? '<small class="text-muted">Sutradara : ' . e($film->sutradara) . '</small>'
                : '';

            $judul = '<div style="display:flex;align-items:center;gap:10px;">' . $posterHtml
                . '<div><div style="font-weight:600;">' . e($film->name) . '</div>' . $sutradara . '</div></div>';

            $createdAt = optional($film->created_at);
            $tanggal = $createdAt->timestamp
                ? $createdAt->format('d M Y') . '<br><small class="text-muted">' . $createdAt->format('H:i') . ' WIB</small>'
                : '-';

            $status = '<span style="background:' . $s['bg'] . ';color:' . $s['color'] . ';padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;white-space:nowrap;">'
                . e($s['label']) . '</span>';

            $aksi = '<a href="' . route('film.show', $film->id) . '" class="btn btn-info btn-xs" title="Detail"><i class="fa fa-eye"></i></a>';
            if ($canManage) {
                $aksi .= ' <a href="' . route('film.edit', $film->id) . '" class="btn btn-warning btn-xs" title="Edit"><i class="fa fa-pencil"></i></a>'
                    . ' <form action="' . route('film.destroy', $film->id) . '" method="POST" style="display:inline-block;" onsubmit="return confirm(\'Yakin ingin menghapus film ini?\')">'
                    . csrf_field() . method_field('DELETE')
                    . '<button type="submit" class="btn btn-danger btn-xs" title="Hapus"><i class="fa fa-trash"></i></button></form>';
            }

            return [
                'DT_RowId' => 'film-' . $film->id,
                'no' => $start + $i + 1,
                'judul' => $judul,
                'kategori' => $film->category->name ?? '-',
                'durasi' => $duration,
                'tanggal' => $tanggal,
                'tanggal_order' => $createdAt->timestamp ?? 0,
                'status' => $status,
                'peserta' => $film->user->name ?? '-',
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