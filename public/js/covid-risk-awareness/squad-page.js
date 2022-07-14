const options = document.getElementById('js-opts').dataset;

document.getElementById('table-area').addEventListener('click', event => {
  if (event.target.dataset.action && event.target.dataset.action == 'void') {
    document.getElementById('revokeModalLabel').textContent = 'Void ' + event.target.dataset.memberName + '\'s Form';
    let body = document.getElementById('revokeModalBody');
    body.innerHTML = '';

    let p = document.createElement('P');
    p.classList.add('mb-0');
    p.textContent = 'Are you sure that you want to void ' + event.target.dataset.memberName + '\'s COVID-19 Risk Awareness Form?';
    body.appendChild(p);

    let submission = event.target.dataset.formSubmissionId;

    document.getElementById('void-button').addEventListener('click', event => {
      // HTTP REQUEST
      var req = new XMLHttpRequest();
      req.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
          let json = JSON.parse(this.responseText);
          if (json.status == 200) {
            location.reload();
          } else {
            alert(json.error);
          }
        } else if (this.readyState == 4) {
          // Not ok
          alert('An error occurred and we could not void the form.');
        }
      }
      req.open('POST', options.voidAjaxUrl, true);
      req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
      req.send('submission=' + encodeURI(submission) + '&action=void');
    });

    let modal = new bootstrap.Modal(document.getElementById('revokeModal'));
    modal.show();
  }
});

document.getElementById('voidAllButton').addEventListener('click', (event) => {
  document.getElementById('revokeModalLabel').textContent = 'Void All Forms';
  let body = document.getElementById('revokeModalBody');
  body.innerHTML = '';

  let squad = event.target.dataset.squadId;

  let p = document.createElement('P');
  p.textContent = 'Are you sure that you want to void all COVID-19 Risk Awareness Forms for members of ' + event.target.dataset.squadName + '?';
  body.appendChild(p);

  p = document.createElement('P');
  p.classList.add('mb-0');
  p.textContent = 'Unlike when you void an individual member\'s form, we won\'t send any automatic emails. Please write an email explaining why the entire squad\'s forms have been voided using Notify.';
  body.appendChild(p);

  document.getElementById('void-button').textContent = 'Void Forms';

  document.getElementById('void-button').addEventListener('click', event => {
    // HTTP REQUEST
    var req = new XMLHttpRequest();
    req.onreadystatechange = function () {
      if (this.readyState == 4 && this.status == 200) {
        let json = JSON.parse(this.responseText);
        if (json.status == 200) {
          location.reload();
        } else {
          alert(json.error);
        }
      } else if (this.readyState == 4) {
        // Not ok
        alert('An error occurred and we could not void the form.');
      }
    }
    req.open('POST', options.voidAjaxUrl, true);
    req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    req.send('squad=' + encodeURI(squad) + '&action=void');
  });

  let modal = new bootstrap.Modal(document.getElementById('revokeModal'));
  modal.show();
});