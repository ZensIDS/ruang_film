<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'merchandise_id',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function merchandise()
    {
        return $this->belongsTo(Merchandise::class);
    }

    public function subtotal()
    {
        return (float) ($this->quantity * $this->unit_price);
    }
}
