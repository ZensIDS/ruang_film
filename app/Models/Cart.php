<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function subtotal()
    {
        return (float) $this->items->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });
    }

    public function totalQuantity()
    {
        return (int) $this->items->sum('quantity');
    }

    /**
     * Samakan isi keranjang dengan kondisi stok & harga terbaru.
     * - Produk nonaktif / stok habis  => item dihapus
     * - Qty melebihi stok             => qty diturunkan ke sisa stok
     * - Harga berubah                 => unit_price diperbarui
     *
     * @return array<int,string> daftar pesan perubahan (kosong jika tidak ada)
     */
    public function syncWithStock(): array
    {
        $this->load('items.merchandise.category');

        $notices = [];

        foreach ($this->items as $item) {
            $merchandise = $item->merchandise;
            $name = $merchandise->name ?? 'Merchandise';

            if (!$merchandise || !$merchandise->is_active || $merchandise->qty_stock < 1) {
                $item->delete();
                $notices[] = "\"{$name}\" dihapus dari keranjang karena stok habis atau tidak tersedia.";
                continue;
            }

            $updates = [];

            if ($item->quantity > $merchandise->qty_stock) {
                $updates['quantity'] = $merchandise->qty_stock;
                $notices[] = "Jumlah \"{$name}\" disesuaikan menjadi {$merchandise->qty_stock} karena stok tersisa {$merchandise->qty_stock}.";
            }

            if ((float) $item->unit_price !== (float) $merchandise->currentPrice()) {
                $updates['unit_price'] = $merchandise->currentPrice();
                $notices[] = "Harga \"{$name}\" telah diperbarui.";
            }

            if ($updates) {
                $item->update($updates);
            }
        }

        if ($notices) {
            $this->load('items.merchandise.category');
        }

        return $notices;
    }
}