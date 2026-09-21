<?php

namespace App\Console\Commands;

use App\Models\Film;
use App\Support\PosterThumbnail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateFilmThumbnails extends Command
{
    protected $signature = 'film:thumbnails {--force : Timpa thumbnail yang sudah ada}';

    protected $description = 'Buat thumbnail untuk poster film yang sudah ada (jalankan sekali setelah deploy)';

    public function handle()
    {
        $made = $skipped = $failed = 0;
        $force = (bool) $this->option('force');

        Film::query()
            ->whereNotNull('poster')
            ->where('poster', '!=', '')
            ->select('id', 'poster')
            ->chunkById(50, function ($films) use (&$made, &$skipped, &$failed, $force) {
                foreach ($films as $film) {
                    $thumb = PosterThumbnail::path($film->poster);

                    if (!$force && $thumb && Storage::disk('public')->exists($thumb)) {
                        $skipped++;
                        continue;
                    }

                    PosterThumbnail::make($film->poster) ? $made++ : $failed++;
                }
            });

        $this->info("Selesai. Dibuat: {$made}, dilewati: {$skipped}, gagal: {$failed}");

        return 0;
    }
}
