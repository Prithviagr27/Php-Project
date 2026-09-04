<?php
    require_once 'functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

// Handle session timeout
if (isset($_GET['timeout'])) {
    $error = "Your session has expired. Please login again.";
}

// Handle logout
if (isset($_GET['logout'])) {
    $success = "You have been logged out successfully.";
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields";
    } else {
        $result = loginUser($email, $password);
        
        if ($result['success']) {
            header("Location: dashboard.php");
            exit();
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Inventory System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h1>🏪 Inventory System</h1>
            <p>Login to manage your products</p>
        </div>
        
        <div class="auth-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo sanitizeOutput($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo sanitizeOutput($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" id="loginForm">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                    <span class="error-message" id="emailError"></span>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                    <span class="error-message" id="passwordError"></span>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember Me</label>
                </div>
                
                <button type="submit" name="login" class="btn btn-primary">Login</button>
            </form>
        </div>
        
        <div class="auth-footer">
            <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
           
        </div>
    </div>
    
    <script src="validation.js"></script>
    <script>
        // Login form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            let isValid = true;
            
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            const emailError = document.getElementById('emailError');
            const passwordError = document.getElementById('passwordError');
            
            // Reset errors
            email.classList.remove('error');
            password.classList.remove('error');
            emailError.classList.remove('show');
            passwordError.classList.remove('show');
            
            // Validate email
            if (!validateEmail(email.value)) {
                email.classList.add('error');
                emailError.textContent = 'Please enter a valid email address';
                emailError.classList.add('show');
                isValid = false;
            }
            
            // Validate password
            if (password.value.trim() === '') {
                password.classList.add('error');
                passwordError.textContent = 'Password is required';
                passwordError.classList.add('show');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>