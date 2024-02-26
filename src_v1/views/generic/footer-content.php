<?php

$districts = json_decode(file_get_contents(BASE_PATH . 'includes/regions/regions.json'), true);
$counties = json_decode(file_get_contents(BASE_PATH . 'includes/regions/counties.json'), true);
$time = new DateTime('now', new DateTimeZone('Europe/London'));
$tenant = app()->tenant;
$logos = $tenant->getKey('LOGO_DIR')

?>

<!-- THE HEPPELL FOOTER -->
<footer>

  <div class="cls-global-footer cls-global-footer-inverse cls-global-footer-body d-print-none mt-3 pb-0 focus-highlight">
    <div class="<?php if (isset($this->fluidContainer) && $this->fluidContainer == true) { ?>container-fluid<?php } else { ?>container-xl<?php } ?>">
      <div class="row">
        <div class="col-lg-6">
          <div class="row">
            <div class="col">
              <address>
                <?php $addr = json_decode((string) app()->tenant->getKey('CLUB_ADDRESS')); ?>
                <strong><?= htmlspecialchars((string) app()->tenant->getKey('CLUB_NAME')) ?></strong><br>
                <?php if ($addr) {
                  for ($i = 0; $i < sizeof($addr); $i++) { ?>
                    <?= htmlspecialchars((string) $addr[$i]) ?><br>
                <?php }
                } ?>
              </address>
              <!--<p><i class="fa fa-envelope fa-fw" aria-hidden="true"></i> <a href="mailto:enquiries@chesterlestreetasc.co.uk" target="new">E-Mail Us</a></p>-->
              <p><i class="fa fa-flag fa-fw" aria-hidden="true"></i> <a href="<?= htmlspecialchars("/report-an-issue?url=" . urlencode((string) app('request')->curl)) ?>">Report an issue with this page</a>
              </p>
              <p><i class="fa fa-info-circle fa-fw" aria-hidden="true"></i> <a href="/about">Support information</a>
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
                  <a href="/privacy" target="_blank" title="<?= htmlspecialchars((string) app()->tenant->getKey('CLUB_NAME')) ?> Privacy Policy">
                    Our Privacy Policy
                  </a>
                </li>
                <li>
                  <a href="https://docs.myswimmingclub.uk" title="Help and Support">
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
                  <a href="https://forms.office.com/Pages/ResponsePage.aspx?id=eUyplshmHU2mMHhet4xottqTRsfDlXxPnyldf9tMT9ZUODZRTFpFRzJWOFpQM1pLQ0hDWUlXRllJVS4u" target="_blank" title="Report email abuse">
                    Report mail abuse
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
                <li><a title="<?= htmlspecialchars((string) $districts[app()->tenant->getKey('ASA_DISTRICT')]['title']) ?>" target="_blank" href="<?= htmlspecialchars((string) $districts[app()->tenant->getKey('ASA_DISTRICT')]['website']) ?>"><?= htmlspecialchars((string) $districts[app()->tenant->getKey('ASA_DISTRICT')]['name']) ?></a></li>
                <li><a title="<?= htmlspecialchars((string) $counties[app()->tenant->getKey('ASA_COUNTY')]['title']) ?>" target="_blank" href="<?= htmlspecialchars((string) $counties[app()->tenant->getKey('ASA_COUNTY')]['website']) ?>"><?= htmlspecialchars((string) $counties[app()->tenant->getKey('ASA_COUNTY')]['name']) ?></a></li>
                <?php if (app()->tenant->getKey('CLUB_WEBSITE')) { ?>
                  <li><a title="<?= htmlspecialchars((string) app()->tenant->getKey('CLUB_NAME')) ?> Website" target="_blank" href="<?= htmlspecialchars((string) app()->tenant->getKey('CLUB_WEBSITE')) ?>"><?= htmlspecialchars((string) app()->tenant->getKey('CLUB_NAME')) ?></a></li>
                <?php } ?>
              </ul>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="cls-global-footer-legal">
    <div class="<?php if (isset($this->fluidContainer) && $this->fluidContainer == true) { ?>container-fluid<?php } else { ?>container-xl<?php } ?>">
      <div class="row align-items-center footer-auto-padding-bottom">
        <div class="col-sm-auto source-org vcard copyright">
          <div class="row no-gutters">
            <div class="col-auto">
              <a href="https://myswimmingclub.uk" target="_blank" title="Swimming Club Data Systems Website">
                <img src="<?= autoUrl("img/corporate/scds.png", false) ?>" width="100">
              </a>
              <div class="d-block d-sm-none mb-3"></div>
            </div>
            <!-- <?php if ($logos) { ?>
            <div class="col-auto d-none d-md-flex">
              <div style="height: 100px; padding: 0 12.5px 0 12.5px; max-width: 100%;" class="bg-white d-flex">
                <img src="<?= htmlspecialchars((string) getUploadedAssetUrl($logos . 'logo-75.png')) ?>" srcset="<?= htmlspecialchars((string) getUploadedAssetUrl($logos . 'logo-75@2x.png')) ?> 2x, <?= htmlspecialchars((string) getUploadedAssetUrl($logos . 'logo-75@3x.png')) ?> 3x" alt="<?= htmlspecialchars((string) $tenant->getName()) ?>" class="img-fluid my-auto">
              </div>
            </div>
            <?php } ?> -->
          </div>

        </div>
        <div class="col">

          <?php
          global $time_start;
          $time_end = microtime(true);

          $seconds = $time_end - $time_start;
          ?>
          <p class="hidden-print mb-1">
            Membership is designed and built by <a class="text-white" href="https://www.myswimmingclub.uk" target="_blank">Swimming Club Data Systems</a>. Licenced to <?= htmlspecialchars((string) app()->tenant->getKey('CLUB_NAME')) ?>.
          </p>
          <p class="mb-1">Page rendered in <?= number_format($seconds, 3) ?> seconds. <?php if (defined('SOFTWARE_VERSION')) { ?>Software version <?= mb_substr((string) SOFTWARE_VERSION, 0, 7); ?>.<?php } ?>
          </p>
          <p class="mb-0">
            &copy; <?= $time->format('Y') ?> <span class="org fn">Swimming Club Data Systems</span>. Swimming Club Data Systems is not responsible
            for the content of external sites.
          </p>
        </div>
      </div>
    </div>
  </div><!-- /.container -->
  <div id="app-js-info" data-root="<?= htmlspecialchars((string) autoUrl("")) ?>" data-check-login-url="<?= htmlspecialchars((string) autoUrl("check-login.json")) ?>" data-service-worker-url="<?= htmlspecialchars((string) autoUrl("sw.js")) ?>"></div>
</footer>