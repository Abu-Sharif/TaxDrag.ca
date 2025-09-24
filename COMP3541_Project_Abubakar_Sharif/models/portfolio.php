<?php
require_once __DIR__ . '/../database.php';

class Portfolio {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    // create a new portfolio, add user_id to the portfolio when created so we can trace it back to the user
    public function createPortfolio($name, $anon_user = null, $userId = null) { // anon_user and userId are optional, if not provided, we use a placeholder value
        try {
            $query = "INSERT INTO portfolios (name, anon_user, user_id) VALUES (:name, :anon_user, :userId)";
            $statement = $this->db->prepare($query);
            $statement->bindValue(':name', $name, PDO::PARAM_STR);
            $statement->bindValue(':anon_user', $anon_user, PDO::PARAM_STR);
            $statement->bindValue(':userId', $userId, PDO::PARAM_INT);
            $statement->execute(); 
            return $this->db->lastInsertId(); // return the id of the new portfolio, used later when we add a holding the portfolio
        } catch (PDOException $e) {
            throw new Exception("Error creating portfolio: " . $e->getMessage());
        }
    }
    
    // add a holding to a portfolio
    public function addHolding($portfolioId, $assetClass, $etfTicker, $allocationPercentage, $accountType, $exchange, $fundStructure, $trailingYield) {
        try {
            $query = "INSERT INTO holdings (portfolio_id, asset_class, etf_ticker, allocation_percentage, account_type, exchange, fund_structure, trailing_yield) VALUES (:portfolioId, :assetClass, :etfTicker, :allocationPercentage, :accountType, :exchange, :fundStructure, :trailingYield)";
            $statement = $this->db->prepare($query);
            $statement->bindValue(':portfolioId', $portfolioId, PDO::PARAM_INT);
            $statement->bindValue(':assetClass', $assetClass, PDO::PARAM_STR);
            $statement->bindValue(':etfTicker', $etfTicker, PDO::PARAM_STR);
            $statement->bindValue(':allocationPercentage', $allocationPercentage, PDO::PARAM_STR);
            $statement->bindValue(':accountType', $accountType, PDO::PARAM_STR);
            $statement->bindValue(':exchange', $exchange, PDO::PARAM_STR);
            $statement->bindValue(':fundStructure', $fundStructure, PDO::PARAM_STR);
            $statement->bindValue(':trailingYield', $trailingYield, PDO::PARAM_STR);
            $statement->execute();
            return true;
        } catch (PDOException $e) {
            throw new Exception("Error adding holding: " . $e->getMessage());
        }
    }
    
    // get asset classes for dropdown
    public function getAssetClasses() {
        return [
            'Canadian Equity',
            'US Equity',
            'International Developed Equity',
            'International Emerging Markets Equity',
            'Cash'
        ];
    }
    
    // get account types for dropdown
    public function getAccountTypes() {
        return [
            'TFSA',
            'RRSP',
            'Non-Registered',
        ];
    }
    
    // get exchange options for dropdown
    public function getExchanges() { 
        return [
            'US Listed',
            'CA Listed'
        ];
    }
    
    // get fund structure options for dropdown
    public function getFundStructures() {
        return [
            'Direct Holdings',
            'Fund of Funds'
        ];
    }
    
    // get all portfolios associated with a user_id, prebuilt portfolios (user_id = 1) are shown to everyone
    public function getAllPortfolios($anon_user = null, $userId = null) {
        try {
            if ($userId !== null) {
                // return user's portfolios and prebuilt portfolios (system user_id = 1)
                $query = "SELECT id, name FROM portfolios WHERE user_id = :userId OR user_id = 1 ORDER BY name";
                $statement = $this->db->prepare($query);
                $statement->bindValue(':userId', $userId, PDO::PARAM_INT);
            } else {
                // return prebuilt portfolios (system user_id = 1) and anon_user portfolios if user is not logged in
                $query = "SELECT id, name FROM portfolios WHERE anon_user = :anon_user OR user_id = 1 ORDER BY name";
                $statement = $this->db->prepare($query);
                $statement->bindValue(':anon_user', $anon_user, PDO::PARAM_STR);
            }
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error getting portfolios: " . $e->getMessage());
        }
    }


    public function getHolding($portfolioId) {
        try {
            $query = "SELECT * FROM holdings WHERE portfolio_id = :portfolioId";
            $statement = $this->db->prepare($query);
            $statement->bindValue(':portfolioId', $portfolioId, PDO::PARAM_INT);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error getting holding: " . $e->getMessage());
        }
    }
    
    // get all portfolios with their details and holdings for each user
    public function getAllPortfoliosWithDetails($anon_user = null, $userId = null) {
        try {
            // if user is logged in, return their personal portfolios and prebuilt portfolios and assocated holdings
            if ($userId !== null) {
                // return user's portfolios and prebuilt portfolios (system user_id = 1)
                $query = "SELECT portfolios.id, portfolios.name, portfolios.description, portfolios.user_id,
                                 holdings.asset_class, holdings.etf_ticker, holdings.allocation_percentage, holdings.account_type
                          FROM portfolios
                          INNER JOIN holdings ON portfolios.id = holdings.portfolio_id
                          WHERE portfolios.user_id = :userId OR portfolios.user_id = 1";
                $statement = $this->db->prepare($query);
                $statement->bindValue(':userId', $userId, PDO::PARAM_INT);
            } else {
                // return prebuilt portfolios (system user_id = 1) and anon_user portfolios and holdings if user is not logged in
                $query = "SELECT portfolios.id, portfolios.name, portfolios.description, portfolios.user_id, portfolios.anon_user,
                                 holdings.asset_class, holdings.etf_ticker, holdings.allocation_percentage, holdings.account_type
                          FROM portfolios
                          INNER JOIN holdings ON portfolios.id = holdings.portfolio_id
                          WHERE portfolios.anon_user = :anon_user OR portfolios.user_id = 1";
                $statement = $this->db->prepare($query);
                $statement->bindValue(':anon_user', $anon_user, PDO::PARAM_STR);
            }
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error getting portfolios with details: " . $e->getMessage());
        }
    }
    
    // delete a portfolio and all its holdings
    public function deletePortfolio($portfolioId) {
        try {
            // this starts a database transaction,  ensure entire transaction completes or all changes will be undone
            $this->db->beginTransaction();
            
            // delete holdings first (due to foreign key constraint)
            $query = "DELETE FROM holdings WHERE portfolio_id = :portfolioId";
            $statement = $this->db->prepare($query);
            $statement->bindValue(':portfolioId', $portfolioId, PDO::PARAM_INT);
            $statement->execute();
            
            // delete portfolio
            $query = "DELETE FROM portfolios WHERE id = :portfolioId";
            $statement = $this->db->prepare($query);
            $statement->bindValue(':portfolioId', $portfolioId, PDO::PARAM_INT);
            $statement->execute();
            
            // commit the transaction ensrues that the holdings are deleted with the portfolio
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            // rollback transaction if there is an error
            $this->db->rollBack();
            throw new Exception("Error deleting portfolio: " . $e->getMessage());
        }
    }
}
?>
