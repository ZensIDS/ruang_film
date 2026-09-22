<?php

namespace App\Console\Commands;

use App\Models\Film;
use App\Support\PosterThumbnail;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GenerateFilmThumbnails extends Command
{
    protected $signature = 'film:thumbnails {--force : Timpa thumbnail yang sudah ada}';

    protected $description = 'Buat thumbnail untuk poster film yang sudah ada (jalankan sekali setelah deploy)';

    public function handle()
    {
        $made = $skipped = $failed = 0;
        $force = (bool) $this->option('force');

        $lastId = 0;
        $batchSize = 20; // lebih kecil dari sebelumnya (50) supaya per-batch lebih cepat

        while (true) {
            // Pastikan koneksi DB masih hidup / sambungkan ulang sebelum tiap batch,
            // supaya tidak kena "MySQL server has gone away" akibat idle lama
            // selama proses generate thumbnail pada batch sebelumnya.
            try {
                DB::connection()->getPdo();
            } catch (\Throwable $e) {
                DB::reconnect();
            }

            try {
                $films = Film::query()
                    ->whereNotNull('poster')
                    ->where('poster', '!=', '')
                    ->where('id', '>', $lastId)
                    ->orderBy('id')
                    ->select('id', 'poster')
                    ->limit($batchSize)
                    ->get();
            } catch (QueryException $e) {
                // Koneksi sempat putus tepat saat query batch ini, coba sambung ulang sekali lalu retry
                DB::reconnect();

                $films = Film::query()
                    ->whereNotNull('poster')
                    ->where('poster', '!=', '')
                    ->where('id', '>', $lastId)
                    ->orderBy('id')
                    ->select('id', 'poster')
                    ->limit($batchSize)
                    ->get();
            }

            if ($films->isEmpty()) {
                break;
            }

            foreach ($films as $film) {
                $lastId = $film->id;

                try {
                    $thumb = PosterThumbnail::path($film->poster);

                    if (!$force && $thumb && Storage::disk('public')->exists($thumb)) {
                        $skipped++;
                        continue;
                    }

                    PosterThumbnail::make($film->poster) ? $made++ : $failed++;
                } catch (\Throwable $e) {
                    $failed++;
                    $this->warn("Gagal proses film ID {$film->id}: {$e->getMessage()}");
                }
            }

            // Bebaskan memori model per batch
            unset($films);
        }

        $this->info("Selesai. Dibuat: {$made}, dilewati: {$skipped}, gagal: {$failed}");

        return 0;
    }
}