<?
include '../Liber/Liber.php';
Liber::setup();
Liber::conf('BASE_PATH', realpath('../Liber/').'/');
Liber::conf('APP_PATH', realpath('../app/').'/');
Liber::conf('APP_MODE', 'PROD');

/* content of config file */
function config_php($aData) {
    $file = '
<?php

$project    = realpath((dirname(__FILE__))."/../../")."/";
$aConfigs   = Array(
                "configs" => Array(
                    "APP_PATH"      => $project."app/",
                    "BASE_PATH"     => $project."Liber/",
                    "APP_MODE"      => "PROD",
                    "FUNKY_PATH"    => "static/",
                    "LAYOUT"        => "",
                    "VERSION"       => "1.1"
                ),

                "routes"=>Array(),

                "dbconfig"=>Array(
                    "DEV"  => Array("'.$aData['db']['server'].'","'.$aData['db']['database'].'","'.$aData['db']['user'].'","'.$aData['db']['password'].'", "mysql"),
                    "PROD" => Array("'.$aData['db']['server'].'","'.$aData['db']['database'].'","'.$aData['db']['user'].'","'.$aData['db']['password'].'", "mysql")
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
';
    return trim($file);
}

// return the content of index.php
function index_php($aData) {
    $file = '
<?php

// include Liber framework
include_once "../Liber/Liber.php";

// prepares enviroment to Liber application
Liber::loadApp( realpath("../app/")."/" );

// avoid cache
header("Pragma: public");
header("Expires: -1");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);
header("Cache-Control: no-cache");

Liber::run();
?>
';

    return trim($file);
}


/* =====================================================================================   */
Liber::loadHelper(Array('Form', 'Url'));
$oSession = Liber::loadClass('Session', true);
$action   = basename($_SERVER['SCRIPT_NAME']);
$error    = '';


if ( !isset($_REQUEST['step']) ) {
    $_REQUEST['step'] = 1;
}


if ( $_REQUEST['step'] == 'info' ) {
    @phpinfo();
    var_dump(get_loaded_extensions ());
}



// validations for step 2
if ( ($_REQUEST['step'])==2 and $_POST) {
    $oSession->val('database', $_POST);
    if ( ($aDbConfig = $oSession->val('database')) ) {
        Liber::$aDbConfig = Array('PROD'=>Array($aDbConfig['server'],$aDbConfig['database'],$aDbConfig['user'],$aDbConfig['password'], 'mysql'));
    }

    $db = Liber::db('PROD');
    if ( !$db ) {
        $error = "Wrong database connection, please fill correct informations.";
    }

    if ( $db and $db->query('show tables')->rowCount() > 0 ) {
        $error = 'There are some tables in database, please verify if it is a correct database name. ';
    }

    // return to previous step
    if ( $error ) {
        $_REQUEST['step'] = 1;
    }
} else {
    if ( ($aDbConfig = $oSession->val('database')) ) {
        Liber::$aDbConfig = Array('PROD'=>Array($aDbConfig['server'],$aDbConfig['database'],$aDbConfig['user'],$aDbConfig['password'], 'mysql'));
    }
}

// validations for step 3
if ( ($_REQUEST['step'])==3 and $_POST) {
    $oSession->val('app', $_POST);
    $error='';
    $oVal = Liber::loadClass('Validation', true);
    if ( ($errors = $oVal->validate($_POST['contact_email'], Validation::EMAIL))  ) {
        $error = "Contact Email: ".implode('', $errors)."<br/>";
    }

    if ( ($errors = $oVal->validate($_POST['facebook_url'], Validation::URL))  ) {
        $error .= "Facebook Url: ".implode('', $errors)."<br/>";
    }

    if ( ($errors = $oVal->validate($_POST['twitter_url'], Validation::URL))  ) {
        $error .= "Twitter Url: ".implode('', $errors)."<br/>";
    }

    if ( ($errors = $oVal->validate($_POST['login'], Validation::EMAIL))  ) {
        $error = "Login: ".implode('', $errors)."<br/>";
    }

    // return to previous step
    if ( $error ) {
        $_REQUEST['step'] = 2;
    }
}


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html xml:lang="pt-br" xmlns="http://www.w3.org/1999/xhtml" lang="pt-br">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Liber Simple Blog Install</title>
    <link rel="stylesheet" type="text/css" media="screen" href="../app/assets/css/admin.css">
    <script type="text/javascript" src="../app/assets/js/jquery.js"></script>
    <style>
        body {
            padding:10px;
        }
        .field_name {
            width:150px;
            float:left;
            padding:5px 0px 5px 0px;
        }

    </style>
</head>
<body>
    <h1>Welcome to Liber Simple Blog Wizard Installer</h1>
    <?
        $nav = '';
        switch( $_REQUEST['step'] ) {
            case 4:
                $nav = ' » <a href="?step=4">Step 4</a>';
            case 3:
                $nav = ' » Finished'.$nav;
            case 2:
                $nav = ' » Step 2'.$nav;
            case 1:
                $nav = '<a href="?step=1">Step 1</a>'.$nav;
        }
    ?>

    <h2><?=$nav?></h2>

    <?
        if ( $_REQUEST['step'] == 1 ) {
    ?>

        <div class='form_area'>
            <div id='content_area'>
                <h3>Step <?=$_REQUEST['step']?>: MySQL database settings</h3>
                <p>Please fill correct informations about your database connection.</p>
                <form method='post' action='<?=$action?>' id='frm' onsubmit='return false;'>
                    <?form_hidden_('step', '2')?>
                    <p>
                        <div class='field_name'>Server name:</div><?form_input_('server',$oSession->val('database'),"title='IP or Hostname of mysql server'")?>
                    </p>
                    <p>
                        <div class='field_name'>Database name:</div><?form_input_('database',$oSession->val('database'), "title='Existing database name'")?>
                    </p>
                    <p>
                        <div class='field_name'>User:</div><?form_input_('user',$oSession->val('database'), "title='Database User name'")?>
                    </p>
                    <p>
                        <div class='field_name'>Password:</div><?form_password_('password', $oSession->val('database'), "title='Database Password'")?>
                    </p>
                </form>
                <script>
                    function _next() {
                        $('#frm').submit();
                    }
                </script>
                <?  if ( isset($error) ) {?>
                    <p class='msg_error'><?=$error?></p>
                <?  }?>
                <?form_button_('btnContinue', 'Continue', 'onclick="_next()"')?>
            </div>
        </div>
    <?  }?>



    <?
        if ( $_REQUEST['step'] == 2 ) {
    ?>

        <div class='form_area'>
            <div id='content_area'>
                <h3>Step <?=$_REQUEST['step']?>: Application settings</h3>
                <p>Please fill correct informations about your application.</p>
                <form method='post' action='<?=$action?>' id='frm' onsubmit='return false;'>
                    <?form_hidden_('step', '3')?>
                    <p>
                        <div class='field_name'>Site Name:</div><?form_input_('site_name',$oSession->val('app'),"title='Put your site name.(i.e. my blog)'")?>
                    </p>
                    <p>
                        <div class='field_name'>Contact Email:</div><?form_input_('contact_email',$oSession->val('app'), "title='Put a default email address to receive messages.'")?>
                    </p>
                    <p>
                        <div class='field_name'>Facebook URL:</div><?form_input_('facebook_url',$oSession->val('app'), "title='If you have a Facebook account, put here your URL.'")?> (optional)
                    </p>
                    <p>
                        <div class='field_name'>Twitter URL:</div><?form_input_('twitter_url',$oSession->val('app'), "title='If you have a Twitter account, put here your URL.'")?> (optional)
                    </p>
                    <p>
                        <div class='field_name'>Login:</div><?form_input_('login',$oSession->val('app'), "title='Administrator User to allow access administration area.'")?>
                    </p>
                    <p>
                        <div class='field_name'>Password:</div><?form_password_('password',$oSession->val('app'), "title='Administrator Password'")?>
                    </p>

                </form>
                <script>
                    function _next() {
                        $('#frm').submit();
                    }
                </script>
                <?  if ( isset($error) ) {?>
                    <p class='msg_error'><?=$error?></p>
                <?  }?>
                <?form_button_('btnContinue', 'Continue', 'onclick="_next()"')?>
            </div>
        </div>
    <?  }

        if ( $_REQUEST['step'] == 3 and $_POST) {


            $schemes = Array();
            $schemes[] = "SET NAMES utf8;";
            $schemes[] = "SET foreign_key_checks = 0;";
            $schemes[] = "SET time_zone = 'SYSTEM';";
            $schemes[] = "SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';";

            $schemes[] = "CREATE TABLE `comment` (
                `comment_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `content_id` bigint(20) unsigned NOT NULL,
                `name` varchar(100) NOT NULL,
                `email` varchar(255) NOT NULL,
                `comment` text NOT NULL,
                `datetime` datetime NOT NULL,
                `status` char(1) NOT NULL,
                `netinfo` varchar(255) NOT NULL,
                PRIMARY KEY (`comment_id`),
                KEY `status` (`status`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";

            $schemes[] = "CREATE TABLE `config` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `site_name` varchar(255) NOT NULL,
                `contact_email` varchar(255) NOT NULL,
                `twitter_url` varchar(255) NOT NULL,
                `facebook_url` varchar(255) NOT NULL,
                PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ";

            $schemes[] = "CREATE TABLE `content` (
                `content_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `content_type_id` bigint(20) unsigned NOT NULL,
                `title` varchar(255) NOT NULL,
                `body` mediumtext NOT NULL,
                `datetime` datetime NOT NULL,
                `create_datetime` datetime NOT NULL,
                `permalink` varchar(255) NOT NULL,
                PRIMARY KEY (`content_id`),
                UNIQUE KEY `content_type_id` (`content_type_id`,`title`),
                UNIQUE KEY `permalink` (`permalink`,`content_type_id`),
                KEY `datetime` (`datetime`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ";

            $schemes[] = "CREATE TABLE `content_type` (
                `content_type_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `description` varchar(255) NOT NULL,
                `status` char(2) NOT NULL,
                PRIMARY KEY (`content_type_id`),
                UNIQUE KEY `description` (`description`),
                KEY `status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ";

            $schemes[] = "CREATE TABLE `user` (
                `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `login` varchar(255) NOT NULL,
                `email` varchar(255) NOT NULL,
                `password` varchar(255) NOT NULL,
                `status` char(2) NOT NULL,
                `token` varchar(255),
                PRIMARY KEY (`user_id`),
                UNIQUE KEY `login` (`login`),
                KEY `status` (`status`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
            $schemes[] = "INSERT INTO `user` (`user_id`, `name`, `login`, `email`, `password`, `status`) VALUES
                (1, 'admin',    'admin@localhost',  'admin@localhost',  '', 'A');
            ";
            $schemes[] = "INSERT INTO `config` (`id`, `site_name`, `contact_email`, `twitter_url`, `facebook_url`) VALUES
                (1, 'teste site name',  'email',    'twitter',  'facebook');
            ";
            $schemes[] = "INSERT INTO `content` (`content_id`, `content_type_id`, `title`, `body`, `datetime`) VALUES
                (1, 1,  'Welcome to Liber Simple Blog', '<p>\n  &nbsp;</p>\n\n<p style=\"margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; padding-top: 0px; padding-right: 0px; padding-bottom: 0px; padding-left: 0px; \">\n  This is a simple LiberBlog example that you can change.</p>\n<p style=\"margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; padding-top: 0px; padding-right: 0px; padding-bottom: 0px; padding-left: 0px; \">\n   This application has some features like:</p>\n<div class=\"cleaner_h20\" style=\"clear: both; width: 530px; height: 20px; \">\n &nbsp;</div>\n<ul class=\"list_01\" style=\"margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 20px; padding-top: 0px; padding-right: 0px; padding-bottom: 0px; padding-left: 0px; list-style-type: decimal-leading-zero; list-style-position: initial; list-style-image: initial; \">\n  <li style=\"color: rgb(0, 132, 0); margin-bottom: 7px; \">\n        Very simple manipulation content;</li>\n    <li style=\"color: rgb(0, 132, 0); margin-bottom: 7px; \">\n        Uses Funky cache concept to maximize page load speed;</li>\n    <li style=\"color: rgb(0, 132, 0); margin-bottom: 7px; \">\n        Can be changed to your personal project;</li>\n <li style=\"color: rgb(0, 132, 0); margin-bottom: 7px; \">\n        Uses Liber Framework as a application core;</li>\n</ul>\n<p style=\"color: rgb(0, 132, 0); margin-bottom: 7px; \">\n    &nbsp;</p>\n',  '2011-02-09 19:01:22');
            ";
            $schemes[] = "INSERT INTO `content_type` (`content_type_id`, `description`, `status`) VALUES
                (1, 'Posts',    'A');
            ";
            $schemes[] = "alter table comment add CONSTRAINT FOREIGN KEY (`content_id`) REFERENCES `content` (`content_id`) ON DELETE CASCADE ON UPDATE CASCADE";

            $schemes[] = "alter table content add CONSTRAINT FOREIGN KEY (`content_type_id`) REFERENCES `content_type` (`content_type_id`) ON DELETE CASCADE ON UPDATE CASCADE";

            $db  = Liber::db();
            $ret = true;
            foreach($schemes as $sql) {
                if ( $ret === false ) {break;}
                $ret = $db->exec($sql);
            }

            if ( $ret ) {
                $config = Liber::loadModel('Config', true);
                $config->loadFrom($_POST);
                if ( $config->save() ) {
                    $oUser = Liber::loadModel('User', true);
                    $oUser->field('user_id', 1);
                    $oUser->field('name', 'Administrator');
                    $oUser->field('login', $_POST['login']);
                    $oUser->field('email', $_POST['login']);
                    $oUser->field('password', sha1($_POST['login'].sha1($_POST['password'])) );
                    if ( $oUser->save() ) {
                        // login user
                        $token = $oUser->token();
                        $hash  = hash_hmac('sha1', $oUser->field('login'), $oUser->field('password').$token);
                        $oUser->login($oUser->field('login'), $hash);
                        $aData['db'] = $oSession->val('database');

                        // put config file
                        if ( file_put_contents(Liber::conf('APP_PATH').'config/config.php', config_php($aData)) ) {

                            // try to create a assets dir
                            $oSetup = Liber::loadClass('Setup', true);
                            if ( count($oSetup->publishAsset()) == 0 ) {
                                // replace index.php (install)
                                if ( file_put_contents(Liber::conf('APP_ROOT').'index.php', index_php(Array())) ) {
                                }
                            }
                        }




                        ?>
                        <div class='form_area'>
                            <div id='content_area'>
                            <h2>Congratulations, your blog is ready.</h2>
                            <h3><a href='<?url_to_('/')?>' target='_blank'>Go to Blog</a></h3>
                            <h3><a href='<?url_to_('/admin')?>' target='_blank'>Go to Administration</a></h3>
                            </div>
                        </div>
                        <?
                    }
                }
            } else {
                print_r($db->errorInfo());
            }

        }
    ?>

</body>
</html>