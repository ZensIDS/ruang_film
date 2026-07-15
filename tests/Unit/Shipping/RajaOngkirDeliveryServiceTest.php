<?php

namespace Tests\Unit\Shipping;

use App\Exceptions\ShippingException;
use App\Models\AppSetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Shipping\RajaOngkirDeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RajaOngkirDeliveryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.rajaongkir.order_base_url' => 'https://api.collaborator.komerce.id/order/api/v1',
            'services.rajaongkir.api_key_shipping_delivery' => 'delivery-key',
            'services.rajaongkir.origin_destination_id' => '68423',
            'services.rajaongkir.shipper' => [
                'brand_name' => 'Ruang Film',
                'name' => 'Admin Gudang',
                'phone' => '081234567890',
                'email' => 'warehouse@ruangfilm.test',
                'address' => 'Pacitan Warehouse',
            ],
        ]);
    }

    public function test_delivery_service_normalizes_order_creation_and_tracking_sync()
    {
        $order = Order::factory()->create([
            'status' => Order::STATUS_PAID,
            'shipping_destination_id' => '68424',
            'expedition_code' => 'jne',
            'expedition_service_name' => 'REG',
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'quantity' => 1,
            'weight' => 250,
            'subtotal' => 100000,
        ]);

        Http::fake([
            'https://api.collaborator.komerce.id/order/api/v1/orders/store' => Http::response([
                'data' => [
                    'order_no' => 'KOM123',
                    'cnote' => 'AWB123',
                    'status' => 'Created',
                ],
            ], 200),
            'https://api.collaborator.komerce.id/order/api/v1/orders/detail*' => Http::response([
                'data' => [
                    'order_no' => 'KOM123',
                    'cnote' => 'AWB123',
                    'status' => 'Delivered',
                    'shipping' => 'JNE',
                ],
            ], 200),
            'https://api.collaborator.komerce.id/order/api/v1/orders/history-airway-bill*' => Http::response([
                'data' => [[
                    'date' => '2026-06-23 10:00:00',
                    'city_name' => 'Pacitan',
                    'status' => 'Diterima',
                    'description' => 'Paket diterima',
                ]],
            ], 200),
        ]);

        $service = $this->app->make(RajaOngkirDeliveryService::class);

        $created = $service->createShipment($order->load('items'));
        $order->forceFill($created)->save();

        $synced = $service->syncShipment($order->fresh());

        $this->assertSame('KOM123', $created['shipping_order_no']);
        $this->assertSame('AWB123', $created['shipping_airway_bill']);
        $this->assertSame(Order::SHIPPING_STATUS_BOOKED, $created['shipping_status']);
        $this->assertSame(Order::SHIPPING_STATUS_DELIVERED, $synced['shipping_status']);
        $this->assertCount(1, $synced['shipping_tracking_payload']['histories']);
    }

    public function test_delivery_service_wraps_connection_failures()
    {
        $order = Order::factory()->create([
            'status' => Order::STATUS_PAID,
            'shipping_destination_id' => '68424',
            'expedition_code' => 'jne',
            'expedition_service_name' => 'REG',
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'quantity' => 1,
        ]);

        Http::fake([
            'https://api.collaborator.komerce.id/order/api/v1/orders/store' => function () {
                throw new ConnectionException('timeout');
            },
        ]);

        $this->expectException(ShippingException::class);

        $service = $this->app->make(RajaOngkirDeliveryService::class);
        $service->createShipment($order->load('items'));
    }

    public function test_delivery_service_uses_fallback_origin_when_primary_origin_matches_destination()
    {
        AppSetting::setValue('shipping_origin_rajaongkir_destination_id', '68424');
        AppSetting::setValue('shipping_origin_laravolt_auto_destination_id', '68424');

        $order = Order::factory()->create([
            'status' => Order::STATUS_PAID,
            'shipping_destination_id' => '68424',
            'expedition_code' => 'jne',
            'expedition_service_name' => 'REG',
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'quantity' => 1,
            'weight' => 250,
            'subtotal' => 100000,
        ]);

        Http::fake([
            'https://api.collaborator.komerce.id/order/api/v1/orders/store' => function ($request) {
                $this->assertSame(
                    (int) AppSetting::shippingOriginDestinationId('68424'),
                    (int) data_get($request->data(), 'shipper_destination_id')
                );

                return Http::response([
                    'data' => [
                        'order_no' => 'KOM124',
                        'cnote' => 'AWB124',
                        'status' => 'Created',
                    ],
                ], 200);
            },
        ]);

        $service = $this->app->make(RajaOngkirDeliveryService::class);
        $created = $service->createShipment($order->load('items'));

        $this->assertSame('KOM124', $created['shipping_order_no']);
    }
}
