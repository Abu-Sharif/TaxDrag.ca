<!-- start the session if its not already started -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// include anonymous user tracking
require_once '../uuid_tracking.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Canada Withholding Tax Calculator | TaxDrag'; ?></title>
    <meta name="description" content="<?php echo isset($page_description) ? $page_description : 'Easily calculate foreign withholding tax drag on your Canadian investments with our free calculator.'; ?>">
    <link rel="icon" type="image/png" href="../images/logo.jpg">
    <!-- JQuery  -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" 
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="../styles/tool_tip_styles.css"> 

</head>
<body>
    <header>
        <!-- logo on the left -->
        <div class="logo-container">
            <a href="../controllers/calculator.php"><img src="../images/logo.jpg" alt="Canada Withholding Tax Calculator" class="header-logo"></a>
        </div>
        
        <nav class="navbar">
            <div class="nav-container">
                <button class="burger-menu" onclick="toggleNav()">☰</button>
                <ul class="nav-links" id="navLinks">
                    <li><a href="../controllers/calculator.php">Calculator</a></li>
                    <li><a href="../controllers/compare.php">Compare</a></li>
                    <li><a href="../controllers/portfolio_builder.php">Portfolio Builder</a></li>
                    <li><a href="../controllers/portfolio_explorer.php">Portfolio Explorer</a></li>
                </ul>
            </div>
        </nav>

         <!-- if user isn't logged in, show login, otherwise show logout -->
        <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
            <form method="POST" action="../controllers/logout.php">
                <button type="submit" name="logout" class="btn-login" >Logout as <?php echo strtolower(htmlspecialchars($_SESSION['username'])); ?></button>
            </form>
        <?php else: ?>
            <form method="POST" action="../view/login_form.php">
                <button type="submit" name="login" >Login</button>
            </form>
        <?php endif; ?>
    </header>
    
    <script>
        function toggleNav() {
            var navLinks = document.getElementById('navLinks');
            navLinks.classList.toggle('show');
        }
    </script>
    