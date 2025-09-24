<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../uuid_tracking.php';
require_once '../models/portfolio.php';
require_once '../models/tax_calculator.php';

$portfolio_db = new Portfolio();
$tax_calculator = new TaxCalculator();
$message = '';
$error = '';
$comparison_results = [];

// get all portfolios for the dropdowns
if (isset($_SESSION['user_id'])) {
    $portfolios = $portfolio_db->getAllPortfolios(null, $_SESSION['user_id']);
} else {
    $portfolios = $portfolio_db->getAllPortfolios($_SESSION['anon_user'], null);
}

// handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['compare_portfolios'])) {
    // validate inputs
    if (empty($_POST['portfolio1_id']) || empty($_POST['portfolio2_id']) || 
        empty($_POST['growth_rate']) || empty($_POST['time_horizon'])) {
        $error = "All fields are required";
    } else {
        $portfolio1_id = $_POST['portfolio1_id'];
        $portfolio2_id = $_POST['portfolio2_id'];
        $growth_rate = floatval($_POST['growth_rate']);
        $time_horizon = intval($_POST['time_horizon']);
        $initial_investment = 100000; // fixed at $100k
        
        try {
            // get holdings for both portfolios
            $holdings1 = $portfolio_db->getHolding($portfolio1_id);
            $holdings2 = $portfolio_db->getHolding($portfolio2_id);
            
            // get portfolio names
            $portfolio1_name = '';
            $portfolio2_name = '';
            foreach ($portfolios as $portfolio) {
                if ($portfolio['id'] == $portfolio1_id) {
                    $portfolio1_name = $portfolio['name'];
                }
                if ($portfolio['id'] == $portfolio2_id) {
                    $portfolio2_name = $portfolio['name'];
                }
            }
            
            // calculate tax drag for both portfolios
            $results1 = $tax_calculator->calculateTaxDrag($holdings1, $initial_investment, $growth_rate, $time_horizon);
            $results2 = $tax_calculator->calculateTaxDrag($holdings2, $initial_investment, $growth_rate, $time_horizon);
            
            // store comparison results
            $comparison_results = [
                'portfolio1' => [
                    'name' => $portfolio1_name,
                    'results' => $results1
                ],
                'portfolio2' => [
                    'name' => $portfolio2_name,
                    'results' => $results2
                ],
                'time_horizon' => $time_horizon,
                'growth_rate' => $growth_rate,
                'initial_investment' => $initial_investment
            ];
            
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// display the form
include '../view/compare_form.php';
?>
