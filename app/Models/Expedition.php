<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expedition extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'external_code',
        'service_name',
        'fee',
        'is_active',
    ];

    protected $casts = [
        'fee' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function getDisplayNameAttribute()
    {
        return trim($this->name . ' ' . $this->service_name);
    }
}
