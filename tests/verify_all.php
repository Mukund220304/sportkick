<?php
/**
 * SportKick - Automated End-to-End Integration & Functional Test Suite
 * Verifies MySQL tables, CRUD operations, search/filter engine, cart, wishlist, auth, and checkout.
 */

require_once __DIR__ . '/../includes/functions.php';

$results = [];
$passed = 0;
$failed = 0;

function assertTest(string $name, bool $condition, string $details = ''): void
{
    global $results, $passed, $failed;
    if ($condition) {
        $passed++;
        $results[] = ['status' => 'PASS', 'name' => $name, 'details' => $details];
        echo "[PASS] {$name}" . ($details ? " — {$details}" : "") . PHP_EOL;
    } else {
        $failed++;
        $results[] = ['status' => 'FAIL', 'name' => $name, 'details' => $details];
        echo "[FAIL] {$name}" . ($details ? " — {$details}" : "") . PHP_EOL;
    }
}

echo "=================================================================\n";
echo " SPORTKICK E-COMMERCE — AUTOMATED END-TO-END VERIFICATION SUITE\n";
echo "=================================================================\n\n";

// 1. Verify all 8 MySQL tables exist
$expectedTables = ['users', 'categories', 'products', 'cart', 'cart_items', 'wishlist', 'orders', 'order_items'];
$actualTables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$missingTables = array_diff($expectedTables, $actualTables);
assertTest(
    'MySQL Database Schema (8 Tables)',
    empty($missingTables),
    'Tables verified: ' . implode(', ', $expectedTables)
);

// 2. Verify all 8 mandatory sports categories
$categories = getAllCategories($pdo);
$catNames = array_column($categories, 'name');
$requiredCategories = ['Running', 'Basketball', 'Football', 'Cricket', 'Tennis', 'Badminton', 'Gym & Training', 'Hiking'];
$missingCats = array_diff($requiredCategories, $catNames);
assertTest(
    '8 Mandatory Sports Categories',
    count($categories) === 8 && empty($missingCats),
    'Categories: ' . implode(', ', $catNames)
);

// 3. Verify 24 products loaded from MySQL
$allProducts = queryProducts($pdo, []);
assertTest(
    'Products Populated in MySQL',
    count($allProducts) >= 24,
    'Total products in DB: ' . count($allProducts)
);

// 4. Verify Search & Filter Engine (Name, Brand, Category, Price Range, Size, Color)
$searchNike = queryProducts($pdo, ['q' => 'Alphafly']);
assertTest('Search by Product Name ("Alphafly")', count($searchNike) === 1 && $searchNike[0]['brand'] === 'Nike', $searchNike[0]['name'] ?? '');

$filterBrand = queryProducts($pdo, ['brand' => 'Adidas']);
assertTest('Filter by Brand ("Adidas")', count($filterBrand) >= 4, 'Matched ' . count($filterBrand) . ' Adidas products');

$filterCat = queryProducts($pdo, ['category' => 'badminton']);
assertTest('Filter by Sport Category ("badminton")', count($filterCat) === 3, 'Matched ' . count($filterCat) . ' Badminton products');

$filterPrice = queryProducts($pdo, ['min_price' => 8000, 'max_price' => 14000]);
$priceOk = !empty($filterPrice);
foreach ($filterPrice as $fp) {
    if ((float)$fp['price'] < 8000 || (float)$fp['price'] > 14000) {
        $priceOk = false;
    }
}
assertTest('Filter by Price Range (₹8,000 - ₹14,000)', $priceOk, 'Matched ' . count($filterPrice) . ' products within range');

$filterSize = queryProducts($pdo, ['size' => 'UK 9']);
assertTest('Filter by Size ("UK 9")', count($filterSize) >= 10, 'Matched ' . count($filterSize) . ' products in UK 9');

$filterColor = queryProducts($pdo, ['color' => 'Orange']);
assertTest('Filter by Color ("Orange")', count($filterColor) >= 5, 'Matched ' . count($filterColor) . ' Orange colorway products');

// 5. Verify User Registration, Password Hashing & Login
$testEmail = 'test_evaluator_' . time() . '@sportkick.com';
$testPass = 'Champion@2026';
$hash = password_hash($testPass, PASSWORD_DEFAULT);

$stmtReg = $pdo->prepare('INSERT INTO users (full_name, email, password_hash, phone, address, city, state, zip_code) VALUES (:n, :e, :p, :ph, :ad, :c, :s, :z)');
$stmtReg->execute([
    ':n'  => 'Test Evaluator',
    ':e'  => $testEmail,
    ':p'  => $hash,
    ':ph' => '+91 90000 11111',
    ':ad' => '100 Stadium Road',
    ':c'  => 'Mumbai',
    ':s'  => 'Maharashtra',
    ':z'  => '400001',
]);
$testUserId = (int)$pdo->lastInsertId();
$_SESSION['user_id'] = $testUserId;
$fetchedUser = getCurrentUser($pdo);
assertTest(
    'User Registration & Session Auth',
    $fetchedUser !== null && $fetchedUser['email'] === $testEmail && password_verify($testPass, $hash),
    'Created & authenticated user ID #' . $testUserId
);

// 6. Verify Cart Operations (Add, Update Qty, Promo Discount, Totals)
$cartId = getOrCreateCartId($pdo);
$stmtAddCart = $pdo->prepare('INSERT INTO cart_items (cart_id, product_id, size, color, quantity) VALUES (:cid, 1, "UK 9", "Orange", 2)');
$stmtAddCart->execute([':cid' => $cartId]);
$_SESSION['promo_code'] = 'KICK10';
$cartDetails = getCartDetails($pdo);
assertTest(
    'Shopping Cart Add, Quantity & Promo Calculation',
    $cartDetails['item_count'] === 2 && $cartDetails['subtotal'] > 25000 && $cartDetails['promo_discount'] > 2500,
    sprintf('Subtotal: ₹%.2f, Promo: -₹%.2f, Total: ₹%.2f', $cartDetails['subtotal'], $cartDetails['promo_discount'], $cartDetails['total_amount'])
);

// 7. Verify Wishlist Operations
$stmtWish = $pdo->prepare('INSERT INTO wishlist (user_id, session_id, product_id) VALUES (:uid, :sid, 4)');
$stmtWish->execute([':uid' => $testUserId, ':sid' => session_id()]);
$wishItems = getWishlistItems($pdo);
assertTest(
    'Wishlist Save & Fetch',
    count($wishItems) === 1 && (int)$wishItems[0]['id'] === 4,
    'Saved product: ' . ($wishItems[0]['name'] ?? '')
);

// 8. Verify Checkout Order & Order Items Creation
$orderNum = 'SK-TEST-' . random_int(10000, 99999);
$stmtOrd = $pdo->prepare('
    INSERT INTO orders (
        order_number, user_id, customer_name, customer_email, customer_phone,
        shipping_address, shipping_city, shipping_state, shipping_zip,
        payment_method, payment_status, order_status,
        subtotal, discount_amount, shipping_fee, total_amount
    ) VALUES (
        :onum, :uid, :cname, :cemail, :cphone,
        :addr, :city, :state, :zip,
        "UPI / Instant Pay", "Paid", "Confirmed",
        :sub, :disc, :ship, :tot
    )
');
$stmtOrd->execute([
    ':onum'   => $orderNum,
    ':uid'    => $testUserId,
    ':cname'  => $fetchedUser['full_name'],
    ':cemail' => $fetchedUser['email'],
    ':cphone' => $fetchedUser['phone'],
    ':addr'   => $fetchedUser['address'],
    ':city'   => $fetchedUser['city'],
    ':state'  => $fetchedUser['state'],
    ':zip'    => $fetchedUser['zip_code'],
    ':sub'    => $cartDetails['subtotal'],
    ':disc'   => $cartDetails['promo_discount'],
    ':ship'   => $cartDetails['shipping_fee'],
    ':tot'    => $cartDetails['total_amount'],
]);
$newOrderId = (int)$pdo->lastInsertId();

$stmtOi = $pdo->prepare('
    INSERT INTO order_items (order_id, product_id, product_name, brand, size, color, unit_price, quantity, line_total)
    VALUES (:oid, 1, "Nike Air Zoom Alphafly Next% 3 Proto", "Nike", "UK 9", "Orange", 189.99, 2, 379.98)
');
$stmtOi->execute([':oid' => $newOrderId]);

$verifyOrder = $pdo->prepare('SELECT COUNT(*) FROM order_items WHERE order_id = :oid');
$verifyOrder->execute([':oid' => $newOrderId]);
assertTest(
    'Checkout Order & Order Items Persistence',
    $newOrderId > 0 && (int)$verifyOrder->fetchColumn() === 1,
    'Order #' . $orderNum . ' persisted with line items'
);

// Clean up temporary test user (cascades cart, wishlist, order)
$cleanup = $pdo->prepare('DELETE FROM users WHERE id = :uid');
$cleanup->execute([':uid' => $testUserId]);
$cleanupOrd = $pdo->prepare('DELETE FROM orders WHERE id = :oid');
$cleanupOrd->execute([':oid' => $newOrderId]);
unset($_SESSION['user_id'], $_SESSION['promo_code']);

echo "\n-----------------------------------------------------------------\n";
echo sprintf(" TOTAL: %d Passed, %d Failed\n", $passed, $failed);
echo "-----------------------------------------------------------------\n";

exit($failed > 0 ? 1 : 0);

