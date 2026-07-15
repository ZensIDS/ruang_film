<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Merchandise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartInteractionTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_quantity_update_returns_json_summary_and_badge_counts()
    {
        $user = User::factory()->role('peserta')->create();
        $cart = Cart::factory()->create([
            'user_id' => $user->id,
        ]);
        $merchandise = Merchandise::factory()->create([
            'price' => 75000,
            'qty_stock' => 10,
        ]);
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'merchandise_id' => $merchandise->id,
            'quantity' => 2,
            'unit_price' => 75000,
        ]);

        $this->actingAs($user)
            ->putJson(route('cart.update', $cartItem), [
                'quantity' => 4,
            ])
            ->assertOk()
            ->assertJson([
                'item_id' => $cartItem->id,
                'item_quantity' => 4,
                'cart_total_quantity' => 4,
                'cart_count_display' => '4',
                'cart_subtotal_formatted' => '300.000',
                'item_subtotal_formatted' => '300.000',
                'empty' => false,
            ]);

        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 4,
        ]);
    }

    public function test_cart_delete_returns_empty_payload_when_last_item_is_removed()
    {
        $user = User::factory()->role('peserta')->create();
        $cart = Cart::factory()->create([
            'user_id' => $user->id,
        ]);
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'quantity' => 1,
            'unit_price' => 50000,
        ]);

        $this->actingAs($user)
            ->deleteJson(route('cart.destroy', $cartItem))
            ->assertOk()
            ->assertJson([
                'deleted_item_id' => $cartItem->id,
                'cart_total_quantity' => 0,
                'cart_count_display' => '0',
                'cart_subtotal_formatted' => '0',
                'empty' => true,
            ]);

        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id,
        ]);
    }

    public function test_cart_update_allows_owner_when_cart_user_id_is_hydrated_as_string()
    {
        $user = User::factory()->role('umum')->create();
        $cart = Cart::factory()->create([
            'user_id' => $user->id,
        ]);
        $merchandise = Merchandise::factory()->create([
            'price' => 50000,
            'qty_stock' => 10,
        ]);
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'merchandise_id' => $merchandise->id,
            'quantity' => 1,
            'unit_price' => 50000,
        ]);

        $this->app['router']->bind('cartItem', function ($value) {
            $boundCartItem = CartItem::with(['cart', 'merchandise'])->findOrFail($value);
            $boundCart = $boundCartItem->cart;

            $boundCart->setRawAttributes(array_merge($boundCart->getAttributes(), [
                'user_id' => (string) $boundCart->getRawOriginal('user_id'),
            ]), true);

            $boundCartItem->setRelation('cart', $boundCart);

            return $boundCartItem;
        });

        $this->actingAs($user)
            ->putJson(route('cart.update', $cartItem), [
                'quantity' => 3,
            ])
            ->assertOk()
            ->assertJson([
                'item_id' => $cartItem->id,
                'item_quantity' => 3,
                'cart_total_quantity' => 3,
            ]);
    }
}
