var stripeData = document.getElementById('stripe-data');

var stripe = Stripe(stripeData.dataset.stripePublishable, {
  stripeAccount: stripeData.dataset.stripeAccountId
});

var cardButton = document.getElementById('new-card-button');
var clientSecret = cardButton.dataset.secret;
var elements = stripe.elements({
  fonts: [
    {
      cssSrc: stripeData.dataset.stripeFontCss,
    },
  ]
});
var successAlert = '<div class="alert alert-success"><p class="mb-0"><strong>Payment Successful</strong></p><p class="mb-0">Please wait while we redirect you</p></div>';

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

cardNumberElement.addEventListener('change', function (event) {
  cardChangeEvent(event, 'card-number-element-errors');
  if (event.brand) {
    setCardBrandIcon(event.brand);
  }
});
cardExpiryElement.addEventListener('change', function (event) {
  cardChangeEvent(event, 'card-expiry-element-errors');
});
cardCvcElement.addEventListener('change', function (event) {
  cardChangeEvent(event, 'card-cvc-element-errors');
});

function verifyDetails() {
  var status = true;
  if (!document.getElementById('new-cardholder-name').value) {
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

var cardholderName = document.getElementById('new-cardholder-name');
var cardholderAddress1 = document.getElementById('addr-line-1');
var cardholderZip = document.getElementById('addr-post-code');
cardholderZip.addEventListener('change', function (event) {
  //cardElement.update({value: {postalCode: event.target.value.toUpperCase()}});
});
var cardholderCountry = document.getElementById('addr-country');

var savedCardButton = document.getElementById('saved-card-button');
var clientSecret = cardButton.dataset.secret;

var form = document.getElementById('new-card-form');
form.addEventListener('submit', function (event) {
  event.preventDefault();

  if (verifyDetails()) {
    stripe.handleCardPayment(
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
    ).then(function (result) {
      if (result.error) {
        // Display error.message in your UI.
        document.getElementById('new-card-errors').innerHTML = '<div class="alert alert-danger"><p class="mb-0"><strong>An error occurred trying to take your payment</strong></p><p class="mb-0">' + result.error.message + '</p></div>';
      } else {
        // Disable buttons
        disableButtons();
        document.getElementById('new-card-errors').innerHTML = successAlert;
        // The payment has succeeded. Display a success message.
        window.location.replace(document.getElementById('stripe-data').dataset.redirectUrlNew);
      }
    });
  } else {
    document.getElementById('new-card-errors').innerHTML = '<div class="alert alert-danger"><p class="mb-0"><strong>An error occurred trying to take your payment</strong></p><p class="mb-0">You must provide all details required, including the cardholder\'s name and address</p></div>';
  }
});

var paymentRequest = stripe.paymentRequest({
  country: 'GB',
  currency: stripeData.dataset.intentCurrency,
  total: {
    label: stripeData.dataset.orgName,
    amount: parseInt(stripeData.dataset.intentAmount),
  },
  displayItems: JSON.parse(stripeData.dataset.paymentRequestLineItems),
  requestPayerName: true,
  requestPayerEmail: true,
});

let style = 'dark';
if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
  style = 'light';
}
var prButton = elements.create('paymentRequestButton', {
  paymentRequest: paymentRequest,
  style: {
    paymentRequestButton: {
      type: 'default', // default: 'default'
      theme: style,// | 'light' | 'light-outline', // default: 'dark'
      // default: '40px', the width is always '100%'
    },
  },
});

// Check the availability of the Payment Request API first.
paymentRequest.canMakePayment().then(function (result) {
  if (result) {
    prButton.mount('#payment-request-button');
  } else {
    document.getElementById('payment-request-card').style.display = 'none';
    // prButton.mount('#payment-request-button');
  }
});

paymentRequest.on('paymentmethod', function (ev) {
  stripe.confirmPaymentIntent(clientSecret, {
    payment_method: ev.paymentMethod.id,
  }).then(function (confirmResult) {
    if (confirmResult.error) {
      // Report to the browser that the payment failed, prompting it to
      // re-show the payment interface, or show an error message and close
      // the payment interface.
      ev.complete('fail');
      document.getElementById('alert-placeholder').innerHTML = '<div class="alert alert-danger"><p class="mb-0"><strong>An error occurred trying to take your payment</strong></p><p class="mb-0">' + result.error.message + '</p></div>';
    } else {
      // Report to the browser that the confirmation was successful, prompting
      // it to close the browser payment method collection interface.
      ev.complete('success');
      // Let Stripe.js handle the rest of the payment flow.
      stripe.retrievePaymentIntent(
        clientSecret
      ).then(function (result) {
        if (!result.error) {
          if (result.paymentIntent.status == 'succeeded') {
            // Disable buttons
            disableButtons();
            document.getElementById('alert-placeholder').innerHTML = successAlert;
            window.location.replace(document.getElementById('stripe-data').dataset.redirectUrlNew);
          } else {
            stripe.handleCardPayment(clientSecret).then(function (result) {
              if (result.error) {
                // The payment failed -- ask your customer for a new payment method.
                document.getElementById('alert-placeholder').innerHTML = '<div class="alert alert-danger mt-3"><p class="mb-0"><strong>An error occurred trying to take your payment</strong></p><p class="mb-0">' + result.error.message + '</p></div>';
              } else {
                // The payment has succeeded.
                // Disable buttons
                disableButtons();
                document.getElementById('new-card-errors').innerHTML = successAlert;
                window.location.replace(document.getElementById('stripe-data').dataset.redirectUrlNew);
              }
            });
          }
        }
      });
    }
  });
});

let method = document.getElementById('method');
if (method) {
  method.addEventListener('change', function (event) {
    var boxArea = document.getElementById('save-card-box');
    if (this.value !== 'select') {
      // Card can be used
      boxArea.classList.remove('d-none');
    } else {
      // Card cannot be used
      boxArea.classList.add('d-none');
    }
  });
}

if (savedCardButton) {
  savedCardButton.addEventListener('click', function (ev) {
    stripe.handleCardPayment(
      clientSecret,
      {
        payment_method: document.getElementById('method').value,
      }
    ).then(function (result) {
      if (result.error) {
        document.getElementById('saved-card-errors').innerHTML = '<div class="alert alert-danger"><p class="mb-0"><strong>An error occurred trying to take your payment</strong></p><p class="mb-0">' + result.error.message + '</p></div>';
        // Display error.message in your UI.
      } else {
        // The payment has succeeded. Display a success message.
        disableButtons();
        document.getElementById('saved-card-errors').innerHTML = successAlert;
        window.location.replace(document.getElementById('stripe-data').dataset.redirectUrl);
      }
    });
  });
}

function setDark() {
  cardNumberElement.update({ style: stripeElementStyleDarkMode });
  cardExpiryElement.update({ style: stripeElementStyleDarkMode });
  cardCvcElement.update({ style: stripeElementStyleDarkMode });
  prButton.update({
    style: {
      paymentRequestButton: {
        theme: 'light'
      }
    }
  });
}

function setLight() {
  cardNumberElement.update({ style: stripeElementStyle });
  cardExpiryElement.update({ style: stripeElementStyle });
  cardCvcElement.update({ style: stripeElementStyle });
  prButton.update({
    style: {
      paymentRequestButton: {
        theme: 'dark'
      }
    }
  });
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