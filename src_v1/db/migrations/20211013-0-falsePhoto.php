<?php

// SET PERMISSION TO FALSE ON NOTICEBOARD
$db->query("UPDATE `memberPhotography` SET `Noticeboard` = FALSE;");