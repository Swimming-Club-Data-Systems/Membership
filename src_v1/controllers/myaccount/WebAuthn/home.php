<?php

$fluidContainer = true;

$db = app()->db;
$tenant = app()->tenant;
$user = app()->user;

$pagetitle = "Security Keys";
include BASE_PATH . "views/header.php";
$userID = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
?>
<div class="container-fluid">
  <div class="row justify-content-between">
    <div class="col-md-3 d-none d-md-block">
      <?php
      $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/myaccount/ProfileEditorLinks.json'));
      echo $list->render();
      ?>
    </div>
    <div class="col-md-9">
      <h1>Security Keys</h1>
      <p class="lead">Add and remove security keys.</p>
    </div>
  </div>
</div>

<div id="relying-party-info" data-name="<?= htmlspecialchars(app()->tenant->getName()) ?>"></div>
<div id="user-info" data-display-name="<?= htmlspecialchars($user->getFullName()) ?>" data-id="<?= htmlspecialchars($user->getId()) ?>" data-name="<?= htmlspecialchars($user->getEmail()) ?>"></div>

<script>

  let rpDetails = document.getElementById('relying-party-info').dataset;
  let userDetails = document.getElementById('user-info').dataset;

  // sample arguments for registration
  var createCredentialDefaultArgs = {
    publicKey: {
      // Relying Party (a.k.a. - Service):
      rp: {
        name: rpDetails.name,
      },

      // User:
      user: {
        id: new Uint8Array([userDetails.id]),
        name: userDetails.name,
        displayName: userDetails.displayName,
      },

      pubKeyCredParams: [{
        type: "public-key",
        alg: -7
      }],

      attestation: "direct",

      timeout: 60000,

      challenge: new Uint8Array([ // must be a cryptographically random number sent from a server
        0x8C, 0x0A, 0x26, 0xFF, 0x22, 0x91, 0xC1, 0xE9, 0xB9, 0x4E, 0x2E, 0x17, 0x1A, 0x98, 0x6A, 0x73,
        0x71, 0x9D, 0x43, 0x48, 0xD5, 0xA7, 0x6A, 0x15, 0x7E, 0x38, 0x94, 0x52, 0x77, 0x97, 0x0F, 0xEF
      ]).buffer
    }
  };

  console.log(createCredentialDefaultArgs);

  // sample arguments for login
  var getCredentialDefaultArgs = {
    publicKey: {
      timeout: 60000,
      // allowCredentials: [newCredential] // see below
      challenge: new Uint8Array([ // must be a cryptographically random number sent from a server
        0x79, 0x50, 0x68, 0x71, 0xDA, 0xEE, 0xEE, 0xB9, 0x94, 0xC3, 0xC2, 0x15, 0x67, 0x65, 0x26, 0x22,
        0xE3, 0xF3, 0xAB, 0x3B, 0x78, 0x2E, 0xD5, 0x6F, 0x81, 0x26, 0xE2, 0xA6, 0x01, 0x7D, 0x74, 0x50
      ]).buffer
    },
  };

  // register / create a new credential
  navigator.credentials.create(createCredentialDefaultArgs)
    .then((cred) => {
      console.log("NEW CREDENTIAL", cred);

      // normally the credential IDs available for an account would come from a server
      // but we can just copy them from above...
      var idList = [{
        id: cred.rawId,
        transports: ["usb", "nfc", "ble"],
        type: "public-key"
      }];
      // getCredentialDefaultArgs.publicKey.allowCredentials = idList;
      return navigator.credentials.get(getCredentialDefaultArgs);
    })
    .then((assertion) => {
      console.log("ASSERTION", assertion);
    })
    .catch((err) => {
      console.log("ERROR", err);
    });
</script>

<?php $footer = new \SCDS\Footer();
$footer->useFluidContainer();
$footer->render(); ?>