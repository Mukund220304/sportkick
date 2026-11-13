<?php
/**
 * SportKick - Home Page (index.php)
 * Sections:
 *  1. Hero Banner ("GEAR UP. SHOW UP. NEVER STOP.") + Shop Now CTA
 *  2. Popular Brands Strip
 *  3. Featured 8 Sports Categories
 *  4. Trending Pro Gear
 *  5. Exclusive Deals & Offers Banner + Deal Products
 *  6. New Season Arrivals
 */

$pageTitle = 'Home — Gear Up. Show Up. Never Stop.';
require_once __DIR__ . '/includes/header.php';

$categories = getAllCategories($pdo);
$trendingProducts = queryProducts($pdo, ['badge' => 'trending', 'limit' => 8]);
$newArrivals = queryProducts($pdo, ['badge' => 'new', 'limit' => 8]);
$dealProducts = queryProducts($pdo, ['badge' => 'deal', 'limit' => 4]);
$popularBrands = ['Nike', 'Adidas', 'Puma', 'Asics', 'Under Armour', 'Yonex', 'Salomon'];
?>

<!-- 1. HERO SECTION -->
<section class="hero-section">
    <div class="container hero-section__grid">
        <div class="hero-content">
            <div class="hero-eyebrow">
                <span class="pulse-dot"></span>
                <span>2026 PRO CHAMPIONSHIP SERIES</span>
            </div>
            <h1 class="hero-headline">
                GEAR UP.<br>
                SHOW UP.<br>
                <span>NEVER STOP.</span>
            </h1>
            <p class="hero-lead">
                Tour-grade carbon road racers, explosive court shoes, firm-ground football cleats, and breathable technical sportswear engineered for peak athletic output.
            </p>
            <div class="hero-cta-group">
                <a href="shop.php" class="btn btn--primary btn--lg">
                    <span>Shop Now</span>
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="5" y1="12" x2="19" y2="12"/>
                        <polyline points="12 5 19 12 12 19"/>
                    </svg>
                </a>
                <a href="#categoriesSection" class="btn btn--outline-light btn--lg">Explore 8 Sports</a>
            </div>
            <div class="hero-metrics">
                <div>
                    <div class="hero-metric__val">8</div>
                    <div class="hero-metric__label">Pro Sport Disciplines</div>
                </div>
                <div>
                    <div class="hero-metric__val">100%</div>
                    <div class="hero-metric__label">Authentic Brand Gear</div>
                </div>
                <div>
                    <div class="hero-metric__val">4.8★</div>
                    <div class="hero-metric__label">Verified Athlete Rating</div>
                </div>
            </div>
        </div>

        <div class="hero-visual">
            <div class="hero-showcase-card">
                <img
                    src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1200&q=85"
                    alt="Nike Air Zoom Alphafly Next% 3 Proto"
                    class="hero-showcase-card__img"
                >
                <div class="hero-floating-tag">
                    <div>
                        <div class="hero-floating-tag__sub">FEATURED DROP &bull; RUNNING</div>
                        <div class="hero-floating-tag__title">Nike Air Zoom Alphafly Next% 3</div>
                    </div>
                    <a href="product.php?id=1" class="btn btn--primary btn--sm">₹15,999 &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 2. POPULAR BRANDS BAR -->
<section class="brands-bar" aria-label="Popular Sports Brands">
    <div class="container brands-bar__inner">
        <span class="brands-bar__label">Official Performance Partners:</span>
        <div class="brands-bar__list">
            <?php foreach ($popularBrands as $brandName): ?>
                <a href="shop.php?brand=<?= urlencode($brandName) ?>" class="brand-pill">
                    <?= e($brandName) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 3. FEATURED 8 SPORTS CATEGORIES -->
<section class="section" id="categoriesSection">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="section-eyebrow">Shop By Discipline</span>
                <h2 class="section-title">Featured Sports Categories</h2>
            </div>
            <a href="shop.php" class="btn btn--outline-dark btn--sm">View Full Catalog &rarr;</a>
        </div>

        <div class="categories-grid">
            <?php foreach ($categories as $cat): ?>
                <a href="shop.php?category=<?= e($cat['slug']) ?>" class="category-card">
                    <img
                        src="<?= e($cat['image_url']) ?>"
                        alt="<?= e($cat['name']) ?>"
                        class="category-card__img"
                        loading="lazy"
                    >
                    <div class="category-card__content">
                        <span class="category-card__count"><?= (int)$cat['product_count'] ?> Pro Products</span>
                        <h3 class="category-card__title"><?= e($cat['name']) ?></h3>
                        <p class="category-card__tagline"><?= e($cat['tagline']) ?></p>
                        <span class="category-card__cta">Explore <?= e($cat['name']) ?> &rarr;</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 4. TRENDING PRODUCTS -->
<section class="section section--white">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="section-eyebrow">Most Wanted Right Now</span>
                <h2 class="section-title">Trending Athlete Picks</h2>
            </div>
            <a href="shop.php?badge=trending" class="btn btn--dark btn--sm">See All Trending &rarr;</a>
        </div>

        <div class="products-grid">
            <?php foreach ($trendingProducts as $product): ?>
                <?php include __DIR__ . '/includes/product_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 5. EXCLUSIVE DEALS & OFFERS -->
<section class="section">
    <div class="container">
        <div class="deals-banner">
            <div style="max-width:640px;">
                <span class="section-eyebrow">Limited-Time Locker Room Offers</span>
                <h2 class="deals-banner__title">Save Up To 25% On Championship Footwear &amp; Kits</h2>
                <p style="color:#d4d4d8;font-size:0.98rem;">
                    Stack instant cart discounts on already-reduced pro shoes and training apparel. Apply these codes directly in your shopping bag at checkout:
                </p>
                <div class="deals-banner__codes">
                    <div class="promo-coupon">
                        <span>CODE:</span>
                        <strong>KICK10</strong>
                        <span style="font-size:0.78rem;color:#a1a1aa;">Extra 10% Off Any Order</span>
                    </div>
                    <div class="promo-coupon">
                        <span>CODE:</span>
                        <strong>ATHLETE20</strong>
                        <span style="font-size:0.78rem;color:#a1a1aa;">Extra 20% Off Orders ₹10,000+</span>
                    </div>
                </div>
            </div>
            <div>
                <a href="shop.php?badge=deal" class="btn btn--primary btn--lg">Unlock All Deals &rarr;</a>
            </div>
        </div>

        <div class="products-grid">
            <?php foreach ($dealProducts as $product): ?>
                <?php include __DIR__ . '/includes/product_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 6. NEW ARRIVALS -->
<section class="section section--white">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="section-eyebrow">Fresh Out Of The Lab</span>
                <h2 class="section-title">New Season Arrivals</h2>
            </div>
            <a href="shop.php?badge=new" class="btn btn--outline-dark btn--sm">Explore New Drops &rarr;</a>
        </div>

        <div class="products-grid">
            <?php foreach ($newArrivals as $product): ?>
                <?php include __DIR__ . '/includes/product_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

