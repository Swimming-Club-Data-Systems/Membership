<?php

$db = app()->db;
$tenant = app()->tenant;

$pagetitle = 'Session Booking';
include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container-xl">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('timetable')) ?>">Timetable</a></li>
        <li class="breadcrumb-item active" aria-current="page">Booking</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col">
        <h1>
          Book a session
        </h1>
        <p class="lead mb-0">
          Book numbers limited or pay as you go sessions
        </p>
      </div>
    </div>

  </div>
</div>

<div class="container-xl">

  <div class="row">
    <div class="col-lg-8">
      <p class="lead">
        Session Booking is a new service, introduced to help clubs during COVID-19.
      </p>
    </div>
  </div>

  <div class="row">
    <div class="col-md-6">
      <div class="card card-body h-100" style="display: grid;">
        <span>
          <h2>Book a place</h2>
          <p class="lead">
            To book a place at a session, search for it in our timetable and follow the booking instructions.
          </p>
        </span>

        <p class="mb-0 mt-auto sd-flex">
          <a href="<?= htmlspecialchars(autoUrl('timetable')) ?>" class="btn btn-primary">
            View Timetable
          </a>
        </p>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card card-body h-100 d-grid" style="display: grid;">
        <span>
          <h2>View my bookings</h2>
          <p class="lead">
            See your upcoming booked sessions
          </p>
        </span>

        <p class="mb-0 mt-auto d-flex">
          <a href="<?= htmlspecialchars(autoUrl('timetable/booking/my-bookings')) ?>" class="btn btn-primary">
            View Bookings
          </a>
        </p>
      </div>
    </div>
  </div>
</div>

</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
