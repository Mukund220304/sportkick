<?php
/**
 * SportKick - Checkout & Order Creation API
 * Creates persistent records in `orders` and `order_items` inside a MySQL transaction.
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);
if (!is_array($input)) {
    $input = $_POST;
}

$cart = getCartDetails($pdo);
if (empty($cart['items'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Your shopping bag is empty.']);
    exit;
}

$customerName = trim((string)($input['customer_name'] ?? ''));
$customerEmail = strtolower(trim((string)($input['customer_email'] ?? '')));
$customerPhone = trim((string)($input['customer_phone'] ?? ''));
$shippingAddress = trim((string)($input['shipping_address'] ?? ''));
$shippingCity = trim((string)($input['shipping_city'] ?? ''));
$shippingState = trim((string)($input['shipping_state'] ?? ''));
$shippingZip = trim((string)($input['shipping_zip'] ?? ''));
$paymentMethod = trim((string)($input['payment_method'] ?? 'Credit / Debit Card'));

if (strlen($customerName) < 2) {
    http_response_code(400);
    echo json_encode(['success' => false, 'field' => 'customer_name', 'message' => 'Please enter the recipient full name.']);
    exit;
}
if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'field' => 'customer_email', 'message' => 'Please enter a valid email address for order confirmation.']);
    exit;
}
if (strlen($customerPhone) < 7) {
    http_response_code(400);
    echo json_encode(['success' => false, 'field' => 'customer_phone', 'message' => 'Please enter a valid contact phone number.']);
    exit;
}
if (strlen($shippingAddress) < 5 || strlen($shippingCity) < 2 || strlen($shippingState) < 2 || strlen($shippingZip) < 3) {
    http_response_code(400);
    echo json_encode(['success' => false, 'field' => 'shipping_address', 'message' => 'Please complete all delivery address fields (street, city, state, and postal code).']);
    exit;
}

$allowedPayments = ['Credit / Debit Card', 'UPI / Instant Pay', 'Net Banking', 'Cash on Delivery'];
if (!in_array($paymentMethod, $allowedPayments, true)) {
    $paymentMethod = 'Credit / Debit Card';
}

$paymentStatus = ($paymentMethod === 'Cash on Delivery') ? 'Pending (COD)' : 'Paid';
$orderNumber = 'SK-' . date('Y') . '-' . random_int(10000, 99999);
$userId = isLoggedIn() ? (int)$_SESSION['user_id'] : null;

try {
    $pdo->beginTransaction();

    $stmtOrder = $pdo->prepare('
        INSERT INTO orders (
            order_number, user_id, customer_name, customer_email, customer_phone,
            shipping_address, shipping_city, shipping_state, shipping_zip,
            payment_method, payment_status, order_status,
            subtotal, discount_amount, shipping_fee, total_amount
        ) VALUES (
            :order_number, :user_id, :customer_name, :customer_email, :customer_phone,
            :shipping_address, :shipping_city, :shipping_state, :shipping_zip,
            :payment_method, :payment_status, :order_status,
            :subtotal, :discount_amount, :shipping_fee, :total_amount
        )
    ');

    $stmtOrder->execute([
        ':order_number'     => $orderNumber,
        ':user_id'          => $userId,
        ':customer_name'    => $customerName,
        ':customer_email'   => $customerEmail,
        ':customer_phone'   => $customerPhone,
        ':shipping_address' => $shippingAddress,
        ':shipping_city'    => $shippingCity,
        ':shipping_state'   => $shippingState,
        ':shipping_zip'     => $shippingZip,
        ':payment_method'   => $paymentMethod,
        ':payment_status'   => $paymentStatus,
        ':order_status'     => 'Confirmed',
        ':subtotal'         => $cart['subtotal'],
        ':discount_amount'  => $cart['promo_discount'],
        ':shipping_fee'     => $cart['shipping_fee'],
        ':total_amount'     => $cart['total_amount'],
    ]);

    $orderId = (int)$pdo->lastInsertId();

    $stmtItem = $pdo->prepare('
        INSERT INTO order_items (
            order_id, product_id, product_name, brand, size, color, unit_price, quantity, line_total
        ) VALUES (
            :order_id, :product_id, :product_name, :brand, :size, :color, :unit_price, :quantity, :line_total
        )
    ');

    $stmtStock = $pdo->prepare('UPDATE products SET stock = GREATEST(0, stock - :qty) WHERE id = :pid');

    foreach ($cart['items'] as $item) {
        $stmtItem->execute([
            ':order_id'     => $orderId,
            ':product_id'   => (int)$item['product_id'],
            ':product_name' => $item['name'],
            ':brand'        => $item['brand'],
            ':size'         => $item['size'],
            ':color'        => $item['color'],
            ':unit_price'   => (float)$item['price'],
            ':quantity'     => (int)$item['quantity'],
            ':line_total'   => (float)$item['line_total'],
        ]);

        $stmtStock->execute([
            ':qty' => (int)$item['quantity'],
            ':pid' => (int)$item['product_id'],
        ]);
    }

    // Update logged-in user's default address if not set
    if ($userId !== null) {
        $stmtUserUpd = $pdo->prepare('
            UPDATE users
            SET phone = :phone, address = :addr, city = :city, state = :state, zip_code = :zip
            WHERE id = :uid
        ');
        $stmtUserUpd->execute([
            ':phone' => $customerPhone,
            ':addr'  => $shippingAddress,
            ':city'  => $shippingCity,
            ':state' => $shippingState,
            ':zip'   => $shippingZip,
            ':uid'   => $userId,
        ]);
    }

    // Clear cart items and promo code
    $stmtClear = $pdo->prepare('DELETE FROM cart_items WHERE cart_id = :cart_id');
    $stmtClear->execute([':cart_id' => $cart['cart_id']]);
    unset($_SESSION['promo_code']);
    $_SESSION['last_order_number'] = $orderNumber;

    $pdo->commit();

    echo json_encode([
        'success'      => true,
        'message'      => 'Order placed successfully!',
        'order_number' => $orderNumber,
        'order_id'     => $orderId,
        'redirect'     => 'checkout.php?order=' . urlencode($orderNumber),
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Could not complete order: ' . $e->getMessage(),
    ]);
}
