let attachments = JSON.parse(document.getElementById('email-attachments').value);
let dropzone = document.getElementById('upload-zone');
let canSubmit = true;

// TINY MCE

tinymce.init({
  skin: (window.matchMedia("(prefers-color-scheme: dark)").matches ? "oxide-dark" : ""),
  relative_urls: false,
  remove_script_host: false,
  document_base_url: document.getElementById('message').dataset.documentBaseUrl,
  selector: '#message',
  images_upload_url: document.getElementById('message').dataset.imagesUploadUrl,
  automatic_uploads: true,
  images_upload_credentials: true,
  branding: false,
  plugins: [
    'autolink lists link image charmap print preview anchor',
    'searchreplace visualblocks code autoresize insertdatetime media table',
    'paste help wordcount'
  ],
  statusbar: false,
  paste_as_text: true,
  toolbar: 'insert | undo redo |  formatselect | bold italic | bullist numlist outdent indent | removeformat | help',
  content_css: (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'default'),
  fontsize_formats: '12pt',
  font_formats: 'Default=system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Liberation Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji";',
  style_formats: [
    {
      title: 'Headings', items: [
        { title: 'Heading 1', format: 'h1' },
        { title: 'Heading 2', format: 'h2' },
        { title: 'Heading 3', format: 'h3' },
        { title: 'Heading 4', format: 'h4' },
        { title: 'Heading 5', format: 'h5' },
        { title: 'Heading 6', format: 'h6' }
      ]
    },
    {
      title: 'Inline', items: [
        { title: 'Bold', format: 'bold' },
        { title: 'Italic', format: 'italic' },
        { title: 'Underline', format: 'underline' },
        { title: 'Strikethrough', format: 'strikethrough' },
        { title: 'Superscript', format: 'superscript' },
        { title: 'Subscript', format: 'subscript' },
        { title: 'Code', format: 'code' }
      ]
    },
    {
      title: 'Blocks', items: [
        { title: 'Paragraph', format: 'p' },
        { title: 'Blockquote', format: 'blockquote' },
        { title: 'Div', format: 'div' },
        { title: 'Pre', format: 'pre' }
      ]
    }
  ],
  menu: {
    edit: { title: 'Edit', items: 'undo redo | cut copy paste | selectall | searchreplace' },
    view: { title: 'View', items: 'code | visualaid visualchars visualblocks | spellchecker | preview fullscreen' },
    insert: { title: 'Insert', items: 'image link template codesample inserttable | charmap emoticons hr | pagebreak nonbreaking anchor toc | insertdatetime' },
    format: { title: 'Format', items: 'bold italic underline strikethrough superscript subscript codeformat | formats blockformats | removeformat' },
    tools: { title: 'Tools', items: 'spellchecker spellcheckerlanguage | code wordcount' },
    table: { title: 'Table', items: 'inserttable | cell row column | tableprops deletetable' },
    help: { title: 'Help', items: 'help' }
  },

  //toolbar: "link",
});

window.addEventListener('keydown', function (e) {
  if (e.keyIdentifier == 'U+000A' || e.keyIdentifier == 'Enter' || e.keyCode == 13) {
    if (e.target.nodeName == 'INPUT' && e.target.type == 'text') {
      e.preventDefault(); return false;
    }
  }
}, true);

let force = document.getElementById('force');
if (force) {
  force.addEventListener('change', event => {
    if (force.checked) {
      force.checked = false;
      let modal = new bootstrap.Modal(document.getElementById('force-alert-modal'));
      modal.show();

      document.getElementById('accept').addEventListener('click', event => {
        modal.hide();
        if (force) {
          force.checked = true;
        }
      })
    }
  })
}

document.getElementById('tinymce-preview').addEventListener('click', ev => {
  tinymce.activeEditor.execCommand('mcePreview');
});

// DROPZONE

let myDropzone = new Dropzone("div#upload-zone", {
  url: dropzone.dataset.action,
  previewsContainer: '#upload-previews',
  // addRemoveLinks: true,
  filesizeBase: 1024,
  maxFilesize: dropzone.dataset.maxTotalFileSize,
  params: {
    notify_uuid: dropzone.dataset.uuid,
    notify_date: dropzone.dataset.date
  },
  previewTemplate: `\
<div class="col-6 col-md-4 col-lg-3 mb-2">
  <div class="border rounded p-2 text-center h-100">
    <div class="dz-preview dz-file-preview">
      <div class="dz-image"><i class="fa fa-file fa-3x" aria-hidden="true"></i></div>
      <div class="dz-details">
        <div class="align-items-center text-truncate"><span class="dz-success-mark"><i class="fa fa-check-circle text-success" aria-hidden="true"></i></span><span class="dz-error-mark"><i class="fa fa-times-circle text-danger" aria-hidden="true"></i></span> <span class="dz-filename"><span data-dz-name></span></span></div>
        <div class="align-items-center dz-size mb-2"><span data-dz-size></span> <button class="btn btn-sm btn-danger" alt="Remove file" data-dz-remove><i class="fa fa-trash" aria-hidden="true"></i></button></div>
      </div>
      <div class="progress">
        <div class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" data-dz-uploadprogress></div>
      </div>
      <!--<div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>-->
      <div class="dz-error-message"><span data-dz-errormessage></span></div>
    </div>
  </div>
</div>\
`,

});

myDropzone.on("dragenter", function (event) {
  dropzone.classList.add('bg-light');
});

myDropzone.on("dragend", function (event) {
  dropzone.classList.remove('bg-light');
});

myDropzone.on("dragleave", function (event) {
  dropzone.classList.remove('bg-light');
});

myDropzone.on("dragover", function (event) {
  dropzone.classList.add('bg-light');
});

myDropzone.on("drop", function (event) {
  dropzone.classList.remove('bg-light');
});

myDropzone.on("success", function (file, response) {
  // console.log(file);
  // console.log(response);
  if (response.status == 200) {
    attachments.push({
      filename: response.name,
      filesize: response.size,
      mimetype: response.type,
      s3_key: response.key,
      url: response.url,
    });
  } else {
    console.log(response);
  }
  printFileDetails();
});

myDropzone.on("removedfile", function (file) {
  // console.log(file);

  for (let i = 0; i < attachments.length; i++) {
    if (attachments[i].filename == file.name && attachments[i].mimetype == file.type) {
      attachments.splice(i, 1);
    }
  }
  printFileDetails();
});

/**
 * Print file details on change
 * @param {Event} event 
 */
function printFileDetails(event) {
  // Set a default max file size
  var maxFileSize = 1024 * 1024; // 1 megabyte
  if (dropzone.dataset.maxFileSizeBytes) {
    var maxFileSize = parseInt(dropzone.dataset.maxFileSizeBytes);
  }

  // Set a default max total file size
  var maxTotalFileSize = 1024 * 1024; // 1 megabyte
  if (dropzone.dataset.maxTotalFileSizeBytes) {
    var maxTotalFileSize = parseInt(dropzone.dataset.maxTotalFileSizeBytes);
  }

  // Total file size
  var totalFileSize = 0;

  // Array of files too large
  var tooLarge = [];

  for (let i = 0; i < attachments.length; i++) {
    totalFileSize += attachments[i].filesize;
    var maxFileSizeOK = attachments[i].filesize <= maxFileSize;
    var maxTotalFileSizeOK = totalFileSize <= maxTotalFileSize;

    if (!maxFileSizeOK) {
      tooLarge.push({
        'name': attachments[i].filename,
        'size': attachments[i].size,
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

  console.info(totalFileSize);

  var maxTotalFileSizeOK = totalFileSize <= maxTotalFileSize;

  console.log(maxTotalFileSizeOK);

  let warningMessage = document.getElementById('file-warning-message');

  if (maxTotalFileSizeOK && tooLarge.length == 0) {
    warningMessage.textContent = '';
    warningMessage.classList.add('d-none');
    canSubmit = true;
  } else {
    canSubmit = false;
    warningMessage.textContent = 'Files too large';
    var errorMessage = 'Files are too large or non-compliant';

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
      errorMessage = 'The files you are trying to upload are collectively bigger than the maximum size allowed. Please remove some files.'
    }

    warningMessage.textContent = errorMessage;

    warningMessage.classList.remove('d-none');
  }
}

// ON LOAD

document.addEventListener('DOMContentLoaded', function (event) {
  // Get all file input elements (in case more than one)

  // Fetch all the forms we want to apply custom Bootstrap validation styles to
  var form = document.getElementById('notify-form');
  form.addEventListener('submit', function (event) {
    document.getElementById('email-attachments').value = JSON.stringify(attachments);
    if (form.checkValidity() === false || !canSubmit) {
      event.preventDefault();
      event.stopPropagation();
    }
    form.classList.add('was-validated');
  }, false);

  // Init bootstrap custom file element code
  // bsCustomFileInput.init();

});