/**
 * Add payment card code
 * Uses setup intents
 */

var stripeData = document.getElementById('stripe-data');

var stripe = Stripe(stripeData.dataset.stripePublishable, {
  stripeAccount: stripeData.dataset.stripeAccountId
});

var elements = stripe.elements({
  fonts: [
    {
      cssSrc: stripeData.dataset.stripeFontCss,
    },
  ]
});

// Add an instance of the card Element into the `card-element` <div>.
//cardElement.mount('#card-element');

var cardNumberElement = elements.create('cardNumber', {
  style: stripeElementStyle
});
cardNumberElement.mount('#card-number-element');
var cardExpiryElement = elements.create('cardExpiry', {
  style: stripeElementStyle
});
cardExpiryElement.mount('#card-expiry-element');
var cardCvcElement = elements.create('cardCvc', {
  style: stripeElementStyle
});
cardCvcElement.mount('#card-cvc-element');

function cardChangeEvent(event, elementId) {
  var displayError = document.getElementById(elementId);
  if (event.error) {
    displayError.textContent = event.error.message;
  } else {
    displayError.textContent = '';
  }
}

cardNumberElement.addEventListener('change', function(event) {
  cardChangeEvent(event, 'card-number-element-errors');
  if (event.brand) {
  	setCardBrandIcon(event.brand);
  }
});
cardExpiryElement.addEventListener('change', function(event) {
  cardChangeEvent(event, 'card-expiry-element-errors');
});
cardCvcElement.addEventListener('change', function(event) {
  cardChangeEvent(event, 'card-cvc-element-errors');
});

function verifyDetails() {
  var status = true;
  if (!document.getElementById('cardholder-name').value) {
    status = false;
  }
  if (!document.getElementById('addr-line-1').value) {
    status = false;
  }
  if (!document.getElementById('addr-post-code').value) {
    status = false;
  }
  return status;
}

var cardholderName = document.getElementById('cardholder-name');
var cardholderAddress1 = document.getElementById('addr-line-1');
var cardholderZip = document.getElementById('addr-post-code');
cardholderZip.addEventListener('change', function(event) {
  //cardNumberElement.update({value: {postalCode: event.target.value.toUpperCase()}});
});
var cardholderCountry = document.getElementById('addr-country');
var cardButton = document.getElementById('card-button');
var clientSecret = cardButton.dataset.secret;

var form = document.getElementById('payment-form');
form.addEventListener('submit', function(event) {
  event.preventDefault();
  var displayError = document.getElementById('card-errors');

  if (verifyDetails()) {
    stripe.handleCardSetup(
      clientSecret, cardNumberElement, {
        payment_method_data: {
          billing_details: {
            name: cardholderName.value,
            address: {
              line1: cardholderAddress1.value,
              postal_code: cardholderZip.value,
              country: cardholderCountry.value,
            },
          }
        }
      }
    ).then(function(result) {
      if (result.error) {
        // Display error.message in your UI.
        displayError.innerHTML = '<div class="alert alert-danger" id="card-errors-message"></div>'
        document.getElementById('card-errors-message').textContent = result.error.message;
      } else {
        // The setup has succeeded. Display a success message.
        disableButtons();
        var hideForm = document.getElementById('form-hideable');
        hideForm.classList.remove('show');
        hideForm.classList.add('hidden');
        hideForm.classList.add('d-none');
        displayError.innerHTML = '<div class="alert alert-success text-center" id="card-errors-message"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div><div class="mb-0 mt-3 h3">Loading</div><hr>Card setup successfully. Please wait while we redirect you.</div>';
        //document.getElementById('card-errors-message').textContent = 'Card setup successfully. Please wait while we redirect you.';
        // The payment has succeeded. Display a success message.
        var form = document.getElementById('payment-form');
        // Submit the form
        form.submit();
      }
    });
  } else {
    displayError.innerHTML = '<div class="alert alert-danger" id="card-errors-message"></div>'
    document.getElementById('card-errors-message').textContent = 'You must provide all details required, including the cardholder\'s name and address';
  }
});

function setDark() {
  cardNumberElement.update({style: stripeElementStyleDarkMode});
  cardExpiryElement.update({style: stripeElementStyleDarkMode});
  cardCvcElement.update({style: stripeElementStyleDarkMode});
}

function setLight() {
  cardNumberElement.update({style: stripeElementStyle});
  cardExpiryElement.update({style: stripeElementStyle});
  cardCvcElement.update({style: stripeElementStyle});
}

if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
  // dark mode
  setDark();
}

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
  const newColorScheme = e.matches ? "dark" : "light";
  console.log(newColorScheme);
  if (newColorScheme == 'light') {
    setLight();
  } else {
    setDark();
  }
});