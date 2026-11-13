<?php
/**
 * SportKick - Flagship Sportswear Landing Page (index.php)
 * Sections:
 *  1. Hero Banner ("PUSH BEYOND LIMITS." / "Performance starts with what you wear." / SHOP NOW / EXPLORE COLLECTION)
 *  2. Featured Brands Strip
 *  3. Shop by Sport Categories (8 disciplines)
 *  4. Trending Products
 *  5. Promotional Campaign Banner
 *  6. New Arrivals
 *  7. Best Sellers
 *  8. About SportKick Performance Lab (#aboutSection)
 *  9. Newsletter Section
 * 10. Professional Footer
 */

$pageTitle = 'Push Beyond Limits — Pro Sportswear & Footwear';
require_once __DIR__ . '/includes/header.php';

$categories = getAllCategories($pdo);
$trendingProducts = queryProducts($pdo, ['badge' => 'trending', 'limit' => 8]);
$newArrivals = queryProducts($pdo, ['badge' => 'new', 'limit' => 4]);
$bestSellers = queryProducts($pdo, ['sort' => 'rating', 'limit' => 4]);
$featuredBrands = [
    ['name' => 'Nike',         'tag' => 'Air Zoom & Flyplate'],
    ['name' => 'Adidas',       'tag' => 'Lightstrike & Boost'],
    ['name' => 'Puma',         'tag' => 'NITRO Elite Series'],
    ['name' => 'Asics',        'tag' => 'GEL & FlyteFoam'],
    ['name' => 'Under Armour', 'tag' => 'HOVR & TriBase'],
    ['name' => 'Yonex',        'tag' => 'Power Cushion+'],
    ['name' => 'Salomon',      'tag' => 'GORE-TEX Contagrip'],
];
?>

<!-- 1. HERO SECTION -->
<section class="hero-section">
    <div class="hero-section__bg" aria-hidden="true"></div>
    <div class="container hero-section__grid">
        <div class="hero-content">
            <div class="hero-eyebrow">
                <span class="pulse-dot"></span>
                <span>SPORTKICK 2026 PERFORMANCE SERIES</span>
            </div>

            <h1 class="hero-headline">
                PUSH BEYOND <span>LIMITS.</span>
            </h1>

            <p class="hero-subtitle">
                Performance starts with what you wear.
            </p>

            <p class="hero-lead">
                Engineered for marathon road records, explosive court lock-down, firm-ground pitch acceleration, and relentless gym conditioning across 8 competitive sports.
            </p>

            <div class="hero-cta-group">
                <a href="shop.php" class="btn btn--primary btn--lg">
                    <span>SHOP NOW</span>
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="5" y1="12" x2="19" y2="12"/>
                        <polyline points="12 5 19 12 12 19"/>
                    </svg>
                </a>
                <a href="#shopBySport" class="btn btn--outline-light btn--lg">
                    <span>EXPLORE COLLECTION</span>
                </a>
            </div>

            <div class="hero-metrics">
                <div class="hero-metric">
                    <div class="hero-metric__val">08</div>
                    <div class="hero-metric__label">Athletic Disciplines</div>
                </div>
                <div class="hero-metric">
                    <div class="hero-metric__val">24+</div>
                    <div class="hero-metric__label">Tour-Grade Models</div>
                </div>
                <div class="hero-metric">
                    <div class="hero-metric__val">4.9★</div>
                    <div class="hero-metric__label">Pro Athlete Rating</div>
                </div>
            </div>
        </div>

        <div class="hero-visual">
            <div class="hero-showcase-card">
                <span class="hero-FloatingBadge">SIGNATURE EDITION</span>
                <img
                    src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1300&q=85"
                    alt="Nike Air Zoom Alphafly Next% 3 Proto"
                    class="hero-showcase-card__img"
                >
                <div class="hero-floating-tag">
                    <div>
                        <div class="hero-floating-tag__sub">MARATHON CARBON RACER &bull; RUNNING</div>
                        <div class="hero-floating-tag__title">Nike Air Zoom Alphafly Next% 3</div>
                    </div>
                    <a href="product.php?id=1" class="btn btn--primary btn--sm">₹15,999 &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 2. FEATURED BRANDS -->
<section class="brands-bar" aria-label="Featured Sportswear Brands">
    <div class="container">
        <div class="brands-bar__header">
            <span class="brands-bar__label">FEATURED PERFORMANCE BRANDS</span>
            <a href="shop.php" class="brands-bar__all">Browse All Partners &rarr;</a>
        </div>
        <div class="brands-bar__grid">
            <?php foreach ($featuredBrands as $b): ?>
                <a href="shop.php?brand=<?= urlencode($b['name']) ?>" class="brand-card-tile">
                    <span class="brand-card-tile__name"><?= e($b['name']) ?></span>
                    <span class="brand-card-tile__tag"><?= e($b['tag']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 3. SHOP BY SPORT CATEGORIES -->
<section class="section" id="shopBySport">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="section-eyebrow">Engineered For Every Arena</span>
                <h2 class="section-title">Shop By Sport</h2>
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
                        <span class="category-card__count"><?= (int)$cat['product_count'] ?> Products</span>
                        <h3 class="category-card__title"><?= e($cat['name']) ?></h3>
                        <p class="category-card__tagline"><?= e($cat['tagline']) ?></p>
                        <span class="category-card__cta">Shop <?= e($cat['name']) ?> &rarr;</span>
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
                <span class="section-eyebrow">High-Demand Drops</span>
                <h2 class="section-title">Trending Products</h2>
            </div>
            <a href="shop.php?badge=trending" class="btn btn--dark btn--sm">Explore Trending &rarr;</a>
        </div>

        <div class="products-grid">
            <?php foreach ($trendingProducts as $product): ?>
                <?php include __DIR__ . '/includes/product_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 5. PROMOTIONAL CAMPAIGN BANNER -->
<section class="section">
    <div class="container">
        <div class="campaign-banner">
            <div class="campaign-banner__content">
                <span class="campaign-badge">SEASONAL CHAMPIONSHIP CAMPAIGN</span>
                <h2 class="campaign-banner__title">
                    UPGRADE YOUR ROTATION.<br>
                    <span>SAVE UP TO 25% OFF.</span>
                </h2>
                <p class="campaign-banner__desc">
                    Unlock extra member savings on marathon racers, firm-ground football cleats, and pro training kits. Apply these codes directly in your shopping bag:
                </p>
                <div class="deals-banner__codes">
                    <div class="promo-coupon">
                        <span>CODE:</span>
                        <strong>KICK10</strong>
                        <span class="promo-coupon__meta">Flat 10% Off Any Order</span>
                    </div>
                    <div class="promo-coupon">
                        <span>CODE:</span>
                        <strong>ATHLETE20</strong>
                        <span class="promo-coupon__meta">Flat 20% Off Orders ₹10,000+</span>
                    </div>
                </div>
                <div style="margin-top:28px;display:flex;gap:14px;flex-wrap:wrap;">
                    <a href="shop.php?badge=deal" class="btn btn--primary btn--lg">Shop Campaign Deals &rarr;</a>
                    <a href="shop.php?type=Footwear" class="btn btn--outline-light btn--lg">Shop Pro Footwear</a>
                </div>
            </div>

            <div class="campaign-banner__visual">
                <img
                    src="https://images.unsplash.com/photo-1511886929837-354d827aae26?auto=format&fit=crop&w=1000&q=85"
                    alt="Championship Football & Running Gear"
                    loading="lazy"
                >
            </div>
        </div>
    </div>
</section>

<!-- 6. NEW ARRIVALS -->
<section class="section section--white">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="section-eyebrow">Just Landed In The Locker</span>
                <h2 class="section-title">New Arrivals</h2>
            </div>
            <a href="shop.php?badge=new" class="btn btn--outline-dark btn--sm">View All New Arrivals &rarr;</a>
        </div>

        <div class="products-grid">
            <?php foreach ($newArrivals as $product): ?>
                <?php include __DIR__ . '/includes/product_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 7. BEST SELLERS -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="section-eyebrow">Highest Rated By Athletes</span>
                <h2 class="section-title">Best Sellers</h2>
            </div>
            <a href="shop.php?sort=rating" class="btn btn--dark btn--sm">Shop All Best Sellers &rarr;</a>
        </div>

        <div class="products-grid">
            <?php foreach ($bestSellers as $product): ?>
                <?php include __DIR__ . '/includes/product_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 8. ABOUT US / BRAND ENGINEERING SECTION -->
<section class="section section--dark" id="aboutSection">
    <div class="container">
        <div class="about-showcase">
            <div class="about-showcase__text">
                <span class="section-eyebrow">About SportKick</span>
                <h2 class="section-title" style="color:#ffffff;margin-bottom:18px;">
                    BUILT FOR ATHLETES WHO DEMAND PRECISION.
                </h2>
                <p style="color:#b4b4be;font-size:1.02rem;margin-bottom:24px;line-height:1.7;">
                    At SportKick, we believe performance starts with what you wear. Every shoe and technical garment in our catalog is curated for biomechanical efficiency—from carbon-plated marathon propulsion and non-marking gum court soles to GORE-TEX alpine trail protection.
                </p>
                <div class="about-pillars">
                    <div class="about-pillar">
                        <span class="about-pillar__num">01</span>
                        <h4>100% Authentic Pro Gear</h4>
                        <p>Direct partnerships with Nike, Adidas, Puma, Asics, Under Armour, Yonex, and Salomon.</p>
                    </div>
                    <div class="about-pillar">
                        <span class="about-pillar__num">02</span>
                        <h4>Lab-Verified Specifications</h4>
                        <p>Complete transparency on midsole compounds, heel-to-toe drops, stud configs, and weights.</p>
                    </div>
                    <div class="about-pillar">
                        <span class="about-pillar__num">03</span>
                        <h4>Fast All-India Dispatch</h4>
                        <p>Free express delivery on orders above ₹4,999 with hassle-free size exchange support.</p>
                    </div>
                </div>
            </div>

            <div class="about-showcase__media">
                <img
                    src="https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=1000&q=85"
                    alt="SportKick Athlete Training"
                    loading="lazy"
                >
            </div>
        </div>
    </div>
</section>

<!-- 9. DEDICATED NEWSLETTER SECTION -->
<section class="newsletter-banner-section">
    <div class="container">
        <div class="newsletter-banner">
            <div class="newsletter-banner__copy">
                <span class="section-eyebrow">Join The SportKick Locker Club</span>
                <h2 class="newsletter-banner__title">GET EARLY ACCESS &amp; 10% OFF YOUR FIRST ORDER</h2>
                <p>Sign up for limited-edition colorway alerts, marathon training guides, and member-only locker room deals.</p>
            </div>
            <form class="newsletter-banner__form" id="homeNewsletterForm">
                <input
                    type="email"
                    placeholder="Enter your athlete email address..."
                    required
                    aria-label="Email address for newsletter"
                >
                <button type="submit" class="btn btn--primary btn--lg">Subscribe Now &rarr;</button>
            </form>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
