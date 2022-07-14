let checkoutButton = document.getElementById('checkout-button');
let form = document.getElementById('form');
if (checkoutButton) {

  // Change button text
  let selectMethod = document.getElementById('payment-method-select');
  selectMethod.addEventListener('change', event => {
    let radios = document.getElementsByName('payment-method');
    let selectedOption = 'card';
    for (var i = 0, length = radios.length; i < length; i++) {
      if (radios[i].checked) {
        selectedOption = radios[i].value;
        break;
      }
    }

    let descriptionBox = document.getElementById('descripton-box');
    if (selectedOption == 'card') {
      checkoutButton.textContent = 'Proceed to payment';
      descriptionBox.innerHTML = '';
    } else if (selectedOption == 'dd') {
      checkoutButton.textContent = 'Complete registration/renewal';
      descriptionBox.innerHTML = '';
    } else {
      checkoutButton.textContent = 'Complete registration/renewal';
      descriptionBox.innerHTML = '<p>Registration will be marked as completed immediately after you press <em>Complete registration/renewal</em>. Please ensure you\'ve noted down how much you owe.</p>';
    }
  });

  // Handle alerts
  form.addEventListener('submit', event => {

    // event.stopPropagation();
    // event.preventDefault();

    // console.log(form);

  });
}