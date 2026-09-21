<?php

namespace App\Http\Controllers;

use App\Support\PublicMedia;
use Illuminate\Support\Facades\Storage;

class PublicStorageController extends Controller
{
    // Nama file upload berupa hash acak, jadi aman di-cache browser 30 hari.
    const CACHE_HEADERS = ['Cache-Control' => 'public, max-age=2592000'];

    public function show($path)
    {
        $normalizedPath = PublicMedia::normalizeStoragePath($path);

        if (!$normalizedPath) {
            abort(404);
        }

        if (Storage::disk('public')->exists($normalizedPath)) {
            return Storage::disk('public')->response($normalizedPath, null, self::CACHE_HEADERS);
        }

        $legacyPath = public_path('storage/' . $normalizedPath);

        if (is_file($legacyPath)) {
            return response()->file($legacyPath, self::CACHE_HEADERS);
        }

        abort(404);
    }
}
