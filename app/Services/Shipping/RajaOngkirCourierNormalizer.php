<?php

namespace App\Services\Shipping;

use Illuminate\Support\Str;

class RajaOngkirCourierNormalizer
{
    public static function normalize($value)
    {
        $normalized = Str::lower(trim((string) $value));

        if ($normalized === '') {
            return '';
        }

        $compact = preg_replace('/[^a-z0-9]+/', '', $normalized);

        $aliases = [
            'jne' => ['jne', 'jalurnugrahaekakurir'],
            'jnt' => ['jnt', 'jtexpress', 'jntexpress', 'jt'],
            'sicepat' => ['sicepat', 'sicepatekspres', 'sicepatexpress'],
            'anteraja' => ['anteraja', 'anterajaexpress'],
            'ninja' => ['ninja', 'ninjaxpress', 'ninjaexpress'],
            'pos' => ['pos', 'post', 'posindonesia'],
            'tiki' => ['tiki', 'titipkilat'],
            'wahana' => ['wahana', 'wahanaexpress'],
            'lion' => ['lion', 'lionparcel'],
        ];

        foreach ($aliases as $canonical => $values) {
            if (in_array($compact, $values, true)) {
                return $canonical;
            }
        }

        return $compact;
    }
}
