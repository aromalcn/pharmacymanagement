<?php
/**
 * medicine_details.php
 * Shows individual medicine details and quantity selection before ordering.
 */
require_once 'config/database.php';
$db = getDB();

$id = (int)($_GET['id'] ?? 0);

// Fetch Medicine (Ensuring it's not expired or out of stock)
$stmt = $db->prepare("SELECT * FROM medicines WHERE id = ? AND expiry_date > date('now') AND stock_quantity > 0");
$stmt->execute([$id]);
$med = $stmt->fetch();

if (!$med) {
    header('Location: index.php');
    exit;
}

$pageTitle = htmlspecialchars($med['name']) . ' – PharmaCare';
$cssPath = '';
$rootPath = '';
include 'includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1 class="page-title">
            <?= htmlspecialchars($med['name'])?>
        </h1>
        <a href="index.php" class="btn btn-secondary">← Back to List</a>
    </div>

    <div class="form-row" style="gap:2rem;">
        <div class="card" style="flex:1;">
            <div class="card-body">
                <div style="font-size: 3rem; margin-bottom: 1rem;">💊</div>
                <p><strong>Category:</strong>
                    <?= htmlspecialchars($med['category'])?>
                </p>
                <p><strong>Manufacturer:</strong>
                    <?= htmlspecialchars($med['manufacturer'])?>
                </p>
                <p><strong>Batch Number:</strong>
                    <?= htmlspecialchars($med['batch_number'])?>
                </p>
                <p><strong>Expiry Date:</strong>
                    <?= date('d M Y', strtotime($med['expiry_date']))?>
                </p>
                <p style="margin-top: 1rem; color: var(--gray-500); font-size: 0.85rem;">
                    * This medicine is verified and currently in stock.
                </p>

                <div class="section-divider"></div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">₹
                    <?= number_format($med['price'], 2)?>
                </div>
            </div>
        </div>

        <div class="card" style="flex:1; max-width: 400px;">
            <div class="card-header">
                <h3>Select Quantity</h3>
            </div>
            <div class="card-body">
                <p style="margin-bottom: 1rem; color: var(--success); font-weight: 600;">✅
                    <?= $med['stock_quantity']?> units available
                </p>

                <form action="order_form.php" method="GET">
                    <input type="hidden" name="med_id" value="<?= $med['id']?>">
                    <div class="form-group">
                        <label for="qty">Quantity</label>
                        <input type="number" id="qty" name="qty" class="form-control" value="1" min="1"
                            max="<?= $med['stock_quantity']?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">Proceed to
                        Checkout</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>