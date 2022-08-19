<?php

header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);

$db = app()->db;
$tenant = app()->tenant;

$now = new DateTime('now', new DateTimeZone('Europe/London'));

$galas = $db->prepare("SELECT GalaID, GalaName, ClosingDate, GalaDate, GalaVenue, CourseLength FROM galas WHERE Tenant = ? AND ClosingDate >= ? ORDER BY GalaDate ASC");
$galas->execute([
	$tenant->getId(),
	$now->format('Y-m-d')
]);
$gala = $galas->fetch(PDO::FETCH_ASSOC);
$entriesOpen = false;

?>

<div class="cell">
	<h2>
		Open galas
	</h2>
	<?php if ($gala != null) { ?>
	<div class="news-grid my-3" style="grid-template-columns: repeat(1, 1fr);">
		<?php do {
			$now = new DateTime();
			$closingDate = new DateTime($gala['ClosingDate']);
			$endDate = new DateTime($gala['GalaDate']);
			if ($now <= $closingDate) {
				$entriesOpen = true;
			}
			?>
			<a href="<?=autoUrl("galas/" . $gala['GalaID'])?>">
				<div>
					<span class="title mb-0 justify-content-between align-items-start">
						<span><?=htmlspecialchars($gala['GalaName'])?></span>
						<span class="ms-2 badge bg-success">ENTRIES OPEN</span>
					</span>
					<span class="d-flex mb-3"><?=htmlspecialchars($gala['GalaVenue'])?></span>
				</div>
				<span class="category">Entries close on <?=$closingDate->format('j F Y')?></span>
			</a>
		<?php } while ($gala = $galas->fetch(PDO::FETCH_ASSOC)); ?>
	</div>
	<?php if ($entriesOpen) { ?>
	<p>
		<a href="<?=autoUrl("galas/entergala")?>" class="btn btn-success">
			Enter a gala
		</a>
	</p>
  <p class="mb-0">
    Enter via My Account
  </p>
  <?php } else { ?>
  <p class="mb-0">
    There are no galas open for entries at the moment.
  </p>
  <?php } ?>
  <?php } else { ?>
  <p class="mb-0">
		There are no galas open for entries at the moment.
	</p>
  <?php } ?>
</div>