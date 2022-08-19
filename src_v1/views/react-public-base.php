<?php

include BASE_PATH . "views/head.php";

?>
<div id="root"></div>
<?php

$footer = new SCDS\Footer();
$footer->addJs(getCompiledAsset('public-react-router.js'));
$footer->showContent(false);
$footer->render();
