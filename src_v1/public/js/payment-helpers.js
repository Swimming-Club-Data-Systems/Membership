/**
 * Common code for CLS Membership Payments
 */

var stripeElementStyle = {
  base: {
    iconColor: '#ced4da',
    lineHeight: '1.5',
    height: '33px',
    color: '#212529',
    fontWeight: 400,
    fontFamily: 'system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Liberation Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"',
    fontSize: '14px',
    fontSmoothing: 'antialiased',
    '::placeholder': {
      color: '#868e96',
    },
    padding: '0.375rem 0.75rem',
  },
  invalid: {
    color: '#212529',
  },
}

var stripeElementStyleDarkMode = {
  base: {
    iconColor: '#ced4da',
    lineHeight: '1.5',
    height: '33px',
    color: '#ffffff',
    fontWeight: 400,
    fontFamily: 'system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Liberation Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"',
    fontSize: '14px',
    fontSmoothing: 'antialiased',
    '::placeholder': {
      color: '#6c757d',
    },
    padding: '0.375rem 0.75rem',
  },
  invalid: {
    color: '#ffffff',
  },
}

function setCardBrandIcon(brand) {
  root = document.getElementById('app-js-info').dataset.root;
  var content = '<img class="fa fa-fw" src="' + root + '/img/stripe/network-svgs/credit-card.svg" aria-hidden="true">';
  if (brand === 'visa') {
    content = '<img class="fa fa-fw d-dark-none" src="' + root + '/img/stripe/brand-checkout/visa_light.svg" aria-hidden="true"><img class="fa fa-fw d-light-none" src="' + root + '/img/stripe/brand-checkout/visa_dark.svg" aria-hidden="true">';
  } else if (brand === 'mastercard') {
    content = '<img class="fa fa-fw d-dark-none" src="' + root + '/img/stripe/brand-checkout/mastercard_light.svg" aria-hidden="true"><img class="fa fa-fw d-light-none" src="' + root + '/img/stripe/brand-checkout/mastercard_dark.svg" aria-hidden="true">';
  } else if (brand === 'amex') {
    content = '<img class="fa fa-fw d-dark-none" src="' + root + '/img/stripe/brand-checkout/amex_light.svg" aria-hidden="true"><img class="fa fa-fw d-light-none" src="' + root + '/img/stripe/brand-checkout/amex_dark.svg" aria-hidden="true">';
  } else if (brand === 'discover') {
    content = '<img class="fa fa-fw d-dark-none" src="' + root + '/img/stripe/brand-checkout/discover_light.svg" aria-hidden="true"><img class="fa fa-fw d-light-none" src="' + root + '/img/stripe/brand-checkout/discover_dark.svg" aria-hidden="true">';
  } else if (brand === 'diners') {
    content = '<img class="fa fa-fw d-dark-none" src="' + root + '/img/stripe/brand-checkout/diners_light.svg" aria-hidden="true"><img class="fa fa-fw d-light-none" src="' + root + '/img/stripe/brand-checkout/diners_dark.svg" aria-hidden="true">';
  } else if (brand === 'jcb') {
    content = '<img class="fa fa-fw" src="' + root + '/img/stripe/network-svgs/jcb.svg" aria-hidden="true">';
  } else if (brand === 'unionpay') {
    content = '<img class="fa fa-fw" src="' + root + '/img/stripe/network-svgs/union.svg" aria-hidden="true">';
  }
  document.getElementById('card-brand-element').innerHTML = content;
}

function disableButtons() {
  document.querySelectorAll('.pm-can-disable').forEach(elem => {
    elem.disabled = true;
  });
}

function enableButtons() {
  document.querySelectorAll('.pm-can-disable').forEach(elem => {
    elem.disabled = false;
  });
}

function jumpToPosition(h) {
  var url = location.href;               //Save down the URL without hash.
  location.href = "#"+h;                 //Go to the target element.
  history.replaceState(null,null,url);   //Don't like hashes. Changing it back.
}