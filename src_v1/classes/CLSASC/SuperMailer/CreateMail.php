<?php

namespace CLSASC\SuperMailer;

/**
 * A PHP Class to extend Mail formats
 *
 * @copyright Chester-le-Street ASC https://github.com/Chester-le-Street-ASC
 * @author Chris Heppell https://github.com/clheppell
 */
class CreateMail
{
  private $htmlContent;
  private $plainContent;
  private $allowUnsubscribe;
  private $name;

  function __construct()
  {
  }

  public function setUnsubscribable()
  {
    $this->allowUnsubscribe = true;
  }

  public function setForced()
  {
    $this->allowUnsubscribe = false;
  }

  public function showName($name = null)
  {
    $this->showName = true;
    $this->name = $name;
  }

  public function hideName()
  {
    $this->showName = false;
  }

  public function setHtmlContent($htmlContent)
  {
    $this->htmlContent = $htmlContent;
  }

  public function getHtmlContent()
  {
    return $this->htmlContent;
  }

  public function setPlainContent($plainContent)
  {
    $this->plainContent = html_entity_decode($plainContent);
  }

  public function getPlainContent()
  {
    if ($this->plainContent != null) {
      return $this->plainContent;
    } else {
      return html_entity_decode(strip_tags($this->getHtmlContent()));
    }
  }

  public function getFormattedHtml()
  {
    $fontStack = 'system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Liberation Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"';
    $head = "
    <!DOCTYPE html>
    <html lang=\"en-gb\">
    <head>
      <meta charset=\"utf-8\">";
    $head .= "
      <style type=\"text/css\">
        html, body {
          font-family: " . $fontStack . ";
          font-size: 16px;
          background: #ffffff;
        }

        p, h1, h2, h3, h4, h5, h6, ul, ol, img, table, .table, blockquote, address {
          margin: 0 0 16px 0;
          font-family: " . $fontStack . ";
        }

        .small {
          font-size: 11px;
          color: #868e96;
          margin-bottom: 11px;
        }

        .text-center {
          text-align: center;
        }

        .bottom {
          margin: 16px 0 0 0;
        }

        cell {
          display: table;
          background: #eee;
          padding: 0rem;
          margin 0 0 1rem 0;
          width: 100%;
        }
      </style>
    </head>
    <body>
    <div style=\"background:#ffffff;\">
      <table style=\"width:100%;border:0px;text-align:left;padding:0px 0px 0px 0px;background:#ffffff;\"><tr><td align=\"center\">
        <table style=\"width:100%;max-width:700px;border:0px;text-align:center;background:#ffffff;padding:0px 0px 0px 0px;\"><tr><td>";
    if (isset(app()->tenant) && $logos = app()->tenant->getKey('LOGO_DIR')) {
      $head .= "<img src=\"" . getUploadedAssetUrl($logos . 'logo-75.png') . "\" srcset=\"" .
        getUploadedAssetUrl($logos . 'logo-75@2x.png') . " 2x, " .
        getUploadedAssetUrl($logos . 'logo-75@3x.png') . " 3x\" style=\"max-width:100%;max-height:75px;\" alt=\"" . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . " Logo\">";
    } else if (isset(app()->tenant)) {
      $head .= htmlspecialchars(app()->tenant->getKey('CLUB_NAME'));
    } else {
      $head .= "<img src=\"" . autoUrl('public/img/corporate/icons/apple-touch-icon-152x152.png') . "\" alt=\"SCDS Membership Logo\">";
    }
    $head .= "</td></tr></table>
        <table style=\"width:100%;max-width:700px;border:0px;text-align:left;background:#ffffff;padding:0px 0px;\"><tr><td>
    ";
    if (isset($this->showName) && $this->showName && (!isset($this->name) || $this->name == null)) {
      $head .= '<p class="small text-muted">Hello -name-, </p>';
    } else if (isset($this->showName) && $this->showName && isset($this->name) && $this->name != null) {
      $head .= '<p class="small text-muted">Hello  ' . htmlspecialchars($this->name) .  ', </p>';
    }

    $foot = "</td></tr></table>
    <table style=\"width:100%;max-width:700px;border:0px;background:#f8fcff;padding:0px 0px;\"><tr><td>
    <div
    class=\"bottom text-center\">";
    if (isset(app()->tenant)) {
      $foot .= "
      <p class=\"small\" align=\"center\"><strong>" . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . "</strong><br>";
      $addr = json_decode(app()->tenant->getKey('CLUB_ADDRESS'));
      if ($addr) {
        for ($i = 0; $i < sizeof($addr); $i++) {
          $foot .= htmlspecialchars($addr[$i]) . '<br>';
          if (isset($addr[$i + 1]) && $addr[$i + 1] == "") {
            break;
          }
        }
      }
    } else {
      $foot .= "<p class=\"small\" align=\"center\">SCDS Membership Software<br>Newcastle-upon-Tyne and Sheffield</p><p class=\"small\" align=\"center\">For support call <a href=\"tel:+441912494320\">+44 191 249 4320</a> or email <a href=\"mailto:support@myswimmingclub.uk\">support@myswimmingclub.uk</a>";
    }
    $foot .= "</p>";
    if (isset(app()->tenant)) {
      $foot .= "
    <p class=\"small\" align=\"center\">This email was sent via the " . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . " Membership System.</p>";
      $foot .= "<p class=\"small\" align=\"center\">Have questions? Contact us at <a
    href=\"mailto:" . htmlspecialchars(app()->tenant->getKey('CLUB_EMAIL')) . "\">" . htmlspecialchars(app()->tenant->getKey('CLUB_EMAIL')) . "</a>.</p>
    <p class=\"small\" align=\"center\">To control your email options, go to <a href=\"" .
        autoUrl("myaccount/email") . "\">My Account</a>.</p>
    <p class=\"small\" align=\"center\">Unwanted email? <a href=\"https://forms.office.com/Pages/ResponsePage.aspx?id=eUyplshmHU2mMHhet4xottqTRsfDlXxPnyldf9tMT9ZUODZRTFpFRzJWOFpQM1pLQ0hDWUlXRllJVS4u\" target=\"_blank\" title=\"Report email abuse\">Report email abuse</a>.</p>";
      if ($this->allowUnsubscribe) {
        $foot .= '<p class="small" align="center"><a href="-unsub_link-">Click to Unsubscribe</a></p>';
      }
      $foot .= "
    <p class=\"small\" align=\"center\">&copy; " . htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) . " " . date("Y") . ", Design &copy; SCDS</p>";
    }
    $foot .= "
      </div>
      </table>
    </table>
    </div>
    </body>
    </html>";

    return $head . $this->getHtmlContent() . $foot;
  }

  public function getFormattedPlain()
  {
    $head = "";

    if (isset($this->showName) && $this->showName && (!isset($this->name) || $this->name == null)) {
      $head .= "Hello -name-,\r\n\r\n";
    } else if (isset($this->showName) && $this->showName && isset($this->name) && $this->name != null) {
      $head .= "Hello " . htmlspecialchars($this->name) . ",\r\n\r\n";
    }

    if (isset(app()->tenant)) {
      $foot = "\r\n\n\n " . app()->tenant->getKey('CLUB_NAME') . "\r\n\r\n";
      $foot .= app()->tenant->getKey('CLUB_NAME') . "\r\n";
      $addr = json_decode(app()->tenant->getKey('CLUB_ADDRESS'));
      if ($addr) {
        for ($i = 0; $i < sizeof($addr); $i++) {
          $foot .= $addr[$i] . "\r\n";
          if (isset($addr[$i + 1]) && $addr[$i + 1] == "") {
            break;
          }
        }
      }
      $foot .= "\r\nThis email was sent automatically by the " . app()->tenant->getKey('CLUB_NAME') . " Membership System.\r\n\r\n";
      $foot .= "Have questions? Contact us at " . app()->tenant->getKey('CLUB_EMAIL') . ".\r\n\r\n";
      $foot .= "To control your email options go to My Account at " . autoUrl("myaccount") . ".\r\n\r\n";
      $foot .= "Report email abuse at https://forms.office.com/Pages/ResponsePage.aspx?id=eUyplshmHU2mMHhet4xottqTRsfDlXxPnyldf9tMT9ZUODZRTFpFRzJWOFpQM1pLQ0hDWUlXRllJVS4u.\r\n\r\n";
      if ($this->allowUnsubscribe) {
        $foot .= "Unsubscribe at -unsub_link-\r\n\r\n";
      }
      $foot .= "Content copyright " . date("Y") . " " . app()->tenant->getKey('CLUB_NAME') . ", Design copyright SCDS";
    } else {
      $foot = "Copyright SCDS. Membership MT.";
    }

    return $head . $this->getPlainContent() . $foot;
  }
}
