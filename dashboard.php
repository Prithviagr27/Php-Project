<?php
require_once 'functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Get inventory statistics
$stats = getInventoryStats($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Inventory System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body style="display: block; background: #f5f5f5;">
    <div class="container">
        <div class="dashboard-container">
            <!-- Navigation -->
            <nav class="navbar">
                <div class="navbar-brand">🏪 Inventory System</div>
                <div class="navbar-menu">
                    <a href="dashboard.php" class="active">Dashboard</a>
                    <a href="products.php">Products</a>
                    <a href="profile.php">Profile</a>
                    <a href="logout.php">Logout</a>
                </div>
            </nav>
            
            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <h1 class="page-title">Welcome back, <?php echo sanitizeOutput($user_name); ?>! 👋</h1>
                
                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Total Products</h3>
                        <div class="value"><?php echo $stats['total_products']; ?></div>
                    </div>
                    
                    <div class="stat-card warning">
                        <h3>Low Stock Items</h3>
                        <div class="value"><?php echo $stats['low_stock_items']; ?></div>
                    </div>
                    
                    <div class="stat-card success">
                        <h3>Total Inventory Value</h3>
                        <div class="value">$<?php echo number_format($stats['total_value'], 2); ?></div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div style="margin-top: 40px;">
                    <h2 style="margin-bottom: 20px; color: #333;">Quick Actions</h2>
                    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <a href="products.php" class="btn btn-primary">View All Products</a>
                        <a href="products.php#add-product" class="btn btn-success">Add New Product</a>
                        <a href="profile.php" class="btn btn-secondary">View Profile</a>
                    </div>
                </div>
                
                <!-- Recent Activity or Tips -->
                <div style="margin-top: 40px; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h2 style="margin-bottom: 20px; color: #333;">📊 Inventory Tips</h2>
                    <ul style="line-height: 2; color: #666;">
                        <li>Keep your inventory updated regularly to avoid stock issues</li>
                        <li>Monitor low stock items and reorder before running out</li>
                        <li>Use categories to organize your products efficiently</li>
                        <li>Review your inventory value monthly for better financial planning</li>
                        <li>Set reorder points for critical items to maintain availability</li>
                    </ul>
                </div>
                
                <?php if ($stats['low_stock_items'] > 0): ?>
                <div style="margin-top: 20px;">
                    <div class="alert alert-danger">
                        ⚠️ You have <strong><?php echo $stats['low_stock_items']; ?></strong> item(s) with low stock. 
                        <a href="products.php" style="color: #721c24; font-weight: bold; text-decoration: underline;">View them now</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>