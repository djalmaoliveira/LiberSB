<?
include '../Liber/Liber.php';
Liber::setup();
Liber::conf('BASE_PATH', realpath('../Liber/').'/');
Liber::conf('APP_MODE', 'PROD');

if ( !isset($_REQUEST['step']) ) {
	$_REQUEST['step'] = 1;
}
Liber::loadHelper(Array('Form', 'Url'));
$oSession = Liber::loadClass('Session', true);

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
				$nav = ' » <a href="?step=3">Step 3</a>'.$nav;
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
				<form method='post' action='' id='frm' onsubmit='return false;'>
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
						<div class='field_name'>Password:</div><?form_password_('password', '', "title='Database Password'")?>
					</p>
				</form>
				<script>
					function _next() {
						$('#frm').submit();
					}
				</script>
				<? 	if ( isset($_REQUEST['error']) ) {?>
					<p class='msg_error'>Database settings wrong, please check information above and try again.</p>
				<?	}?>
				<?form_button_('btnContinue', 'Continue', 'onclick="_next()"')?>
			</div>
		</div>
	<?	}?>



	<?
		if ( $_REQUEST['step'] == 2 ) {
			$oSession->val('database', $_POST);
			Liber::$aDbConfig = Array('PROD'=>Array($_POST['server'],$_POST['database'],$_POST['user'],$_POST['password'], 'mysql'));
			if ( !Liber::db('PROD') ) {
				Liber::redirect(url_current_(true).'?error=true&step=1');
			}
	?>

		<div class='form_area'>
			<div id='content_area'>
				<h3>Step <?=$_REQUEST['step']?>: Application settings</h3>
				<p>Please fill correct informations about your application.</p>
				<form method='post' action='' id='frm' onsubmit='return false;'>
					<?form_hidden_('step', '3')?>
					<p>
						<div class='field_name'>Site Name:</div><?form_input_('site',$oSession->val('app'),"title='Put your site name.(i.e. my blog)'")?>
					</p>
					<p>
						<div class='field_name'>Contact Email:</div><?form_input_('email',$oSession->val('app'), "title='Put a default email address to receive messages.'")?>
					</p>
					<p>
						<div class='field_name'>Facebook URL:</div><?form_input_('facebook',$oSession->val('app'), "title='If you have a Facebook account, put here your URL.'")?> (optional)
					</p>
					<p>
						<div class='field_name'>Twitter URL:</div><?form_input_('twitter',$oSession->val('app'), "title='If you have a Twitter account, put here your URL.'")?> (optional)
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
				<? 	if ( isset($_REQUEST['error']) ) {?>
					<p class='msg_error'>Database settings wrong, please check information above and try again.</p>
				<?	}?>
				<?form_button_('btnContinue', 'Continue', 'onclick="_next()"')?>
			</div>
		</div>
	<?	} ?>

	<?
		if ( $_REQUEST['step'] == 3 ) {
			$oSession->val('app', $_POST);
	?>



	<?	} ?>

</body>
</html>