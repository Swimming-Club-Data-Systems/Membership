/**
 * Edit BATCH JS Code
 */

const data = document.getElementById('js-data').dataset;
const modal = new bootstrap.Modal(document.getElementById('main-modal'));

function loadList() {
  let request = new XMLHttpRequest();
  let box = document.getElementById('batch-items-section');

  let failed = `
  <div class="alert alert-warning">
    <p class="mb-0">
      <strong>Can't load batch items</strong>
    </p>
    <p class="mb-0">
      We were unable to load your batch items. Please try again.
    </p>
  </div>
  `

  request.addEventListener('load', function () {
    if (this.status == 200) {
      let out = JSON.parse(this.responseText);
      box.innerHTML = out.listHtml;
      document.getElementById('formatted-total').textContent = out.formattedTotal;

    } else {
      box.innerHTML = failed;
    }
  });

  let formData = new FormData();
  formData.append('id', data.batchId);

  request.open("POST", data.listAjaxUrl);
  request.send(formData);
}

function getPossibleMemberships(ev) {
  // Get membership list
  let request = new XMLHttpRequest();
  let box = document.getElementById('add-membership-form-details');

  let failed = `
    <div class="alert alert-warning">
      <p class="mb-0">
        <strong>Can't load batch items</strong>
      </p>
      <p class="mb-0">
        We were unable to load your batch items. Please try again.
      </p>
    </div>
    `

  document.getElementById('modal-confirm-button').disabled = true;

  let member = document.getElementById('member');
  let year = document.getElementById('membership-year');

  // Check values for member and membership-year
  if (member.value != 'null' && year.value != 'null') {
    request.addEventListener('load', function () {
      if (this.status == 200) {
        let out = JSON.parse(this.responseText);
        box.innerHTML = out.html;

        let select = document.getElementById('membership');
        select.addEventListener('change', ev => {
          if (ev.target.value != 'null') {
            document.getElementById('membership-amount').value = ev.target.options[ev.target.selectedIndex].dataset.fee;

            if (ev.target.options[ev.target.selectedIndex].dataset.discountMessage) {
              document.getElementById('membership-info-box').innerHTML = '<div class="alert mt-3 mb-0 alert-info">' + ev.target.options[ev.target.selectedIndex].dataset.discountMessage + '</div>';
            } else {
              document.getElementById('membership-info-box').innerHTML = '';
            }

            // Show details
            let collapse = new bootstrap.Collapse(document.getElementById('add-membership-form-details-opts'), { toggle: false });
            collapse.show();

            // Enable submission
            document.getElementById('modal-confirm-button').disabled = false;
          }
        })

      } else {
        box.innerHTML = failed;
      }
    });

    let formData = new FormData();
    formData.append('id', data.batchId);
    formData.append('member', member.value);
    formData.append('membership-year', year.value);

    request.open("POST", data.selectAjaxUrl);
    request.send(formData);
  } else {

  }
}

function handleNew(ev) {
  ev.preventDefault();

  let request = new XMLHttpRequest();
  let box = document.getElementById('main-modal-body');

  let failed = `
  <div class="alert alert-warning">
    <p class="mb-0">
      <strong>Can't add batch item</strong>
    </p>
    <p class="mb-0">
      Please try again.
    </p>
  </div>
  `

  request.addEventListener('load', function () {
    if (this.status == 200) {
      let out = JSON.parse(this.responseText);
      if (out.success) {
        modal.hide();
        loadList();
      }

    } else {
      box.innerHTML = failed;
    }
  });

  let formData = new FormData(ev.target);
  formData.append('id', data.batchId);

  request.open("POST", data.addItemAjaxUrl);
  request.send(formData);
}

loadList();

let addButton = document.getElementById('add-membership-button');
addButton.addEventListener('click', ev => {
  // Get member list
  let request = new XMLHttpRequest();
  let box = document.getElementById('main-modal-body');

  let failed = `
  <div class="alert alert-warning">
    <p class="mb-0">
      <strong>Can't load batch items</strong>
    </p>
    <p class="mb-0">
      We were unable to load your batch items. Please try again.
    </p>
  </div>
  `

  request.addEventListener('load', function () {
    if (this.status == 200) {
      let out = JSON.parse(this.responseText);
      box.innerHTML = out.html;

      document.getElementById('add-membership-form').addEventListener('submit', handleNew);

      // Listen to select box and load memberships
      document.getElementById('member').addEventListener('change', getPossibleMemberships);
      document.getElementById('membership-year').addEventListener('change', getPossibleMemberships);

    } else {
      box.innerHTML = failed;
    }
  });

  let formData = new FormData();
  formData.append('id', data.batchId);

  request.open("POST", data.addAjaxUrl);
  request.send(formData);

  // Show modal
  // Set modal title
  document.getElementById('main-modal-title').textContent = 'Add another membership';

  // Set buttons
  document.getElementById('main-modal-footer').innerHTML = '<button type="button" class="btn btn-dark" data-bs-dismiss="modal">Cancel</button><button type="submit" form="add-membership-form" id="modal-confirm-button" class="btn btn-success" disabled>Add Membership</button>';

  modal.show();
});

function handleItemClick(ev) {
  if (ev.target.dataset.action && ev.target.dataset.action == 'delete') {
    // Delete this batch item
    let request = new XMLHttpRequest();

    request.addEventListener('load', function () {
      if (this.status == 200) {
        let out = JSON.parse(this.responseText);

        if (out.success) {
          loadList();
        } else {
          alert('Could not delete');
        }

      } else {
        alert('Could not delete');
      }
    });

    let formData = new FormData();
    formData.append('id', data.batchId);
    formData.append('item-id', ev.target.dataset.id);

    request.open("POST", data.deleteItemAjaxUrl);
    request.send(formData);
  }
}

function handleOptionsForm(ev) {
  // Update batch options
  ev.preventDefault();

  let request = new XMLHttpRequest();

  request.addEventListener('load', function () {
    if (this.status == 200) {
      let out = JSON.parse(this.responseText);

      if (out.success) {
        alert('Saved');
      } else {
        alert('Could not save');
      }

    } else {
      alert('Could not save (HTTP)');
    }
  });

  let formData = new FormData(ev.target);
  formData.append('id', data.batchId);

  request.open("POST", data.optionsUpdateAjaxUrl);
  request.send(formData);
}

function handleItemSubmission(ev) {
  ev.preventDefault();

  // Update this batch item
  let request = new XMLHttpRequest();

  request.addEventListener('load', function () {
    if (this.status == 200) {
      let out = JSON.parse(this.responseText);

      if (out.success) {
        loadList();
      } else {
        alert('Could not update');
      }

    } else {
      alert('Could not update');
    }
  });

  let formData = new FormData(ev.target);
  formData.append('id', data.batchId);

  request.open("POST", data.updateItemAjaxUrl);
  request.send(formData);
}

let itemsSection = document.getElementById('batch-items-section');
itemsSection.addEventListener('submit', handleItemSubmission);
itemsSection.addEventListener('click', handleItemClick);

document.getElementById('options-form').addEventListener('submit', handleOptionsForm);