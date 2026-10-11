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
        'merchandise_variant_id',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    // Setiap perubahan item ikut memperbarui updated_at keranjang (dipakai untuk membersihkan keranjang tamu yang basi)
    protected $touches = ['cart'];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function merchandise()
    {
        return $this->belongsTo(Merchandise::class);
    }

    public function variant()
    {
        return $this->belongsTo(MerchandiseVariant::class, 'merchandise_variant_id');
    }

    public function subtotal()
    {
        return (float) ($this->quantity * $this->unit_price);
    }

    /** Nama produk + varian, mis. "Kaos Horor (M)". */
    public function label()
    {
        $name = $this->merchandise ? $this->merchandise->name : 'Merchandise';

        return $this->variant ? $name . ' (' . $this->variant->name . ')' : $name;
    }

    /**
     * Stok yang benar-benar tersedia untuk baris keranjang ini
     * (stok varian jika produk bervarian, stok produk jika tidak). 0 jika tidak valid.
     */
    public function availableStock()
    {
        $merchandise = $this->merchandise;

        if (!$merchandise || !$merchandise->is_active) {
            return 0;
        }

        if ($merchandise->has_variants) {
            $variant = $this->variant;

            if (!$variant || !$variant->is_active || (int) $variant->merchandise_id !== (int) $merchandise->id) {
                return 0;
            }

            return (int) $variant->qty_stock;
        }

        return $this->merchandise_variant_id ? 0 : (int) $merchandise->qty_stock;
    }
}