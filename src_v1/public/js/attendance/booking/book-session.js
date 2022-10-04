const options = document.getElementById('ajaxData').dataset;
const socket = io(options.socketInit);

let sharingModal = new bootstrap.Modal(document.getElementById('sharing-modal'));
let bookingModal = new bootstrap.Modal(document.getElementById('booking-modal'));
let cancelModal = new bootstrap.Modal(document.getElementById('cancel-modal'));

function updateBookingNumbers(stats) {
  // Update numbers
  if (stats.placesTotal) {
    let intSpans = document.getElementsByClassName('place-numbers-max-places-int');
    let stringSpans = document.getElementsByClassName('place-numbers-max-places-string');

    for (let element of intSpans) {
      element.textContent = stats.placesTotal.int;
    }

    for (let element of stringSpans) {
      if (element.classList.contains('uc-first')) {
        element.textContent = stats.placesTotal.string.charAt(0).toUpperCase() + stats.placesTotal.string.slice(1);
      } else {
        element.textContent = stats.placesTotal.string;
      }
    }
  }

  if (stats.placesBooked) {
    let intSpans = document.getElementsByClassName('place-numbers-places-booked-int');
    let stringSpans = document.getElementsByClassName('place-numbers-places-booked-string');

    for (let element of intSpans) {
      element.textContent = stats.placesBooked.int;
    }

    for (let element of stringSpans) {
      if (element.classList.contains('uc-first')) {
        element.textContent = stats.placesBooked.string.charAt(0).toUpperCase() + stats.placesBooked.string.slice(1);
      } else {
        element.textContent = stats.placesBooked.string;
      }
    }

    let bookedPlacesString = document.getElementById('place-numbers-booked-places-member-string');
    if (stats.placesBooked.int == 1) {
      bookedPlacesString.textContent = 'member has';
    } else {
      bookedPlacesString.textContent = 'members have';
    }
  }

  if (stats.placesRemaining) {
    let intSpans = document.getElementsByClassName('place-numbers-places-remaining-int');
    let stringSpans = document.getElementsByClassName('place-numbers-places-remaining-string');

    for (let element of intSpans) {
      element.textContent = stats.placesRemaining.int;
    }

    if (stats.placesRemaining.int == 0) {
      bookingModal.hide();
    }

    for (let element of stringSpans) {
      if (element.classList.contains('uc-first')) {
        element.textContent = stats.placesRemaining.string.charAt(0).toUpperCase() + stats.placesRemaining.string.slice(1);
      } else {
        element.textContent = stats.placesRemaining.string;
      }
    }

    let remainingPlacesString = document.getElementById('place-numbers-places-remaining-member-string');
    if (stats.placesRemaining.int == 1) {
      remainingPlacesString.textContent = 'place remains';
    } else {
      remainingPlacesString.textContent = 'places remain';
    }
  }
}

async function reloadMyMemberList() {

  let targetBox = document.getElementById('my-member-booking-container-box');
  if (targetBox) {

    // ------------------------------------------------------------
    // Reload booking box etc via ajax request
    // ------------------------------------------------------------
    var dataReload = new XMLHttpRequest();
    dataReload.onreadystatechange = function () {
      if (this.readyState == 4 && this.status == 200) {
        let json = JSON.parse(this.responseText);
        if (json.status == 200) {

          targetBox.innerHTML = json.html;
          updateBookingNumbers(json.stats);

        } else {
          location.reload();
        }
      } else if (this.readyState == 4) {
        location.reload();
      }
    }
    dataReload.open('GET', options.myMemberReloadAjaxUrl, true);
    dataReload.setRequestHeader('Accept', 'application/json');
    dataReload.send();
    // ------------------------------------------------------------
    // ENDS Reload booking box etc via ajax request
    // ------------------------------------------------------------

  }

}

async function reloadAllMemberList() {

  let targetBox = document.getElementById('all-member-booking-container-box');
  if (targetBox) {

    // ------------------------------------------------------------
    // Reload booking box etc via ajax request
    // ------------------------------------------------------------
    var dataReload = new XMLHttpRequest();
    dataReload.onreadystatechange = function () {
      if (this.readyState == 4 && this.status == 200) {
        let json = JSON.parse(this.responseText);
        if (json.status == 200) {

          targetBox.innerHTML = json.html;
          updateBookingNumbers(json.stats);

        } else {
          location.reload();
        }
      } else if (this.readyState == 4) {
        // Not ok
        location.reload();
      }
    }
    dataReload.open('GET', options.allMemberReloadAjaxUrl, true);
    dataReload.setRequestHeader('Accept', 'application/json');
    dataReload.send();
    // ------------------------------------------------------------
    // ENDS Reload booking box etc via ajax request
    // ------------------------------------------------------------

  }

}

let form = document.getElementById('member-booking-form');
form.addEventListener('submit', event => {
  event.preventDefault();
  let formData = new FormData(form);

  // console.log(formData);
  var req = new XMLHttpRequest();
  req.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      let json = JSON.parse(this.responseText);
      if (json.status == 200) {
        // Reload booking box etc
        // console.log(formData);
        reloadMyMemberList();
        reloadAllMemberList();

        // Show success message, dismiss modal, reload booking box
        bookingModal.hide();

      } else {
        alert(json.error);
      }
    } else if (this.readyState == 4) {
      // Not ok
      alert('An error occurred and we could not parse the submission.');
    }
  }
  req.open('POST', options.bookingAjaxUrl, true);
  req.setRequestHeader('Accept', 'application/json');
  req.send(formData);
});

let cancelForm = document.getElementById('cancel-booking-form');
cancelForm.addEventListener('submit', event => {
  event.preventDefault();
  let formData = new FormData(cancelForm);

  // console.log(formData);
  var req = new XMLHttpRequest();
  req.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      let json = JSON.parse(this.responseText);
      if (json.status == 200) {

        reloadMyMemberList();
        reloadAllMemberList();

        // Hide modal
        cancelModal.hide();

      } else {
        alert(json.error);
      }
    } else if (this.readyState == 4) {
      // Not ok
      alert('An error occurred and we could not parse the submission.');
    }
  }
  req.open('POST', options.cancellationAjaxUrl, true);
  req.setRequestHeader('Accept', 'application/json');
  req.send(formData);
});

let mbl = document.getElementById('my-member-booking-container-box');
if (mbl) {
  mbl.addEventListener('click', event => {
    if (event.target.tagName === 'BUTTON' && event.target.dataset.operation === 'book-place') {
      document.getElementById('booking-modal-member-name').textContent = event.target.dataset.memberName;
      document.getElementById('booking-modal-session-name').textContent = event.target.dataset.sessionName;
      // document.getElementById('booking-modal-session-location').textContent = event.target.dataset.sessionLocation;

      document.getElementById('member-id').value = event.target.dataset.memberId;
      document.getElementById('session-id').value = event.target.dataset.sessionId;
      document.getElementById('session-date').value = event.target.dataset.sessionDate;

      bookingModal.show();
    }
  });
}

let ambl = document.getElementById('all-member-booking-container-box');
if (ambl) {
  ambl.addEventListener('click', event => {
    if (event.target.tagName === 'BUTTON' && event.target.dataset.operation === 'cancel-place') {
      document.getElementById('cancel-modal-member-name').textContent = event.target.dataset.memberName;
      document.getElementById('cancel-modal-session-name').textContent = event.target.dataset.sessionName;
      // document.getElementById('cancel-modal-session-location').textContent = event.target.dataset.sessionLocation;

      document.getElementById('cancel-member-id').value = event.target.dataset.memberId;
      document.getElementById('cancel-session-id').value = event.target.dataset.sessionId;
      document.getElementById('cancel-session-date').value = event.target.dataset.sessionDate;

      cancelModal.show();
    }
  });
}

let shareButton = document.getElementById('share-this');
shareButton.addEventListener('click', event => {
  if (navigator.share) {
    // Use system sharing sheet

    navigator.share({
      title: event.target.dataset.shareTitle,
      text: event.target.dataset.shareText,
      url: event.target.dataset.shareUrl,
    })
      .catch((err) => {
        // Don't log error
        if (err.name === 'NotAllowedError') {
          sharingModal.show();
        }
      }
      );
  } else {
    // Use sharing modal
    sharingModal.show();
  }
});

let printButton = document.getElementById('print-this-page');
if (printButton) {
  printButton.addEventListener('click', event => {
    sharingModal.hide();
    window.print();
  });
}

sharingModal.addEventListener('click', event => {
  if (event.target.classList.contains('dismiss-share-box')) {
    sharingModal.hide();
  }
});

socket.on('connect', () => {
  joinSocketRoom();

  // console.log(socket.connected); // true
});

socket.on('booking-page-book-cancel-event', (message) => {
  if (message.event == 'booking-page-book-cancel') {
    // Reload
    if (message.update == true) {
      reloadMyMemberList();
      reloadAllMemberList();
    }
  }
})

/**
 * Join the socket.io room for the current register
 */
async function joinSocketRoom() {
  if (options.socketRoomName) {
    let currentSocketRoom = options.socketRoomName;
    // console.log(currentSocketRoom);
    // Tell the socket which room we want to join
    socket.emit('booking-page-join-room', {
      room: currentSocketRoom,
    });

    // console.info('Socket connected');
  }
}