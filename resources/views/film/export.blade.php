<table>
    <tr>
        <th>No</th>
        <th>Judul Film</th>
        <th>Kategori</th>
        <th>Sub Kategori</th>
        <th>Peserta (User)</th>
        <th>Email Peserta</th>
        <th>Durasi</th>
        <th>Tahun Produksi</th>
        <th>Subtitle</th>
        <th>Sinopsis</th>
        <th>Sutradara</th>
        <th>Produser</th>
        <th>Penulis</th>
        <th>Kru (URL)</th>
        <th>GSM</th>
        <th>Other 1 (URL)</th>
        <th>Other 2 (URL)</th>
        <th>Status</th>
        <th>Status Kurasi</th>
        <th>Catatan Kurator</th>
        <th>Winner Rank</th>
        <th>Periode Submission</th>
        <th>Tanggal Submit</th>
        <th>Poster (URL)</th>
        <th>Trailer (URL)</th>
        <th>File Film (URL)</th>
    </tr>
    @foreach($films as $index => $film)
    @php
        $detik = (int) $film->duration;
        $jam = floor($detik / 3600);
        $menit = floor(($detik % 3600) / 60);
        $sisa = $detik % 60;
        $durasiFormatted = sprintf('%02d:%02d:%02d', $jam, $menit, $sisa);
    @endphp
    <tr>
        <td>{{ $index + 1 }}</td>
        <td>{{ $film->name }}</td>
        <td>{{ $film->category->name ?? '-' }}</td>
        <td>{{ $film->user->category->name ?? '-' }}</td>
        <td>{{ $film->user->name ?? '-' }}</td>
        <td>{{ $film->user->email ?? '-' }}</td>
        <td>{{ $durasiFormatted }}</td>
        <td>{{ $film->tahun_produksi ?? '-' }}</td>
        <td>{{ $film->subtitle ?? '-' }}</td>
        <td>{{ $film->sinopsis ?? '-' }}</td>
        <td>{{ $film->sutradara ?? '-' }}</td>
        <td>{{ $film->produser ?? '-' }}</td>
        <td>{{ $film->penulis ?? '-' }}</td>
        <td>{{ \App\Exports\FilmsExport::fullUrl($film->kru) }}</td>
        <td>{{ \App\Exports\FilmsExport::gsmUrlList($film->gsm) }}</td>
        <td>{{ \App\Exports\FilmsExport::fullUrl($film->other_1) }}</td>
        <td>{{ \App\Exports\FilmsExport::fullUrl($film->other_2) }}</td>
        <td>{{ strtoupper($film->status) }}</td>
        <td>{{ strtoupper($film->curation_status) }}</td>
        <td>{{ $film->curator_note ?? '-' }}</td>
        <td>{{ $film->winner_rank ?? '-' }}</td>
        <td>{{ $film->submissionSetting->name ?? '-' }}</td>
        <td>{{ optional($film->created_at)->format('d M Y H:i') }}</td>
        <td>{{ \App\Exports\FilmsExport::fullUrl($film->poster) }}</td>
        <td>{{ \App\Exports\FilmsExport::fullUrl($film->trailer) }}</td>
        <td>{{ \App\Exports\FilmsExport::fullUrl($film->film) }}</td>
    </tr>
    @endforeach
</table>
