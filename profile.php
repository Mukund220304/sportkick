<?php
/**
 * SportKick - Athlete Profile, Address Book & Order History Page (profile.php)
 */

require_once __DIR__ . '/includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php?redirect=profile.php');
    exit;
}

$user = getCurrentUser($pdo);
if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Fetch user's orders & items
$stmtOrders = $pdo->prepare('
    SELECT * FROM orders
    WHERE user_id = :uid OR customer_email = :email
    ORDER BY created_at DESC
');
$stmtOrders->execute([
    ':uid'   => (int)$user['id'],
    ':email' => $user['email'],
]);
$userOrders = $stmtOrders->fetchAll();

$stmtOrderItems = $pdo->prepare('SELECT * FROM order_items WHERE order_id = :oid');

$pageTitle = 'Athlete Profile — ' . $user['full_name'];
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero-compact">
    <div class="container" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
        <div>
            <span class="section-eyebrow">SportKick Locker Room</span>
            <h1 class="page-hero-compact__title"><?= e($user['full_name']) ?></h1>
            <p class="page-hero-compact__sub">Member since <?= date('F Y', strtotime($user['created_at'])) ?> &bull; <?= e($user['email']) ?></p>
        </div>
        <a href="logout.php" class="btn btn--outline-light btn--sm">Sign Out</a>
    </div>
</section>

<div class="container">
    <div class="profile-layout">
        <!-- Left Column: Edit Profile & Delivery Details -->
        <aside class="cart-table-card" aria-label="Profile Settings">
            <h2 style="font-family:var(--font-display);font-size:1.5rem;font-weight:900;text-transform:uppercase;margin-bottom:16px;">
                Profile &amp; Delivery Address
            </h2>

            <form id="profileUpdateForm">
                <input type="hidden" name="action" value="update_profile">

                <div class="form-group">
                    <label for="profName" class="form-label">Full Name *</label>
                    <input type="text" id="profName" name="full_name" class="form-control" required value="<?= e($user['full_name']) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled style="background:var(--bg-light);color:var(--text-muted);">
                </div>

                <div class="form-group">
                    <label for="profPhone" class="form-label">Phone Number</label>
                    <input type="tel" id="profPhone" name="phone" class="form-control" value="<?= e($user['phone']) ?>" placeholder="+91 98765 43210">
                </div>

                <div class="form-group">
                    <label for="profAddress" class="form-label">Street Address</label>
                    <input type="text" id="profAddress" name="address" class="form-control" value="<?= e($user['address']) ?>" placeholder="Street, Apartment, Landmark">
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="profCity" class="form-label">City</label>
                        <input type="text" id="profCity" name="city" class="form-control" value="<?= e($user['city']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="profState" class="form-label">State</label>
                        <input type="text" id="profState" name="state" class="form-control" value="<?= e($user['state']) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="profZip" class="form-label">Postal / ZIP Code</label>
                    <input type="text" id="profZip" name="zip_code" class="form-control" value="<?= e($user['zip_code']) ?>">
                </div>

                <button type="submit" class="btn btn--primary btn--full">Save Profile Changes</button>
            </form>

            <hr style="border:none;border-top:1px solid var(--border-light);margin:26px 0;">

            <h3 style="font-family:var(--font-display);font-size:1.3rem;font-weight:900;text-transform:uppercase;margin-bottom:14px;">
                Update Password
            </h3>
            <form id="passwordChangeForm">
                <input type="hidden" name="action" value="change_password">
                <div class="form-group">
                    <label for="newPass" class="form-label">New Password</label>
                    <input type="password" id="newPass" name="new_password" class="form-control" required minlength="6" placeholder="Min 6 characters">
                </div>
                <div class="form-group">
                    <label for="confPass" class="form-label">Confirm New Password</label>
                    <input type="password" id="confPass" name="confirm_password" class="form-control" required minlength="6" placeholder="Repeat new password">
                </div>
                <button type="submit" class="btn btn--dark btn--sm btn--full">Update Password</button>
            </form>
        </aside>

        <!-- Right Column: Order History -->
        <section aria-label="Order History">
            <h2 style="font-family:var(--font-display);font-size:1.85rem;font-weight:900;text-transform:uppercase;margin-bottom:18px;">
                Order History (<?= count($userOrders) ?>)
            </h2>

            <?php if (empty($userOrders)): ?>
                <div class="empty-state">
                    <h3 class="empty-state__title">No Orders Yet</h3>
                    <p class="empty-state__text">
                        Once you place an order at checkout, your complete receipt and tracking status will appear right here.
                    </p>
                    <a href="shop.php" class="btn btn--primary">Start Shopping &rarr;</a>
                </div>
            <?php else: ?>
                <?php foreach ($userOrders as $ord): ?>
                    <?php
                    $stmtOrderItems->execute([':oid' => (int)$ord['id']]);
                    $oItems = $stmtOrderItems->fetchAll();
                    ?>
                    <article class="order-card">
                        <div class="order-card__header">
                            <div>
                                <span style="font-size:0.75rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;">
                                    Order Placed: <?= date('M d, Y • h:i A', strtotime($ord['created_at'])) ?>
                                </span>
                                <h3 style="font-family:var(--font-display);font-size:1.45rem;font-weight:900;">
                                    <a href="checkout.php?order=<?= urlencode($ord['order_number']) ?>">
                                        Order #<?= e($ord['order_number']) ?>
                                    </a>
                                </h3>
                            </div>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <span class="badge" style="background:rgba(16,185,129,0.14);color:var(--success);">
                                    <?= e($ord['order_status']) ?>
                                </span>
                                <span class="badge badge--new"><?= e($ord['payment_method']) ?></span>
                                <strong style="font-family:var(--font-display);font-size:1.45rem;" class="text-accent">
                                    <?= formatPrice((float)$ord['total_amount']) ?>
                                </strong>
                            </div>
                        </div>

                        <div style="font-size:0.88rem;color:var(--text-secondary);margin-bottom:12px;">
                            Ship to: <strong><?= e($ord['customer_name']) ?></strong> —
                            <?= e($ord['shipping_address']) ?>, <?= e($ord['shipping_city']) ?>, <?= e($ord['shipping_state']) ?> <?= e($ord['shipping_zip']) ?>
                        </div>

                        <div style="background:var(--bg-light);padding:12px 16px;border-radius:var(--radius-sm);">
                            <?php foreach ($oItems as $item): ?>
                                <div style="display:flex;justify-content:space-between;padding:4px 0;font-size:0.88rem;">
                                    <span>
                                        <strong><?= e($item['product_name']) ?></strong>
                                        (<?= e($item['brand']) ?> • Size: <?= e($item['size']) ?> • Color: <?= e($item['color']) ?>)
                                        &times; <?= (int)$item['quantity'] ?>
                                    </span>
                                    <strong><?= formatPrice((float)$item['line_total']) ?></strong>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const profForm = document.getElementById('profileUpdateForm');
    if (profForm) {
        profForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const payload = Object.fromEntries(new FormData(profForm).entries());
            const res = await fetch('api/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (window.SportKick) {
                window.SportKick.showToast(data.message || 'Profile updated', !data.success);
            }
        });
    }

    const passForm = document.getElementById('passwordChangeForm');
    if (passForm) {
        passForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const payload = Object.fromEntries(new FormData(passForm).entries());
            const res = await fetch('api/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (window.SportKick) {
                window.SportKick.showToast(data.message || 'Password updated', !data.success);
            }
            if (data.success) {
                passForm.reset();
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

