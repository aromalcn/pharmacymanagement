<?php
/**
 * admin/add_medicine.php
 * Form to add a new medicine to the inventory.
 */
if (session_status() === PHP_SESSION_NONE)
    session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

require_once '../config/database.php';
$db = getDB();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $manufacturer = trim($_POST['manufacturer'] ?? '');
    $batch_number = trim($_POST['batch_number'] ?? '');
    $expiry_date = $_POST['expiry_date'] ?? '';
    $price = (float) ($_POST['price'] ?? 0);
    $stock_quantity = (int) ($_POST['stock_quantity'] ?? 0);

    if (empty($name) || empty($category) || empty($expiry_date) || $price <= 0) {
        $error = 'Please fill in all required fields and ensure price is greater than 0.';
    } else {
        try {
            $stmt = $db->prepare("
                INSERT INTO medicines (name, category, manufacturer, batch_number, expiry_date, price, stock_quantity)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $category, $manufacturer, $batch_number, $expiry_date, $price, $stock_quantity]);
            $success = 'Medicine added successfully!';
        } catch (PDOException $e) {
            $error = 'Error adding medicine: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Add Medicine – PharmaCare';
$cssPath = '../';
$rootPath = '../';
include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <div>
            <h1 class="page-title">➕ Add New Medicine</h1>
            <p class="page-subtitle">Enter details to add to inventory.</p>
        </div>
        <a href="manage_medicines.php" class="btn btn-secondary">Back to List</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Medicine Name *</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="category">Category *</label>
                        <input type="text" name="category" id="category" class="form-control"
                            placeholder="e.g. Antibiotics, Pain Relief" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="manufacturer">Manufacturer</label>
                        <input type="text" name="manufacturer" id="manufacturer" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="batch_number">Batch Number</label>
                        <input type="text" name="batch_number" id="batch_number" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="expiry_date">Expiry Date *</label>
                        <input type="date" name="expiry_date" id="expiry_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="price">Price (₹) *</label>
                        <input type="number" step="0.01" name="price" id="price" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="stock_quantity">Stock Quantity *</label>
                        <input type="number" name="stock_quantity" id="stock_quantity" class="form-control" value="0"
                            required>
                    </div>
                </div>

                <div style="margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary btn-lg">Save Medicine</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>