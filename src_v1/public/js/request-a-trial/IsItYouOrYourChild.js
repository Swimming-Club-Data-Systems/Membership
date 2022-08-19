/**
 * IsItYouOrYourChild.js
 *
 * A simple script which handles user input (a selection by the user who presses
 * a button) and rearranges the form layout/content to suit.
 */

var optionBox = document.getElementById('apply-for-option');
optionBox.addEventListener('click', handleOptionSelection);

function handleOptionSelection(event) {
  var button = event.explicitOriginalTarget;
  if (button.nodeName = "BUTTON") {
    var begin = document.getElementById('begin');
    begin.innerHTML = '<h2>Thanks for letting us know</h2><p>Letting us know helps us make this process simpler.</p>';


    if (button.value == 'myself') {
      var hide = document.getElementsByClassName('hide-if-parent');
      for (var i = 0; i < hide.length; i++) {
        hide[i].classList.add('d-none');
      }
      begin.innerHTML = begin.innerHTML + '<input type="hidden" name="is-parent" value="1">';
      document.getElementById('swimmer-forename').removeAttribute('required');
      document.getElementById('swimmer-surname').removeAttribute('required');
    } else if (button.value == 'minor') {
      var hide = document.getElementsByClassName('hide-if-minor');
      for (var i = 0; i < hide.length; i++) {
        hide[i].classList.add('d-none');
      }
    }
    console.log(button.value);
  }
}
