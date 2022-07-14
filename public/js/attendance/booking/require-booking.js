let limitRadio = document.getElementById('number-limit');

limitRadio.addEventListener('change', event => {
  let maxPlacesContainer = document.getElementById('max-places-container');
  let maxCount = document.getElementById('max-count');
  if (event.target.value == '1') {
    maxPlacesContainer.classList.remove('d-none');
    maxCount.required = true;
  } else {
    maxPlacesContainer.classList.add('d-none');
    maxCount.required = false;
  }
});

let openBookingRadio = document.getElementById('open-bookings');

openBookingRadio.addEventListener('change', event => {
  let openAtContainer = document.getElementById('open-bookings-at-container');
  let opensDate = document.getElementById('open-booking-at-date');
  let opensTime = document.getElementById('open-booking-at-time');
  if (event.target.value == '1') {
    openAtContainer.classList.remove('d-none');
    opensDate.required = true;
    opensTime.required = true;
  } else {
    openAtContainer.classList.add('d-none');
    opensDate.required = false;
    opensTime.required = false;
  }
});

let chargeFeeRadio = document.getElementById('booking-fees');

chargeFeeRadio.addEventListener('change', event => {
  let feesContainer = document.getElementById('booking-fees-container');
  let feesInput = document.getElementById('booking-fees-amount');
  if (event.target.value == '1') {
    feesContainer.classList.remove('d-none');
    feesInput.required = true;
  } else {
    feesContainer.classList.add('d-none');
    feesInput.required = false;
  }
});