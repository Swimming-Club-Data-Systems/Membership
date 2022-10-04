let modal = new bootstrap.Modal(document.getElementById('main-modal'));

function deleteCoachFromSquad(user, squad) {
  let url = document.getElementById('coach-squad-assign').dataset.ajaxUrl;
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      // Decode
      let json = JSON.parse(this.responseText);
      if (json.status == 200) {
        // All good
        getCoachSquadList();
      } else {
        alert(json.message);
      }
    } else if (this.readyState == 4) {
      // Not ok
      alert('An error occurred which meant we could not delete this user\'s assignment to the squad.');
    }
  }
  xhttp.open('POST', url, true);
  xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhttp.send('operation=revoke&user=' + encodeURI(user) + '&squad=' + encodeURI(squad));
}

function getCoachSquadList() {
  var listUrl = document.getElementById('coach-squad-list').dataset.ajaxUrl;
  var userId = document.getElementById('coach-squad-list').dataset.userId;

  // Get a list of assigned squads
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      var json = JSON.parse(this.responseText);
      if (json.status == 500) {
        // Failed
        document.getElementById('coach-squad-list').innerHTML = '<div class="alert alert-danger"><p class="mb-0"><strong>An error occurred.</strong></p><p class="mb-0">We\'re unable to load the list of squads for this user. Please try refreshing the page.</p></div>';
      } else {
        // Success

        // Update selectable squad list
        if (json.squadSelects) {
          document.getElementById('coach-squad').dataset.squadList = json.squadSelects;
        }

        // Control assign button visibility
        if (json.canAssign) {
          document.getElementById('coach-squad-assign-container').classList.remove('d-none');
        } else {
          document.getElementById('coach-squad-assign-container').classList.add('d-none');
        }

        // If array has items
        if (json.squads.length > 0) {
          document.getElementById('coach-squad-list').textContent = '';
          let listGroup = document.createElement('UL');
          listGroup.classList.add('list-group', 'mb-3');
          json.squads.forEach(squad => {
            // Create list item
            let listItem = document.createElement('LI');
            listItem.classList.add('list-group-item');

            // Create row
            let row = document.createElement('DIV');
            row.classList.add('row', 'justify-content-between', 'align-items-center');

            // Squad name field
            let nameCol = document.createElement('DIV');
            nameCol.classList.add('col-auto');
            let squadBold = document.createElement('STRONG');
            let squadText = document.createTextNode(squad.squad);
            squadBold.appendChild(squadText);
            let nameText = document.createTextNode(', ' + squad.coachTypeDescription);
            nameCol.appendChild(squadBold);
            nameCol.appendChild(nameText);

            // Delete button field
            let deleteCol = document.createElement('DIV');
            deleteCol.classList.add('col-auto');
            let deleteButton = document.createElement('BUTTON');
            deleteButton.classList.add('btn', 'btn-danger');
            let deleteText = document.createTextNode('Remove');
            deleteButton.appendChild(deleteText);
            deleteButton.dataset.id = squad.id;
            deleteCol.appendChild(deleteButton);

            // Add cols to row
            row.appendChild(nameCol);
            row.appendChild(deleteCol);

            listItem.appendChild(row);
            listGroup.appendChild(listItem);
          });

          document.getElementById('coach-squad-list').appendChild(listGroup);

          document.getElementById('coach-squad-list').addEventListener('click', function (event) {
            if (event.target.tagName == 'BUTTON') {
              // Delete
              deleteCoachFromSquad(document.getElementById('coach-squad-list').dataset.userId, event.target.dataset.id);
            }
          });
        } else {
          // Note no squads
          document.getElementById('coach-squad-list').innerHTML = '<div class="alert alert-warning"><p class="mb-0"><strong>User is not assigned to any squads.</strong></p></div>';
        }
      }
    }
  }
  xhttp.open('POST', listUrl, true);
  xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhttp.send('user=' + userId);
}


document.addEventListener('DOMContentLoaded', function (event) {

  function confirmResendRegistrationEmail(event) {
    event.preventDefault();

    var resendButton = event.target;

    // Set modal title
    document.getElementById('main-modal-title').textContent = 'Confirm resending registration email';

    // Set buttons
    document.getElementById('main-modal-footer').innerHTML = '<button type="button" class="btn btn-dark" data-bs-dismiss="modal">Cancel</button><button type="button" id="modal-confirm-button" class="btn btn-success">Confirm resend</button>';

    // Compose modal body content
    var bodyContent = '<p>Are you sure you want to resend the registration email to <span id="modal-user-name"></span>?</p>';
    bodyContent += '<p>If the original email did not arrive, please check if the email address for this user is correct and <a href="#" id="modal-user-edit-link">edit it</a> before sending another registration email.</p>';
    bodyContent += '<div class="alert alert-warning mb-0">As a security precaution, we\'ll cancel the old registration link sent to this user.</div>';
    document.getElementById('main-modal-body').innerHTML = bodyContent;
    document.getElementById('modal-user-name').textContent = resendButton.dataset.userName;
    document.getElementById('modal-user-edit-link').href = resendButton.dataset.userEditLink;

    // Setup confirm event listeners
    document.getElementById('modal-confirm-button').addEventListener('click', resendRegistrationEmail);

    modal.show();
  }

  function resendRegistrationEmail() {
    var resendButton = document.getElementById('registration-resend-button');
    document.getElementById('modal-confirm-button').disabled = true;
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
      if (this.readyState == 4 && this.status == 200) {
        var json = JSON.parse(this.responseText);

        modal.hide();
        // Setup resend status alert
        var resendStatus = document.getElementById('resend-status');
        resendStatus.innerHTML = '<div id="resend-status-alert"><div id="resend-status-content"></div><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div></div>';

        var resendStatusAlert = document.getElementById('resend-status-alert');
        resendStatusAlert.classList.add('alert', 'alert-dismissible', 'fade', 'show');

        // Setup resend status alert content
        var resendStatusContent = document.getElementById('resend-status-content');
        resendStatusContent.innerHTML = json.alertContent;

        // Setup resend status alert contextual class based on server response
        resendStatusAlert.classList.add(json.alertContextualClass);

        if (json.status === true) {
          resendButton.disabled = true;
        }

        document.getElementById('modal-confirm-button').disabled = false;
      }
    }
    xhttp.open("POST", resendButton.dataset.ajaxUrl, true);
    xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhttp.send('user=' + resendButton.dataset.user);
  }

  var resendButton = document.getElementById('registration-resend-button');
  if (resendButton) {
    resendButton.addEventListener('click', confirmResendRegistrationEmail)
  }

  let button = document.getElementById('delete-button');

  button.addEventListener('click', function (event) {
    document.getElementById('main-modal-title').textContent = 'Delete ' + button.dataset.userName + '\'s account';

    let body = '<p>By deleting this account, we will remove all personal information. Financial information about direct debit or card payments will be retained for the completeness of your club\'s records.</p>';

    body = body + '<p>We will cancel all existing direct debit mandates for this use. This may cause any pending payments or payment retries to fail.</p>';

    // body = body + '<p>We will send a confirmation email to yourself and the user you are deleting when we have successfully deleted their information.</p>';

    body = body + '<div class="form-group mb-0"><label for="confirm-password">Confirm your password to proceed</label><input autocomplete="current-password" type="password" class="form-control" id="confirm-password" aria-describedby="pwHelp"><small id="pwHelp" class="form-text text-muted">This is an added security measure. Deleting a user\'s account is not reversible.</small></div>';

    document.getElementById('main-modal-body').innerHTML = body;

    document.getElementById('main-modal-footer').innerHTML = '<button type="button" class="btn btn-dark" data-bs-dismiss="modal">Cancel</button><button type="button" id="modal-confirm-button" class="btn btn-danger">Delete account</button>';

    document.getElementById('modal-confirm-button').addEventListener('click', function (event) {
      let button = document.getElementById('delete-button');
      document.getElementById('modal-confirm-button').disabled = true;
      let password = document.getElementById('confirm-password').value;
      let removeableAlert = document.getElementById('main-modal-alert');
      if (removeableAlert) {
        removeableAlert.outerHTML = '';
      }
      let oldContent = document.getElementById('main-modal-body').innerHTML;
      document.getElementById('main-modal-body').innerHTML = '<div class="py-5 text-center"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div></div>';

      var xhttp = new XMLHttpRequest();
      xhttp.onreadystatechange = function () {
        if (this.readyState == 4) {
          var json = JSON.parse(this.responseText);
          if (json.status == 500) {
            // Failed
            document.getElementById('main-modal-body').innerHTML = '<div class="alert alert-danger" id="main-modal-alert"><p class="mb-0"><strong>An error occurred.</strong></p><p id="alert-text" class="mb-0"></p></div>' + oldContent;
            document.getElementById('modal-confirm-button').disabled = false;
          } else {
            // Success
            document.getElementById('main-modal-body').innerHTML = '<div class="alert alert-success mb-0"><p class="mb-0"><strong>Success.</strong></p><p id="alert-text" class="mb-0"></p></div>';
            document.getElementById('main-modal-footer').innerHTML = '<a id="close-link" href="" class="btn btn-dark">Close</a>';
            document.getElementById('close-link').href = document.getElementById('delete-button').dataset.usersUrl;
          }
          document.getElementById('alert-text').textContent = json.message;
        }
      }
      xhttp.open('POST', button.dataset.ajaxUrl, true);
      xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
      xhttp.send('user=' + button.dataset.userId + '&password=' + password);
    });

    modal.show();
  });

  let coachSquad = document.getElementById('coach-squad');

  if (coachSquad) {

    getCoachSquadList();

    // Handle assignment
    document.getElementById('coach-squad-assign').addEventListener('click', function (event) {
      json = JSON.parse(document.getElementById('coach-squad').dataset.squadList);
      if (json) {
        document.getElementById('main-modal-title').textContent = 'Assign a squad';

        let body = document.getElementById('main-modal-body');

        body.textContent = '';

        let p = document.createElement('P');
        p.appendChild(document.createTextNode('Please choose a squad and role for the user.'));

        let roleInfo = document.createElement('P');
        roleInfo.appendChild(document.createTextNode('Roles help indicate to members the seniority of this person with respect to the squad.'));

        body.appendChild(p);
        body.appendChild(roleInfo);

        // SQUAD
        let formGroup = document.createElement('DIV');
        formGroup.classList.add('mb-3');
        let selectLabel = document.createElement('LABEL');
        selectLabel.classList.add('form-label');
        selectLabel.appendChild(document.createTextNode('Select a squad'));
        selectLabel.setAttribute('for', 'squad-coach-select');

        let select = document.createElement('SELECT');
        select.classList.add('form-select');
        select.id = 'squad-coach-select';
        select.setAttribute('required', true);

        let option = document.createElement('OPTION');
        option.value = 'NONE';
        option.appendChild(document.createTextNode('Select a squad'));
        option.setAttribute('selected', true);
        option.setAttribute('disabled', true);
        select.appendChild(option);

        squads = json.squads;
        squads.forEach(squad => {
          let option = document.createElement('OPTION');
          option.value = squad.id;
          option.appendChild(document.createTextNode(squad.name));
          select.appendChild(option);
        });

        formGroup.appendChild(selectLabel);
        formGroup.appendChild(select);

        body.appendChild(formGroup);

        // TYPE
        formGroup = document.createElement('DIV');
        formGroup.classList.add('mb-0');
        selectLabel = document.createElement('LABEL');
        selectLabel.classList.add('form-label');
        selectLabel.appendChild(document.createTextNode('Select a role'));
        selectLabel.setAttribute('for', 'coach-role-select');

        select = document.createElement('SELECT');
        select.classList.add('form-select');
        select.id = 'coach-role-select';
        select.setAttribute('required', true);

        option = document.createElement('OPTION');
        option.value = 'NONE';
        option.appendChild(document.createTextNode('Select a role'));
        option.setAttribute('selected', true);
        option.setAttribute('disabled', true);
        select.appendChild(option);

        roles = json.roles;
        roles.forEach(role => {
          let option = document.createElement('OPTION');
          option.value = role.code;
          option.appendChild(document.createTextNode(role.description));
          select.appendChild(option);
        });

        formGroup.appendChild(selectLabel);
        formGroup.appendChild(select);

        body.appendChild(formGroup);

        document.getElementById('main-modal-footer').innerHTML = '<button type="button" class="btn btn-dark" data-bs-dismiss="modal">Cancel</button><button type="button" id="modal-confirm-button" class="btn btn-success">Assign squad</button>';

        document.getElementById('modal-confirm-button').addEventListener('click', function (event) {
          let assignButton = document.getElementById('coach-squad-assign');
          let squadSelect = document.getElementById('squad-coach-select');
          let roleSelect = document.getElementById('coach-role-select');
          if (squadSelect.value != 'NONE' && roleSelect.value != 'NONE') {
            document.getElementById('modal-confirm-button').disabled = true;
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function () {
              if (this.readyState == 4 && this.status == 200) {
                console.log(this.responseText);
                var json = JSON.parse(this.responseText);
                if (json.status == 200) {
                  modal.hide();
                  getCoachSquadList();
                } else {
                  document.getElementById('modal-confirm-button').disabled = false;
                  alert(json.message);
                }
              }
            }
            xhttp.open('POST', assignButton.dataset.ajaxUrl, true);
            xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhttp.send('operation=assign&user=' + encodeURI(assignButton.dataset.userId) + '&squad=' + encodeURI(squadSelect.value) + '&role=' + encodeURI(roleSelect.value));
          } else {
            // Report an error
            alert('Please select a squad and role.');
          }
        });

        modal.show();
      } else {
        alert('Squad list not loaded.');
      }
    });

  }
});

let earlyPayment = document.getElementById('trigger-early-payment');
if (earlyPayment) {
  earlyPayment.addEventListener('click', ev => {
    document.getElementById('main-modal-title').textContent = 'Trigger an early payment';
    let body = document.getElementById('main-modal-body');

    let httpRequest = new XMLHttpRequest();
    httpRequest.onreadystatechange = function () {
      // Process the server response here.
      if (httpRequest.readyState === XMLHttpRequest.DONE && httpRequest.status === 200) {
        // Everything is good, the response was received.
        body.innerHTML = httpRequest.responseText;

        let selectionArea = document.getElementById('payment-selector');
        if (selectionArea) {
          selectionArea.addEventListener('change', handleEarlyPaymentSelections);
        }

        document.getElementById('main-modal-footer').innerHTML = '<button type="button" class="btn btn-dark" data-bs-dismiss="modal">Cancel</button><button disabled type="submit" id="modal-confirm-button" class="btn btn-success" form="trigger-early-payment-form">Confirm charge</button>';

        let form = document.getElementById('trigger-early-payment-form');
        if (form) {
          form.addEventListener('submit', handleEarlyPaymentForm);
          document.getElementById('confirm-charge').addEventListener('change', ev => {
            if (ev.target.checked) {
              document.getElementById('modal-confirm-button').disabled = false;
            } else {
              document.getElementById('modal-confirm-button').disabled = true;
            }
          })
        }

        modal.show();
      } else if (httpRequest.readyState === XMLHttpRequest.DONE) {
        alert(httpRequest.status + " Error. Please try again later.")
      } else {
        // Not ready yet.
      }
    };

    httpRequest.open('POST', ev.target.dataset.infoUrl, true);
    httpRequest.send();
  });
}

handleEarlyPaymentSelections = (ev) => {
  console.log(ev);

  let totalSpan = document.getElementById('early-total');
  if (!totalSpan) return;

  let total = parseInt(totalSpan.dataset.total);

  if (ev.target.nodeName === 'INPUT' && ev.target.type === 'checkbox') {
    if (ev.target.checked) {
      total += parseInt(ev.target.dataset.amount);
    } else {
      total -= parseInt(ev.target.dataset.amount);
    }

    if (total < 100) {
      document.getElementById('confirm-charge').checked = false;
      document.getElementById('confirm-charge').disabled = true;
      document.getElementById('modal-confirm-button').disabled = true;
      document.getElementById('too-low-warning').classList.remove('d-none');
    } else {
      document.getElementById('confirm-charge').disabled = false;
      document.getElementById('too-low-warning').classList.add('d-none');
    }

    totalSpan.dataset.total = total;
    // totalSpan.textContent = total;
    totalSpan.textContent = (new BigNumber(total)).shiftedBy(-2).decimalPlaces(2).toFormat(2);
  }
}

handleEarlyPaymentForm = (ev) => {
  ev.preventDefault();

  let totalSpan = document.getElementById('early-total');
  if (!totalSpan) return;

  let total = parseInt(totalSpan.dataset.total);

  if (total < 100) {
    alert('An error occurred');
    return;
  }

  let confirmButton = document.getElementById('modal-confirm-button')
  confirmButton.disabled = true;
  confirmButton.textContent = 'LOADING';

  let formdata = new FormData(ev.target);
  let httpRequest = new XMLHttpRequest();
  httpRequest.onreadystatechange = function () {
    let body = document.getElementById('main-modal-body');
    if (httpRequest.readyState === XMLHttpRequest.DONE && httpRequest.status === 200) {
      // Everything is good, the response was received.
      body.innerHTML = '<p class="mb-0">Early charge successfully applied.</p>';
      document.getElementById('main-modal-footer').innerHTML = '<button type="button" class="btn btn-dark" data-bs-dismiss="modal">Dismiss</button>';
    } else if (httpRequest.readyState === XMLHttpRequest.DONE) {
      body.innerHTML = '<div class="alert alert-danger mb-0"><p class="mb-0"><strong>Error ' + httpRequest.status + '</strong></p><p class="mb-0">Please try again later</p></div>';
      document.getElementById('main-modal-footer').innerHTML = '<button type="button" class="btn btn-dark" data-bs-dismiss="modal">Dismiss</button>';
    } else {
      // Not ready yet.
    }
  };
  httpRequest.open('POST', ev.target.dataset.submitUrl, true);
  httpRequest.send(formdata);
}