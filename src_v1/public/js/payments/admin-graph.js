var ctx = document.getElementById('incomeChart').getContext('2d');
let chartData = JSON.parse(document.getElementById('incomeChart').dataset.data);
var chart = new Chart(ctx, {
  // The type of chart we want to create
  type: 'bar',

  // The data for our dataset
  data: {
    labels: chartData.labels,
    datasets: chartData.data,
  },

  // Configuration options go here
  options: {
    scales: {
      yAxes: [{
        ticks: {
          beginAtZero: true
        }
      }]
    }
  }
});