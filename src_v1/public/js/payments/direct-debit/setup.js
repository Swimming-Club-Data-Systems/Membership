const stripeData = document.getElementById('stripe-data');

var stripe = Stripe(stripeData.dataset.stripePublishable, {
  stripeAccount: stripeData.dataset.stripeAccountId
});

document.getElementById('set-up-button').addEventListener('click', (event) => {
  stripe.redirectToCheckout({
    // Make the id field from the Checkout Session creation API response
    // available to this file, so you can provide it as parameter here
    // instead of the {{CHECKOUT_SESSION_ID}} placeholder.
    sessionId: stripeData.dataset.sessionId,
  }).then(function (result) {
    // If `redirectToCheckout` fails due to a browser or network
    // error, display the localized error message to your customer
    // using `result.error.message`.
    console.warn(result);
  });
});