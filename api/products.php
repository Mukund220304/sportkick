<?php
/**
 * SportKick - Products & Quick View JSON API
 * Endpoints:
 *   GET /api/products.php?action=detail&id={id}
 *   GET /api/products.php?action=filter&q=...&category=...&brand=...&min_price=...&max_price=...&size=...&color=...&sort=...
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

$action = $_GET['action'] ?? 'filter';
$wishlistIds = getWishlistProductIds($pdo);

if ($action === 'detail') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        exit;
    }

    $stmt = $pdo->prepare('
        SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM products p
        INNER JOIN categories c ON p.category_id = c.id
        WHERE p.id = :id
        LIMIT 1
    ');
    $stmt->execute([':id' => $id]);
    $product = $stmt->fetch();

    if (!$product) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }

    $product['sizes_array'] = array_values(array_filter(array_map('trim', explode(',', $product['sizes']))));
    $product['colors_array'] = array_values(array_filter(array_map('trim', explode(',', $product['colors']))));
    $product['gallery_array'] = json_decode($product['image_gallery'], true) ?: [$product['image_main']];
    $product['specs_object'] = json_decode($product['specifications'], true) ?: [];
    $product['in_wishlist'] = in_array((int)$product['id'], $wishlistIds, true);
    $product['price_formatted'] = formatPrice((float)$product['price']);
    $product['original_price_formatted'] = formatPrice((float)$product['original_price']);

    echo json_encode([
        'success' => true,
        'product' => $product,
    ]);
    exit;
}

// Default: filter / search products
$filters = [
    'q'         => $_GET['q'] ?? '',
    'category'  => $_GET['category'] ?? '',
    'brand'     => $_GET['brand'] ?? '',
    'min_price' => $_GET['min_price'] ?? '',
    'max_price' => $_GET['max_price'] ?? '',
    'size'      => $_GET['size'] ?? '',
    'color'     => $_GET['color'] ?? '',
    'type'      => $_GET['type'] ?? '',
    'badge'     => $_GET['badge'] ?? '',
    'sort'      => $_GET['sort'] ?? 'featured',
];

$products = queryProducts($pdo, $filters);
foreach ($products as &$p) {
    $p['sizes_array'] = array_values(array_filter(array_map('trim', explode(',', $p['sizes']))));
    $p['colors_array'] = array_values(array_filter(array_map('trim', explode(',', $p['colors']))));
    $p['in_wishlist'] = in_array((int)$p['id'], $wishlistIds, true);
    $p['price_formatted'] = formatPrice((float)$p['price']);
    $p['original_price_formatted'] = formatPrice((float)$p['original_price']);
}
unset($p);

echo json_encode([
    'success'  => true,
    'count'    => count($products),
    'products' => $products,
]);
