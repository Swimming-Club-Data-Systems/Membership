const appInfo = document.getElementById('app-js-info');

// if ('serviceWorker' in navigator) {
//   navigator.serviceWorker.register(appInfo.dataset.serviceWorkerUrl);
// }

// var hidden, visibilityChange;
// if (typeof document.hidden !== 'undefined') { // Opera 12.10 and Firefox 18 and later support 
//   hidden = 'hidden';
//   visibilityChange = 'visibilitychange';
// } else if (typeof document.msHidden !== 'undefined') {
//   hidden = 'msHidden';
//   visibilityChange = 'msvisibilitychange';
// } else if (typeof document.webkitHidden !== 'undefined') {
//   hidden = 'webkitHidden';
//   visibilityChange = 'webkitvisibilitychange';
// }

// function handleVisibilityChange() {
//   if (document[hidden]) {
//     // Do nothing
//   } else {
//     // Check still signed in when user returns
//     let xhttp = new XMLHttpRequest();
//     xhttp.onreadystatechange = function () {
//       if (this.readyState == 4 && this.status == 200) {
//         let status = JSON.parse(this.responseText);
//         if (!status.signed_in) {
//           window.location.reload(true);
//         }
//       }
//     }
//     xhttp.open('POST', appInfo.dataset.checkLoginUrl, true);
//     xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
//     xhttp.send(JSON.stringify({}));
//   }
// }

// document.addEventListener(visibilityChange, handleVisibilityChange, false);


// document.querySelector('#show').addEventListener('click', () => {
//   const iconUrl = document.querySelector('select').selectedOptions[0].value;
//   let imgElement = document.createElement('img');
//   imgElement.src = iconUrl;
//   document.querySelector('#container').appendChild(imgElement);
// });