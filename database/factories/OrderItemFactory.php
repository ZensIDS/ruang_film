<?php

namespace Database\Factories;

use App\Models\Merchandise;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition()
    {
        return [
            'order_id' => Order::factory(),
            'merchandise_id' => Merchandise::factory(),
            'merchandise_name' => $this->faker->words(2, true),
            'merchandise_slug' => $this->faker->slug(),
            'merchandise_image' => null,
            'unit_price' => 50000,
            'quantity' => 2,
            'weight' => 200,
            'subtotal' => 100000,
        ];
    }
}
