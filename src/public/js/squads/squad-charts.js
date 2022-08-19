
let sexSplit = document.getElementById('sexSplit').getContext('2d');
let sexSplitData = JSON.parse(document.getElementById('sexSplit').dataset.data);
let sexSplitChart = new Chart(sexSplit, {
  // The type of chart we want to create
  type: 'pie',

  // The data for our dataset
  data: sexSplitData,

  // Configuration options go here
  options: {}
});

var ageDistribution = document.getElementById('ageDistribution').getContext('2d');
let ageDistributionData = JSON.parse(document.getElementById('ageDistribution').dataset.data);
var ageDistributionChart = new Chart(ageDistribution, {
  // The type of chart we want to create
  type: 'horizontalBar',

  // The data for our dataset
  data: ageDistributionData,

  // Configuration options go here
  options: {
    scales: {
      yAxes: [{
        ticks: {
          beginAtZero: true,
          stepSize: 1,
        }
      }],
      xAxes: [{
        ticks: {
          beginAtZero: true,
          stepSize: 1,
        }
      }]
    }
  }
});