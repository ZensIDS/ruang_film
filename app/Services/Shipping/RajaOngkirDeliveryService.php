<?php

namespace App\Services\Shipping;

use App\Exceptions\ShippingException;
use App\Models\AppSetting;
use App\Models\Order;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RajaOngkirDeliveryService
{
    public function createShipment(Order $order)
    {
        $payload = $this->buildOrderPayload($order);
        $response = $this->requestJson('POST', '/orders/store', $payload);
        $data = $this->extractData($response);

        $orderNo = (string) ($this->findValueByKeys($data, ['order_no', 'orderNo', 'no_order']) ?: '');
        $airwayBill = (string) ($this->findValueByKeys($data, ['airway_bill', 'airwayBill', 'cnote', 'awb', 'resi']) ?: '');
        $statusLabel = (string) ($this->findValueByKeys($data, ['status_label', 'status']) ?: 'Booked');
        $status = $this->normalizeShippingStatus($statusLabel ?: Order::SHIPPING_STATUS_BOOKED);

        if ($orderNo === '') {
            throw new ShippingException('Komship order number not found in response.', 'Nomor order Komship tidak ditemukan dari respons API.');
        }

        return [
            'shipping_order_no' => $orderNo,
            'shipping_airway_bill' => $airwayBill,
            'shipping_status' => $status ?: Order::SHIPPING_STATUS_BOOKED,
            'shipping_status_label' => $statusLabel ?: 'Booked',
            'shipping_payload' => $response,
            'shipping_tracking_payload' => [
                'detail' => $response,
                'history' => [],
                'histories' => [],
            ],
        ];
    }

    public function syncShipment(Order $order)
    {
        $orderNo = trim((string) $order->shipping_order_no);

        if ($orderNo === '') {
            throw new ShippingException('Order does not have shipping order number.', 'Shipment belum pernah dibuat untuk order ini.');
        }

        $detailPayload = $this->requestJson('GET', '/orders/detail', [
            'order_no' => $orderNo,
        ]);
        $detailData = $this->extractData($detailPayload);

        $airwayBill = (string) ($this->findValueByKeys($detailData, ['airway_bill', 'airwayBill', 'cnote', 'awb', 'resi']) ?: $order->shipping_airway_bill);
        $shippingCode = strtoupper((string) ($this->findValueByKeys($detailData, ['shipping', 'courier', 'shipping_code']) ?: $order->expedition_code));
        $statusLabel = (string) ($this->findValueByKeys($detailData, ['status_label', 'status']) ?: '');

        $historyPayload = [];
        $histories = [];

        if ($airwayBill !== '' && $shippingCode !== '') {
            $historyPayload = $this->requestJson('GET', '/orders/history-airway-bill', [
                'shipping' => $shippingCode,
                'airway_bill' => $airwayBill,
            ]);
            $histories = $this->normalizeHistoryEvents($historyPayload);
        }

        $normalizedStatus = $this->normalizeShippingStatus($statusLabel);

        if (!$normalizedStatus && !empty($histories)) {
            $normalizedStatus = $this->normalizeShippingStatus($histories[0]['status_label']);
        }

        return [
            'shipping_order_no' => $orderNo,
            'shipping_airway_bill' => $airwayBill,
            'shipping_status' => $normalizedStatus ?: Order::SHIPPING_STATUS_PENDING,
            'shipping_status_label' => $statusLabel ?: ($histories[0]['status_label'] ?? 'Pending'),
            'shipping_payload' => $detailPayload,
            'shipping_tracking_payload' => [
                'detail' => $detailPayload,
                'history' => $historyPayload,
                'histories' => $histories,
            ],
        ];
    }

    protected function buildOrderPayload(Order $order)
    {
        $originDestinationId = trim((string) AppSetting::shippingOriginDestinationId($order->shipping_destination_id));
        $shipper = (array) config('services.rajaongkir.shipper', []);

        if ($originDestinationId === '') {
            throw new ShippingException('Origin destination ID is missing.', 'Konfigurasi origin pengirim Komship belum lengkap.');
        }

        foreach (['brand_name', 'name', 'phone', 'email', 'address'] as $requiredKey) {
            if (trim((string) Arr::get($shipper, $requiredKey)) === '') {
                throw new ShippingException('Shipper configuration is incomplete.', 'Konfigurasi pengirim Komship belum lengkap.');
            }
        }

        if (trim((string) $order->shipping_destination_id) === '') {
            throw new ShippingException('Order is missing shipping destination ID.', 'Order belum memiliki tujuan pengiriman RajaOngkir.');
        }

        if (trim((string) $order->expedition_code) === '' || trim((string) $order->expedition_service_name) === '') {
            throw new ShippingException('Order is missing expedition data.', 'Kurir order belum lengkap untuk membuat shipment.');
        }

        return [
            'order_date' => optional($order->created_at)->format('Y-m-d') ?: now()->format('Y-m-d'),
            'brand_name' => $shipper['brand_name'],
            'shipper_name' => $shipper['name'],
            'shipper_phone' => $shipper['phone'],
            'shipper_destination_id' => (int) $originDestinationId,
            'shipper_address' => $shipper['address'],
            'shipper_email' => $shipper['email'],
            'origin_pin_point' => config('services.rajaongkir.origin_pin_point'),
            'receiver_name' => $order->recipient_name,
            'receiver_phone' => $order->recipient_phone,
            'receiver_destination_id' => (int) $order->shipping_destination_id,
            'receiver_address' => $order->full_address,
            'destination_pin_point' => null,
            'shipping' => strtoupper((string) $order->expedition_code),
            'shipping_type' => $order->expedition_service_name,
            'payment_method' => 'BANK TRANSFER',
            'shipping_cost' => (int) round((float) $order->shipping_fee),
            'shipping_cashback' => 0,
            'service_fee' => 0,
            'additional_cost' => 0,
            'grand_total' => (int) round((float) $order->total),
            'cod_value' => 0,
            'insurance_value' => 0,
            'order_details' => $order->items->map(function ($item) {
                return [
                    'product_name' => $item->merchandise_name,
                    'product_variant_name' => '',
                    'product_price' => (int) round((float) $item->unit_price),
                    'product_weight' => (int) $item->weight,
                    'product_width' => 0,
                    'product_height' => 0,
                    'product_length' => 0,
                    'qty' => (int) $item->quantity,
                    'subtotal' => (int) round((float) $item->subtotal),
                ];
            })->values()->all(),
        ];
    }

    protected function requestJson($method, $uri, array $payload = [])
    {
        try {
            if ($method === 'GET') {
                $response = $this->client()->get($uri, $payload);
            } else {
                $response = $this->client()->post($uri, $payload);
            }
        } catch (RequestException $exception) {
            throw new ShippingException(
                $exception->getMessage(),
                $this->extractErrorMessage(optional($exception->response)->json()) ?: 'Permintaan shipment gagal diproses.',
                0,
                $exception
            );
        } catch (ConnectionException $exception) {
            throw new ShippingException($exception->getMessage(), 'Gagal terhubung ke layanan Komship.', 0, $exception);
        }

        return $this->ensureSuccessfulResponse($response);
    }

    protected function client()
    {
        return Http::baseUrl(rtrim((string) config('services.rajaongkir.order_base_url'), '/'))
            ->acceptJson()
            ->withHeaders([
                'x-api-key' => (string) config('services.rajaongkir.api_key_shipping_delivery'),
            ])
            ->timeout((int) config('services.rajaongkir.timeout', 20))
            ->retry(
                (int) config('services.rajaongkir.retry_times', 2),
                (int) config('services.rajaongkir.retry_sleep_ms', 250)
            );
    }

    protected function ensureSuccessfulResponse(Response $response)
    {
        if (!$response->successful()) {
            throw new ShippingException(
                'Komship request failed with status ' . $response->status() . '.',
                $this->extractErrorMessage($response->json()) ?: 'Permintaan shipment gagal diproses.'
            );
        }

        $payload = $response->json();

        if (data_get($payload, 'success') === false || (int) data_get($payload, 'meta.code', 200) >= 400) {
            throw new ShippingException(
                $this->extractErrorMessage($payload) ?: 'Komship returned an error.',
                $this->extractErrorMessage($payload) ?: 'Permintaan shipment gagal diproses.'
            );
        }

        return $payload;
    }

    protected function extractData($payload)
    {
        $data = data_get($payload, 'data');

        if (is_array($data)) {
            return $data;
        }

        return is_array($payload) ? $payload : [];
    }

    protected function normalizeHistoryEvents($payload)
    {
        $items = collect(data_get($payload, 'data', []));

        if ($items->isEmpty()) {
            $items = collect(data_get($payload, 'history', []));
        }

        return $items
            ->filter(function ($item) {
                return is_array($item);
            })
            ->map(function ($item) {
                $statusLabel = (string) ($this->findValueByKeys($item, ['status', 'description', 'desc', 'note']) ?: 'Pending');

                return [
                    'date' => (string) ($this->findValueByKeys($item, ['date', 'datetime', 'created_at', 'time']) ?: ''),
                    'location' => (string) ($this->findValueByKeys($item, ['location', 'city_name', 'city']) ?: ''),
                    'status_label' => $statusLabel,
                    'status' => $this->normalizeShippingStatus($statusLabel) ?: Order::SHIPPING_STATUS_PENDING,
                    'description' => (string) ($this->findValueByKeys($item, ['description', 'desc', 'note', 'status']) ?: $statusLabel),
                ];
            })
            ->values()
            ->all();
    }

    protected function extractErrorMessage($payload)
    {
        foreach (['meta.message', 'message', 'error', 'errors.0.message'] as $key) {
            $value = data_get($payload, $key);

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    protected function findValueByKeys($payload, array $keys)
    {
        if (!is_array($payload)) {
            return null;
        }

        foreach ($keys as $key) {
            $value = Arr::get($payload, $key);

            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        foreach ($payload as $value) {
            if (is_array($value)) {
                $nested = $this->findValueByKeys($value, $keys);

                if ($nested !== null && $nested !== '') {
                    return $nested;
                }
            }
        }

        return null;
    }

    protected function normalizeShippingStatus($value)
    {
        $normalized = Str::lower(trim((string) $value));

        if ($normalized === '') {
            return null;
        }

        if (Str::contains($normalized, ['deliver', 'diterima', 'received', 'success'])) {
            return Order::SHIPPING_STATUS_DELIVERED;
        }

        if (Str::contains($normalized, ['transit', 'antar', 'kirim', 'manifest', 'pickup', 'process', 'shipped'])) {
            return Order::SHIPPING_STATUS_IN_TRANSIT;
        }

        if (Str::contains($normalized, ['cancel', 'return', 'gagal', 'failed', 'reject'])) {
            return Order::SHIPPING_STATUS_FAILED;
        }

        if (Str::contains($normalized, ['book', 'created', 'siap'])) {
            return Order::SHIPPING_STATUS_BOOKED;
        }

        if (Str::contains($normalized, ['pending', 'wait', 'menunggu'])) {
            return Order::SHIPPING_STATUS_PENDING;
        }

        return Order::SHIPPING_STATUS_PENDING;
    }
}
