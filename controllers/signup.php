<?php 
require_once '../models/login_db.php';
# connect to the database
$login_db = new LoginDB();

# password validation using regular expression - at least 3 chars, 1 lowercase, 1 uppercase
$password_pattern = '/^(?=.*[a-z])(?=.*[A-Z]).{3,}$/';
# start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// include anonymous user tracking
require_once '../uuid_tracking.php';



# if no form submitted, show the signup form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    include '../view/signup_form.php';
    exit();
}



# get the email and password from the form and do password validation
if (isset($_POST['email']) && isset($_POST['password']) && isset($_POST['username'])) {

    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    # validate password using the pattern, if it doesnt match then display error 
    if (!preg_match($password_pattern, $password)) {
        $_SESSION['error'] = "Password must be at least 3 characters with at least one uppercase and one lowercase letter";
        header('Location: ../view/signup_form.php');
        exit();
    }
}
else{
    header('Location: ../view/signup_form.php?error=1');
    exit(); // exit the script
}

try{
    # check if the email is already in the database, we can reuse our getUserID function, 
    # since it returns the user id, if the email is associated with an account then we can use it to check if the email is already in the database
    $email_result = $login_db->getUserID($email);
    if ($email_result) {
        $_SESSION['error'] = "Email already associated with an account.";
        header('Location: ../view/signup_form.php');
        exit();
    }

    # hash the password
    $hash = password_hash($password, PASSWORD_DEFAULT);
    # register the user in the database
    $login_db->registerUser($email, $hash, $username);

    # login the user
    $user_id_result = $login_db->getUserID($email);
    $_SESSION['user_id'] = $user_id_result['id'];
    $_SESSION['user_logged_in'] = true;
    $_SESSION['username'] = $username; // used later in the dashboard
    # redirect to the dashboard page
    header('Location: ../controllers/calculator.php');
    exit(); // exit the script
}
catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

?> 