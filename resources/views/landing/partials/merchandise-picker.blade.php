{{--
    Komponen "pilih varian + qty + masukkan keranjang" (gaya Shopee).
    - Popup / bottom sheet : tombol dengan atribut data-merch-open + data-product='{json}'
    - Inline (halaman detail): elemen dengan atribut data-merch-inline + data-product='{json}'
    Cukup di-include berulang kali; script hanya dirender sekali per request.
--}}
@php
    $merchPickerLoaded = app()->bound('merch.picker.loaded');

    if (!$merchPickerLoaded) {
        app()->instance('merch.picker.loaded', true);
    }
@endphp
@unless($merchPickerLoaded)
<script>
(function () {
    if (window.MerchPicker) { return; }

    var cfg = {
        csrf: @json(csrf_token()),
        cartUrl: @json(route('cart.index'))
    };

    var reduceMotion = !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);

    function isDesktop() {
        return window.matchMedia('(min-width: 768px)').matches;
    }

    function el(tag, cls, text) {
        var node = document.createElement(tag);
        if (cls) { node.className = cls; }
        if (text !== undefined && text !== null) { node.textContent = text; }
        return node;
    }

    function icon(cls) {
        var i = document.createElement('i');
        i.className = cls;
        return i;
    }

    function firstError(data) {
        if (data && data.errors) {
            var keys = Object.keys(data.errors);
            if (keys.length && data.errors[keys[0]].length) { return data.errors[keys[0]][0]; }
        }
        return data && data.message ? data.message : '';
    }

    /* ------------------------------------------------------------ Toast */
    var toastEl = null;
    var toastTimer = null;

    function hideToast() {
        if (toastEl) { toastEl.remove(); toastEl = null; }
        clearTimeout(toastTimer);
    }

    function toast(message, type, withCartLink) {
        hideToast();

        var isError = type === 'error';
        toastEl = el('div',
            'fixed z-[10001] left-4 right-4 md:left-auto md:right-6 md:w-96 rounded-2xl border px-4 py-3 shadow-2xl ' +
            'flex items-center gap-3 text-sm backdrop-blur-xl ' +
            (isError ? 'border-red-500/40 bg-red-950/90 text-red-100' : 'border-green-500/40 bg-[#0f1f1a]/95 text-green-100'));
        toastEl.setAttribute('role', isError ? 'alert' : 'status');

        if (isDesktop()) {
            toastEl.style.top = '96px';
        } else {
            toastEl.style.bottom = 'calc(env(safe-area-inset-bottom, 0px) + 88px)';
        }

        toastEl.appendChild(icon(isError ? 'fas fa-exclamation-circle text-red-300' : 'fas fa-check-circle text-green-400'));
        toastEl.appendChild(el('span', 'flex-1', message));

        if (withCartLink) {
            var link = el('a', 'font-semibold text-white underline underline-offset-2 whitespace-nowrap', 'Lihat Keranjang');
            link.href = cfg.cartUrl;
            toastEl.appendChild(link);
        }

        document.body.appendChild(toastEl);

        if (!reduceMotion && toastEl.animate) {
            toastEl.animate(
                [{ opacity: 0, transform: 'translateY(12px)' }, { opacity: 1, transform: 'translateY(0)' }],
                { duration: 200, easing: 'ease-out' }
            );
        }

        toastTimer = setTimeout(hideToast, isError ? 4500 : 4000);
    }

    /* ------------------------------------------------- Badge + animasi */
    function updateBadge(total, display) {
        ['cart-count', 'cart-count-mobile'].forEach(function (id) {
            var badge = document.getElementById(id);
            if (!badge) { return; }

            badge.textContent = display;
            badge.classList.toggle('hidden', total < 1);

            if (!reduceMotion && total > 0 && badge.animate) {
                badge.animate(
                    [{ transform: 'scale(1)' }, { transform: 'scale(1.6)' }, { transform: 'scale(1)' }],
                    { duration: 380, easing: 'ease-out' }
                );
            }
        });
    }

    function cartIconTarget() {
        var ids = ['cart-count', 'cart-count-mobile'];
        for (var i = 0; i < ids.length; i++) {
            var badge = document.getElementById(ids[i]);
            var anchor = badge ? badge.parentElement : null;
            if (anchor && anchor.getClientRects().length > 0) { return anchor; }
        }
        return null;
    }

    // Gambar produk "terbang" ke ikon keranjang, lalu jalankan done()
    function fly(src, rect, done) {
        var target = cartIconTarget();

        if (reduceMotion || !src || !rect || !rect.width || !target || !document.body.animate) {
            done();
            return;
        }

        var t = target.getBoundingClientRect();

        if (t.bottom < 0 || t.top > window.innerHeight) {
            done();
            return;
        }

        var clone = document.createElement('img');
        clone.src = src;
        clone.alt = '';
        clone.style.cssText =
            'position:fixed;z-index:10002;object-fit:cover;border-radius:14px;pointer-events:none;' +
            'box-shadow:0 8px 24px rgba(0,0,0,.5);' +
            'left:' + rect.left + 'px;top:' + rect.top + 'px;width:' + rect.width + 'px;height:' + rect.height + 'px;';
        document.body.appendChild(clone);

        var dx = (t.left + t.width / 2) - (rect.left + rect.width / 2);
        var dy = (t.top + t.height / 2) - (rect.top + rect.height / 2);

        var animation = clone.animate([
            { transform: 'translate(0,0) scale(1)', opacity: 1 },
            { transform: 'translate(' + (dx * 0.55) + 'px,' + (dy * 0.55 - 70) + 'px) scale(.55)', opacity: 0.95, offset: 0.5 },
            { transform: 'translate(' + dx + 'px,' + dy + 'px) scale(.08)', opacity: 0.35 }
        ], { duration: 700, easing: 'cubic-bezier(.4,0,.2,1)' });

        animation.onfinish = function () { clone.remove(); done(); };
        animation.oncancel = function () { clone.remove(); done(); };
    }

    function afterAdded(data, thumb, closeFn) {
        var rect = thumb ? thumb.getBoundingClientRect() : null;
        var src = thumb ? (thumb.currentSrc || thumb.src) : null;

        if (closeFn) { closeFn(); }

        fly(src, rect, function () {
            updateBadge(data.cart_total_quantity, data.cart_count_display);
        });

        toast('Berhasil ditambahkan ke keranjang', 'success', true);
    }

    /* ------------------------------------------------------ Picker UI */
    function mount(root, product, opts) {
        opts = opts || {};

        var state = { variantId: null, qty: 1, busy: false };
        var chips = [];
        var variantsById = {};

        product.variants.forEach(function (v) { variantsById[v.id] = v; });

        root.textContent = '';

        var thumb = null;
        var stockLine = el('p', 'text-xs text-gray-400');

        /* Header (khusus popup) */
        if (opts.header) {
            var head = el('div', 'flex gap-4 pr-10');
            thumb = el('img', 'w-24 h-24 rounded-2xl object-cover bg-black/30 border border-white/10 flex-shrink-0');
            thumb.src = product.image;
            thumb.alt = product.name;

            var info = el('div', 'min-w-0 flex flex-col justify-center gap-1');
            info.appendChild(el('h3', 'text-white font-semibold leading-snug line-clamp-2', product.name));
            info.appendChild(el('p', 'text-purple-300 text-xl font-bold', product.price_formatted));
            info.appendChild(stockLine);

            head.appendChild(thumb);
            head.appendChild(info);
            root.appendChild(head);
        } else {
            thumb = opts.flySource || null;
        }

        /* Varian */
        var chipsWrap = null;

        if (product.has_variants) {
            var variantBlock = el('div', opts.header ? 'mt-5' : '');
            variantBlock.appendChild(el('p', 'text-xs uppercase tracking-[0.2em] text-gray-400 mb-3', 'Pilih Varian'));

            chipsWrap = el('div', 'flex flex-wrap gap-2');
            chipsWrap.setAttribute('role', 'group');
            chipsWrap.setAttribute('aria-label', 'Pilih varian');

            product.variants.forEach(function (v) {
                var chip = el('button', '', v.name);
                chip.type = 'button';
                chip.setAttribute('aria-pressed', 'false');

                if (v.stock < 1) {
                    chip.disabled = true;
                    chip.title = 'Stok habis';
                }

                chip.addEventListener('click', function () { selectVariant(v.id); });
                chips.push({ node: chip, variant: v });
                chipsWrap.appendChild(chip);
            });

            variantBlock.appendChild(chipsWrap);
            root.appendChild(variantBlock);
        }

        /* Jumlah */
        var qtyBlock = el('div', 'mt-5');
        qtyBlock.appendChild(el('p', 'text-xs uppercase tracking-[0.2em] text-gray-400 mb-3', 'Jumlah'));

        var stepper = el('div', 'flex items-center gap-2');
        var minus = el('button', 'w-11 h-11 rounded-xl border border-purple-500/30 text-purple-200 text-lg flex items-center justify-center transition hover:bg-purple-500/10', '\u2212');
        minus.type = 'button';
        minus.setAttribute('aria-label', 'Kurangi jumlah');

        var qtyInput = el('input', 'w-16 h-11 rounded-xl bg-white/5 border border-purple-500/20 text-white text-center text-base');
        qtyInput.type = 'text';
        qtyInput.inputMode = 'numeric';
        qtyInput.setAttribute('aria-label', 'Jumlah');
        qtyInput.value = '1';

        var plus = el('button', 'w-11 h-11 rounded-xl border border-purple-500/30 text-purple-200 text-lg flex items-center justify-center transition hover:bg-purple-500/10', '+');
        plus.type = 'button';
        plus.setAttribute('aria-label', 'Tambah jumlah');

        stepper.appendChild(minus);
        stepper.appendChild(qtyInput);
        stepper.appendChild(plus);
        qtyBlock.appendChild(stepper);

        if (!opts.header) { qtyBlock.appendChild(stockLine); stockLine.className = 'text-xs text-gray-400 mt-3'; }

        root.appendChild(qtyBlock);

        /* Pesan error + tombol */
        var errorLine = el('p', 'mt-4 min-h-[1.25rem] text-sm text-red-300');
        errorLine.setAttribute('aria-live', 'polite');
        root.appendChild(errorLine);

        var submitBtn = el('button', 'mt-2 w-full h-12 rounded-xl text-white text-sm font-semibold flex items-center justify-center gap-2 transition');
        submitBtn.type = 'button';
        root.appendChild(submitBtn);

        function currentVariant() {
            return state.variantId ? variantsById[state.variantId] : null;
        }

        function maxQty() {
            if (product.has_variants) {
                var v = currentVariant();
                if (v) { return v.stock; }

                var best = 0;
                product.variants.forEach(function (item) { if (item.stock > best) { best = item.stock; } });
                return best;
            }
            return product.stock;
        }

        function setError(message) {
            errorLine.textContent = message || '';
        }

        function renderChips() {
            chips.forEach(function (c) {
                var selected = state.variantId === c.variant.id;
                var base = 'min-w-[3rem] h-11 px-4 rounded-xl border text-sm font-semibold transition select-none ';

                if (c.variant.stock < 1) {
                    c.node.className = base + 'border-white/10 bg-white/5 text-gray-600 line-through cursor-not-allowed';
                } else if (selected) {
                    c.node.className = base + 'border-purple-400 bg-purple-600 text-white shadow-[0_0_14px_rgba(139,92,246,.45)]';
                } else {
                    c.node.className = base + 'border-purple-500/30 bg-white/5 text-purple-100 hover:border-purple-400';
                }

                c.node.setAttribute('aria-pressed', selected ? 'true' : 'false');
            });
        }

        function renderStock() {
            var v = currentVariant();
            var stock = product.has_variants ? (v ? v.stock : product.stock) : product.stock;
            var label = product.has_variants && !v ? 'Total stok: ' : 'Stok: ';

            stockLine.textContent = label + stock;

            if (stock > 0 && stock <= 5 && (!product.has_variants || v)) {
                stockLine.textContent += ' \u2022 Tinggal ' + stock + '!';
                stockLine.classList.add('text-amber-300');
            } else {
                stockLine.classList.remove('text-amber-300');
            }
        }

        function renderQty() {
            var max = maxQty();
            qtyInput.value = String(state.qty);

            var minusOff = state.qty <= 1;
            var plusOff = state.qty >= max;

            minus.disabled = minusOff;
            plus.disabled = plusOff;
            minus.classList.toggle('opacity-40', minusOff);
            plus.classList.toggle('opacity-40', plusOff);
        }

        function renderSubmit() {
            submitBtn.textContent = '';

            if (state.busy) {
                submitBtn.className = 'mt-2 w-full h-12 rounded-xl text-white text-sm font-semibold flex items-center justify-center gap-2 transition bg-purple-700/70 cursor-wait';
                submitBtn.appendChild(icon('fas fa-spinner fa-spin'));
                submitBtn.appendChild(document.createTextNode('Menambahkan...'));
                submitBtn.disabled = true;
                return;
            }

            if (product.stock < 1) {
                submitBtn.className = 'mt-2 w-full h-12 rounded-xl text-gray-400 text-sm font-semibold flex items-center justify-center gap-2 bg-white/5 border border-white/10 cursor-not-allowed';
                submitBtn.appendChild(document.createTextNode('Stok Habis'));
                submitBtn.disabled = true;
                return;
            }

            submitBtn.className = 'mt-2 w-full h-12 rounded-xl text-white text-sm font-semibold flex items-center justify-center gap-2 transition bg-gradient-to-r from-purple-700 to-purple-500 hover:opacity-90 active:scale-[0.98]';
            submitBtn.appendChild(icon('fas fa-cart-plus'));
            submitBtn.appendChild(document.createTextNode('Masukkan Keranjang'));
            submitBtn.disabled = false;
        }

        function clampQty() {
            var max = maxQty();
            if (state.qty > max) { state.qty = Math.max(1, max); }
            if (state.qty < 1) { state.qty = 1; }
        }

        function selectVariant(id) {
            var v = variantsById[id];
            if (!v || v.stock < 1) { return; }

            state.variantId = (state.variantId === id) ? null : id;
            clampQty();
            setError('');
            renderChips();
            renderStock();
            renderQty();
        }

        function changeQty(next) {
            var max = maxQty();

            if (next > max) {
                state.qty = Math.max(1, max);
                setError('Stok hanya tersisa ' + max + '.');
            } else {
                state.qty = Math.max(1, next);
                setError('');
            }

            renderQty();
        }

        minus.addEventListener('click', function () { changeQty(state.qty - 1); });
        plus.addEventListener('click', function () { changeQty(state.qty + 1); });

        qtyInput.addEventListener('input', function () {
            var digits = qtyInput.value.replace(/[^0-9]/g, '');
            if (digits === '') { return; }
            changeQty(parseInt(digits, 10));
        });
        qtyInput.addEventListener('blur', function () { renderQty(); });
        qtyInput.addEventListener('focus', function () { qtyInput.select(); });

        function setBusy(flag) {
            state.busy = flag;
            renderSubmit();
        }

        function submit() {
            if (state.busy) { return; }

            if (product.has_variants && !state.variantId) {
                setError('Pilih varian terlebih dahulu.');

                if (chipsWrap && !reduceMotion && chipsWrap.animate) {
                    chipsWrap.animate(
                        [{ transform: 'translateX(0)' }, { transform: 'translateX(-6px)' }, { transform: 'translateX(6px)' }, { transform: 'translateX(0)' }],
                        { duration: 300 }
                    );
                }
                return;
            }

            setBusy(true);
            setError('');

            var payload = { quantity: state.qty };
            if (state.variantId) { payload.merchandise_variant_id = state.variantId; }

            fetch(product.add_url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': cfg.csrf,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            }).then(function (res) {
                return res.json().catch(function () { return {}; }).then(function (data) {
                    return { res: res, data: data };
                });
            }).then(function (result) {
                if (result.res.status === 419) {
                    throw new Error('Sesi berakhir. Muat ulang halaman lalu coba lagi.');
                }

                if (result.res.status === 429) {
                    throw new Error('Terlalu banyak permintaan. Tunggu sebentar lalu coba lagi.');
                }

                if (!result.res.ok) {
                    throw new Error(firstError(result.data) || 'Gagal menambahkan ke keranjang.');
                }

                setBusy(false);
                state.qty = 1;
                renderQty();

                if (opts.onAdded) { opts.onAdded(result.data, thumb); }
            }).catch(function (error) {
                setBusy(false);
                setError(error && error.message ? error.message : 'Terjadi kesalahan jaringan. Coba lagi.');
            });
        }

        submitBtn.addEventListener('click', submit);

        renderChips();
        renderStock();
        renderQty();
        renderSubmit();

        return { root: root };
    }

    /* ------------------------------------------- Popup / bottom sheet */
    var modal = null;

    function focusables(container) {
        return Array.prototype.slice.call(
            container.querySelectorAll('button:not([disabled]), a[href], input:not([disabled])')
        ).filter(function (node) { return node.getClientRects().length > 0; });
    }

    function onKeydown(event) {
        if (!modal) { return; }

        if (event.key === 'Escape') {
            event.preventDefault();
            closeModal();
            return;
        }

        if (event.key === 'Tab') {
            var list = focusables(modal.panel);
            if (!list.length) { return; }

            var first = list[0];
            var last = list[list.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    }

    function closeModal(immediate) {
        if (!modal) { return; }

        var current = modal;
        modal = null;

        document.removeEventListener('keydown', onKeydown);
        document.body.style.overflow = current.prevOverflow;

        if (immediate || reduceMotion) {
            current.overlay.remove();
        } else {
            current.overlay.style.opacity = '0';
            current.panel.style.transform = isDesktop() ? 'scale(.96)' : 'translateY(100%)';
            setTimeout(function () { current.overlay.remove(); }, 230);
        }

        if (current.trigger && current.trigger.focus) { current.trigger.focus(); }
    }

    function openModal(product, trigger) {
        closeModal(true);
        hideToast();

        var overlay = el('div', 'fixed inset-0 z-[10000] flex items-end md:items-center justify-center bg-black/70 backdrop-blur-sm');
        overlay.style.opacity = '0';
        overlay.style.transition = 'opacity .2s ease';

        var panel = el('div', 'relative w-full md:max-w-md overflow-y-auto bg-[#14102a] border border-purple-500/30 rounded-t-3xl md:rounded-3xl shadow-2xl');
        panel.setAttribute('role', 'dialog');
        panel.setAttribute('aria-modal', 'true');
        panel.setAttribute('aria-label', 'Pilih ' + product.name);
        panel.style.maxHeight = '90vh';
        panel.style.maxHeight = '90dvh';
        panel.style.padding = '1.25rem';
        panel.style.paddingBottom = 'calc(1.25rem + env(safe-area-inset-bottom, 0px))';
        panel.style.transition = 'transform .25s cubic-bezier(.2,.8,.2,1), opacity .2s ease';
        panel.style.transform = isDesktop() ? 'scale(.96)' : 'translateY(100%)';
        panel.style.opacity = isDesktop() ? '0' : '1';

        panel.appendChild(el('div', 'md:hidden mx-auto mb-3 h-1 w-10 rounded-full bg-white/20'));

        var closeBtn = el('button', 'absolute top-3 right-3 w-10 h-10 rounded-full text-gray-400 hover:text-white hover:bg-white/10 flex items-center justify-center z-10');
        closeBtn.type = 'button';
        closeBtn.setAttribute('aria-label', 'Tutup');
        closeBtn.appendChild(icon('fas fa-times'));
        closeBtn.addEventListener('click', function () { closeModal(); });
        panel.appendChild(closeBtn);

        var body = el('div');
        panel.appendChild(body);
        overlay.appendChild(panel);

        overlay.addEventListener('mousedown', function (event) {
            if (event.target === overlay) { closeModal(); }
        });

        modal = { overlay: overlay, panel: panel, trigger: trigger || null, prevOverflow: document.body.style.overflow };
        document.body.style.overflow = 'hidden';
        document.body.appendChild(overlay);
        document.addEventListener('keydown', onKeydown);

        mount(body, product, {
            header: true,
            onAdded: function (data, thumb) {
                afterAdded(data, thumb, function () { closeModal(); });
            }
        });

        requestAnimationFrame(function () {
            overlay.style.opacity = '1';
            panel.style.transform = 'none';
            panel.style.opacity = '1';
        });

        closeBtn.focus();
    }

    /* ---------------------------------------------------------- Bind */
    document.addEventListener('click', function (event) {
        var trigger = event.target.closest ? event.target.closest('[data-merch-open]') : null;
        if (!trigger) { return; }

        event.preventDefault();

        try {
            openModal(JSON.parse(trigger.getAttribute('data-product')), trigger);
        } catch (error) {
            var fallback = trigger.getAttribute('data-detail-url');
            if (fallback) { window.location.href = fallback; }
        }
    });

    function mountInline() {
        document.querySelectorAll('[data-merch-inline]').forEach(function (node) {
            try {
                mount(node, JSON.parse(node.getAttribute('data-product')), {
                    header: false,
                    flySource: document.getElementById('merch-main-image'),
                    onAdded: function (data, thumb) { afterAdded(data, thumb, null); }
                });
            } catch (error) { /* abaikan data rusak */ }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mountInline);
    } else {
        mountInline();
    }

    window.MerchPicker = { open: openModal, mount: mount };
})();
</script>
@endunless