<?php
/**
 * place_order.php
 * Handles the actual database transaction for placing an order.
 * CRITICAL: Updates stock levels and ensures constraints are met.
 */
require_once 'config/database.php';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$med_id = (int)$_POST['med_id'];
$qty = (int)$_POST['qty'];
$customer_name = trim($_POST['customer_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$payment_method = $_POST['payment_method'] ?? 'cash_on_delivery';

if ($med_id <= 0 || $qty <= 0 || empty($customer_name) || empty($phone) || empty($address)) {
    die("Error: Invalid order data.");
}

try {
    // Start transaction to ensure atomicity
    $db->beginTransaction();

    // 1. Lock and Verify Stock (SELECT FOR UPDATE)
    $stmt = $db->prepare("SELECT price, stock_quantity FROM medicines WHERE id = ? AND expiry_date > CURDATE() FOR UPDATE");
    $stmt->execute([$med_id]);
    $med = $stmt->fetch();

    if (!$med) {
        throw new Exception("Medicine no longer available or has expired.");
    }

    if ($med['stock_quantity'] < $qty) {
        throw new Exception("Insufficient stock available.");
    }

    // 2. Create Order Header
    $stmt = $db->prepare("
        INSERT INTO orders (customer_name, phone, address, payment_method, status)
        VALUES (?, ?, ?, ?, 'Pending')
    ");
    $stmt->execute([$customer_name, $phone, $address, $payment_method]);
    $order_id = $db->lastInsertId();

    // 3. Create Order Item
    $stmt = $db->prepare("
        INSERT INTO order_items (order_id, medicine_id, quantity, price)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$order_id, $med_id, $qty, $med['price']]);

    // 4. Update Stock
    $stmt = $db->prepare("UPDATE medicines SET stock_quantity = stock_quantity - ? WHERE id = ?");
    $stmt->execute([$qty, $med_id]);

    // Commit Transaction
    $db->commit();

    // Redirect to success
    header("Location: order_success.php?id=$order_id");
    exit;

}
catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    // Simple error display (beginner friendly)
    echo "<div style='padding: 2rem; font-family: sans-serif; text-align: center;'>";
    echo "<h2>❌ Order Failed</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<a href='index.php'>Return to Home</a>";
    echo "</div>";
}