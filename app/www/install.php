<?
include '../Liber/Liber.php';
Liber::setup();
Liber::conf('BASE_PATH', realpath('../Liber/').'/');
Liber::conf('APP_PATH', realpath('../app/').'/');
Liber::conf('APP_MODE', 'PROD');

Liber::loadHelper(Array('Form', 'Url'));
$oSession = Liber::loadClass('Session', true);
$action = basename($_SERVER['SCRIPT_NAME']);
$error='';



if ( !isset($_REQUEST['step']) ) {
	$_REQUEST['step'] = 1;
}
if ( ($_REQUEST['step'])==2 and $_POST) {
	$oSession->val('database', $_POST);
	if ( ($aDbConfig = $oSession->val('database')) ) {
		Liber::$aDbConfig = Array('PROD'=>Array($aDbConfig['server'],$aDbConfig['database'],$aDbConfig['user'],$aDbConfig['password'], 'mysql'));
	}

	if ( !Liber::db('PROD') ) {
		$error = "Wrong database connection, please fill correct informations.";
	}

	if ( $error ) {
		$_REQUEST['step'] = 1;
	}
} else {
	if ( ($aDbConfig = $oSession->val('database')) ) {
		Liber::$aDbConfig = Array('PROD'=>Array($aDbConfig['server'],$aDbConfig['database'],$aDbConfig['user'],$aDbConfig['password'], 'mysql'));
	}
}
if ( ($_REQUEST['step'])==3 and $_POST) {
	$oSession->val('app', $_POST);
	$error='';
	$oVal = Liber::loadClass('Validation', true);
	if ( ($errors = $oVal->validate($_POST['contact_email'], Validation::EMAIL))  ) {
		$error = "Contact Email: ".implode('', $errors)."<br/>";
	}

	if ( !empty($_POST['facebook_url']) and ($errors = $oVal->validate($_POST['facebook_url'], Validation::URL))  ) {
		$error .= "Facebook Url: ".implode('', $errors)."<br/>";
	}

	if ( !empty($_POST['twitter_url']) and ($errors = $oVal->validate($_POST['twitter_url'], Validation::URL))  ) {
		$error .= "Twitter Url: ".implode('', $errors)."<br/>";
	}

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
				$nav = ' » <a href="?step=3">Finished</a>'.$nav;
			case 2:
				$nav = ' » <a href="?step=2">Step 2</a>'.$nav;
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
				<? 	if ( isset($error) ) {?>
					<p class='msg_error'><?=$error?></p>
				<?	}?>
				<?form_button_('btnContinue', 'Continue', 'onclick="_next()"')?>
			</div>
		</div>
	<?	}?>



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
						<div class='field_name'>User:</div><?form_input_('user',$oSession->val('app'), "title='Administrator User to allow access administration area.'")?>
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
				<? 	if ( isset($error) ) {?>
					<p class='msg_error'><?=$error?></p>
				<?	}?>
				<?form_button_('btnContinue', 'Continue', 'onclick="_next()"')?>
			</div>
		</div>
	<?	}

		if ( $_REQUEST['step'] == 3 ) {


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
				) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;
			";

			$schemes[] = "CREATE TABLE `config` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`site_name` varchar(255) NOT NULL,
				`contact_email` varchar(255) NOT NULL,
				`twitter_url` varchar(255) NOT NULL,
				`facebook_url` varchar(255) NOT NULL,
				PRIMARY KEY (`id`)
				) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
			";

			$schemes[] = "CREATE TABLE `content` (
				`content_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`content_type_id` bigint(20) unsigned NOT NULL,
				`title` varchar(255) NOT NULL,
				`body` mediumtext NOT NULL,
				`datetime` datetime NOT NULL,
				PRIMARY KEY (`content_id`),
				UNIQUE KEY `content_type_id` (`content_type_id`,`title`),
				KEY `datetime` (`datetime`)
				) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;
			";

			$schemes[] = "CREATE TABLE `content_type` (
				`content_type_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`description` varchar(255) NOT NULL,
				`status` char(2) NOT NULL,
				PRIMARY KEY (`content_type_id`),
				UNIQUE KEY `description` (`description`),
				KEY `status` (`status`)
				) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
			";

			$schemes[] = "CREATE TABLE `user` (
				`user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`name` varchar(255) NOT NULL,
				`login` varchar(255) NOT NULL,
				`email` varchar(255) NOT NULL,
				`password` varchar(255) NOT NULL,
				`status` char(2) NOT NULL,
				PRIMARY KEY (`user_id`),
				UNIQUE KEY `login` (`login`),
				KEY `status` (`status`)
				) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
			";
			$schemes[] = "INSERT INTO `user` (`user_id`, `name`, `login`, `email`, `password`, `status`) VALUES
				(1,	'admin',	'admin@localhost',	'admin@localhost',	'',	'A');
			";
			$schemes[] = "INSERT INTO `config` (`id`, `site_name`, `contact_email`, `twitter_url`, `facebook_url`) VALUES
				(1,	'teste site name',	'email',	'twitter',	'facebook');
			";
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
					$oUser->field('login', $_POST['user']);
					$oUser->field('password', sha1($_POST['password']));
					if ( $oUser->save() ) {
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