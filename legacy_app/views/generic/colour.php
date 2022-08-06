<style>
  <?php if (config('SYSTEM_COLOUR')) {

  ?>@media (prefers-color-scheme: light) {
    .membership-header {
      background-color: <?= config('SYSTEM_COLOUR') ?>;
    }

    .club-name-header {
      background-color: <?= config('SYSTEM_COLOUR') ?>;
    }

    .logo-text-shadow {
      text-shadow: 1px 1px 1px rgba(0, 0, 0, .5);
    }
  }

  :root {
    --tenant-brand-colour: <?= config('SYSTEM_COLOUR') ?>;
    <?php if (config('SYSTEM_COLOUR') && getContrastColor(config('SYSTEM_COLOUR'))) { ?>--tenant-colour: #212529;
    <?php } else { ?>--tenant-colour: #ffffff;
    <?php } ?>
  }

  .bg-tenant-brand {
    background-color: var(--tenant-brand-colour);
  }

  .tenant-colour {
    color: var(--tenant-colour);
  }

  <?php } ?>
</style>