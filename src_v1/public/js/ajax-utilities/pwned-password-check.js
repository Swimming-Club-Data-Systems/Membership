const options = document.getElementById('ajax-options').dataset;

let pfr = document.getElementById('password-form-row');
let alertBox = document.getElementById('pwned-password-warning');

let passwordBox1 = document.getElementById('password-1');
let passwordBox2 = document.getElementById('password-2');

pfr.addEventListener('change', event => {
  let password1 = passwordBox1.value;
  let password2 = passwordBox2.value;

  pfr.classList.add('was-validated');

  let formData = new FormData();
  formData.append('csrf', options.crossSiteRequestForgeryValue);
  formData.append('password', password1);

  var getPwnedList = new XMLHttpRequest();
  getPwnedList.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      let json = JSON.parse(this.responseText);
      if (json.success && json.pwned) {
        // Alert the user
        alertBox.classList.remove('d-none');
      } else {
        alertBox.classList.add('d-none');
      }
    } else if (this.readyState == 4) {
      alertBox.classList.remove('d-none');
    }
  }
  getPwnedList.open('POST', options.getPwnedListAjaxUrl, true);
  getPwnedList.setRequestHeader('Accept', 'application/json');
  getPwnedList.send(formData);
});

pfr.addEventListener('input', event => {
  // Check passwords are equal
  let password1 = passwordBox1.value;
  let password2 = passwordBox2.value;

  if (password1.length > 0 && password2.length > 0) {
    // Check if passwords are the same or not
    if (password1 != password2) {
      // Tell user to check
      let errorString = 'Passwords do not match';
      passwordBox2.setCustomValidity(errorString);
    } else {
      passwordBox2.setCustomValidity('');
    }
  }

});