<?php
/**
 * order_form.php
 * Customer order form – collects shipping and payment information.
 */
require_once 'config/database.php';
$db = getDB();

$med_id = (int) ($_GET['med_id'] ?? 0);
$qty = (int) ($_GET['qty'] ?? 1);

// Fetch Medicine (Ensuring it's still available)
$stmt = $db->prepare("SELECT * FROM medicines WHERE id = ? AND expiry_date > CURDATE() AND stock_quantity >= ?");
$stmt->execute([$med_id, $qty]);
$med = $stmt->fetch();

if (!$med || $qty <= 0) {
    header('Location: index.php');
    exit;
}

$subtotal = $med['price'] * $qty;

$pageTitle = 'Order Form – PharmaCare';
$cssPath = '';
$rootPath = '';
include 'includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1 class="page-title">📝 Checkout</h1>
        <p class="page-subtitle">Provide your delivery details to place the order.</p>
    </div>

    <div class="form-row" style="gap:2rem;">
        <!-- Order Summary Column -->
        <div class="card" style="flex:1; max-width: 400px; height: fit-content;">
            <div class="card-header">
                <h3>Order Summary</h3>
            </div>
            <div class="card-body">
                <div style="display:flex; justify-content:space-between; margin-bottom: 0.5rem;">
                    <span>
                        <?= htmlspecialchars($med['name']) ?> (x
                        <?= $qty ?>)
                    </span>
                    <span>₹
                        <?= number_format($subtotal, 2) ?>
                    </span>
                </div>
                <div class="section-divider"></div>
                <div style="display:flex; justify-content:space-between; font-weight: 700; font-size: 1.25rem;">
                    <span>Total</span>
                    <span style="color:var(--primary);">₹
                        <?= number_format($subtotal, 2) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Customer Form Column -->
        <div class="card" style="flex:1;">
            <div class="card-header">
                <h3>Customer Information</h3>
            </div>
            <div class="card-body">
                <form action="place_order.php" method="POST">
                    <input type="hidden" name="med_id" value="<?= $med_id ?>">
                    <input type="hidden" name="qty" value="<?= $qty ?>">

                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="customer_name" class="form-control"
                            placeholder="Enter your full name" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" class="form-control" placeholder="Enter mobile number"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="address">Delivery Address *</label>
                        <textarea id="address" name="address" class="form-control" rows="3"
                            placeholder="Street, City, Pincode" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="payment">Payment Method *</label>
                        <select name="payment_method" id="payment" class="form-control" required>
                            <option value="cash_on_delivery">Cash on Delivery</option>
                            <option value="pay_at_pharmacy">Pay at Pharmacy (Local Pickup)</option>
                        </select>
                    </div>

                    <div style="margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">Confirm Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>