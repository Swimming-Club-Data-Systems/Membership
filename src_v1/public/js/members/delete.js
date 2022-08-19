let button = document.getElementById('delete-button');

button.addEventListener('click', function (event) {
  document.getElementById('main-modal-title').textContent = 'Delete ' + button.dataset.memberName;

  let body = '<p>By deleting this member, we will remove all personal information. Some information will be retained for the completeness of your club\'s records.</p>';

  body = body + '<p>Deleting this member will have no impact on any linked users such as parents, though they will be removed from such accounts.</p>';

  // body = body + '<p>We will send a confirmation email to yourself and the user you are deleting when we have successfully deleted their information.</p>';

  body = body + '<div class="form-group mb-0"><label for="confirm-password">Confirm your password to proceed</label><input autocomplete="current-password" type="password" class="form-control" id="confirm-password" aria-describedby="pwHelp"><small id="pwHelp" class="form-text text-muted">This is an added security measure. Deleting a member is not reversible.</small></div>';

  document.getElementById('main-modal-body').innerHTML = body;

  document.getElementById('main-modal-footer').innerHTML = '<button type="button" class="btn btn-dark" data-bs-dismiss="modal">Cancel</button><button type="button" id="modal-confirm-button" class="btn btn-danger">Delete account</button>';

  document.getElementById('modal-confirm-button').addEventListener('click', function (event) {
    let button = document.getElementById('delete-button');
    document.getElementById('modal-confirm-button').disabled = true;
    let password = document.getElementById('confirm-password').value;
    let removeableAlert = document.getElementById('main-modal-alert');
    if (removeableAlert) {
      removeableAlert.outerHTML = '';
    }
    let oldContent = document.getElementById('main-modal-body').innerHTML;
    document.getElementById('main-modal-body').innerHTML = '<div class="py-5 text-center"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div></div>';

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
      if (this.readyState == 4) {
        var json = JSON.parse(this.responseText);
        if (json.status == 500) {
          // Failed
          document.getElementById('main-modal-body').innerHTML = '<div class="alert alert-danger" id="main-modal-alert"><p class="mb-0"><strong>An error occurred.</strong></p><p id="alert-text" class="mb-0"></p></div>' + oldContent;
          document.getElementById('modal-confirm-button').disabled = false;
        } else {
          // Success
          document.getElementById('main-modal-body').innerHTML = '<div class="alert alert-success mb-0"><p class="mb-0"><strong>Success.</strong></p><p id="alert-text" class="mb-0"></p></div>';
          document.getElementById('main-modal-footer').innerHTML = '<a id="close-link" href="" class="btn btn-dark">Close</a>';
          document.getElementById('close-link').href = document.getElementById('delete-button').dataset.membersUrl;
        }
        document.getElementById('alert-text').textContent = json.message;
      }
    }
    xhttp.open('POST', button.dataset.ajaxUrl, true);
    xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhttp.send('member=' + button.dataset.memberId + '&password=' + password);
  });

  let modal = new bootstrap.Modal(document.getElementById('main-modal'));
  modal.show();
});