<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Merchandise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index()
    {
        // Tamu & user login sama-sama punya keranjang (tamu lewat cookie token)
        $cart = Cart::current(false);

        // Sinkronkan qty/harga keranjang dengan stok terbaru
        $notices = $cart->syncWithStock();

        if ($notices) {
            session()->now('warning', implode(' ', $notices));
        }

        return view('landing.cart', [
            'cart' => $cart,
        ]);
    }

    public function store(Request $request, Merchandise $merchandise)
    {
        $request->validate([
            'quantity' => 'nullable|integer|min:1|max:999',
            'merchandise_variant_id' => 'nullable|integer',
        ]);

        if (!$merchandise->is_active) {
            return $this->cartErrorResponse($request, 'Merchandise tidak tersedia.');
        }

        $quantity = (int) ($request->quantity ?: 1);
        $variantId = $request->input('merchandise_variant_id');

        $result = DB::transaction(function () use ($merchandise, $quantity, $variantId) {
            $cart = Cart::current(true);

            // Kunci keranjang milik user agar klik ganda / dua tab tidak membuat baris dobel
            Cart::whereKey($cart->id)->lockForUpdate()->first();

            // Baca ulang data terbaru (stok bisa berubah sejak halaman dibuka)
            $fresh = Merchandise::find($merchandise->id);

            if (!$fresh || !$fresh->is_active) {
                return ['error' => 'Merchandise tidak tersedia.'];
            }

            $variant = null;

            if ($fresh->has_variants) {
                if (!$variantId) {
                    return ['error' => 'Silakan pilih varian terlebih dahulu.'];
                }

                $variant = $fresh->variants()->where('is_active', true)->find($variantId);

                if (!$variant) {
                    return ['error' => 'Varian yang dipilih tidak tersedia.'];
                }

                $stock = (int) $variant->qty_stock;
            } else {
                $stock = (int) $fresh->qty_stock;
            }

            if ($stock < 1) {
                return ['error' => 'Stok habis.'];
            }

            $item = $cart->items()
                ->where('merchandise_id', $fresh->id)
                ->when(
                    $variant,
                    function ($query) use ($variant) {
                        $query->where('merchandise_variant_id', $variant->id);
                    },
                    function ($query) {
                        $query->whereNull('merchandise_variant_id');
                    }
                )
                ->first();

            $inCart = $item ? (int) $item->quantity : 0;

            if ($inCart + $quantity > $stock) {
                $message = $inCart > 0
                    ? "Stok hanya tersisa {$stock} dan di keranjang kamu sudah ada {$inCart}."
                    : "Stok hanya tersisa {$stock}.";

                return ['error' => $message];
            }

            if ($item) {
                $item->update([
                    'quantity' => $inCart + $quantity,
                    'unit_price' => $fresh->currentPrice(),
                ]);
            } else {
                $item = $cart->items()->create([
                    'merchandise_id' => $fresh->id,
                    'merchandise_variant_id' => $variant ? $variant->id : null,
                    'quantity' => $quantity,
                    'unit_price' => $fresh->currentPrice(),
                ]);
            }

            return ['item' => $item, 'cart' => $cart];
        });

        if (isset($result['error'])) {
            return $this->cartErrorResponse($request, $result['error']);
        }

        if ($request->expectsJson()) {
            return $this->cartJsonResponse(
                $result['cart']->fresh()->load('items'),
                'Berhasil ditambahkan ke keranjang.',
                $result['item']->fresh()
            );
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

        if (!$merchandise || $request->quantity > $cartItem->availableStock()) {
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
        $cart = Cart::current(false);

        // Item harus milik keranjang pengunjung saat ini (user login atau tamu)
        abort_unless($cart->exists && (int) $cartItem->cart_id === (int) $cart->id, 403);
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