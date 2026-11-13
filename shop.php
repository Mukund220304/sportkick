<?php
/**
 * SportKick - Product Listing, Categories, Search & Multi-Filter Page (shop.php)
 * Supports filtering by: Product Name/Keyword, Sport Category, Brand, Price Range, Size, Color, Type, and Sort.
 */

require_once __DIR__ . '/includes/functions.php';

$filters = [
    'q'         => trim((string)($_GET['q'] ?? '')),
    'category'  => trim((string)($_GET['category'] ?? '')),
    'brand'     => $_GET['brand'] ?? '',
    'min_price' => isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : '',
    'max_price' => isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : '',
    'size'      => trim((string)($_GET['size'] ?? '')),
    'color'     => trim((string)($_GET['color'] ?? '')),
    'type'      => trim((string)($_GET['type'] ?? '')),
    'badge'     => trim((string)($_GET['badge'] ?? '')),
    'sort'      => trim((string)($_GET['sort'] ?? 'featured')),
];

$allCategories = getAllCategories($pdo);
$activeCategoryObj = null;
foreach ($allCategories as $c) {
    if ($c['slug'] === $filters['category'] || (string)$c['id'] === $filters['category']) {
        $activeCategoryObj = $c;
        break;
    }
}

$products = queryProducts($pdo, $filters);

// Fetch distinct brands & colors from DB for filter controls
$brandsList = $pdo->query('SELECT DISTINCT brand FROM products ORDER BY brand ASC')->fetchAll(PDO::FETCH_COLUMN);
$colorsList = ['Black', 'White', 'Orange', 'Red', 'Blue', 'Green', 'Grey'];
$sizesList = ['UK 6', 'UK 7', 'UK 8', 'UK 9', 'UK 10', 'UK 11', 'UK 12', 'S', 'M', 'L', 'XL'];

$heroHeading = 'All Pro Sportswear & Footwear';
$heroSubtitle = 'Explore championship-grade athletic shoes and technical training apparel across 8 sports.';

if ($activeCategoryObj) {
    $heroHeading = $activeCategoryObj['name'] . ' Gear & Footwear';
    $heroSubtitle = $activeCategoryObj['description'];
} elseif ($filters['q'] !== '') {
    $heroHeading = 'Search Results: "' . $filters['q'] . '"';
} elseif ($filters['badge'] === 'deal') {
    $heroHeading = 'Exclusive Locker Room Deals';
} elseif ($filters['badge'] === 'new') {
    $heroHeading = 'New Season Arrivals';
} elseif ($filters['badge'] === 'trending') {
    $heroHeading = 'Trending Pro Picks';
} elseif ($filters['brand'] !== '') {
    $heroHeading = (is_string($filters['brand']) ? $filters['brand'] : 'Brand') . ' Official Collection';
}

$pageTitle = $heroHeading;
require_once __DIR__ . '/includes/header.php';
?>

<!-- Compact Category / Shop Hero -->
<section class="page-hero-compact">
    <div class="container">
        <span class="section-eyebrow">SportKick Catalog</span>
        <h1 class="page-hero-compact__title"><?= e($heroHeading) ?></h1>
        <p class="page-hero-compact__sub"><?= e($heroSubtitle) ?></p>
    </div>
</section>

<div class="container">
    <div class="shop-layout">
        <!-- LEFT SIDEBAR: SEARCH & FILTERS -->
        <aside class="filter-sidebar" aria-label="Product Filters">
            <form action="shop.php" method="GET" id="shopFilterForm">
                <div class="filter-sidebar__header">
                    <h2 class="filter-sidebar__title">Filter Gear</h2>
                    <a href="shop.php" class="filter-reset-link">Reset All</a>
                </div>

                <!-- 1. Search by Product Name / Keyword -->
                <div class="filter-group">
                    <label for="filterQ" class="filter-group__label">Keyword / Product Name</label>
                    <input
                        type="search"
                        id="filterQ"
                        name="q"
                        value="<?= e($filters['q']) ?>"
                        placeholder="e.g. Alphafly, Metcon, Spikes..."
                        class="filter-input"
                    >
                </div>

                <!-- 2. Sport Category -->
                <div class="filter-group">
                    <label for="filterCategory" class="filter-group__label">Sport Category</label>
                    <select name="category" id="filterCategory" class="filter-select" onchange="this.form.submit()">
                        <option value="">All 8 Sports Categories</option>
                        <?php foreach ($allCategories as $cat): ?>
                            <option value="<?= e($cat['slug']) ?>" <?= $filters['category'] === $cat['slug'] ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?> (<?= (int)$cat['product_count'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- 3. Brand -->
                <div class="filter-group">
                    <label for="filterBrand" class="filter-group__label">Brand</label>
                    <select name="brand" id="filterBrand" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Brands</option>
                        <?php foreach ($brandsList as $bName): ?>
                            <option value="<?= e($bName) ?>" <?= $filters['brand'] === $bName ? 'selected' : '' ?>>
                                <?= e($bName) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- 4. Price Range (₹ Min - ₹ Max) -->
                <div class="filter-group">
                    <span class="filter-group__label">Price Range (₹)</span>
                    <div class="filter-price-row">
                        <input
                            type="number"
                            name="min_price"
                            value="<?= e((string)$filters['min_price']) ?>"
                            placeholder="Min (₹0)"
                            min="0"
                            max="30000"
                            step="500"
                            class="filter-input"
                            aria-label="Minimum Price"
                        >
                        <input
                            type="number"
                            name="max_price"
                            value="<?= e((string)$filters['max_price']) ?>"
                            placeholder="Max (₹25,000)"
                            min="0"
                            max="30000"
                            step="500"
                            class="filter-input"
                            aria-label="Maximum Price"
                        >
                    </div>
                </div>

                <!-- 5. Size Filter -->
                <div class="filter-group">
                    <span class="filter-group__label">Size</span>
                    <div class="filter-pills-wrap">
                        <label class="filter-radio-pill">
                            <input type="radio" name="size" value="" <?= $filters['size'] === '' ? 'checked' : '' ?> onchange="this.form.submit()">
                            <span>All</span>
                        </label>
                        <?php foreach ($sizesList as $sz): ?>
                            <label class="filter-radio-pill">
                                <input type="radio" name="size" value="<?= e($sz) ?>" <?= $filters['size'] === $sz ? 'checked' : '' ?> onchange="this.form.submit()">
                                <span><?= e($sz) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 6. Color Filter -->
                <div class="filter-group">
                    <span class="filter-group__label">Colorway</span>
                    <div class="filter-pills-wrap">
                        <label class="filter-radio-pill">
                            <input type="radio" name="color" value="" <?= $filters['color'] === '' ? 'checked' : '' ?> onchange="this.form.submit()">
                            <span>Any</span>
                        </label>
                        <?php foreach ($colorsList as $clr): ?>
                            <label class="filter-radio-pill">
                                <input type="radio" name="color" value="<?= e($clr) ?>" <?= $filters['color'] === $clr ? 'checked' : '' ?> onchange="this.form.submit()">
                                <span><?= e($clr) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 7. Product Type -->
                <div class="filter-group">
                    <label for="filterType" class="filter-group__label">Equipment Type</label>
                    <select name="type" id="filterType" class="filter-select" onchange="this.form.submit()">
                        <option value="">Shoes &amp; Apparel</option>
                        <option value="Footwear" <?= $filters['type'] === 'Footwear' ? 'selected' : '' ?>>Sports Shoes / Footwear</option>
                        <option value="Apparel" <?= $filters['type'] === 'Apparel' ? 'selected' : '' ?>>Sportswear Apparel</option>
                    </select>
                </div>

                <!-- Preserve Sort & Badge if active -->
                <input type="hidden" name="sort" id="hiddenSortInput" value="<?= e($filters['sort']) ?>">
                <?php if ($filters['badge'] !== ''): ?>
                    <input type="hidden" name="badge" value="<?= e($filters['badge']) ?>">
                <?php endif; ?>

                <button type="submit" class="btn btn--primary btn--full">Apply Filters</button>
            </form>
        </aside>

        <!-- RIGHT MAIN CONTENT: TOOLBAR + PRODUCT LISTING GRID -->
        <section aria-label="Product Results">
            <div class="shop-toolbar">
                <div class="shop-toolbar__count">
                    Showing <strong><?= count($products) ?></strong> performance product<?= count($products) === 1 ? '' : 's' ?>
                    <?php if ($activeCategoryObj): ?>
                        in <strong><?= e($activeCategoryObj['name']) ?></strong>
                    <?php endif; ?>
                </div>

                <div class="shop-toolbar__sort">
                    <label for="sortSelect">Sort By:</label>
                    <select
                        id="sortSelect"
                        class="filter-select"
                        style="width:auto;min-width:190px;"
                        onchange="document.getElementById('hiddenSortInput').value=this.value; document.getElementById('shopFilterForm').submit();"
                    >
                        <option value="featured" <?= $filters['sort'] === 'featured' ? 'selected' : '' ?>>Featured &amp; Trending</option>
                        <option value="price_asc" <?= $filters['sort'] === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_desc" <?= $filters['sort'] === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="rating" <?= $filters['sort'] === 'rating' ? 'selected' : '' ?>>Highest Athlete Rating</option>
                        <option value="discount" <?= $filters['sort'] === 'discount' ? 'selected' : '' ?>>Biggest Discount</option>
                        <option value="newest" <?= $filters['sort'] === 'newest' ? 'selected' : '' ?>>Newest Arrivals</option>
                    </select>
                </div>
            </div>

            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <h3 class="empty-state__title">No Matching Gear Found</h3>
                    <p class="empty-state__text">
                        We couldn't find any products matching your exact filter combination. Try broadening your price range or clearing active filters.
                    </p>
                    <a href="shop.php" class="btn btn--primary">Reset All Filters</a>
                </div>
            <?php else: ?>
                <div class="shop-grid">
                    <?php foreach ($products as $product): ?>
                        <?php include __DIR__ . '/includes/product_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

