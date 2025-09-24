// number formatting utilities for the calculator form

function formatNumberWithCommas(input) {
    // get the current value and strip any existing commas and dollar signs
    var value = input.value.replace(/[,$]/g, '');
    
    // if the field is empty, keep it empty
    if (value === '') {
        input.value = '';
        return;
    }

    // convert the value to a number
    var number = parseFloat(value);
    // if the value is not a number, return
    if (isNaN(number)) return;
    // if the value is less than 0, return
    if (number < 0) return;
    
    // format the number with commas and max 0 decimal places, add $ prefix
    input.value = '$' + number.toLocaleString('en-US', {
        maximumFractionDigits: 0
    });
}

function stripCommas(value) {
    return value.replace(/[,$]/g, '');
}

function updateCalculation() {
    var selectedPortfolio = $("select#selected_portfolio").val();
    var initialInvestment = stripCommas($("input#initial_investment").val());
    var calculationType = $("input#calculation_type").is(':checked') ? 'compound' : 'simple';
    var timeHorizon = $("input#time_horizon").val();
    var compoundRate = $("input#compound_rate").val();
    
    $.ajax({
        url: "calculator.php",
        type: "POST",
        data: {selected_portfolio: selectedPortfolio, initial_investment: initialInvestment, calculation_type: calculationType, time_horizon: timeHorizon, compound_rate: compoundRate},
        success: function(response) {
            $("#results").html($(response).find('#results').html());
        }
    });
}


