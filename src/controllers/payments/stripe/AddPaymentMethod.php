<?php

global $db;

$getUserEmail = $db->prepare("SELECT Forename, Surname, EmailAddress FROM users WHERE UserID = ?");
$getUserEmail->execute([$_SESSION['UserID']]);
$user = $getUserEmail->fetch(PDO::FETCH_ASSOC);

$checkIfCustomer = $db->prepare("SELECT COUNT(*) FROM stripeCustomers WHERE User = ?");
$checkIfCustomer->execute([$_SESSION['UserID']]);

if ($checkIfCustomer->fetchColumn() == 0) {
  // See your keys here: https://dashboard.stripe.com/account/apikeys
  \Stripe\Stripe::setApiKey(env('STRIPE'));

  // Create a Customer:
  $customer = \Stripe\Customer::create([
    "name" => $user['Forename'] . ' ' . $user['Surname'],
    "description" => "Customer for " . $_SESSION['UserID'] . ' (' . $user['EmailAddress'] . ')'
  ]);

  // YOUR CODE: Save the customer ID and other info in a database for later.
  $id = $customer->id;
  $addCustomer = $db->prepare("INSERT INTO stripeCustomers (User, CustomerID) VALUES (?, ?)");
  $addCustomer->execute([
    $_SESSION['UserID'],
    $id
  ]);
}

include BASE_PATH . 'views/header.php';

?>

<style>
/**
 * The CSS shown here will not be introduced in the Quickstart guide, but shows
 * how you can use CSS to style your Element's container.
 */
.card-element {
  box-sizing: border-box;

  /* height: 40px; */

  padding: 1rem;

  color: #333;
  font-family: "Open Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
  font-size: 1rem;

  border: 1px solid #ced4da;

  background-color: white;

  box-shadow: none;
}
</style>
<?php if (bool(env('IS_CLS'))) { ?>
<style>
.card-element {
  border-radius: 0px;
}
</style>
<?php } ?>

<div class="container">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=autoUrl("payments")?>">Payments</a></li>
      <li class="breadcrumb-item"><a href="<?=autoUrl("payments/cards")?>">Cards</a></li>
      <li class="breadcrumb-item active" aria-current="page">Add card</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-md-8">
      <h1>Add a payment card</h1>

      <?php if (isset($_SESSION['PayCardError'])) { ?>
      <div class="alert alert-danger">
        <p class="mb-0">
          <strong>An error occurred</strong>
        </p>
        <?php if (isset($_SESSION['PayCardErrorMessage'])) { ?>
        <p class="mb-0"><?=htmlspecialchars($_SESSION['PayCardErrorMessage'])?></p>
        <?php } ?>
      </div>
      <?php unset($_SESSION['PayCardError']); unset($_SESSION['PayCardErrorMessage']); ?>
      <?php } ?>

      <form action="<?=currentUrl()?>" method="post" id="payment-form" class="mb-5">
        <div class="form-group">
          <label for="name">Name this card</label>
          <input type="text" class="form-control" id="name" name="name" placeholder="Card Name" required
            aria-describedby="cardNameHelp">
          <small id="cardNameHelp" class="form-text text-muted">Name your card to help you select it more easily</small>
        </div>
        <div class="mb-3">
          <label for="card-element">
            Credit or debit card
          </label>
          <div id="card-element" class="card-element">
            <!-- A Stripe Element will be inserted here. -->
          </div>

          <!-- Used to display form errors. -->
          <div id="card-errors" role="alert"></div>
        </div>

        <p>
          <button class="btn btn-success">Add payment card</button>
        </p>
      </form>

      <div class="text-muted">
        <p>
          We accept Visa, MasterCard and American Express.
        </p>
      </div>
    </div>
  </div>
</div>

<script>
var stripe = Stripe('<?=htmlspecialchars(env('STRIPE_PUBLISHABLE'))?>');

// Custom styling can be passed to options when creating an Element.
// (Note that this demo uses a wider set of styles than the guide below.)
// Try to match bootstrap 4 styling
var style = {
  base: {
    'lineHeight': '1.35',
    'fontSize': '1.11rem',
    'color': '#495057',
    'fontFamily': 'apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif'
  }
};

var options = {
  options: {
    style: style
  }
};

var elements = stripe.elements({
  fonts: [
    {
      cssSrc: 'https://fonts.googleapis.com/css?family=Open+Sans',
    },
  ]
});

// Create an instance of the card Element.
var card = elements.create('card', {
  iconStyle: 'solid',
  style: {
    base: {
      iconColor: '#ced4da',
      color: '#212529',
      fontWeight: 400,
      fontFamily: 'Open Sans, Segoe UI, sans-serif',
      fontSize: '16px',
      fontSmoothing: 'antialiased',
      ':-webkit-autofill': {
        color: '#868e96',
      },
      '::placeholder': {
        color: '#868e96',
      },
    },
    invalid: {
      iconColor: '#dc3545',
      color: '#dc3545',
    },
  },
});

// Add an instance of the card Element into the `card-element` <div>.
card.mount('#card-element');

card.addEventListener('change', function(event) {
  var displayError = document.getElementById('card-errors');
  if (event.error) {
    displayError.textContent = event.error.message;
  } else {
    displayError.textContent = '';
  }
});

var form = document.getElementById('payment-form');
form.addEventListener('submit', function(event) {
  event.preventDefault();

  token = stripe.createPaymentMethod('card', card).then(function(result) {
    if (result.error) {
      // Inform the customer that there was an error.
      var errorElement = document.getElementById('card-errors');
      errorElement.textContent = result.error.message;
    } else {
      // Send the token to your server.
      stripeTokenHandler(result.paymentMethod);
    }
  });
});

function stripeTokenHandler(token) {
  // Insert the token ID into the form so it gets submitted to the server
  var form = document.getElementById('payment-form');
  var hiddenInput = document.createElement('input');
  hiddenInput.setAttribute('type', 'hidden');
  hiddenInput.setAttribute('name', 'stripeToken');
  hiddenInput.setAttribute('value', token.id);
  form.appendChild(hiddenInput);

  // Submit the form
  form.submit();
}
</script>

<?php

include BASE_PATH . 'views/footer.php';