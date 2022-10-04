
function getSwimmers() {
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      document.getElementById('output').innerHTML = this.responseText;
    }
  }
  xhttp.open('POST', document.getElementById('addSwimmer').dataset.ajaxUrl, true);
  xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhttp.send('response=getSwimmers');
}

function getSwimmersForSquad() {
  var squad = document.getElementById('squadSelect').value;
  var swimmerSelect = document.getElementById('swimmerSelect');
  var addButton = document.getElementById('addSwimmer');
  swimmerSelect.value = 'null';
  swimmerSelect.disabled = true;
  addButton.disabled = true;
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      var response = JSON.parse(this.responseText);
      swimmerSelect.innerHTML = response.swimmerSelectContent;
      swimmerSelect.disabled = !response.status;
    }
  }
  xhttp.open('POST', addButton.dataset.ajaxUrl, true);
  xhttp.setRequestHeader('content-type', 'application/x-www-form-urlencoded');
  xhttp.send('response=squadSelect&squadSelect=' + squad);
}

function swimmerSelectChange(event) {
  if (document.getElementById('swimmerSelect').value != 'null') {
    document.getElementById('addSwimmer').disabled = false;
  } else {
    document.getElementById('addSwimmer').disabled = true;
  }
}

function userSelectChange(event) {
  if (document.getElementById('user-select').value != 'null') {
    document.getElementById('user-add').disabled = false;
  } else {
    document.getElementById('user-add').disabled = true;
  }
}

function addSwimmerToList() {
  var swimmer = (document.getElementById('swimmerSelect')).value;
  if (swimmer !== 'Select a swimmer') {
      var xhttp = new XMLHttpRequest();
      xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        getSwimmers();
        getSwimmersForSquad();
        document.getElementById("status").innerHTML =
        '<div class="mt-3 mb-0 alert alert-success alert-dismissible fade show" role="alert">' +
        '<strong>Successfully Added Swimmer</strong>'  +
        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">' +
        '' +
        '</button>' +
        '</div>';
      } else {
        document.getElementById("status").innerHTML =
        '<div class="mt-3 mb-0 alert alert-warning alert-dismissible fade show" role="alert">' +
        '<strong>Unable to add swimmer</strong><br>They may already be on the list' +
        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">' +
        '' +
        '</button>' +
        '</div>';
      }
    }
    xhttp.open('POST', document.getElementById('addSwimmer').dataset.ajaxUrl, true);
    xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhttp.send('response=insert&swimmerInsert=' + swimmer);
  } else {
    document.getElementById('status').innerHTML =
    '<div class="mt-3 mb-0 alert alert-warning alert-dismissible fade show" role="alert">' +
    '<strong>No swimmer selected</strong><br>Please select a swimmer to add to this list' +
    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">' +
    '' +
    '</button>' +
    '</div>';
  }
}

function getUsers() {
  var userSearchTerm = document.getElementById('user-name-search').value;
  var userSelect = document.getElementById('user-select');
  var addButton = document.getElementById('user-add');
  userSelect.value = 'null';
  userSelect.disabled = true;
  addButton.disabled = true;
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      var response = JSON.parse(this.responseText);
      userSelect.innerHTML = response.userSelectContent;
      userSelect.disabled = !response.status;
    }
  }
  xhttp.open('POST', addButton.dataset.ajaxUrl, true);
  xhttp.setRequestHeader('content-type', 'application/x-www-form-urlencoded');
  xhttp.send('response=userSelect&searchTerm=' + userSearchTerm);
}

function addUserToList() {
  var swimmer = (document.getElementById('user-select')).value;
  if (swimmer !== 'Select user') {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        getSwimmers();
        document.getElementById("user-status").innerHTML =
        '<div class="mt-3 mb-0 alert alert-success alert-dismissible fade show" role="alert">' +
        '<strong>Successfully Added Swimmer</strong>'  +
        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">' +
        '' +
        '</button>' +
        '</div>';
      } else {
        document.getElementById("user-status").innerHTML =
        '<div class="mt-3 mb-0 alert alert-warning alert-dismissible fade show" role="alert">' +
        '<strong>Unable to add swimmer</strong><br>They may already be on the list' +
        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">' +
        '' +
        '</button>' +
        '</div>';
      }
    }
    xhttp.open('POST', document.getElementById('user-add').dataset.ajaxUrl, true);
    xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhttp.send('response=insert-user&swimmerInsert=' + swimmer);
  } else {
    document.getElementById('user-status').innerHTML =
    '<div class="mt-3 mb-0 alert alert-warning alert-dismissible fade show" role="alert">' +
    '<strong>No swimmer selected</strong><br>Please select a user to add to this list' +
    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">' +
    '' +
    '</button>' +
    '</div>';
  }
}

function dropSwimmerFromList(relation) {
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      getSwimmers();
    }
  }
  xhttp.open('POST', document.getElementById('addSwimmer').dataset.ajaxUrl, true);
  xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhttp.send('response=dropRelation&relation=' + relation);
  console.log(relation);
}

var entryTable = document.querySelector('#output');
entryTable.addEventListener('click', clickPropogation, false);

function clickPropogation(e) {
  if (e.target !== e.currentTarget) {
    var clickedItem = e.target.id;
    var clickedItemValue;
    if (clickedItem != '') {
      var clickedItemValue = document.getElementById(clickedItem).value;
      dropSwimmerFromList(clickedItemValue);
    }
  }
  e.stopPropagation();
}

// Call getResult immediately
getSwimmers();
getSwimmersForSquad();
document.getElementById('squadSelect').onchange=getSwimmersForSquad;
document.getElementById('addSwimmer').onclick=addSwimmerToList;document.getElementById('user-add').onclick=addUserToList;
document.getElementById('swimmerSelect').addEventListener('change', swimmerSelectChange);
document.getElementById('user-select').addEventListener('change', userSelectChange);
document.getElementById('user-name-search').addEventListener('input', getUsers);