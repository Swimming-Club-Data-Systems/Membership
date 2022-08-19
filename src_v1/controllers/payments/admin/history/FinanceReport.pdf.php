<?php

require 'FinanceReport.json.php';

$data = json_encode($output);
$data = json_decode($data);
$items = $data->items;

$pagetitle = 'Finance Report';

$netCosts = $netIncome = 0;

ob_start(); ?>

<!DOCTYPE html>
<html>

<head>
  <meta charset='utf-8'>
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i" rel="stylesheet" type="text/css">
  <!--<link href="https://fonts.googleapis.com/css?family=Open+Sans:700,700i" rel="stylesheet" type="text/css">-->
  <?php include BASE_PATH . 'helperclasses/PDFStyles/Main.php'; ?>
  <title><?= $pagetitle ?></title>
  <style>
    thead tr th {
      text-align: left;
    }
  </style>
</head>

<body>
  <?php include BASE_PATH . 'helperclasses/PDFStyles/Letterhead.php'; ?>

  <div class="row mb-3 text-end">
    <div class="split-50">
    </div>
    <div class="split-50">
      <p>
        <?= date("d/m/Y", strtotime($data->date_produced)) ?>
      </p>
    </div>
  </div>

  <div class="primary-box mb-3" id="title">
    <h1 class="mb-0">
      Card and Direct Debit Financial Report
    </h1>
    <p class="lead mb-0">
      For <?= htmlspecialchars(date('F Y'), strtotime($data->year . '-' . $data->month . '-01')) ?>
    </p>
  </div>

  <h2 id="payment-details">Itemised Details</h2>
  <p>All amounts are shown in GBP (£)</p>

  <table>
    <thead>
      <tr>
        <th>
          Date
        </th>
        <th>
          Type
        </th>
        <th>
          Details
        </th>
        <th>
          Credits
        </th>
        <th>
          Debits
        </th>
        <th>
          Gross/Net
        </th>
        <th>
          Status
        </th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $item) { ?>
        <?php
        $date = new DateTime($item->date);
        if ($item->income == 'Net') {
          $netIncome += $item->credits;
          $netCosts += $item->debits;
        }
        ?>
        <tr>
          <td>
            <?= $date->format('d/m/Y') ?>
          </td>
          <td>
            <?php if ($item->object == 'Payment') {
              echo htmlspecialchars($item->type);
            } else {
              echo htmlspecialchars($item->object);
            } ?>
          </td>
          <td>
            <?= htmlspecialchars($item->details) ?>
          </td>
          <td class="font-monospace">
            <?= number_format($item->credits / 100, 2, '.', '') ?>
          </td>
          <td class="font-monospace">
            <?= number_format($item->debits / 100, 2, '.', '') ?>
          </td>
          <td>
            <?= htmlspecialchars($item->income) ?>
          </td>
          <td>
            <?= htmlspecialchars($item->status) ?>
          </td>
        </tr>
      <?php } ?>
      <tr>
        <td>
        </td>
        <td>
          <strong>Total</strong>
        </td>
        <td>
          Net Income
        </td>
        <td class="font-monospace">
          <?= number_format($netIncome / 100, 2, '.', '') ?>
        </td>
        <td class="font-monospace">
          <?= number_format($netCosts / 100, 2, '.', '') ?>
        </td>
        <td>
          Net
        </td>
        <td>
          Paid out
        </td>
      </tr>
    </tbody>
  </table>

  <div class="page-break"></div>

  <img src="<?= BASE_PATH ?>public/img/corporate/scds.png" style="height:1.5cm;" class="mb-3" alt="Swimming CLub Data Systems Logo">

  <h2 id="about">SCDS Payment Finance Reports</h2>
  <p>
    SCDS Payment Finance Reports are available as CSV, JSON and PDF files. These files can be used by your treasurer or accounting software for accounting costs and income from direct debit and card payments.
  </p>

  <p>
    Charged back payments may not be accurately reflected in this document. We're working to improve this.
  </p>

  <p>
    <strong>These reports are a new feature.</strong> Please tell us how we could improve these.
  </p>

  <p>
    Payments are handled by Stripe and GoCardless on behalf of <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?>. You can also download reports from the Stripe Dashboard at <a href="https://dashboard.stripe.com/">dashboard.stripe.com</a> and from within the GoCardless user interface at <a href="https://manage.gocardless.com/">manage.gocardless.com</a>.
  </p>

  <p>
    Further auditing may be required for tax purposes - Talk to an accountant if you're not sure what is required. This software has <strong>not</strong> been written to produce reports which can be accepted electronically by HMRC.
  </p>

  <h2 id="pci-dss">Ensure you're compliant with PCI DSS!</h2>
  <p>
    All Stripe users must validate their PCI compliance annually. Most users can do this with a Self-Assessment Questionnaire (SAQ) provided by the PCI Security Standards Council. The type of SAQ depends on how you integrated Stripe and which of the methods below you use to collect card data. Certain methods may require you to upload additional PCI documentation to us. If this is necessary, Stripe will notify you in the Stripe Dashboard.
  </p>

  <p>
    &copy; Swimming Club Data Systems <?= date("Y", strtotime($data->date_produced)) ?>. Produced for <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?>.
  </p>

  <?php $landscape = true;
  include BASE_PATH . 'helperclasses/PDFStyles/PageNumbers.php'; ?>
</body>

</html>

<?php

$html = ob_get_clean();

// reference the Dompdf namespace
use Dompdf\Dompdf;
use Dompdf\Options;

// instantiate and use the dompdf class
$dompdf = new Dompdf();

// set font dir here
$options = new Options([
  'fontDir' => getenv('FILE_STORE_PATH') . 'fonts/',
  'fontCache' => getenv('FILE_STORE_PATH') . 'fonts/',
  'isFontSubsettingEnabled' => true,
  'isRemoteEnabled' => true,
  'defaultFont' => 'Open Sans',
  'defaultMediaType' => 'all',
  'isPhpEnabled' => true,
]);
$dompdf->setOptions($options);
$dompdf->loadHtml($html);

// (Optional) Setup the paper size and orientation
$dompdf->setPaper('A4', 'landscape');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser
header('Content-Description: File Transfer');
header('Content-Type: application/pdf');
header('Content-Disposition: inline');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$dompdf->stream(str_replace(' ', '', $pagetitle) . ".pdf", ['Attachment' => 0]);
