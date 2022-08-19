function clearOutput() {
  enableBtn();
  document.getElementById("output").innerHTML = '<div class="ajaxPlaceholder">Select a swimmer and gala</div>';
}
      
function enableBtn() {
  var swimmer = document.getElementById("swimmer");
  var gala = document.getElementById("gala");
  if (swimmer.value != "null" && gala.value != "null") {
    document.getElementById("submit").disabled = false;
  } else {
    document.getElementById("submit").disabled = true;
  }
}

function getResult() {
  var gala = document.getElementById("gala");
  var swimmer = document.getElementById("swimmer");
  var swimmerValue = swimmer.value;
  var galaValue = gala.options[gala.selectedIndex].value;
  
  if (swimmerValue == "null" || galaValue == "null") {
    clearOutput();
  } else {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        document.getElementById("output").innerHTML = this.responseText;
        setupSumming();
        enableBtn();
      }
    }
    var ajaxRequest = "<?=autoUrl('galas/ajax/entryForm')?>?galaID=" + galaValue + "&swimmer=" + swimmerValue;
    xmlhttp.open("GET", ajaxRequest, true);
    xmlhttp.send();
  }
}

document.getElementById('submit').disabled = true;
var swimmer = document.getElementById('swimmer');
var gala = document.getElementById('gala');

swimmer.addEventListener('change', getResult);
gala.addEventListener('change', getResult);