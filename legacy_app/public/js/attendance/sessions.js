const ajaxInfo = document.getElementById('ajax-info').dataset;
let addModal = document.getElementById('add-session-modal');

let recurrenceChange = ((ev = null) => {
  let showUntil = document.getElementById('session-end-date');
  let showUntilContainer = document.getElementById('show-until-container');
  let onlyIfBooking = document.getElementsByClassName('show-if-one-off');
  if (document.querySelector('input[name="recurring"]:checked').value === 'recurring') {
    showUntil.required = true;
    showUntilContainer.classList.remove('d-none');
    for (let item of onlyIfBooking) {
      item.classList.add('d-none');
    }
  } else {
    showUntil.required = false;
    showUntilContainer.classList.add('d-none');
    for (let item of onlyIfBooking) {
      item.classList.remove('d-none');
    }
  }
});

document.getElementById('recurrence-radios').addEventListener('change', recurrenceChange);

document.getElementById('new-session-form').addEventListener('submit', ev => {
  ev.preventDefault();

  if (ev.target.checkValidity()) {
    let form = new FormData(ev.target);
    console.log(form);

    // Handle submission (AJAX it)
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
      if (this.readyState == 4 && this.status == 200) {
        let response = JSON.parse(this.responseText);
        if (response.status) {
          // Reload sessions, hide modal, reset values]
          if (response.redirect) {
            window.location.href = response.redirect;
          } else {
            getSessions();
            addModal.hide();
          }
        } else {
          // Error
          alert(response.errors);
        }
      }
    }
    xhr.open('POST', ajaxInfo.ajaxAddSessionUrl, true);
    xhr.send(form);
  }
});

addModal.addEventListener('hidden.bs.modal', function (event) {
  let resetForms = document.getElementsByClassName('add-session-form-reset-input');
  document.getElementById('recurring-session').checked = true;
  document.getElementById('main-sequence-all').checked = true;
  for (let input of resetForms) {
    if (input.dataset.defaultValue) {
      input.value = input.dataset.defaultValue;
    } else {
      input.value = '';
    }
  }
  resetForms = document.getElementsByClassName('add-session-form-squad-checkboxes');
  for (let input of resetForms) {
    input.checked = false;
  }
  // Handle changes required
  recurrenceChange();

  // Remove form was-validated class
  document.getElementById('new-session-form').classList.remove('was-validated');
});

addModal.addEventListener('show.bs.modal', function (event) {
  let currentSquad = document.getElementById('squad').value;
  if (currentSquad) {
    let squadCheck = document.getElementById('squad-check-' + currentSquad);
    if (squadCheck) {
      squadCheck.checked = true;
    }
  }
});

// ---------------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------


function resetRegisterArea() {
  var register = document.getElementById('output');
  register.innerHTML = '<div class="ajaxPlaceholder mb-0">Fill in the details above and we can load the register</div>';
}

function getSessions() {
  var squad = document.getElementById('squad');
  var squadValue = squad.options[squad.selectedIndex].value;
  if (squadValue == 0) {
    document.getElementById('output').innerHTML = '<div class="ajaxPlaceholder"><strong>Session Manager will appear here</strong> <br>Select a squad first</div>';
    return;
  } else {
    var sessAjax = new XMLHttpRequest();
    sessAjax.onreadystatechange = function () {
      if (this.readyState == 4 && this.status == 200) {
        document.getElementById('output').innerHTML = this.responseText;
        window.history.replaceState('string', 'Title', ajaxInfo.pageUrl + '?squad=' + squadValue);
      }
    }
    sessAjax.open('POST', ajaxInfo.ajaxUrl, true);
    sessAjax.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    sessAjax.send('action=getSessions&squadID=' + squadValue);
  }
}

function addSession() {
  var squad = document.getElementById('squad');
  var squadValue = squad.options[squad.selectedIndex].value;

  var sessionName = document.getElementById('newSessionName');
  var sessionNameValue = sessionName.value;

  var sessionDay = document.getElementById('newSessionDay');
  var sessionDayValue = sessionDay.options[sessionDay.selectedIndex].value;

  var sessionVenue = document.getElementById('newSessionVenue');
  var sessionVenueValue = sessionVenue.options[sessionVenue.selectedIndex].value;

  var sessionStart = document.getElementById('newSessionStartTime');
  var sessionStartValue = sessionStart.value;

  var sessionEnd = document.getElementById('newSessionEndTime');
  var sessionEndValue = sessionEnd.value;

  var startDate = document.getElementById('newSessionStartDate');
  var startDateValue = startDate.value;

  var endDate = document.getElementById('newSessionEndDate');
  var endDateValue = endDate.value;

  var mainSequenceValue = null;
  var radios = document.getElementsByName('newSessionMS');
  for (var i = 0, length = radios.length; i < length; i++) {
    if (radios[i].checked) {
      // do whatever you want with the checked radio
      mainSequenceValue = radios[i].value;

      // only one radio can be logically checked, don\'t check the rest
      break;
    }
  }

  var sessAjax = new XMLHttpRequest();
  sessAjax.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      var json = JSON.parse(this.responseText);
      document.getElementById('output').innerHTML = json.sessions_view;

      // Show alert
      document.getElementById('status-message').innerHTML = '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Success!</strong> We\'ve added the session to the system. If the session starts in the future, it won\'t appear on this page until that day.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    } else if (this.readyState == 4 && this.status != 200) {
      // Show alert
      document.getElementById('status-message').innerHTML = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>We could not add the session!</strong> Please check the details and try again.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    }
  }
  sessAjax.open('POST', ajaxInfo.ajaxUrl, true);
  sessAjax.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  sessAjax.send('action=addSession&squadID=' + squadValue + '&sessionName=' + sessionNameValue + '&venueID=' + sessionVenueValue + '&sessionDay=' + sessionDayValue + '&startTime=' + sessionStartValue + '&endTime=' + sessionEndValue + '&newSessionMS=' + mainSequenceValue + '&newSessionStartDate=' + startDateValue + '&newSessionEndDate=' + endDateValue);
}

// Load squad sessions and add event listener
getSessions();
document.getElementById('squad').onchange = getSessions;