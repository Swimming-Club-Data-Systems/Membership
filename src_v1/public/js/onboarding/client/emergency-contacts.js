/**
 * JS for editing Emergency Contacts in the new onboarding flow
 * 
 */

let box = document.getElementById('contact-box');
let data = document.getElementById('ajax-data').dataset;
let modal = new bootstrap.Modal(document.getElementById('main-modal'));

function loadContacts() {

  let request = new XMLHttpRequest();

  let failed = `
  <div class="alert alert-warning">
    <p class="mb-0">
      <strong>Can't load contacts</strong>
    </p>
    <p class="mb-0">
      We were unable to load your emergency contacts. Please try again.
    </p>
  </div>
  `

  request.addEventListener('load', function () {
    if (this.status == 200) {
      let out = JSON.parse(this.responseText);
      box.innerHTML = out.html;

      let contForm = document.getElementById('continue-form');
      if (out.count < 1) {
        contForm.classList.add('d-none');
      } else {
        contForm.classList.remove('d-none');
      }
    } else {
      box.innerHTML = failed;
    }
  });

  request.open("GET", data.contactsListUrl);
  request.send();
}

function handleNew(ev) {

  ev.preventDefault();
  ev.stopPropagation();
  console.log(ev);

  if (ev.target.checkValidity() === false) {
    // Only add .was-validated here - otherwise gives flash of green
    ev.target.classList.add('was-validated');
  } else {
    // Try form submission
    let request = new XMLHttpRequest();

    request.addEventListener('load', function () {
      if (this.status == 200) {
        let out = JSON.parse(this.responseText);
        if (out.success) {
          loadContacts();
          modal.hide();
        } else {
          document.getElementById('error-box').classList.remove('d-none');
        }
      } else {
        alert('An unexpected failure occurred');
      }
    });

    request.open("POST", data.newUrl);
    let formData = new FormData(ev.target);
    request.send(formData);
  }

}

function addNewLaunch(ev) {
  // Set modal title
  document.getElementById('main-modal-title').textContent = 'Add new emergency contact';

  // Compose modal body content
  var bodyContent = `
  <form method="post" class="needs-validation" novalidate id="add-new">
    <div class="alert alert-danger d-none" id="error-box">
      <p class="mb-0"><strong>There was a problem with the data you submitted</strong></p>
    </div>

    <div class="mb-3">
      <label class="form-label" for="name">Name</label>
      <input type="text" class="form-control" id="name" name="name" placeholder="Name" required>
      <div class="invalid-feedback">
        You must provide the name of the emergency contact
      </div>
    </div>


    <div class="mb-3">
      <label class="form-label" for="relation">Relationship to you/members</label>
      <input type="text" class="form-control" id="relation" name="relation" placeholder="Relation" required>
      <div class="invalid-feedback">
        You must provide the relation so we can decide who is best to call
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label" for="num">Contact Number</label>
      <input type="tel" class="form-control" id="num" name="num" placeholder="Phone" required>
      <div class="invalid-feedback">
        You must provide a valid phone number
      </div>

    </div>
  </form>
  `
  document.getElementById('main-modal-body').innerHTML = bodyContent;

  // Set buttons
  document.getElementById('main-modal-footer').innerHTML = '<button type="button" class="btn btn-dark" data-bs-dismiss="modal">Cancel</button><button type="submit" id="modal-confirm-button" form="add-new" class="btn btn-success">Add contact</button>';

  // Setup confirm event listeners
  document.getElementById('add-new').addEventListener('submit', handleNew);

  modal.show();
}

function handleEdit(ev) {
  ev.preventDefault();
  ev.stopPropagation();
  console.log(ev);

  if (ev.target.checkValidity() === false) {
    // Only add .was-validated here - otherwise gives flash of green
    ev.target.classList.add('was-validated');
  } else {
    // Try form submission
    let request = new XMLHttpRequest();

    request.addEventListener('load', function () {
      if (this.status == 200) {
        let out = JSON.parse(this.responseText);
        if (out.success) {
          loadContacts();
          modal.hide();
        } else {
          document.getElementById('error-box').classList.remove('d-none');
        }
      } else {
        alert('An unexpected failure occurred');
      }
    });

    request.open("POST", data.editUrl);
    let formData = new FormData(ev.target);
    request.send(formData);
  }
}

function editLaunch(ev) {
  // Set modal title
  document.getElementById('main-modal-title').textContent = 'Edit emergency contact';

  // Compose modal body content
  var bodyContent = `
  <form method="post" class="needs-validation" novalidate id="edit-form">

    <div class="alert alert-danger d-none" id="error-box">
      <p class="mb-0"><strong>There was a problem with the data you submitted</strong></p>
    </div>

    <input type="hidden" value="" id="contact-id" name="contact-id">

    <div class="mb-3">
      <label class="form-label" for="name">Name</label>
      <input type="text" class="form-control" id="name" name="name" placeholder="Name" required>
      <div class="invalid-feedback">
        You must provide the name of the emergency contact
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label" for="relation">Relationship to you/members</label>
      <input type="text" class="form-control" id="relation" name="relation" placeholder="Relation" required>
      <div class="invalid-feedback">
        You must provide the relation so we can decide who is best to call
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label" for="num">Contact Number</label>
      <input type="tel" class="form-control" id="num" name="num" placeholder="Phone" required>
      <div class="invalid-feedback">
        You must provide a valid UK phone number
      </div>

    </div>
  </form>
  `
  document.getElementById('main-modal-body').innerHTML = bodyContent;

  // Set buttons
  document.getElementById('main-modal-footer').innerHTML = '<button type="button" class="btn btn-dark" data-bs-dismiss="modal">Cancel</button><button type="submit" id="modal-confirm-button" class="btn btn-success" form="edit-form">Save changes</button>';

  document.getElementById('contact-id').value = ev.target.dataset.id;
  document.getElementById('name').value = ev.target.dataset.name;
  document.getElementById('relation').value = ev.target.dataset.relation;
  document.getElementById('num').value = ev.target.dataset.number;

  // Setup confirm event listeners
  document.getElementById('edit-form').addEventListener('submit', handleEdit);

  modal.show();
}

function handleDelete(ev) {
  ev.preventDefault();
  ev.stopPropagation();
  console.log(ev);

  if (ev.target.checkValidity() === false) {
    // Only add .was-validated here - otherwise gives flash of green
    ev.target.classList.add('was-validated');
  } else {
    // Try form submission
    let request = new XMLHttpRequest();

    request.addEventListener('load', function () {
      if (this.status == 200) {
        console.log(this.responseText);
        let out = JSON.parse(this.responseText);
        if (out.success) {
          loadContacts();
          modal.hide();
        } else {
          document.getElementById('error-box').classList.remove('d-none');
        }
      } else {
        alert('An unexpected failure occurred');
      }
    });

    request.open("POST", data.deleteUrl);
    let formData = new FormData();
    formData.append('id', ev.target.dataset.id);
    request.send(formData);
  }
}

function deleteLaunch(ev) {
  // Set modal title
  document.getElementById('main-modal-title').textContent = 'Delete emergency contact';

  // Set buttons
  document.getElementById('main-modal-footer').innerHTML = '<button type="button" class="btn btn-dark" data-bs-dismiss="modal">No</button><button type="button" id="modal-confirm-button" class="btn btn-success" data-id="">Yes</button>';

  // Compose modal body content
  var bodyContent = `
  <div class="alert alert-danger d-none" id="error-box">
    <p class="mb-0"><strong>There was a problem with the data you submitted</strong></p>
  </div>
  <p class="mb-0">Are you sure you want to delete <span id="name"></span> (<span id="num"></span>) from your list of emergency contacts?</p>
  `
  document.getElementById('main-modal-body').innerHTML = bodyContent;

  document.getElementById('name').textContent = ev.target.dataset.name;
  document.getElementById('num').textContent = ev.target.dataset.number;

  // Setup confirm event listeners
  document.getElementById('modal-confirm-button').dataset.id = ev.target.dataset.id;
  document.getElementById('modal-confirm-button').addEventListener('click', handleDelete);

  modal.show();
}

box.addEventListener('click', ev => {
  if (ev.target) {
    console.log(ev.target);
    if (ev.target.dataset.action) {
      if (ev.target.dataset.action == 'edit') {
        // Trigger edit
        editLaunch(ev);
      } else if (ev.target.dataset.action == 'delete') {
        // Trigger delete
        deleteLaunch(ev);
      }
    }
  }
});

let button = document.getElementById('add-new-button');
button.addEventListener('click', addNewLaunch);

loadContacts();