<?php
/**
 * SportKick - Shopping Cart JSON & Form API
 * Supports: add, update, remove, clear, promo, get
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $cart = getCartDetails($pdo);
    echo json_encode(['success' => true, 'cart' => $cart]);
    exit;
}

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);
if (!is_array($input)) {
    $input = $_POST;
}

$action = $input['action'] ?? 'add';
$cartId = getOrCreateCartId($pdo);

if ($action === 'add') {
    $productId = isset($input['product_id']) ? (int)$input['product_id'] : 0;
    $quantity = isset($input['quantity']) ? max(1, min(10, (int)$input['quantity'])) : 1;
    $size = trim((string)($input['size'] ?? ''));
    $color = trim((string)($input['color'] ?? ''));

    if ($productId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Please select a valid product.']);
        exit;
    }

    $stmtProd = $pdo->prepare('SELECT id, name, sizes, colors, color_primary, stock FROM products WHERE id = :id LIMIT 1');
    $stmtProd->execute([':id' => $productId]);
    $product = $stmtProd->fetch();

    if (!$product) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product not found.']);
        exit;
    }

    if ((int)$product['stock'] < 1) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Sorry, this product is currently out of stock.']);
        exit;
    }

    $availableSizes = array_values(array_filter(array_map('trim', explode(',', $product['sizes']))));
    if ($size === '' || !in_array($size, $availableSizes, true)) {
        $size = $availableSizes[0] ?? 'UK 9';
    }

    $availableColors = array_values(array_filter(array_map('trim', explode(',', $product['colors']))));
    if ($color === '' || !in_array($color, $availableColors, true)) {
        $color = $product['color_primary'] ?: ($availableColors[0] ?? 'Black');
    }

    // Check if same product + size + color exists in cart_items
    $stmtExist = $pdo->prepare('
        SELECT id, quantity
        FROM cart_items
        WHERE cart_id = :cart_id AND product_id = :product_id AND size = :size AND color = :color
        LIMIT 1
    ');
    $stmtExist->execute([
        ':cart_id'    => $cartId,
        ':product_id' => $productId,
        ':size'       => $size,
        ':color'      => $color,
    ]);
    $existing = $stmtExist->fetch();

    if ($existing) {
        $newQty = min((int)$product['stock'], min(10, (int)$existing['quantity'] + $quantity));
        $upd = $pdo->prepare('UPDATE cart_items SET quantity = :qty WHERE id = :id');
        $upd->execute([':qty' => $newQty, ':id' => (int)$existing['id']]);
    } else {
        $ins = $pdo->prepare('
            INSERT INTO cart_items (cart_id, product_id, size, color, quantity)
            VALUES (:cart_id, :product_id, :size, :color, :quantity)
        ');
        $ins->execute([
            ':cart_id'    => $cartId,
            ':product_id' => $productId,
            ':size'       => $size,
            ':color'      => $color,
            ':quantity'   => min((int)$product['stock'], $quantity),
        ]);
    }

    $cart = getCartDetails($pdo);
    echo json_encode([
        'success' => true,
        'message' => sprintf('%s (%s) added to your bag.', $product['name'], $size),
        'cart'    => $cart,
    ]);
    exit;
}

if ($action === 'update') {
    $cartItemId = isset($input['cart_item_id']) ? (int)$input['cart_item_id'] : 0;
    $quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;

    if ($cartItemId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid cart item.']);
        exit;
    }

    if ($quantity <= 0) {
        $del = $pdo->prepare('DELETE FROM cart_items WHERE id = :id AND cart_id = :cart_id');
        $del->execute([':id' => $cartItemId, ':cart_id' => $cartId]);
        $msg = 'Item removed from cart.';
    } else {
        $quantity = min(10, $quantity);
        $upd = $pdo->prepare('UPDATE cart_items SET quantity = :qty WHERE id = :id AND cart_id = :cart_id');
        $upd->execute([':qty' => $quantity, ':id' => $cartItemId, ':cart_id' => $cartId]);
        $msg = 'Cart quantity updated.';
    }

    $cart = getCartDetails($pdo);
    echo json_encode([
        'success' => true,
        'message' => $msg,
        'cart'    => $cart,
    ]);
    exit;
}

if ($action === 'remove') {
    $cartItemId = isset($input['cart_item_id']) ? (int)$input['cart_item_id'] : 0;
    $del = $pdo->prepare('DELETE FROM cart_items WHERE id = :id AND cart_id = :cart_id');
    $del->execute([':id' => $cartItemId, ':cart_id' => $cartId]);

    $cart = getCartDetails($pdo);
    echo json_encode([
        'success' => true,
        'message' => 'Product removed from your bag.',
        'cart'    => $cart,
    ]);
    exit;
}

if ($action === 'clear') {
    $del = $pdo->prepare('DELETE FROM cart_items WHERE cart_id = :cart_id');
    $del->execute([':cart_id' => $cartId]);
    unset($_SESSION['promo_code']);

    $cart = getCartDetails($pdo);
    echo json_encode([
        'success' => true,
        'message' => 'Shopping bag cleared.',
        'cart'    => $cart,
    ]);
    exit;
}

if ($action === 'promo') {
    $code = strtoupper(trim((string)($input['promo_code'] ?? '')));
    if ($code === '') {
        unset($_SESSION['promo_code']);
        $cart = getCartDetails($pdo);
        echo json_encode([
            'success' => true,
            'message' => 'Promo code removed.',
            'cart'    => $cart,
        ]);
        exit;
    }

    if ($code === 'KICK10') {
        $_SESSION['promo_code'] = 'KICK10';
        $cart = getCartDetails($pdo);
        echo json_encode([
            'success' => true,
            'message' => 'Promo code KICK10 applied (10% OFF)!',
            'cart'    => $cart,
        ]);
        exit;
    }

    if ($code === 'ATHLETE20') {
        $currentCart = getCartDetails($pdo);
        if ($currentCart['subtotal'] < 10000) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'ATHLETE20 requires a minimum subtotal of ₹10,000.',
                'cart'    => $currentCart,
            ]);
            exit;
        }
        $_SESSION['promo_code'] = 'ATHLETE20';
        $cart = getCartDetails($pdo);
        echo json_encode([
            'success' => true,
            'message' => 'Promo code ATHLETE20 applied (20% OFF)!',
            'cart'    => $cart,
        ]);
        exit;
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid promo code. Try KICK10 or ATHLETE20.',
        'cart'    => getCartDetails($pdo),
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Unsupported cart action.']);

