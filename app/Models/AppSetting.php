<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class AppSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    public static function getValue($key, $default = null)
    {
        try {
            return optional(static::where('key', $key)->first())->value ?? $default;
        } catch (QueryException $exception) {
            return $default;
        }
    }

    public static function setValue($key, $value)
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value]
        );
    }

    public static function paymentDueHours()
    {
        return (int) static::getValue('payment_due_hours', 24);
    }

    public static function shippingOriginDestinationId($destinationId = null)
    {
        return static::shippingOriginDestinationIds($destinationId)[0] ?? '';
    }

    public static function shippingOriginDestinationIds($destinationId = null)
    {
        $destinationId = trim((string) $destinationId);

        $originIds = collect([
            static::getValue('shipping_origin_rajaongkir_destination_id', ''),
            static::getValue('shipping_origin_laravolt_auto_destination_id', ''),
            config('services.rajaongkir.fallback_origin_destination_id', config('services.rajaongkir.origin_destination_id')),
            config('services.rajaongkir.origin_destination_id', ''),
            config('services.rajaongkir.legacy_origin_district_id', ''),
        ])->map(function ($value) {
            return trim((string) $value);
        })->filter()->unique()->values();

        if ($destinationId === '') {
            return $originIds->all();
        }

        return $originIds
            ->reject(function ($originId) use ($destinationId) {
                return $originId === $destinationId;
            })
            ->values()
            ->all();
    }
}
