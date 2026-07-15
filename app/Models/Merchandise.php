<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Merchandise extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchandise_category_id',
        'name',
        'slug',
        'image',
        'price',
        'discount_price',
        'weight',
        'qty_stock',
        'summary',
        'description',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(MerchandiseCategory::class, 'merchandise_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function currentPrice()
    {
        return $this->price;
    }
}
