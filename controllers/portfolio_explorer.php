<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../uuid_tracking.php';
require_once '../models/portfolio.php';

$db = new Portfolio(); 
$message = '';
$error = '';

// check for session messages (from redirects)
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // clear the message after displaying
}

// get all portfolios including the ones the user built
try {
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != null) {
        // get portfolios for logged-in user (their portfolios + prebuilt)
        $portfolios = $db->getAllPortfoliosWithDetails(null, $_SESSION['user_id']);
    } else {
        // get portfolios for anonymous user (their portfolios + prebuilt)
        $portfolios = $db->getAllPortfoliosWithDetails($_SESSION['anon_user'], null);
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    $portfolios = [];
}


// handle delete portfolio action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_portfolio'])) {
    $portfolioId = $_POST['portfolio_id'];
    
    // only allow deletion if user is logged in
    if (isset($_SESSION['user_id'])) {
        try {
            $db->deletePortfolio($portfolioId);
            $_SESSION['message'] = "Portfolio deleted successfully!";
            header("Location: portfolio_explorer.php");
            exit();
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } else {
        $error = "Cannot delete prebuilt portfolios.";
    }
}

// include the view file to display the interface
include '../view/portfolio_explorer.php';
?>
    