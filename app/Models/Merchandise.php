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
        'has_variants',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'is_active' => 'boolean',
        'has_variants' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(MerchandiseCategory::class, 'merchandise_category_id');
    }

    /** Semua varian (termasuk yang nonaktif/diarsipkan). */
    public function variants()
    {
        return $this->hasMany(MerchandiseVariant::class)->orderBy('sort_order')->orderBy('id');
    }

    /** Varian aktif saja, dipakai untuk tampilan ke pembeli. */
    public function activeVariants()
    {
        return $this->hasMany(MerchandiseVariant::class)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function currentPrice()
    {
        return $this->price;
    }

    /** URL halaman detail (memakai slug, sesuai route {merchandise:slug}). */
    public function detailUrl()
    {
        return route('merchandise.show', ['merchandise' => $this->slug]);
    }

    public function imageUrl()
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : asset('landing/images/merchan/merchan1.jpg');
    }

    /**
     * Untuk produk bervarian, qty_stock adalah total stok semua varian aktif
     * (disimpan agar listing/sold-out tetap sederhana). Panggil setiap stok varian berubah.
     */
    public function syncStockFromVariants()
    {
        if (!$this->has_variants) {
            return;
        }

        $total = (int) $this->variants()->where('is_active', true)->sum('qty_stock');

        static::whereKey($this->getKey())->update(['qty_stock' => $total]);
        $this->setAttribute('qty_stock', $total);
        $this->syncOriginalAttribute('qty_stock');
    }

    /** Data produk untuk popup pilih varian / qty di sisi pembeli. */
    public function pickerData()
    {
        $variants = $this->relationLoaded('activeVariants')
            ? $this->activeVariants
            : $this->activeVariants()->get();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->imageUrl(),
            'price_formatted' => 'Rp ' . number_format($this->currentPrice(), 0, ',', '.'),
            'has_variants' => (bool) $this->has_variants,
            'stock' => $this->has_variants ? (int) $variants->sum('qty_stock') : (int) $this->qty_stock,
            'variants' => $variants->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'stock' => (int) $variant->qty_stock,
                ];
            })->values()->all(),
            'add_url' => route('cart.store', $this),
            'detail_url' => $this->detailUrl(),
        ];
    }
}