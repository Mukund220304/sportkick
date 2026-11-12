<?php
/**
 * SportKick - Checkout & Order Confirmation Page (checkout.php)
 * Includes: Customer info, delivery address, payment method selection, order summary, and order confirmation receipt.
 */

require_once __DIR__ . '/includes/functions.php';

$confirmedOrderNumber = trim((string)($_GET['order'] ?? ''));
$confirmedOrder = null;
$confirmedItems = [];

if ($confirmedOrderNumber !== '') {
    $stmtOrd = $pdo->prepare('SELECT * FROM orders WHERE order_number = :onum LIMIT 1');
    $stmtOrd->execute([':onum' => $confirmedOrderNumber]);
    $confirmedOrder = $stmtOrd->fetch();

    if ($confirmedOrder) {
        $stmtItems = $pdo->prepare('SELECT * FROM order_items WHERE order_id = :oid');
        $stmtItems->execute([':oid' => (int)$confirmedOrder['id']]);
        $confirmedItems = $stmtItems->fetchAll();
    }
}

$cart = getCartDetails($pdo);
if (!$confirmedOrder && empty($cart['items'])) {
    header('Location: cart.php');
    exit;
}

$pageTitle = $confirmedOrder ? 'Order Confirmed #' . $confirmedOrder['order_number'] : 'Secure Checkout';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero-compact">
    <div class="container">
        <span class="section-eyebrow"><?= $confirmedOrder ? 'Order Confirmation' : 'Final Step' ?></span>
        <h1 class="page-hero-compact__title">
            <?= $confirmedOrder ? 'Thank You For Your Order!' : 'Secure Athlete Checkout' ?>
        </h1>
        <p class="page-hero-compact__sub">
            <?= $confirmedOrder
                ? 'Your SportKick order #' . e($confirmedOrder['order_number']) . ' has been registered in our fulfillment system.'
                : 'Complete your delivery details and select a payment method to dispatch your gear.' ?>
        </p>
    </div>
</section>

<div class="container">
    <?php if ($confirmedOrder): ?>
        <!-- ORDER CONFIRMATION RECEIPT -->
        <div class="order-confirmation-box">
            <div style="width:64px;height:64px;border-radius:50%;background:rgba(16,185,129,0.15);color:var(--success);display:inline-flex;align-items:center;justify-content:center;font-size:2rem;font-weight:900;margin-bottom:16px;">
                ✓
            </div>
            <span class="badge" style="background:var(--success);color:#fff;margin-bottom:10px;">
                ORDER STATUS: <?= e(strtoupper($confirmedOrder['order_status'])) ?>
            </span>
            <h2 style="font-family:var(--font-display);font-size:2.4rem;font-weight:900;text-transform:uppercase;">
                Order #<?= e($confirmedOrder['order_number']) ?>
            </h2>
            <p style="color:var(--text-secondary);margin:8px 0 26px;">
                Confirmation sent to <strong><?= e($confirmedOrder['customer_email']) ?></strong>
            </p>

            <div style="text-align:left;background:var(--bg-light);padding:22px;border-radius:var(--radius-md);margin-bottom:24px;">
                <div class="form-grid-2" style="margin-bottom:16px;">
                    <div>
                        <div style="font-size:0.75rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;">Recipient &amp; Contact</div>
                        <div style="font-weight:700;"><?= e($confirmedOrder['customer_name']) ?></div>
                        <div style="font-size:0.88rem;color:var(--text-secondary);"><?= e($confirmedOrder['customer_phone']) ?></div>
                    </div>
                    <div>
                        <div style="font-size:0.75rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;">Delivery Address</div>
                        <div style="font-weight:700;"><?= e($confirmedOrder['shipping_address']) ?></div>
                        <div style="font-size:0.88rem;color:var(--text-secondary);">
                            <?= e($confirmedOrder['shipping_city']) ?>, <?= e($confirmedOrder['shipping_state']) ?> - <?= e($confirmedOrder['shipping_zip']) ?>
                        </div>
                    </div>
                </div>

                <div style="border-top:1px solid var(--border-light);padding-top:14px;">
                    <div style="font-size:0.78rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;margin-bottom:8px;">
                        Ordered Items (Payment: <?= e($confirmedOrder['payment_method']) ?> — <?= e($confirmedOrder['payment_status']) ?>)
                    </div>
                    <?php foreach ($confirmedItems as $oi): ?>
                        <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:0.9rem;border-bottom:1px dashed var(--border-light);">
                            <span>
                                <strong><?= e($oi['product_name']) ?></strong>
                                (<?= e($oi['size']) ?>, <?= e($oi['color']) ?>) &times; <?= (int)$oi['quantity'] ?>
                            </span>
                            <strong><?= formatPrice((float)$oi['line_total']) ?></strong>
                        </div>
                    <?php endforeach; ?>
                    <div style="display:flex;justify-content:space-between;padding-top:12px;font-family:var(--font-display);font-size:1.45rem;font-weight:900;">
                        <span>TOTAL PAID</span>
                        <span class="text-accent"><?= formatPrice((float)$confirmedOrder['total_amount']) ?></span>
                    </div>
                </div>
            </div>

            <div style="display:flex;justify-content:center;gap:14px;flex-wrap:wrap;">
                <?php if ($currentUser): ?>
                    <a href="profile.php" class="btn btn--dark">View In Order History</a>
                <?php endif; ?>
                <a href="shop.php" class="btn btn--primary">Continue Shopping &rarr;</a>
            </div>
        </div>
    <?php else: ?>
        <!-- CHECKOUT FORM & SUMMARY -->
        <form id="checkoutForm" class="checkout-layout" novalidate>
            <div class="checkout-form-card">
                <!-- 1. Customer Information -->
                <h2 style="font-family:var(--font-display);font-size:1.55rem;font-weight:900;text-transform:uppercase;margin-bottom:18px;">
                    1. Customer Information
                </h2>

                <div class="form-group">
                    <label for="customer_name" class="form-label">Full Name *</label>
                    <input
                        type="text"
                        id="customer_name"
                        name="customer_name"
                        class="form-control"
                        required
                        value="<?= e($currentUser['full_name'] ?? '') ?>"
                        placeholder="e.g. Aarav Sharma"
                    >
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="customer_email" class="form-label">Email Address *</label>
                        <input
                            type="email"
                            id="customer_email"
                            name="customer_email"
                            class="form-control"
                            required
                            value="<?= e($currentUser['email'] ?? '') ?>"
                            placeholder="athlete@sportkick.com"
                        >
                    </div>
                    <div class="form-group">
                        <label for="customer_phone" class="form-label">Phone Number *</label>
                        <input
                            type="tel"
                            id="customer_phone"
                            name="customer_phone"
                            class="form-control"
                            required
                            value="<?= e($currentUser['phone'] ?? '') ?>"
                            placeholder="+91 98765 43210"
                        >
                    </div>
                </div>

                <!-- 2. Delivery Address -->
                <h2 style="font-family:var(--font-display);font-size:1.55rem;font-weight:900;text-transform:uppercase;margin:26px 0 18px;">
                    2. Delivery Address
                </h2>

                <div class="form-group">
                    <label for="shipping_address" class="form-label">Street Address / Apartment / Landmark *</label>
                    <input
                        type="text"
                        id="shipping_address"
                        name="shipping_address"
                        class="form-control"
                        required
                        value="<?= e($currentUser['address'] ?? '') ?>"
                        placeholder="42 Olympic Residency, Indiranagar"
                    >
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="shipping_city" class="form-label">City *</label>
                        <input
                            type="text"
                            id="shipping_city"
                            name="shipping_city"
                            class="form-control"
                            required
                            value="<?= e($currentUser['city'] ?? '') ?>"
                            placeholder="Bengaluru"
                        >
                    </div>
                    <div class="form-group">
                        <label for="shipping_state" class="form-label">State / Province *</label>
                        <input
                            type="text"
                            id="shipping_state"
                            name="shipping_state"
                            class="form-control"
                            required
                            value="<?= e($currentUser['state'] ?? '') ?>"
                            placeholder="Karnataka"
                        >
                    </div>
                </div>

                <div class="form-group" style="max-width:240px;">
                    <label for="shipping_zip" class="form-label">Postal / ZIP Code *</label>
                    <input
                        type="text"
                        id="shipping_zip"
                        name="shipping_zip"
                        class="form-control"
                        required
                        value="<?= e($currentUser['zip_code'] ?? '') ?>"
                        placeholder="560038"
                    >
                </div>

                <!-- 3. Payment Method Selection -->
                <h2 style="font-family:var(--font-display);font-size:1.55rem;font-weight:900;text-transform:uppercase;margin:26px 0 14px;">
                    3. Payment Method
                </h2>

                <div class="payment-methods-grid">
                    <label class="payment-option-card">
                        <input type="radio" name="payment_method" value="Credit / Debit Card" checked>
                        <span>Credit / Debit Card</span>
                    </label>
                    <label class="payment-option-card">
                        <input type="radio" name="payment_method" value="UPI / Instant Pay">
                        <span>UPI / Instant Pay</span>
                    </label>
                    <label class="payment-option-card">
                        <input type="radio" name="payment_method" value="Net Banking">
                        <span>Net Banking</span>
                    </label>
                    <label class="payment-option-card">
                        <input type="radio" name="payment_method" value="Cash on Delivery">
                        <span>Cash on Delivery (COD)</span>
                    </label>
                </div>

                <div id="checkoutErrorBox" style="display:none;margin-top:18px;padding:12px 16px;background:rgba(239,68,68,0.1);border-left:4px solid var(--danger);color:var(--danger);font-weight:700;font-size:0.9rem;"></div>
            </div>

            <!-- Right Column: Order Summary -->
            <aside class="summary-card" aria-label="Checkout Summary">
                <h2 class="summary-card__title">Order Summary</h2>

                <div style="max-height:260px;overflow-y:auto;margin-bottom:16px;padding-right:4px;">
                    <?php foreach ($cart['items'] as $ci): ?>
                        <div style="display:flex;justify-content:space-between;gap:10px;padding:8px 0;border-bottom:1px solid var(--border-light);font-size:0.86rem;">
                            <div>
                                <div style="font-weight:700;color:var(--bg-dark);"><?= e($ci['name']) ?></div>
                                <div style="color:var(--text-muted);">
                                    Size: <?= e($ci['size']) ?> &bull; Qty: <?= (int)$ci['quantity'] ?>
                                </div>
                            </div>
                            <strong style="white-space:nowrap;"><?= formatPrice((float)$ci['line_total']) ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-row">
                    <span>Subtotal (<?= (int)$cart['item_count'] ?> items)</span>
                    <strong><?= formatPrice((float)$cart['subtotal']) ?></strong>
                </div>

                <?php if ((float)$cart['promo_discount'] > 0): ?>
                    <div class="summary-row" style="color:var(--accent);font-weight:700;">
                        <span>Promo Discount (<?= e($cart['promo_code']) ?>)</span>
                        <span>-<?= formatPrice((float)$cart['promo_discount']) ?></span>
                    </div>
                <?php endif; ?>

                <div class="summary-row">
                    <span>Express Delivery</span>
                    <strong><?= (float)$cart['shipping_fee'] === 0.0 ? 'FREE' : formatPrice((float)$cart['shipping_fee']) ?></strong>
                </div>

                <div class="summary-row summary-row--total">
                    <span>Total Payable</span>
                    <span class="text-accent"><?= formatPrice((float)$cart['total_amount']) ?></span>
                </div>

                <button type="submit" id="placeOrderBtn" class="btn btn--primary btn--lg btn--full" style="margin-top:20px;">
                    Confirm &amp; Place Order &rarr;
                </button>

                <a href="cart.php" class="btn btn--outline-dark btn--sm btn--full" style="margin-top:10px;">
                    &larr; Return To Bag
                </a>
            </aside>
        </form>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('checkoutForm');
    if (!form) return;

    const errBox = document.getElementById('checkoutErrorBox');
    const submitBtn = document.getElementById('placeOrderBtn');

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        if (errBox) errBox.style.display = 'none';

        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());

        submitBtn.disabled = true;
        submitBtn.textContent = 'Processing Order...';

        try {
            const res = await fetch('api/checkout.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success && data.redirect) {
                window.location.href = data.redirect;
            } else {
                if (errBox) {
                    errBox.textContent = data.message || 'Please check all required fields.';
                    errBox.style.display = 'block';
                }
                submitBtn.disabled = false;
                submitBtn.textContent = 'Confirm & Place Order →';
            }
        } catch (err) {
            if (errBox) {
                errBox.textContent = 'Network error occurred while placing order.';
                errBox.style.display = 'block';
            }
            submitBtn.disabled = false;
            submitBtn.textContent = 'Confirm & Place Order →';
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
