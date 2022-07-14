/**
 * Attendance register javascript handler
 * 
 * For the redesigned register system
 */

// var data = document.getElementById('data-block');

// document.getElementById('date').addEventListener('change', changeHandler);
// document.getElementById('squad').addEventListener('change', changeHandler);
// document.getElementById('session').addEventListener('change', changeHandler);

// function changeHandler() {
//   var date = document.getElementById('date').value;
//   var squad = document.getElementById('squad').value;
//   var session = document.getElementById('session').value;

//   console.log([
//     date,
//     squad,
//     session
//   ]);
// }

function resetRegisterArea() {
  var register = document.getElementById('register');
  register.innerHTML = '<div class="ajaxPlaceholder mb-0">Fill in the details above and we can load the register</div>';
}

function getQueryVariable(variable) {
  var query = window.location.search.substring(1);
  var vars = query.split('&');
  for (var i = 0; i < vars.length; i++) {
    var pair = vars[i].split('=');
    if (decodeURIComponent(pair[0]) == variable) {
      return decodeURIComponent(pair[1]);
    }
  }
  return null;
}

function getSessions(firstLoad = false, callback = null) {
  var data = document.getElementById('data-block');
  var firstLoadSquad = data.dataset.squadInit;
  var fLSession = data.dataset.sessionInit;
  var e = document.getElementById('squad');
  var value = e.options[e.selectedIndex].value;
  if (firstLoad === true) {
    value = firstLoadSquad;
  } else {
    fLSession = null;
  }
  var date = document.getElementById('date');
  console.log(value);
  if (value == '' || value == null) {
    document.getElementById('session').innerHTML = '<option selected>Choose the squad from the menu</option>';
    return;
  } else {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        document.getElementById('session').innerHTML = this.responseText;
        console.log(this.responseText);
        resetRegisterArea();
        typeof callback === 'function' && callback();
      }
    }
    var target = data.dataset.ajaxUrl + '?squadID=' + value + '&selected=' + fLSession + '&date=' + date.value;
    console.log(target);
    xmlhttp.open('GET', target, true);
    xmlhttp.send();
  }
}

function getRegister(firstLoad = false) {
  var data = document.getElementById('data-block');
  var presetSession = data.dataset.sessionInit;
  var e = document.getElementById('session');
  var value = e.options[e.selectedIndex].value;
  if (firstLoad === true) {
    value = presetSession;
  }
  var date = document.getElementById('date');
  var dateValue = date.value;

  var squad = document.getElementById('squad');
  if (value == '') {
    document.getElementById('register').innerHTML =
      '<div class="ajaxPlaceholder mb-0">Fill in the details above to load a register.</div>';
    return;
  } else {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        document.getElementById('register').innerHTML = this.responseText;
        window.history.replaceState('string', 'Title', data.dataset.pageUrl + '?date=' + encodeURI(dateValue) + '&squad=' + encodeURI(squad.value) + '&session=' + encodeURI(value));
      } else {
        console.log(this.readyState);
        console.log(this.status);
        console.log(this.responseText);
      }
    }
    xmlhttp.open('GET', data.dataset.ajaxUrl + '?sessionID=' + value + '&date=' + dateValue,
      true);
    xmlhttp.send();
  }
}

document.getElementById('date').onchange = getSessions;
document.getElementById('squad').onchange = getSessions;
document.getElementById('session').onchange = getRegister;

function updateTime() {
  var datetimeScreenOutput = document.getElementById('dtOut');
  if (datetimeScreenOutput != null) {
    var today = new Date();
    datetimeScreenOutput.textContent = today.toLocaleTimeString(undefined, {
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit'
    });
  }
}
var intervalID = window.setInterval(updateTime, 250);

/**
 * On load if these are set load the register
 */

function setSessionAndGetRegister() {
  document.getElementById('session').value = getQueryVariable('session');
  getRegister();
}

var date = getQueryVariable('date');
var squad = getQueryVariable('squad');
var session = getQueryVariable('session');
if (date !== null && squad !== null && session !== null) {
  document.getElementById('date').value = date;
  document.getElementById('squad').value = squad;
  getSessions(false, setSessionAndGetRegister);
} else {
  resetRegisterArea();
}