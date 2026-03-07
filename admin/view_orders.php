<?php
/**
 * admin/view_orders.php
 * Lists all orders and shows details for a specific order.
 * Allows updating order status.
 */
if (session_status() === PHP_SESSION_NONE)
    session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

require_once '../config/database.php';
$db = getDB();

$msg = '';
// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int) $_POST['order_id'];
    $new_status = $_POST['status'];

    $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $order_id]);
    $msg = 'success|Order status updated to ' . $new_status;
}

// Check for single order view
$single_id = (int) ($_GET['id'] ?? 0);
$order_details = null;
$order_items = [];

if ($single_id > 0) {
    // Fetch Order Header
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$single_id]);
    $order_details = $stmt->fetch();

    if ($order_details) {
        // Fetch Order Items
        $stmt = $db->prepare("
            SELECT oi.*, m.name as medicine_name 
            FROM order_items oi 
            JOIN medicines m ON oi.medicine_id = m.id 
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$single_id]);
        $order_items = $stmt->fetchAll();
    }
}

// Fetch all orders for the list
$orders = $db->query("SELECT * FROM orders ORDER BY order_date DESC")->fetchAll();

function statusBadge(string $status): string
{
    $map = [
        'Pending' => 'warning',
        'Ready for Pickup' => 'info',
        'Completed' => 'success',
        'Cancelled' => 'danger',
    ];
    $class = $map[$status] ?? 'gray';
    return "<span class='badge badge-$class'>" . htmlspecialchars($status) . "</span>";
}

$pageTitle = 'View Orders – PharmaCare';
$cssPath = '../';
$rootPath = '../';
include '../includes/header.php';

if (!empty($msg)) {
    [$type, $text] = explode('|', $msg, 2);
    echo "<div class='container'><div class='alert alert-$type'>" . htmlspecialchars($text) . "</div></div>";
}
?>

<div class="container">
    <div class="page-header">
        <div>
            <h1 class="page-title">📦 Orders</h1>
            <p class="page-subtitle">Manage customer medicine orders.</p>
        </div>
    </div>

    <?php if ($order_details): ?>
        <!-- Single Order Detail View -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h3>Order #
                    <?= $order_details['id'] ?> Details
                </h3>
                <a href="view_orders.php" class="btn btn-sm btn-secondary">Close Details</a>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div>
                        <p><strong>Customer:</strong>
                            <?= htmlspecialchars($order_details['customer_name']) ?>
                        </p>
                        <p><strong>Phone:</strong>
                            <?= htmlspecialchars($order_details['phone']) ?>
                        </p>
                        <p><strong>Address:</strong>
                            <?= nl2br(htmlspecialchars($order_details['address'])) ?>
                        </p>
                    </div>
                    <div>
                        <p><strong>Date:</strong>
                            <?= date('d M Y, H:i', strtotime($order_details['order_date'])) ?>
                        </p>
                        <p><strong>Payment:</strong>
                            <?= str_replace('_', ' ', ucwords($order_details['payment_method'], '_')) ?>
                        </p>
                        <p><strong>Status:</strong>
                            <?= statusBadge($order_details['status']) ?>
                        </p>
                    </div>
                </div>

                <h4 style="margin: 1.5rem 0 0.5rem 0;">Items Ordered</h4>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Medicine</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $grand_total = 0;
                            foreach ($order_items as $item):
                                $subtotal = $item['quantity'] * $item['price'];
                                $grand_total += $subtotal;
                                ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($item['medicine_name']) ?>
                                    </td>
                                    <td>
                                        <?= $item['quantity'] ?>
                                    </td>
                                    <td>₹
                                        <?= number_format($item['price'], 2) ?>
                                    </td>
                                    <td>₹
                                        <?= number_format($subtotal, 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr style="font-weight: bold; background: var(--gray-50);">
                                <td colspan="3" style="text-align: right;">Total Amount:</td>
                                <td>₹
                                    <?= number_format($grand_total, 2) ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="section-divider"></div>

                <form method="POST" style="display: flex; gap: 1rem; align-items: flex-end;">
                    <input type="hidden" name="order_id" value="<?= $order_details['id'] ?>">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Update Status</label>
                        <select name="status" class="form-control">
                            <option value="Pending" <?= $order_details['status'] === 'Pending' ? 'selected' : '' ?>>Pending
                            </option>
                            <option value="Ready for Pickup" <?= $order_details['status'] === 'Ready for Pickup' ? 'selected' : '' ?>>Ready for Pickup</option>
                            <option value="Completed" <?= $order_details['status'] === 'Completed' ? 'selected' : '' ?>
                                >Completed</option>
                            <option value="Cancelled" <?= $order_details['status'] === 'Cancelled' ? 'selected' : '' ?>
                                >Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" name="update_status" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- All Orders List -->
    <div class="card">
        <div class="card-body" style="padding:0;">
            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <p>No orders yet.</p>
                </div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th>Method</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $o): ?>
                                <tr <?= ($single_id === (int) $o['id']) ? 'style="background: var(--primary-light);"' : '' ?>>
                                    <td>#
                                        <?= $o['id'] ?>
                                    </td>
                                    <td><strong>
                                            <?= htmlspecialchars($o['customer_name']) ?>
                                        </strong></td>
                                    <td>
                                        <?= htmlspecialchars($o['phone']) ?>
                                    </td>
                                    <td>
                                        <?= $o['payment_method'] === 'cash_on_delivery' ? 'COD' : 'Pickup' ?>
                                    </td>
                                    <td>
                                        <?= date('d M Y', strtotime($o['order_date'])) ?>
                                    </td>
                                    <td>
                                        <?= statusBadge($o['status']) ?>
                                    </td>
                                    <td>
                                        <a href="view_orders.php?id=<?= $o['id'] ?>#details" class="btn btn-sm btn-outline">👁️
                                            View Details</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>