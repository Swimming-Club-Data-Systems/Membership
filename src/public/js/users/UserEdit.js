document.addEventListener("DOMContentLoaded", function (event) {
  let modal = new bootstrap.Modal(document.getElementById('main-modal'));

  function apply() {
    var type = document.getElementById('accountType');
    var typeValue = type.value;
    var user = type.dataset.userId;
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
      if (this.readyState == 4 && this.status == 200) {
        document.getElementById('accountTypeOutput').innerHTML = this.responseText;
      }
    }
    xhttp.open("POST", type.dataset.ajaxUrl + user, true);
    xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhttp.send('accountType=' + typeValue);
  }

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

  document.getElementById('accountType').onchange = apply;
});