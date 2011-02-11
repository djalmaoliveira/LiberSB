<?php
include "../app/config/config.php";
include $aConfigs["configs"]["BASE_PATH"]."Liber.php";
Liber::loadConfig($aConfigs);

// avoid cache
header("Pragma: public");
header("Expires: -1");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);
header("Cache-Control: no-cache");

Liber::run();

?>