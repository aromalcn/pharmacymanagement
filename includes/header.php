<?php
/**
 * includes/header.php
 * Common HTML header included at the top of every page.
 * The $pageTitle variable should be set BEFORE including this file.
 */

// Start or resume a session (used for admin authentication)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Default page title if none provided
$pageTitle = $pageTitle ?? 'PharmaCare - Online Medicine Ordering';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= $cssPath ?? '../' ?>css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

    <!-- ===== MAIN NAV HEADER ===== -->
    <header class="site-header">
        <div class="container header-inner">
            <a href="<?= $rootPath ?? '../' ?>index.php" class="logo">
                💊 PharmaCare
            </a>
            <nav class="main-nav">
                <?php if (isset($_SESSION['admin_id'])): ?>
                    <!-- Admin is logged in – show admin navigation -->
                    <a href="<?= $rootPath ?? '../' ?>admin/dashboard.php">Dashboard</a>
                    <a href="<?= $rootPath ?? '../' ?>admin/manage_medicines.php">Medicines</a>
                    <a href="<?= $rootPath ?? '../' ?>admin/view_orders.php">Orders</a>
                    <a href="<?= $rootPath ?? '../' ?>admin/logout.php" class="btn btn-danger btn-sm">Logout</a>
                <?php else: ?>
                    <!-- Public / Customer navigation -->
                    <a href="<?= $rootPath ?? '../' ?>index.php">Browse Medicines</a>
                    <a href="<?= $rootPath ?? '../' ?>admin/admin_login.php" class="btn btn-primary btn-sm">Admin Login</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Page content starts here (closed by footer.php) -->
    <main class="main-content">