let expiresBox = document.getElementById('expires-box');
expiresBox.addEventListener('change', ev => {
  let v = ev.target.value;
  let expiresOptions = document.getElementById('expires-options');
  let requires = document.getElementsByClassName('requirable');
  if (v === 'yes') {
    expiresOptions.classList.remove('d-none');
    for (let item of requires) {
      item.required = true;
    }
  } else if (v === 'no') {
    expiresOptions.classList.add('d-none');
    for (let item of requires) {
      item.required = false;
    }
  }
});

let expiresWhenType = document.getElementById('expires-when-type');
expiresWhenType.addEventListener('change', ev => {
  let v = ev.target.value;
  let expiresTypeDesc = document.getElementById('expires-when-addon');
  if (v === 'years') {
    expiresTypeDesc.textContent = 'years';
  } else if (v === 'months') {
    expiresTypeDesc.textContent = 'months';
  } else if (v === 'days') {
    expiresTypeDesc.textContent = 'days';
  }
});