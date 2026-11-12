<?php
/**
 * SportKick - Core Helper & Data Access Functions
 * All database operations use PDO prepared statements.
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Format currency value cleanly in USD ($).
 */
function formatPrice(float $amount): string
{
    return '$' . number_format($amount, 2);
}

/**
 * Sanitize string output for HTML templates.
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Generate or retrieve CSRF token for forms and AJAX requests.
 */
function getCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token when supplied.
 */
function verifyCsrfToken(?string $token): bool
{
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Check if a user is currently authenticated.
 */
function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

/**
 * Get current logged-in user row from DB.
 */
function getCurrentUser(PDO $pdo): ?array
{
    if (!isLoggedIn()) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT id, full_name, email, phone, address, city, state, zip_code, created_at FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => (int)$_SESSION['user_id']]);
    $user = $stmt->fetch();
    return $user ?: null;
}

/**
 * Get or create active cart ID for current user or guest session.
 */
function getOrCreateCartId(PDO $pdo): int
{
    $userId = isLoggedIn() ? (int)$_SESSION['user_id'] : null;
    $sessionId = session_id();

    if ($userId !== null) {
        $stmt = $pdo->prepare('SELECT id FROM cart WHERE user_id = :user_id ORDER BY id DESC LIMIT 1');
        $stmt->execute([':user_id' => $userId]);
        $row = $stmt->fetch();
        if ($row) {
            return (int)$row['id'];
        }
        // Check if session already had a guest cart and attach it to user
        $stmtGuest = $pdo->prepare('SELECT id FROM cart WHERE session_id = :session_id AND user_id IS NULL ORDER BY id DESC LIMIT 1');
        $stmtGuest->execute([':session_id' => $sessionId]);
        $guestRow = $stmtGuest->fetch();
        if ($guestRow) {
            $update = $pdo->prepare('UPDATE cart SET user_id = :user_id WHERE id = :id');
            $update->execute([':user_id' => $userId, ':id' => (int)$guestRow['id']]);
            return (int)$guestRow['id'];
        }

        $insert = $pdo->prepare('INSERT INTO cart (user_id, session_id) VALUES (:user_id, :session_id)');
        $insert->execute([':user_id' => $userId, ':session_id' => $sessionId]);
        return (int)$pdo->lastInsertId();
    } else {
        $stmt = $pdo->prepare('SELECT id FROM cart WHERE session_id = :session_id AND user_id IS NULL ORDER BY id DESC LIMIT 1');
        $stmt->execute([':session_id' => $sessionId]);
        $row = $stmt->fetch();
        if ($row) {
            return (int)$row['id'];
        }
        $insert = $pdo->prepare('INSERT INTO cart (user_id, session_id) VALUES (NULL, :session_id)');
        $insert->execute([':session_id' => $sessionId]);
        return (int)$pdo->lastInsertId();
    }
}

/**
 * Merge guest cart and wishlist into user account upon login.
 */
function syncGuestDataOnLogin(PDO $pdo, int $userId, string $oldSessionId): void
{
    // Sync guest wishlist items
    $stmtWish = $pdo->prepare('SELECT product_id FROM wishlist WHERE session_id = :sid AND user_id IS NULL');
    $stmtWish->execute([':sid' => $oldSessionId]);
    $guestWishes = $stmtWish->fetchAll();

    foreach ($guestWishes as $gw) {
        $check = $pdo->prepare('SELECT id FROM wishlist WHERE user_id = :uid AND product_id = :pid LIMIT 1');
        $check->execute([':uid' => $userId, ':pid' => (int)$gw['product_id']]);
        if (!$check->fetch()) {
            $ins = $pdo->prepare('INSERT INTO wishlist (user_id, session_id, product_id) VALUES (:uid, :sid, :pid)');
            $ins->execute([':uid' => $userId, ':sid' => session_id(), ':pid' => (int)$gw['product_id']]);
        }
    }
    $delWish = $pdo->prepare('DELETE FROM wishlist WHERE session_id = :sid AND user_id IS NULL');
    $delWish->execute([':sid' => $oldSessionId]);

    // Sync guest cart items
    $stmtGuestCart = $pdo->prepare('SELECT id FROM cart WHERE session_id = :sid AND user_id IS NULL ORDER BY id DESC LIMIT 1');
    $stmtGuestCart->execute([':sid' => $oldSessionId]);
    $guestCart = $stmtGuestCart->fetch();

    if ($guestCart) {
        $stmtUserCart = $pdo->prepare('SELECT id FROM cart WHERE user_id = :uid ORDER BY id DESC LIMIT 1');
        $stmtUserCart->execute([':uid' => $userId]);
        $userCart = $stmtUserCart->fetch();

        if (!$userCart) {
            $upd = $pdo->prepare('UPDATE cart SET user_id = :uid, session_id = :new_sid WHERE id = :cid');
            $upd->execute([':uid' => $userId, ':new_sid' => session_id(), ':cid' => (int)$guestCart['id']]);
        } else {
            $userCartId = (int)$userCart['id'];
            $guestCartId = (int)$guestCart['id'];
            $itemsStmt = $pdo->prepare('SELECT product_id, size, color, quantity FROM cart_items WHERE cart_id = :gcid');
            $itemsStmt->execute([':gcid' => $guestCartId]);
            foreach ($itemsStmt->fetchAll() as $item) {
                $existStmt = $pdo->prepare('SELECT id, quantity FROM cart_items WHERE cart_id = :ucid AND product_id = :pid AND size = :sz AND color = :cl LIMIT 1');
                $existStmt->execute([
                    ':ucid' => $userCartId,
                    ':pid'  => (int)$item['product_id'],
                    ':sz'   => $item['size'],
                    ':cl'   => $item['color'],
                ]);
                $existing = $existStmt->fetch();
                if ($existing) {
                    $newQty = min(10, (int)$existing['quantity'] + (int)$item['quantity']);
                    $uStmt = $pdo->prepare('UPDATE cart_items SET quantity = :qty WHERE id = :id');
                    $uStmt->execute([':qty' => $newQty, ':id' => (int)$existing['id']]);
                } else {
                    $iStmt = $pdo->prepare('INSERT INTO cart_items (cart_id, product_id, size, color, quantity) VALUES (:ucid, :pid, :sz, :cl, :qty)');
                    $iStmt->execute([
                        ':ucid' => $userCartId,
                        ':pid'  => (int)$item['product_id'],
                        ':sz'   => $item['size'],
                        ':cl'   => $item['color'],
                        ':qty'  => (int)$item['quantity'],
                    ]);
                }
            }
            $delCart = $pdo->prepare('DELETE FROM cart WHERE id = :gcid');
            $delCart->execute([':gcid' => $guestCartId]);
        }
    }
}

/**
 * Get detailed cart items + totals for current user/session.
 */
function getCartDetails(PDO $pdo): array
{
    $cartId = getOrCreateCartId($pdo);
    $sql = '
        SELECT
            ci.id AS cart_item_id,
            ci.product_id,
            ci.size,
            ci.color,
            ci.quantity,
            p.name,
            p.slug,
            p.brand,
            p.sku,
            p.price,
            p.original_price,
            p.discount_percent,
            p.image_main,
            p.stock,
            c.name AS category_name,
            c.slug AS category_slug
        FROM cart_items ci
        INNER JOIN products p ON ci.product_id = p.id
        INNER JOIN categories c ON p.category_id = c.id
        WHERE ci.cart_id = :cart_id
        ORDER BY ci.created_at DESC
    ';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':cart_id' => $cartId]);
    $items = $stmt->fetchAll();

    $subtotal = 0.0;
    $originalTotal = 0.0;
    $itemCount = 0;

    foreach ($items as &$item) {
        $qty = (int)$item['quantity'];
        $price = (float)$item['price'];
        $orig = (float)$item['original_price'];
        $item['line_total'] = round($price * $qty, 2);
        $item['line_original'] = round($orig * $qty, 2);
        $subtotal += $item['line_total'];
        $originalTotal += $item['line_original'];
        $itemCount += $qty;
    }
    unset($item);

    $subtotal = round($subtotal, 2);
    $productSavings = round(max(0, $originalTotal - $subtotal), 2);

    // Promo code support via session
    $promoDiscount = 0.0;
    $promoCode = $_SESSION['promo_code'] ?? '';
    if ($promoCode === 'KICK10' && $subtotal > 0) {
        $promoDiscount = round($subtotal * 0.10, 2);
    } elseif ($promoCode === 'ATHLETE20' && $subtotal >= 150) {
        $promoDiscount = round($subtotal * 0.20, 2);
    }

    // Free shipping on orders over $120
    $shippingFee = ($subtotal === 0.0 || ($subtotal - $promoDiscount) >= 120.0) ? 0.00 : 9.99;
    $totalAmount = round(max(0, $subtotal - $promoDiscount) + $shippingFee, 2);

    return [
        'cart_id'         => $cartId,
        'items'           => $items,
        'item_count'      => $itemCount,
        'subtotal'        => $subtotal,
        'product_savings' => $productSavings,
        'promo_code'      => $promoCode,
        'promo_discount'  => $promoDiscount,
        'shipping_fee'    => $shippingFee,
        'total_amount'    => $totalAmount,
    ];
}

/**
 * Get wishlist product IDs for current user/session.
 */
function getWishlistProductIds(PDO $pdo): array
{
    if (isLoggedIn()) {
        $stmt = $pdo->prepare('SELECT product_id FROM wishlist WHERE user_id = :uid');
        $stmt->execute([':uid' => (int)$_SESSION['user_id']]);
    } else {
        $stmt = $pdo->prepare('SELECT product_id FROM wishlist WHERE session_id = :sid AND user_id IS NULL');
        $stmt->execute([':sid' => session_id()]);
    }
    return array_map('intval', array_column($stmt->fetchAll(), 'product_id'));
}

/**
 * Get full wishlist products for current user/session.
 */
function getWishlistItems(PDO $pdo): array
{
    if (isLoggedIn()) {
        $sql = '
            SELECT w.id AS wishlist_id, w.created_at AS added_at, p.*, c.name AS category_name, c.slug AS category_slug
            FROM wishlist w
            INNER JOIN products p ON w.product_id = p.id
            INNER JOIN categories c ON p.category_id = c.id
            WHERE w.user_id = :uid
            ORDER BY w.created_at DESC
        ';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':uid' => (int)$_SESSION['user_id']]);
    } else {
        $sql = '
            SELECT w.id AS wishlist_id, w.created_at AS added_at, p.*, c.name AS category_name, c.slug AS category_slug
            FROM wishlist w
            INNER JOIN products p ON w.product_id = p.id
            INNER JOIN categories c ON p.category_id = c.id
            WHERE w.session_id = :sid AND w.user_id IS NULL
            ORDER BY w.created_at DESC
        ';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':sid' => session_id()]);
    }
    return $stmt->fetchAll();
}

/**
 * Fetch all sports categories with product counts.
 */
function getAllCategories(PDO $pdo): array
{
    $sql = '
        SELECT c.*, COUNT(p.id) AS product_count
        FROM categories c
        LEFT JOIN products p ON p.category_id = c.id
        GROUP BY c.id
        ORDER BY c.display_order ASC
    ';
    return $pdo->query($sql)->fetchAll();
}

/**
 * Query products with rich multi-parameter search and filtering.
 */
function queryProducts(PDO $pdo, array $filters = []): array
{
    $where = ['1=1'];
    $params = [];

    // Search query (matches product name, brand, description, category name, or SKU)
    if (!empty($filters['q'])) {
        $q = trim($filters['q']);
        $where[] = '(p.name LIKE :q1 OR p.brand LIKE :q2 OR p.description LIKE :q3 OR c.name LIKE :q4 OR p.sku LIKE :q5)';
        $like = '%' . $q . '%';
        $params[':q1'] = $like;
        $params[':q2'] = $like;
        $params[':q3'] = $like;
        $params[':q4'] = $like;
        $params[':q5'] = $like;
    }

    // Category filter (by slug or ID)
    if (!empty($filters['category'])) {
        if (is_numeric($filters['category'])) {
            $where[] = 'p.category_id = :cat_id';
            $params[':cat_id'] = (int)$filters['category'];
        } else {
            $where[] = 'c.slug = :cat_slug';
            $params[':cat_slug'] = trim($filters['category']);
        }
    }

    // Brand filter (single brand or comma-separated list)
    if (!empty($filters['brand'])) {
        $brands = is_array($filters['brand'])
            ? $filters['brand']
            : array_filter(array_map('trim', explode(',', (string)$filters['brand'])));
        if (!empty($brands)) {
            $brandPlaceholders = [];
            foreach (array_values($brands) as $idx => $b) {
                $key = ':brand_' . $idx;
                $brandPlaceholders[] = $key;
                $params[$key] = $b;
            }
            $where[] = 'p.brand IN (' . implode(', ', $brandPlaceholders) . ')';
        }
    }

    // Price range filter
    if (isset($filters['min_price']) && $filters['min_price'] !== '') {
        $where[] = 'p.price >= :min_price';
        $params[':min_price'] = (float)$filters['min_price'];
    }
    if (isset($filters['max_price']) && $filters['max_price'] !== '') {
        $where[] = 'p.price <= :max_price';
        $params[':max_price'] = (float)$filters['max_price'];
    }

    // Size filter (substring match against CSV sizes column)
    if (!empty($filters['size'])) {
        $where[] = 'FIND_IN_SET(:size_exact, p.sizes) > 0';
        $params[':size_exact'] = trim($filters['size']);
    }

    // Color filter
    if (!empty($filters['color'])) {
        $where[] = '(p.color_primary = :color_primary OR FIND_IN_SET(:color_set, p.colors) > 0)';
        $params[':color_primary'] = trim($filters['color']);
        $params[':color_set'] = trim($filters['color']);
    }

    // Product type filter (Footwear / Apparel)
    if (!empty($filters['type'])) {
        $where[] = 'p.product_type = :ptype';
        $params[':ptype'] = trim($filters['type']);
    }

    // Special badges (trending, new_arrival, deal)
    if (!empty($filters['badge'])) {
        if ($filters['badge'] === 'trending') {
            $where[] = 'p.is_trending = 1';
        } elseif ($filters['badge'] === 'new') {
            $where[] = 'p.is_new_arrival = 1';
        } elseif ($filters['badge'] === 'deal') {
            $where[] = 'p.is_deal = 1';
        }
    }

    // Sorting
    $sortSql = 'p.is_trending DESC, p.rating DESC, p.id ASC';
    $sort = $filters['sort'] ?? 'featured';
    switch ($sort) {
        case 'price_asc':
            $sortSql = 'p.price ASC';
            break;
        case 'price_desc':
            $sortSql = 'p.price DESC';
            break;
        case 'rating':
            $sortSql = 'p.rating DESC, p.reviews_count DESC';
            break;
        case 'newest':
            $sortSql = 'p.is_new_arrival DESC, p.id DESC';
            break;
        case 'discount':
            $sortSql = 'p.discount_percent DESC, p.price ASC';
            break;
    }

    $limitSql = '';
    if (!empty($filters['limit'])) {
        $limitSql = ' LIMIT ' . max(1, (int)$filters['limit']);
    }

    $sql = sprintf(
        'SELECT p.*, c.name AS category_name, c.slug AS category_slug
         FROM products p
         INNER JOIN categories c ON p.category_id = c.id
         WHERE %s
         ORDER BY %s%s',
        implode(' AND ', $where),
        $sortSql,
        $limitSql
    );

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
