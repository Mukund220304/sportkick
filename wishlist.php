<?php
/**
 * SportKick - Saved Wishlist Page (wishlist.php)
 * Allows users to view saved favorite products, move them to cart, or remove them.
 */

$pageTitle = 'Saved Wishlist';
require_once __DIR__ . '/includes/header.php';

$wishlistProducts = getWishlistItems($pdo);
?>

<section class="page-hero-compact">
    <div class="container">
        <span class="section-eyebrow">Your Locker Favorites</span>
        <h1 class="page-hero-compact__title">Saved Wishlist (<?= count($wishlistProducts) ?>)</h1>
        <p class="page-hero-compact__sub">Keep track of your favorite shoes and sportswear drops and move them to your bag anytime.</p>
    </div>
</section>

<div class="container" style="padding-top:40px;padding-bottom:72px;">
    <?php if (empty($wishlistProducts)): ?>
        <div class="empty-state">
            <h2 class="empty-state__title">Your Wishlist Is Empty</h2>
            <p class="empty-state__text">
                Tap the heart icon on any sports shoe or apparel card to save your favorite items here for quick access.
            </p>
            <a href="shop.php" class="btn btn--primary btn--lg">Discover Pro Gear &rarr;</a>
        </div>
    <?php else: ?>
        <div class="products-grid" id="wishlistGrid">
            <?php foreach ($wishlistProducts as $product): ?>
                <?php include __DIR__ . '/includes/product_card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
