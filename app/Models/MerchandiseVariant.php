<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerchandiseVariant extends Model
{
    protected $fillable = [
        'merchandise_id',
        'name',
        'qty_stock',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function merchandise()
    {
        return $this->belongsTo(Merchandise::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}