<?php
/**
 * SportKick - Global Header & Navigation Component
 */
require_once __DIR__ . '/functions.php';

$navCategories = getAllCategories($pdo);
$headerCart = getCartDetails($pdo);
$wishlistIds = getWishlistProductIds($pdo);
$currentUser = getCurrentUser($pdo);
$currentPage = basename($_SERVER['PHP_SELF']);
$pageTitle = isset($pageTitle) ? $pageTitle . ' | SportKick' : 'SportKick — Pro Sportswear & Performance Footwear';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="SportKick is the premier destination for high-performance sports shoes and technical sportswear across Running, Basketball, Football, Cricket, Tennis, Badminton, Gym & Hiking.">
    <!-- Google Fonts: Barlow Condensed (Athletic Display) & Plus Jakarta Sans (UI Body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,600;0,700;0,800;0,900;1,800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom Athletic Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Top Announcement Bar -->
    <div class="announcement-bar">
        <div class="container announcement-bar__inner">
            <div class="announcement-bar__left">
                <span class="pulse-dot"></span>
                <span>PRO SEASON DROP LIVE — FREE EXPRESS DELIVERY ON ORDERS OVER ₹4,999</span>
            </div>
            <div class="announcement-bar__right">
                <span>PROMO CODE: <strong>KICK10</strong> (10% OFF) &bull; <strong>ATHLETE20</strong> (20% OFF ₹10,000+)</span>
                <a href="shop.php?badge=deal" class="announcement-bar__cta">Shop Deals &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Main Sticky Navigation -->
    <header class="site-header" id="siteHeader">
        <div class="container site-header__main">
            <!-- Brand Logo -->
            <a href="index.php" class="brand-logo" aria-label="SportKick Home">
                <span class="brand-logo__mark">
                    <svg viewBox="0 0 36 36" width="30" height="30" fill="none">
                        <polygon points="4,28 18,6 23,6 13,22 32,16 14,30" fill="#FF5500"/>
                        <polygon points="8,18 28,8 22,19" fill="#FFFFFF"/>
                    </svg>
                </span>
                <span class="brand-logo__text">SPORT<span class="text-accent">KICK</span></span>
            </a>

            <!-- Primary Desktop Navigation -->
            <nav class="primary-nav" aria-label="Primary Navigation">
                <a href="index.php" class="primary-nav__link <?= $currentPage === 'index.php' ? 'is-active' : '' ?>">Home</a>
                <a href="shop.php" class="primary-nav__link <?= ($currentPage === 'shop.php' && empty($_GET['badge']) && empty($_GET['type'])) ? 'is-active' : '' ?>">All Gear</a>
                <a href="shop.php?type=Footwear" class="primary-nav__link <?= ($_GET['type'] ?? '') === 'Footwear' ? 'is-active' : '' ?>">Shoes</a>
                <a href="shop.php?type=Apparel" class="primary-nav__link <?= ($_GET['type'] ?? '') === 'Apparel' ? 'is-active' : '' ?>">Apparel</a>
                <a href="shop.php?badge=new" class="primary-nav__link <?= ($_GET['badge'] ?? '') === 'new' ? 'is-active' : '' ?>">New Arrivals</a>
                <a href="shop.php?badge=deal" class="primary-nav__link primary-nav__link--accent <?= ($_GET['badge'] ?? '') === 'deal' ? 'is-active' : '' ?>">Deals</a>
            </nav>

            <!-- Search Bar + User & Bag Icons -->
            <div class="header-utilities">
                <form action="shop.php" method="GET" class="header-search" role="search">
                    <input
                        type="search"
                        name="q"
                        value="<?= e($_GET['q'] ?? '') ?>"
                        placeholder="Search shoes, sports, Nike, Yonex..."
                        class="header-search__input"
                        aria-label="Search products"
                    >
                    <button type="submit" class="header-search__btn" aria-label="Submit search">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                    </button>
                </form>

                <!-- Wishlist Link -->
                <a href="wishlist.php" class="utility-btn <?= $currentPage === 'wishlist.php' ? 'is-active' : '' ?>" title="Saved Wishlist" aria-label="Wishlist">
                    <svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                    </svg>
                    <span class="utility-badge" id="headerWishlistCount"><?= count($wishlistIds) ?></span>
                </a>

                <!-- Shopping Bag Link -->
                <a href="cart.php" class="utility-btn utility-btn--cart <?= $currentPage === 'cart.php' ? 'is-active' : '' ?>" title="Shopping Cart" aria-label="Shopping Cart">
                    <svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                    <span class="utility-badge utility-badge--accent" id="headerCartCount"><?= (int)$headerCart['item_count'] ?></span>
                </a>

                <!-- Auth / Profile Menu -->
                <?php if ($currentUser): ?>
                    <div class="user-menu">
                        <a href="profile.php" class="utility-user-pill <?= $currentPage === 'profile.php' ? 'is-active' : '' ?>">
                            <span class="user-avatar"><?= e(strtoupper(substr($currentUser['full_name'], 0, 1))) ?></span>
                            <span class="user-first-name"><?= e(explode(' ', $currentUser['full_name'])[0]) ?></span>
                        </a>
                        <a href="logout.php" class="utility-logout" title="Sign Out">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn--sm btn--outline-light">Sign In</a>
                <?php endif; ?>

                <!-- Mobile Hamburger Toggle -->
                <button type="button" class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle Menu" aria-expanded="false">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>

        <!-- Dedicated 8 Sports Categories Sub-Bar -->
        <div class="sports-strip">
            <div class="container sports-strip__inner">
                <span class="sports-strip__label">SHOP BY SPORT:</span>
                <div class="sports-strip__links">
                    <?php foreach ($navCategories as $cat): ?>
                        <?php $isActiveCat = ($currentPage === 'shop.php' && ($_GET['category'] ?? '') === $cat['slug']); ?>
                        <a
                            href="shop.php?category=<?= e($cat['slug']) ?>"
                            class="sports-strip__item <?= $isActiveCat ? 'is-active' : '' ?>"
                        >
                            <?= e($cat['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer -->
        <div class="mobile-drawer" id="mobileDrawer">
            <form action="shop.php" method="GET" class="mobile-drawer__search">
                <input type="search" name="q" placeholder="Search shoes, brands, sports..." value="<?= e($_GET['q'] ?? '') ?>">
                <button type="submit">Search</button>
            </form>
            <div class="mobile-drawer__links">
                <a href="index.php">Home</a>
                <a href="shop.php">Shop All Products</a>
                <a href="shop.php?type=Footwear">Sports Shoes</a>
                <a href="shop.php?type=Apparel">Sportswear Apparel</a>
                <a href="shop.php?badge=new">New Arrivals</a>
                <a href="shop.php?badge=deal" class="text-accent">Exclusive Deals</a>
                <hr>
                <span class="mobile-drawer__subhead">SPORTS CATEGORIES</span>
                <?php foreach ($navCategories as $cat): ?>
                    <a href="shop.php?category=<?= e($cat['slug']) ?>"><?= e($cat['name']) ?></a>
                <?php endforeach; ?>
                <hr>
                <?php if ($currentUser): ?>
                    <a href="profile.php">My Athlete Profile (<?= e($currentUser['full_name']) ?>)</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Sign In</a>
                    <a href="register.php">Create Athlete Account</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Main Content Wrapper -->
    <main class="site-main">

