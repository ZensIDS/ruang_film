<?php

namespace App\Http\Controllers;

use App\Support\PublicMedia;
use Illuminate\Support\Facades\Storage;

class PublicStorageController extends Controller
{
    public function show($path)
    {
        $normalizedPath = PublicMedia::normalizeStoragePath($path);

        if (!$normalizedPath) {
            abort(404);
        }

        if (Storage::disk('public')->exists($normalizedPath)) {
            return Storage::disk('public')->response($normalizedPath);
        }

        $legacyPath = public_path('storage/' . $normalizedPath);

        if (is_file($legacyPath)) {
            return response()->file($legacyPath);
        }

        abort(404);
    }
}
