<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../uuid_tracking.php';
require_once '../models/portfolio.php';
require_once '../models/tax_calculator.php';
$taxCalculator_db = new TaxCalculator(); // database and model for tax calculations
$db = new Portfolio(); // database and model for portfolio calculations 
$message = ''; // -- might be able to remove this
$error = '';
// check for session messages (from redirects)
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // clear the message after displaying
}
// handle form submission 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // validation checks for calculator form inputs
    if (empty($_POST['selected_portfolio'])) {
        $error = "Please select a portfolio";
    } else {
        // strip commas from initial investment value
        $_POST['initial_investment'] = str_replace(',', '', $_POST['initial_investment']);
        
        // set default value of investment amount if user did not enter a value
        if (empty($_POST['initial_investment']) || $_POST['initial_investment'] <= 0 || $_POST['initial_investment'] > 10000000000) {
            $_POST['initial_investment'] = 10000;
        }
        
        // validate time and set defualt values for timehorizon and compound rate inputs, if compoundinh was selected
        if (isset($_POST['calculation_type']) && $_POST['calculation_type'] === 'compound') {
            if (empty($_POST['time_horizon']) || $_POST['time_horizon'] < 1 || $_POST['time_horizon'] > 50) {
                $_POST['time_horizon'] = 10;
            }
            if (empty($_POST['compound_rate']) || $_POST['compound_rate'] < 0 || $_POST['compound_rate'] > 100) {
                $_POST['compound_rate'] = 7.0;
            }
        }

        // get all holdings for the selected portfolio
        $holdings = $db->getHolding($_POST['selected_portfolio']);
 
        if (empty($holdings)) {
            $error = "No holdings found for this portfolio";
        } else {
            $results = [];
            
            // calculate tax rates and impacts for each holding in one pass
            foreach ($holdings as $holding) {
                $tax_rules = $taxCalculator_db->getWithholdingTaxRate(
                    $holding['account_type'], 
                    $holding['asset_class'], 
                    $holding['exchange'], 
                    $holding['fund_structure']
                );
                
                // calculate the impact of the combined withholding tax for this holding
                $allocation = $holding['allocation_percentage'] / 100;
                $holding_value = $_POST['initial_investment'] * $allocation;
                $trailing_yield_decimal = $holding['trailing_yield'] / 100;
                $FWL_L1 = $tax_rules['FWL_L1'] * $trailing_yield_decimal;
                $FWL_L2 = $tax_rules['FWL_L2'] * ($trailing_yield_decimal - $FWL_L1);
                $combined_FWT = $FWL_L1 + $FWL_L2;
                $impact = $holding_value * $combined_FWT;
                
                $results[] = [
                    'ticker' => $holding['etf_ticker'],
                    'allocation' => $holding['allocation_percentage'],
                    'yield' => $holding['trailing_yield'],
                    'fwl_l1' => $tax_rules['FWL_L1'],
                    'fwl_l2' => $tax_rules['FWL_L2'],
                    'impact' => $impact
                ];
            }
            
            // if compounding was selected
            if (isset($_POST['calculation_type']) && $_POST['calculation_type'] === 'compound') {
                // calculate compound interest with FWT 
                $calculation_result = $taxCalculator_db->calculateCompoundInterestWithFWT(
                    $holdings,
                    $_POST['initial_investment'],
                    $_POST['time_horizon'],
                    $_POST['compound_rate'] / 100
                );
                
                // get the results from the function 
                $total_impact = $calculation_result['total_impact'];
                $total_FWT_paid = $calculation_result['total_FWT_paid'];
                $yearly_breakdown = $calculation_result['yearly_breakdown'];

            // if compounding was not selected
            } else {
                // calculate current year only
                $calculation_result = $taxCalculator_db->calculateCurrentYearFWT(
                    $holdings,
                    $_POST['initial_investment']
                );
                
                // get the function results 
                $total_impact = $calculation_result['total_impact'];
                $total_FWT_paid = $total_impact; // same as current year impact
                $yearly_breakdown = []; // no yearly breakdown for current year only
            }
        }
    }
}

// get all portfolios for dropdown
try {
    if (isset($_SESSION['user_id'])) {
        $portfolios = $db->getAllPortfolios(null, $_SESSION['user_id']);
    } else {
        $portfolios = $db->getAllPortfolios($_SESSION['anon_user'], null);
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    $portfolios = [];
}

// include the view file to display the calculator
include '../view/calculator_form.php';
?>
