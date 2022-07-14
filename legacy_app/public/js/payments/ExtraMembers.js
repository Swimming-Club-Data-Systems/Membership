
function getSwimmers() {
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      document.getElementById("output").innerHTML = this.responseText;
    }
  }
  xhttp.open("POST", document.getElementById('addSwimmer').dataset.ajaxUrl, true);
  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhttp.send("response=getSwimmers");
}

function getSwimmersForSquad() {
  var squadSelect = document.getElementById("squadSelect");
  var swimmerSelect = document.getElementById('swimmerSelect');
  
  // On squad change, disable buttons and select immediately
  swimmerSelect.value = 'null';
  swimmerSelect.disabled = true;
  swimmerSelectChange();

  var squad = squadSelect.value;
  var button = document.getElementById('addSwimmer');
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    var swimmerSelect = document.getElementById('swimmerSelect');
    if (this.readyState == 4 && this.status == 200) {
      var response = JSON.parse(this.responseText);
      if (response.state) {
        swimmerSelect.disabled = false;
      } else {
        swimmerSelect.disabled = true;
        button.disabled = true;
      }
      swimmerSelect.innerHTML = response.swimmerSelectContent;
    } else if (this.readyState == 4) {
      swimmerSelect.disabled = true;
      button.disabled = true;
    }
  }
  xhttp.open("POST", button.dataset.ajaxUrl, true);
  xhttp.setRequestHeader("content-type", "application/x-www-form-urlencoded");
  xhttp.send("response=squadSelect&squadSelect=" + squad);
}

function swimmerSelectChange(event) {
  if (document.getElementById('swimmerSelect').value != 'null') {
    document.getElementById('addSwimmer').disabled = false;
  } else {
    document.getElementById('addSwimmer').disabled = true;
  }
}

function addSwimmerToExtra() {
  var button = document.getElementById('addSwimmer');
  var ajaxUrl = button.dataset.ajaxUrl;
  button.disabled = true;
  var swimmer = (document.getElementById('swimmerSelect')).value;
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    var status = document.getElementById('status');
    if (this.readyState == 4 && this.status == 200) {
      getSwimmers();
      button.disabled = false;
      var response = JSON.parse(this.response);
      status.innerHTML =
      '<div class="mt-3 mb-0 alert alert-dismissible fade show" id="status-alert-box" role="alert">' + response.alertContent +
      '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">' +
      '' +
      '</button>' +
      '</div>';
      document.getElementById('status-alert-box').classList.add(response.alertClass);
      document.getElementById('swimmerSelect').value = 'null';
      swimmerSelectChange();
    } else if (this.readyState == 4) {
      button.disabled = false;
      status.innerHTML =
      '<div class="mt-3 mb-0 alert alert-warning alert-dismissible fade show" role="alert">' +
      '<strong>An unknown error occurred</strong><br>Please try again later' +
      '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">' +
      '' +
      '</button>' +
      '</div>';
    }
  }
  xhttp.open('POST', ajaxUrl, true);
  xhttp.setRequestHeader("content-type", "application/x-www-form-urlencoded");
  xhttp.send("response=insert&swimmerInsert=" + swimmer);
}

function dropSwimmerFromExtra(relation) {
var xhttp = new XMLHttpRequest();
xhttp.onreadystatechange = function() {
  if (this.readyState == 4 && this.status == 200) {
    getSwimmers();
  }
}
xhttp.open("POST", document.getElementById('addSwimmer').dataset.ajaxUrl, true);
xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
xhttp.send("response=dropRelation&relation=" + relation);
}

var entryTable = document.querySelector("#output");
entryTable.addEventListener("click", clickPropogation, false);

function clickPropogation(e) {
  if (e.target !== e.currentTarget) {
      var clickedItem = e.target.id;
      var clickedItemValue;
      if (clickedItem != "") {
        var clickedItemValue = document.getElementById(clickedItem).value;
        dropSwimmerFromExtra(clickedItemValue);
      }
  }
  e.stopPropagation();
}

// Call getResult immediately
getSwimmers();
document.getElementById("squadSelect").onchange=getSwimmersForSquad;
document.getElementById("addSwimmer").onclick=addSwimmerToExtra;
document.getElementById('swimmerSelect').addEventListener('change', swimmerSelectChange);