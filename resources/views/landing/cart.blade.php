@extends('layouts.landing.master')
@section('main')
<main class="relative z-10">
    <section class="max-w-6xl mx-auto px-6 md:px-10 py-16">
        <div class="mb-8">
            <p class="text-purple-400 text-sm uppercase tracking-wider font-semibold mb-2">KERANJANG</p>
            <h1 class="text-3xl md:text-5xl font-bold border-l-8 border-purple-500 pl-6 tracking-tight">Keranjang Belanja</h1>
        </div>

        @if($cart->items->isEmpty())
        <div class="glass-card rounded-3xl p-10 text-center text-gray-300">
            Keranjang masih kosong.
            <div class="mt-6">
                <a href="{{ route('merchandise') }}" class="btn-gradient px-6 py-3 rounded-full text-white font-semibold">Belanja Merchandise</a>
            </div>
        </div>
        @else
        <div id="cart-feedback" class="hidden rounded-2xl border px-5 py-4 mb-6 text-sm"></div>
        <div class="grid grid-cols-1 lg:grid-cols-[1.8fr_1fr] gap-8">
            <div class="glass-card rounded-3xl p-6 md:p-8">
                <div id="cart-items" class="space-y-4">
                    @foreach($cart->items as $item)
                    <div
                        class="rounded-2xl border border-white/10 bg-white/5 p-4 md:p-5 flex flex-col md:flex-row gap-4 md:items-center transition"
                        data-cart-item
                        data-item-id="{{ $item->id }}"
                        data-update-url="{{ route('cart.update', $item) }}"
                        data-delete-url="{{ route('cart.destroy', $item) }}"
                        data-current-quantity="{{ $item->quantity }}"
                        data-max-quantity="{{ optional($item->merchandise)->qty_stock ?? $item->quantity }}">
                        <img src="{{ $item->merchandise && $item->merchandise->image ? asset('storage/' . $item->merchandise->image) : asset('landing/images/merchan/merchan1.jpg') }}"
                            alt="{{ $item->merchandise->name ?? 'Merchandise' }}"
                            class="w-full md:w-28 h-28 object-cover rounded-2xl">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-white">{{ $item->merchandise->name ?? 'Merchandise tidak ditemukan' }}</h3>
                            <p class="text-sm text-gray-400 mt-1">
                                {{ $item->merchandise->category->name ?? '-' }} • {{ number_format(optional($item->merchandise)->weight ?? 0) }} gram
                            </p>
                            <p class="text-purple-300 font-bold mt-2">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</p>
                            <p class="text-sm text-gray-300 mt-2">
                                Total:
                                <span data-item-subtotal>Rp {{ number_format($item->subtotal(), 0, ',', '.') }}</span>
                            </p>
                        </div>
                        <div class="md:w-60">
                            <form action="{{ route('cart.update', $item) }}" method="POST" class="space-y-3" data-cart-update-form>
                                @csrf
                                @method('PUT')
                                <label class="text-xs uppercase tracking-[0.2em] text-gray-400 block">Jumlah</label>
                                <div class="flex items-center gap-2">
                                    <button type="button" data-qty-action="decrease"
                                        class="w-10 h-10 rounded-xl border border-purple-500/30 text-purple-200 hover:bg-purple-500/10 transition">-</button>
                                    <input type="number" name="quantity" min="1" max="{{ optional($item->merchandise)->qty_stock ?? $item->quantity }}"
                                        value="{{ $item->quantity }}"
                                        data-quantity-input
                                        onchange="if (!window.cartAutoUpdateEnabled) { this.form.submit(); }"
                                        class="w-24 rounded-xl bg-white/5 border border-purple-500/20 px-3 py-2 text-white text-center">
                                    <button type="button" data-qty-action="increase"
                                        class="w-10 h-10 rounded-xl border border-purple-500/30 text-purple-200 hover:bg-purple-500/10 transition">+</button>
                                </div>
                                <p class="text-xs text-gray-500">Stok tersedia: {{ optional($item->merchandise)->qty_stock ?? $item->quantity }}</p>
                            </form>
                            <form action="{{ route('cart.destroy', $item) }}" method="POST" class="mt-3" data-cart-delete-form>
                                @csrf
                                @method('DELETE')
                                <button type="submit" data-cart-delete-button
                                    class="w-full rounded-xl border border-red-400/40 px-3 py-2 text-sm text-red-200 hover:bg-red-500/10 transition">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="glass-card rounded-3xl p-6 md:p-8 h-fit">
                <h2 class="text-xl font-semibold text-white">Ringkasan</h2>
                <div class="mt-6 space-y-3 text-sm text-gray-300">
                    <div class="flex justify-between">
                        <span>Total Item</span>
                        <span id="cart-summary-quantity">{{ $cart->totalQuantity() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span id="cart-summary-subtotal">Rp {{ number_format($cart->subtotal(), 0, ',', '.') }}</span>
                    </div>
                </div>
                <a href="{{ route('checkout.show') }}"
                    class="mt-8 w-full h-11 rounded-xl bg-gradient-to-r from-purple-700 to-purple-500 text-white text-sm font-semibold flex items-center justify-center gap-2 hover:opacity-90 transition">
                    Checkout
                </a>
            </div>
        </div>
        @endif
    </section>
</main>

<script>
    window.cartAutoUpdateEnabled = true;

    (function() {
        const cartItems = document.getElementById('cart-items');
        const summaryQuantity = document.getElementById('cart-summary-quantity');
        const summarySubtotal = document.getElementById('cart-summary-subtotal');
        const feedback = document.getElementById('cart-feedback');
        const csrfToken = '{{ csrf_token() }}';
        const requestHeaders = {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
        };
        let feedbackTimer = null;

        if (!cartItems) {
            return;
        }

        function setFeedback(message, type) {
            if (!feedback) {
                return;
            }

            const classes = {
                success: 'border-green-500/30 bg-green-500/10 text-green-300',
                warning: 'border-yellow-500/30 bg-yellow-500/10 text-yellow-200',
            };

            feedback.className = 'rounded-2xl border px-5 py-4 mb-6 text-sm ' + (classes[type] || classes.success);
            feedback.textContent = message;
            feedback.classList.remove('hidden');

            if (feedbackTimer) {
                clearTimeout(feedbackTimer);
            }

            feedbackTimer = setTimeout(function() {
                feedback.classList.add('hidden');
            }, 2500);
        }

        function updateCartBadge(totalQuantity, displayValue) {
            ['cart-count', 'cart-count-mobile'].forEach(function(id) {
                const badge = document.getElementById(id);

                if (!badge) {
                    return;
                }

                badge.textContent = displayValue;
                badge.classList.toggle('hidden', totalQuantity < 1);
            });
        }

        function updateSummary(totalQuantity, subtotalFormatted) {
            if (summaryQuantity) {
                summaryQuantity.textContent = totalQuantity;
            }

            if (summarySubtotal) {
                summarySubtotal.textContent = 'Rp ' + subtotalFormatted;
            }
        }

        function setRowLoading(row, loading) {
            row.dataset.loading = loading ? '1' : '0';
            row.classList.toggle('opacity-70', loading);

            row.querySelectorAll('input, button').forEach(function(element) {
                element.disabled = loading;
            });
        }

        async function sendCartRequest(url, payload) {
            const response = await fetch(url, {
                method: 'POST',
                headers: requestHeaders,
                body: new URLSearchParams(payload),
            });
            const data = await response.json().catch(function() {
                return {};
            });

            if (!response.ok) {
                throw new Error(data.message || 'Perubahan keranjang gagal diproses.');
            }

            return data;
        }

        async function persistQuantity(row, quantity) {
            if (row.dataset.loading === '1') {
                return;
            }

            const input = row.querySelector('[data-quantity-input]');
            const maxQuantity = parseInt(row.dataset.maxQuantity, 10) || 1;
            const safeQuantity = Math.max(1, Math.min(maxQuantity, quantity || 1));

            if (String(safeQuantity) === row.dataset.currentQuantity) {
                input.value = safeQuantity;
                return;
            }

            setRowLoading(row, true);

            try {
                const data = await sendCartRequest(row.dataset.updateUrl, {
                    _method: 'PUT',
                    quantity: safeQuantity,
                });

                row.dataset.currentQuantity = String(data.item_quantity);
                input.value = data.item_quantity;

                const subtotal = row.querySelector('[data-item-subtotal]');

                if (subtotal) {
                    subtotal.textContent = 'Rp ' + data.item_subtotal_formatted;
                }

                updateSummary(data.cart_total_quantity, data.cart_subtotal_formatted);
                updateCartBadge(data.cart_total_quantity, data.cart_count_display);
                setFeedback(data.message, 'success');
            } catch (error) {
                input.value = row.dataset.currentQuantity;
                setFeedback(error.message, 'warning');
            } finally {
                setRowLoading(row, false);
            }
        }

        function scheduleQuantityUpdate(row, quantity) {
            const input = row.querySelector('[data-quantity-input]');
            const maxQuantity = parseInt(row.dataset.maxQuantity, 10) || 1;
            const safeQuantity = Math.max(1, Math.min(maxQuantity, quantity || 1));
            const existingTimer = row.dataset.timerId ? Number(row.dataset.timerId) : null;

            input.value = safeQuantity;

            if (existingTimer) {
                window.clearTimeout(existingTimer);
            }

            row.dataset.timerId = window.setTimeout(function() {
                persistQuantity(row, safeQuantity);
                delete row.dataset.timerId;
            }, 300);
        }

        cartItems.addEventListener('click', function(event) {
            const quantityButton = event.target.closest('[data-qty-action]');

            if (quantityButton) {
                const row = quantityButton.closest('[data-cart-item]');
                const input = row.querySelector('[data-quantity-input]');
                const currentQuantity = parseInt(input.value || row.dataset.currentQuantity, 10) || 1;
                const step = quantityButton.dataset.qtyAction === 'increase' ? 1 : -1;

                scheduleQuantityUpdate(row, currentQuantity + step);
                return;
            }

            const deleteButton = event.target.closest('[data-cart-delete-button]');

            if (!deleteButton) {
                return;
            }

            event.preventDefault();

            if (!window.confirm('Hapus item ini dari keranjang?')) {
                return;
            }

            const deleteForm = deleteButton.closest('[data-cart-delete-form]');
            const row = deleteButton.closest('[data-cart-item]');

            setRowLoading(row, true);

            sendCartRequest(deleteForm.action, {
                _method: 'DELETE',
            }).then(function(data) {
                row.remove();
                updateSummary(data.cart_total_quantity, data.cart_subtotal_formatted);
                updateCartBadge(data.cart_total_quantity, data.cart_count_display);
                setFeedback(data.message, 'success');

                if (data.empty || !cartItems.querySelector('[data-cart-item]')) {
                    window.location.reload();
                }
            }).catch(function(error) {
                setRowLoading(row, false);
                setFeedback(error.message, 'warning');
            });
        });

        cartItems.addEventListener('input', function(event) {
            const input = event.target.closest('[data-quantity-input]');

            if (!input) {
                return;
            }

            const row = input.closest('[data-cart-item]');
            const value = parseInt(input.value, 10);

            if (Number.isNaN(value)) {
                return;
            }

            scheduleQuantityUpdate(row, value);
        });

        cartItems.addEventListener('blur', function(event) {
            const input = event.target.closest('[data-quantity-input]');

            if (!input) {
                return;
            }

            const row = input.closest('[data-cart-item]');
            const value = parseInt(input.value, 10);

            persistQuantity(row, Number.isNaN(value) ? parseInt(row.dataset.currentQuantity, 10) : value);
        }, true);
    })();
</script>
@endsection
