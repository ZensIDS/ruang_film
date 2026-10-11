<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bioskop extends Model
{
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_FULL      = 'full';

    protected $guarded = ['id'];

    protected $casts = [
        'schedules' => 'array',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /** Ringkasan untuk tabel admin, mis. "3 tanggal · 9 sesi". */
    public function getScheduleSummaryAttribute(): string
    {
        $schedules = collect($this->schedules ?? []);
        $sessions  = $schedules->sum(fn ($s) => count($s['sessions'] ?? []));

        return $schedules->count() . ' tanggal · ' . $sessions . ' sesi';
    }
}