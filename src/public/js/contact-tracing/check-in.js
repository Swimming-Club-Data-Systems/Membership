function uuidv4() {
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
    var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
    return v.toString(16);
  });
}

function addField(event, hideRemove = false) {
  var id = uuidv4();

  var container = document.createElement('DIV');
  container.classList.add('row', 'mb-4');
  container.id = id;

  var formContainer = document.createElement('DIV');
  formContainer.classList.add('col');

  var formRow = document.createElement('DIV');
  formRow.classList.add('row', 'g-2');

  var name = document.createElement('DIV');
  name.classList.add('form-group', 'mb-0');
  var nameLabel = document.createElement('LABEL');
  var nameField = document.createElement('INPUT');
  var invalidFeedback = document.createElement('DIV');
  invalidFeedback.classList.add('invalid-feedback');
  invalidFeedback.textContent = 'Please enter a name';
  nameField.type = 'text';
  nameField.required = true;
  nameField.placeholder = 'Name';
  nameField.classList.add('form-control');
  nameField.name = 'guest_name[]';
  nameField.id = id + '-name';
  nameLabel.textContent = 'Name';
  nameLabel.htmlFor = id + '-name';
  nameLabel.classList.add('sr-only');

  name.appendChild(nameLabel);
  name.appendChild(nameField);
  name.appendChild(invalidFeedback);

  var col = document.createElement('div');
  col.classList.add('col');
  col.appendChild(name);
  formRow.appendChild(col);

  var number = document.createElement('DIV');
  number.classList.add('form-group', 'mb-0');
  var numberLabel = document.createElement('LABEL');
  var numberField = document.createElement('INPUT');
  invalidFeedback = document.createElement('DIV');
  invalidFeedback.classList.add('invalid-feedback');
  invalidFeedback.textContent = 'Please enter a telephone number';
  numberField.type = 'tel';
  numberField.required = true;
  numberField.placeholder = 'Phone';
  numberField.classList.add('form-control');
  numberField.name = 'guest_phone[]';
  numberField.id = id + '-phone';
  numberField.pattern = '\\+{0,1}[0-9]*';
  numberLabel.textContent = 'Phone';
  numberLabel.htmlFor = id + '-phone';
  numberLabel.classList.add('sr-only');

  number.appendChild(numberLabel);
  number.appendChild(numberField);
  number.appendChild(invalidFeedback);

  var formId = document.createElement('INPUT');
  formId.name = 'guest_entry_uuid[]';
  formId.type = 'hidden';
  formId.value = id;
  container.appendChild(formId);

  col = document.createElement('div');
  col.classList.add('col');
  col.appendChild(number);
  formRow.appendChild(col);

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

  formContainer.appendChild(formRow);
  container.appendChild(formContainer);
  if (!hideRemove) {
    container.appendChild(removeContainer);
  }

  document.getElementById('guests-box').appendChild(container);

}

document.getElementById('add-guest').addEventListener('click', addField);

if (document.getElementById('guests-box').dataset.init === 'true') {
  addField(null, true);
}

document.getElementById('guests-box').addEventListener('click', (event) => {
  if (event.target.dataset.removeTarget) {
    var remove = document.getElementById(event.target.dataset.removeTarget);
    if (remove) {
      remove.parentElement.removeChild(remove);
    }
  }
});