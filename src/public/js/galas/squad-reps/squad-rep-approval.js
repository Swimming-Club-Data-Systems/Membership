const selectMenu = document.getElementById('squad-select');
const windowTitle = document.title;

selectMenu.addEventListener('change', function(event) {
  let squad = selectMenu.value;
  let gala = selectMenu.dataset.galaId;
  console.log(selectMenu);
  if (squad !== null) {
    // Redirect to new page
    window.location.href = selectMenu.dataset.page + '/galas/' + gala + '/squad-rep-view?squad=' + squad;
    // document.title = 'Test - ' + windowTitle;
  } else {
    // Redirect to new page
    window.location.href = selectMenu.dataset.page + '/galas/' + gala + '/squad-rep-view';
    // document.title = 'Test - ' + windowTitle;
  }
});

function ajaxUpdate(event, entryId, state) {
  let xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      console.log(this.responseText);
    }
  };
  xhttp.open('POST', selectMenu.dataset.page, true);
  xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhttp.send(encodeURI('entry=' + entryId + '&state=' + state + '&event=' + event));
}

document.getElementById('entries-list').addEventListener("click", clickPropogation, false);

function clickPropogation(e) {
  if (e.target !== e.currentTarget) {
    let clickedItem = e.target.id;
    let clickedItemChecked;
    if (clickedItem != "") {
      let item = document.getElementById(clickedItem);
      let clickedItemChecked = item.checked;
      let entryId = item.dataset.entryId;
      if (item.dataset.ajaxAction == 'mark-paid') {
        ajaxUpdate('mark-paid', entryId, clickedItemChecked);
      } else if (item.dataset.ajaxAction == 'approve-entry') {
        ajaxUpdate('approve-entry', entryId, clickedItemChecked);
      }
    }
  }
  e.stopPropagation();
}