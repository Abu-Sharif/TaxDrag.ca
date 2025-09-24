<?php 
// set page-specific seo variables
$page_title = 'Portfolio Explorer | TaxDrag';
$page_description = 'Browse portfolios and their tax implications. Explore different investment strategies and their withholding tax impact.';

include 'header.php'; 
?>

<main>
    <div class="container">
        
        <!-- page header -->
        <div class="page-header">
            <h1>Portfolio Explorer</h1>
            <p>Browse portfolios and their tax implications</p>
        </div>

        <!-- display messages -->
        <?php if (!empty($message)): ?>
            <div class="success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <!-- two column layout -->
        <div class="two-column-layout">
            
            <!-- left column - portfolio list -->
            <div class="input-area">
                <h2>Portfolios</h2>
                
                <?php 
                if (!empty($portfolios)): 
                    // group the data by portfolio so i can display each portfolio with its holdings
                    $groupedPortfolios = [];
                    foreach ($portfolios as $portfolio) {
                        $portfolioId = $portfolio['id'];
                        // if this is a new portfolio, create a new entry for it
                        if (!isset($groupedPortfolios[$portfolioId])) {
                            $groupedPortfolios[$portfolioId] = [
                                'user_id' => $portfolio['user_id'],
                                'anon_user' => $portfolio['anon_user'] ?? null, // if anon_user is not set, set it to null
                                'id' => $portfolio['id'],
                                'name' => $portfolio['name'],
                                'description' => $portfolio['description'],
                                'holdings' => []
                            ];
                        }
                        // add the holding to the portfolio if it has an etf ticker
                        if ($portfolio['etf_ticker']) {
                            $groupedPortfolios[$portfolioId]['holdings'][] = [
                                'asset_class' => $portfolio['asset_class'],
                                'etf_ticker' => $portfolio['etf_ticker'],
                                'allocation_percentage' => $portfolio['allocation_percentage'],
                                'account_type' => $portfolio['account_type']
                            ];
                        }
                    }
                ?>
                
                <!-- display each portfolio -->
                <?php foreach ($groupedPortfolios as $portfolio): ?>
                    <div class="form-group">
                        <h3><?php echo htmlspecialchars($portfolio['name']); ?></h3>
                        
                        <div class="form-group">
                            <button type="button" class="view-details-btn" data-portfolio-id="<?php echo $portfolio['id']; ?>">
                                View Details
                            </button>
                            <button type="button" class="hide-details-btn" data-portfolio-id="<?php echo $portfolio['id']; ?>" style="display: none;">
                                Hide Details
                            </button>
                            
                            <!-- user should only be able to delete their own portfolios --> 
                            <?php if ((isset($_SESSION['user_id']) && $portfolio['user_id'] == $_SESSION['user_id']) 
                            || (isset($_SESSION['anon_user']) && isset($portfolio['anon_user']) && $portfolio['anon_user'] == $_SESSION['anon_user'])): ?>
                                <form method="POST" action="portfolio_explorer.php" style="display: inline;">
                                    <input type="hidden" name="portfolio_id" value="<?php echo $portfolio['id']; ?>">
                                    <button type="submit" name="delete_portfolio" class="btn" >Delete</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <br>

                    <!-- Hidden template for this portfolio's details -->
                    <!-- This template contains the complete HTML structure that will be copied to the right column -->
                    <!-- when the user clicks "View Details". It's hidden by default and only shown when copied. -->
                    <div id="portfolio-template-<?php echo $portfolio['id']; ?>" style="display: none;">
                        <!-- Portfolio name and description -->
                        <h3><?php echo htmlspecialchars($portfolio['name']); ?></h3>
                        <?php if ($portfolio['description']): ?>
                            <p><strong>Description:</strong> <?php echo htmlspecialchars($portfolio['description']); ?></p>
                        <?php endif; ?>
                        
                        <!-- Holdings table (if portfolio has holdings) -->
                        <?php if ($portfolio['holdings'] && count($portfolio['holdings']) > 0): ?>
                            <h4>Holdings</h4>
                            <table>
                                <thead>
                                    <tr>
                                        <th>ETF Ticker</th>
                                        <th>Asset Class</th>
                                        <th>Allocation</th>
                                        <th>Account Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($portfolio['holdings'] as $holding): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($holding['etf_ticker']); ?></td>
                                            <td><?php echo htmlspecialchars($holding['asset_class']); ?></td>
                                            <td><?php echo number_format($holding['allocation_percentage'], 2); ?>%</td>
                                            <td><?php echo htmlspecialchars($holding['account_type']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- right column - portfolio details -->
            <div class="results-card">
                <h2>Portfolio Details</h2>
                <div id="portfolio-details-container">
                    <p class="empty-state">Select a portfolio to view its details</p>
                </div>
            </div>
            
        </div>
    </div>
</main>
<?php include 'footer.php'; ?>
<script>
$(document).ready(function() {
    // handle view details button click
    // instead of building html strings, we copy from pre-rendered templates
    $(".view-details-btn").click(function(){
        var portfolioId = $(this).data('portfolio-id');
        
        // copy the hidden template content to the details container
        // this approach is much cleaner than building html strings in javascript
        var templateContent = $("#portfolio-template-" + portfolioId).html();
        $("#portfolio-details-container").html(templateContent);
        
        // toggle button visibility: hide view button, show hide button
        $(this).hide();
        $(".hide-details-btn[data-portfolio-id='" + portfolioId + "']").show();
    });
    
    // handle hide details button click
    $(".hide-details-btn").click(function(){
        var portfolioId = $(this).data('portfolio-id');
        
        // clear the details section and show default message
        $("#portfolio-details-container").html('<p class="empty-state">Select a portfolio to view its details</p>');
        
        // toggle button visibility: hide hide button, show view button
        $(this).hide();
        $(".view-details-btn[data-portfolio-id='" + portfolioId + "']").show();
    });
    
    // auto-select the first portfolio (e.g., XEQT) on load
    $(".view-details-btn").first().trigger("click");
});
</script> 





