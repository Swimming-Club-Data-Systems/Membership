document.getElementById('fee-type').addEventListener('change', ev => {
  let type = ev.target.value;

  let perPerson = document.getElementById('per-person');
  let nSwimmers = document.getElementById('n-swimmers');

  let personFields = document.getElementsByClassName('person-fee-input');
  let nSwimmersFields = document.getElementsByClassName('nswimmers-fee-input');

  if (type == 'PerPerson') {
    perPerson.classList.remove('d-none');
    nSwimmers.classList.add('d-none');

    // Set required
    for (let field of personFields) {
      field.required = true;
    }
    // Unrequire
    for (let field of nSwimmersFields) {
      field.required = false;
    }
  } else if (type == 'NSwimmers') {
    perPerson.classList.add('d-none');
    nSwimmers.classList.remove('d-none');

    // Set required
    for (let field of personFields) {
      field.required = false;
    }
    // Unrequire
    for (let field of nSwimmersFields) {
      field.required = true;
    }
  }
});

function uuidv4() {
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
    var r = Math.random() * 16 | 0,
      v = c == 'x' ? r : (r & 0x3 | 0x8);
    return v.toString(16);
  });
}

function addField(event, hideRemove = false, value = null) {
  var id = uuidv4();

  var container = document.createElement('DIV');
  container.classList.add('row', 'mb-3', 'align-items-end');
  container.id = id;

  var formContainer = document.createElement('DIV');
  formContainer.classList.add('col');

  var name = document.createElement('DIV');
  name.classList.add('form-group', 'mb-0');
  var nameLabel = document.createElement('LABEL');
  var nameField = document.createElement('INPUT');
  var invalidFeedback = document.createElement('DIV');
  invalidFeedback.classList.add('invalid-feedback');
  invalidFeedback.textContent = 'Please enter a valid price';
  nameField.type = 'number';
  nameField.setAttribute('min', '0');
  nameField.setAttribute('step', '0.01');
  nameField.required = true;
  nameField.placeholder = '0';
  nameField.classList.add('form-control', 'nswimmers-fee-input');
  nameField.name = 'class_fee[]';
  nameField.id = id + '-class-fee';
  if (value) {
    nameField.value = value;
  }
  nameLabel.textContent = 'Person N+';
  nameLabel.htmlFor = id + '-class-fee';
  nameLabel.classList.add('fee-name-label');

  name.appendChild(nameLabel);
  name.appendChild(nameField);
  name.appendChild(invalidFeedback);

  formContainer.appendChild(name);

  var removeContainer = document.createElement('DIV');
  removeContainer.classList.add('col-md-auto');
  var removeButton = document.createElement('BUTTON');
  removeButton.classList.add('btn', 'btn-warning', 'remove-buttons');
  removeButton.textContent = 'Remove';
  removeButton.type = 'button';
  removeButton.dataset.removeTarget = id;

  var removeButtonTopSpace = document.createElement('DIV');
  removeButtonTopSpace.classList.add('mb-2', 'd-md-none');

  removeContainer.appendChild(removeButtonTopSpace);
  removeContainer.appendChild(removeButton);

  container.appendChild(formContainer);
  if (!hideRemove) {
    container.appendChild(removeContainer);
  }

  document.getElementById('fees-box').appendChild(container);

  assignNames();
}

function assignNames() {
  let labels = document.getElementsByClassName('fee-name-label');
  let i = 1;
  for (let label of labels) {
    if (i < labels.length) {
      label.textContent = 'Total for ' + i + ' person';
      if (i != 1) {
        label.textContent += 's';
      }
    } else {
      label.textContent = 'Total for ' + i + '+ persons';
    }
    i++;
  };
}

document.getElementById('add-guest').addEventListener('click', addField);

let box = document.getElementById('fees-box');
let fees = JSON.parse(box.dataset.fees);
if (box.dataset.init === 'true') {
  if (fees.length > 0) {
    fees.forEach(fee => {
      addField(null, true, fee);
    });
  } else {
    addField(null, true);
  }
}

document.getElementById('fees-box').addEventListener('click', (event) => {
  if (event.target.dataset.removeTarget) {
    var remove = document.getElementById(event.target.dataset.removeTarget);
    if (remove) {
      remove.parentElement.removeChild(remove);
    }
    assignNames();
  }
});