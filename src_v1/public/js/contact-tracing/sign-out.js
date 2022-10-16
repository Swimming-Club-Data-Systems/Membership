const options = document.getElementById('socket-info').dataset;

console.info(options.room);

const socket = io(document.getElementById('socket-info').dataset.init);

socket.on('connect', () => {
  // Tell the socket which room we want to join
  socket.emit('covid-join-room', {
    room: options.room,
  });

  // console.log(socket.connected); // true
});

socket.on('covid-test', (message) => {
  // console.log(message);
})

socket.on('tick-event', (message) => {
  // console.log(message);

  if (message.event == 'covid-sign-out-change') {
    let input = document.getElementById('visitor-' + message.field);
    if (input)
      input.checked = message.state;
  }
})

socket.on('disconnect', () => {
  // console.log(socket.connected); // false
});

document.getElementById('checkboxes').addEventListener('change', (event) => {
  if (event.target.type == 'checkbox') {
    // Get value and send an AJAX request
    let value = event.target.checked;

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
    req.send('id=' + encodeURI(event.target.dataset.id) + '&state=' + encodeURI(value));
  }
});