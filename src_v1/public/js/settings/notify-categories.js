/**
 * JS for Notify Categories
 */

const data = document.getElementById('js-data').dataset;
const modal = new bootstrap.Modal(document.getElementById('main-modal'));

function loadList() {
  let request = new XMLHttpRequest();
  let box = document.getElementById('category-section');

  let failed = `
  <div class="alert alert-warning">
    <p class="mb-0">
      <strong>Can't load notify categories</strong>
    </p>
    <p class="mb-0">
      Please try again later.
    </p>
  </div>
  `

  request.addEventListener('load', function () {
    if (this.status == 200) {
      let out = JSON.parse(this.responseText);
      box.innerHTML = out.listHtml;

    } else {
      box.innerHTML = failed;
    }
  });

  let formData = new FormData();

  request.open("POST", data.listAjaxUrl);
  request.send(formData);
}

function newSubmission(ev) {
  ev.preventDefault();
  ev.stopPropagation();

  let request = new XMLHttpRequest();

  request.addEventListener('load', function () {
    if (this.status == 200) {
      let out = JSON.parse(this.responseText);

      if (out.success) {
        modal.hide();
        loadList();
      } else {
        alert('Could not add category');
      }

    } else {
      alert('Could not add category');
    }
  });

  let form = ev.target;
  if (form.checkValidity() === false) {

    // Only add .was-validated here - otherwise gives flash of green
    form.classList.add('was-validated');
  } else {
    let formData = new FormData(form);

    request.open("POST", data.addAjaxUrl);
    request.send(formData);
  }
}

loadList();

document.getElementById('new-button').addEventListener('click', ev => {
  // Display modal
  document.getElementById('main-modal-title').textContent = 'New Category';
  let form = `
  <form class="needs-validation" novalidate id="new-cat-form">
    <p>
      <strong>Note: </strong> By default, no users will receive messages under this category until they opt-in, as per the General Data Protection Regulation and the UK Data Protection Act.
    </p>
    <div class="mb-3">
      <label for="name" class="form-label">Category name</label>
      <input type="text" class="form-control" id="name" name="name" required>
      <div class="invalid-feedback">You must provide a category name</div>
    </div>
    <div class="">
      <label for="description" class="form-label">Category description</label>
      <textarea rows="3" class="form-control" id="description" name="description" required></textarea>
      <div class="invalid-feedback">You must provide a textual description of this category</div>
    </div>
  </form>
  `
  document.getElementById('main-modal-body').innerHTML = form;
  let footer = `
  <button type="button" class="btn btn-dark-l btn-outline-light-d" data-bs-dismiss="modal">Cancel</button>
  <button type="submit" form="new-cat-form" class="btn btn-success">Add category</button>
  `
  document.getElementById('main-modal-footer').innerHTML = footer;

  document.getElementById('new-cat-form').addEventListener('submit', newSubmission);

  modal.show();
});

function handleUpdate(ev) {
  ev.preventDefault();
  ev.stopPropagation();

  let request = new XMLHttpRequest();

  request.addEventListener('load', function () {
    if (this.status == 200) {
      let out = JSON.parse(this.responseText);

      if (out.success) {
        modal.hide();
        loadList();
      } else {
        alert('Could not add category');
      }

    } else {
      alert('Could not add category');
    }
  });

  let form = ev.target;
  if (form.checkValidity() === false) {

    // Only add .was-validated here - otherwise gives flash of green
    form.classList.add('was-validated');
  } else {
    let formData = new FormData(form);

    request.open("POST", data.updateAjaxUrl);
    request.send(formData);
  }
}

function handleDelete(ev) {

  if (ev.target.dataset.action && ev.target.dataset.action == 'delete') {
    let request = new XMLHttpRequest();

    request.addEventListener('load', function () {
      if (this.status == 200) {
        let out = JSON.parse(this.responseText);

        if (out.success) {
          loadList();
        } else {
          alert('Could not delete category');
        }

      } else {
        alert('Could not delete category');
      }
    });

    let form = ev.target;
    if (form.checkValidity() === false) {

      // Only add .was-validated here - otherwise gives flash of green
      form.classList.add('was-validated');
    } else {
      let formData = new FormData();

      formData.append('category', ev.target.dataset.categoryId);

      request.open("POST", data.deleteAjaxUrl);
      request.send(formData);
    }
  }
}

document.getElementById('category-section').addEventListener('submit', handleUpdate);

document.getElementById('category-section').addEventListener('click', handleDelete);