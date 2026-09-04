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

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $new_password = !empty($_POST['new_password']) ? $_POST['new_password'] : null;
    
    // Validate password confirmation if new password is provided
    if ($new_password && $new_password !== $_POST['confirm_password']) {
        $message = "Passwords do not match";
        $messageType = "danger";
    } else {
        $result = updateUserProfile($user_id, $full_name, $phone, $new_password);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'danger';
    }
}

// Get user information
$user = getUserInfo($user_id);

// Get inventory stats
$stats = getInventoryStats($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Inventory System</title>
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
                    <a href="products.php">Products</a>
                    <a href="profile.php" class="active">Profile</a>
                    <a href="logout.php">Logout</a>
                </div>
            </nav>
            
            <!-- Profile Content -->
            <div class="dashboard-content">
                <h1 class="page-title">My Profile</h1>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>" id="alertMessage">
                        <?php echo sanitizeOutput($message); ?>
                    </div>
                <?php endif; ?>
                
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                        </div>
                        <h2><?php echo sanitizeOutput($user['full_name']); ?></h2>
                        <p style="color: #666;"><?php echo sanitizeOutput($user['email']); ?></p>
                    </div>
                    
                    <div class="profile-info">
                        <h3 style="margin-bottom: 20px; color: #333;">Account Information</h3>
                        
                        <div class="info-row">
                            <div class="info-label">Full Name:</div>
                            <div class="info-value"><?php echo sanitizeOutput($user['full_name']); ?></div>
                        </div>
                        
                        <div class="info-row">
                            <div class="info-label">Email:</div>
                            <div class="info-value"><?php echo sanitizeOutput($user['email']); ?></div>
                        </div>
                        
                        <div class="info-row">
                            <div class="info-label">Phone:</div>
                            <div class="info-value"><?php echo $user['phone'] ? sanitizeOutput($user['phone']) : 'Not provided'; ?></div>
                        </div>
                        
                        <div class="info-row">
                            <div class="info-label">Member Since:</div>
                            <div class="info-value"><?php echo date('F d, Y', strtotime($user['created_at'])); ?></div>
                        </div>
                        
                        <div class="info-row">
                            <div class="info-label">Total Products:</div>
                            <div class="info-value"><?php echo $stats['total_products']; ?></div>
                        </div>
                        
                        <div class="info-row" style="border-bottom: none;">
                            <div class="info-label">Inventory Value:</div>
                            <div class="info-value">$<?php echo number_format($stats['total_value'], 2); ?></div>
                        </div>
                    </div>
                    
                    <button class="btn btn-primary" onclick="openEditModal()" style="width: 100%;">Edit Profile</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Profile Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Profile</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" action="" id="profileForm">
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" 
                               value="<?php echo sanitizeOutput($user['full_name']); ?>" required>
                        <span class="error-message" id="nameError"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control" 
                               value="<?php echo sanitizeOutput($user['phone']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password (leave blank to keep current)</label>
                        <input type="password" id="new_password" name="new_password" class="form-control">
                        <span class="error-message" id="passwordError"></span>
                        <small style="color: #666; font-size: 12px;">Min 8 characters, 1 uppercase, 1 lowercase, 1 number</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                        <span class="error-message" id="confirmPasswordError"></span>
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>
    </div>
    
    <script src="validation.js"></script>
    <script>
        function openEditModal() {
            document.getElementById('editModal').classList.add('show');
        }
        
        function closeModal() {
            document.getElementById('editModal').classList.remove('show');
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                closeModal();
            }
        }
        
        // Form validation
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            let isValid = true;
            
            const fullName = document.getElementById('full_name');
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            
            const nameError = document.getElementById('nameError');
            const passwordError = document.getElementById('passwordError');
            const confirmPasswordError = document.getElementById('confirmPasswordError');
            
            // Reset errors
            fullName.classList.remove('error');
            newPassword.classList.remove('error');
            confirmPassword.classList.remove('error');
            nameError.classList.remove('show');
            passwordError.classList.remove('show');
            confirmPasswordError.classList.remove('show');
            
            // Validate full name
            if (fullName.value.trim().length < 3) {
                fullName.classList.add('error');
                nameError.textContent = 'Full name must be at least 3 characters';
                nameError.classList.add('show');
                isValid = false;
            }
            
            // Validate password if provided
            if (newPassword.value.trim() !== '') {
                const passwordValidation = validatePassword(newPassword.value);
                if (!passwordValidation.valid) {
                    newPassword.classList.add('error');
                    passwordError.textContent = passwordValidation.message;
                    passwordError.classList.add('show');
                    isValid = false;
                }
                
                // Validate confirm password
                if (newPassword.value !== confirmPassword.value) {
                    confirmPassword.classList.add('error');
                    confirmPasswordError.textContent = 'Passwords do not match';
                    confirmPasswordError.classList.add('show');
                    isValid = false;
                }
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
    </script>
</body>
</html>