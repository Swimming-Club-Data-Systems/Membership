function autoGrow(element) {
  element.style.height = element.scrollHeight+"px";
}

function toggleState(id, radio, state) {
	var element = document.getElementById(id);
  var radios = document.getElementsByName(radio);

  for (var i = 0; i < radios.length; i++) {
    if (radios[i].checked) {
      if (radios[i].value == 1) {
        element.disabled = false;
      } else {
        element.disabled = true;
      }

    	if (element.disabled) {
    		element.value = "";
    	}

      break;
    }
  }
}

autoGrow(document.getElementById('medConDisDetails'));
autoGrow(document.getElementById('allergiesDetails'));
autoGrow(document.getElementById('medicineDetails'));
