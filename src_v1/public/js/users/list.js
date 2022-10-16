const originalTitle = document.title;

function getResult() {
  let search = document.getElementById('search');
  let output = document.getElementById('output');
  let searchValue = search.value;

  console.log(output.dataset.page + '?search=' + encodeURIComponent(searchValue));

  // Change query string
  if (searchValue.length > 0) {
    window.history.replaceState('string', 'Title', output.dataset.page + '?search=' + encodeURIComponent(searchValue));
    document.title = '"' + searchValue.trim() + '" - ' + originalTitle;
  } else {
    window.history.replaceState('string', 'Title', output.dataset.page);
    document.title = originalTitle;
  }

  // Make ajax request
  let xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      document.getElementById('output').innerHTML = this.responseText;
    }
  }
  xhttp.open('POST', output.dataset.ajaxUrl, true);
  xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhttp.send('search=' + searchValue);
}
// Call getResult immediately
getResult();

document.getElementById('search').oninput=getResult;