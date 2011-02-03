<?php

/**
 *
 *
 */
class AdminConfigController extends Controller {

    function __construct($p=Array()) {
        parent::__construct($p);

        Liber::loadHelper(Array('Url', 'HTML', 'Form'));
		Liber::loadModel('User');
		if ( !User::logged()  ) {
			Liber::redirect( url_to_('/admin', true) );
			exit;
		}

		if ( (User::token() != Input::get('t')) ) {
			exit;
		}
    }


    public function index() {
		$oConfig = Liber::loadModel('Config', true);
		$oSec    = Liber::loadClass('Security', true);
		$aData['action'] = url_current_(true);
		$aData['config'] = $oConfig->data( Array('site_name', 'contact_email', 'facebook_url', 'twitter_url') );
		$aData['token']  = $oSec->token();
		$this->view()->load('admin/config.html', $aData);
    }


	public function save() {
		Liber::loadHelper('Util', 'APP');
		$oConfig = Liber::loadModel('Config', true);
		$oSec    = Liber::loadClass('Security', true);

		if ( $oSec->validToken(Input::post('token')) ) {
			$oConfig->loadFrom( Input::post() );
			if ( $oConfig->save() ) {
				die( jsonout('ok','Configurations saved successfully.') );
			} else {
				die( jsonout('error',"Sorry, these informations can't be save.") );
			}
		}
		die( jsonout('error','Please reload this page.') );
	}



}
?>