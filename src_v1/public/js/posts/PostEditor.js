function autoGrow(element) {
  element.style.height = (element.scrollHeight)+"px";
}

autoGrow(document.getElementById('content'));
