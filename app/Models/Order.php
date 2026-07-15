<?php

namespace App\Models;

use App\Support\PublicMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
