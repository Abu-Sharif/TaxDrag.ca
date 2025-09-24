<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

# check if user is already logged in - redirect to dashboard if they are
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header('Location: ../view/dashboard.php');
    exit();
}

// set page-specific seo variables
$page_title = 'Sign Up | TaxDrag';
$page_description = 'Create your TaxDrag account to start calculating foreign withholding tax drag on your Canadian investments.';

include 'header.php'; 
?>

<main>
    <div class="container">
        
        <!-- page header -->
        <div class="page-header">
            <h1>Sign Up</h1>
            <p>Create your account</p>
        </div>
        
        <!-- signup card -->
        <div class="input-area auth-card">
            <!-- display error message if there is one -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error">
                    <?php 
                    echo htmlspecialchars($_SESSION['error']); 
                    unset($_SESSION['error']); // clear the error after displaying
                    ?>
                </div>
            <?php endif; ?>
            
            <form action="../controllers/signup.php" method="post">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                
                <button type="submit" class="btn">Register</button>
            </form>
            
            <br>
            <div>
                <p>Already have an account? <a href="../view/login_form.php">Login</a></p>
            </div>
        </div>

    </div>
</main>
<?php include 'footer.php'; ?>