<?php 
require_once '../uuid_tracking.php';
require_once '../models/login_db.php';

# connect to the database
$login_db = new LoginDB();

# added a session cookie to expire when browser closes 
session_set_cookie_params(0);

# start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// include anonymous user tracking
require_once '../uuid_tracking.php';

// check if login form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // get the email and password from the form
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // validate inputs
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Please enter both email and password";
        header('Location: ../view/login_form.php');
        exit();
    }
    
    try {
        // check if the user exists in the database
        $hash_result = $login_db->returnPasswordHash($email);
        
        if ($hash_result) {
            # verify the password, if its correct then update session variables (ie log them in)
            if (password_verify($password, $hash_result[0]['password_hash'])) {
                $user_id_result = $login_db->getUserID($email);
                $username_result = $login_db->getUsername($email);
                $_SESSION['user_id'] = $user_id_result['id'];
                $_SESSION['user_logged_in'] = true;
                $_SESSION['username'] = $username_result['username'];
                header('Location: ../controllers/calculator.php');
                exit();
        }   else {
                $_SESSION['error'] = "Invalid email or password";
                header('Location: ../view/login_form.php');
                exit();
        }
        }
        // the user does not exist 
        else{
            $_SESSION['error'] = "Invalid email or password";
            header('Location: ../view/login_form.php');
            exit();
        }     
    } catch (Exception $e) {
        $_SESSION['error'] = "Login error: " . $e->getMessage();
        header('Location: ../view/login_form.php');
        exit();
    }
} else {
    # if not a request, show the login form
    include '../view/login_form.php';
    exit();
}
?> 