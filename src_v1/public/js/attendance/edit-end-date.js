var save = document.getElementById("saveDate");
save.addEventListener("click", setDate, false)

function setDate(clickedItem, clickedItemChecked) {
  var date = document.getElementById('endDate');
  var dateValue = date.value;
  
  var session = date.dataset.sessionId;

  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      document.getElementById("successAlert").innerHTML = '<div class="alert alert-success"><strong>We have saved the new end date</strong> <br>This session will disappear from the register after this date, but still display in past attendance records</div>';
    } else if (this.readyState == 4) {
      document.getElementById("successAlert").innerHTML = '<div class="alert alert-danger"><strong>We failed to update that properly</strong> <br>Please reload the page and try again</div>';
    }
  };

  xhttp.open("POST", date.dataset.ajaxUrl, true);
  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhttp.send('sessionID=' + session + '&sessionEndDate=' + dateValue);
}