<?php

$project    = realpath((dirname(__FILE__))."/../../")."/";

$aConfigs['configs']['APP_MODE']    = 'PROD';
$aConfigs['configs']['FUNKY_PATH']  = "static/";
$aConfigs['configs']['LAYOUT']      = "";
$aConfigs['configs']['VERSION']     = '2.0.1';
$aConfigs['routes']                 = Array();
$aConfigs['db']['default']          = Array("localhost","tl","root","root", "mysql");
$aConfigs['db']['DEV']              = Array("localhost","tl","root","root", "mysql");
$aConfigs['db']['PROD']             = Array("localhost","tl","root","root", "mysql");

$route      = &$aConfigs["routes"];

$route["/"]["*"]                 = Array("MainController");
$route["/notfound"]["*"]         = Array("NotFoundController");
$route["/comment"]["*"]          = Array("CommentController", "comment");
$route["/admin"]["*"]            = Array("AdminController");
$route["/admin/setting"]["*"]    = Array("AdminSettingController");
$route["/admin/content"]["*"]    = Array("AdminContentController");
$route["/admin/topic"]["*"]      = Array("AdminTopicController");
$route["/admin/comment"]["*"]    = Array("AdminCommentController");
?>