<?php
include '../app/config/config.php';

include $aConfigs['configs']['BASE_PATH'].'Liber.php';
Liber::loadConfig($aConfigs);

Liber::run();

?>