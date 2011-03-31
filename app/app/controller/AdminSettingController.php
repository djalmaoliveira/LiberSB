<?php

/**
 *	Admin controller for application settings.
 *
 */
class AdminSettingController extends Controller {

    function __construct($p=Array()) {
        parent::__construct($p);

        Liber::loadHelper(Array('Url', 'HTML', 'Form'));
		Liber::loadModel('User');
		if ( !User::logged()  ) {
			Liber::redirect( url_to_('/admin', true) );
			exit;
		}
    }


    public function index() {

		// avoid CSRF access settings data
		if ( (User::token() != Input::get('t')) ) {	exit;}

		$oConfig = Liber::loadModel('Config', true);
		$oSec    = Liber::loadClass('Security', true);
		$aData['action'] = url_to_('/admin/setting', true);
		$aData['config'] = $oConfig->data( Array('site_name', 'contact_email', 'facebook_url', 'twitter_url') );
		$aData['token']  = $oSec->token();
		$this->view()->load('admin/settings.html', $aData);
    }

	public function save() {
		Liber::loadHelper('Util', 'APP');
		$oConfig = Liber::loadModel('Config', true);
		$oSec    = Liber::loadClass('Security', true);

		if ( $oSec->validToken(Input::post('token')) ) {
			$oConfig->loadFrom( Input::post() );
			if ( $oConfig->save() ) {
				cleancache();
				die( jsonout('ok','Configurations saved successfully.') );
			} else {
				die( jsonout('error',implode($oConfig->buildFriendlyErrorMsg())) );
			}
		}
		die( jsonout('error','Please reload this page.') );
	}

	public function user() {
		$oSec  = Liber::loadClass('Security', true);
		$aUser = User::logged();

		if ( $oSec->validToken(Input::post('token')) ) {
			Liber::loadHelper('Util', 'APP');
			$oUser = new User;
			$oUser->get( $aUser['user_id'] );
			$oUser->field('status', 'A');

			// change login
			$login = trim(Input::post('login'));
			if ( $login != $aUser['login'] ) {
				$users = $oUser->searchBy('login', $login);
				if ( $users ) {
					die( jsonout('error', 'Login not avaiable, try another one.') );
				} else {
					$oUser->field('login', $login);
					$oUser->field('email', $login);
				}
			}

			// change password
			if ( (Input::post('new_password')) != sha1('') ) {
				if ( sha1($aUser['login'].trim(Input::post('password'))) == $oUser->field('password') ) {
					$oUser->field('password', sha1($aUser['login'].trim( Input::post('new_password') )));
				} else {
					die( jsonout('error', 'Wrong password, try again.') );
				}
			} else {
				die( jsonout('error', "New password can't be empty.") );
			}

			if ( $oUser->save() ) {
				die( jsonout('ok', date('h:i:s')) );
			} else {
				die( jsonout('error', $oUser->buildFriendlyErrorMsg()) );
			}

		}

		$aData['token']  = $oSec->token();
		$aData['action'] = url_current_(true);
		$aData['user']	 = &$aUser;
		$this->view()->load('admin/settings_user.html', $aData);
	}

}
?>