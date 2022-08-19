
function getResult() {
  var squad = document.getElementById('squad');
  var squadValue = squad.options[squad.selectedIndex].value;
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        document.getElementById('output').value = this.responseText;
      }
    }
    xhttp.open('POST', document.getElementById('copyButton').dataset.ajaxUrl, true);
    xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhttp.send('squadID=' + squadValue);
    // console.log('Sent');
}
// Call getResult immediately
getResult();

function copyToClipboard(elem) {
  // create hidden text element, if it doesn't already exist
  var targetId = 'output';
  var isInput = elem.tagName === 'INPUT' || elem.tagName === 'TEXTAREA';
  var origSelectionStart, origSelectionEnd;
  if (isInput) {
      // can just use the original source element for the selection and copy
      target = elem;
      origSelectionStart = elem.selectionStart;
      origSelectionEnd = elem.selectionEnd;
  } else {
    // must use a temporary form element for the selection and copy
    target = document.getElementById(targetId);
    if (!target) {
      var target = document.createElement('textarea');
      target.style.position = 'absolute';
      target.style.left = '-9999px';
      target.style.top = '0';
      target.id = targetId;
      document.body.appendChild(target);
    }
    target.textContent = elem.textContent;
  }
  // select the content
  var currentFocus = document.activeElement;
  target.focus();
  target.setSelectionRange(0, target.value.length);

  // copy the selection
  var succeed;
  try {
	  succeed = document.execCommand('copy');
  } catch(e) {
    succeed = false;
  }
  // restore original focus
  if (currentFocus && typeof currentFocus.focus === 'function') {
    currentFocus.focus();
  }

  if (isInput) {
    // restore prior selection
    elem.setSelectionRange(origSelectionStart, origSelectionEnd);
  } else {
    // clear temporary content
    target.textContent = '';
  }
  return succeed;
}

document.getElementById('squad').onchange=getResult;
document.getElementById('copyButton').addEventListener('click', function() {
  copyToClipboard(document.getElementById('output'));
});