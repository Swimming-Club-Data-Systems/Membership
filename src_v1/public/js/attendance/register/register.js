const options = document.getElementById('data-block').dataset;
const registerArea = document.getElementById('register-area');
const ssf = document.getElementById('session-selection-form');
const socket = io(options.socketInit);

ssf.addEventListener('change', (event) => {
  // console.log(event);

  // Get values
  let datePicker = document.getElementById('session-date');
  let date = datePicker.value;
  let sessionPicker = document.getElementById('session-select');
  let session = sessionPicker.value;

  if (event.target.id == 'session-date') {
    // Date changed - Reload session list
    sessionPicker.disabled = true;
    sessionPicker.innerHTML = `<option value="none">Loading sessions...</option>`;
    registerArea.innerHTML = '';

    // Get sessions
    var req = new XMLHttpRequest();
    req.onreadystatechange = function () {
      if (this.readyState == 4 && this.status == 200) {
        object = JSON.parse(this.responseText);
        if (object.status == 200) {
          sessionPicker.innerHTML = object.html;
          sessionPicker.disabled = false;
        }
      } else if (this.readyState == 4) {
        // Not ok
      }
    }
    req.open('POST', options.sessionListAjaxUrl, true);
    req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    let sessionString = '';
    if (session != 'none') {
      sessionString = '&session=' + encodeURI(session);
    }
    req.send('date=' + encodeURI(date) + sessionString);
  }

  let sessionUrlString = '';
  if (event.target.id == 'session-select') {
    sessionUrlString = '&session=' + encodeURI(session);

    // Get register for this session
    var req = new XMLHttpRequest();
    req.onreadystatechange = function () {
      if (this.readyState == 4 && this.status == 200) {
        object = JSON.parse(this.responseText);
        if (object.status == 200) {
          registerArea.innerHTML = object.html;
          setIndeterminate();

          if (socket.connected) {
            joinSocketRoom();
          }
        }
      } else if (this.readyState == 4) {
        // Not ok
      }
    }
    req.open('POST', options.registerSheetAjaxUrl, true);
    req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    req.send('date=' + encodeURI(date) + '&session=' + encodeURI(session));
  }

  window.history.replaceState('string', 'Title', window.location.origin + window.location.pathname + '?date=' + encodeURI(date) + sessionUrlString);
});

let regArea = document.getElementById('register-area');
regArea.addEventListener('click', (event) => {

  if (event) {
    let clickedTarget = event.target;
    if (clickedTarget.tagName == 'SPAN' && clickedTarget.parentNode && clickedTarget.parentNode.tagName == 'BUTTON') {
      clickedTarget = clickedTarget.parentNode;
    }

    if (clickedTarget.dataset.show) {
      let target = document.getElementById(clickedTarget.dataset.show);
      if (target.classList.contains('d-none')) {
        // Currently hidden so lets show
        target.classList.remove('d-none');
        target.classList.add('register-hideable-active');
      } else {
        // Currently displayed so hide
        target.classList.add('d-none');
        target.classList.remove('register-hideable-active');
      }

      // Hide all other displayed ones
      let displayable = document.getElementsByClassName('register-hideable register-hideable-active');
      for (let i = 0; i < displayable.length; i++) {
        let toClose = displayable[i];
        if (toClose != target) {
          toClose.classList.add('d-none');
          toClose.classList.remove('register-hideable-active');
        }
      }
    }
  }

});

function sendChange(weekId, sessionId, memberId, value) {
  if (typeof weekId !== 'undefined' && typeof sessionId !== 'undefined' && typeof memberId !== 'undefined') {
    var req = new XMLHttpRequest();
    req.onreadystatechange = function () {
      if (this.readyState == 4 && this.status == 200) {

      } else if (this.readyState == 4) {
        // Not ok
        alert('An error occurred. Your change was not saved.');
      }
    }
    req.open('POST', options.ajaxUrl, true);
    req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    req.send('weekId=' + encodeURI(weekId) + '&sessionId=' + encodeURI(sessionId) + '&memberId=' + encodeURI(memberId) + '&state=' + encodeURI(value));
  }
}

var mouseDownTimer;
regArea.addEventListener('pointerdown', function (event) {
  // simulating hold event
  if (event.target.type == 'checkbox' || (event.target.previousElementSibling && event.target.previousElementSibling.nodeName == 'INPUT')) {
    mouseDownTimer = window.setTimeout(function (event) {
      // You are now in a hold state, you can do whatever you like!
      // console.log(event);
      event.stopPropagation();
      event.preventDefault();

      let checkbox = event.target;
      if (event.target.previousElementSibling && event.target.previousElementSibling.nodeName == 'INPUT') {
        checkbox = event.target.previousElementSibling;
      }

      let setState = !checkbox.indeterminate;
      checkbox.indeterminate = setState;

      // Get value and send an AJAX request
      let value = checkbox.value;
      if (setState) {
        value = 2;
      }
      let weekId = checkbox.dataset.weekId;
      let sessionId = checkbox.dataset.sessionId;
      let memberId = checkbox.dataset.memberId;

      checkbox.parentElement.addEventListener('change', (event) => {
        // Ignore the event
        event.stopPropagation();
        event.preventDefault();
        checkbox.indeterminate = setState;
        checkbox.value = false;
      }, { once: true });

      sendChange(weekId, sessionId, memberId, value);
    }, 500, event);
  }
});

regArea.addEventListener('pointerup', function (event) {
  if (mouseDownTimer) window.clearTimeout(mouseDownTimer);
});

regArea.addEventListener('change', (event) => {

  if (event) {
    // console.info('Change event -');
    // console.log(event);
  }

  if (event.target.type == 'checkbox') {
    // Remove any indeterminate state
    delete event.target.indeterminate;

    // Get value and send an AJAX request
    let value = event.target.checked;
    let weekId = event.target.dataset.weekId;
    let sessionId = event.target.dataset.sessionId;
    let memberId = event.target.dataset.memberId;

    sendChange(weekId, sessionId, memberId, value);
  }

});

socket.on('connect', () => {
  joinSocketRoom();

  // console.log(socket.connected); // true
});

// socket.on('register-test', (message) => {
//   console.log(message);
// });

socket.on('register-tick-event', (message) => {
  if (message.event == 'register-item-state-change') {
    let input = document.getElementById('member-' + message.field);
    if (input) {
      if (message.state == null) {
        input.checked = false;
        input.indeterminate = true;
      } else {
        input.checked = message.state;
        input.indeterminate = false;
      }
    }
  }
})

/**
 * Join the socket.io room for the current register
 */
async function joinSocketRoom() {
  let socketRoomInfo = document.getElementById('socket-room-info');
  if (socketRoomInfo) {
    let currentSocketRoom = socketRoomInfo.dataset.roomName;
    // console.log(currentSocketRoom);
    // Tell the socket which room we want to join
    socket.emit('register-join-room', {
      room: currentSocketRoom,
    });

    // console.info('Socket connected');
  }
}

function setIndeterminate() {
  let regCheckboxes = document.getElementsByClassName('checkbox-input');
  for (let i = 0; i < regCheckboxes.length; i++) {
    let checkbox = regCheckboxes[i];

    if (checkbox.dataset.indeterminate && checkbox.dataset.indeterminate == 'true') {
      checkbox.indeterminate = true;
    } else {
      checkbox.indeterminate = false;
    }

  }
}

// Call on first run
setIndeterminate()