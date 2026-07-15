<?php

namespace App\Support;

use Illuminate\Support\Str;

class PublicMedia
{
    public static function url($path, $fallback = null)
    {
        $path = trim((string) $path);

        if ($path === '') {
            return $fallback ? asset($fallback) : null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (Str::startsWith($path, ['landing/', 'img/', 'assets/'])) {
            return asset($path);
        }

        $normalizedPath = static::normalizeStoragePath($path);

        if (!$normalizedPath) {
            return $fallback ? asset($fallback) : null;
        }

        return url('storage/' . $normalizedPath);
    }

    public static function normalizeStoragePath($path)
    {
        $path = trim((string) $path);

        if ($path === '' || Str::startsWith($path, ['http://', 'https://'])) {
            return null;
        }

        $path = str_replace('\\', '/', ltrim($path, '/'));

        foreach (['public/storage/', 'storage/', 'public/'] as $prefix) {
            if (Str::startsWith($path, $prefix)) {
                $path = Str::after($path, $prefix);
                break;
            }
        }

        $segments = collect(explode('/', $path))
            ->filter(function ($segment) {
                return $segment !== '' && $segment !== '.';
            });

        if ($segments->contains('..')) {
            return null;
        }

        return $segments->implode('/') ?: null;
    }
}
