<?php

$time = new DateTime('now', new DateTimeZone('Europe/London'));

?>

</div>

<!-- THE HEPPELL FOOTER -->
<?php if ($this->chrome) { ?>
  <footer>
    <div class="cls-global-footer cls-global-footer-inverse cls-global-footer-body d-print-none mt-3 pb-0 focus-highlight">
      <div class="<?php if (isset($this->fluidContainer) && $this->fluidContainer == true) { ?>container-fluid<?php } else { ?>container-xl<?php } ?>">
        <div class="row">
          <div class="col-lg-6">
            <div class="row">
              <div class="col-sm-6">
                <address>
                  <strong>Swimming Club Data Systems</strong><br>
                  Newcastle-upon-Tyne
                </address>
                <!--<p><i class="fa fa-envelope fa-fw" aria-hidden="true"></i> <a href="mailto:enquiries@chesterlestreetasc.co.uk" target="new">E-Mail Us</a></p>-->
                <p><i class="fa fa-flag fa-fw" aria-hidden="true"></i> <a href="<?= htmlspecialchars('mailto:support@myswimmingclub.uk') ?>">Report an issue with this page</a>
                </p>
              </div>
              <div class="col-sm-6">
                <ul class="list-unstyled cls-global-footer-link-spacer">
                  <?php if (!isset($_SESSION['SCDS-SuperUser'])) { ?>
                    <li><strong>Admin</strong></li>
                    <li>
                      <a href="<?= htmlspecialchars(autoUrl("admin")) ?>" title="Sign in to your admin account">
                        Login
                      </a>
                    </li>
                  <?php } ?>
                </ul>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="row">
              <div class="col-sm-6 col-lg-6">
                <ul class="list-unstyled cls-global-footer-link-spacer">
                  <li><strong>Membership System Support</strong></li>
                  <li>
                    <a href="<?= htmlspecialchars(autoUrl('help-and-support')) ?>" title="Help and Support">
                      Help and Support
                    </a>
                  </li>
                  <li>
                    <a href="https://forms.office.com/Pages/ResponsePage.aspx?id=eUyplshmHU2mMHhet4xottqTRsfDlXxPnyldf9tMT9ZUODZRTFpFRzJWOFpQM1pLQ0hDWUlXRllJVS4u" target="_blank" title="Report email abuse">
                      Report mail abuse
                    </a>
                  </li>
                  <li>
                    <a href="https://membership.git.myswimmingclub.uk/whats-new/" target="_blank" title="New membership system features">
                      What's new?
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
                  <li><img class="fa fa-fw" src="<?= htmlspecialchars(autoUrl('img/stripe/climate/badge.svg')) ?>" alt=""> <a title="SCDS is a Stripe Climate Member contributing to remove CO2 from the atmosphere" href="https://climate.stripe.com/pkIT9H" target="_blank">Carbon Removal</a></li>
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
          <div class="col-sm-auto">
            <a href="https://myswimmingclub.uk" target="_blank" title="Swimming Club Data Systems Website">
              <img src="<?= autoUrl("img/corporate/scds.png") ?>" width="100">
            </a>
            <div class="d-block d-sm-none mb-3"></div>
          </div>
          <div class="col">

            <?php if (defined('SOFTWARE_VERSION')) { ?>
              <p class="mb-2">
                Software version <?= mb_substr(SOFTWARE_VERSION, 0, 7); ?>.
              </p>
            <?php } ?>

            <p class="mb-0 source-org vcard copyright">
              &copy; <?= $time->format('Y') ?> <span class="org fn">Swimming Club Data Systems</span>. Swimming Club Data Systems is not responsible
              for the content of external sites.
            </p>
          </div>
        </div>
      </div>
    </div><!-- /.container -->
  </footer>
<?php } ?>

<div id="app-js-info" data-root="<?= htmlspecialchars(autoUrl("")) ?>" data-service-worker-url="<?= htmlspecialchars(autoUrl("sw.js")) ?>"></div>

<!-- Modals and Other Hidden HTML -->
<?php

$script = autoUrl(getCompiledAsset('main.js'));

?>
<script rel="preload" src="<?= htmlspecialchars($script) ?>"></script>

<?php if (isset($this->js)) { ?>
  <!-- Load per page JS -->
  <?php foreach ($this->js as $script) {
  ?><script <?php if ($script['module']) { ?>type="module" <?php } ?> src="<?= htmlspecialchars($script['url']) ?>"></script><?php
                                                                                                                            }
                                                                                                                          } ?>

<?php if (!bool(getenv('IS_DEV'))) { ?>
  <!-- Cloudflare Web Analytics -->
  <script defer src='https://static.cloudflareinsights.com/beacon.min.js' data-cf-beacon='{"token": "579ac2dc2ea54799918144a5e7d894ef"}'></script><!-- End Cloudflare Web Analytics -->
<?php } ?>

</body>

</html>