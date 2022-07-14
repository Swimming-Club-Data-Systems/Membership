var ajax = new XMLHttpRequest();
ajax.onreadystatechange = function() {
  if (this.readyState == 4 && this.status == 200) {
    data = JSON.parse(this.responseText);
    if (data.showMessage) {
      document.getElementById('emergency-message-container').innerHTML = data.message;
    }
  }
}
ajax.open('GET', document.getElementById('emergency-message-container').dataset.url, true);
ajax.send();