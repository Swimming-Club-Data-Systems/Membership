/**
 * New onboarding session JS
 */

const data = document.getElementById('data').dataset;
let form = document.getElementById('form');

console.log(form);

form.addEventListener('submit', ev => {
  console.log(ev);

  let hasUser = JSON.parse(data.hasUser);
  let expanded = JSON.parse(data.expanded);

  let email = document.getElementById('user-email');

  if ((hasUser || expanded) && form.checkValidity() === false) {
    ev.preventDefault();
    ev.stopPropagation();

    // Only add .was-validated here - otherwise gives flash of green
    form.classList.add('was-validated');
  }

  if (!hasUser && !expanded) {

    ev.preventDefault();
    ev.stopPropagation();

    if (email.checkValidity() === false) {
      form.classList.add('was-validated');
    } else {

      let request = new XMLHttpRequest();

      request.addEventListener('load', function () {
        if (this.status == 200) {
          let out = JSON.parse(this.responseText);
          if (out.userExists) {
            // Redirect
            // data.hasUser = 'true';
            window.location.href = out.redirect;
          } else {
            // Reveal items
            let userInfo = new bootstrap.Collapse(document.getElementById('user-info'));
            let selectMembers = new bootstrap.Collapse(document.getElementById('select-members'));
            let button = new bootstrap.Collapse(document.getElementById('lookup-button'));
            userInfo.show();
            selectMembers.show();
            button.hide();
            data.expanded = 'true';
          }
        }
      });

      let formData = new FormData();
      formData.append('email', email.value);

      request.open("POST", data.checkUserUrl);
      request.send(formData);
    }
  }
});