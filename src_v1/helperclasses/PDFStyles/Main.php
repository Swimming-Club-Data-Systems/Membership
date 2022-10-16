<style>
  @font-face {
    font-family: 'Source Sans Pro';
    font-style: normal;
    font-weight: 400;
    src: url('<?= autoUrl('fonts/SourceSansPro-Regular.ttf') ?>') format('truetype');
  }

  @font-face {
    font-family: 'Source Sans';
    font-style: italic;
    font-weight: 400;
    src: url('<?= autoUrl('fonts/SourceSansPro-Italic.ttf') ?>') format('truetype');
  }

  @font-face {
    font-family: 'Source Sans Pro Bold';
    font-style: normal;
    font-weight: 400;
    src: url('<?= autoUrl('fonts/SourceSansPro-Bold.ttf') ?>') format('truetype');
  }

  @font-face {
    font-family: 'Roboto Mono';
    font-style: normal;
    font-weight: 400;
    src: url('<?= autoUrl('fonts/RobotoMono-Regular.ttf') ?>') format('truetype');
  }

  /* @font-face {
    font-family: 'Noto Emoji';
    src: url('<?= autoUrl('fonts/NotoEmoji-Regular.ttf') ?>') format('truetype');
  } */

  html {
    font-family: 'Source Sans Pro', 'Noto Emoji', sans-serif;
    color: black;
    margin: 1.27cm;
  }

  body {
    font-size: 10pt;
    line-height: 1;
    text-align: left;
  }

  * {
    box-sizing: border-box;
  }

  h1,
  h2,
  h3,
  h4,
  h5,
  h6 {
    font-family: 'Source Sans Pro Bold', 'Helvetica', 'Noto Emoji', sans-serif;
    font-weight: 400;
    font-style: normal;
    margin: 0 0 10pt 0;
    padding: 0;
    line-height: 1;
    break-after: avoid;
    page-break-after: avoid;
  }

  h1 {
    font-size: 25pt;
  }

  h2 {
    font-size: 20pt;
  }

  h3 {
    font-size: 17.5pt;
  }

  h4 {
    font-size: 15pt;
  }

  h5 {
    font-size: 12.5pt;
  }

  h6 {
    font-size: 10pt;
  }

  p {
    margin: 0 0 10pt 0;
    padding: 0;
  }

  strong,
  thead,
  th {
    font-family: 'Source Sans Pro', 'Helvetica', 'Noto Emoji', sans-serif;
    font-weight: 400;
    font-style: normal;
  }

  address {
    font-style: normal;
  }

  .lead {
    font-size: 18pt;
  }

  table {
    width: 100%;
    border-collapse: collapse;
  }

  td,
  th {
    padding: 3pt;
    border-top: 1px solid #eee;
    vertical-align: top;
  }

  thead th {
    border-bottom: 2px solid #eee;
  }

  <?php if (!app()->tenant->isCLS()) { ?>
  
  .primary {
    color: <?= htmlspecialchars(app()->tenant->getKey('SYSTEM_COLOUR')) ?>;
  }

  .primary-box {
    background-color: <?= htmlspecialchars(app()->tenant->getKey('SYSTEM_COLOUR')) ?>;
  }

  a,
  a:hover,
  a:active,
  a:visited {
    color: <?= htmlspecialchars(app()->tenant->getKey('SYSTEM_COLOUR')) ?>;
    text-decoration: underline;
  }

  <?php } else { ?>
  
  .primary {
    color: #bd0000;
  }

  .primary-box {
    padding: 4pt 10pt 10pt 10pt;
    background-color: #bd0000;
    color: #ffffff;
  }

  a,
  a:hover,
  a:active,
  a:visited {
    color: #bd0000;
    text-decoration: underline;
  }

  <?php } ?>
  
  .mb-0 {
    margin-bottom: 0;
  }

  .mb-3 {
    margin-bottom: 10pt;
  }

  .mt-0 {
    margin-top: 0;
  }

  .mt-3 {
    margin-top: 10pt;
  }

  .logo {
    height: 1.2cm;
    width: auto;
    max-width: 100%;
  }

  .text-end {
    text-align: right;
  }

  .row::after {
    content: "";
    clear: both;
    display: table;
  }

  .split-25 {
    width: 25%;
    float: left;
  }

  .split-30 {
    width: 30%;
    float: left;
  }

  .split-50 {
    width: 50%;
    float: left;
  }

  .split-70 {
    width: 70%;
    float: left;
  }

  .split-75 {
    width: 75%;
    float: left;
  }

  .list-unstyled {
    list-style: none;
    margin: 0;
    padding: 0;
  }

  .mono {
    font-family: 'Roboto Mono', 'Noto Emoji', monospace;
  }

  .page-break {
    page-break-after: always;
    break-after: always;
  }

  .prevent-page-break {
    page-break-inside: avoid;
    break-inside: avoid;
  }

  .address-font {
    font-size: 11pt;
  }

  .address-box {
    position: absolute;
    top: 2.23cm;
    left: 0.73cm;
    height: 3.45cm;
    width: 9cm;
    padding: 0.5cm 0.75cm;
    /* background: #eee; */
  }

  .after-address-box {
    height: 4.5cm;
    display: block;
  }

  .avoid-page-break-inside {
    page-break-inside: avoid;
  }

  .d-inline {
    display: inline;
  }

  .d-block {
    display: block;
  }

  .pb-3 {
    padding-bottom: 12pt;
  }

  .text-center {
    text-align: center;
  }

  <?php if (app()->tenant->getKey('SYSTEM_COLOUR') && getContrastColor(app()->tenant->getKey('SYSTEM_COLOUR'))) { ?>.primary-box {
    color: #000;
  }

  <?php } ?>
</style>