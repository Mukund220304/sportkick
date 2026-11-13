<?php
/**
 * SportKick - Product Details Page (product.php)
 * Displays: Multiple product images, specifications, description, size selection,
 * quantity selection, price & discount, Add to Cart, Wishlist, and Related Products.
 */

require_once __DIR__ . '/includes/functions.php';

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$slug = trim((string)($_GET['slug'] ?? ''));

if ($productId > 0) {
    $stmt = $pdo->prepare('
        SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM products p
        INNER JOIN categories c ON p.category_id = c.id
        WHERE p.id = :id
        LIMIT 1
    ');
    $stmt->execute([':id' => $productId]);
} elseif ($slug !== '') {
    $stmt = $pdo->prepare('
        SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM products p
        INNER JOIN categories c ON p.category_id = c.id
        WHERE p.slug = :slug
        LIMIT 1
    ');
    $stmt->execute([':slug' => $slug]);
} else {
    $stmt = $pdo->query('
        SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM products p
        INNER JOIN categories c ON p.category_id = c.id
        ORDER BY p.id ASC
        LIMIT 1
    ');
}

$productDetail = $stmt->fetch();

if (!$productDetail) {
    header('Location: shop.php');
    exit;
}

$pId = (int)$productDetail['id'];
$sizesArray = array_values(array_filter(array_map('trim', explode(',', $productDetail['sizes']))));
$colorsArray = array_values(array_filter(array_map('trim', explode(',', $productDetail['colors']))));
$galleryImages = json_decode($productDetail['image_gallery'], true);
if (!is_array($galleryImages) || empty($galleryImages)) {
    $galleryImages = [$productDetail['image_main']];
}
$specsObject = json_decode($productDetail['specifications'], true) ?: [];

// Fetch related products in the same sport category
$stmtRelated = $pdo->prepare('
    SELECT p.*, c.name AS category_name, c.slug AS category_slug
    FROM products p
    INNER JOIN categories c ON p.category_id = c.id
    WHERE p.category_id = :cat_id AND p.id != :pid
    ORDER BY p.rating DESC
    LIMIT 4
');
$stmtRelated->execute([
    ':cat_id' => (int)$productDetail['category_id'],
    ':pid'    => $pId,
]);
$relatedProducts = $stmtRelated->fetchAll();

// If fewer than 4 in same category, supplement with top trending products
if (count($relatedProducts) < 4) {
    $needed = 4 - count($relatedProducts);
    $excludeIds = array_merge([$pId], array_column($relatedProducts, 'id'));
    $inClause = implode(',', array_map('intval', $excludeIds));
    $extra = $pdo->query("
        SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM products p
        INNER JOIN categories c ON p.category_id = c.id
        WHERE p.id NOT IN ($inClause)
        ORDER BY p.is_trending DESC, p.rating DESC
        LIMIT $needed
    ")->fetchAll();
    $relatedProducts = array_merge($relatedProducts, $extra);
}

$pageTitle = $productDetail['name'] . ' (' . $productDetail['brand'] . ')';
require_once __DIR__ . '/includes/header.php';

$inWishlist = in_array($pId, $wishlistIds, true);
$savingsAmount = max(0, (float)$productDetail['original_price'] - (float)$productDetail['price']);
?>

<div class="container">
    <!-- Breadcrumbs -->
    <nav class="breadcrumb-bar" aria-label="Breadcrumb">
        <a href="index.php">Home</a> &nbsp;/&nbsp;
        <a href="shop.php?category=<?= e($productDetail['category_slug']) ?>"><?= e($productDetail['category_name']) ?></a> &nbsp;/&nbsp;
        <strong style="color:var(--text-primary);"><?= e($productDetail['name']) ?></strong>
    </nav>

    <!-- Main Product Detail Showcase -->
    <div class="product-detail-card">
        <!-- Left Column: Multi-Image Interactive Gallery -->
        <div class="pd-gallery">
            <div class="pd-gallery__main">
                <img
                    src="<?= e($galleryImages[0]) ?>"
                    alt="<?= e($productDetail['name']) ?>"
                    id="pdMainImage"
                    onerror="this.onerror=null;this.src='assets/images/placeholder-shoe.svg';"
                >
            </div>
            <div class="pd-gallery__thumbs" role="region" aria-label="Product Image Gallery">
                <?php foreach ($galleryImages as $idx => $imgUrl): ?>
                    <button
                        type="button"
                        class="pd-thumb <?= $idx === 0 ? 'is-active' : '' ?>"
                        data-large-src="<?= e($imgUrl) ?>"
                        aria-label="View product angle <?= $idx + 1 ?>"
                    >
                        <img src="<?= e($imgUrl) ?>" alt="<?= e($productDetail['name']) ?> angle <?= $idx + 1 ?>">
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Right Column: Specs, Options, Pricing & Add to Cart -->
        <div class="pd-info">
            <div class="pd-info__top">
                <a href="shop.php?brand=<?= urlencode($productDetail['brand']) ?>" class="pd-brand-pill">
                    <?= e($productDetail['brand']) ?>
                </a>
                <?php if ((int)$productDetail['discount_percent'] > 0): ?>
                    <span class="badge badge--discount">SAVE <?= (int)$productDetail['discount_percent'] ?>%</span>
                <?php endif; ?>
                <span class="pd-sku">SKU: <?= e($productDetail['sku']) ?></span>
                <span style="margin-left:auto;font-size:0.8rem;font-weight:800;color:var(--success);">
                    ● In Stock (<?= (int)$productDetail['stock'] ?> available)
                </span>
            </div>

            <h1 class="pd-title"><?= e($productDetail['name']) ?></h1>

            <div class="pd-rating-row">
                <span class="star-icon">★★★★★</span>
                <span><?= number_format((float)$productDetail['rating'], 2) ?> / 5.0</span>
                <span style="color:var(--text-muted);">• <?= (int)$productDetail['reviews_count'] ?> Verified Athlete Reviews</span>
                <a href="shop.php?category=<?= e($productDetail['category_slug']) ?>" style="margin-left:auto;color:var(--accent);text-transform:uppercase;font-size:0.8rem;">
                    <?= e($productDetail['category_name']) ?> Series &rarr;
                </a>
            </div>

            <div class="pd-price-box">
                <span class="pd-price-current"><?= formatPrice((float)$productDetail['price']) ?></span>
                <?php if ($savingsAmount > 0): ?>
                    <span class="pd-price-original"><?= formatPrice((float)$productDetail['original_price']) ?></span>
                    <span style="margin-left:auto;font-size:0.84rem;font-weight:800;color:var(--accent);">
                        You Save <?= formatPrice($savingsAmount) ?>
                    </span>
                <?php endif; ?>
            </div>

            <p class="pd-description"><?= e($productDetail['description']) ?></p>

            <!-- Size Selector -->
            <div class="pd-selector-group">
                <div class="pd-selector-label">
                    <span>Select Size: <strong id="selectedSizeLabel" class="text-accent"><?= e($sizesArray[0] ?? 'UK 9') ?></strong></span>
                    <span style="font-size:0.78rem;color:var(--text-muted);">True to Athletic Fit</span>
                </div>
                <div class="pd-options-row" id="pdSizeGroup">
                    <?php foreach ($sizesArray as $idx => $sz): ?>
                        <button
                            type="button"
                            class="pd-option-btn <?= $idx === 0 ? 'is-selected' : '' ?>"
                            data-size-val="<?= e($sz) ?>"
                        ><?= e($sz) ?></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Color Selector -->
            <div class="pd-selector-group">
                <div class="pd-selector-label">
                    <span>Select Colorway: <strong id="selectedColorLabel"><?= e($colorsArray[0] ?? $productDetail['color_primary']) ?></strong></span>
                </div>
                <div class="pd-options-row" id="pdColorGroup">
                    <?php foreach ($colorsArray as $idx => $clr): ?>
                        <button
                            type="button"
                            class="pd-option-btn <?= $idx === 0 ? 'is-selected' : '' ?>"
                            data-color-val="<?= e($clr) ?>"
                        ><?= e($clr) ?></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Quantity & Purchase Actions -->
            <div class="pd-purchase-row">
                <div class="qty-stepper" aria-label="Select Quantity">
                    <button type="button" id="pdQtyMinus" aria-label="Decrease quantity">&minus;</button>
                    <input type="number" id="pdQtyInput" value="1" min="1" max="10" readonly aria-label="Quantity">
                    <button type="button" id="pdQtyPlus" aria-label="Increase quantity">&plus;</button>
                </div>

                <button
                    type="button"
                    class="btn btn--primary"
                    id="pdAddToCartBtn"
                    style="flex:1;"
                    data-product-id="<?= $pId ?>"
                >
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                    <span>Add To Shopping Bag</span>
                </button>

                <button
                    type="button"
                    class="btn btn--outline-dark js-wishlist-toggle <?= $inWishlist ? 'is-active' : '' ?>"
                    data-product-id="<?= $pId ?>"
                    title="Save to Wishlist"
                    aria-label="Save to Wishlist"
                >
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="<?= $inWishlist ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                    </svg>
                </button>
            </div>

            <!-- Technical Specifications Table -->
            <?php if (!empty($specsObject)): ?>
                <div class="pd-specs-box">
                    <h3 class="pd-specs-title">Technical Specifications</h3>
                    <table class="specs-table">
                        <tbody>
                            <?php foreach ($specsObject as $specKey => $specVal): ?>
                                <tr>
                                    <th><?= e((string)$specKey) ?></th>
                                    <td><?= e((string)$specVal) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Related Products Section -->
    <section style="margin-bottom:72px;">
        <div class="section-header">
            <div>
                <span class="section-eyebrow">Complete Your Kit</span>
                <h2 class="section-title">Related <?= e($productDetail['category_name']) ?> Gear</h2>
            </div>
            <a href="shop.php?category=<?= e($productDetail['category_slug']) ?>" class="btn btn--outline-dark btn--sm">
                View All <?= e($productDetail['category_name']) ?> &rarr;
            </a>
        </div>

        <div class="products-grid">
            <?php foreach ($relatedProducts as $product): ?>
                <?php include __DIR__ . '/includes/product_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Thumbnail gallery switcher
    const mainImg = document.getElementById('pdMainImage');
    document.querySelectorAll('.pd-thumb[data-large-src]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.pd-thumb[data-large-src]').forEach(function (b) {
                b.classList.remove('is-active');
            });
            btn.classList.add('is-active');
            if (mainImg) {
                mainImg.src = btn.getAttribute('data-large-src');
            }
        });
    });

    // 2. Size & Color selection
    let chosenSize = <?= json_encode($sizesArray[0] ?? 'UK 9') ?>;
    let chosenColor = <?= json_encode($colorsArray[0] ?? $productDetail['color_primary']) ?>;

    const sizeGroup = document.getElementById('pdSizeGroup');
    const sizeLabel = document.getElementById('selectedSizeLabel');
    if (sizeGroup) {
        sizeGroup.querySelectorAll('[data-size-val]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                sizeGroup.querySelectorAll('[data-size-val]').forEach(function (b) { b.classList.remove('is-selected'); });
                btn.classList.add('is-selected');
                chosenSize = btn.getAttribute('data-size-val');
                if (sizeLabel) sizeLabel.textContent = chosenSize;
            });
        });
    }

    const colorGroup = document.getElementById('pdColorGroup');
    const colorLabel = document.getElementById('selectedColorLabel');
    if (colorGroup) {
        colorGroup.querySelectorAll('[data-color-val]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                colorGroup.querySelectorAll('[data-color-val]').forEach(function (b) { b.classList.remove('is-selected'); });
                btn.classList.add('is-selected');
                chosenColor = btn.getAttribute('data-color-val');
                if (colorLabel) colorLabel.textContent = chosenColor;
            });
        });
    }

    // 3. Quantity Stepper
    const qtyInput = document.getElementById('pdQtyInput');
    const minusBtn = document.getElementById('pdQtyMinus');
    const plusBtn = document.getElementById('pdQtyPlus');
    if (minusBtn && plusBtn && qtyInput) {
        minusBtn.addEventListener('click', function () {
            const v = Math.max(1, parseInt(qtyInput.value || '1', 10) - 1);
            qtyInput.value = String(v);
        });
        plusBtn.addEventListener('click', function () {
            const v = Math.min(10, parseInt(qtyInput.value || '1', 10) + 1);
            qtyInput.value = String(v);
        });
    }

    // 4. Add to Cart Button on Product Detail Page
    const pdCartBtn = document.getElementById('pdAddToCartBtn');
    if (pdCartBtn) {
        pdCartBtn.addEventListener('click', async function () {
            const productId = parseInt(pdCartBtn.getAttribute('data-product-id') || '0', 10);
            const quantity = parseInt(qtyInput ? qtyInput.value : '1', 10);
            pdCartBtn.disabled = true;
            try {
                const res = await fetch('api/cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'add',
                        product_id: productId,
                        size: chosenSize,
                        color: chosenColor,
                        quantity: quantity
                    })
                });
                const data = await res.json();
                if (data.success && window.SportKick) {
                    window.SportKick.updateHeaderCounters(data.cart.item_count, undefined);
                    window.SportKick.showToast(data.message);
                } else if (window.SportKick) {
                    window.SportKick.showToast(data.message || 'Could not add item', true);
                }
            } finally {
                pdCartBtn.disabled = false;
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

