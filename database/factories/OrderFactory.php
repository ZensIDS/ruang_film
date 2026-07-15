<?php

namespace Database\Factories;

use App\Models\Expedition;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'invoice_number' => 'INV-RF-' . now()->format('Ymd') . '-' . $this->faker->unique()->numerify('####'),
            'user_id' => User::factory(),
            'expedition_id' => Expedition::factory(),
            'expedition_name' => 'JNE',
            'expedition_code' => 'jne',
            'expedition_service_name' => 'REG',
            'expedition_service_code' => 'reg',
            'recipient_name' => $this->faker->name(),
            'recipient_email' => $this->faker->safeEmail(),
            'recipient_phone' => $this->faker->numerify('08##########'),
            'province_name' => 'Jawa Timur',
            'city_name' => 'Pacitan',
            'district_name' => 'Kota',
            'village_name' => 'Kelurahan',
            'postal_code' => '63511',
            'shipping_destination_id' => '68424',
            'full_address' => $this->faker->address(),
            'notes' => null,
            'shipping_fee' => 15000,
            'shipping_etd' => '2-3',
            'subtotal' => 100000,
            'total' => 115000,
            'status' => Order::STATUS_WAITING_PAYMENT,
            'shipping_order_no' => null,
            'shipping_airway_bill' => null,
            'shipping_status' => null,
            'shipping_status_label' => null,
            'shipping_payload' => null,
            'shipping_tracking_payload' => null,
            'shipping_synced_at' => null,
            'payment_due_at' => now()->addDay(),
            'payment_proof_path' => null,
            'payment_submitted_at' => null,
            'verified_at' => null,
            'verified_by' => null,
            'verification_note' => null,
            'rejection_note' => null,
        ];
    }
}
