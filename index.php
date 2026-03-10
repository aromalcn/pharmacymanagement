<?php
/**
 * index.php
 * Customer home page – listing available (non-expired and in-stock) medicines.
 * Supports Search and Filter by Category.
 */
require_once 'config/database.php';
$db = getDB();

// Filters from Query String
$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');

// Build Query
// Rule: Expired medicines must NOT appear, zero stock must NOT appear.
$params = [];
$sql = "SELECT * FROM medicines WHERE expiry_date > date('now') AND stock_quantity > 0";

if ($search) {
    $sql .= " AND name LIKE ?";
    $params[] = "%$search%";
}

if ($category) {
    $sql .= " AND category = ?";
    $params[] = $category;
}

$sql .= " ORDER BY name ASC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$medicines = $stmt->fetchAll();

// Get unique categories for the filter dropdown
$categories = $db->query("SELECT DISTINCT category FROM medicines WHERE expiry_date > date('now') AND stock_quantity > 0")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'Home – PharmaCare Online Medicine';
$cssPath = '';
$rootPath = '';
include 'includes/header.php';
?>

<!-- ===== HERO SECTON ===== -->
<div class="hero">
    <div class="container">
        <h1>Welcome to PharmaCare</h1>
        <p>Order your medicines online with ease. Trusted pharmacy inventory, real-time availability, and doorstep
            delivery.</p>

        <form action="index.php" method="GET" class="filter-bar">
            <input type="text" name="search" class="form-control" placeholder="Search medicine name..."
                value="<?= htmlspecialchars($search)?>">
            <select name="category" class="form-control">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat)?>" <?=($category===$cat) ? 'selected' : ''?>>
                    <?= htmlspecialchars($cat)?>
                </option>
                <?php
endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">🔍 Search</button>
            <?php if ($search || $category): ?>
            <a href="index.php" class="btn btn-secondary">Clear</a>
            <?php
endif; ?>
        </form>
    </div>
</div>

<div class="container">
    <div class="page-header">
        <h2 class="page-title">
            <?=(empty($medicines)) ? 'No Medicines Found' : 'Available Medicines'?>
        </h2>
    </div>

    <?php if (empty($medicines)): ?>
    <div class="empty-state">
        <div class="empty-icon">💊</div>
        <p>Our apologies, we couldn't find any medicines matching your criteria.</p>
    </div>
    <?php
else: ?>
    <div class="medicines-grid">
        <?php foreach ($medicines as $med): ?>
        <div class="medicine-card">
            <div class="medicine-card-header">
                <div class="med-icon">💊</div>
                <h3>
                    <?= htmlspecialchars($med['name'])?>
                </h3>
            </div>
            <div class="medicine-card-body">
                <p class="meta"><strong>Category:</strong>
                    <?= htmlspecialchars($med['category'])?>
                </p>
                <p class="meta"><strong>Manufacturer:</strong>
                    <?= htmlspecialchars($med['manufacturer'])?>
                </p>
                <div class="price">₹
                    <?= number_format($med['price'], 2)?>
                </div>
            </div>
            <div class="medicine-card-footer">
                <span class="badge badge-success">IN STOCK</span>
                <a href="medicine_details.php?id=<?= $med['id']?>" class="btn btn-sm btn-outline">Order Now →</a>
            </div>
        </div>
        <?php
    endforeach; ?>
    </div>
    <?php
endif; ?>
</div>

<?php include 'includes/footer.php'; ?>