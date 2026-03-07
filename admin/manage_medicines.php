<?php
/**
 * admin/manage_medicines.php
 * Lists all medicines with edit/delete actions.
 * Highlights expired medicines in red and expiring-soon in yellow.
 * Supports optional filter: ?filter=expired | ?filter=expiring | ?filter=low_stock
 */
if (session_status() === PHP_SESSION_NONE)
    session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

require_once '../config/database.php';
$db = getDB();

// -------- Handle DELETE action --------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $del_id = (int) $_POST['delete_id'];
    // Check if any order references this medicine before deleting
    $ref = $db->prepare("SELECT COUNT(*) FROM order_items WHERE medicine_id = ?");
    $ref->execute([$del_id]);
    if ($ref->fetchColumn() > 0) {
        $msg = 'danger|Cannot delete: this medicine has existing order records.';
    } else {
        $stmt = $db->prepare("DELETE FROM medicines WHERE id = ?");
        $stmt->execute([$del_id]);
        $msg = 'success|Medicine deleted successfully.';
    }
}

// -------- Pagination --------
$per_page = 15;
$current_page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($current_page - 1) * $per_page;

// -------- Active Filter --------
$filter = $_GET['filter'] ?? '';

// Build WHERE clause for filter
$where = '1=1';
if ($filter === 'expired')
    $where = 'expiry_date <= CURDATE()';
if ($filter === 'expiring')
    $where = 'expiry_date > CURDATE() AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)';
if ($filter === 'low_stock')
    $where = 'stock_quantity > 0 AND stock_quantity < 10';
if ($filter === 'out_stock')
    $where = 'stock_quantity = 0';

// Count for pagination
$total_count = $db->query("SELECT COUNT(*) FROM medicines WHERE $where")->fetchColumn();
$total_pages = (int) ceil($total_count / $per_page);

// Fetch medicines
$medicines = $db->query("
    SELECT * FROM medicines
    WHERE $where
    ORDER BY name ASC
    LIMIT $per_page OFFSET $offset
")->fetchAll();

// Today for expiry comparisons
$today = new DateTime();

$pageTitle = 'Manage Medicines – PharmaCare';
$cssPath = '../';
$rootPath = '../';
include '../includes/header.php';

// Show message if any
if (!empty($msg)) {
    [$type, $text] = explode('|', $msg, 2);
    echo "<div class='container'><div class='alert alert-$type'>" . htmlspecialchars($text) . "</div></div>";
}
?>

<div class="container">
    <div class="page-header">
        <div>
            <h1 class="page-title">💊 Medicines</h1>
            <p class="page-subtitle">
                <?= $total_count ?> record(s) found
            </p>
        </div>
        <a href="add_medicine.php" class="btn btn-primary">+ Add New Medicine</a>
    </div>

    <!-- Filter Tabs -->
    <div style="display:flex; gap:.5rem; flex-wrap:wrap; margin-bottom:1.25rem;">
        <a href="manage_medicines.php"
            class="btn btn-sm <?= $filter === '' ? 'btn-primary' : 'btn-outline' ?>">All</a>
        <a href="manage_medicines.php?filter=expired"
            class="btn btn-sm <?= $filter === 'expired' ? 'btn-danger' : 'btn-outline' ?>">🗑 Expired</a>
        <a href="manage_medicines.php?filter=expiring"
            class="btn btn-sm <?= $filter === 'expiring' ? 'btn-warning' : 'btn-outline' ?>">⏰ Expiring Soon</a>
        <a href="manage_medicines.php?filter=low_stock"
            class="btn btn-sm <?= $filter === 'low_stock' ? 'btn-warning' : 'btn-outline' ?>">⚠️ Low Stock</a>
        <a href="manage_medicines.php?filter=out_stock"
            class="btn btn-sm <?= $filter === 'out_stock' ? 'btn-secondary' : 'btn-outline' ?>">❌ Out of Stock</a>
    </div>

    <div class="card">
        <div class="card-body" style="padding:0;">
            <?php if (empty($medicines)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <p>No medicines found for the selected filter.</p>
                </div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Manufacturer</th>
                                <th>Batch</th>
                                <th>Expiry Date</th>
                                <th>Price (₹)</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($medicines as $med):
                                $expiryDate = new DateTime($med['expiry_date']);
                                $diff = $today->diff($expiryDate);
                                $isExpired = $expiryDate < $today;
                                $expiringSoon = !$isExpired && $diff->days <= 30;
                                $rowClass = $isExpired ? 'style="background:#fff5f5;"' : ($expiringSoon ? 'style="background:#fffbeb;"' : '');
                                ?>
                                <tr <?= $rowClass ?>>
                                    <td>
                                        <?= $med['id'] ?>
                                    </td>
                                    <td><strong>
                                            <?= htmlspecialchars($med['name']) ?>
                                        </strong></td>
                                    <td>
                                        <?= htmlspecialchars($med['category']) ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($med['manufacturer']) ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($med['batch_number']) ?>
                                    </td>
                                    <td>
                                        <?php if ($isExpired): ?>
                                            <span class="badge badge-danger">EXPIRED –
                                                <?= date('d M Y', strtotime($med['expiry_date'])) ?>
                                            </span>
                                        <?php elseif ($expiringSoon): ?>
                                            <span class="badge badge-warning">
                                                <?= date('d M Y', strtotime($med['expiry_date'])) ?>
                                            </span>
                                        <?php else: ?>
                                            <?= date('d M Y', strtotime($med['expiry_date'])) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>₹
                                        <?= number_format($med['price'], 2) ?>
                                    </td>
                                    <td>
                                        <?= $med['stock_quantity'] ?>
                                    </td>
                                    <td>
                                        <?php if ($isExpired): ?>
                                            <span class="badge badge-danger">Expired</span>
                                        <?php elseif ($med['stock_quantity'] == 0): ?>
                                            <span class="badge badge-gray">Out of Stock</span>
                                        <?php elseif ($med['stock_quantity'] < 10): ?>
                                            <span class="badge badge-warning">Low Stock</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">In Stock</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="edit_medicine.php?id=<?= $med['id'] ?>" class="btn btn-sm btn-outline">✏️
                                                Edit</a>
                                            <form method="POST" onsubmit="return confirm('Delete this medicine?')">
                                                <input type="hidden" name="delete_id" value="<?= $med['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">🗑 Del</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                <a href="?filter=<?= urlencode($filter) ?>&page=<?= $p ?>" class="<?= $p === $current_page ? 'active' : '' ?>">
                    <?= $p ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>