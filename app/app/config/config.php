<?php

$project    = realpath((dirname(__FILE__))."/../../")."/";
$aConfigs   = Array(
                "configs" => Array(
                    "APP_MODE"      => "PROD",
                    "FUNKY_PATH"    => "static/",
					"LAYOUT"		=> "",
                    "VERSION"       => "1.1"
                ),

                "routes"=>Array(),

                "dbconfig"=>Array(
                    "DEV"  => Array("localhost","tl","root","root", "mysql"),
                    "PROD" => Array("localhost","tl","root","root", "mysql")
                )
            );

$route      = &$aConfigs["routes"];

$route["/"]["*"]                 = Array("MainController", "*");
$route["/notfound"]["*"]         = Array("NotFoundController", "index");
$route["/comment"]["*"]          = Array("CommentController", "comment");
$route["/admin"]["*"]            = Array("AdminController", "*");
$route["/admin/setting"]["*"]    = Array("AdminSettingController", "*");
$route["/admin/content"]["*"]    = Array("AdminContentController", "*");
$route["/admin/topic"]["*"]      = Array("AdminTopicController", "*");
$route["/admin/comment"]["*"]    = Array("AdminCommentController", "*");
?>