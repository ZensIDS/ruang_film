<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Merchandise;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = Cart::firstOrCreate(
            ['user_id' => auth()->id()]
        );

        $cart->load('items.merchandise.category');

        return view('landing.cart', [
            'cart' => $cart,
        ]);
    }

    public function store(Request $request, Merchandise $merchandise)
    {
        $request->validate([
            'quantity' => 'nullable|integer|min:1',
        ]);

        if (!$merchandise->is_active) {
            return back()->with('warning', 'Merchandise tidak tersedia.');
        }

        $quantity = (int) ($request->quantity ?: 1);

        if ($quantity > $merchandise->qty_stock) {
            return back()->with('warning', 'Jumlah melebihi stok yang tersedia.');
        }

        $cart = Cart::firstOrCreate(['user_id' => auth()->id()]);
        $item = $cart->items()->where('merchandise_id', $merchandise->id)->first();

        if ($item) {
            $newQty = $item->quantity + $quantity;

            if ($newQty > $merchandise->qty_stock) {
                return back()->with('warning', 'Jumlah total di keranjang melebihi stok.');
            }

            $item->update([
                'quantity' => $newQty,
                'unit_price' => $merchandise->currentPrice(),
            ]);
        } else {
            $cart->items()->create([
                'merchandise_id' => $merchandise->id,
                'quantity' => $quantity,
                'unit_price' => $merchandise->currentPrice(),
            ]);
        }

        return back()->with('success', 'Merchandise berhasil ditambahkan ke keranjang.');
    }

    public function update(Request $request, CartItem $cartItem)
    {
        $this->authorizeCartItem($cartItem);

        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $merchandise = $cartItem->merchandise;

        if (!$merchandise || $request->quantity > $merchandise->qty_stock) {
            return $this->cartErrorResponse($request, 'Jumlah melebihi stok yang tersedia.');
        }

        $cartItem->update([
            'quantity' => $request->quantity,
            'unit_price' => $merchandise->currentPrice(),
        ]);

        if ($request->expectsJson()) {
            $cartItem->refresh();

            return $this->cartJsonResponse(
                $cartItem->cart->fresh()->load('items'),
                'Keranjang berhasil diperbarui.',
                $cartItem
            );
        }

        return back()->with('success', 'Keranjang berhasil diperbarui.');
    }

    public function destroy(Request $request, CartItem $cartItem)
    {
        $this->authorizeCartItem($cartItem);

        $cart = $cartItem->cart;
        $deletedItemId = $cartItem->id;
        $cartItem->delete();

        if ($request->expectsJson()) {
            return $this->cartJsonResponse(
                $cart->fresh()->load('items'),
                'Item keranjang berhasil dihapus.',
                null,
                ['deleted_item_id' => $deletedItemId]
            );
        }

        return back()->with('success', 'Item keranjang berhasil dihapus.');
    }

    protected function authorizeCartItem(CartItem $cartItem)
    {
        abort_unless(
            $cartItem->cart
                && auth()->user()
                && auth()->user()->ownsUserId($cartItem->cart->user_id),
            403
        );
    }

    protected function cartErrorResponse(Request $request, $message, $status = 422)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ], $status);
        }

        return back()->with('warning', $message);
    }

    protected function cartJsonResponse(Cart $cart, $message, CartItem $cartItem = null, array $extra = [])
    {
        $cart->loadMissing('items');
        $totalQuantity = $cart->totalQuantity();

        $payload = array_merge([
            'message' => $message,
            'cart_total_quantity' => $totalQuantity,
            'cart_count_display' => $totalQuantity > 99 ? '99+' : (string) $totalQuantity,
            'cart_subtotal_formatted' => number_format($cart->subtotal(), 0, ',', '.'),
            'empty' => $cart->items->isEmpty(),
        ], $extra);

        if ($cartItem) {
            $payload['item_id'] = $cartItem->id;
            $payload['item_quantity'] = $cartItem->quantity;
            $payload['item_subtotal_formatted'] = number_format($cartItem->subtotal(), 0, ',', '.');
        }

        return response()->json($payload);
    }
}
