/**
 * Edit.js for editing user details
 */

/**
 * Function to show/hide fields
 * 
 * @param {string} radio name of radio
 * @param {string} box id of box
 */
function toggleVisible(radio, box) {
  var radioButtons = document.forms['main-form'].elements[radio];

  radioButtons.forEach(element => {
    element.addEventListener('change', function () {
      if (radioButtons.value == 1) {
        document.getElementById(box).classList.remove('d-none');
      } else {
        document.getElementById(box).classList.add('d-none');
      }
    });
  });
}

/**
 * Function to show/hide fields
 * 
 * @param {string} radio name of radio
 * @param {string} radios radios
 */
function toggleEnabled(radio, radios) {
  var radioButtons = document.forms['main-form'].elements[radio];

  radioButtons.forEach(element => {
    element.addEventListener('change', function () {
      if (radioButtons.value == 1) {
        enableDisable(radios, false);
      } else {
        enableDisable(radios, true);
      }
    });
  });
}

function enableDisable(radio, state) {
  var radioButtons = document.forms['main-form'].elements[radio];

  radioButtons.forEach(element => {
    element.disabled = state;
  });
}

function emailInvalid(email, errorMessage = 'Unknown problem.') {
  email.setCustomValidity(errorMessage);
  // email.dataset.valid = false;
}

function handleEmail(event) {
  var email = event.target;
  if (email.value) {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
      if (this.readyState == 4 && this.status == 200) {
        var result = JSON.parse(this.responseText);
        if (result.valid) {
          email.setCustomValidity("");
          // email.dataset.valid = true;
        } else if (!result.valid) {
          emailInvalid(email, result.message);
          document.getElementById('email-error-message').textContent = result.message;
        } else {
          emailInvalid(email);
        }
      }
    }
    var target = email.dataset.ajaxUrl;
    var params = 'email=' + encodeURIComponent(email.value);
    // Make an ajax request to search for user
    xhr.open('POST', target, true);
    xhr.setRequestHeader('X-Requested-With', 'xhrRequest');
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.send(params);
  } else {
    var message = 'Please provide a valid email address.';
    emailInvalid(email, message);
    document.getElementById('email-error-message').textContent = message;
  }
}

var email = document.getElementById('email-address');
email.addEventListener('input', handleEmail);

toggleVisible('is-se-member', 'asa-details');
if (!JSON.parse(document.getElementById('club-membership-status').dataset.swimmerToo)) {
  toggleVisible('is-club-member', 'club-details');
}
toggleEnabled('is-se-primary-club', 'se-club-pays');

(function() {
  'use strict';
  window.addEventListener('load', function() {
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.getElementsByClassName('needs-validation');
    // Loop over them and prevent submission
    var validation = Array.prototype.filter.call(forms, function(form) {
      form.addEventListener('submit', function(event) {
        if (form.checkValidity() === false || !JSON.parse(document.getElementById('email-address').dataset.valid)) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    });
  }, false);
})();
