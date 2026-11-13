<?php
/**
 * SportKick - Global Flagship Header & Responsive Navigation
 * Navigation items: SportKick Logo | Home | Shop | Categories (Dropdown) | New Arrivals | About Us
 * Icons: Search Icon | Wishlist Icon | Shopping Cart Icon | Login/Profile
 */
require_once __DIR__ . '/functions.php';

$navCategories = getAllCategories($pdo);
$headerCart = getCartDetails($pdo);
$wishlistIds = getWishlistProductIds($pdo);
$currentUser = getCurrentUser($pdo);
$currentPage = basename($_SERVER['PHP_SELF']);
$pageTitle = isset($pageTitle) ? $pageTitle . ' | SportKick' : 'SportKick — Push Beyond Limits';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="SportKick — Push Beyond Limits. Performance starts with what you wear. Shop authentic sports shoes and technical sportswear across 8 athletic disciplines.">
    <!-- Google Fonts: Barlow Condensed (Athletic Display) & Plus Jakarta Sans (UI Body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,600;0,700;0,800;0,900;1,700;1,800;1,900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom Athletic Design System -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Top Announcement Ticker -->
    <div class="announcement-bar">
        <div class="container announcement-bar__inner">
            <div class="announcement-bar__left">
                <span class="pulse-dot"></span>
                <span>FREE EXPRESS DELIVERY ON ORDERS OVER ₹4,999 &bull; 30-DAY PERFORMANCE TRIAL</span>
            </div>
            <div class="announcement-bar__right">
                <span>USE CODE <strong>KICK10</strong> (10% OFF) &bull; <strong>ATHLETE20</strong> (20% OFF ₹10,000+)</span>
                <a href="shop.php?badge=deal" class="announcement-bar__cta">Shop Campaign &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Main Sticky Navbar -->
    <header class="site-header" id="siteHeader">
        <div class="container site-header__main">
            <!-- 1. SportKick Logo -->
            <a href="index.php" class="brand-logo" aria-label="SportKick Home">
                <span class="brand-logo__mark">
                    <svg viewBox="0 0 36 36" width="32" height="32" fill="none">
                        <polygon points="4,28 18,6 23,6 13,22 32,16 14,30" fill="#FF5500"/>
                        <polygon points="8,18 28,8 22,19" fill="#FFFFFF"/>
                    </svg>
                </span>
                <span class="brand-logo__text">SPORT<span class="text-accent">KICK</span></span>
            </a>

            <!-- 2. Primary Navigation Links -->
            <nav class="primary-nav" aria-label="Primary Navigation">
                <a href="index.php" class="primary-nav__link <?= $currentPage === 'index.php' ? 'is-active' : '' ?>">Home</a>
                <a href="shop.php" class="primary-nav__link <?= ($currentPage === 'shop.php' && empty($_GET['badge']) && empty($_GET['category'])) ? 'is-active' : '' ?>">Shop</a>

                <!-- Categories Dropdown + Link -->
                <div class="nav-dropdown">
                    <a href="index.php#shopBySport" class="primary-nav__link nav-dropdown__trigger <?= !empty($_GET['category']) ? 'is-active' : '' ?>">
                        <span>Categories</span>
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </a>
                    <div class="nav-dropdown__menu" aria-label="Sports Categories Submenu">
                        <div class="nav-dropdown__grid">
                            <?php foreach ($navCategories as $cat): ?>
                                <a href="shop.php?category=<?= e($cat['slug']) ?>" class="nav-dropdown__item">
                                    <span class="nav-dropdown__name"><?= e($cat['name']) ?></span>
                                    <span class="nav-dropdown__count"><?= (int)$cat['product_count'] ?> Items &rarr;</span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <a href="shop.php?badge=new" class="primary-nav__link <?= ($_GET['badge'] ?? '') === 'new' ? 'is-active' : '' ?>">New Arrivals</a>
                <a href="index.php#aboutSection" class="primary-nav__link">About Us</a>
            </nav>

            <!-- 3. Utility Icons: Search, Wishlist, Cart, Login/Profile -->
            <div class="header-utilities">
                <!-- Inline Desktop Search + Search Icon Button -->
                <form action="shop.php" method="GET" class="header-search" id="headerSearchForm" role="search">
                    <input
                        type="search"
                        name="q"
                        id="headerSearchInput"
                        value="<?= e($_GET['q'] ?? '') ?>"
                        placeholder="Search shoes, sports, brands..."
                        class="header-search__input"
                        aria-label="Search products"
                    >
                    <button type="submit" class="header-search__btn" aria-label="Search">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                    </button>
                </form>

                <!-- Search Icon Trigger (for tablet/mobile or quick focus) -->
                <button type="button" class="utility-btn utility-btn--search-toggle" id="searchToggleBtn" title="Search Gear" aria-label="Toggle Search">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.1">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </button>

                <!-- Wishlist Icon -->
                <a href="wishlist.php" class="utility-btn <?= $currentPage === 'wishlist.php' ? 'is-active' : '' ?>" title="Saved Wishlist" aria-label="Wishlist">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.1">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                    </svg>
                    <span class="utility-badge" id="headerWishlistCount"><?= count($wishlistIds) ?></span>
                </a>

                <!-- Shopping Cart Icon -->
                <a href="cart.php" class="utility-btn utility-btn--cart <?= $currentPage === 'cart.php' ? 'is-active' : '' ?>" title="Shopping Cart" aria-label="Shopping Cart">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.1">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                    <span class="utility-badge utility-badge--accent" id="headerCartCount"><?= (int)$headerCart['item_count'] ?></span>
                </a>

                <!-- Login / Profile -->
                <?php if ($currentUser): ?>
                    <div class="user-menu">
                        <a href="profile.php" class="utility-user-pill <?= $currentPage === 'profile.php' ? 'is-active' : '' ?>" title="Athlete Profile">
                            <span class="user-avatar"><?= e(strtoupper(substr($currentUser['full_name'], 0, 1))) ?></span>
                            <span class="user-first-name"><?= e(explode(' ', $currentUser['full_name'])[0]) ?></span>
                        </a>
                        <a href="logout.php" class="utility-logout" title="Sign Out">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="utility-login-btn <?= in_array($currentPage, ['login.php', 'register.php'], true) ? 'is-active' : '' ?>" title="Sign In / Register">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.1">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        <span>Login</span>
                    </a>
                <?php endif; ?>

                <!-- Mobile Hamburger Toggle -->
                <button type="button" class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle Menu" aria-expanded="false">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>

        <!-- Dedicated 8 Sports Categories Quick-Access Ribbon -->
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

        <!-- Mobile Navigation Drawer -->
        <div class="mobile-drawer" id="mobileDrawer">
            <form action="shop.php" method="GET" class="mobile-drawer__search">
                <input type="search" name="q" placeholder="Search shoes, brands, sports..." value="<?= e($_GET['q'] ?? '') ?>">
                <button type="submit">Search</button>
            </form>
            <div class="mobile-drawer__links">
                <a href="index.php">Home</a>
                <a href="shop.php">Shop All Collection</a>
                <a href="index.php#shopBySport">Categories</a>
                <a href="shop.php?badge=new">New Arrivals</a>
                <a href="index.php#aboutSection">About Us</a>
                <a href="shop.php?badge=deal" class="text-accent">Exclusive Deals</a>
                <hr>
                <span class="mobile-drawer__subhead">SHOP BY SPORT</span>
                <?php foreach ($navCategories as $cat): ?>
                    <a href="shop.php?category=<?= e($cat['slug']) ?>"><?= e($cat['name']) ?></a>
                <?php endforeach; ?>
                <hr>
                <?php if ($currentUser): ?>
                    <a href="profile.php">Athlete Profile (<?= e($currentUser['full_name']) ?>)</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login / Sign In</a>
                    <a href="register.php">Create Athlete Account</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Main Content Wrapper -->
    <main class="site-main">
