/**
 * JS Code for Notify Email Composer
 * 
 * Primary focus on file uploads
 */

/**
 * Included for ease
 */

/*!
 * bsCustomFileInput v1.3.2 (https://github.com/Johann-S/bs-custom-file-input)
 * Copyright 2018 - 2019 Johann-S <johann.servoire@gmail.com>
 * Licensed under MIT (https://github.com/Johann-S/bs-custom-file-input/blob/master/LICENSE)
 */
!function(e,t){"object"==typeof exports&&"undefined"!=typeof module?module.exports=t():"function"==typeof define&&define.amd?define(t):(e=e||self).bsCustomFileInput=t()}(this,function(){"use strict";var d={CUSTOMFILE:'.custom-file input[type="file"]',CUSTOMFILELABEL:".custom-file-label",FORM:"form",INPUT:"input"},r=function(e){if(0<e.childNodes.length)for(var t=[].slice.call(e.childNodes),n=0;n<t.length;n++){var r=t[n];if(3!==r.nodeType)return r}return e},u=function(e){var t=e.bsCustomFileInput.defaultText,n=e.parentNode.querySelector(d.CUSTOMFILELABEL);n&&(r(n).innerHTML=t)},n=!!window.File,l=function(e){if(e.hasAttribute("multiple")&&n)return[].slice.call(e.files).map(function(e){return e.name}).join(", ");if(-1===e.value.indexOf("fakepath"))return e.value;var t=e.value.split("\\");return t[t.length-1]};function v(){var e=this.parentNode.querySelector(d.CUSTOMFILELABEL);if(e){var t=r(e),n=l(this);n.length?t.innerHTML=n:u(this)}}function p(){for(var e=[].slice.call(this.querySelectorAll(d.INPUT)).filter(function(e){return!!e.bsCustomFileInput}),t=0,n=e.length;t<n;t++)u(e[t])}var m="bsCustomFileInput",L="reset",h="change";return{init:function(e,t){void 0===e&&(e=d.CUSTOMFILE),void 0===t&&(t=d.FORM);for(var n,r,l,i=[].slice.call(document.querySelectorAll(e)),o=[].slice.call(document.querySelectorAll(t)),u=0,c=i.length;u<c;u++){var f=i[u];Object.defineProperty(f,m,{value:{defaultText:(n=f,r=void 0,void 0,r="",l=n.parentNode.querySelector(d.CUSTOMFILELABEL),l&&(r=l.innerHTML),r)},writable:!0}),v.call(f),f.addEventListener(h,v)}for(var a=0,s=o.length;a<s;a++)o[a].addEventListener(L,p),Object.defineProperty(o[a],m,{value:!0,writable:!0})},destroy:function(){for(var e=[].slice.call(document.querySelectorAll(d.FORM)).filter(function(e){return!!e.bsCustomFileInput}),t=[].slice.call(document.querySelectorAll(d.INPUT)).filter(function(e){return!!e.bsCustomFileInput}),n=0,r=t.length;n<r;n++){var l=t[n];u(l),l[m]=void 0,l.removeEventListener(h,v)}for(var i=0,o=e.length;i<o;i++)e[i].removeEventListener(L,p),e[i][m]=void 0}}});


/**
 * Print file details on change
 * @param {Event} event 
 */
function printFileDetails(event) {
  // Set a default max file size
  var maxFileSize = 1024 * 1024; // 1 megabyte
  if (this.dataset.maxFileSize) {
    var maxFileSize = parseInt(this.dataset.maxFileSize);
  }

  // Set a default max total file size
  var maxTotalFileSize = 1024 * 1024; // 1 megabyte
  if (this.dataset.maxTotalFileSize) {
    var maxTotalFileSize = parseInt(this.dataset.maxTotalFileSize);
  }

  // Total file size
  var totalFileSize = 0;

  // Array of files too large
  var tooLarge = [];

  if (this.files) {
    for (let i = 0; i < this.files.length; i++) {
      totalFileSize += this.files[i].size;
      var maxFileSizeOK = this.files[i].size <= maxFileSize;
      var maxTotalFileSizeOK = totalFileSize <= maxTotalFileSize;

      if (!maxFileSizeOK) {
        tooLarge.push({
          'name': this.files[i].name,
          'size': this.files[i].size,
        });
      }

      // console.log({
      //   'fileSize': this.files[i].size,
      //   'maxFileSize': maxFileSize,
      //   'maxTotalFileSize': maxTotalFileSize,
      //   'totalFileSize': totalFileSize,
      //   'maxFileSizeOK': maxFileSizeOK,
      //   'maxTotalFileSizeOK': maxTotalFileSizeOK,
      //   'mime': this.files[i].type,
      //   'lastModified': this.files[i].lastModified,
      // });
    }
  }

  var maxTotalFileSizeOK = totalFileSize <= maxTotalFileSize;

  if (maxTotalFileSizeOK && tooLarge.length == 0) {
    this.setCustomValidity('');
  } else {
    this.setCustomValidity('Files too large');
    if (this.dataset.errorMessageId) {
      var errorMessage = 'Files are too large or non-compliant';
      var errorContainer = document.getElementById(this.dataset.errorMessageId);

      // Custom error messages
      if (tooLarge.length > 0) {
        var needsComma = false;
        errorMessage = 'The following files are too large:';
        tooLarge.forEach(file => {
          if (needsComma) {
            errorMessage += ', '
          }
          needsComma = true;
          errorMessage += file.name;
        });
        errorMessage += '.';

        if (!maxTotalFileSizeOK) {
          errorMessage += ' The files also collectively exceed the maximum size for attachments.';
        }

      } else {
        errorMessage = 'The files you are trying to upload are collectively bigger than the maximum size allowed.'
      }

      errorContainer.textContent = errorMessage;
    }
  }
}

// On load
document.addEventListener('DOMContentLoaded', function (event) {
  // Get all file input elements (in case more than one)
  var inputs = document.querySelectorAll('input[type=file]');

  for (let i = 0; i < inputs.length; i++) {
    const fileUploadElement = inputs[i];
    fileUploadElement.addEventListener('change', printFileDetails);
  }

  // Fetch all the forms we want to apply custom Bootstrap validation styles to
  var form = document.getElementById('logo-form');
  form.addEventListener('submit', function(event) {
    if (form.checkValidity() === false) {
      event.preventDefault();
      event.stopPropagation();
    }
    form.classList.add('was-validated');
  }, false);

  // Init bootstrap custom file element code
  bsCustomFileInput.init();
  
});