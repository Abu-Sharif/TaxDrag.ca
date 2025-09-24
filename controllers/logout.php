<?php 
require_once '../models/login_db.php';

# connect to the database
$login_db = new LoginDB();

# start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// include anonymous user tracking
require_once '../uuid_tracking.php';

# logout the user
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    # destroy the session
    session_destroy();
}

# redirect to the login page
header('Location: ../view/login_form.php');
exit();
?>