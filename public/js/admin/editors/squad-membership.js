const options = document.getElementById('options-data').dataset;

let slg = document.getElementById('squad-list-group');
if (slg) {
  slg.addEventListener('change', event => {

    if (event.target.tagName == 'INPUT' && event.target.type == 'checkbox') {
      // Handle a state change

      let formData = new FormData();
      formData.append('squad', event.target.dataset.squad);
      formData.append('member', event.target.dataset.member);
      formData.append('state', event.target.checked);

      var req = new XMLHttpRequest();
      req.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
          let json = JSON.parse(this.responseText);
          if (json.status == 200) {
            // Do nothing
          } else {
            // Change button state back
            event.target.checked = !event.target.checked;
            alert(json.error);
          }
        } else if (this.readyState == 4) {
          // Not ok
          // Change button state back
          event.target.checked = !event.target.checked;
          alert('An error occurred and we could not parse the submission.');
        }
      }
      req.open('POST', options.ajaxUrl, true);
      req.setRequestHeader('Accept', 'application/json');
      req.send(formData);
    }

  });
}