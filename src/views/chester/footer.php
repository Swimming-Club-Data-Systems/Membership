</div>

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
  <div class="container">
    <video class="d-none d-sm-block mx-auto my-0 p-0 img-fluid" autoplay loop muted playsinline>
      <source src="<?=htmlspecialchars($covidVideos[rand(0, sizeof($covidVideos) - 1)])?>" type="video/mp4" />
      A COVID-19 video message appears here but your browser does not support the video element.
    </video>
    <video class="d-block d-sm-none mx-auto my-0 p-0 img-fluid" autoplay loop muted playsinline>
      <source src="<?=htmlspecialchars($covidMobileVideos[rand(0, sizeof($covidMobileVideos) - 1)])?>" type="video/mp4" />
      A COVID-19 video message appears here but your browser does not support the video element.
    </video>
  </div>
</div> -->

<!-- THE HEPPELL FOOTER -->
<div class="cls-global-footer cls-global-footer-sponsors d-print-none">
  <?php if (isset($this->fluidContainer) && $this->fluidContainer == true) { ?>
  <div class="container-fluid">
    <?php } else { ?>
    <div class="container">
      <?php } ?>
      <div class="row align-items-center text-center justify-content-center">
        <div class="col-xs-12 col-sm-6 col-md-4 col-lg">
          <a href="http://www.gblf.co.uk" target="_blank">
            <img class="img-responsive sponsor center-block"
              src="https://static.chesterlestreetasc.co.uk/global/img/sponsors/white/gblf.png"
              srcset="https://static.chesterlestreetasc.co.uk/global/img/sponsors/white/gblf@2x.png 2x, https://static.chesterlestreetasc.co.uk/global/img/sponsors/white/gblf@3x.png 3x"
              alt="Gordon Brown Law Firm Logo" />
          </a>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-4 col-lg">
          <a href="http://www.ukmail.com" target="_blank">
            <img class="img-responsive sponsor center-block"
              src="https://static.chesterlestreetasc.co.uk/global/img/sponsors/white/ukmail.png"
              srcset="https://static.chesterlestreetasc.co.uk/global/img/sponsors/white/ukmail@2x.png 2x, https://static.chesterlestreetasc.co.uk/global/img/sponsors/white/ukmail@3x.png 3x"
              alt="UK Mail Logo" />
          </a>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-4 col-lg">
          <a href="http://www.nessswimwear.co.uk" target="_blank">
            <img class="img-responsive sponsor center-block"
              src="https://static.chesterlestreetasc.co.uk/global/img/sponsors/white/ness.png"
              srcset="https://static.chesterlestreetasc.co.uk/global/img/sponsors/white/ness@2x.png 2x, https://static.chesterlestreetasc.co.uk/global/img/sponsors/white/ness@3x.png 3x"
              alt="NESS Swimwear Logo" />
          </a>
        </div>
        <div class="clearfix visible-sm"></div>
        <div class="col-xs-12 col-sm-6 col-md-4 col-lg">
          <a href="http://www.michaelenglishroofing.com" target="_blank">
            <img class="img-responsive sponsor center-block"
              src="https://static.chesterlestreetasc.co.uk/global/img/sponsors/white/menglish.png"
              srcset="https://static.chesterlestreetasc.co.uk/global/img/sponsors/white/menglish@2x.png 2x, https://static.chesterlestreetasc.co.uk/global/img/sponsors/white/menglish@3x.png 3x"
              alt="Michael English Roofing Logo" />
          </a>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-4 col-lg">
          <a href="http://www.harlandsaccountants.co.uk" target="_blank">
            <img class="img-responsive sponsor center-block"
              src="https://static.chesterlestreetasc.co.uk/global/img/sponsors/white/harlands.png"
              srcset="https://static.chesterlestreetasc.co.uk/global/img/sponsors/white/harlands@2x.png 2x, https://static.chesterlestreetasc.co.uk/global/img/sponsors/white/harlands@3x.png 3x"
              alt="Harlands Accountants Logo" />
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="cls-global-footer cls-global-footer-inverse cls-global-footer-body d-print-none pb-0">
  <?php if (isset($this->fluidContainer) && $this->fluidContainer == true) { ?>
  <div class="container-fluid">
    <?php } else { ?>
    <div class="container">
      <?php } ?>
      <div class="row">
        <div class="col-lg-6">
          <div class="row">
            <div class="col-sm-6 col-lg-6">
              <address>
                <strong><?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?></strong><br>
                Burns Green Leisure Centre<br>
                Chester-le-Street<br>
                DH3 3QH
              </address>
              <p><i class="fa fa-envelope fa-fw" aria-hidden="true"></i> <a
                  href="mailto:enquiries@chesterlestreetasc.co.uk" target="new">E-Mail Us</a></p>
              <p class="mb-0"><i class="fa fa-commenting fa-fw" aria-hidden="true"></i> <a target="new"
                  href="mailto:websitefeedback@chesterlestreetasc.co.uk">Website Feedback</a></p>
              <p><i class="fa fa-flag fa-fw" aria-hidden="true"></i> <a
                  href="https://membership.chesterlestreetasc.co.uk/reportanissue?url=<?=urlencode(currentUrl())?>">Report
                  an issue with this page</a></p>
            </div>
            <div class="col-sm-6 col-lg-6">
              <ul class="list-unstyled cls-global-footer-link-spacer">
                <li><strong>Membership System Support</strong></li>
                <li>
                  <a href="https://www.chesterlestreetasc.co.uk/policies/privacy/" target="_blank"
                    title="CLS ASC General Privacy Policy">
                    Our Privacy Policy
                  </a>
                </li>
                <li>
                  <a href="https://www.chesterlestreetasc.co.uk/support/onlinemembership/" target="_blank"
                    title="Chester-le-Street ASC Help and Support">
                    Help and Support
                  </a>
                </li>
                <li>
                  <a href="https://membership.git.myswimmingclub.uk/whats-new/" target="_blank"
                    title="New membership system features">
                    What's new?
                  </a>
                </li>
                <li>
                  <a href="<?php echo autoUrl("notify"); ?>" target="_self" title="About our Notify Email Service">
                    Emails from us
                  </a>
                </li>
                <li>
                  <a href="https://github.com/Chester-le-Street-ASC/Membership" target="_blank"
                    title="Membership by CLSASC on GitHub">
                    Software by CLS ASC on GitHub
                  </a>
                </li>
              </ul>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="row">
            <div class="col-sm-6 col-lg-6">
              <!--<ul class="list-unstyled cls-global-footer-link-spacer">
		          <li><strong>Downloads</strong></li>
		          <li><i class="fa fa-file-pdf-o fa-fw"></i> <a title="Entry Form" target="_blank" href="http://www.chesterlestreetasc.co.uk/wp-content/uploads/2016/06/GalaEntryForm.pdf">Gala Entry Form</a></li>
		          <li><i class="fa fa-file-pdf-o fa-fw"></i> <a title="Order Form" target="_blank" href="http://www.chesterlestreetasc.co.uk/wp-content/uploads/2016/06/ClothingOrderFormChild.pdf">Children's Kit Order Form</a></li>
		          <li><i class="fa fa-file-pdf-o fa-fw"></i> <a title="Order Form" target="_blank" href="http://www.chesterlestreetasc.co.uk/wp-content/uploads/2016/06/ClothingOrderFormAdult.pdf">Adult Kit Order Form</a></li>
		        </ul>-->
              <ul class="list-unstyled cls-global-footer-link-spacer">
                <li><strong>Social Media and More</strong></li>
                <li><i class="fa fa-twitter fa-fw" aria-hidden="true"></i> <a title="CLSASC on Twitter" target="_blank"
                    href="https://twitter.com/CLSASC">Twitter</a></li>
                <li><i class="fa fa-facebook fa-fw" aria-hidden="true"></i> <a title="CLSASC on Facebook"
                    target="_blank" href="https://www.facebook.com/Chester-le-Street-ASC-349933305154137/">Facebook</a>
                </li>
                <li><i class="fa fa-rss fa-fw" aria-hidden="true"></i> <a title="Stay up to date with RSS"
                    target="_blank" href="https://www.chesterlestreetasc.co.uk/feed/">RSS Feeds</a></li>
                <li><i class="fa fa-github fa-fw" aria-hidden="true"></i> <a
                    title="CLSASC on GitHub - A Home for our Software Development Projects" target="_blank"
                    href="https://github.com/Chester-le-Street-ASC/">GitHub</a></li>
              </ul>
            </div>
            <div class="col-sm-6 col-lg-6">
              <ul class="list-unstyled cls-global-footer-link-spacer">
                <li><strong>Related Sites</strong></li>
                <li><a title="British Swimming" target="_blank" href="http://www.swimming.org/britishswimming/">British
                    Swimming</a></li>
                <li><a title="the Amateur Swimming Association" target="_blank"
                    href="http://www.swimming.org/swimengland/">Swim England</a></li>
                <li><a title="Swim England North East Region" target="_blank" href="http://asaner.org.uk/">Swim
                    England North East</a></li>
                <li><a title="Northumberland and Durham Swimming" target="_blank"
                    href="http://asaner.org.uk/northumberland-durham-swimming-association/">Northumberland &amp;
                    Durham Swimming</a></li>
              </ul>

              <p><strong>Committee Services</strong><br><a title="Login to G Suite" target="_blank"
                  href="http://mail.chesterlestreetasc.co.uk/">G Suite Login</a></p>

            </div>
          </div>
        </div>
      </div>
    </div> <!-- /.container -->
  </div>
</div>

<div class="cls-global-footer-legal d-print-none">
  <?php if (isset($this->fluidContainer) && $this->fluidContainer == true) { ?>
  <div class="container-fluid">
  <?php } else { ?>
  <div class="container">
  <?php } ?>
    <div class="row">
      <div class="col source-org vcard copyright">
        <?php
        global $time_start;
        $time_end = microtime(true);

        $seconds = $time_end - $time_start;
        ?>
        <p class="hidden-print">Designed and Built by Chester&#8209;le&#8209;Street ASC. Page rendered in <?=number_format($seconds, 3)?> seconds. <?php if (defined('SOFTWARE_VERSION')) { ?>Version <?=mb_substr(SOFTWARE_VERSION, 0, 7);?>.<?php } ?></p>
        <p class="mb-0" style="margin-bottom:0">&copy; <?=date("Y")?> <span class="org fn">Chester&#8209;le&#8209;Street ASC</span>. CLS ASC is not responsible for the content of external sites.</p>
      </div>
    </div>
  </div>
</div>

<div id="app-js-info" data-root="<?=htmlspecialchars(autoUrl(""))?>" data-service-worker-url="<?=htmlspecialchars(autoUrl("sw.js"))?>"></div>

<!-- Modals and Other Hidden HTML -->
<?php

$script = "";

try {
  $hash = file_get_contents(BASE_PATH . 'cachebuster.json');
  $hash = json_decode($hash);
  $hash = $hash->resourcesHash;
  $script = autoUrl('public/compiled/js/main.' . $hash . '.js');
} catch (Exception $e) {
  $script = autoUrl('public/compiled/js/main.js');
}


?>
<script rel="preload" src="<?=htmlspecialchars($script)?>"></script>
<?php if (!isset($_SESSION['TENANT-' . app()->tenant->getId()]['PWA']) || !$_SESSION['TENANT-' . app()->tenant->getId()]['PWA']) { ?>
<script defer src="https://static.chesterlestreetasc.co.uk/global/headers/GlobalNavigation.js"></script>
<script async src="<?=htmlspecialchars(autoUrl("public/js/Cookies.js"))?>"></script>
<?php } ?>
<?php if (isset($use_website_menu) && $use_website_menu) { ?>
<script defer src="https://static.chesterlestreetasc.co.uk/global/headers/MainSiteMenu.js"></script>
<?php } ?>
<script src="<?=htmlspecialchars(autoUrl("public/js/app.js"))?>"></script>

<?php if (isset($this->js)) { ?>
  <!-- Load per page JS -->
  <?php foreach ($this->js as $script) {
    ?><script src="<?=htmlspecialchars($script)?>"></script><?php
  }
} ?>

</body>

</html>
