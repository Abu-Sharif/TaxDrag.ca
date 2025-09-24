<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

# check if user is already logged in - redirect to calculator controller if they are
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header('Location: ../controllers/calculator.php');
    exit();
}

// set page-specific seo variables
$page_title = 'Login | TaxDrag';
$page_description = 'Login to your TaxDrag account to access your portfolios and tax calculations.';

include 'header.php'; 
?>
<?php if (isset($_SESSION['error'])): ?>
    <div class="error">
        <?php 
        echo htmlspecialchars($_SESSION['error']); 
        unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<main>
    <div class="container">
        
        <!-- page header -->
        <div class="page-header">
            <h1>Welcome Back</h1>
            <p>Login to your account</p>
        </div>
        
        <!-- login card -->
        <div class="input-area auth-card">
            <form action="../controllers/login.php" method="post">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                
                <button type="submit" class="btn">Login</button>
            </form>
            <br>
            <div>
                <p>Don't have an account? <a href="../view/signup_form.php">Sign up</a></p>
            </div>
        </div>

    </div>
</main>
<?php include 'footer.php'; ?>