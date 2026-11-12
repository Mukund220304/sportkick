<?php
/**
 * SportKick - Wishlist JSON API
 * Supports: toggle, remove, move_to_cart, list
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $ids = getWishlistProductIds($pdo);
    echo json_encode([
        'success'      => true,
        'count'        => count($ids),
        'wishlist_ids' => $ids,
    ]);
    exit;
}

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);
if (!is_array($input)) {
    $input = $_POST;
}

$action = $input['action'] ?? 'toggle';
$productId = isset($input['product_id']) ? (int)$input['product_id'] : 0;

if ($productId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid product ID.']);
    exit;
}

// Verify product exists
$stmtProd = $pdo->prepare('SELECT id, name, sizes, colors, color_primary FROM products WHERE id = :id LIMIT 1');
$stmtProd->execute([':id' => $productId]);
$product = $stmtProd->fetch();

if (!$product) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Product not found.']);
    exit;
}

$userId = isLoggedIn() ? (int)$_SESSION['user_id'] : null;
$sessionId = session_id();

if ($action === 'toggle') {
    if ($userId !== null) {
        $check = $pdo->prepare('SELECT id FROM wishlist WHERE user_id = :uid AND product_id = :pid LIMIT 1');
        $check->execute([':uid' => $userId, ':pid' => $productId]);
    } else {
        $check = $pdo->prepare('SELECT id FROM wishlist WHERE session_id = :sid AND user_id IS NULL AND product_id = :pid LIMIT 1');
        $check->execute([':sid' => $sessionId, ':pid' => $productId]);
    }

    $existing = $check->fetch();
    if ($existing) {
        $del = $pdo->prepare('DELETE FROM wishlist WHERE id = :id');
        $del->execute([':id' => (int)$existing['id']]);
        $status = 'removed';
        $message = sprintf('%s removed from your wishlist.', $product['name']);
    } else {
        $ins = $pdo->prepare('INSERT INTO wishlist (user_id, session_id, product_id) VALUES (:uid, :sid, :pid)');
        $ins->execute([
            ':uid' => $userId,
            ':sid' => $sessionId,
            ':pid' => $productId,
        ]);
        $status = 'added';
        $message = sprintf('%s saved to your wishlist.', $product['name']);
    }

    $ids = getWishlistProductIds($pdo);
    echo json_encode([
        'success'      => true,
        'status'       => $status,
        'message'      => $message,
        'count'        => count($ids),
        'wishlist_ids' => $ids,
    ]);
    exit;
}

if ($action === 'remove') {
    if ($userId !== null) {
        $del = $pdo->prepare('DELETE FROM wishlist WHERE user_id = :uid AND product_id = :pid');
        $del->execute([':uid' => $userId, ':pid' => $productId]);
    } else {
        $del = $pdo->prepare('DELETE FROM wishlist WHERE session_id = :sid AND user_id IS NULL AND product_id = :pid');
        $del->execute([':sid' => $sessionId, ':pid' => $productId]);
    }

    $ids = getWishlistProductIds($pdo);
    echo json_encode([
        'success'      => true,
        'status'       => 'removed',
        'message'      => sprintf('%s removed from wishlist.', $product['name']),
        'count'        => count($ids),
        'wishlist_ids' => $ids,
    ]);
    exit;
}

if ($action === 'move_to_cart') {
    $sizes = array_values(array_filter(array_map('trim', explode(',', $product['sizes']))));
    $size = trim((string)($input['size'] ?? ($sizes[0] ?? 'UK 9')));
    $color = trim((string)($input['color'] ?? ($product['color_primary'] ?: 'Black')));
    $cartId = getOrCreateCartId($pdo);

    $stmtExist = $pdo->prepare('
        SELECT id, quantity FROM cart_items
        WHERE cart_id = :cid AND product_id = :pid AND size = :sz AND color = :cl LIMIT 1
    ');
    $stmtExist->execute([':cid' => $cartId, ':pid' => $productId, ':sz' => $size, ':cl' => $color]);
    $existing = $stmtExist->fetch();

    if ($existing) {
        $upd = $pdo->prepare('UPDATE cart_items SET quantity = :qty WHERE id = :id');
        $upd->execute([':qty' => min(10, (int)$existing['quantity'] + 1), ':id' => (int)$existing['id']]);
    } else {
        $ins = $pdo->prepare('INSERT INTO cart_items (cart_id, product_id, size, color, quantity) VALUES (:cid, :pid, :sz, :cl, 1)');
        $ins->execute([':cid' => $cartId, ':pid' => $productId, ':sz' => $size, ':cl' => $color]);
    }

    // Remove from wishlist
    if ($userId !== null) {
        $del = $pdo->prepare('DELETE FROM wishlist WHERE user_id = :uid AND product_id = :pid');
        $del->execute([':uid' => $userId, ':pid' => $productId]);
    } else {
        $del = $pdo->prepare('DELETE FROM wishlist WHERE session_id = :sid AND user_id IS NULL AND product_id = :pid');
        $del->execute([':sid' => $sessionId, ':pid' => $productId]);
    }

    $ids = getWishlistProductIds($pdo);
    $cart = getCartDetails($pdo);
    echo json_encode([
        'success'      => true,
        'message'      => sprintf('%s (%s) moved to your bag.', $product['name'], $size),
        'count'        => count($ids),
        'wishlist_ids' => $ids,
        'cart'         => $cart,
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Unsupported wishlist action.']);
