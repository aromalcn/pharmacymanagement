<?php
/**
 * admin/dashboard.php
 * Admin dashboard – shows key inventory stats and recent orders.
 * PROTECTED: Only accessible when admin is logged in.
 */
if (session_status() === PHP_SESSION_NONE)
    session_start();

// Session guard – redirect to login if not authenticated
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

require_once '../config/database.php';
$db = getDB();

// --------------- Dashboard Statistics ---------------

// 1. Total medicines in stock
$total = $db->query("SELECT COUNT(*) FROM medicines")->fetchColumn();

// 2. Low stock medicines (stock between 1 and 9)
$low_stock = $db->query("SELECT COUNT(*) FROM medicines WHERE stock_quantity > 0 AND stock_quantity < 10")->fetchColumn();

// 3. Expired medicines (expiry date is today or before)
$expired = $db->query("SELECT COUNT(*) FROM medicines WHERE expiry_date <= date('now')")->fetchColumn();

// 4. Expiring soon – within the next 30 days (and not already expired)
$expiring_soon = $db->query("
    SELECT COUNT(*) FROM medicines
    WHERE expiry_date > date('now')
      AND expiry_date <= date('now', '+30 days')
")->fetchColumn();

// 5. Total orders
$total_orders = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();

// 6. Pending orders
$pending_orders = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'")->fetchColumn();

// 7. Recent 10 orders
$recent_orders = $db->query("
    SELECT id, customer_name, phone, status, order_date
    FROM orders
    ORDER BY order_date DESC
    LIMIT 10
")->fetchAll();

// 8. Recent 5 expiring medicines
$expiring_meds = $db->query("
    SELECT name, batch_number, expiry_date, stock_quantity
    FROM medicines
    WHERE expiry_date > date('now')
      AND expiry_date <= date('now', '+30 days')
    ORDER BY expiry_date ASC
    LIMIT 5
")->fetchAll();

// Status badge helper
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

$pageTitle = 'Admin Dashboard – PharmaCare';
$cssPath = '../';
$rootPath = '../';
include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <div>
            <h1 class="page-title">📊 Dashboard</h1>
            <p class="page-subtitle">Welcome back, <strong>
                    <?= htmlspecialchars($_SESSION['admin_name'])?>
                </strong>! Here's your pharmacy overview.</p>
        </div>
        <a href="add_medicine.php" class="btn btn-primary">+ Add Medicine</a>
    </div>

    <!-- ===== STAT CARDS ===== -->
    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="stat-icon">💊</div>
            <div class="stat-label">Total Medicines</div>
            <div class="stat-value">
                <?= $total?>
            </div>
        </div>
        <div class="stat-card yellow">
            <div class="stat-icon">⚠️</div>
            <div class="stat-label">Low Stock (&lt;10)</div>
            <div class="stat-value">
                <?= $low_stock?>
            </div>
        </div>
        <div class="stat-card red">
            <div class="stat-icon">🗑️</div>
            <div class="stat-label">Expired</div>
            <div class="stat-value">
                <?= $expired?>
            </div>
        </div>
        <div class="stat-card yellow">
            <div class="stat-icon">📅</div>
            <div class="stat-label">Expiring in 30 Days</div>
            <div class="stat-value">
                <?= $expiring_soon?>
            </div>
        </div>
        <div class="stat-card green">
            <div class="stat-icon">📦</div>
            <div class="stat-label">Total Orders</div>
            <div class="stat-value">
                <?= $total_orders?>
            </div>
        </div>
        <div class="stat-card blue">
            <div class="stat-icon">🕐</div>
            <div class="stat-label">Pending Orders</div>
            <div class="stat-value">
                <?= $pending_orders?>
            </div>
        </div>
    </div>

    <!-- ===== TWO COLUMN: Expiring Medicines + Recent Orders ===== -->
    <div class="form-row" style="gap:1.5rem; margin-bottom:2rem;">

        <!-- Expiring Soon Block -->
        <div class="card">
            <div class="card-header">
                <h3>⏰ Medicines Expiring Soon</h3>
                <a href="manage_medicines.php?filter=expiring" class="btn btn-sm btn-outline">View All</a>
            </div>
            <div class="card-body" style="padding:0;">
                <?php if (empty($expiring_meds)): ?>
                <div class="empty-state">
                    <div class="empty-icon">✅</div>
                    <p>No medicines expiring soon.</p>
                </div>
                <?php
else: ?>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Medicine</th>
                                <th>Batch</th>
                                <th>Expiry</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expiring_meds as $m): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($m['name'])?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($m['batch_number'])?>
                                </td>
                                <td>
                                    <span class="badge badge-warning">
                                        <?= date('d M Y', strtotime($m['expiry_date']))?>
                                    </span>
                                </td>
                                <td>
                                    <?= $m['stock_quantity']?>
                                </td>
                            </tr>
                            <?php
    endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php
endif; ?>
            </div>
        </div>

        <!-- Recent Orders Block -->
        <div class="card">
            <div class="card-header">
                <h3>🛒 Recent Orders</h3>
                <a href="view_orders.php" class="btn btn-sm btn-outline">View All</a>
            </div>
            <div class="card-body" style="padding:0;">
                <?php if (empty($recent_orders)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <p>No orders yet.</p>
                </div>
                <?php
else: ?>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $o): ?>
                            <tr>
                                <td><a href="view_orders.php?id=<?= $o['id']?>">#
                                        <?= $o['id']?>
                                    </a></td>
                                <td>
                                    <?= htmlspecialchars($o['customer_name'])?>
                                </td>
                                <td>
                                    <?= statusBadge($o['status'])?>
                                </td>
                                <td>
                                    <?= date('d M Y', strtotime($o['order_date']))?>
                                </td>
                            </tr>
                            <?php
    endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php
endif; ?>
            </div>
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>