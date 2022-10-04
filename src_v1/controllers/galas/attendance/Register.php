<?php

\SCDS\Can::view('TeamManager', $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], $id);

$db = app()->db;

$getGala = $db->prepare("SELECT GalaName `name` FROM galas WHERE GalaID = ?");
$getGala->execute([$id]);
$gala = $getGala->fetch(PDO::FETCH_ASSOC);

$getSessions = $db->prepare("SELECT `Name`, `ID` FROM galaSessions WHERE Gala = ? ORDER BY `ID` ASC");
$getSessions->execute([$id]);
$session = $getSessions->fetch(PDO::FETCH_ASSOC);

$getSwimmers = $db->prepare("SELECT members.MemberID id, MForename fn, MSurname sn, SquadName squad FROM ((galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID) INNER JOIN squads ON members.SquadID = squads.SquadID) WHERE galaEntries.GalaID = ? ORDER BY MForename ASC, MSurname ASC");
$getSwimmers->execute([$id]);
$swimmers = $getSwimmers->fetchAll(PDO::FETCH_ASSOC);

$pagetitle = "Take register";
$fluidContainer = true;

include BASE_PATH . 'views/header.php';

?>

<div class="container-fluid">

  <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != 'Parent') { ?>
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=autoUrl("galas")?>">Galas</a></li>
      <li class="breadcrumb-item"><a href="<?=autoUrl("galas/" . $id)?>"><?=htmlspecialchars($gala['name'])?></a></li>
      <li class="breadcrumb-item active" aria-current="page">Registers</li>
    </ol>
  </nav>
  <?php } else { ?>
  <nav aria-label="breadcrumb">
		<ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=autoUrl("galas")?>">Galas</a></li>
      <li class="breadcrumb-item"><a href="<?=autoUrl("galas/" . $id)?>">This Gala</a></li>
			<li class="breadcrumb-item"><a href="<?=autoUrl("galas/" . $id . "/team-manager")?>">TM Dashboard</a></li>
			<li class="breadcrumb-item active" aria-current="page">Registers</li>
		</ol>
	</nav>
  <?php } ?>

  <div class="row">
    <div class="col-md-8">
      <h1>Take a register for <?=htmlspecialchars($gala['name'])?></h1>
      <p class="lead">Take a register for any session.</p>
    </div>
  </div>

  <?php if ($session == null) { ?>

  <div class="alert alert-warning">
    <p class="mb-0">
      <strong>
        There are no sessions for this gala.
      </strong>
    </p>
    <p class="mb-0">
      You must <a class="alert-link" href="<?=autoUrl("galas/" . $id . "/sessions")?>">add sessions for this gala</a> to take a register.
    </p>
  </div>
  
  <?php } else { ?>

  <div class="accordion" id="registerAccordion">
    <?php do { ?>
    <div class="card">
      <div class="card-header py-2" id="heading-<?=htmlspecialchars($session['ID'])?>">
        <h2 class="mb-0">
          <button class="btn btn-primary collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?=htmlspecialchars($session['ID'])?>" aria-expanded="true" aria-controls="collapse-<?=htmlspecialchars($session['ID'])?>">
            <?=htmlspecialchars($session['Name'])?> <i class="fa fa-chevron-down" aria-hidden="true"></i>
          </button>
        </h2>
      </div>

      <div id="collapse-<?=htmlspecialchars($session['ID'])?>" class="collapse" aria-labelledby="heading-<?=htmlspecialchars($session['ID'])?>" data-parent="#registerAccordion">
        <div class="card-body">
          <!-- FORM SECTION FOR SESSION <?=htmlspecialchars($session['ID'])?> -->
          <form method="post">
            <h2>Take register for <?=htmlspecialchars($session['Name'])?></h2>
            <input type="hidden" name="selected-session" value="<?=htmlspecialchars($session['ID'])?>">

            <ul class="list-group mb-3">
              <?php for ($i = 0; $i < sizeof($swimmers); $i++) { ?>
              <li class="list-group-item">
                <div class="row">
                  <div class="col">
                    <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="s-<?=htmlspecialchars($session['ID'])?>-m-<?=htmlspecialchars($swimmers[$i]['id'])?>" value="1" id="s-<?=htmlspecialchars($session['ID'])?>-m-<?=htmlspecialchars($swimmers[$i]['id'])?>">
                    <label class="form-check-label d-block" for="s-<?=htmlspecialchars($session['ID'])?>-m-<?=htmlspecialchars($swimmers[$i]['id'])?>">
                      <?=htmlspecialchars($swimmers[$i]['fn'] . " " . $swimmers[$i]['sn'] . ", " . $swimmers[$i]['squad'])?>
                    </label>
                  </div>
                </div>
              </li>
              <?php } ?>
            </ul>

            <button class="btn btn-primary" type="submit">
              Save register
            </button>
          </form>
        </div>
      </div>
    </div>
    <?php } while ($session = $getSessions->fetch(PDO::FETCH_ASSOC)); ?>
  </div>

  <?php } ?>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->useFluidContainer();
$footer->render();