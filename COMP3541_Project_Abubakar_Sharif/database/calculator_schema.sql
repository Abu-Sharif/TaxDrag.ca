-- Create the calculator database
-- DROP DATABASE IF EXISTS calculator;
-- CREATE DATABASE calculator;
-- USE calculator;

-- users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL
);

-- insert system user for prebuilt portfolios
INSERT INTO users (id, email, password_hash, username) VALUES 
(1, 'system@taxdrag.com', 'system_user_no_login', 'System');

-- portfolios table (cascade deletes the portfolio is user is deleted)
CREATE TABLE portfolios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    anon_user VARCHAR(255) NULL,
    user_id INT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE 
);

-- holdings table
CREATE TABLE holdings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    portfolio_id INT NOT NULL,
    asset_class VARCHAR(50) NOT NULL,
    etf_ticker VARCHAR(20) NOT NULL,
    allocation_percentage DECIMAL(5,2) NOT NULL,
    account_type VARCHAR(50) NOT NULL,
    exchange VARCHAR(10) NOT NULL,
    fund_structure VARCHAR(20) NOT NULL,
    trailing_yield DECIMAL(5,2),
    FOREIGN KEY (portfolio_id) REFERENCES portfolios(id) ON DELETE CASCADE
);

-- tax_rules table for withholding tax rates
CREATE TABLE tax_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_type VARCHAR(50) NOT NULL,
    asset_class VARCHAR(50) NOT NULL,
    exchange VARCHAR(10) NOT NULL,
    fund_structure VARCHAR(20) NOT NULL,
    FWL_L1 DECIMAL(5,2) NOT NULL, 
    FWL_L2 DECIMAL(5,2) NOT NULL
);

-- insert tax rules
-- See the README.txt for tax assumptions i've made: 
SET @FWT_US = 0.15;
SET @FWT_DEVELOPED = 0.078;
SET @FWT_EMERGING = 0.108;
SET @FWT_RECOVERABLE = 0.00;
SET @NOT_APPLICABLE = 0.00;

SET @XEQT_RRSP_DESCRIPTION = 'This portfolio is just XEQT inside an RRSP. Since XEQT is a Canadian-listed fund that holds U.S. 
and international stocks through other iShares ETFs, there is some foreign withholding tax (FWT) leakage built in. 
U.S. dividends from XTOT face the 15% FWT since it is CA listed, however, ITOT is exempt from FWT as it is US listed and held in the RRSP. 
XEQT holds XEF for developed markets and XEC for emerging markets, both of which are held in the most efficient way possible, however, still suffer from unavoidable FWT that their host countries impose.
Overall, XEQT provides extremely efficient tax management with the avoidable tax drag at less than 3 basis points (0.03%)—so hardly noticeable in practice.';

SET @XEQT_TFSA_DESCRIPTION = 'This portfolio is just XEQT if it were held in a TFSA.
U.S. dividends from both XTOT and ITOT face the 15% FWT since the TFSA is not eligble for FWT exemption under the CA-US tax treaty. 
XEQT holds XEF for developed markets and XEC for emerging markets, both of which are held in the most efficient way possible, however, still suffer from unavoidable FWT that their host countries impose.
Overall, holding XEQT in a TFSA is as tax effecient as an all-in-one ETF gets.';

SET @XEQT_NON_REG_DESCRIPTION = 'This portfolio is just XEQT if it were held in a Non-Registered.
This is the most efficient way to hold XEQT, since it is held in the most tax efficient way possible, and avoids FWT altogether. This is assuming, that recoverable FWT is indeed recovered, which is generally the case.';

SET @PWL_RRSP_DESCRIPTION = 'This is the PWL Capital model portfolio, if it were held in the RRSP. 
It\'s a straightforward design that has a home bias with broad global exposure.
From a foreign withholding tax (FWT) perspective, U.S. dividends in VUN get hit with the 15% FWT since VUN is CA listed and held in an RRSP.
The tax drag from XEF and XEC are unavoidable and are already held in the most efficient way possible. Just like with XEQT, the avoidable FWT here is tiny — less than 6 basis points overall, 
barely noticeable. The main idea is to keep things simple, diversified and tax-efficient.';

SET @INTERNATIONAL_PORTFOLIO_DESCRIPTION = 'This is a basic international portfolio that excludes US and Canadian equity, commonly known as EX-NA
(Excluding North America). This portfolio designed show you how much you could pay 
in foreign withholding taxes (FWT) under a suboptimal strategy. The fund holds IEFA and IEMG for international developed and emerging markets, respectively. 
Layer 1 of the FWT is unavoidable, since it comes from the host countries at ~8%, and ~10%. However, since we hold these US listed ETFs in the TFSA, it triggers Layer 2 of FWT of 15%. 
This portfolio has a massive tax drag of 63bps, many multiples larger than the management fees of the ETFs it consists of!';

-- insert portfolio (using system user id = 1 for prebuilt portfolios)
INSERT INTO portfolios (name, description, user_id) VALUES ('1. ETF Portfolio - XEQT in RRSP', @XEQT_RRSP_DESCRIPTION, 1);
INSERT INTO portfolios (name, description, user_id) VALUES ('2. ETF Portfolio - XEQT in TFSA', @XEQT_TFSA_DESCRIPTION, 1);
INSERT INTO portfolios (name, description, user_id) VALUES ('3. ETF Portfolio - XEQT in Non-Registered', @XEQT_NON_REG_DESCRIPTION, 1);
INSERT INTO portfolios (name, description, user_id) VALUES ('4. PWL Capital - Model Portfolio in RRSP', @PWL_RRSP_DESCRIPTION, 1);
INSERT INTO portfolios (name, description, user_id) VALUES ('5. International Portfolio in TFSA', @INTERNATIONAL_PORTFOLIO_DESCRIPTION, 1);

-- insert the holdigns into the table 
-- ETF Portfolio - XEQT in RRSP
INSERT INTO holdings (portfolio_id, asset_class, etf_ticker, allocation_percentage, account_type, exchange, fund_structure, trailing_yield) VALUES
(1, 'US Equity', 'XTOT', 30, 'RRSP', 'CA Listed', 'Fund of Funds', 1.21),
(1, 'US Equity', 'ITOT', 15, 'RRSP', 'US Listed', 'Direct Holdings', 1.21),
(1, 'Canadian Equity', 'XIC', 25, 'RRSP', 'CA Listed', 'Direct Holdings', 2.39),
(1, 'International Developed Equity', 'XEF', 25, 'RRSP', 'CA Listed', 'Direct Holdings', 2.53),
(1, 'International Emerging Markets Equity', 'XEC', 5, 'RRSP', 'CA Listed', 'Direct Holdings', 2.16);

-- ETF Portfolio - XEQT in TFSA
INSERT INTO holdings (portfolio_id, asset_class, etf_ticker, allocation_percentage, account_type, exchange, fund_structure, trailing_yield) VALUES
(2, 'US Equity', 'XTOT', 30, 'TFSA', 'CA Listed', 'Fund of Funds', 1.21),
(2, 'US Equity', 'ITOT', 15, 'TFSA', 'US Listed', 'Direct Holdings', 1.21),
(2, 'Canadian Equity', 'XIC', 25, 'TFSA', 'CA Listed', 'Direct Holdings', 2.39),
(2, 'International Developed Equity', 'XEF', 25, 'TFSA', 'CA Listed', 'Direct Holdings', 2.53),
(2, 'International Emerging Markets Equity', 'XEC', 5, 'TFSA', 'CA Listed', 'Direct Holdings', 2.16);

-- ETF Portfolio - XEQT in Non-Registered
INSERT INTO holdings (portfolio_id, asset_class, etf_ticker, allocation_percentage, account_type, exchange, fund_structure, trailing_yield) VALUES
(3, 'US Equity', 'XTOT', 30, 'Non-Registered', 'CA Listed', 'Fund of Funds', 1.21),
(3, 'US Equity', 'ITOT', 15, 'Non-Registered', 'US Listed', 'Direct Holdings', 1.21),
(3, 'Canadian Equity', 'XIC', 25, 'Non-Registered', 'CA Listed', 'Direct Holdings', 2.39),
(3, 'International Developed Equity', 'XEF', 25, 'Non-Registered', 'CA Listed', 'Direct Holdings', 2.53),
(3, 'International Emerging Markets Equity', 'XEC', 5, 'Non-Registered', 'CA Listed', 'Direct Holdings', 2.16);

-- PWL Capital - Model Portfolio in RRSP
INSERT INTO holdings (portfolio_id, asset_class, etf_ticker, allocation_percentage, account_type, exchange, fund_structure, trailing_yield) VALUES
(4, 'US Equity', 'VUN', 43, 'RRSP', 'CA Listed', 'Direct Holdings', 0.90),
(4, 'Canadian Equity', 'XIC', 33, 'RRSP', 'CA Listed', 'Direct Holdings', 2.39),
(4, 'International Developed Equity', 'XEF', 17, 'RRSP', 'CA Listed', 'Direct Holdings', 2.53),
(4, 'International Emerging Markets Equity', 'XEC', 7, 'RRSP', 'CA Listed', 'Direct Holdings', 2.16);

-- International Portfolio in TFSA
INSERT INTO holdings (portfolio_id, asset_class, etf_ticker, allocation_percentage, account_type, exchange, fund_structure, trailing_yield) VALUES
(5, 'International Developed Equity', 'IEFA', 50, 'TFSA', 'US Listed', 'Direct Holdings', 2.5),
(5, 'International Emerging Markets Equity', 'IEMB', 50, 'TFSA', 'US Listed', 'Direct Holdings', 3);



INSERT INTO tax_rules (account_type, asset_class, exchange, fund_structure, FWL_L1, FWL_L2) VALUES
-- RRSP
('RRSP', 'US Equity', 'US Listed', 'Direct Holdings', @NOT_APPLICABLE, @NOT_APPLICABLE),
('RRSP', 'US Equity', 'CA Listed', 'Direct Holdings', @FWT_US, @NOT_APPLICABLE),
('RRSP', 'US Equity', 'CA Listed', 'Fund of Funds', @FWT_US, @NOT_APPLICABLE),
('RRSP', 'International Developed Equity', 'US Listed', 'Direct Holdings', @FWT_DEVELOPED, @NOT_APPLICABLE),
('RRSP', 'International Developed Equity', 'US Listed', 'Fund of Funds', @FWT_DEVELOPED, @NOT_APPLICABLE),
('RRSP', 'International Developed Equity', 'CA Listed', 'Direct Holdings', @FWT_DEVELOPED, @NOT_APPLICABLE),
('RRSP', 'International Developed Equity', 'CA Listed', 'Fund of Funds', @FWT_DEVELOPED, @FWT_US),
('RRSP', 'International Emerging Markets Equity', 'US Listed', 'Direct Holdings', @FWT_EMERGING, @NOT_APPLICABLE),
('RRSP', 'International Emerging Markets Equity', 'US Listed', 'Fund of Funds', @FWT_EMERGING, @NOT_APPLICABLE),
('RRSP', 'International Emerging Markets Equity', 'CA Listed', 'Direct Holdings', @FWT_EMERGING, @NOT_APPLICABLE),
('RRSP', 'International Emerging Markets Equity', 'CA Listed', 'Fund of Funds', @FWT_EMERGING, @FWT_US),
('RRSP', 'Canadian Equity', 'US Listed', 'Direct Holdings', @NOT_APPLICABLE, @NOT_APPLICABLE),
('RRSP', 'Canadian Equity', 'CA Listed', 'Direct Holdings', @NOT_APPLICABLE, @NOT_APPLICABLE),
('RRSP', 'Cash', 'US Listed', 'Direct Holdings', @NOT_APPLICABLE, @NOT_APPLICABLE),
('RRSP', 'Cash', 'CA Listed', 'Direct Holdings', @NOT_APPLICABLE, @NOT_APPLICABLE),

-- TFSA
('TFSA', 'US Equity', 'US Listed', 'Direct Holdings', @FWT_US, @NOT_APPLICABLE),
('TFSA', 'US Equity', 'CA Listed', 'Direct Holdings', @FWT_US, @NOT_APPLICABLE),
('TFSA', 'US Equity', 'CA Listed', 'Fund of Funds', @FWT_US, @NOT_APPLICABLE),
('TFSA', 'International Developed Equity', 'US Listed', 'Direct Holdings', @FWT_DEVELOPED, @FWT_US),
('TFSA', 'International Developed Equity', 'US Listed', 'Fund of Funds', @FWT_DEVELOPED, @FWT_US),
('TFSA', 'International Developed Equity', 'CA Listed', 'Direct Holdings', @FWT_DEVELOPED, @NOT_APPLICABLE),
('TFSA', 'International Developed Equity', 'CA Listed', 'Fund of Funds', @FWT_DEVELOPED, @FWT_US),
('TFSA', 'International Emerging Markets Equity', 'US Listed', 'Direct Holdings', @FWT_EMERGING, @FWT_US),
('TFSA', 'International Emerging Markets Equity', 'US Listed', 'Fund of Funds', @FWT_EMERGING, @FWT_US),
('TFSA', 'International Emerging Markets Equity', 'CA Listed', 'Direct Holdings', @FWT_EMERGING, @NOT_APPLICABLE),
('TFSA', 'International Emerging Markets Equity', 'CA Listed', 'Fund of Funds', @FWT_EMERGING, @FWT_US),
('TFSA', 'Canadian Equity', 'US Listed', 'Direct Holdings', @FWT_US, @NOT_APPLICABLE),
('TFSA', 'Canadian Equity', 'CA Listed', 'Direct Holdings', @NOT_APPLICABLE, @NOT_APPLICABLE),
('TFSA', 'Cash', 'US Listed', 'Direct Holdings', @NOT_APPLICABLE, @NOT_APPLICABLE),
('TFSA', 'Cash', 'CA Listed', 'Direct Holdings', @NOT_APPLICABLE, @NOT_APPLICABLE),

-- Non-Registered (personal taxable)
('Non-Registered', 'US Equity', 'US Listed', 'Direct Holdings', @FWT_RECOVERABLE, @NOT_APPLICABLE),
('Non-Registered', 'US Equity', 'CA Listed', 'Direct Holdings', @FWT_RECOVERABLE, @NOT_APPLICABLE),
('Non-Registered', 'International Developed Equity', 'US Listed', 'Direct Holdings', @FWT_DEVELOPED, @FWT_RECOVERABLE),
('Non-Registered', 'International Developed Equity', 'US Listed', 'Fund of Funds', @FWT_DEVELOPED, @FWT_RECOVERABLE),
('Non-Registered', 'International Developed Equity', 'CA Listed', 'Direct Holdings', @NOT_APPLICABLE, @NOT_APPLICABLE),
('Non-Registered', 'International Developed Equity', 'CA Listed', 'Fund of Funds', @FWT_DEVELOPED, @NOT_APPLICABLE),
('Non-Registered', 'International Emerging Markets Equity', 'US Listed', 'Direct Holdings', @FWT_EMERGING, @FWT_RECOVERABLE),
('Non-Registered', 'International Emerging Markets Equity', 'US Listed', 'Fund of Funds', @FWT_EMERGING, @FWT_RECOVERABLE),
('Non-Registered', 'International Emerging Markets Equity', 'CA Listed', 'Direct Holdings', @FWT_RECOVERABLE, @NOT_APPLICABLE),
('Non-Registered', 'International Emerging Markets Equity', 'CA Listed', 'Fund of Funds', @FWT_EMERGING, @FWT_RECOVERABLE),
('Non-Registered', 'Canadian Equity', 'US Listed', 'Direct Holdings', @FWT_RECOVERABLE, @NOT_APPLICABLE),
('Non-Registered', 'Canadian Equity', 'CA Listed', 'Direct Holdings', @FWT_RECOVERABLE, @NOT_APPLICABLE),
('Non-Registered', 'Cash', 'US Listed', 'Direct Holdings', @NOT_APPLICABLE, @NOT_APPLICABLE),
('Non-Registered', 'Cash', 'CA Listed', 'Direct Holdings', @NOT_APPLICABLE, @NOT_APPLICABLE);



