document.getElementById('recurrence-radios').addEventListener('change', ev => {
  let showUntil = document.getElementById('session-end-date');
  let showUntilContainer = document.getElementById('show-until-container');
  let onlyIfBooking = document.getElementsByClassName('show-if-one-off');
  if (ev.target.value === 'recurring') {
    showUntil.required = true;
    showUntilContainer.classList.remove('d-none');
    for (let item of onlyIfBooking) {
      item.classList.add('d-none');
    }
  } else {
    showUntil.required = false;
    showUntilContainer.classList.add('d-none');
    for (let item of onlyIfBooking) {
      item.classList.remove('d-none');
    }
  }
});