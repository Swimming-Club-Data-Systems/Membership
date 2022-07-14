/**
 * Code to handle invoice payment form
 */

function clearPaymentAreas() {
  document.getElementById('user-info-box').innerHTML = '';
  document.getElementById('payment-details').classList.add('d-none');
  document.getElementById('payment-submit-button').disabled = true;
}

function getUser(event) {
  var user = event.target.value;

  if (user != 'none') {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        var result = JSON.parse(this.responseText);
        if (result.has_result) {
          document.getElementById('user-info-box').innerHTML = '<div class="cell"><h2 id="user-info-name"></h2><dl class="row mb-0"><dt class="col-sm-3">Email</dt><dd class="col-sm-9 text-truncate"><a href="" id="user-info-email"></a></dd><dt class="col-sm-3">Phone</dt><dd class="col-sm-9"><a href="" id="user-info-phone"></a></dd><dt class="col-sm-3">Address</dt><dd class="col-sm-9 mb-0"><span id="user-info-address"></span></dd></div>';
          document.getElementById('user-info-name').textContent = result.forename + ' ' + result.surname;
          document.getElementById('user-info-email').textContent = result.email;
          document.getElementById('user-info-email').href = 'mailto:' + result.email;
          document.getElementById('user-info-phone').textContent = result.mobile_national;
          document.getElementById('user-info-phone').href = result.mobile_rfc3966;

          var addressSpan = document.getElementById('user-info-address');
          if (result.hasOwnProperty('address') && result.address !== null) {
            if (result.address.hasOwnProperty('streetAndNumber')) {
              addressSpan.textContent = result.address.streetAndNumber;
            }
            if (result.address.hasOwnProperty('city')) {
              addressSpan.textContent = addressSpan.textContent + ', ' + result.address.city;
            }
            if (result.address.hasOwnProperty('postCode')) {
              addressSpan.textContent = addressSpan.textContent + ', ' + result.address.postCode;
            }
          } else {
            addressSpan.textContent = 'No address on file';
          }

          document.getElementById('user-id').value = result.id;

          document.getElementById('payment-submit-button').disabled = false;

          document.getElementById('payment-details').classList.remove('d-none');
        } else {
          clearPaymentAreas();
        }
      }
    }
    var target = event.target.dataset.ajaxUrl;
    var params = 'user=' + encodeURIComponent(user);
    // Make an ajax request to search for user
    xhr.open('POST', target, true);
    xhr.setRequestHeader('X-Requested-With', 'xhrRequest');
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.send(params);
  } else {
    clearPaymentAreas();
  }
}

let userSearch = document.getElementById('user-first-name');
userSearch.addEventListener('input', (event) => {
  var req = new XMLHttpRequest();
  req.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      let json = JSON.parse(this.responseText);
      let select = document.getElementById('user-select');
      select.innerHTML = json.html;
      select.disabled = json.disabled;
    } else if (this.readyState == 4) {
      // Not ok
      alert('An error occurred.');
    }
  }
  req.open('POST', userSearch.dataset.ajaxUrl, true);
  req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  req.send('search=' + encodeURI(userSearch.value));
});

let userSelect = document.getElementById('user-select');
userSelect.addEventListener('change', getUser);
// getUser();

var form = document.getElementById('payment-form');
form.addEventListener('submit', function(event) {
  // Cancel form submission
  event.preventDefault();

  if (document.getElementById('payment-form').checkValidity()) {
    document.getElementById('error-notice').classList.add('d-none');

    var type = this.elements['type'];
    if (type.value === 'Payment') {
      document.getElementById('confirmation-modal-pay-credit').textContent = 'charge';
    } else {
      document.getElementById('confirmation-modal-pay-credit').textContent = 'credit/refund';
    }

    var amountString = (new BigNumber(document.getElementById('amount').value)).decimalPlaces(2).toFormat(2);
    document.getElementById('confirmation-modal-amount').textContent = amountString;

    document.getElementById('confirmation-modal-name').textContent = document.getElementById('user-info-name').textContent;

    document.getElementById('confirmation-modal-user-info-name').textContent = document.getElementById('user-info-name').textContent;

    var email = document.getElementById('confirmation-modal-user-info-email');
    var emailData = document.getElementById('user-info-email');
    email.textContent = emailData.textContent;
    email.href = emailData.href;

    var phone = document.getElementById('confirmation-modal-user-info-phone');
    var phoneData = document.getElementById('user-info-phone');
    phone.textContent = phoneData.textContent;
    phone.href = phoneData.href;

    document.getElementById('confirmation-modal-user-info-address').textContent = document.getElementById('user-info-address').textContent;

    document.getElementById('confirm').addEventListener('click', function(event) {
      document.getElementById('payment-form').submit();
    });

    // Show alert modal
    let modal = new bootstrap.Modal(document.getElementById('confirmation-modal'));
    modal.show();
  } else {
    document.getElementById('error-notice').classList.remove('d-none');
  }
});

var radios = document.getElementById('radios');

if (radios !== null) {
  var options = radios.getElementsByTagName('input');
  for (var i = 0, len = options.length; i < len; i++) {
    if (options[i].type === 'radio') {
      options[i].addEventListener('change', function(event) {
        var type = document.getElementById('payment-form').elements['type'];
        if (type.value === 'Payment') {
          document.getElementById('amount-type').textContent = 'charge';
          document.getElementById('payment-submit-button').textContent = 'Add charge';
        } else {
          document.getElementById('amount-type').textContent = 'credit/refund';
          document.getElementById('payment-submit-button').textContent = 'Add credit/refund';
        }
      });
    }
  }
}