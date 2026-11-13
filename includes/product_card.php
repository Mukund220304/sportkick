<?php
/**
 * SportKick - Reusable Premium Product Card Component
 * Expects $product array and $wishlistIds array in scope.
 */
if (!isset($product)) {
    return;
}
$pId = (int)$product['id'];
$inWish = isset($wishlistIds) && in_array($pId, $wishlistIds, true);
$sizesList = array_values(array_filter(array_map('trim', explode(',', $product['sizes'] ?? 'UK 8,UK 9,UK 10'))));
$defaultSize = $sizesList[0] ?? 'UK 9';
$defaultColor = $product['color_primary'] ?? 'Black';
$discount = (int)($product['discount_percent'] ?? 0);
?>
<article class="product-card" data-product-id="<?= $pId ?>" data-default-size="<?= e($defaultSize) ?>" data-default-color="<?= e($defaultColor) ?>">
    <div class="product-card__media">
        <a href="product.php?id=<?= $pId ?>" class="product-card__img-link" aria-label="<?= e($product['name']) ?>">
            <img
                src="<?= e($product['image_main']) ?>"
                alt="<?= e($product['name']) ?>"
                class="product-card__img"
                loading="lazy"
                onerror="this.onerror=null;this.src='assets/images/placeholder-shoe.svg';"
            >
        </a>

        <div class="product-card__badges">
            <?php if ($discount > 0): ?>
                <span class="badge badge--discount">-<?= $discount ?>%</span>
            <?php endif; ?>
            <?php if (!empty($product['is_new_arrival'])): ?>
                <span class="badge badge--new">NEW</span>
            <?php elseif (!empty($product['is_trending'])): ?>
                <span class="badge badge--hot">HOT</span>
            <?php endif; ?>
        </div>

        <div class="product-card__actions-floating">
            <button
                type="button"
                class="icon-action-btn js-wishlist-toggle <?= $inWish ? 'is-active' : '' ?>"
                data-product-id="<?= $pId ?>"
                title="<?= $inWish ? 'Remove from Wishlist' : 'Save to Wishlist' ?>"
                aria-label="Toggle Wishlist"
            >
                <svg viewBox="0 0 24 24" width="18" height="18" fill="<?= $inWish ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
            </button>
            <button
                type="button"
                class="icon-action-btn js-quick-view"
                data-product-id="<?= $pId ?>"
                title="Quick View"
                aria-label="Quick View Product"
            >
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            </button>
        </div>
    </div>

    <div class="product-card__body">
        <div class="product-card__meta">
            <span class="product-card__brand"><?= e($product['brand']) ?></span>
            <span class="product-card__dot">•</span>
            <a href="shop.php?category=<?= e($product['category_slug'] ?? '') ?>" class="product-card__category">
                <?= e($product['category_name'] ?? 'Sport') ?>
            </a>
            <div class="product-card__rating" title="Rated <?= e((string)$product['rating']) ?> out of 5">
                <span class="star-icon">★</span>
                <span><?= number_format((float)$product['rating'], 1) ?></span>
                <span class="rating-count">(<?= (int)$product['reviews_count'] ?>)</span>
            </div>
        </div>

        <h3 class="product-card__title">
            <a href="product.php?id=<?= $pId ?>"><?= e($product['name']) ?></a>
        </h3>

        <div class="product-card__sizes" aria-label="Available Sizes">
            <span class="sizes-label">SIZE:</span>
            <div class="sizes-pills">
                <?php foreach (array_slice($sizesList, 0, 5) as $idx => $sz): ?>
                    <button
                        type="button"
                        class="size-chip js-card-size <?= $idx === 0 ? 'is-selected' : '' ?>"
                        data-size="<?= e($sz) ?>"
                    ><?= e($sz) ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="product-card__footer">
            <div class="product-card__pricing">
                <span class="price-current"><?= formatPrice((float)$product['price']) ?></span>
                <?php if ((float)$product['original_price'] > (float)$product['price']): ?>
                    <span class="price-original"><?= formatPrice((float)$product['original_price']) ?></span>
                <?php endif; ?>
            </div>

            <button
                type="button"
                class="btn-card-cart js-add-to-cart"
                data-product-id="<?= $pId ?>"
            >
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
                <span>Add to Cart</span>
            </button>
        </div>
    </div>
</article>

