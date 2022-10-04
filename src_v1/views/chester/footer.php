<?php

$districts = json_decode(file_get_contents(BASE_PATH . 'includes/regions/regions.json'), true);
$counties = json_decode(file_get_contents(BASE_PATH . 'includes/regions/counties.json'), true);
$time = new DateTime('now', new DateTimeZone('Europe/London'));

?>

</div>


<!-- THE HEPPELL FOOTER -->
<footer>

  <!-- COVID ALERT ADVERT -->
  <?php
  $covidVideos = [
    'https://myswimmingclub.uk/assets/covid/act-like-youve-got-it.mov',
  ];

  $covidMobileVideos = [
    'https://myswimmingclub.uk/assets/covid/act-like-youve-got-it-mobile.mov',
    'https://myswimmingclub.uk/assets/covid/anyone-can-get-it-mobile.mov',
    'https://myswimmingclub.uk/assets/covid/anyone-can-spread-it-mobile.mov'
  ];
  ?>

  <!-- <div class="mt-3 mb-n3 text-center" style="background: #000000;">
    <div class="container-xl">
      <video class="d-none d-sm-block mx-auto my-0 p-0 img-fluid" autoplay loop muted playsinline>
        <source src="<?= htmlspecialchars($covidVideos[rand(0, sizeof($covidVideos) - 1)]) ?>" type="video/mp4" />
        A COVID-19 video message appears here but your browser does not support the video element.
      </video>
      <video class="d-block d-sm-none mx-auto my-0 p-0 img-fluid" autoplay loop muted playsinline>
        <source src="<?= htmlspecialchars($covidMobileVideos[rand(0, sizeof($covidMobileVideos) - 1)]) ?>" type="video/mp4" />
        A COVID-19 video message appears here but your browser does not support the video element.
      </video>
    </div>
  </div> -->

  <div class="cls-global-footer cls-global-footer-inverse cls-global-footer-body d-print-none mt-3 pb-0 focus-highlight">
    <div class="<?php if (isset($this->fluidContainer) && $this->fluidContainer == true) { ?>container-fluid<?php } else { ?>container-xl<?php } ?>">
      <div class="row">
        <div class="col-lg-6">
          <div class="row">
            <div class="col">
              <address>
                <?php $addr = json_decode(app()->tenant->getKey('CLUB_ADDRESS')); ?>
                <strong><?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?></strong><br>
                <?php
                if ($addr) {
                  for ($i = 0; $i < sizeof($addr); $i++) { ?>
                    <?= htmlspecialchars($addr[$i]) ?><br>
                <?php }
                } ?>
              </address>
              <!--<p><i class="fa fa-envelope fa-fw" aria-hidden="true"></i> <a href="mailto:enquiries@chesterlestreetasc.co.uk" target="new">E-Mail Us</a></p>-->
              <p><i class="fa fa-flag fa-fw" aria-hidden="true"></i> <a href="<?= autoUrl("reportanissue?url=" . urlencode(app('request')->curl)) ?>">Report an issue with this page</a>
              </p>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="row">
            <div class="col-sm-6 col-lg-6">
              <ul class="list-unstyled cls-global-footer-link-spacer">
                <li><strong>Membership System Support</strong></li>
                <li>
                  <a href="<?= autoUrl("privacy") ?>" target="_blank" title="<?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?> Privacy Policy">
                    Our Privacy Policy
                  </a>
                </li>
                <li>
                  <a href="<?= htmlspecialchars(autoUrl('help-and-support', false)) ?>" title="Help and Support">
                    Help and Support
                  </a>
                </li>
                <li>
                  <a href="https://membership.git.myswimmingclub.uk/whats-new/" target="_blank" title="New membership system features">
                    What's new?
                  </a>
                </li>
                <li>
                  <a href="<?= autoUrl("notify") ?>" target="_self" title="About our Notify Email Service">
                    Emails from us
                  </a>
                </li>
                <li>
                  <a href="https://github.com/Chester-le-Street-ASC/Membership" target="_blank" title="Membership by CLSASC on GitHub">
                    GitHub
                  </a>
                </li>
              </ul>
            </div>
            <div class="col-sm-6 col-lg-6">
              <ul class="list-unstyled cls-global-footer-link-spacer">
                <li><strong>Related Sites</strong></li>
                <li><a title="British Swimming" target="_blank" href="https://www.swimming.org/britishswimming/">British
                    Swimming</a></li>
                <li><a title="the Amateur Swimming Association" target="_blank" href="https://www.swimming.org/swimengland/">Swim England</a></li>
                <li><a title="<?= htmlspecialchars($districts[app()->tenant->getKey('ASA_DISTRICT')]['title']) ?>" target="_blank" href="<?= htmlspecialchars($districts[app()->tenant->getKey('ASA_DISTRICT')]['website']) ?>"><?= htmlspecialchars($districts[app()->tenant->getKey('ASA_DISTRICT')]['name']) ?></a></li>
                <li><a title="<?= htmlspecialchars($counties[app()->tenant->getKey('ASA_COUNTY')]['title']) ?>" target="_blank" href="<?= htmlspecialchars($counties[app()->tenant->getKey('ASA_COUNTY')]['website']) ?>"><?= htmlspecialchars($counties[app()->tenant->getKey('ASA_COUNTY')]['name']) ?></a></li>
              </ul>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="cls-global-footer-legal">
    <div class="<?php if (isset($this->fluidContainer) && $this->fluidContainer == true) { ?>container-fluid<?php } else { ?>container-xl<?php } ?>">
      <div class="row align-items-center">
        <div class="col-sm-auto source-org vcard copyright">
          <div class="row no-gutters">
            <div class="col-auto">
              <a href="https://myswimmingclub.uk" target="_blank" title="Swimming Club Data Systems Website">
                <img src="<?= autoUrl("img/corporate/scds.png", false) ?>" width="100">
              </a>
              <div class="d-block d-sm-none mb-3"></div>
            </div>
          </div>

        </div>
        <div class="col">

          <?php
          global $time_start;
          $time_end = microtime(true);

          $seconds = $time_end - $time_start;
          ?>
          <p class="hidden-print mb-1">
            Membership is designed and built by <a class="text-white" href="https://www.myswimmingclub.uk" target="_blank">Swimming Club Data Systems</a>. Licenced to <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?>.
          </p>
          <p class="mb-1">Page rendered in <?= number_format($seconds, 3) ?> seconds. <?php if (defined('SOFTWARE_VERSION')) { ?>Software version <?= mb_substr(SOFTWARE_VERSION, 0, 7); ?>.<?php } ?>
          </p>
          <p class="mb-0">
            &copy; <?= $time->format('Y') ?> <span class="org fn">Swimming Club Data Systems</span>. Swimming Club Data Systems is not responsible
            for the content of external sites.
          </p>
        </div>
      </div>
    </div>
  </div><!-- /.container -->
</footer>

<div id="app-js-info" data-root="<?= htmlspecialchars(autoUrl("")) ?>" data-check-login-url="<?= htmlspecialchars(autoUrl("check-login.json")) ?>" data-service-worker-url="<?= htmlspecialchars(autoUrl("sw.js", false)) ?>"></div>

<!-- Modals and Other Hidden HTML -->
<?php

$script = autoUrl(getCompiledAsset('main.js'), false);

?>
<script rel="preload" src="<?= htmlspecialchars($script) ?>"></script>
<?php if (!isset($_SESSION['TENANT-' . app()->tenant->getId()]['PWA']) || !$_SESSION['TENANT-' . app()->tenant->getId()]['PWA']) { ?>
  <script defer src="https://static.chesterlestreetasc.co.uk/global/headers/GlobalNavigation.js"></script>
<?php } ?>
<?php if (isset($use_website_menu) && $use_website_menu) { ?>
  <script defer src="https://static.chesterlestreetasc.co.uk/global/headers/MainSiteMenu.js"></script>
<?php } ?>
<script src="<?= htmlspecialchars(autoUrl("js/app.js", false)) ?>"></script>

<?php if (isset($this->js)) { ?>
  <!-- Load per page JS -->
  <?php foreach ($this->js as $script) { ?>
    <script <?php if ($script['module']) { ?>type="module" <?php } ?> src="<?= htmlspecialchars($script['url']) ?>"></script>
  <?php } ?>
<?php } ?>

</body>

</html>