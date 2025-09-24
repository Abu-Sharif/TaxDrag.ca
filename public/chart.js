// credit to Digital Fox for the script, i modified it to fit my needs: https://www.youtube.com/watch?v=XPOSEf40SkQ
// this uses the chart.js library, and despite project requirments not mentioning libraries, it makes it a ton easier to implement a chart

// create cumulative tax drag chart for portfolio comparison
if (typeof chartData !== 'undefined' && chartData.portfolio1 && chartData.portfolio2) {

    // get the canvas element for the chart from the compare_form.php file need this becuase this is where the chart is put
    var canvas = document.getElementById('taxDragChart');
    // chart.js requires this as a paramter to draw the chart
    var ctx = canvas.getContext('2d');
        
        // arrays to store the tax drag calculations for each portfolo
        var portfolioTotalTaxDrag = [];
        var secondPortfolioTotalTaxDrag = [];
        
        // calculate running total of tax drag for portfolio 1
        let totalTaxDrag = 0;
        for (let currentYear = 0; currentYear < chartData.portfolio1.yearlyData.length; currentYear++) { // loop through each year
            let thisYearsTaxDrag = chartData.portfolio1.yearlyData[currentYear].fwt; 
            totalTaxDrag += thisYearsTaxDrag; // add the current years tax drag to the total
            portfolioTotalTaxDrag.push(totalTaxDrag); // add the running total to the array
        }
        
        // same thing but for the second portfolio
        let secondtotalTaxDrag = 0;
        for (let currentYear = 0; currentYear < chartData.portfolio2.yearlyData.length; currentYear++) {
            let thisYearsTaxDrag = chartData.portfolio2.yearlyData[currentYear].fwt;
            secondtotalTaxDrag += thisYearsTaxDrag;
            secondPortfolioTotalTaxDrag.push(secondtotalTaxDrag);
        }
        
        // create the bar chart showing cumulative tax drag over time
        var cumulativeTaxDragChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: Array.from({length: chartData.timeHorizon}, (_, i) => 'Year ' + (i + 1)),
                datasets: [
                    {
                        label: chartData.portfolio1.name,
                        data: portfolioTotalTaxDrag,
                        tension: 0.1
                    },
                    {
                        label: chartData.portfolio2.name,
                        data: secondPortfolioTotalTaxDrag,
                        tension: 0.1
                    }
                ]
            },
            // mandatory options for chart.js to draw the chart
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    // this shows the title on top of the chart
                    title: {
                        display: true,
                        text: 'Compounded Tax Drag Effect'
                    },
                    // this is the feature that shows the values of the points on the chart when you hower over them
                    tooltip: {
                        enabled: true,
                        // this just makes it so that there are no decimals values, makes it easier to read
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': $' + Math.round(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    // starts the y axis at  0
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Compounded Tax Drag ($)',
                        }
                    }
                }
            }
        });
}

// simple portfolio doughnut chart function
function renderPortfolioChart(ctx, labels, data) {
    // default Chart.js colors
    const defaultColors = [
        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
        '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
    ];
    
    // create colors array - green for CASH, default colors for others
    let colors = [];
    let colorIndex = 0;
    
    // cash has a default color of green, other use default colors provided by chart.js
    for (let i = 0; i < labels.length; i++) {
        if (labels[i] === 'CASH') {
            colors.push('#28a745'); // green color for CASH
        } else {
            colors.push(defaultColors[colorIndex % defaultColors.length]);
            colorIndex++;
        }
    }
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors

            }]
        }
    });
}
