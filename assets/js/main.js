/**
 * SportKick — Main Frontend Application Script (Vanilla ES6+)
 * Connects UI components with PHP/MySQL backend APIs (`api/*.php`).
 */

(function () {
    'use strict';

    /* ----------------------------------------------------------------------
       1. Toast Notification System
       ---------------------------------------------------------------------- */
    function showToast(message, isError = false) {
        const container = document.getElementById('toastContainer');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = 'toast-item';
        if (isError) {
            toast.style.borderLeftColor = '#ef4444';
        }
        toast.innerHTML = `
            <span>${escapeHtml(message)}</span>
            <button type="button" style="color:#a1a1aa;font-weight:800;font-size:1.1rem;" aria-label="Dismiss">&times;</button>
        `;
        toast.querySelector('button').addEventListener('click', () => toast.remove());
        container.appendChild(toast);

        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 3800);
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    function updateHeaderCounters(cartCount, wishlistCount) {
        if (typeof cartCount === 'number') {
            const cartBadge = document.getElementById('headerCartCount');
            if (cartBadge) cartBadge.textContent = String(cartCount);
        }
        if (typeof wishlistCount === 'number') {
            const wishBadge = document.getElementById('headerWishlistCount');
            if (wishBadge) wishBadge.textContent = String(wishlistCount);
        }
    }

    window.SportKick = {
        showToast,
        updateHeaderCounters
    };

    /* ----------------------------------------------------------------------
       2. Mobile Navigation Drawer
       ---------------------------------------------------------------------- */
    const mobileToggle = document.getElementById('mobileMenuToggle');
    const mobileDrawer = document.getElementById('mobileDrawer');
    if (mobileToggle && mobileDrawer) {
        mobileToggle.addEventListener('click', () => {
            const isOpen = mobileDrawer.classList.toggle('is-open');
            mobileToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }

    /* ----------------------------------------------------------------------
       3. Delegated Event Listeners (Size Chips, Add to Cart, Wishlist, Quick View)
       ---------------------------------------------------------------------- */
    document.addEventListener('click', async (e) => {
        // 3a. Product Card Size Pill selection
        const sizeChip = e.target.closest('.js-card-size');
        if (sizeChip) {
            e.preventDefault();
            const card = sizeChip.closest('.product-card');
            if (card) {
                card.querySelectorAll('.js-card-size').forEach((btn) => btn.classList.remove('is-selected'));
                sizeChip.classList.add('is-selected');
                card.setAttribute('data-default-size', sizeChip.getAttribute('data-size') || 'UK 9');
            }
            return;
        }

        // 3b. Add to Cart from Product Card
        const addCartBtn = e.target.closest('.js-add-to-cart');
        if (addCartBtn) {
            e.preventDefault();
            const productId = parseInt(addCartBtn.getAttribute('data-product-id') || '0', 10);
            const card = addCartBtn.closest('.product-card');
            const size = addCartBtn.getAttribute('data-size') || (card ? card.getAttribute('data-default-size') : 'UK 9');
            const color = addCartBtn.getAttribute('data-color') || (card ? card.getAttribute('data-default-color') : 'Black');
            const quantity = parseInt(addCartBtn.getAttribute('data-quantity') || '1', 10);

            if (!productId) return;
            const originalHtml = addCartBtn.innerHTML;
            addCartBtn.disabled = true;

            try {
                const res = await fetch('api/cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'add',
                        product_id: productId,
                        size,
                        color,
                        quantity
                    })
                });
                const data = await res.json();
                if (data.success) {
                    updateHeaderCounters(data.cart.item_count, undefined);
                    showToast(data.message || 'Added to your bag!');
                } else {
                    showToast(data.message || 'Could not add item.', true);
                }
            } catch (err) {
                showToast('Network error while adding to cart.', true);
            } finally {
                addCartBtn.disabled = false;
                addCartBtn.innerHTML = originalHtml;
            }
            return;
        }

        // 3c. Wishlist Toggle
        const wishBtn = e.target.closest('.js-wishlist-toggle');
        if (wishBtn) {
            e.preventDefault();
            const productId = parseInt(wishBtn.getAttribute('data-product-id') || '0', 10);
            if (!productId) return;

            try {
                const res = await fetch('api/wishlist.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'toggle',
                        product_id: productId
                    })
                });
                const data = await res.json();
                if (data.success) {
                    updateHeaderCounters(undefined, data.count);
                    const isAdded = data.status === 'added';
                    document.querySelectorAll(`.js-wishlist-toggle[data-product-id="${productId}"]`).forEach((btn) => {
                        btn.classList.toggle('is-active', isAdded);
                        const svg = btn.querySelector('svg');
                        if (svg) {
                            svg.setAttribute('fill', isAdded ? 'currentColor' : 'none');
                        }
                    });
                    showToast(data.message);

                    // If on wishlist.php and item was removed, remove card or reload if empty
                    const wishPageGrid = document.getElementById('wishlistGrid');
                    if (wishPageGrid && !isAdded) {
                        const card = wishBtn.closest('.product-card, .wishlist-card-wrap');
                        if (card) card.remove();
                        if (wishPageGrid.children.length === 0) {
                            window.location.reload();
                        }
                    }
                }
            } catch (err) {
                showToast('Could not update wishlist.', true);
            }
            return;
        }

        // 3d. Quick View Modal Trigger
        const qvBtn = e.target.closest('.js-quick-view');
        if (qvBtn) {
            e.preventDefault();
            const productId = parseInt(qvBtn.getAttribute('data-product-id') || '0', 10);
            if (productId) {
                openQuickViewModal(productId);
            }
            return;
        }
    });

    /* ----------------------------------------------------------------------
       4. Quick View Modal Logic
       ---------------------------------------------------------------------- */
    const qvModal = document.getElementById('quickViewModal');
    const qvBody = document.getElementById('quickViewBody');
    const qvClose = document.getElementById('quickViewClose');

    function closeQuickView() {
        if (!qvModal) return;
        qvModal.classList.remove('is-open');
        qvModal.setAttribute('aria-hidden', 'true');
    }

    if (qvClose) {
        qvClose.addEventListener('click', closeQuickView);
    }
    if (qvModal) {
        qvModal.addEventListener('click', (e) => {
            if (e.target === qvModal) closeQuickView();
        });
    }
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeQuickView();
    });

    async function openQuickViewModal(productId) {
        if (!qvModal || !qvBody) return;
        qvModal.classList.add('is-open');
        qvModal.setAttribute('aria-hidden', 'false');
        qvBody.innerHTML = '<div style="padding:48px;text-align:center;font-weight:700;">Loading technical specifications...</div>';

        try {
            const res = await fetch(`api/products.php?action=detail&id=${productId}`);
            const data = await res.json();
            if (!data.success || !data.product) {
                qvBody.innerHTML = '<p style="padding:24px;">Product could not be loaded.</p>';
                return;
            }
            const p = data.product;
            const sizes = p.sizes_array || ['UK 9'];
            const colors = p.colors_array || [p.color_primary || 'Black'];
            const specsEntries = Object.entries(p.specs_object || {}).slice(0, 4);

            qvBody.innerHTML = `
                <div class="qv-grid">
                    <div>
                        <div class="pd-gallery__main" style="margin-bottom:10px;">
                            <img src="${escapeHtml(p.image_main)}" id="qvMainImg" alt="${escapeHtml(p.name)}">
                        </div>
                        <div class="pd-gallery__thumbs">
                            ${(p.gallery_array || []).map((img, i) => `
                                <button type="button" class="pd-thumb ${i === 0 ? 'is-active' : ''}" data-qv-img="${escapeHtml(img)}">
                                    <img src="${escapeHtml(img)}" alt="View ${i + 1}">
                                </button>
                            `).join('')}
                        </div>
                    </div>
                    <div>
                        <div class="pd-info__top">
                            <span class="pd-brand-pill">${escapeHtml(p.brand)}</span>
                            <span class="badge badge--discount">-${p.discount_percent}% OFF</span>
                            <span class="pd-sku">SKU: ${escapeHtml(p.sku)}</span>
                        </div>
                        <h2 class="pd-title" id="qvTitle" style="font-size:2rem;">${escapeHtml(p.name)}</h2>
                        <div class="pd-rating-row">
                            <span class="star-icon">★</span>
                            <span>${Number(p.rating).toFixed(1)} (${p.reviews_count} verified athlete reviews)</span>
                        </div>
                        <div class="pd-price-box" style="padding:12px 16px;margin-bottom:16px;">
                            <span class="pd-price-current" style="font-size:1.85rem;">${escapeHtml(p.price_formatted)}</span>
                            <span class="pd-price-original">${escapeHtml(p.original_price_formatted)}</span>
                        </div>
                        <p class="pd-description" style="margin-bottom:16px;">${escapeHtml(p.description)}</p>

                        <div class="pd-selector-group">
                            <div class="pd-selector-label"><span>Select Size</span></div>
                            <div class="pd-options-row" id="qvSizeRow">
                                ${sizes.map((s, idx) => `
                                    <button type="button" class="pd-option-btn ${idx === 0 ? 'is-selected' : ''}" data-val="${escapeHtml(s)}">${escapeHtml(s)}</button>
                                `).join('')}
                            </div>
                        </div>

                        <div class="pd-selector-group">
                            <div class="pd-selector-label"><span>Select Colorway</span></div>
                            <div class="pd-options-row" id="qvColorRow">
                                ${colors.map((c, idx) => `
                                    <button type="button" class="pd-option-btn ${idx === 0 ? 'is-selected' : ''}" data-val="${escapeHtml(c)}">${escapeHtml(c)}</button>
                                `).join('')}
                            </div>
                        </div>

                        ${specsEntries.length ? `
                            <table class="specs-table" style="margin-bottom:18px;">
                                ${specsEntries.map(([k, v]) => `<tr><th>${escapeHtml(k)}</th><td>${escapeHtml(String(v))}</td></tr>`).join('')}
                            </table>
                        ` : ''}

                        <div style="display:flex;gap:10px;flex-wrap:wrap;">
                            <button type="button" class="btn btn--primary" id="qvAddCartBtn" style="flex:1;">Add to Bag</button>
                            <a href="product.php?id=${p.id}" class="btn btn--outline-dark">Full Details &rarr;</a>
                        </div>
                    </div>
                </div>
            `;

            // Bind thumbnail switcher inside modal
            qvBody.querySelectorAll('[data-qv-img]').forEach((thumb) => {
                thumb.addEventListener('click', () => {
                    qvBody.querySelectorAll('[data-qv-img]').forEach((t) => t.classList.remove('is-active'));
                    thumb.classList.add('is-active');
                    const mainImg = document.getElementById('qvMainImg');
                    if (mainImg) mainImg.src = thumb.getAttribute('data-qv-img');
                });
            });

            // Bind size/color option buttons inside modal
            ['qvSizeRow', 'qvColorRow'].forEach((rowId) => {
                const row = document.getElementById(rowId);
                if (!row) return;
                row.querySelectorAll('.pd-option-btn').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        row.querySelectorAll('.pd-option-btn').forEach((b) => b.classList.remove('is-selected'));
                        btn.classList.add('is-selected');
                    });
                });
            });

            // Bind Add to Bag button inside modal
            const qvAddBtn = document.getElementById('qvAddCartBtn');
            if (qvAddBtn) {
                qvAddBtn.addEventListener('click', async () => {
                    const selectedSize = qvBody.querySelector('#qvSizeRow .is-selected')?.getAttribute('data-val') || sizes[0];
                    const selectedColor = qvBody.querySelector('#qvColorRow .is-selected')?.getAttribute('data-val') || colors[0];
                    qvAddBtn.disabled = true;
                    const resCart = await fetch('api/cart.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'add',
                            product_id: p.id,
                            size: selectedSize,
                            color: selectedColor,
                            quantity: 1
                        })
                    });
                    const cartData = await resCart.json();
                    qvAddBtn.disabled = false;
                    if (cartData.success) {
                        updateHeaderCounters(cartData.cart.item_count, undefined);
                        showToast(cartData.message);
                        closeQuickView();
                    } else {
                        showToast(cartData.message || 'Error adding to cart', true);
                    }
                });
            }
        } catch (err) {
            qvBody.innerHTML = '<p style="padding:24px;">Error loading product details.</p>';
        }
    }

    /* ----------------------------------------------------------------------
       5. Newsletter Subscription Handler
       ---------------------------------------------------------------------- */
    const newsletterForm = document.getElementById('newsletterForm');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const input = newsletterForm.querySelector('input[type="email"]');
            if (input && input.value.trim()) {
                showToast('Welcome to The Locker Club! Use code KICK10 for 10% off.');
                input.value = '';
            }
        });
    }
})();
