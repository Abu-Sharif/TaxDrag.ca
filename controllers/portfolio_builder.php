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

// if the session is not set, set it to an empty array
if (!isset($_SESSION['current_holdings'])) {
    $_SESSION['current_holdings'] = array();
}

// handle form submission (add holding, create portfolio, or reset portfolio)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // check if user clicked "Add Holding" button
    if (isset($_POST['add_holding'])) {
        // validate the current holding data
        if (!empty($_POST['etf_ticker']) && !empty($_POST['allocation_percentage']) && !empty($_POST['account_type']) 
        && !empty($_POST['asset_class']) && !empty($_POST['exchange']) && !empty($_POST['fund_structure']) && !empty($_POST['trailing_yield'])) {
            // store portfolio name in session if provided so it doesn't reset when we clear the form
            if (!empty($_POST['portfolio_name'])) {
                $_SESSION['portfolio_name'] = $_POST['portfolio_name'];
            }
                
            // add the holding to our session array 
            $_SESSION['current_holdings'][] = [
                'asset_class' => $_POST['asset_class'],
                'etf_ticker' => strtoupper($_POST['etf_ticker']),
                'allocation_percentage' => $_POST['allocation_percentage'],
                'account_type' => $_POST['account_type'],
                'exchange' => $_POST['exchange'],
                'fund_structure' => $_POST['fund_structure'],
                'trailing_yield' => $_POST['trailing_yield']
            ];
            $_SESSION['message'] = "Holding added! Add another or click 'create portfolio'.";
                
            // redirect to prevent form resubmission on page reload
            header("Location: portfolio_builder.php");
            exit();
    } else {
        $error = "All fields are required (ETF ticker, allocation, account type, asset class, exchange, fund structure, trailing yield)";
    }
    } 

    // user clicked "Create Portfolio" - do some basic validation to make sure the portfolio is valid
    else if (isset($_POST['create_portfolio'])) {
        // check if there are any holdings
        if (empty($_SESSION['current_holdings'])) {
            $error = "At least one holding is required";
        } else {
            // check if the portfolio's total allocation is 100%
            $total_allocation = 0;
            foreach ($_SESSION['current_holdings'] as $holding) { 
                // add up the total allocation
                $total_allocation += floatval($holding['allocation_percentage']);
            } 
            
            if ($total_allocation != 100) {
                // automatically add CASH holding for remaining allocation
                $remaining_allocation = 100 - $total_allocation;
                $cash_holding = [
                    'etf_ticker' => 'CASH',
                    'allocation_percentage' => $remaining_allocation,
                    'asset_class' => 'Cash',
                    'account_type' => 'Taxable',
                    'exchange' => 'N/A',
                    'fund_structure' => 'Cash',
                    'trailing_yield' => 0
                ];
                $_SESSION['current_holdings'][] = $cash_holding;
            }
            
            // proceed with portfolio creation (whether 100% or we added cash)
            // the portfolio passes validation checks, so create it in the database
            try {
                if (isset($_SESSION['user_id'])) {
                    // create portfolio for logged-in user
                    $portfolioId = $db->createPortfolio($_POST['portfolio_name'], null, $_SESSION['user_id']);
                } else {
                    // create portfolio for anonymous user - user_id should be null
                    $portfolioId = $db->createPortfolio($_POST['portfolio_name'], $_SESSION['anon_user'], null);
                }
                
                // add all holdings from session to database
                foreach ($_SESSION['current_holdings'] as $holding) {
                    $db->addHolding(
                        $portfolioId,
                        $holding['asset_class'],
                        $holding['etf_ticker'],
                        floatval($holding['allocation_percentage']),
                        $holding['account_type'],
                        $holding['exchange'],
                        $holding['fund_structure'],
                        floatval($holding['trailing_yield'])
                    );
                }
                
                // clear the session data after successful creation by setting to an empty array 
                $_SESSION['current_holdings'] = array();
                unset($_SESSION['portfolio_name']); // clear portfolio name from session
                $_SESSION['message'] = "Portfolio created! Select it to see it's tax drag.";
                
                // redirect to prevent form resubmission
                header("Location: calculator.php");
                exit();
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
            }
        }
            // if reset button is clicked, clear the session data (holdings array)
    else if (isset($_POST['reset_portfolio'])) { 


        $_SESSION['current_holdings'] = array();
        unset($_SESSION['portfolio_name']); // clear portfolio name from session
        $_SESSION['message'] = "Portfolio reset!";
        
        // redirect to prevent form resubmission
        header("Location: portfolio_builder.php");
        exit();
    }
}



// get dropdown options for the form
$assetClasses = $db->getAssetClasses();
$accountTypes = $db->getAccountTypes();
$exchanges = $db->getExchanges();
$fundStructures = $db->getFundStructures();

// include the view file to display the form
include '../view/portfolio_builder_form.php';
?>
