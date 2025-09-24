<?php
require_once __DIR__ . '/../database.php';

class TaxCalculator {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    // get withholding tax rate for specific combination
    public function getWithholdingTaxRate($accountType, $assetClass, $exchange, $fundStructure) {
        try {
            $query = "SELECT FWL_L1, FWL_L2 FROM tax_rules WHERE account_type = :accountType AND asset_class = :assetClass AND exchange = :exchange AND fund_structure = :fundStructure";
            $statement = $this->db->prepare($query);
            $statement->bindValue(':accountType', $accountType, PDO::PARAM_STR);
            $statement->bindValue(':assetClass', $assetClass, PDO::PARAM_STR);
            $statement->bindValue(':exchange', $exchange, PDO::PARAM_STR);
            $statement->bindValue(':fundStructure', $fundStructure, PDO::PARAM_STR);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return $result;
            } else {
                // return 0 if no rule found (no withholding tax)
                return ['FWL_L1' => 0.0, 'FWL_L2' => 0.0];
            }
        } catch (PDOException $e) {
            throw new Exception("Error getting tax rate: " . $e->getMessage());
        }
    }
    
    // calculate compound interest with FWT over time
    public function calculateCompoundInterestWithFWT($holdings, $initial_investment, $years, $growth_rate) {
        $portfolio_value = $initial_investment;
        $total_FWT_paid = 0;
        $yearly_breakdown = [];
        
        // calculate weighted average yield and FWT rate once
        $weighted_yield = 0;
        $total_impact = 0;
        
        // iterate through each holding in the portfolio 
        foreach ($holdings as $holding) {
            $allocation = $holding['allocation_percentage'] / 100;
            $weighted_yield += ($holding['trailing_yield'] / 100) * $allocation;
            
            // get the FWT tax rate that matches the structure of the holding
            $tax_rules = $this->getWithholdingTaxRate(
                $holding['account_type'], 
                $holding['asset_class'], 
                $holding['exchange'], 
                $holding['fund_structure']
            );
            // calculate the FWT rate for this holding
            $trailing_yield_decimal = $holding['trailing_yield'] / 100;
            $FWL_L1 = $tax_rules['FWL_L1'] * $trailing_yield_decimal;
            $FWL_L2 = $tax_rules['FWL_L2'] * ($trailing_yield_decimal - $FWL_L1);
            $combined_FWT = $FWL_L1 + $FWL_L2;
            // calculate the initial FWT impact for this holding
            $holding_value = $initial_investment * $allocation;
            $holding_impact = $holding_value * $combined_FWT;
            $total_impact += $holding_impact;
        }
        
        // calculate weighted FWT rate
        $weighted_FWT_rate = $total_impact / ($initial_investment * $weighted_yield);
        
        // compound over years
        for ($i = 1; $i <= $years; $i++) {
            // grow the portfolio
            $portfolio_value *= (1 + $growth_rate);
            
            // calculate dividend for this year
            $dividend = $portfolio_value * $weighted_yield;
            
            // calculate FWT for this year
            $FWT = $dividend * $weighted_FWT_rate;
            $total_FWT_paid += $FWT;
            
            $yearly_breakdown[] = [
                'year' => $i,
                'portfolio_value' => $portfolio_value,
                'dividend' => $dividend,
                'fwt' => $FWT
            ];
        }
        
        // return the results in an array
        return [
            'total_impact' => $total_impact,
            'total_FWT_paid' => $total_FWT_paid,
            'yearly_breakdown' => $yearly_breakdown,
            'weighted_yield' => $weighted_yield,
            'weighted_FWT_rate' => $weighted_FWT_rate
        ];
    }
    
    // calculate current year FWT only we later use this in comparison page
    public function calculateCurrentYearFWT($holdings, $initial_investment) {
        $total_impact = 0;
        
        foreach ($holdings as $holding) {
            $allocation = $holding['allocation_percentage'] / 100;
            $weighted_yield = ($holding['trailing_yield'] / 100) * $allocation;
            
            // calculate immediate impact for this holding
            $tax_rules = $this->getWithholdingTaxRate(
                $holding['account_type'], 
                $holding['asset_class'], 
                $holding['exchange'], 
                $holding['fund_structure']
            );
            
            $trailing_yield_decimal = $holding['trailing_yield'] / 100;
            $FWL_L1 = $tax_rules['FWL_L1'] * $trailing_yield_decimal;
            $FWL_L2 = $tax_rules['FWL_L2'] * ($trailing_yield_decimal - $FWL_L1);
            $combined_FWT = $FWL_L1 + $FWL_L2;
            
            $holding_value = $initial_investment * $allocation;
            $holding_impact = $holding_value * $combined_FWT;
            $total_impact += $holding_impact;
        }
        
        return [
            'total_impact' => $total_impact
        ];
    }
    
    // calculate tax drag for comparison page returns annual tax drag, percentage tax drag and total tax drag
    public function calculateTaxDrag($holdings, $initial_investment, $growth_rate, $time_horizon) {
        // calculate compound interest with FWT over the time horizon
        $results = $this->calculateCompoundInterestWithFWT($holdings, $initial_investment, $time_horizon, $growth_rate / 100);
        
        // calculate annual tax drag (first year)
        $tax_drag = $this->calculateCurrentYearFWT($holdings, $initial_investment);
        $annual_tax_drag = $tax_drag['total_impact'];
        // calculate percentage tax drag (annual tax drag as percentage of initial investment)
        $percentage_tax_drag = ($annual_tax_drag / $initial_investment) * 100;
        
        // total tax drag over the time horizon
        $total_tax_drag = $results['total_FWT_paid'];
        
        return [
            'annual_tax_drag' => $annual_tax_drag,
            'percentage_tax_drag' => $percentage_tax_drag,
            'total_tax_drag' => $total_tax_drag,
            'yearly_breakdown' => $results['yearly_breakdown'] // store for future use
        ];
    }
    
}
?>
