/*
 * Storefront behaviour: add-to-cart with duplicate-click protection, live cart count,
 * and the confirmation drawer. Forms still submit normally if JavaScript is unavailable.
 */
(function () {
    'use strict';

    document.documentElement.classList.add('js');

    var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    var drawer = document.querySelector('[data-cart-drawer]');
    var lastFocus = null;

    function setState(button, state) {
        button.dataset.state = state;
    }

    function setStatus(form, text, isError) {
        var el = form.querySelector('.atc-status');
        if (!el) return;
        el.textContent = text || '';
        el.classList.toggle('is-error', !!isError);
    }

    function updateCount(count) {
        document.querySelectorAll('[data-cart-count]').forEach(function (el) {
            el.textContent = count;
        });
    }

    function newRequestId() {
        if (window.crypto && typeof window.crypto.randomUUID === 'function') {
            return window.crypto.randomUUID();
        }
        return String(Date.now()) + '-' + Math.random().toString(36).slice(2);
    }

    function openDrawer(item, subtotalText, quantity) {
        if (!drawer) return;
        lastFocus = document.activeElement;
        var img = drawer.querySelector('[data-drawer-image]');
        if (item.image) { img.src = item.image; img.alt = ''; img.hidden = false; } else { img.hidden = true; }
        drawer.querySelector('[data-drawer-name]').textContent = item.name;
        drawer.querySelector('[data-drawer-variant]').textContent = item.variant;
        drawer.querySelector('[data-drawer-qty]').textContent = quantity;
        drawer.querySelector('[data-drawer-subtotal]').textContent = subtotalText;
        drawer.hidden = false;
        drawer.querySelector('.cart-drawer__panel').focus();
    }

    function closeDrawer() {
        if (!drawer || drawer.hidden) return;
        drawer.hidden = true;
        if (lastFocus && typeof lastFocus.focus === 'function') lastFocus.focus();
    }

    document.addEventListener('submit', function (event) {
        var form = event.target.closest && event.target.closest('form[data-add-to-cart]');
        if (!form) return;
        event.preventDefault();

        var button = form.querySelector('.atc-button');
        // Ignore clicks while a request is in flight; this is the first layer of duplicate protection.
        if (!button || button.disabled || button.dataset.busy === '1') return;

        button.dataset.busy = '1';
        setState(button, 'loading');
        setStatus(form, '');

        var data = new FormData(form);
        data.set('request_id', newRequestId());
        var quantity = parseInt(data.get('quantity'), 10) || 1;

        fetch(form.action, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf },
            body: data
        })
            .then(function (response) {
                return response.json().catch(function () { return {}; }).then(function (json) {
                    return { ok: response.ok, json: json };
                });
            })
            .then(function (result) {
                if (!result.ok || !result.json.ok) {
                    throw new Error(result.json.message || form.dataset.errorMessage);
                }
                setState(button, 'success');
                updateCount(result.json.count);
                setStatus(form, result.json.message);
                openDrawer(result.json.item, result.json.subtotal_text, quantity);
                window.setTimeout(function () {
                    if (button.dataset.state === 'success') setState(button, 'idle');
                }, 2200);
            })
            .catch(function (error) {
                setState(button, 'idle');
                setStatus(form, error.message || form.dataset.errorMessage, true);
            })
            .finally(function () {
                button.dataset.busy = '0';
            });
    });

    /* ---------- Checkout: live total, payment fields, single submission ---------- */
    var checkout = document.querySelector('form[data-checkout]');
    if (checkout) {
        var feeEl = checkout.querySelector('[data-delivery-fee]');
        var totalEl = checkout.querySelector('[data-order-total]');

        var syncZone = function () {
            var zone = checkout.querySelector('input[name="delivery_zone"]:checked');
            if (!zone) return;
            if (feeEl) feeEl.textContent = zone.dataset.feeText;
            if (totalEl) totalEl.textContent = zone.dataset.totalText;
        };

        var syncMethod = function () {
            var picked = checkout.querySelector('input[name="payment_method"]:checked');
            var method = picked ? picked.value : '';
            checkout.querySelectorAll('[data-method-info]').forEach(function (el) {
                var show = el.dataset.methodInfo.split(' ').indexOf(method) !== -1;
                el.hidden = !show;
                // Hidden payment fields must not block submission with "required" or stale values.
                el.querySelectorAll('input').forEach(function (input) {
                    input.disabled = !show;
                    if (input.name === 'payer_number' || input.name === 'payment_reference') input.required = show;
                });
            });
        };

        checkout.addEventListener('change', function (event) {
            if (event.target.name === 'delivery_zone') syncZone();
            if (event.target.name === 'payment_method') syncMethod();
        });

        checkout.addEventListener('submit', function (event) {
            var button = checkout.querySelector('button[type="submit"]');
            if (checkout.dataset.submitting === '1') { event.preventDefault(); return; }
            checkout.dataset.submitting = '1';
            if (button) {
                if (!button.dataset.idleText) button.dataset.idleText = button.textContent;
                button.disabled = true;
                button.setAttribute('aria-busy', 'true');
                if (button.dataset.placingText) button.textContent = button.dataset.placingText;
            }
        });

        // If the browser restores this page from history, allow submitting again.
        window.addEventListener('pageshow', function () {
            checkout.dataset.submitting = '0';
            var button = checkout.querySelector('button[type="submit"]');
            if (button) {
                button.disabled = false;
                button.removeAttribute('aria-busy');
                if (button.dataset.idleText) button.textContent = button.dataset.idleText;
            }
        });

        syncZone();
        syncMethod();
    }

    if (drawer) {
        drawer.addEventListener('click', function (event) {
            if (event.target.closest('[data-drawer-close]')) closeDrawer();
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') closeDrawer();
        });
    }
})();
