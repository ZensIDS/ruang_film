<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Bikin & kelola thumbnail poster kecil (180x240, JPEG) untuk tampilan tabel.
 * Poster asli (bisa sampai 5-6 MB) tetap disimpan utuh untuk halaman detail.
 * Tidak butuh kolom/migration baru: lokasi thumbnail diturunkan dari path poster.
 */
class PosterThumbnail
{
    const DIR     = 'posters/thumbs';
    const WIDTH   = 180;
    const HEIGHT  = 240; // rasio 3:4, sama dengan kotak 72x96 di tabel
    const QUALITY = 78;

    /** Path thumbnail (relatif ke disk 'public') untuk sebuah path poster. */
    public static function path($posterPath)
    {
        $normalized = PublicMedia::normalizeStoragePath($posterPath);

        if (!$normalized) {
            return null;
        }

        return self::DIR . '/' . pathinfo($normalized, PATHINFO_FILENAME) . '.jpg';
    }

    /** URL thumbnail kalau ada; kalau belum ada, pakai URL poster asli sebagai fallback. */
    public static function url($posterPath, $fallbackUrl)
    {
        $thumb = self::path($posterPath);

        if ($thumb && Storage::disk('public')->exists($thumb)) {
            return PublicMedia::url($thumb);
        }

        return $fallbackUrl;
    }

    /** Buat thumbnail dari poster. Return path thumbnail, atau null kalau gagal (tidak melempar error). */
    public static function make($posterPath)
    {
        if (!function_exists('imagecreatefromstring')) {
            return null; // ekstensi GD tidak aktif
        }

        $source = PublicMedia::normalizeStoragePath($posterPath);
        $target = self::path($posterPath);
        $disk   = Storage::disk('public');

        if (!$source || !$target || !$disk->exists($source)) {
            return null;
        }

        try {
            $binary = $disk->get($source);
            $img    = @imagecreatefromstring($binary);

            if (!$img) {
                return null;
            }

            $img = self::applyExifOrientation($img, $binary);

            $sw = imagesx($img);
            $sh = imagesy($img);

            // Crop tengah dengan rasio 3:4 (mirip object-fit: cover)
            $ratio = self::WIDTH / self::HEIGHT;
            if ($sw / $sh > $ratio) {
                $ch = $sh;
                $cw = (int) round($sh * $ratio);
            } else {
                $cw = $sw;
                $ch = (int) round($sw / $ratio);
            }
            $cx = (int) floor(($sw - $cw) / 2);
            $cy = (int) floor(($sh - $ch) / 2);

            $thumb = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
            imagefill($thumb, 0, 0, imagecolorallocate($thumb, 255, 255, 255)); // PNG transparan -> latar putih
            imagecopyresampled($thumb, $img, 0, 0, $cx, $cy, self::WIDTH, self::HEIGHT, $cw, $ch);

            ob_start();
            imagejpeg($thumb, null, self::QUALITY);
            $jpeg = ob_get_clean();

            imagedestroy($img);
            imagedestroy($thumb);

            $disk->put($target, $jpeg);

            return $target;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    /** Hapus thumbnail milik sebuah poster (aman dipanggil walau thumbnail tidak ada). */
    public static function delete($posterPath)
    {
        $thumb = self::path($posterPath);

        if ($thumb) {
            Storage::disk('public')->delete($thumb);
        }
    }

    /** Foto dari HP sering punya EXIF rotate; GD mengabaikannya, jadi kita putar manual. */
    private static function applyExifOrientation($img, $binary)
    {
        if (!function_exists('exif_read_data') || !function_exists('imagerotate')) {
            return $img;
        }

        $exif = @exif_read_data('data://image/jpeg;base64,' . base64_encode($binary));
        $orientation = is_array($exif) ? ($exif['Orientation'] ?? 1) : 1;
        $angle = [3 => 180, 6 => -90, 8 => 90][$orientation] ?? 0;

        if ($angle) {
            $rotated = imagerotate($img, $angle, 0);
            if ($rotated) {
                imagedestroy($img);

                return $rotated;
            }
        }

        return $img;
    }
}
