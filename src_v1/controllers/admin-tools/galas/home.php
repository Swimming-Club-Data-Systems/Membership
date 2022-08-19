<?php

$pagetitle = "Gala Admin";

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=htmlspecialchars(autoUrl("admin"))?>">Admin</a></li>
      <li class="breadcrumb-item active" aria-current="page">Galas</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-lg-8">
      <h1>Gala tools</h1>
      <p class="lead">Welcome to the gala tools dashboard.</p>

      <h2>Upload meet result SDIF files</h2>
      <p class="lead">Upload times for your members quickly using an SDIF (*.sd3) file.</p>
      <p>
        <a href="<?=htmlspecialchars(autoUrl("admin/galas/sdif/upload"))?>" class="btn btn-primary">
          Upload results
        </a>
      </p>

    </div>

    <div class="col">
      <?php
        $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/admin-tools/list.json'));
        echo $list->render('admin-galas');
      ?>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();