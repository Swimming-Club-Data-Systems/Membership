let login = document.getElementById('login-form');
const loginFormHtml = login.innerHTML;
const csrf = document.getElementById('SCDS-GLOBAL-CSRF').value;

login.addEventListener('submit', handleLogin);

function handleLogin(event) {
  event.preventDefault();

  let emailInput = document.getElementById('email-address');
  let pwInput = document.getElementById('password');

  // Try signin
  let email = emailInput.value;
  let pw = pwInput.value;
  let target = null;
  let org = null;
  let urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('target')) {
    target = urlParams.get('target')
  }
  if (urlParams.has('club')) {
    org = urlParams.get('club')
  }

  let data = {
    email: email,
    password: pw,
    csrfToken: csrf,
    target: target,
    organisation: org,
    staySignedIn: false
  }

  // XHR
  let xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      console.log(this.responseText);
      var json = JSON.parse(this.responseText);
      if (json.next === 'pass') {
        // Success, go forth
        console.log(json.pass);
        orgString = "";
        targetString = "";
        if (org) {
          orgString = org.toString()
        }
        if (target) {
          targetString = target.toString()
        }
        console.log(json.pass.redirect + '?club=' + encodeURIComponent(orgString) + '&target=' + encodeURIComponent(targetString) + '&remember=true');
        window.location.href = json.pass.redirect + '?club=' + encodeURIComponent(orgString) + '&target=' + encodeURIComponent(targetString) + '&remember=true';
      } else if (json.next === 'twoFactor') {
        // Show Two Factor Auth
        let text = 'Enter your two factor authentication code';
        if (json.twoFactor.type == 'ga') {
          text += ' from your authenticator app.';
        } else {
          text += ' we have just sent by email.';
        }
        document.getElementById('login-box-text').textContent = text;
        let html = '<div class="form-group"><label for="code">Authentication code</label><input type="number" class="form-control form-control-lg text-lowercase" id="code" name="code" placeholder="654321" required autofocus min="0" max="999999"></div>';
        html += '<p><button type="submit" class="btn btn-primary btn-lg" id="confirm-2fa" disabled>Confirm</button></p>';
        html += '<p class="mb-0"><button type="button" class="btn btn-light" id="resend">Sign in</button> <button type="button" class="btn btn-light" id="cancel">Cancel</button></p>';
        login.innerHTML = html;
        let resend = document.getElementById('resend');
        if (json.twoFactor.type == 'ga') {
          resend.textContent = 'Send by email';
        } else {
          resend.textContent = 'Resend email';
        }
        resend.addEventListener('click', () => {
          let xhttp = new XMLHttpRequest();
          xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {

            }
          }
          xhttp.open('POST', document.getElementById('login-form').dataset.ajaxTwoFactorUrl, true);
          xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
          xhttp.send(JSON.stringify({action: 'resend'}));
        });
        document.getElementById('code').addEventListener('input', canSubmit2FA);
        document.getElementById('login-form').removeEventListener('submit', handleLogin)
        document.getElementById('login-form').addEventListener('submit', verify2FA);
        console.log(json.twoFactor);
      } else if (json.next === 'incorrect') {
        // Handle invalid pw
        console.log(json.incorrect);
      } else if (json.next === 'notSent') {
        // Handle not sent
        console.log(json.notSent);
      } else if (json.next === 'error') {
        // Handle error
        console.log(json.error);
      }
    }
  }
  xhttp.open('POST', document.getElementById('login-form').dataset.ajaxLoginUrl, true);
  xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhttp.send(JSON.stringify(data));

}

function verify2FA(event) {
  event.preventDefault();

  let code = document.getElementById('code');

  // Verify
  let codeValue = code.value;
  let target = null;
  let org = null;
  let urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('target')) {
    target = urlParams.get('target')
  }
  if (urlParams.has('club')) {
    org = urlParams.get('club')
  }

  let data = {
    action: 'verify',
    code: codeValue,
    csrfToken: csrf,
    target: target,
    organisation: org,
    staySignedIn: false
  }

  // XHR
  let xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      console.log(this.responseText);
      var json = JSON.parse(this.responseText);
      if (json.next === 'pass') {
        // Success, go forth
        console.log(json.pass);
        orgString = "";
        targetString = "";
        if (org) {
          orgString = org.toString()
        }
        if (target) {
          targetString = target.toString()
        }
        console.log(json.pass.redirect + '?club=' + encodeURIComponent(orgString) + '&target=' + encodeURIComponent(targetString) + '&remember=true');
        window.location.href = json.pass.redirect + '?club=' + encodeURIComponent(orgString) + '&target=' + encodeURIComponent(targetString) + '&remember=true';
      } else if (json.next === 'csrf') {
        // Handle invalid pw
        console.log(json.csrf);
      } else if (json.next === 'incorrect') {
        // Handle invalid pw
        console.log(json.incorrect);
      } else if (json.next === 'notSent') {
        // Handle not sent
        console.log(json.notSent);
      } else if (json.next === 'error') {
        // Handle error
        console.log(json.error);
      }
    }
  }
  xhttp.open('POST', document.getElementById('login-form').dataset.ajaxTwoFactorUrl, true);
  xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhttp.send(JSON.stringify(data));

}

function canSubmit(event) {
  let user = document.getElementById('email-address');
  let pw = document.getElementById('password');
  let btn = document.getElementById('submit');

  if (user.checkValidity() && pw.checkValidity()) {
    btn.disabled = false;
  } else {
    btn.disabled = true;
  }
}

function canSubmit2FA(event) {
  let box = document.getElementById('code');
  let btn = document.getElementById('confirm-2fa');

  if (box.checkValidity()) {
    btn.disabled = false;
  } else {
    btn.disabled = true;
  }
}

let user = document.getElementById('email-address');
let pw = document.getElementById('password');

user.addEventListener('input', canSubmit);
pw.addEventListener('input', canSubmit);