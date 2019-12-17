<?php

global $db;
$getGala = $db->prepare("SELECT GalaName `name`, ClosingDate FROM galas WHERE GalaID = ?");
$getGala->execute([$id]);
$gala = $getGala->fetch(PDO::FETCH_ASSOC);

if ($gala == null) {
  halt(404);
}

// Get price and event information
$galaData = new GalaPrices($db, $id);

$today = new DateTime('now', new DateTimeZone('Europe/London'));
$closingDate = new DateTime($gala['ClosingDate'], new DateTimeZone('Europe/London'));

// Arrays of swims used to check whever to print the name of the swim entered
// BEWARE This is in an order to ease inputting data into SportSystems, contrary to these arrays in other files
$swimsArray = GalaEvents::getEvents();

$pagetitle = 'Pricing and Events';
include BASE_PATH . 'views/header.php';

?>

<div class="container">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=autoUrl("galas")?>">Galas</a></li>
      <li class="breadcrumb-item"><a href="<?=autoUrl("galas/" . $id)?>">This gala</a></li>
      <li class="breadcrumb-item active" aria-current="page">Prices and events</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-lg-8">
      <h1>Pricing and events</h1>
      <p class="lead">Manage events and entry fees for <?=htmlspecialchars($gala['name'])?>.</p>

      <p>You can select events which will be run at this gala and enter the price for each of these events.</p>

      <p>To do this, tick or untick the box on the left of each event and enter the price on the right.</p>

      <form method="post">

        <ul class="list-group mb-3">
        <?php foreach ($swimsArray as $eventKey => $event) { ?>
          <li class="list-group-item">
            <h2><?=$event?></h2>

            <div class="input-group mb-3">
              <div class="input-group-prepend">
                <div class="input-group-text">
                  <input type="checkbox" aria-label="Tick to confirm event" <?php if ($galaData->getEvent($eventKey)->isEnabled()) { ?> checked <?php } ?> value="1" name="<?=$eventKey?>-check">
                </div>
                <span class="input-group-text" id="<?=$eventKey?>-price-addon">&pound;</span>
              </div>
              <input type="number" step="0.01" min="0" class="form-control" id="<?=$eventKey?>-price" name="<?=$eventKey?>-price" aria-label="<?=$event?> price" value="<?=htmlspecialchars($galaData->getEvent($eventKey)->getPriceAsString())?>">
            </div>
          </li>
        <?php } ?>
        </ul>

        <p>
          <button type="submit" class="btn btn-primary">
            Save
          </button>
        </p>

      </form>

    </div>
    <div class="col">
      <div class="cell">
        <h2>This is a new type of page</h2>
        <p class="lead">We're working to add more features and improve your experience while using it.</p>
        <p>Please bear with us while we do this.</p>
      </div>
    </div>
  </div>
</div>

<?php

include BASE_PATH . 'views/footer.php';