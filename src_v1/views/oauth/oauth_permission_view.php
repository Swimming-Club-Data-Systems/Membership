<?php

$app_name = "CLS ASC Store";

include BASE_PATH . "views/head.php";

?>
<div class="frontpage1 d-flex flex-column" style="height:100vh;margin: 0 0 -1rem 0;background:#eee">
  <div class="mb-auto"></div>
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-sm-8 col-md-5 col-lg-6 col-xl-4 align-middle">
        <div class="my-3 p-3 bg-white rounded">
          <span class="text-muted border-bottom border-gray pb-2 mb-0 d-block">
						Login with <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?>
					</span>
          <div class="pt-3">
						<h1 class="h4">
							<?=$app_name?> wants to access your <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?> Account.
						</h1>
						<p class="lead mb-0 text-truncate">
							<?=getUserName($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'])?> <em><?=$_SESSION['TENANT-' . app()->tenant->getId()]['EmailAddress']?></em>
						</p>
						<hr>
						<p>
							<strong>This will allow <?=$app_name?> to:</strong>
						</p>
						<ul>
							<li>
								Access your Email Address
							</li>
              <li>
								Access your basic information
							</li>
						</ul>
						<p>
							Allow <?=$app_name?> to do this?php
						</p>
						<p class="small text-muted">
							By clicking Allow, you allow this app to use your information in
							accordance to their terms of service and privacy policies. You can
							view or remove app access in your Account.
						</p>
						<div class="text-end">
							<a class="btn btn-light" href="">Cancel</a>
							<a class="btn btn-success" href="">Allow</a>
						</div>
	        </div>
	      </div>

	    </div>
	  </div>
	</div>
<div class="mt-auto"></div>
</div>
<?php
  $footer = new \SCDS\Footer();
$footer->render();
?>
