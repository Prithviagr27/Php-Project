<?php
require_once 'functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$messageType = '';

// Handle Add Product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $result = addProduct(
        $user_id,
        $_POST['product_name'],
        $_POST['category'],
        $_POST['quantity'],
        $_POST['price'],
        $_POST['description']
    );
    
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'danger';
}

// Handle Update Product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product'])) {
    $result = updateProduct(
        $_POST['product_id'],
        $user_id,
        $_POST['product_name'],
        $_POST['category'],
        $_POST['quantity'],
        $_POST['price'],
        $_POST['description']
    );
    
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'danger';
}

// Handle Delete Product
if (isset($_GET['delete'])) {
    $result = deleteProduct($_GET['delete'], $user_id);
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'danger';
}

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';

// Get all products
$products = getAllProducts($user_id, $search, $category_filter);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Inventory System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body style="display: block; background: #f5f5f5;">
    <div class="container">
        <div class="dashboard-container">
            <!-- Navigation -->
            <nav class="navbar">
                <div class="navbar-brand">🏪 Inventory System</div>
                <div class="navbar-menu">
                    <a href="dashboard.php">Dashboard</a>
                    <a href="products.php" class="active">Products</a>
                    <a href="profile.php">Profile</a>
                    <a href="logout.php">Logout</a>
                </div>
            </nav>
            
            <!-- Products Content -->
            <div class="dashboard-content">
                <h1 class="page-title">Product Management</h1>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>" id="alertMessage">
                        <?php echo sanitizeOutput($message); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Products Table -->
                <div class="table-container">
                    <div class="table-header">
                        <div class="search-filter">
                            <input type="text" id="searchInput" placeholder="Search products..." value="<?php echo sanitizeOutput($search); ?>">
                            <select id="categoryFilter">
                                <option value="">All Categories</option>
                                <option value="Electronics" <?php echo $category_filter == 'Electronics' ? 'selected' : ''; ?>>Electronics</option>
                                <option value="Clothing" <?php echo $category_filter == 'Clothing' ? 'selected' : ''; ?>>Clothing</option>
                                <option value="Food" <?php echo $category_filter == 'Food' ? 'selected' : ''; ?>>Food</option>
                                <option value="Furniture" <?php echo $category_filter == 'Furniture' ? 'selected' : ''; ?>>Furniture</option>
                                <option value="Other" <?php echo $category_filter == 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <button class="btn btn-success" onclick="openAddModal()">+ Add Product</button>
                    </div>
                    
                    <?php if (count($products) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total Value</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo $product['id']; ?></td>
                                    <td><?php echo sanitizeOutput($product['product_name']); ?></td>
                                    <td><?php echo sanitizeOutput($product['category']); ?></td>
                                    <td class="<?php echo $product['quantity'] < 10 ? 'low-stock' : ''; ?>">
                                        <?php echo $product['quantity']; ?>
                                        <?php if ($product['quantity'] < 10): ?>
                                            ⚠️
                                        <?php endif; ?>
                                    </td>
                                    <td>$<?php echo number_format($product['price'], 2); ?></td>
                                    <td>$<?php echo number_format($product['quantity'] * $product['price'], 2); ?></td>
                                    <td>
                                        <div class="actions">
                                            <button class="btn btn-secondary btn-sm" onclick='editProduct(<?php echo json_encode($product); ?>)'>Edit</button>
                                            <button class="btn btn-danger btn-sm" onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo sanitizeOutput($product['product_name']); ?>')">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty-state">
                        <h3>No products found</h3>
                        <p>Start by adding your first product</p>
                        <button class="btn btn-success" onclick="openAddModal()">+ Add Product</button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add/Edit Product Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Product</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" action="" id="productForm">
                    <input type="hidden" id="product_id" name="product_id">
                    <input type="hidden" id="form_action" name="add_product" value="1">
                    
                    <div class="form-group">
                        <label for="product_name">Product Name *</label>
                        <input type="text" id="product_name" name="product_name" class="form-control" required>
                        <span class="error-message" id="nameError"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" class="form-control" required>
                            <option value="">Select Category</option>
                            <option value="Electronics">Electronics</option>
                            <option value="Clothing">Clothing</option>
                            <option value="Food">Food</option>
                            <option value="Furniture">Furniture</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="quantity">Quantity *</label>
                        <input type="number" id="quantity" name="quantity" class="form-control" min="0" required>
                        <span class="error-message" id="quantityError"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price ($) *</label>
                        <input type="number" id="price" name="price" class="form-control" min="0" step="0.01" required>
                        <span class="error-message" id="priceError"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" id="submitBtn">Add Product</button>
                </form>
            </div>
        </div>
    </div>
    
    <script src="validation.js"></script>
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            filterProducts();
        });
        
        document.getElementById('categoryFilter').addEventListener('change', function() {
            filterProducts();
        });
        
        function filterProducts() {
            const search = document.getElementById('searchInput').value;
            const category = document.getElementById('categoryFilter').value;
            window.location.href = `products.php?search=${encodeURIComponent(search)}&category=${encodeURIComponent(category)}`;
        }
        
        // Modal functions
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Add New Product';
            document.getElementById('productForm').reset();
            document.getElementById('product_id').value = '';
            document.getElementById('form_action').name = 'add_product';
            document.getElementById('submitBtn').textContent = 'Add Product';
            document.getElementById('productModal').classList.add('show');
        }
        
        function editProduct(product) {
            document.getElementById('modalTitle').textContent = 'Edit Product';
            document.getElementById('product_id').value = product.id;
            document.getElementById('product_name').value = product.product_name;
            document.getElementById('category').value = product.category;
            document.getElementById('quantity').value = product.quantity;
            document.getElementById('price').value = product.price;
            document.getElementById('description').value = product.description;
            document.getElementById('form_action').name = 'update_product';
            document.getElementById('submitBtn').textContent = 'Update Product';
            document.getElementById('productModal').classList.add('show');
        }
        
        function closeModal() {
            document.getElementById('productModal').classList.remove('show');
        }
        
        function deleteProduct(id, name) {
            if (confirm(`Are you sure you want to delete "${name}"?`)) {
                window.location.href = `products.php?delete=${id}`;
            }
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('productModal');
            if (event.target == modal) {
                closeModal();
            }
        }
        
        // Form validation
        document.getElementById('productForm').addEventListener('submit', function(e) {
            let isValid = true;
            
            const productName = document.getElementById('product_name');
            const quantity = document.getElementById('quantity');
            const price = document.getElementById('price');
            
            // Reset errors
            productName.classList.remove('error');
            quantity.classList.remove('error');
            price.classList.remove('error');
            
            // Validate product name
            if (productName.value.trim().length < 3) {
                productName.classList.add('error');
                document.getElementById('nameError').textContent = 'Product name must be at least 3 characters';
                document.getElementById('nameError').classList.add('show');
                isValid = false;
            }
            
            // Validate quantity
            if (quantity.value < 0) {
                quantity.classList.add('error');
                document.getElementById('quantityError').textContent = 'Quantity cannot be negative';
                document.getElementById('quantityError').classList.add('show');
                isValid = false;
            }
            
            // Validate price
            if (price.value < 0) {
                price.classList.add('error');
                document.getElementById('priceError').textContent = 'Price cannot be negative';
                document.getElementById('priceError').classList.add('show');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
        
        // Auto-hide alert after 5 seconds
        setTimeout(function() {
            const alert = document.getElementById('alertMessage');
            if (alert) {
                alert.style.display = 'none';
            }
        }, 5000);
        
        // Open modal if hash is present
        if (window.location.hash === '#add-product') {
            openAddModal();
        }
    </script>
</body>
</html>