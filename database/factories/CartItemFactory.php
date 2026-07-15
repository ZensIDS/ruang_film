<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Merchandise;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartItemFactory extends Factory
{
    protected $model = CartItem::class;

    public function definition()
    {
        return [
            'cart_id' => Cart::factory(),
            'merchandise_id' => Merchandise::factory(),
            'quantity' => 1,
            'unit_price' => 100000,
        ];
    }
}
