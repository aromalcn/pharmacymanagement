<?php
/**
 * order_success.php
 * Final confirmation page shown after a successful checkout.
 */
require_once 'config/database.php';
$db = getDB();

$id = (int) ($_GET['id'] ?? 0);

$pageTitle = 'Order Placed – PharmaCare';
$cssPath = '';
$rootPath = '';
include 'includes/header.php';
?>

<div class="container">
    <div class="success-box card">
        <div class="card-body">
            <div class="check">🎉</div>
            <h2>Order Placed Successfully!</h2>
            <p>Thank you for choosing PharmaCare. Your order <strong>#
                    <?= $id ?>
                </strong> has been received and is current being processed.</p>
            <p style="margin-top: 1rem; font-size: 0.9rem; color: var(--gray-500);">
                A pharmacist will review your order shortly. You can pay via your selected method upon receiving or
                picking up your medicines.
            </p>
            <div class="section-divider"></div>
            <div class="btn-group" style="justify-content: center;">
                <a href="index.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>