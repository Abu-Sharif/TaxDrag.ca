<?php 
// set page-specific seo variables
$page_title = 'Portfolio Builder | TaxDrag';
$page_description = 'Build your investment portfolio and view its tax implications with our comprehensive portfolio builder tool.';

include 'header.php'; 
?>
<main>
    <div class="two-column-layout">
        
        <!-- page header -->
        <div class="page-header">
            <h1>Portfolio Builder</h1>
            <p>Build your portfolio, and view it's tax implications</p>
        </div>

        <!-- input area -->
        <div class="input-area">
            <h2>Build Your Portfolio</h2>
            
            <!-- show success message if portfolio was created -->
            <?php if (!empty($message)): ?>
                <div class="success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <!-- show error message if there was a problem -->
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            

        <form action="portfolio_builder.php" method="post">
            <!-- portfolio name field -->
            <div>
                <label for="portfolio_name">Portfolio Name *</label>
                <input type="text" id="portfolio_name" name="portfolio_name" placeholder="e.g., My Portfolio" required 
                       value="<?php 
                        // if the portfolio name is already set in the session, display it
                           if (isset($_POST['portfolio_name'])) {
                               echo htmlspecialchars($_POST['portfolio_name']); 
                           } elseif (isset($_SESSION['portfolio_name'])) {
                               echo htmlspecialchars($_SESSION['portfolio_name']);
                           }
                        ?>">
            </div>
            <br>
            <!-- Single Holding Form -->
            <div>
                <h3>Add an Holding</h3> 
                <div>
                    <!-- ETF ticker symbol input -->
                    <div>
                        <label>Ticker Symbol
                            <span class="tooltip" data-tooltip="The ticker symbol of the Stock/ETF you want to add to your portfolio."></span>
                        </label>
                        <input type="text" name="etf_ticker" 
                               placeholder="e.g., XIC" required>
                    </div> 
                        
                    <!-- allocation percentage - number between 0 and 100 -->
                    <div>
                        <label>Allocation %
                            <span class="tooltip" data-tooltip="The percent that this holding makes up of your total portfolio"></span>
                        </label>
                        <input type="number" name="allocation_percentage" 
                               step="0.01" min="0" max="100" placeholder="30.0" required>
                    </div>

                    <!-- account type dropdown menu -->
                    <div>
                        <label>Account Type
                            <span class="tooltip" data-tooltip="The account that you hold the ETF in (tax calculations vary by account type)."></span>
                        </label>
                        <select name="account_type" required>
                        <option value="">Select Account Type</option>
                            <?php foreach ($accountTypes as $type): ?>
                                <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                           <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <!-- asset class dropdown menu -->
                        <label>Asset Class 
                            <span class="tooltip" data-tooltip="The underlying holdings/asset class of the ETF."></span>
                        </label>

                        <select name="asset_class" required> 
                            <option value="">Select Asset Class</option>
                            <?php foreach ($assetClasses as $class): ?>
                                <option value="<?php echo htmlspecialchars($class); ?>"><?php echo htmlspecialchars($class); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                        
                    <!-- exchange dropdown menu -->
                    <div>
                        <label>Exchange
                            <span class="tooltip" data-tooltip="The stock exchange that the ETF is listed on (TSX: CA Listed, NYSE: US Listed)."></span>
                        </label>
                        <select name="exchange" required>
                            <option value="">Select Exchange</option>
                            <?php foreach ($exchanges as $exchange): ?>
                                <option value="<?php echo htmlspecialchars($exchange); ?>"><?php echo htmlspecialchars($exchange); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                        
                    <!-- fund structure dropdown menu -->
                    <div>
                        <label>Fund Structure
                            <span class="tooltip" data-tooltip="Direct Holdings: ETF that invests directly in individual stocks (e.g. SPY). Fund of Funds: ETF that holds other ETFs, indirect ownership (e.g. VUN). If the holding is an individual stock, select Direct Holdings."></span>
                        </label>
                        <select name="fund_structure" required>
                            <option value="">Select Fund Structure</option>
                            <?php foreach ($fundStructures as $structure): ?>
                                <option value="<?php echo htmlspecialchars($structure); ?>"><?php echo htmlspecialchars($structure); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                        
                     <!-- 12M trailing yield input -->
                    <div>
                        <label>12M Trailing Yield (%)
                            <span class="tooltip" data-tooltip="The 12-month trailing dividend yield of the ETF (can be found on stock/ETF's website)."></span>
                        </label>
                        <input type="number" name="trailing_yield" 
                               step="0.01" min="0" max="100" placeholder="2.5" required>
                    </div>
                </div>
                
                <!-- add holding button - adds the current holding to the portfolio -->
                <button type="submit" name="add_holding">Add This Holding</button>
            </div>
        </form>
        
        <!-- create portfolio button - sends the portfolio to the database -->
        <form action="portfolio_builder.php" method="post" style="margin-top: 20px;">
            <input type="hidden" name="portfolio_name" value="<?php 
                if (isset($_POST['portfolio_name'])) {
                    echo htmlspecialchars($_POST['portfolio_name']); 
                } elseif (isset($_SESSION['portfolio_name'])) {
                    echo htmlspecialchars($_SESSION['portfolio_name']);
                }
            ?>">
        <?php if (!empty($_SESSION['current_holdings'])): ?>
            <button type="submit" name="create_portfolio" class="view-details-btn" >Create Portfolio</button>
            <input type="hidden" name="reset_portfolio" value="true"> 
            <button type="submit" name="reset_portfolio" class="view-details-btn" >Reset Portfolio</button>
        <?php endif; ?>
        </form>
        
        <!-- reset portfolio button - clears the current holdings -->
        <!-- <form action="portfolio_builder.php" method="post" >

        </form> -->
        </div>

        <!-- results area -->
        <div class="results-area">
            
            <!-- portfolio allocation pie chart  -->
            <?php if (!empty($_SESSION['current_holdings'])): ?>
                <!-- Chart.js to load a pie chart of the portfolio allocations -->
                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script src="../public/chart.js"></script>
                 <h3 style="text-align: center;"><?php echo htmlspecialchars(ucfirst($_SESSION['portfolio_name'])); ?> Allocation</h3>
                 <canvas id="portfolioDoughnutChart" class="chart-canvas"></canvas>

                <script>
                     // PHP directly outputs arrays, json_encode converts the PHP arrays to JS arrays
                     let labels = <?php echo json_encode(array_column($_SESSION['current_holdings'], 'etf_ticker')); ?>;
                     let data   = <?php echo json_encode(array_column($_SESSION['current_holdings'], 'allocation_percentage')); ?>;
                     
                     // convert data to numbers (in case they're strings)
                     data = data.map(Number);
                     
                     // calculate total allocation
                     let total_allocation = 0;
                     for (let i = 0; i < data.length; i++){
                         total_allocation += data[i];
                     }
                     
                     // add cash if needed
                     if (total_allocation < 100){
                         let cash = 100 - total_allocation;
                         labels.push("CASH");
                         data.push(cash);
                     }
                     
                     // render chart
                     renderPortfolioChart(document.getElementById('portfolioDoughnutChart').getContext('2d'), labels, data);
                </script>
             <?php 
             else: ?> 
                <h3 style="text-align: center;">No holdings added yet</h3>
                <p style="text-align: center; color: #666; margin-top: 1rem;">Add your first holding to see the portfolio allocation chart.</p>

            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>