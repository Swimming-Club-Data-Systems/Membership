tinymce.init({
  skin: (window.matchMedia("(prefers-color-scheme: dark)").matches ? "oxide-dark" : ""),
  relative_urls: false,
  remove_script_host: false,
  document_base_url: document.getElementById('message').dataset.documentBaseUrl,
  selector: '#message',
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