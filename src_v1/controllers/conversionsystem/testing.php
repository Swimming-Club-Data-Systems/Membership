<?php

if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['Time'])) {
  $fluidContainer = true;
}

$pagetitle = "Time Conversion System";
include BASE_PATH . "views/header.php";
?>

<div class="bg-light mt-n3 py-3 mb-3">

  <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['Time'])) { ?>
    <div class="container-fluid">
    <?php } else { ?>
      <div class="container-xl">
      <?php } ?>

      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="<?= htmlspecialchars(autoUrl("galas")) ?>">
              Galas
            </a>
          </li>
          <li class="breadcrumb-item active" aria-current="page">
            Time Converter
          </li>
        </ol>
      </nav>

      <div class="row align-items-center">
        <div class="col-lg-8">
          <h1>
            Swim Time Converter
          </h1>
          <p class="lead mb-0">
            Easily get converted times
          </p>
          <div class="mb-3 d-lg-none"></div>
        </div>
        <!-- <div class="col text-end">
        </div> -->
      </div>

      </div>
    </div>

    <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['Time'])) { ?>
      <div class="container-fluid">
      <?php } else { ?>
        <div class="container-xl">
        <?php } ?>
        <div class="row">
          <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['Time'])) { ?>
            <?php if (!$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Error']) { ?>
              <div class="col-lg-4">
                <table class="table table-sm table-striped table-light">
                  <thead class="">
                    <tr>
                      <th scope="col">Pool Length</th>
                      <th scope="col">Time (<?= htmlentities($_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Event']) ?>)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (!$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-20m-fail']) { ?>
                      <tr>
                        <td>20m</td>
                        <td class="font-monospace"><?= $_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-20m'] ?></td>
                      </tr>
                    <?php } else { ?>
                      <tr>
                        <td>20m</td>
                        <td class="font-monospace">No Conversion*</td>
                      </tr>
                    <?php } ?>

                    <?php if (!$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-25m-fail']) { ?>
                      <tr>
                        <td>25m</td>
                        <td class="font-monospace"><?= $_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-25m'] ?></td>
                      </tr>
                    <?php } else { ?>
                      <tr>
                        <td>25m</td>
                        <td class="font-monospace">No Conversion*</td>
                      </tr>
                    <?php } ?>

                    <?php if (!$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-33m-fail']) { ?>
                      <tr>
                        <td>33 1/3m</td>
                        <td class="font-monospace"><?= $_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-33m'] ?></td>
                      </tr>
                    <?php } else { ?>
                      <tr>
                        <td>33 1/3m</td>
                        <td class="font-monospace">No Conversion*</td>
                      </tr>
                    <?php } ?>

                    <?php if (!$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-50m-fail']) { ?>
                      <tr>
                        <td>50m</td>
                        <td class="font-monospace"><?= $_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-50m'] ?></td>
                      </tr>
                    <?php } else { ?>
                      <tr>
                        <td>50m</td>
                        <td class="font-monospace">No Conversion*</td>
                      </tr>
                    <?php } ?>

                    <?php if (!$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-20y-fail']) { ?>
                      <tr>
                        <td>20y</td>
                        <td class="font-monospace"><?= $_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-20y'] ?></td>
                      </tr>
                    <?php } else { ?>
                      <tr>
                        <td>20y</td>
                        <td class="font-monospace">No Conversion*</td>
                      </tr>
                    <?php } ?>

                    <?php if (!$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-25y-fail']) { ?>
                      <tr>
                        <td>25y</td>
                        <td class="font-monospace"><?= $_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-25y'] ?></td>
                      </tr>
                    <?php } else { ?>
                      <tr>
                        <td>25y</td>
                        <td class="font-monospace">No Conversion*</td>
                      </tr>
                    <?php } ?>

                    <?php if (!$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-27y-fail']) { ?>
                      <tr>
                        <td>27 1/2y</td>
                        <td class="font-monospace"><?= $_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-27y'] ?></td>
                      </tr>
                    <?php } else { ?>
                      <tr>
                        <td>27 1/2y</td>
                        <td class="font-monospace">No Conversion*</td>
                      </tr>
                    <?php } ?>

                    <?php if (!$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-33y-fail']) { ?>
                      <tr>
                        <td>33 1/3y</td>
                        <td class="font-monospace"><?= $_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-33y'] ?></td>
                      </tr>
                    <?php } else { ?>
                      <tr>
                        <td>33 1/3y</td>
                        <td class="font-monospace">No Conversion*</td>
                      </tr>
                    <?php } ?>

                    <?php if (!$_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-36y-fail']) { ?>
                      <tr>
                        <td>36 2/3y</td>
                        <td class="font-monospace"><?= $_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Conv-36y'] ?></td>
                      </tr>
                    <?php } else { ?>
                      <tr>
                        <td>36 2/3y</td>
                        <td class="font-monospace">No Conversion*</td>
                      </tr>
                    <?php } ?>
                  </tbody>
                </table>
                <p class="small">
                  Times are rounded to the nearest tenth of a second
                </p>
                <p class="small">
                  If you see <span class="font-monospace">No Conversion*</span>, this is because a
                  conversion for that event and pool length is not possible or the
                  resulting converted time contained a negative component.
                </p>
              </div>
          <?php }
          } ?>
          <div class="col">
            <!-- consider order-lg-first -->
            <div class="cell">
              <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Error']) && $_SESSION['TENANT-' . app()->tenant->getId()]['Time']['Error']) { ?>
                <div class="alert alert-warning">
                  An error occured during calculation
                </div>
              <?php } ?>
              <?php include "Form.php"; ?>
            </div>
          </div>
        </div>
        <p class="small text-muted">
          This software is powered by the <a href="https://github.com/Chester-le-Street-ASC/EquivalentTime" target="_blank">Chester-le-Street-ASC/EquivalentTime</a> package.
        </p>
        <p class="small text-muted mb-0">
          <a href="https://www.chesterlestreetasc.co.uk/wp-content/uploads/2019/02/Equation.pdf" target="_blank">Learn about the maths involved in converting times</a>.
        </div>

        <?php

        $footer = new \SCDS\Footer();
        $footer->addJS("js/NeedsValidation.js");
        $footer->useFluidContainer();
        $footer->render();
