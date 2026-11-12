<?php
/**
 * SportKick - Shopping Cart Page (cart.php)
 * Features: Add/remove products, update quantities, promo code, subtotal, total price, proceed to checkout.
 */

$pageTitle = 'Shopping Bag';
require_once __DIR__ . '/includes/header.php';

$cart = getCartDetails($pdo);
$cartItems = $cart['items'];
?>

<section class="page-hero-compact">
    <div class="container">
        <span class="section-eyebrow">Your Locker Bag</span>
        <h1 class="page-hero-compact__title">Shopping Cart (<?= (int)$cart['item_count'] ?>)</h1>
        <p class="page-hero-compact__sub">Free express shipping on all orders over $120.00.</p>
    </div>
</section>

<div class="container">
    <?php if (empty($cartItems)): ?>
        <div class="empty-state" style="margin:48px 0 80px;">
            <h2 class="empty-state__title">Your Shopping Bag Is Empty</h2>
            <p class="empty-state__text">
                You haven't added any sports shoes or performance gear yet. Explore our 8 sport categories to gear up.
            </p>
            <a href="shop.php" class="btn btn--primary btn--lg">Explore All Gear &rarr;</a>
        </div>
    <?php else: ?>
        <div class="cart-layout">
            <!-- Left: Cart Items List -->
            <div class="cart-table-card">
                <div style="display:flex;justify-content:space-between;align-items:center;padding-bottom:16px;border-bottom:1px solid var(--border-light);margin-bottom:8px;">
                    <h2 style="font-family:var(--font-display);font-size:1.45rem;font-weight:900;text-transform:uppercase;">
                        Bag Items (<?= count($cartItems) ?>)
                    </h2>
                    <button type="button" id="clearCartBtn" class="cart-remove-btn">Clear Entire Bag</button>
                </div>

                <div id="cartItemsList">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item-row" data-cart-item-id="<?= (int)$item['cart_item_id'] ?>">
                            <a href="product.php?id=<?= (int)$item['product_id'] ?>">
                                <img
                                    src="<?= e($item['image_main']) ?>"
                                    alt="<?= e($item['name']) ?>"
                                    class="cart-item__img"
                                    onerror="this.onerror=null;this.src='assets/images/placeholder-shoe.svg';"
                                >
                            </a>

                            <div>
                                <span style="font-size:0.74rem;font-weight:800;color:var(--accent);text-transform:uppercase;">
                                    <?= e($item['brand']) ?> &bull; <?= e($item['category_name']) ?>
                                </span>
                                <h3 class="cart-item__title">
                                    <a href="product.php?id=<?= (int)$item['product_id'] ?>"><?= e($item['name']) ?></a>
                                </h3>
                                <div class="cart-item__meta">
                                    Size: <strong><?= e($item['size']) ?></strong> &nbsp;|&nbsp;
                                    Color: <strong><?= e($item['color']) ?></strong> &nbsp;|&nbsp;
                                    Unit Price: <strong><?= formatPrice((float)$item['price']) ?></strong>
                                </div>

                                <div class="cart-item__controls">
                                    <div class="qty-stepper" style="height:36px;">
                                        <button
                                            type="button"
                                            class="js-cart-qty"
                                            data-item-id="<?= (int)$item['cart_item_id'] ?>"
                                            data-next-qty="<?= (int)$item['quantity'] - 1 ?>"
                                            style="width:32px;height:34px;"
                                            aria-label="Decrease quantity"
                                        >&minus;</button>
                                        <input
                                            type="number"
                                            value="<?= (int)$item['quantity'] ?>"
                                            readonly
                                            style="width:36px;font-size:0.88rem;"
                                            aria-label="Current quantity"
                                        >
                                        <button
                                            type="button"
                                            class="js-cart-qty"
                                            data-item-id="<?= (int)$item['cart_item_id'] ?>"
                                            data-next-qty="<?= (int)$item['quantity'] + 1 ?>"
                                            style="width:32px;height:34px;"
                                            aria-label="Increase quantity"
                                        >&plus;</button>
                                    </div>

                                    <button
                                        type="button"
                                        class="cart-remove-btn js-cart-remove"
                                        data-item-id="<?= (int)$item['cart_item_id'] ?>"
                                    >Remove</button>
                                </div>
                            </div>

                            <div class="cart-item__price-col" style="text-align:right;">
                                <div class="price-current"><?= formatPrice((float)$item['line_total']) ?></div>
                                <?php if ((float)$item['line_original'] > (float)$item['line_total']): ?>
                                    <div class="price-original"><?= formatPrice((float)$item['line_original']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Right: Order Summary & Promo Code -->
            <aside class="summary-card" aria-label="Order Summary">
                <h2 class="summary-card__title">Order Summary</h2>

                <div class="summary-row">
                    <span>Items Subtotal (<?= (int)$cart['item_count'] ?>)</span>
                    <strong style="color:var(--bg-dark);"><?= formatPrice((float)$cart['subtotal']) ?></strong>
                </div>

                <?php if ((float)$cart['product_savings'] > 0): ?>
                    <div class="summary-row" style="color:var(--success);font-weight:700;">
                        <span>Product Markdown Savings</span>
                        <span>-<?= formatPrice((float)$cart['product_savings']) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ((float)$cart['promo_discount'] > 0): ?>
                    <div class="summary-row" style="color:var(--accent);font-weight:700;">
                        <span>Promo Code (<?= e($cart['promo_code']) ?>)</span>
                        <span>-<?= formatPrice((float)$cart['promo_discount']) ?></span>
                    </div>
                <?php endif; ?>

                <div class="summary-row">
                    <span>Estimated Express Shipping</span>
                    <strong style="color:var(--bg-dark);">
                        <?= (float)$cart['shipping_fee'] === 0.0 ? 'FREE' : formatPrice((float)$cart['shipping_fee']) ?>
                    </strong>
                </div>

                <!-- Promo Code Input -->
                <form id="promoForm" class="promo-box">
                    <input
                        type="text"
                        id="promoCodeInput"
                        value="<?= e($cart['promo_code']) ?>"
                        placeholder="Promo code (e.g. KICK10)"
                        aria-label="Promo code"
                    >
                    <button type="submit" class="btn btn--dark btn--sm">Apply</button>
                </form>

                <div class="summary-row summary-row--total">
                    <span>Total</span>
                    <span><?= formatPrice((float)$cart['total_amount']) ?></span>
                </div>

                <a href="checkout.php" class="btn btn--primary btn--lg btn--full" style="margin-top:20px;">
                    Proceed To Checkout &rarr;
                </a>

                <a href="shop.php" class="btn btn--outline-dark btn--sm btn--full" style="margin-top:10px;">
                    Continue Shopping
                </a>
            </aside>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    async function postCartAction(payload) {
        const res = await fetch('api/cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            window.location.reload();
        } else if (window.SportKick) {
            window.SportKick.showToast(data.message || 'Could not update cart.', true);
        }
    }

    document.querySelectorAll('.js-cart-qty').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const itemId = parseInt(btn.getAttribute('data-item-id') || '0', 10);
            const nextQty = parseInt(btn.getAttribute('data-next-qty') || '0', 10);
            postCartAction({ action: 'update', cart_item_id: itemId, quantity: nextQty });
        });
    });

    document.querySelectorAll('.js-cart-remove').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const itemId = parseInt(btn.getAttribute('data-item-id') || '0', 10);
            postCartAction({ action: 'remove', cart_item_id: itemId });
        });
    });

    const clearBtn = document.getElementById('clearCartBtn');
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            postCartAction({ action: 'clear' });
        });
    }

    const promoForm = document.getElementById('promoForm');
    if (promoForm) {
        promoForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const code = document.getElementById('promoCodeInput').value.trim();
            postCartAction({ action: 'promo', promo_code: code });
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
