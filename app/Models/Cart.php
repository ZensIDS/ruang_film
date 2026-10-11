<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Cart extends Model
{
    use HasFactory;

    public const GUEST_COOKIE = 'guest_cart';
    public const GUEST_COOKIE_MINUTES = 43200; // 30 hari

    protected $fillable = [
        'user_id',
        'guest_token',
    ];

    protected $hidden = [
        'guest_token',
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

    /* ------------------------------------------------------------------
     |  Keranjang pengunjung saat ini (user login ATAU tamu)
     | ------------------------------------------------------------------ */

    /** Token keranjang tamu dari cookie (dienkripsi Laravel), hanya diterima jika formatnya valid. */
    public static function guestToken()
    {
        $token = request()->cookie(static::GUEST_COOKIE);

        return is_string($token) && preg_match('/^[A-Za-z0-9]{40}$/', $token) ? $token : null;
    }

    /**
     * Keranjang milik pengunjung saat ini.
     * - Sudah login : berdasarkan user_id (keranjang tamu otomatis digabung)
     * - Belum login : berdasarkan token di cookie
     *
     * $create = false => tidak menulis ke database; jika belum ada, mengembalikan
     * Cart kosong yang belum tersimpan (exists === false).
     */
    public static function current($create = false)
    {
        if (auth()->check()) {
            $userId = auth()->id();

            static::adoptGuestCart($userId);

            if ($create) {
                return static::firstOrCreate(['user_id' => $userId]);
            }

            return static::where('user_id', $userId)->first() ?: new static(['user_id' => $userId]);
        }

        $token = static::guestToken();
        $cart = $token ? static::where('guest_token', $token)->whereNull('user_id')->first() : null;

        if ($cart) {
            return $cart;
        }

        if (!$create) {
            return new static();
        }

        $token = Str::random(40);
        $cart = static::create(['guest_token' => $token]);

        Cookie::queue(cookie(
            static::GUEST_COOKIE,
            $token,
            static::GUEST_COOKIE_MINUTES,
            '/',
            null,
            request()->isSecure(),
            true,   // httpOnly: tidak bisa dibaca JavaScript
            false,
            'lax'
        ));

        // Agar pemanggilan berikutnya dalam request yang sama memakai keranjang yang sama
        request()->cookies->set(static::GUEST_COOKIE, $token);

        static::pruneStaleGuestCarts();

        return $cart;
    }

    /** Jumlah total barang di keranjang pengunjung saat ini (untuk badge navbar). */
    public static function currentQuantity()
    {
        $cart = static::current(false);

        return $cart->exists ? (int) $cart->items()->sum('quantity') : 0;
    }

    /** Pindahkan isi keranjang tamu (cookie) ke keranjang user yang baru login. */
    protected static function adoptGuestCart($userId)
    {
        $token = static::guestToken();

        if (!$token) {
            return;
        }

        $guestCart = static::where('guest_token', $token)->whereNull('user_id')->first();

        Cookie::queue(Cookie::forget(static::GUEST_COOKIE));
        request()->cookies->remove(static::GUEST_COOKIE);

        if (!$guestCart) {
            return;
        }

        DB::transaction(function () use ($guestCart, $userId) {
            $userCart = static::firstOrCreate(['user_id' => $userId]);

            foreach ($guestCart->items()->get() as $item) {
                $existing = $userCart->items()
                    ->where('merchandise_id', $item->merchandise_id)
                    ->when(
                        $item->merchandise_variant_id,
                        function ($query, $variantId) {
                            $query->where('merchandise_variant_id', $variantId);
                        },
                        function ($query) {
                            $query->whereNull('merchandise_variant_id');
                        }
                    )
                    ->first();

                if ($existing) {
                    // Qty akhir dibatasi stok oleh syncWithStock()
                    $existing->update(['quantity' => min(999, $existing->quantity + $item->quantity)]);
                    $item->delete();
                } else {
                    $item->update(['cart_id' => $userCart->id]);
                }
            }

            $guestCart->delete();
        });
    }

    /** Bersihkan keranjang tamu yang tidak disentuh 30 hari (dijalankan sesekali, tanpa scheduler). */
    protected static function pruneStaleGuestCarts()
    {
        if (random_int(1, 40) !== 1) {
            return;
        }

        static::whereNull('user_id')
            ->where('updated_at', '<', now()->subDays(30))
            ->delete();
    }

    /**
     * Samakan isi keranjang dengan kondisi stok, varian & harga terbaru.
     * - Produk/varian nonaktif atau stok habis => item dihapus
     * - Qty melebihi stok                      => qty diturunkan ke sisa stok
     * - Harga berubah                          => unit_price diperbarui
     *
     * @return array<int,string> daftar pesan perubahan (kosong jika tidak ada)
     */
    public function syncWithStock()
    {
        if (!$this->exists) {
            return [];
        }

        $this->load('items.merchandise.category', 'items.variant');

        $notices = [];

        foreach ($this->items as $item) {
            $merchandise = $item->merchandise;
            $label = $item->label();

            if (!$merchandise || !$merchandise->is_active) {
                $item->delete();
                $notices[] = "\"{$label}\" dihapus dari keranjang karena sudah tidak tersedia.";
                continue;
            }

            $stock = $item->availableStock();

            if ($stock < 1) {
                $item->delete();

                if ($merchandise->has_variants && !$item->variant) {
                    $notices[] = "\"{$label}\" dihapus dari keranjang karena varian berubah. Silakan pilih ulang.";
                } else {
                    $notices[] = "\"{$label}\" dihapus dari keranjang karena stok habis.";
                }

                continue;
            }

            $updates = [];

            if ($item->quantity > $stock) {
                $updates['quantity'] = $stock;
                $notices[] = "Jumlah \"{$label}\" disesuaikan menjadi {$stock} karena stok tersisa {$stock}.";
            }

            if ((float) $item->unit_price !== (float) $merchandise->currentPrice()) {
                $updates['unit_price'] = $merchandise->currentPrice();
                $notices[] = "Harga \"{$label}\" telah diperbarui.";
            }

            if ($updates) {
                $item->update($updates);
            }
        }

        if ($notices) {
            $this->load('items.merchandise.category', 'items.variant');
        }

        return $notices;
    }
}