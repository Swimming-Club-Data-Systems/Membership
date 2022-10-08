<?php

if (isset($use_website_menu)) {
    define('USE_CLS_MENU', $use_website_menu && app()->tenant->isCLS());
}

if (!function_exists('chesterStandardMenu')) {
    function chesterStandardMenu()
    {

        $db = app()->db;
        $tenant = app()->tenant;
        $user = null;
        if (isset(app()->user)) {
            /** @var User $user */
            $user = app()->user;
        }
        $use_website_menu = false;
        if (defined('USE_CLS_MENU')) {
            $use_website_menu = USE_CLS_MENU;
        }
        global $allow_edit;
        global $exit_edit;
        global $edit_link;

        $menu = [];

        if ($user) {

            $key = 'USER_APP_MENU_JSON_' . $user->getId();

            if (isset($_SESSION[$key])) {
                $menu = json_decode($_SESSION[$key], true);
            } else {
                try {
                    $client = new GuzzleHttp\Client();
                    // X-SCDS-INTERNAL-KEY
                    $res = $client->request('GET', getenv('INTERNAL_API_BASE_URL') . '/internal/application-menu/' . $user->getId(), [
                        'headers' => [
                            'X-SCDS-INTERNAL-KEY' => getenv('INTERNAL_KEY'),
                        ]
                    ]);
                    $data = (string) $res->getBody();
                    $_SESSION[$key] = $data;
                    $menu = json_decode($data, true);
                } catch (\GuzzleHttp\Exception\ClientException $e) {
                    // ignore the error, no menu will be provided
                }
            }
        }

        $canPayByCard = false;
        if (getenv('STRIPE') && app()->tenant->getStripeAccount() && app()->tenant->getBooleanKey('GALA_CARD_PAYMENTS_ALLOWED')) {
            $canPayByCard = true;
        }

        $haveSquadReps = false;
        $getRepCount = $db->prepare("SELECT COUNT(*) FROM squadReps INNER JOIN users ON squadReps.User = users.UserID WHERE users.Tenant = ?");
        $getRepCount->execute([
            app()->tenant->getId(),
        ]);
        if ($getRepCount->fetchColumn() > 0) {
            $haveSquadReps = true;
        }

        $hasNotifyAccess = false;
        if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel']) && $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent') {
            $getNotify = $db->prepare("SELECT COUNT(*) FROM (SELECT User FROM squadReps UNION SELECT User FROM listSenders) AS T WHERE T.User = ?");
            $getNotify->execute([
                $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
            ]);
            if ($getNotify->fetchColumn()) {
                $hasNotifyAccess = true;
            }
        }

        $isTeamManager = false;
        if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel']) && $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent') {
            $date = new DateTime('-1 day', new DateTimeZone('Europe/London'));
            $getGalas = $db->prepare("SELECT COUNT(*) FROM teamManagers INNER JOIN galas ON teamManagers.Gala = galas.GalaID WHERE teamManagers.User = ? AND galas.GalaDate >= ?");
            $getGalas->execute([
                $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
                $date->format("Y-m-d")
            ]);
            if ($getGalas->fetchColumn()) {
                $isTeamManager = true;
            }
        }

        $logos = $tenant->getKey('LOGO_DIR');

        ?>

        <?php if (!(isset($_SESSION['TENANT-' . app()->tenant->getId()]['UserID']) && user_needs_registration($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'])) && (!isset($use_website_menu) || !$use_website_menu)) { ?>
        <div class="collapse navbar-collapse offcanvas-collapse" id="chesterNavbar">
            <ul class="navbar-nav me-auto">
                <?php if (!empty($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn'])) { ?>
                    <li class="nav-item">
                        <a href="<?= htmlspecialchars(autoUrl("")) ?>"
                           class="text-dark text-decoration-none fw-bold">
                            <?php if ($logos) { ?>
                                <img src="<?= htmlspecialchars(getUploadedAssetUrl($logos . 'logo-75.png')) ?>"
                                     alt="Home" class="img-fluid"
                                     style="height: 2rem">
                            <?php } else { ?>
                                Home
                            <?php } ?>
                        </a>
                    </li>

                    <?php foreach ($menu as $item) { ?>
                        <?php if (sizeof($item['children'])) { ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                                   aria-expanded="false">
                                    <?= htmlspecialchars($item['name']) ?>
                                </a>
                                <ul class="dropdown-menu">
                                    <?php foreach ($item['children'] as $subItem) { ?>
                                        <li><a class="dropdown-item"
                                               href="<?= htmlspecialchars($subItem['href']) ?>"><?= htmlspecialchars($subItem['name']) ?></a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </li>
                        <?php } else { ?>
                            <li class="nav-item">
                                <a class="nav-link"
                                   href="<?= htmlspecialchars($item['href']) ?>"><?= htmlspecialchars($item['name']) ?></a>
                            </li>
                        <?php } ?>
                    <?php } ?>

                <?php } ?>
                <?php if (empty($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn'])) { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= htmlspecialchars(autoUrl("login")) ?>">Login</a>
                    </li>
                    <?php if (isset(app()->tenant) && app()->tenant->getKey('ASA_CLUB_CODE') == 'UOSZ' && false) { ?>
                        <li class="nav-item">
                            <a class="nav-link"
                               href="<?= htmlspecialchars(autoUrl("register/university-of-sheffield")) ?>">Sign Up
                                (Trials)</a>
                        </li>
                    <?php } ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= htmlspecialchars(autoUrl("timetable")) ?>">Timetable</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= htmlspecialchars(autoUrl("timeconverter")) ?>">Time Converter</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= htmlspecialchars(autoUrl("log-books")) ?>">Log Books</a>
                    </li>
                    <?php if (app()->tenant->getKey('CLUB_WEBSITE')) { ?>
                        <li class="nav-item d-lg-none">
                            <a class="nav-link" href="<?= htmlspecialchars(app()->tenant->getKey('CLUB_WEBSITE')) ?>"
                               target="_blank">Club Website <i class="fa fa-external-link" aria-hidden="true"></i></a>
                        </li>
                    <?php } ?>
                <?php } ?>
            </ul>
            <?php if (!empty($_SESSION['TENANT-' . app()->tenant->getId()]['LoggedIn'])) {
                $currentUser = app()->user;
                $user_name = preg_replace("/( +)/", '&nbsp;', htmlspecialchars($currentUser->getFirstName())); ?>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button"
                           aria-haspopup="true" aria-expanded="false">
                            <?= $user_name ?> <i class="fa fa-user-circle-o" aria-hidden="true"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <span class="dropdown-item-text">Signed&nbsp;in&nbsp;as&nbsp;<strong><?= $user_name ?></strong></span>
                            <div class="dropdown-divider"></div>
                            <?php $perms = $currentUser->getPrintPermissions();
                            if (sizeof($perms) > 1) { ?>
                                <h6 class="dropdown-header">Switch account mode</h6>
                                <?php foreach ($perms as $perm => $name) { ?>
                                    <a class="dropdown-item"
                                       href="<?= autoUrl("account-switch?type=" . urlencode($perm)) ?>"><?= htmlspecialchars($name) ?><?php if ($perm == $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel']) { ?>
                                            <i class="text-primary fa fa-check-circle fa-fw"
                                               aria-hidden="true"></i><?php } ?></a>
                                <?php } ?>
                                <div class="dropdown-divider"></div>
                            <?php } ?>
                            <h6 class="dropdown-header">Account settings</h6>
                            <a class="dropdown-item" href="/my-account/profile">
                                Profile
                            </a>
                            <a class="dropdown-item" href="/my-account/email-options">
                                Email Options
                            </a>
                            <a class="dropdown-item" href="<?= htmlspecialchars(autoUrl("emergency-contacts")) ?>">
                                Emergency Contacts
                            </a>
                            <a class="dropdown-item" href="/my-account/password-and-security">
                                Password and Security
                            </a>
                            <a class="dropdown-item"
                               href="<?= htmlspecialchars(autoUrl("my-account/notify-history")) ?>">
                                Message History
                            </a>
                            <?php if ($user->hasPermission('Parent')) { ?>
                                <a class="dropdown-item"
                                   href="<?= htmlspecialchars(autoUrl("account-switch?type=Parent&redirect=" . urlencode(autoUrl("my-account/add-member")))) ?>">
                                    Add Member
                                </a>
                            <?php } ?>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" target="_blank" href="https://docs.myswimmingclub.uk/">Help</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="/logout">Sign out</a>
                        </div>
                    </li>
                </ul>
            <?php } ?>
        </div>
        </nav>
    <?php } ?>

    <?php }
}

chesterStandardMenu();
