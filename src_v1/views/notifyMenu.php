<!--<div class="bg-warning shadow mb-3" style="margin-top:-1rem;">
  <nav class="nav nav-underline">
    <span class="nav-link">
      <strong>
        The Notify Service is currently closed for maintenance. Attempts to send
        messages will fail.
      </strong>
    </span>
  </nav>
</div>
<div class="nav-scroller bg-white shadow mb-3">
  <div class="<?=$container_class?>">
    <nav class="nav-scroller nav nav-underline">
  		<a class="nav-link" href="<?php echo autoUrl("notify")?>">Home</a>
  		<a class="nav-link" href="<?php echo autoUrl("notify/newemail")?>">New Message</a>
      <a class="nav-link" href="<?php echo autoUrl("notify/lists")?>">Targeted Lists</a>
      <?php if ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == "Admin") { ?>
      <a class="nav-link" href="<?php echo autoUrl("notify/sms")?>">SMS Lists</a>
      <?php } ?>
  		<a class="nav-link" href="<?php echo autoUrl("notify/email")?>">Pending Messages</a>
      <a class="nav-link" href="<?php echo autoUrl("notify/history")?>">Previous Messages</a>
  	</nav>
  </div>
</div>-->
