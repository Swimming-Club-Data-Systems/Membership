function clearOutput() {
  document.getElementById('output').innerHTML = '<div class="ajaxPlaceholder">Select a swimmer and gala</div>';
  enableBtn();
}
      
function enableBtn() {
  var enableFromCount = true;
  var total = document.getElementById('total-field');
  if (total !== null) {
    if (parseInt(total.dataset.count) == 0) {
      enableFromCount = false;
    }
  }
  var enterable = document.getElementById('gala-info');
  if (enterable !== null && JSON.parse(enterable.dataset.enterable) && enableFromCount) {
    document.getElementById('submit').disabled = false;
  } else {
    document.getElementById('submit').disabled = true;
  }
}

function getResult() {
  var gala = document.getElementById('gala');
  var swimmer = document.getElementById('swimmer');
  var swimmerValue = swimmer.value;
  var galaValue = gala.options[gala.selectedIndex].value;
  
  if (swimmerValue == 'null' || galaValue == 'null') {
    clearOutput();
  } else {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        document.getElementById('output').innerHTML = this.responseText;
        setupSumming();
        enableBtn();
      }
    }
    var ajaxRequest = document.getElementById('gala-data').dataset.ajaxUrl + '?galaID=' + encodeURI(galaValue) + '&swimmer=' + encodeURI(swimmerValue);
    xmlhttp.open('GET', ajaxRequest, true);
    xmlhttp.send();
  }
}

var swimmer = document.getElementById('swimmer');
var gala = document.getElementById('gala');

swimmer.addEventListener('change', getResult);
gala.addEventListener('change', getResult);

function setupSumming() {
  // get reference to element containing event checkboxes
  var checkboxes = document.getElementById('gala-checkboxes');

  if (checkboxes !== null) {

    // get reference to input elements in toppings container element
    var boxes = checkboxes.getElementsByTagName('input');

    // assign function to onclick property of each checkbox
    for (var i=0, len=boxes.length; i<len; i++) {
      if ( boxes[i].type === 'checkbox' ) {
        boxes[i].onclick = function() {
          // Get the total
          var total = document.getElementById('total-field');
          var entries = document.getElementById('entries-field');

          var newTotal = 0;
          var newCount = 0;
          if (this.checked) {
            newTotal = parseInt(total.dataset.total) + parseInt(this.dataset.eventFee);
            newCount = parseInt(total.dataset.count) + 1;
          } else {
            newTotal = parseInt(total.dataset.total) - parseInt(this.dataset.eventFee);
            newCount = parseInt(total.dataset.count) - 1;
          }

          total.dataset.count = newCount;
          total.dataset.total = newTotal;
          total.textContent = (new BigNumber(newTotal)).shiftedBy(-2).decimalPlaces(2).toFormat(2);

          if (total.dataset.count == 1) {
            entries.textContent = ' for ' + total.dataset.count + ' entry';
          } else if (total.dataset.count > 1) {
            entries.textContent = ' for ' + total.dataset.count + ' entries';
          } else {
            entries.textContent = '';
          }

          // Call enable button - If count is zero, disables button.
          enableBtn();
        }
      }
    }
  }
}