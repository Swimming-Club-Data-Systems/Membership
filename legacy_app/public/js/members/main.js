/**
 * Code for member view pages
 * 
 * Currently handles;
 * - Squad moves
 */

// Enable popovers
document.addEventListener('DOMContentLoaded', function () {
  // your code here
  let popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
  let popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl)
  });
}, false);

async function movesHandler(event) {
  if (event.target.dataset.action) {
    // Delete the squad move via ajax request
    var fd = new FormData();
    fd.append('move', event.target.dataset.moveId);
    fd.append('operation', event.target.dataset.action);
    var req = new XMLHttpRequest();
    req.addEventListener('load', async (event) => {
      if (event.target.status == 200) {
        let result = JSON.parse(event.target.responseText);
        if (result.success) {
          displaySquads();
        } else {
          // TEMPORARY error message
          window.alert(result.error_message);
        }
      } else {
      }
    });
    req.addEventListener('error', (event) => {
      // Error
    });
    req.addEventListener('abort', (event) => {
      // Error
    });
    req.open('POST', document.getElementById('squad-moves-area').dataset.operationsUrl);
    req.send(fd);
  }
}

async function displaySquads() {
  try {
    let squads = await getSquads();
    let memberLeadDesc = '';
    let squadDiv = document.createElement('DIV');
    let squadList = document.createElement('DIV');
    squadList.classList.add('list-group', 'mb-3');
    let numSquads = 0;
    squads.current.forEach(squad => {
      numSquads += 1;
      let listItem = document.createElement('A');
      listItem.classList.add('list-group-item', 'list-group-item-action');
      listItem.href = squad.url;
      listItem.appendChild(document.createTextNode(squad.name + ' '));
      let price = document.createElement('SPAN');
      let em = document.createElement('EM');
      console.log(squad);
      if (!squad.pays) {
        let strike = document.createElement('S');
        strike.textContent = '£' + squad.price_string;
        em.appendChild(strike);
        em.appendChild(document.createTextNode(' £0 - Does not pay'));
      } else {
        em.textContent = '£' + squad.price_string;
      }
      price.appendChild(em);
      listItem.appendChild(price);
      squadList.appendChild(listItem);
      if (numSquads > 1) memberLeadDesc += ', ';
      memberLeadDesc += squad.name;
    });

    if (numSquads > 0) {
      let squadsDesc = document.createElement('P');
      let s = '';
      if (numSquads > 1) {
        s = 's';
        document.getElementById('squads').textContent = 'Squads';
      } else {
        document.getElementById('squads').textContent = 'Squad';
      }
      squadsDesc.textContent = squads.squads_desc_line;
      squadDiv.appendChild(squadsDesc);
      squadDiv.appendChild(squadList);
      document.getElementById('leadDesc').textContent = memberLeadDesc + ' Squad' + s;
    } else {
      let noSquads = document.createElement('DIV');
      noSquads.classList.add('alert', 'alert-warning');
      noSquads.textContent = squads.squads_desc_line;
      squadDiv.appendChild(noSquads);
      document.getElementById('squads').textContent = 'Squads';
      document.getElementById('leadDesc').textContent = 'Member';
    }

    document.getElementById('squadDetails').innerHTML = '';
    document.getElementById('squadDetails').appendChild(squadDiv);

    // Display squad moves
    let movesArea = document.getElementById('squad-moves-area');
    movesArea.innerHTML = '';
    if (squads.moves.length > 0) {
      let title = document.createElement('H3');
      title.textContent = 'Squad moves'
      movesArea.appendChild(title);

      // Create list group
      let listGroup = document.createElement('UL');
      listGroup.classList.add('list-group', 'mb-3');

      squads.moves.forEach(move => {
        let item = document.createElement('LI');
        item.classList.add('list-group-item');

        let row = document.createElement('DIV');
        row.classList.add('row', 'align-items-center');

        let moveCol = document.createElement('DIV');
        moveCol.classList.add('col-sm-6');

        let moveDesc = document.createElement('DIV');

        if (move.from && move.to) {
          let oldSquad = document.createElement('A');
          oldSquad.href = move.from.url;
          oldSquad.textContent = move.from.name;

          let srText = document.createElement('SPAN');
          srText.classList.add('sr-only');
          srText.textContent = 'Moves from ';
          moveDesc.appendChild(srText);

          moveDesc.appendChild(oldSquad);

          srText = document.createElement('SPAN');
          srText.classList.add('sr-only');
          srText.textContent = ' to ';
          moveDesc.appendChild(srText);

          let arrow = document.createElement('I');
          arrow.classList.add('fa', 'fa-long-arrow-right');
          arrow.setAttribute('aria-hidden', 'true');
          srText = document.createElement('SPAN');
          srText.classList.add('sr-only');
          srText.textContent = 'to';
          moveDesc.appendChild(document.createTextNode(' '));
          moveDesc.appendChild(arrow);
          moveDesc.appendChild(srText);
          moveDesc.appendChild(document.createTextNode(' '));

          let newSquad = document.createElement('A');
          newSquad.href = move.to.url;
          newSquad.textContent = move.to.name;

          moveDesc.appendChild(newSquad);

          if (!move.paying) {
            moveDesc.appendChild(document.createTextNode(' (not paying) '));
          }

          moveCol.appendChild(moveDesc);
        } else if (move.to) {
          let newSquad = document.createElement('A');
          newSquad.href = move.to.url;
          newSquad.textContent = move.to.name;

          moveDesc.appendChild(document.createTextNode('Joins '));

          moveDesc.appendChild(newSquad);

          if (!move.paying) {
            moveDesc.appendChild(document.createTextNode(' (not paying) '));
          }

          moveCol.appendChild(moveDesc);
        } else if (move.from) {
          let oldSquad = document.createElement('A');
          oldSquad.href = move.from.url;
          oldSquad.textContent = move.from.name;

          moveDesc.appendChild(document.createTextNode('Leaves '));

          moveDesc.appendChild(oldSquad);
          moveCol.appendChild(moveDesc);
        }

        // Date
        let date = new Date(move.date);
        moveDesc.appendChild(document.createTextNode(' on '));
        moveDesc.appendChild(document.createTextNode(date.toLocaleDateString(undefined, { weekday: 'short', year: 'numeric', month: 'long', day: 'numeric' })));

        row.appendChild(moveCol);

        let optCol = document.createElement('DIV');
        optCol.classList.add('col');

        let cancelButton = document.createElement('BUTTON');
        cancelButton.classList.add('btn', 'btn-danger', 'btn-block');
        cancelButton.textContent = 'Cancel move';
        cancelButton.dataset.action = 'delete';
        cancelButton.dataset.moveId = move.id;

        optCol.appendChild(cancelButton);

        if (movesArea.dataset.showOptions == 'true') {
          row.appendChild(optCol);
        }

        item.appendChild(row);
        listGroup.appendChild(item);
      });

      movesArea.appendChild(listGroup);
      movesArea.addEventListener('click', movesHandler);
    }

  } catch (err) {
    console.error(err);
    document.getElementById('squadDetails').innerHTML = '<div class="alert alert-warning">We couldn\'t load the member\'s squads</div>';
  }
}

const pleaseWait = '<div class="cell text-center mb-0"><p class="h2">Loading</p><p class="mb-0">Please wait</p></div>';

function handleNewMove(event) {
  let member = event.target.dataset.member;
  getSquads()
    .then((squads) => {
      let title = 'New Move';
      let body = '<p>What type of move do you want to create?</p>';
      body += '<p class="d-grid"><button class="btn btn-primary" id="squad-to-squad">Squad to squad</button></p>';
      body += '<p class="d-grid"><button class="btn btn-primary" id="join-squad">Join a squad</button></p>';
      body += '<p class="d-grid"><button class="btn btn-primary" id="leave-squad">Leave a squad</button></p>';

      body += '<p>Usually, you\'ll want to use <strong>squad to squad</strong> moves, where a member will move from one squad to another. <strong>Join a squad</strong> means a member will join a squad (in addition to all existing squads) and <strong>leave a squad</strong> allows you to remove a member from a specific squad.</p>';

      body += '<p class="mb-0">Squad moves can be applied now or be scheduled to take place at any later date, such as the start of next month.</p>';

      document.getElementById('modal-title').textContent = title;
      document.getElementById('modal-body').innerHTML = body;

      let squadToSquadBtn = document.getElementById('squad-to-squad');
      let joinSquadBtn = document.getElementById('join-squad');
      let leaveSquadBtn = document.getElementById('leave-squad');

      if (squadToSquadBtn) {
        squadToSquadBtn.addEventListener('click', squadToSquad);
        if (squads.current.length == 0 || squads.can_join.length == 0) {
          squadToSquadBtn.disabled = true;
        }
      }

      if (joinSquadBtn) {
        joinSquadBtn.addEventListener('click', joinSquad);
        if (squads.can_join.length == 0) {
          joinSquadBtn.disabled = true;
        }
      }

      if (leaveSquadBtn) {
        leaveSquadBtn.addEventListener('click', leaveSquad);
        if (squads.current.length == 0) {
          leaveSquadBtn.disabled = true;
        }
      }

      let modal = new bootstrap.Modal(document.getElementById('modal'));
      modal.show();
    })
    .catch((error) => {
      console.warn(error.message);
      showGetSquadsFailure();
      let modal = new bootstrap.Modal(document.getElementById('modal'));
      modal.show();
    })
}

function squadToSquad(event) {
  let member = document.getElementById('new-move-button').dataset.member;
  let title = 'Squad to Squad Move';

  document.getElementById('modal-title').textContent = title;
  document.getElementById('modal-body').innerHTML = pleaseWait;

  // Fetch current squads and other squads
  getSquads()
    .then((squads) => {

      let body = document.getElementById('modal-body');

      body.textContent = '';

      body.appendChild(document.createElement('P').appendChild(document.createTextNode('Please choose a squad and role for the user.')));

      let form = document.createElement('FORM');

      let fg = document.createElement('DIV');
      fg.classList.add('mb-3');

      let label = document.createElement('LABEL');
      label.classList.add('form-label');
      label.appendChild(document.createTextNode('Leaving'));
      label.htmlFor = 'leave';

      let select = document.createElement('SELECT');
      select.classList.add('form-select');
      select.id = 'leave';
      select.name = 'leave';

      // Loop and add <option>s
      squads.current.forEach(squad => {
        let option = document.createElement('OPTION');
        option.appendChild(document.createTextNode(squad.name));
        option.value = squad.id;
        select.appendChild(option);
      });

      fg.appendChild(label);
      fg.appendChild(select);

      form.appendChild(fg);

      fg = document.createElement('DIV');
      fg.classList.add('mb-3');

      label = document.createElement('LABEL');
      label.classList.add('form-label');
      label.appendChild(document.createTextNode('Joining'));
      label.htmlFor = 'join';

      select = document.createElement('SELECT');
      select.classList.add('form-select');
      select.id = 'join';
      select.name = 'join';

      // Loop and add <option>s
      squads.can_join.forEach(squad => {
        let option = document.createElement('OPTION');
        option.appendChild(document.createTextNode(squad.name));
        option.value = squad.id;
        select.appendChild(option);
      });

      fg.appendChild(label);
      fg.appendChild(select);

      form.appendChild(fg);

      fg = document.createElement('DIV');
      fg.classList.add('mb-3');

      let customRadio = document.createElement('DIV');
      customRadio.classList.add('form-check');

      let radio = document.createElement('input');
      radio.type = 'radio';
      radio.value = '0';
      radio.checked = true;
      radio.id = 'move-when-1';
      radio.name = 'move-when'
      radio.classList.add('form-check-input');

      label = document.createElement('label');
      label.appendChild(document.createTextNode('Move now'));
      label.classList.add('form-check-label');
      label.htmlFor = 'move-when-1';

      customRadio.appendChild(radio);
      customRadio.appendChild(label);

      fg.appendChild(customRadio);

      customRadio = document.createElement('DIV');
      customRadio.classList.add('form-check');

      radio = document.createElement('input');
      radio.type = 'radio';
      radio.value = '1';
      radio.id = 'move-when-2';
      radio.name = 'move-when'
      radio.classList.add('form-check-input');

      label = document.createElement('label');
      label.appendChild(document.createTextNode('Move on a specified date'));
      label.classList.add('form-check-label');
      label.htmlFor = 'move-when-2';

      customRadio.appendChild(radio);
      customRadio.appendChild(label);

      fg.appendChild(customRadio);

      form.appendChild(fg);

      fg = document.createElement('DIV');
      fg.classList.add('mb-3', 'collapse');
      fg.id = 'date-group'

      label = document.createElement('label');
      label.classList.add('form-label');
      label.appendChild(document.createTextNode('Date of move'));
      label.htmlFor = 'move-date';

      let date = document.createElement('input');
      date.type = 'date';
      today = new Date().toISOString().split('T')[0];
      date.min = today;
      date.value = today;
      date.id = 'move-date';
      date.name = 'move-date';
      date.classList.add('form-control');

      fg.appendChild(label);
      fg.appendChild(date);

      form.appendChild(fg);

      /*
       * Is paying
       */
      fg = document.createElement('DIV');
      fg.classList.add('mb-3');

      customRadio = document.createElement('DIV');
      customRadio.classList.add('form-check');

      radio = document.createElement('input');
      radio.type = 'radio';
      radio.value = '0';
      radio.checked = true;
      radio.id = 'paying-yes';
      radio.name = 'paying'
      radio.classList.add('form-check-input');

      label = document.createElement('label');
      label.appendChild(document.createTextNode('Will pay fees for this squad'));
      label.classList.add('form-check-label');
      label.htmlFor = 'paying-yes';

      customRadio.appendChild(radio);
      customRadio.appendChild(label);

      fg.appendChild(customRadio);

      customRadio = document.createElement('DIV');
      customRadio.classList.add('form-check');

      radio = document.createElement('input');
      radio.type = 'radio';
      radio.value = '1';
      radio.id = 'paying-no';
      radio.name = 'paying'
      radio.classList.add('form-check-input');

      label = document.createElement('label');
      label.appendChild(document.createTextNode('Won\'t pay fees for this squad'));
      label.classList.add('form-check-label');
      label.htmlFor = 'paying-no';

      customRadio.appendChild(radio);
      customRadio.appendChild(label);

      fg.appendChild(customRadio);

      form.appendChild(fg);

      fg = document.createElement('P');
      let submit = document.createElement('BUTTON');
      submit.id = 'move-submit';
      submit.classList.add('btn', 'btn-primary');
      submit.textContent = 'Save';

      fg.appendChild(submit);
      form.appendChild(fg);

      body.appendChild(form);

      form.addEventListener('submit', (event) => {
        event.preventDefault();
        let fd = new FormData(event.target);
        let button = document.getElementById('new-move-button');
        fd.append('member', button.dataset.member);
        fd.append('event', 'move');
        fd.append('SCDS-GLOBAL-CSRF', button.dataset.csrf);

        // Send form data ajax
        var req = new XMLHttpRequest();
        req.addEventListener('load', async (event) => {
          if (event.target.status == 200) {
            let result = JSON.parse(event.target.responseText);
            if (result.success) {
              body.innerHTML = '<div class="alert alert-success"><p class="mb-0"><strong>Success</strong></p><p class="mb-0" id="move-result-success-message"></p></div>';
              document.getElementById('move-result-success-message').textContent = result.message;
              displaySquads();
            } else {
              body.innerHTML = '<div class="alert alert-warning"><p class="mb-0"><strong>A problem occurred</strong></p><p class="mb-0" id="errorMessage"></p></div>';
              if (result.error) {
                document.getElementById('errorMessage').textContent = result.error;
              } else {
                document.getElementById('errorMessage').textContent = 'Unknown error';
              }
            }
          } else {
          }
        });
        req.addEventListener('error', (event) => {
          // Error
        });
        req.addEventListener('abort', (event) => {
          // Error
        });
        req.open('POST', button.dataset.moveUrl);
        req.send(fd);
        body.innerHTML = '<div class="cell text-center"><strong>Loading</strong></div>';
      });

      document.querySelectorAll('input[name="move-when"]').forEach((radio) => {
        radio.addEventListener('change', showHideDateGroup);
      });

    })
    .catch((error) => {
      console.warn(error.message);
      showGetSquadsFailure();
    })
}

function joinSquad(event) {
  let member = document.getElementById('new-move-button').dataset.member;
  let title = 'Join a new squad';

  document.getElementById('modal-title').textContent = title;
  document.getElementById('modal-body').innerHTML = pleaseWait;

  // Fetch current squads and other squads
  getSquads()
    .then((squads) => {
      let body = document.getElementById('modal-body');

      body.textContent = '';

      let p = document.createElement('P');
      p.textContent = 'Please choose a squad to join.';
      body.appendChild(p);

      let form = document.createElement('FORM');

      let fg = document.createElement('DIV');
      fg.classList.add('mb-3');

      let label = document.createElement('LABEL');
      label.appendChild(document.createTextNode('Join'));
      label.htmlFor = 'join';

      let select = document.createElement('SELECT');
      select.classList.add('form-select');
      select.id = 'join';
      select.name = 'join';

      // Loop and add <option>s
      squads.can_join.forEach(squad => {
        let option = document.createElement('OPTION');
        option.appendChild(document.createTextNode(squad.name));
        option.value = squad.id;
        select.appendChild(option);
      });

      fg.appendChild(label);
      fg.appendChild(select);

      form.appendChild(fg);

      fg = document.createElement('DIV');
      fg.classList.add('mb-3');

      let customRadio = document.createElement('DIV');
      customRadio.classList.add('form-check');

      let radio = document.createElement('input');
      radio.type = 'radio';
      radio.value = '0';
      radio.checked = true;
      radio.id = 'move-when-1';
      radio.name = 'move-when'
      radio.classList.add('form-check-input');

      label = document.createElement('label');
      label.appendChild(document.createTextNode('Move now'));
      label.classList.add('form-check-label');
      label.htmlFor = 'move-when-1';

      customRadio.appendChild(radio);
      customRadio.appendChild(label);

      fg.appendChild(customRadio);

      customRadio = document.createElement('DIV');
      customRadio.classList.add('form-check');

      radio = document.createElement('input');
      radio.type = 'radio';
      radio.value = '1';
      radio.id = 'move-when-2';
      radio.name = 'move-when'
      radio.classList.add('form-check-input');

      label = document.createElement('label');
      label.appendChild(document.createTextNode('Move on a specified date'));
      label.classList.add('form-check-label');
      label.htmlFor = 'move-when-2';

      customRadio.appendChild(radio);
      customRadio.appendChild(label);

      fg.appendChild(customRadio);

      form.appendChild(fg);

      fg = document.createElement('DIV');
      fg.classList.add('mb-3', 'collapse');
      fg.id = 'date-group'

      label = document.createElement('label');
      label.appendChild(document.createTextNode('Date of move'));
      label.htmlFor = 'move-date';

      let date = document.createElement('input');
      date.type = 'date';
      today = new Date().toISOString().split('T')[0];
      date.min = today;
      date.value = today;
      date.id = 'move-date';
      date.name = 'move-date';
      date.classList.add('form-control');

      fg.appendChild(label);
      fg.appendChild(date);

      form.appendChild(fg);

      /*
       * Is paying
       */
      fg = document.createElement('DIV');
      fg.classList.add('mb-3');

      customRadio = document.createElement('DIV');
      customRadio.classList.add('form-check');

      radio = document.createElement('input');
      radio.type = 'radio';
      radio.value = '0';
      radio.checked = true;
      radio.id = 'paying-yes';
      radio.name = 'paying'
      radio.classList.add('form-check-input');

      label = document.createElement('label');
      label.appendChild(document.createTextNode('Pays fees for this squad'));
      label.classList.add('form-check-label');
      label.htmlFor = 'paying-yes';

      customRadio.appendChild(radio);
      customRadio.appendChild(label);

      fg.appendChild(customRadio);

      customRadio = document.createElement('DIV');
      customRadio.classList.add('form-check');

      radio = document.createElement('input');
      radio.type = 'radio';
      radio.value = '1';
      radio.id = 'paying-no';
      radio.name = 'paying'
      radio.classList.add('form-check-input');

      label = document.createElement('label');
      label.appendChild(document.createTextNode('Does not pay fees for this squad'));
      label.classList.add('form-check-label');
      label.htmlFor = 'paying-no';

      customRadio.appendChild(radio);
      customRadio.appendChild(label);

      fg.appendChild(customRadio);

      form.appendChild(fg);

      fg = document.createElement('P');
      let submit = document.createElement('BUTTON');
      submit.id = 'move-submit';
      submit.classList.add('btn', 'btn-primary');
      submit.textContent = 'Save';

      fg.appendChild(submit);
      form.appendChild(fg);

      // Display
      body.appendChild(form);

      form.addEventListener('submit', (event) => {
        event.preventDefault();
        let fd = new FormData(event.target);
        let button = document.getElementById('new-move-button');
        fd.append('member', button.dataset.member);
        fd.append('event', 'join');
        fd.append('SCDS-GLOBAL-CSRF', button.dataset.csrf);

        // Send form data ajax
        var req = new XMLHttpRequest();
        req.addEventListener('load', async (event) => {
          if (event.target.status == 200) {
            let result = JSON.parse(event.target.responseText);
            if (result.success) {
              body.innerHTML = '<div class="alert alert-success"><p class="mb-0"><strong>Success</strong></p><p class="mb-0" id="move-result-success-message"></p></div>';
              document.getElementById('move-result-success-message').textContent = result.message;
              displaySquads();
            } else {
              body.innerHTML = '<div class="alert alert-warning"><p class="mb-0"><strong>A problem occurred</strong></p><p class="mb-0" id="errorMessage"></p></div>';
              if (result.error) {
                document.getElementById('errorMessage').textContent = result.error;
              } else {
                document.getElementById('errorMessage').textContent = 'Unknown error';
              }
            }
          } else {
          }
        });
        req.addEventListener('error', (event) => {
          // Error
        });
        req.addEventListener('abort', (event) => {
          // Error
        });
        req.open('POST', button.dataset.moveUrl);
        req.send(fd);
        body.innerHTML = '<div class="cell text-center"><strong>Loading</strong></div>';
      });

      document.querySelectorAll('input[name="move-when"]').forEach((radio) => {
        radio.addEventListener('change', showHideDateGroup);
      });
    })
    .catch((error) => {
      console.warn(error.message);
      showGetSquadsFailure();
    })
}

/**
 * Handle a member leaving a squad, now or in future.
 * 
 * @param {Event} event 
 */
function leaveSquad(event) {
  let member = document.getElementById('new-move-button').dataset.member;
  let title = 'Leave a squad';

  document.getElementById('modal-title').textContent = title;
  document.getElementById('modal-body').innerHTML = pleaseWait;

  // Fetch current squads and other squads
  getSquads()
    .then((squads) => {
      let body = document.getElementById('modal-body');

      body.textContent = '';

      let p = document.createElement('P');
      p.textContent = 'Please choose a squad to leave.';
      body.appendChild(p);

      let form = document.createElement('FORM');

      let fg = document.createElement('DIV');
      fg.classList.add('mb-3');

      let label = document.createElement('LABEL');
      label.appendChild(document.createTextNode('Leaving'));
      label.htmlFor = 'leave';

      let select = document.createElement('SELECT');
      select.classList.add('form-select');
      select.id = 'leave';
      select.name = 'leave';

      // Loop and add <option>s
      squads.current.forEach(squad => {
        let option = document.createElement('OPTION');
        option.appendChild(document.createTextNode(squad.name));
        option.value = squad.id;
        select.appendChild(option);
      });

      fg.appendChild(label);
      fg.appendChild(select);

      form.appendChild(fg);

      fg = document.createElement('DIV');
      fg.classList.add('mb-3');

      let customRadio = document.createElement('DIV');
      customRadio.classList.add('form-check');

      let radio = document.createElement('input');
      radio.type = 'radio';
      radio.value = '0';
      radio.checked = true;
      radio.id = 'move-when-1';
      radio.name = 'move-when'
      radio.classList.add('form-check-input');

      label = document.createElement('label');
      label.appendChild(document.createTextNode('Leave now'));
      label.classList.add('form-check-label');
      label.htmlFor = 'move-when-1';

      customRadio.appendChild(radio);
      customRadio.appendChild(label);

      fg.appendChild(customRadio);

      customRadio = document.createElement('DIV');
      customRadio.classList.add('form-check');

      radio = document.createElement('input');
      radio.type = 'radio';
      radio.value = '1';
      radio.id = 'move-when-2';
      radio.name = 'move-when'
      radio.classList.add('form-check-input');

      label = document.createElement('label');
      label.appendChild(document.createTextNode('Leave on a specified date'));
      label.classList.add('form-check-label');
      label.htmlFor = 'move-when-2';

      customRadio.appendChild(radio);
      customRadio.appendChild(label);

      fg.appendChild(customRadio);

      form.appendChild(fg);

      fg = document.createElement('DIV');
      fg.classList.add('mb-3', 'collapse');
      fg.id = 'date-group'

      label = document.createElement('label');
      label.appendChild(document.createTextNode('Date of move'));
      label.htmlFor = 'move-date';

      let date = document.createElement('input');
      date.type = 'date';
      today = new Date().toISOString().split('T')[0];
      date.min = today;
      date.value = today;
      date.id = 'move-date';
      date.name = 'move-date';
      date.classList.add('form-control');

      fg.appendChild(label);
      fg.appendChild(date);

      form.appendChild(fg);

      fg = document.createElement('P');
      let submit = document.createElement('BUTTON');
      submit.id = 'move-submit';
      submit.classList.add('btn', 'btn-primary');
      submit.textContent = 'Save';

      fg.appendChild(submit);
      form.appendChild(fg);

      // Display
      body.appendChild(form);

      form.addEventListener('submit', (event) => {
        event.preventDefault();
        let fd = new FormData(event.target);
        let button = document.getElementById('new-move-button');
        fd.append('member', button.dataset.member);
        fd.append('event', 'leave');
        fd.append('SCDS-GLOBAL-CSRF', button.dataset.csrf);

        // Send form data ajax
        var req = new XMLHttpRequest();
        req.addEventListener('load', async (event) => {
          if (event.target.status == 200) {
            let result = JSON.parse(event.target.responseText);
            if (result.success) {
              body.innerHTML = '<div class="alert alert-success"><p class="mb-0"><strong>Success</strong></p><p class="mb-0" id="move-result-success-message"></p></div>';
              document.getElementById('move-result-success-message').textContent = result.message;
              displaySquads();
            } else {
              body.innerHTML = '<div class="alert alert-warning"><p class="mb-0"><strong>A problem occurred</strong></p><p class="mb-0" id="errorMessage"></p></div>';
              if (result.error) {
                document.getElementById('errorMessage').textContent = result.error;
              } else {
                document.getElementById('errorMessage').textContent = 'Unknown error';
              }
            }
          } else {
          }
        });
        req.addEventListener('error', (event) => {
          // Error
        });
        req.addEventListener('abort', (event) => {
          // Error
        });
        req.open('POST', button.dataset.moveUrl);
        req.send(fd);
        body.innerHTML = '<div class="alert alert-success">SENT</div>';
      });

      document.querySelectorAll('input[name="move-when"]').forEach((radio) => {
        radio.addEventListener('change', showHideDateGroup);
      });
    })
    .catch((error) => {
      console.warn(error.message);
      showGetSquadsFailure();
    })
}

/**
 * Get squads from the db and return a JSON object
 */
function getSquads() {
  return new Promise(function (resolve, reject) {
    var oReq = new XMLHttpRequest();
    oReq.addEventListener('load', (event) => {
      if (event.target.status == 200)
        resolve(JSON.parse(event.target.responseText));
      else
        reject(JSON.parse(event.target.responseText));
    });
    oReq.open('POST', document.getElementById('squads-data').dataset.squadsUrl);
    oReq.send();
  });
}

/**
 * Show or hide the move-date date input based on radio value
 * 
 * @param {Event} event 
 */
function showHideDateGroup(event) {
  if (document.querySelector('input[name="move-when"]:checked').value == true) {
    var myCollapse = document.getElementById('date-group');
    var bsCollapse = new bootstrap.Collapse(myCollapse, {
      show: true
    });
  } else {
    var myCollapse = document.getElementById('date-group');
    var bsCollapse = new bootstrap.Collapse(myCollapse, {
      hide: true
    });
  }
}

/**
 * Report an error when getting squads
 * 
 * @param {Event} event 
 */
function showGetSquadsFailure(event) {
  let title = 'Squad Moves - Error';
  let body = '<div class="alert alert-danger mb-0">';
  body += '<p class="mb-0"><strong>An error occurred while trying to gather the member\'s squads.</strong></p>';
  body += '<p class="mb-0">Please try again later.</p>';
  body += '</div>';

  document.getElementById('modal-title').textContent = title;
  document.getElementById('modal-body').innerHTML = pleaseWait;
}

let moveButton = document.getElementById('new-move-button');
if (moveButton) {
  moveButton.addEventListener('click', handleNewMove);
}
let moveDropdown = document.getElementById('new-move-dropdown');
if (moveDropdown) {
  moveDropdown.addEventListener('click', handleNewMove);
}

displaySquads();

/**
   * Get qualifications from the db and return a JSON object
   */
function getQualifications() {
  return new Promise(function (resolve, reject) {
    var oReq = new XMLHttpRequest();
    oReq.addEventListener('load', (event) => {
      if (event.target.status == 200)
        resolve(JSON.parse(event.target.responseText));
      else
        reject(JSON.parse(event.target.responseText));
    });
    oReq.open('GET', document.getElementById('qualifications-box').dataset.qualificationsUrl);
    oReq.send();
  });
}

async function displayQualifications() {
  try {
    let qualifications = await getQualifications();
    document.getElementById('qualifications-box').innerHTML = qualifications.html;
  } catch (error) {

  }
}

displayQualifications();