<?php

$pagetitle = "Welcome Pack Options";

include BASE_PATH . 'views/header.php';

?>

<div class="container-xl">
  <h1>
    Welcome Pack Settings
  </h1>
  <div class="row">
    <div class="col-lg-8">
    <p class="lead">This software can generate a PDF welcome pack for printing or electronic distribution.</p>
    <p>On this page, you can choose which posts are used in the document.</p>

    <form method="post">
    <div class="mb-3">
      <label class="form-label" for="exampleInputEmail1">Email address</label>
      <input type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter email">
      <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
    </div>
    </form>
    </div>
  </div>
</div>

<?php

include  BASE_PATH . 'views/footer.php';