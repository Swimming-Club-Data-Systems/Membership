function getResult() {
  var data = document.getElementById('entry-details');
  var gala = document.getElementById('galaID');
  var galaValue = gala.options[gala.selectedIndex].value;
  var search = document.getElementById('search');
  var searchValue = search.value;
  var sex = document.getElementById('sex');
  var sexValue = sex.value;
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      document.getElementById('output').innerHTML = this.responseText;
      window.history.replaceState('string', 'Title', data.dataset.pageUrl + '?gala=' + galaValue + '&sex=' +
        sexValue + '&search=' + searchValue);
    } else {
      // console.log(this.status);
    }
  }
  var ajaxRequest = data.dataset.ajaxUrl + '?galaID=' + galaValue + '&sex=' + sexValue + '&search=' +
    searchValue;
  xmlhttp.open('GET', ajaxRequest, true);
  xmlhttp.send();
}
// Call on page load
getResult();

document.getElementById('galaID').onchange = getResult;
document.getElementById('search').oninput = getResult;
document.getElementById('sex').oninput = getResult;

document.querySelectorAll('*[id^="processedEntry-"]');

var entryTable = document.querySelector('#output');
entryTable.addEventListener('click', clickPropogation, false);

function clickPropogation(e) {
  if (e.target !== e.currentTarget) {
    var clickedItem = e.target.id;
    var clickedItemChecked;
    if (clickedItem != '') {
      var item = document.getElementById(clickedItem);
      var clickedItemChecked = item.checked;
      // console.log(clickedItem);
      // console.log(clickedItemChecked);
      if (item.dataset.buttonAction == 'mark-processed') {
        markProcessed(clickedItem, clickedItemChecked);
      } else if (item.dataset.buttonAction == 'mark-paid') {
        markPaid(clickedItem, clickedItemChecked);
      }
    }
  }
  e.stopPropagation();
}

function markProcessed(clickedItem, clickedItemChecked) {
  var data = document.getElementById('entry-details');
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      // document.getElementById(clickedItem).innerHTML = 'WORKED' /*this.responseText*/;
    }
  };
  xhttp.open('POST', data.dataset.processedUrl, true);
  // console.log('POST', data.dataset.processedUrl, true);
  xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhttp.send('processedID=' + clickedItem + '&clickedItemChecked=' + clickedItemChecked + '&verify=markProcessed');
  // console.log('processedID=' + clickedItem + '&clickedItemChecked=' + clickedItemChecked + '&verify=markProcessed')
  // console.log('Sent');
}

function markPaid(clickedItem, clickedItemChecked) {
  var data = document.getElementById('entry-details');
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      // document.getElementById(clickedItem).innerHTML = 'WORKED' /*this.responseText*/;
    }
  };
  xhttp.open('POST', data.dataset.processedUrl, true);
  // console.log('POST', data.dataset.processedUrl, true);
  xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhttp.send('processedID=' + clickedItem + '&clickedItemChecked=' + clickedItemChecked + '&verify=markPaid');
  // console.log('processedID=' + clickedItem + '&clickedItemChecked=' + clickedItemChecked + '&verify=markPaid');
  // console.log('Sent');
}