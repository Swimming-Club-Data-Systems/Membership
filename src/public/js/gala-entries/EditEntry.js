function setupSumming() {
  // get reference to element containing toppings checkboxes
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

          var newTotal = 0;
          if (this.checked) {
            newTotal = parseInt(total.dataset.total) + parseInt(this.dataset.eventFee);
          } else {
            newTotal = parseInt(total.dataset.total) - parseInt(this.dataset.eventFee);
          }

          total.dataset.total = newTotal;
          total.textContent = (new BigNumber(newTotal)).shiftedBy(-2).decimalPlaces(2).toFormat(2);
        }
      }
    }
  }
}

setupSumming();