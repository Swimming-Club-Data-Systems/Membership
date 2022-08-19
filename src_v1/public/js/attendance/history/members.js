const ajaxData = document.getElementById('ajax-data').dataset;

function getResult() {
  var squad = document.getElementById("squad");
  var squadValue = squad.options[squad.selectedIndex].value;
  var search = document.getElementById("search");
  var searchValue = search.value;
  console.log(squadValue);
  console.log(searchValue);
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      console.log("We got here");
      document.getElementById("output").innerHTML = this.responseText;
      console.log(this.responseText);
      window.history.replaceState("string", "Title", ajaxData.pageUrl + "?squad=" + squadValue + "&search=" + searchValue);
    }
  }
  xhttp.open("POST", ajaxData.ajaxUrl, true);
  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhttp.send("squadID=" + squadValue + "&search=" + searchValue);
  console.log("Sent");
}
// Call getResult immediately
getResult();

document.getElementById("squad").onchange = getResult;
document.getElementById("search").oninput = getResult;