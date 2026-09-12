<table>
    <tr>
        <th>No</th>
        <th>Tanggal Submit</th>
        <th>Email Peserta</th>
        <th>Akun Instagram</th>
        <th>Nomor WhatsApp</th>
        <th>Peserta (User)</th>
        <th>Posisi/Jabatan Peserta</th>
        <th>Nama Tim/Komunitas Produksi</th>
        <th>Alamat</th>
        <th>Provinsi</th>
        <th>Kabupaten/Kota</th>
        <th>Judul Film</th>
        <th>Kategori</th>
        <th>Sub Kategori</th>
        <th>Durasi</th>
        <th>Tahun Produksi</th>
        <th>Sinopsis</th>
        <th>File Film (URL)</th>
        <th>Trailer (URL)</th>
        <th>Poster (URL)</th>
        <th>GSM</th>
        <th>Kru (URL)</th>
        <th>Nilai Kurator</th>
        <th>Nilai Juri</th>
        <th>Subtitle</th>
        <th>Sutradara</th>
        <th>Produser</th>
        <th>Penulis</th>
        <th>Other 1 (URL)</th>
        <th>Other 2 (URL)</th>
        <th>Status</th>
        <th>Status Kurasi</th>
        <th>Catatan Kurator</th>
        <th>Winner Rank</th>
        <th>Periode Submission</th>
    </tr>
    @foreach($films as $index => $film)
    @php
        $detik = (int) $film->duration;
        $jam = floor($detik / 3600);
        $menit = floor(($detik % 3600) / 60);
        $sisa = $detik % 60;
        $durasiFormatted = sprintf('%02d:%02d:%02d', $jam, $menit, $sisa);

        $detail = optional($film->user)->detail;

        // Nilai Kurator & Nilai Juri: disamakan dengan logic yang sudah dipakai
        // di SubmissionReviewController@attachReviewMetrics (SUM per stage, bukan AVG),
        // supaya konsisten dengan yang sudah tampil di halaman penilaian admin.
        $curationReviews = $film->submissionReviews->where('stage', \App\Models\ReviewRubric::STAGE_CURATION);
        $juryReviews = $film->submissionReviews->where('stage', \App\Models\ReviewRubric::STAGE_JURY);

        $nilaiKurator = $curationReviews->count()
            ? round((float) $curationReviews->sum('total_score'), 2)
            : null;

        $nilaiJuri = $juryReviews->count()
            ? round((float) $juryReviews->sum('total_score'), 2)
            : ($film->juryScores->count()
                ? round((float) $film->juryScores->sum('score'), 2)
                : null);
    @endphp
    <tr>
        <td>{{ $index + 1 }}</td>
        <td>{{ optional($film->created_at)->format('d M Y H:i') }}</td>
        <td>{{ $film->user->email ?? '-' }}</td>
        <td>{{ optional($detail)->username_ig ? '@'.$detail->username_ig : '-' }}</td>
        <td>{{ $film->user->no_hp ?? '-' }}</td>
        <td>{{ $film->user->name ?? '-' }}</td>
        <td>{{ optional($detail)->posisi ?? '-' }}</td>
        <td>{{ optional($detail)->community_name ?? '-' }}</td>
        <td>{{ optional($detail)->alamat_lengkap ?? '-' }}</td>
        <td>{{ optional($detail)->provinsi_name ?? '-' }}</td>
        <td>{{ optional($detail)->kabupaten_name ?? '-' }}</td>
        <td>{{ $film->name }}</td>
        <td>{{ $film->category->name ?? '-' }}</td>
        <td>{{ $film->user->category->name ?? '-' }}</td>
        <td>{{ $durasiFormatted }}</td>
        <td>{{ $film->tahun_produksi ?? '-' }}</td>
        <td>{{ $film->sinopsis ?? '-' }}</td>
        <td>{{ \App\Exports\FilmsExport::fullUrl($film->film) }}</td>
        <td>{{ \App\Exports\FilmsExport::fullUrl($film->trailer) }}</td>
        <td>{{ \App\Exports\FilmsExport::fullUrl($film->poster) }}</td>
        <td>{{ \App\Exports\FilmsExport::gsmUrlList($film->gsm) }}</td>
        <td>{{ \App\Exports\FilmsExport::fullUrl($film->kru) }}</td>
        <td>{{ $nilaiKurator ?? '-' }}</td>
        <td>{{ $nilaiJuri ?? '-' }}</td>
        <td>{{ $film->subtitle ?? '-' }}</td>
        <td>{{ $film->sutradara ?? '-' }}</td>
        <td>{{ $film->produser ?? '-' }}</td>
        <td>{{ $film->penulis ?? '-' }}</td>
        <td>{{ \App\Exports\FilmsExport::fullUrl($film->other_1) }}</td>
        <td>{{ \App\Exports\FilmsExport::fullUrl($film->other_2) }}</td>
        <td>{{ strtoupper($film->status) }}</td>
        <td>{{ strtoupper($film->curation_status) }}</td>
        <td>{{ $film->curator_note ?? '-' }}</td>
        <td>{{ $film->winner_rank ?? '-' }}</td>
        <td>{{ $film->submissionSetting->name ?? '-' }}</td>
    </tr>
    @endforeach
</table>