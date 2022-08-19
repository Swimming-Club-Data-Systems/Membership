const options = document.getElementById('js-opts').dataset;

let modalHeaderContainer = document.getElementById('edit-delete-modal-header');
let modalHeader = document.getElementById('edit-delete-modal-title');
let modalBody = document.getElementById('edit-delete-modal-body');
let modalFooter = document.getElementById('edit-delete-modal-footer');
let modal = document.getElementById('edit-delete-modal');
let modalObject = new bootstrap.Modal(document.getElementById('edit-delete-modal'));

function loadViewError() {
  document.getElementById('view-window').innerHTML = `<div class="alert alert-danger"><p class="mb-0"><strong>An error has occurred</strong></p><p class="mb-0">We're unable to load the list of emergency contacts</div>`;
}

function loadView() {
  var req = new XMLHttpRequest();
  req.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      let json = JSON.parse(this.responseText);
      if (json.status == 200) {
        document.getElementById('view-window').innerHTML = json.html;
        loadListeners();
      } else {
        loadViewError();
      }
    } else if (this.readyState == 4) {
      // Not ok
      loadViewError();
    }
  }
  req.open('POST', options.viewUrl, true);
  req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  req.send();
}

function setForm(body) {

  let formGroup, label, input, form, invalid;

  form = document.createElement('FORM');
  form.classList.add('needs-validation');
  form.setAttribute('novalidate', '');
  form.id = 'contact-form';


  // Name
  formGroup = document.createElement('DIV');
  formGroup.classList.add('form-group');

  label = document.createElement('LABEL');
  label.textContent = 'Name';

  input = document.createElement('INPUT');
  input.classList.add('form-control');
  input.type = 'text';
  input.required = true;
  input.placeholder = 'Name';
  input.id = input.name = 'contact-name';

  invalid = document.createElement('DIV');
  invalid.classList.add('invalid-feedback')
  invalid.textContent = 'You must provide the name of the emergency contact';

  formGroup.appendChild(label);
  formGroup.appendChild(input);
  formGroup.appendChild(invalid);
  form.appendChild(formGroup);

  // Relation
  formGroup = document.createElement('DIV');
  formGroup.classList.add('form-group');

  label = document.createElement('LABEL');
  label.textContent = 'Relation';

  input = document.createElement('INPUT');
  input.classList.add('form-control');
  input.type = 'text';
  input.required = true;
  input.placeholder = 'Relation';
  input.id = input.name = 'contact-relation';

  invalid = document.createElement('DIV');
  invalid.classList.add('invalid-feedback')
  invalid.textContent = 'You must provide the relation so we can decide who is best to call';

  formGroup.appendChild(label);
  formGroup.appendChild(input);
  formGroup.appendChild(invalid);
  form.appendChild(formGroup);

  // Number
  formGroup = document.createElement('DIV');
  formGroup.classList.add('form-group');

  label = document.createElement('LABEL');
  label.textContent = 'Contact Number';

  input = document.createElement('INPUT');
  input.classList.add('form-control');
  input.type = 'tel';
  input.required = true;
  input.placeholder = 'Phone';
  input.id = input.name = 'contact-number';
  input.pattern = '\\+{0,1}[0-9]*';

  invalid = document.createElement('DIV');
  invalid.classList.add('invalid-feedback')
  invalid.textContent = 'You must provide a valid UK phone number';

  formGroup.appendChild(label);
  formGroup.appendChild(input);
  formGroup.appendChild(invalid);
  form.appendChild(formGroup);

  body.appendChild(form);

  form.addEventListener('submit', event => {
    event.preventDefault();
    if (form.checkValidity() === false) {
      // Only add .was-validated here - otherwise gives flash of green
      form.classList.add('was-validated');
      event.stopPropagation();
    }
  });

}

function loadListeners() {
  document.getElementById('contacts-list').addEventListener('click', event => {
    let contactId;

    if (event.target.dataset.type && event.target.dataset.type == 'edit-button') {
      // Handle edit
      contactId = event.target.dataset.contactId;

      // Unset danger header
      modalHeaderContainer.classList.remove('bg-danger', 'text-white');
      modalHeader.textContent = 'Edit an emergency contact';

      modalBody.innerHTML = '';

      setForm(modalBody);

      document.getElementById('contact-name').dataset.contactId = contactId;
      document.getElementById('contact-name').value = event.target.dataset.contactName;
      document.getElementById('contact-relation').value = event.target.dataset.contactRelation;
      document.getElementById('contact-number').value = event.target.dataset.contactNumber;

      // Set footer
      // <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Cancel</button>
      let dismissButton = document.createElement('BUTTON');
      dismissButton.classList.add('btn', 'btn-dark');
      dismissButton.dataset.dismiss = 'modal';
      dismissButton.type = 'button';
      dismissButton.textContent = 'Cancel';

      let saveButton = document.createElement('BUTTON');
      saveButton.classList.add('btn', 'btn-success');
      saveButton.type = 'submit';
      saveButton.setAttribute('form', 'contact-form');
      saveButton.textContent = 'Save';

      modalFooter.innerHTML = '';
      modalFooter.appendChild(dismissButton);
      modalFooter.appendChild(saveButton);

      modal.removeEventListener('submit', newContactSubmitHandler);
      modal.addEventListener('submit', editContactSubmitHandler);

      modalObject.show();
    }
    if (event.target.dataset.type && event.target.dataset.type == 'delete-button') {
      // Handle delete
      contactId = event.target.dataset.contactId;

      // Set danger header
      modalHeaderContainer.classList.add('bg-danger', 'text-white');
      modalHeader.textContent = 'Are you sure?';

      modalBody.innerHTML = '';

      let p = document.createElement('P');
      p.classList.add('text-danger', 'mb-0');
      p.textContent = 'Are you sure you want to delete ' + event.target.dataset.contactName + ' from your emergency contacts?';
      modalBody.appendChild(p);

      // Set footer
      // <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Cancel</button>
      let dismissButton = document.createElement('BUTTON');
      dismissButton.classList.add('btn', 'btn-dark');
      dismissButton.dataset.dismiss = 'modal';
      dismissButton.type = 'button';
      dismissButton.textContent = 'Cancel';

      let deleteButton = document.createElement('BUTTON');
      deleteButton.classList.add('btn', 'btn-danger');
      deleteButton.type = 'submit';
      deleteButton.textContent = 'Delete';
      deleteButton.dataset.contactId = contactId;

      modalFooter.innerHTML = '';
      modalFooter.appendChild(dismissButton);
      modalFooter.appendChild(deleteButton);

      modal.addEventListener('submit', event => {
        // Do nothing
      });

      deleteButton.addEventListener('click', event => {
        console.log(event);
        var req = new XMLHttpRequest();
        req.onreadystatechange = function () {
          if (this.readyState == 4 && this.status == 200) {
            let json = JSON.parse(this.responseText);
            if (json.status == 200) {
              // location.reload();
              loadView();
              let modal = new bootstrap.Modal(document.getElementById('edit-delete-modal'));
              modalObject.hide();
            } else {
              alert(json.error);
            }
          } else if (this.readyState == 4) {
            // Not ok
            alert('An error occurred. Your action was not saved.');
          }
        }
        req.open('POST', options.deleteUrl, true);
        req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        req.send('contact-id=' + encodeURI(event.target.dataset.contactId));
      });

      modalObject.show();
    }
  });

  document.getElementById('new-button').addEventListener('click', event => {
    // Unset danger header
    modalHeaderContainer.classList.remove('bg-danger', 'text-white');
    modalHeader.textContent = 'Add a new emergency contact';

    modalBody.innerHTML = '';

    setForm(modalBody);

    // Set footer
    // <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Cancel</button>
    let dismissButton = document.createElement('BUTTON');
    dismissButton.classList.add('btn', 'btn-dark');
    dismissButton.dataset.dismiss = 'modal';
    dismissButton.type = 'button';
    dismissButton.textContent = 'Cancel';

    let saveButton = document.createElement('BUTTON');
    saveButton.classList.add('btn', 'btn-success');
    saveButton.type = 'submit';
    saveButton.setAttribute('form', 'contact-form');
    saveButton.textContent = 'Add';

    modalFooter.innerHTML = '';
    modalFooter.appendChild(dismissButton);
    modalFooter.appendChild(saveButton);

    modal.removeEventListener('submit', editContactSubmitHandler);
    modal.addEventListener('submit', newContactSubmitHandler);

    let modal = new bootstrap.Modal(document.getElementById('edit-delete-modal'));
    modal.show();
  });
}
loadListeners();

function newContactSubmitHandler(event) {
  event.preventDefault();
  event.stopPropagation();

  let form = event.target;
  let formData = new FormData(form);

  var req = new XMLHttpRequest();
  req.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      let json = JSON.parse(this.responseText);
      if (json.status == 200) {
        // location.reload();
        loadView();
        let modal = new bootstrap.Modal(document.getElementById('edit-delete-modal'));
        modalObject.hide();
      } else {
        alert(json.error);
      }
    } else if (this.readyState == 4) {
      // Not ok
      alert('An error occurred. Your action was not saved.');
    }
  }
  req.open('POST', options.addUrl, true);
  req.send(formData);
};

function editContactSubmitHandler(event) {
  event.preventDefault();
  event.stopPropagation();

  let form = event.target;
  let formData = new FormData(form);
  formData.append('contact-id', document.getElementById('contact-name').dataset.contactId);

  var req = new XMLHttpRequest();
  req.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      let json = JSON.parse(this.responseText);
      if (json.status == 200) {
        // location.reload();
        loadView();
        let modal = new bootstrap.Modal(document.getElementById('edit-delete-modal'));
        modalObject.hide();
      } else {
        alert(json.error);
      }
    } else if (this.readyState == 4) {
      // Not ok
      alert('An error occurred. Your action was not saved.');
    }
  }
  req.open('POST', options.editUrl, true);
  req.send(formData);
};