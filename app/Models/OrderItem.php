<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'merchandise_id',
        'merchandise_variant_id',
        'merchandise_name',
        'variant_name',
        'merchandise_slug',
        'merchandise_image',
        'unit_price',
        'quantity',
        'weight',
        'subtotal',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function merchandise()
    {
        return $this->belongsTo(Merchandise::class);
    }

    public function variant()
    {
        return $this->belongsTo(MerchandiseVariant::class, 'merchandise_variant_id');
    }
}