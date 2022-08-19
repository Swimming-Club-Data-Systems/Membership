<?php $target = ""; ?>
<div class="bg-light mb-3 py-2 shadow-sm mt-n3">
  <div class="<?=$container_class?>">
    <div class="nav nav-underline">
      <?php if (user_needs_registration($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'])) {
      $target = "Club Registration";?>
      <span class="text-dark me-3"><strong>Club Registration</strong></span>
    <?php } else if (!isset($renewalName)) {
      $target = "Membership Renewal System";?>
      <span class="text-dark me-3">Membership Renewal System</span>
    <?php } else {
      $target = $renewalName;?>
      <span class="text-dark me-3"><strong><?=htmlspecialchars($renewalName)?></strong></span>
      <?php } ?>
      <?php if (isset($renewal_trap) && $renewal_trap) { ?>
        <a class="" href="<?php echo autoUrl("renewal/go")?>">Return to <?= $target ?></a>
      <?php } ?>
    </div>
  </div>
</div>
