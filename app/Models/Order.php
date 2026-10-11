<?php

namespace App\Models;

use App\Support\PublicMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    public const SELF_PICKUP_CODE = 'pickup';
    public const STATUS_WAITING_PAYMENT = 'waiting_payment';
    public const STATUS_WAITING_VERIFICATION = 'waiting_verification';
    public const STATUS_PAID = 'paid';
    public const STATUS_PAYMENT_REJECTED = 'payment_rejected';
    public const STATUS_EXPIRED = 'expired';
    public const SHIPPING_STATUS_PENDING = 'pending';
    public const SHIPPING_STATUS_BOOKED = 'booked';
    public const SHIPPING_STATUS_IN_TRANSIT = 'in_transit';
    public const SHIPPING_STATUS_DELIVERED = 'delivered';
    public const SHIPPING_STATUS_FAILED = 'failed';

    protected $fillable = [
        'invoice_number',
        'access_token',
        'user_id',
        'expedition_id',
        'expedition_name',
        'expedition_code',
        'expedition_service_name',
        'expedition_service_code',
        'recipient_name',
        'recipient_email',
        'recipient_phone',
        'province_name',
        'city_name',
        'district_name',
        'village_name',
        'postal_code',
        'shipping_destination_id',
        'full_address',
        'notes',
        'shipping_fee',
        'shipping_etd',
        'subtotal',
        'total',
        'status',
        'shipping_order_no',
        'shipping_airway_bill',
        'shipping_status',
        'shipping_status_label',
        'shipping_payload',
        'shipping_tracking_payload',
        'shipping_synced_at',
        'payment_due_at',
        'payment_proof_path',
        'payment_submitted_at',
        'verified_at',
        'verified_by',
        'verification_note',
        'rejection_note',
    ];

    protected $hidden = [
        'access_token',
    ];

    protected $casts = [
        'shipping_fee' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'shipping_payload' => 'array',
        'shipping_tracking_payload' => 'array',
        'shipping_synced_at' => 'datetime',
        'payment_due_at' => 'datetime',
        'payment_submitted_at' => 'datetime',
        'verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function expedition()
    {
        return $this->belongsTo(Expedition::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /** Token acak panjang untuk membuka invoice tanpa login. */
    public static function generateAccessToken()
    {
        do {
            $token = Str::random(48);
        } while (static::where('access_token', $token)->exists());

        return $token;
    }

    /** Pesanan dibuat tanpa akun (checkout sebagai tamu). */
    public function isGuest()
    {
        return $this->user_id === null;
    }

    /** Link invoice yang bisa dibuka pembeli (tanpa login jika punya token). */
    public function publicUrl()
    {
        if ($this->access_token) {
            return route('orders.guest.show', ['token' => $this->access_token]);
        }

        return $this->user_id ? route('orders.show', $this) : '';
    }

    public function paymentStatusLabel()
    {
        switch ($this->status) {
            case static::STATUS_WAITING_PAYMENT:
                return 'Menunggu Pembayaran';
            case static::STATUS_WAITING_VERIFICATION:
                return 'Menunggu Verifikasi';
            case static::STATUS_PAID:
                return 'Pembayaran Diterima';
            case static::STATUS_PAYMENT_REJECTED:
                return 'Pembayaran Ditolak';
            case static::STATUS_EXPIRED:
                return 'Kedaluwarsa';
            default:
                return ucwords(str_replace('_', ' ', (string) $this->status));
        }
    }

    /**
     * Nomor WhatsApp pembeli dalam format internasional tanpa "+" (mis. 6281234567890).
     * Mengembalikan null jika nomor tidak valid.
     */
    public function whatsappPhone()
    {
        $digits = preg_replace('/\D+/', '', (string) $this->recipient_phone);

        if ($digits === '') {
            return null;
        }

        if (strpos($digits, '00') === 0) {
            $digits = substr($digits, 2);
        }

        if (strpos($digits, '0') === 0) {
            $digits = '62' . substr($digits, 1);
        } elseif (strpos($digits, '8') === 0) {
            $digits = '62' . $digits;
        }

        return strlen($digits) >= 10 && strlen($digits) <= 15 ? $digits : null;
    }

    /** Pesan WhatsApp: detail pesanan, status pembayaran, dan catatan admin (diterima/ditolak). */
    public function whatsappMessage()
    {
        $money = function ($value) {
            return 'Rp ' . number_format((float) $value, 0, ',', '.');
        };

        $lines = [];
        $lines[] = 'Halo ' . $this->recipient_name . ', terima kasih sudah memesan merchandise di Festival Film Horor.';
        $lines[] = '';
        $lines[] = '*Detail Pesanan*';
        $lines[] = 'Invoice: ' . $this->invoice_number;
        $lines[] = 'Tanggal: ' . optional($this->created_at)->translatedFormat('d F Y H:i');
        $lines[] = 'Item:';

        $items = $this->items;
        $shown = 0;

        foreach ($items as $index => $item) {
            if ($shown >= 8) {
                break;
            }

            $lines[] = ($index + 1) . '. ' . $item->merchandise_name . ' x' . $item->quantity . ' - ' . $money($item->subtotal);
            $shown++;
        }

        if ($items->count() > $shown) {
            $lines[] = '... dan ' . ($items->count() - $shown) . ' item lainnya';
        }

        $lines[] = 'Subtotal: ' . $money($this->subtotal);
        $lines[] = 'Ongkir: ' . ($this->isSelfPickup() ? 'Gratis' : $money($this->shipping_fee));
        $lines[] = '*Total: ' . $money($this->total) . '*';
        $lines[] = 'Pengiriman: ' . $this->deliveryMethodLabel();

        if (!$this->isSelfPickup() && trim((string) $this->full_address) !== '') {
            $lines[] = 'Alamat: ' . $this->full_address;
        }

        $lines[] = '';
        $lines[] = '*Status Pembayaran*';
        $lines[] = $this->paymentStatusLabel();

        switch ($this->status) {
            case static::STATUS_PAID:
                if (trim((string) $this->verification_note) !== '') {
                    $lines[] = 'Catatan: ' . trim($this->verification_note);
                }

                if (trim((string) $this->shipping_airway_bill) !== '') {
                    $lines[] = 'No. Resi: ' . $this->shipping_airway_bill;
                }
                break;

            case static::STATUS_PAYMENT_REJECTED:
                $lines[] = 'Alasan ditolak: ' . (trim((string) $this->rejection_note) !== '' ? trim($this->rejection_note) : '-');
                $lines[] = 'Pesanan ini dibatalkan dan stok dikembalikan. Silakan buat pesanan baru jika masih ingin membeli.';
                break;

            case static::STATUS_WAITING_VERIFICATION:
                $lines[] = 'Bukti transfer sudah kami terima dan sedang diperiksa.';
                break;

            case static::STATUS_WAITING_PAYMENT:
                if ($this->payment_due_at) {
                    $lines[] = 'Mohon selesaikan pembayaran sebelum ' . $this->payment_due_at->translatedFormat('d F Y H:i') . ' WIB.';
                }
                break;

            case static::STATUS_EXPIRED:
                $lines[] = 'Batas waktu pembayaran telah habis sehingga pesanan dibatalkan dan stok dikembalikan.';
                break;
        }

        $url = $this->publicUrl();

        if ($url !== '') {
            $lines[] = '';
            $lines[] = 'Lihat pesanan: ' . $url;
        }

        $lines[] = '';
        $lines[] = 'Terima kasih.';

        return implode("\n", $lines);
    }

    /** Link wa.me ke pembeli dengan pesan di atas; null jika nomor tidak valid. */
    public function whatsappUrl()
    {
        $phone = $this->whatsappPhone();

        if (!$phone) {
            return null;
        }

        return 'https://wa.me/' . $phone . '?text=' . rawurlencode($this->whatsappMessage());
    }

    public function canUploadProof()
    {
        return $this->status === static::STATUS_WAITING_PAYMENT
            && $this->payment_due_at
            && now()->lessThanOrEqualTo($this->payment_due_at);
    }

    public function hasShipment()
    {
        return trim((string) $this->shipping_order_no) !== '';
    }

    public function isSelfPickup()
    {
        return trim((string) $this->expedition_code) === static::SELF_PICKUP_CODE;
    }

    public function deliveryMethodLabel()
    {
        if ($this->isSelfPickup()) {
            return $this->expedition_name ?: 'Ambil di Basecamp';
        }

        return trim($this->expedition_name . ' ' . $this->expedition_service_name) ?: '-';
    }

    public function shippingStatusText()
    {
        if ($this->shipping_status_label) {
            return $this->shipping_status_label;
        }

        if (!$this->shipping_status) {
            return '-';
        }

        return ucwords(str_replace('_', ' ', $this->shipping_status));
    }

    public function shippingTrackingEvents()
    {
        return collect(data_get($this->shipping_tracking_payload, 'histories', []))
            ->filter(function ($event) {
                return is_array($event);
            })
            ->values();
    }

    public function normalizedPaymentProofPath()
    {
        return PublicMedia::normalizeStoragePath($this->payment_proof_path);
    }

    public function paymentProofUrl()
    {
        return PublicMedia::url($this->payment_proof_path);
    }

    public function deleteStoredPaymentProof()
    {
        $path = $this->normalizedPaymentProofPath();

        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}