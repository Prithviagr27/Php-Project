<?php
require_once 'functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

// Handle signup form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup'])) {
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = $_POST['phone'] ?? '';
    
    // Validate inputs
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all required fields";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        $result = registerUser($full_name, $email, $password, $phone);
        
        if ($result['success']) {
            $success = $result['message'] . ". You can now login.";
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
    <title>Sign Up - Inventory System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h1>🏪 Inventory System</h1>
            <p>Create your account</p>
        </div>
        
        <div class="auth-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo sanitizeOutput($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo sanitizeOutput($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" id="signupForm">
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" required>
                    <span class="error-message" id="nameError"></span>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                    <span class="error-message" id="emailError"></span>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" placeholder="Optional">
                    <span class="error-message" id="phoneError"></span>
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                    <span class="error-message" id="passwordError"></span>
                    <small style="color: #666; font-size: 12px;">Min 8 characters, 1 uppercase, 1 lowercase, 1 number</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    <span class="error-message" id="confirmPasswordError"></span>
                </div>
                
                <button type="submit" name="signup" class="btn btn-primary">Sign Up</button>
            </form>
        </div>
        
        <div class="auth-footer">
            <p>Already have an account? <a href="index.php">Login</a></p>
        </div>
    </div>
    
    <script src="validation.js"></script>
    <script>
        // Signup form validation
        document.getElementById('signupForm').addEventListener('submit', function(e) {
            let isValid = true;
            
            const fullName = document.getElementById('full_name');
            const email = document.getElementById('email');
            const phone = document.getElementById('phone');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            const nameError = document.getElementById('nameError');
            const emailError = document.getElementById('emailError');
            const phoneError = document.getElementById('phoneError');
            const passwordError = document.getElementById('passwordError');
            const confirmPasswordError = document.getElementById('confirmPasswordError');
            
            // Reset errors
            [fullName, email, phone, password, confirmPassword].forEach(field => {
                field.classList.remove('error');
            });
            [nameError, emailError, phoneError, passwordError, confirmPasswordError].forEach(error => {
                error.classList.remove('show');
            });
            
            // Validate full name
            if (fullName.value.trim().length < 3) {
                fullName.classList.add('error');
                nameError.textContent = 'Full name must be at least 3 characters';
                nameError.classList.add('show');
                isValid = false;
            }
            
            // Validate email
            if (!validateEmail(email.value)) {
                email.classList.add('error');
                emailError.textContent = 'Please enter a valid email address';
                emailError.classList.add('show');
                isValid = false;
            }
            
            // Validate phone (if provided)
            if (phone.value.trim() !== '' && !validatePhone(phone.value)) {
                phone.classList.add('error');
                phoneError.textContent = 'Please enter a valid phone number';
                phoneError.classList.add('show');
                isValid = false;
            }
            
            // Validate password
            const passwordValidation = validatePassword(password.value);
            if (!passwordValidation.valid) {
                password.classList.add('error');
                passwordError.textContent = passwordValidation.message;
                passwordError.classList.add('show');
                isValid = false;
            }
            
            // Validate confirm password
            if (password.value !== confirmPassword.value) {
                confirmPassword.classList.add('error');
                confirmPasswordError.textContent = 'Passwords do not match';
                confirmPasswordError.classList.add('show');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>