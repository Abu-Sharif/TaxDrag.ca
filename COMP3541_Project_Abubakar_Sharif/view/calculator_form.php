<?php 
// set page-specific seo variables
$page_title = 'Canada Withholding Tax Calculator | TaxDrag';
$page_description = 'Easily calculate foreign withholding tax drag on your Canadian investments with our free calculator.';

include 'header.php'; 
?>

<main>
    <div class="two-column-layout">
        
        <!-- page header -->
        <div class="page-header">
            <h1><?php echo date('Y'); ?> Canada Withholding Tax Calculator</h1> <br>
            <p>Enter a few values and we'll show you the tax drag on your portfolio, <br>
                the compounded impact, and the layers of withholding tax levied, along with an<br>
                estimate of the compounded tax drag over time.</p>
        </div>

        <!-- input area -->
        <div class="input-area">
            <h2>Portfolio Details</h2>
            
            <!-- show success message if calculation was successful, added new holdings, creating protfolio etc-->
            <?php if (!empty($message)): ?>
                <div class="success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <!-- show error message if there was a problem -->
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <!-- calculator form  -->

                <!-- portfolio selection dropdown, show all portfolios user has added, later we will add prebuilt portfolios -->
                <div class="form-group">
                    <label for="selected_portfolio">Select Portfolio</label>
                    <select id="selected_portfolio" name="selected_portfolio" required>
                        <option value="">Choose a portfolio</option>
                        <?php foreach ($portfolios as $portfolio): ?>
                            <option value="<?php echo htmlspecialchars($portfolio['id']); ?>"
                                    <?php if (isset($_POST['selected_portfolio']) && $_POST['selected_portfolio'] == $portfolio['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($portfolio['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- initial portfolio size $ -->
                <div class="form-group">
                    <label for="initial_investment">Portfolio Value</label>
                    <input type="text" id="initial_investment" name="initial_investment"   
                        required
                        placeholder="e.g., 100,000"
                        value="<?php 
                            if (isset($_POST['initial_investment']) && !empty($_POST['initial_investment'])) {
                                // remove the commas for display if they exist
                                $clean_value = str_replace(',', '', $_POST['initial_investment']);
                                echo '$' . htmlspecialchars(number_format($clean_value)); 
                            } else {
                                echo '$100,000'; // default value - 100k with comma
                            }
                        ?>">
                </div>

                <!-- checkbox that allows the user select if they want to see effects of FWT by compounding portfolio growth or show just show  FWT for their existing portfolio -->
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="calculation_type" name="calculation_type" value="compound" class="compound-checkbox">
                        Allow Compounding
                    </label>
                </div>
                
                
                <!-- if the user selected to compound the growth, display the time horizon input, otherwise show just a label "Portfolio Value" -->
                <!-- time horizon input -->
                <div class="form-group" id="time-horizon-div" style="display: none;">
                    <label for="time_horizon">Time Horizon (Years)</label>
                    <input type="number" id="time_horizon" name="time_horizon" 
                        min="1" max="50" step="1" required
                        placeholder="e.g., 10"
                        value="<?php 
                            if (isset($_POST['time_horizon']) && !empty($_POST['time_horizon'])) {
                                echo htmlspecialchars($_POST['time_horizon']); 
                            } else {
                                echo '10'; // default value - 10 years
                            }
                ?>">
                </div>

                <!-- compound/growth rate input  only shown if compounded is selected  -->
                <div class="form-group" id="compound-rate-div" style="display: none;">
                    <label for="compound_rate">Compound Rate (%)</label>
                    <input type="number" id="compound_rate" name="compound_rate" 
                        min="0" max="100" step="0.10" required
                        placeholder="e.g., 7.0"
                        value="<?php 
                            if (isset($_POST['compound_rate']) && !empty($_POST['compound_rate'])) {
                                echo htmlspecialchars($_POST['compound_rate']); 
                            } else {
                                echo '7.0'; // default value - 7%
                            }
                        ?>">
                </div>
        </div>

        <!-- results card -->
        <div class="results-card" id="results">
            <h2 style="text-align: center;">Your Results</h2>
            
            <!-- show calculation results if available -->
            <?php if (isset($results) && !empty($results)): ?>
                <div class="result-summary">
                    <!-- Display the 1st year witholding tax amount and percentage of the initial investment -->
                    <p>Initial Portfolio Impact: <span class="result-value">$<?php echo number_format($total_impact, 2); ?></span></p> <br>
                    <p>Annual Tax Drag: <span class="result-value"><?php echo number_format(($total_impact / $_POST['initial_investment']) * 100, 3); ?>%</span> </p> 

                    <!-- if the user selected to compound the growth, display the accumulated witholding tax over the time horizon -->
                    <?php if (isset($_POST['calculation_type']) && $_POST['calculation_type'] === 'compound'){ ?> <br>
                        <p>Compounded Tax Drag (<?php echo number_format($_POST['time_horizon'], 0); ?>Y): <span class="result-value">$<?php echo number_format($total_FWT_paid, 2); ?></span></p>
                    <?php } ?>
                </div>
                
                <!-- display each holdings data and equivalent witholding tax amount -->
                <table>
                    <thead>
                        <tr>
                            <th>Ticker</th>
                            <th>Allocation</th>
                            <th>Yield</th>
                            <th>FWT L1
                                <span class="tooltip" data-tooltip="Foreign Withholding Tax Layer 1"></span>
                            </th>
                            <th>FWT L2
                                <span class="tooltip" data-tooltip="Foreign Withholding Tax Layer 2"></span>
                            </th>
                            <th>Annual Impact</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $result): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($result['ticker']); ?></td>
                                <td><?php echo htmlspecialchars($result['allocation']); ?>%</td>
                                <td><?php echo htmlspecialchars($result['yield']); ?>%</td>
                                <td><?php echo number_format($result['fwl_l1'] * 100, 1); ?>%</td>
                                <td><?php echo number_format($result['fwl_l2'] * 100, 1); ?>%</td>
                                <td>$<?php echo number_format($result['impact'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- yearly breakdown table, only show if the user selected to compound the growth  -->
                <?php if (isset($yearly_breakdown) && !empty($yearly_breakdown)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Year</th>
                                <th>Portfolio Value</th>
                                <th>Dividend</th>
                                <th>FWT Paid</th>
                            </tr>
                        </thead>

                        <!-- display each years data -->
                        <tbody>
                            <?php foreach ($yearly_breakdown as $year): ?>
                                <tr>
                                    <td><?php echo $year['year']; ?></td>
                                    <td>$<?php echo number_format($year['portfolio_value'], 2); ?></td>
                                    <td>$<?php echo number_format($year['dividend'], 2); ?></td>
                                    <td>$<?php echo number_format($year['fwt'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    

                <?php endif; ?>
            <?php else: ?>
                <p class="empty-state">select a portfolio and click calculate to see results</p>
            <?php endif; ?>
        </div>

    </div>
</main>

<?php include 'footer.php'; ?>
<!-- include number formatting utilities -->
<script src="../public/number_formatting.js"></script>

<!-- jQuery script to handle when form data changes -->
<script>
$(document).ready(function(){

    // add event listener to the initial investment input for real-time formatting
    $('#initial_investment').on('input', function() {
        formatNumberWithCommas(this);
    });


    // trigger calculation when any field changes
    $("select#selected_portfolio").change(updateCalculation);
    $("input#initial_investment").on('keyup change', updateCalculation); // keyup change is from increment on forms
    $("input#calculation_type").change(updateCalculation);
    $("input#time_horizon").on('keyup change', updateCalculation);
    $("input#compound_rate").on('keyup change', updateCalculation);

    // handle compound checkbox click
    $(".compound-checkbox").click(function(){
        // $(this) is the checkbox which was clicked
        var compoundCheckbox = $(this);
        // the divs that contain the time horizon and compound rate inputs 
        var $timeHorizonDiv = $("#time-horizon-div");
        var $compoundRateDiv = $("#compound-rate-div");

        // if the checkbox is checked, show the time horizon and compound rate divs
        if (compoundCheckbox.is(":checked")) {
            $timeHorizonDiv.show();
            $compoundRateDiv.show(); 
            compoundCheckbox.prop("checked", true);
        } else {
            compoundCheckbox.prop("checked", false);
            $timeHorizonDiv.hide();
            $compoundRateDiv.hide(); 

        }
    });

}); 

</script>