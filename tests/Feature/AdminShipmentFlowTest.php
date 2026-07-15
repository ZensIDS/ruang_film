<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminShipmentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.rajaongkir.order_base_url' => 'https://api.collaborator.komerce.id/order/api/v1',
            'services.rajaongkir.api_key_shipping_delivery' => 'delivery-key',
            'services.rajaongkir.origin_destination_id' => '68423',
            'services.rajaongkir.komship_enabled' => true,
            'services.rajaongkir.shipper' => [
                'brand_name' => 'Ruang Film',
                'name' => 'Admin Gudang',
                'phone' => '081234567890',
                'email' => 'warehouse@ruangfilm.test',
                'address' => 'Pacitan Warehouse',
            ],
        ]);
    }

    public function test_admin_can_create_and_sync_shipment_for_paid_order()
    {
        $admin = User::factory()->role('admin')->create();
        $buyer = User::factory()->role('peserta')->create();
        $order = Order::factory()->create([
            'user_id' => $buyer->id,
            'status' => Order::STATUS_PAID,
            'shipping_destination_id' => '68424',
            'expedition_code' => 'jne',
            'expedition_service_name' => 'REG',
            'shipping_order_no' => null,
            'shipping_airway_bill' => null,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'quantity' => 1,
            'weight' => 300,
            'subtotal' => 100000,
        ]);

        Http::fake([
            'https://api.collaborator.komerce.id/order/api/v1/orders/store' => Http::response([
                'data' => [
                    'order_no' => 'KOM202606230001',
                    'cnote' => 'AWB20260623',
                    'status' => 'Created',
                ],
            ], 200),
            'https://api.collaborator.komerce.id/order/api/v1/orders/detail*' => Http::response([
                'data' => [
                    'order_no' => 'KOM202606230001',
                    'cnote' => 'AWB20260623',
                    'status' => 'In Transit',
                    'shipping' => 'JNE',
                ],
            ], 200),
            'https://api.collaborator.komerce.id/order/api/v1/orders/history-airway-bill*' => Http::response([
                'data' => [[
                    'date' => '2026-06-23 10:00:00',
                    'city_name' => 'Pacitan',
                    'status' => 'Diterima',
                    'description' => 'Paket diterima oleh penerima',
                ]],
            ], 200),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.orders.shipment.store', $order))
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'shipping_order_no' => 'KOM202606230001',
            'shipping_airway_bill' => 'AWB20260623',
            'shipping_status' => Order::SHIPPING_STATUS_BOOKED,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.orders.shipment.sync', $order->fresh()))
            ->assertRedirect();

        $order->refresh();

        $this->assertSame(Order::SHIPPING_STATUS_IN_TRANSIT, $order->shipping_status);
        $this->assertSame('In Transit', $order->shipping_status_label);
        $this->assertNotNull($order->shipping_tracking_payload);
        $this->assertCount(1, $order->shippingTrackingEvents());
    }

    public function test_admin_cannot_create_duplicate_or_unpaid_shipments_and_non_admin_is_forbidden()
    {
        $admin = User::factory()->role('admin')->create();
        $buyer = User::factory()->role('peserta')->create();

        $paidOrder = Order::factory()->create([
            'user_id' => $buyer->id,
            'status' => Order::STATUS_PAID,
            'shipping_order_no' => 'KOM-READY',
        ]);

        $unpaidOrder = Order::factory()->create([
            'user_id' => $buyer->id,
            'status' => Order::STATUS_WAITING_PAYMENT,
            'shipping_order_no' => null,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.orders.shipment.store', $paidOrder))
            ->assertRedirect()
            ->assertSessionHas('warning');

        $this->actingAs($admin)
            ->post(route('admin.orders.shipment.store', $unpaidOrder))
            ->assertRedirect()
            ->assertSessionHas('warning');

        $this->actingAs($buyer)
            ->post(route('admin.orders.shipment.store', $unpaidOrder))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('warning');
    }

    public function test_admin_cannot_create_shipment_when_komship_is_disabled()
    {
        config([
            'services.rajaongkir.komship_enabled' => false,
        ]);

        $admin = User::factory()->role('admin')->create();
        $buyer = User::factory()->role('peserta')->create();
        $order = Order::factory()->create([
            'user_id' => $buyer->id,
            'status' => Order::STATUS_PAID,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.orders.shipment.store', $order))
            ->assertRedirect()
            ->assertSessionHas('warning', 'Fitur Komship sedang dinonaktifkan pada environment ini.');
    }

    public function test_admin_can_update_airway_bill_without_touching_other_shipment_identifiers()
    {
        $admin = User::factory()->role('admin')->create();
        $buyer = User::factory()->role('peserta')->create();
        $order = Order::factory()->create([
            'user_id' => $buyer->id,
            'status' => Order::STATUS_PAID,
            'shipping_order_no' => 'KOM202606230001',
            'shipping_airway_bill' => 'AWB-OLD-001',
            'shipping_status' => Order::SHIPPING_STATUS_IN_TRANSIT,
            'shipping_status_label' => 'In Transit',
            'shipping_tracking_payload' => [
                'detail' => ['cnote' => 'AWB-OLD-001'],
                'history' => ['old' => true],
                'histories' => [['status_label' => 'In Transit']],
            ],
            'shipping_synced_at' => now(),
        ]);

        $this->actingAs($admin)
            ->from(route('admin.orders.show', $order))
            ->patch(route('admin.orders.airway-bill.update', $order), [
                'shipping_airway_bill' => 'AWB-NEW-123',
            ])
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHas('success', 'Nomor resi berhasil diperbarui.');

        $order->refresh();

        $this->assertSame('KOM202606230001', $order->shipping_order_no);
        $this->assertSame('AWB-NEW-123', $order->shipping_airway_bill);
        $this->assertSame(Order::SHIPPING_STATUS_IN_TRANSIT, $order->shipping_status);
        $this->assertSame('In Transit', $order->shipping_status_label);
        $this->assertNull($order->shipping_tracking_payload);
        $this->assertNull($order->shipping_synced_at);
    }
}
