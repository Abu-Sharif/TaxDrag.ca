<?php 
// set page-specific seo variables
$page_title = 'Portfolio Comparison Tool | TaxDrag';
$page_description = 'Compare two portfolios side by side to see which one has better withholding tax efficiency over your chosen time horizon.';

include 'header.php'; 
?>
<main>
    <div class="two-column-layout">
        
        <!-- page header -->
        <div class="page-header">
            <h1>Portfolio Comparison</h1>
            <p>Compare two portfolios side by side to see which one has better tax efficiency<br>
                over your chosen time horizon</p>
        </div>
        
        <!-- input area -->
        <div class="input-area">
            <h2>Compare Portfolios</h2>
            
            <!-- display error messages -->
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- comparison form -->
            <form action="compare.php" method="post">
                <!-- select first portfolio -->
                <div class="form-group">
                    <label for="portfolio1_id">Portfolio 1</label>
                    <select id="portfolio1_id" name="portfolio1_id" required>
                        <option value="">Choose first portfolio</option>
                        <?php foreach ($portfolios as $portfolio): ?>
                            <option value="<?php echo htmlspecialchars($portfolio['id']); ?>"
                                    <?php if (isset($_POST['portfolio1_id']) && $_POST['portfolio1_id'] == $portfolio['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($portfolio['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- select second portfolio -->
                <div class="form-group">
                    <label for="portfolio2_id">Portfolio 2</label>
                    <select id="portfolio2_id" name="portfolio2_id" required>
                        <option value="">Choose second portfolio</option>
                        <?php foreach ($portfolios as $portfolio): ?>
                            <option value="<?php echo htmlspecialchars($portfolio['id']); ?>"
                                    <?php if (isset($_POST['portfolio2_id']) && $_POST['portfolio2_id'] == $portfolio['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($portfolio['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                    
                <!-- add a growth rate -->
                <div class="form-group">
                    <label for="growth_rate">Growth Rate (%)</label>
                    <input type="number" id="growth_rate" name="growth_rate" 
                        min="0" max="100" step="0.1" required
                        placeholder="e.g., 7.0"
                        value="<?php 
                            if (isset($_POST['growth_rate']) && !empty($_POST['growth_rate'])) {
                                echo htmlspecialchars($_POST['growth_rate']); 
                            } else {
                                echo '7.0'; // default value - 7%
                            }
                        ?>">
                </div>
                    
                <!-- add a time horizon for the comparison, 10 year is prechecked -->
                <div class="form-group">
                    <label>Time Horizon</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" id="time_horizon" name="time_horizon" value="3" 
                                <?php if (isset($_POST['time_horizon']) && $_POST['time_horizon'] == '3') echo 'checked'; ?>>
                            3 Years
                        </label>
                        <label>
                            <input type="radio" id="time_horizon" name="time_horizon" value="5" 
                                <?php if (isset($_POST['time_horizon']) && $_POST['time_horizon'] == '5') echo 'checked'; ?>>
                            5 Years
                        </label>
                        <label>
                            <input type="radio" id="time_horizon" name="time_horizon" value="10" 
                                <?php if (isset($_POST['time_horizon']) && $_POST['time_horizon'] == '10') echo 'checked'; 
                                        elseif (!isset($_POST['time_horizon'])) echo 'checked'; ?>>
                            10 Years
                        </label>
                    </div>
                </div>
                <button type="submit" name="compare_portfolios" class="btn btn-primary">Compare Portfolios</button>
            </form>
        </div>

        <!-- results card -->
        <div class="results-card">
            <h2 style="text-align: center;">Comparison Results</h2>

            <!-- chart section  -->
            <?php if (!empty($comparison_results)): ?>
                <div class="chart">
                    <canvas id="taxDragChart" class="chart-canvas"></canvas>
                    <p style="text-align: center; font-size: 0.9em;"><strong>Initial Investment:</strong> $<?php echo number_format($comparison_results['initial_investment']); ?></p>
                </div>
            
            <!-- include chart scripts only when we have results -->
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                var chartData = {
                    portfolio1: {
                        name: '<?php echo addslashes($comparison_results['portfolio1']['name']); ?>',
                        yearlyData: <?php echo json_encode($comparison_results['portfolio1']['results']['yearly_breakdown']); ?>
                    },
                    portfolio2: {
                        name: '<?php echo addslashes($comparison_results['portfolio2']['name']); ?>',
                        yearlyData: <?php echo json_encode($comparison_results['portfolio2']['results']['yearly_breakdown']); ?>
                    },
                    timeHorizon: <?php echo $comparison_results['time_horizon']; ?>
                };
            </script>
            <script src="../public/chart.js"></script> 
            <?php endif; ?>
            <br>
            <br>


            <!-- table section -->
            <?php if (!empty($comparison_results)): ?>

                <table>
                    <thead>
                        <tr>
                            <th>Portfolio</th>
                            <th>Initial Tax Drag</th>
                            <th>Annual Tax Drag</th>
                            <th>Compounded Tax Drag (<?php echo $comparison_results['time_horizon']; ?>Y)</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($comparison_results['portfolio1']['name']); ?></strong></td>
                            <td>$<?php echo number_format($comparison_results['portfolio1']['results']['annual_tax_drag'], 2); ?></td>
                            <td><?php echo number_format($comparison_results['portfolio1']['results']['percentage_tax_drag'], 3); ?>%</td>
                            <td>$<?php echo number_format($comparison_results['portfolio1']['results']['total_tax_drag'], 2); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($comparison_results['portfolio2']['name']); ?></strong></td>
                            <td>$<?php echo number_format($comparison_results['portfolio2']['results']['annual_tax_drag'], 2); ?></td>
                            <td><?php echo number_format($comparison_results['portfolio2']['results']['percentage_tax_drag'], 3); ?>%</td>
                            <td>$<?php echo number_format($comparison_results['portfolio2']['results']['total_tax_drag'], 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty-state">select two portfolios to see comparison results</p>
            <?php endif; ?>

        </div>

    </div>
</main>
<?php include 'footer.php'; ?>

</main>
