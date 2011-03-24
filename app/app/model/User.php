<?php
Liber::loadModel('TableModel');
/**
*   @package Content
*/



/**
*   Class model for User table.
*	Status: A = Active, PC= Password changing
*/
class User extends TableModel {

    function __construct () {
        parent::__construct();
        $this->table   = 'user';
        $this->idField = 'user_id';

        $this->aFields = Array (
            'user_id'        	=> Array('', 'User', 0),
            'name'   			=> Array('', 'Name',    Validation::NOTNULL),
            'login'             => Array('', 'Login',   Validation::NOTNULL),
            'email'          	=> Array('', 'Email', 	Validation::NOTNULL),
            'password'          => Array('', 'Password', Validation::NOTNULL),
            'status'          	=> Array('', 'Status', 	Validation::NOTNULL),
			'token'				=> Array('', 'Token', 	0),
        );
    }


	static function logged() {
		Liber::loadClass('Session');
		$oSession = new Session('user_login');
		return $oSession->val('user');
	}

	/**
	*	Try login a user.
	*	Start a session with specified user.
	*	@param String $login
	*	@param String $hash
	*	@return boolean
	*/
	static function login( $login, $hash ) {

		if ( ($aUser=self::authentication($login, $hash)) ) {
			Liber::loadClass('Session');
			$oSession = new Session('user_login');
			unset($aUser['password']);
			$oSession->val('user', $aUser);
			return true;
		}
		return false;
	}

	/**
	*	Log out a current user.
	*/
	static function logout() {
		Liber::loadClass('Session');
		$oSession = new Session('user_login');
		$oSession->val('user', Array());
	}

	/**
	*	Try to authenticate a user.
	*	@param String $login
	*	@param String $hash
	*	@return Array()
	*/
	static function authentication($login, $hash) {
		$oUser = new User;
		$rs = $oUser->searchBy('login', $login);

		if ( $rs ) {
			$hmac = hash_hmac('sha1', trim($login), $rs[0]['password'].self::token());
			if ( $hmac == $hash ) {
				return $rs[0];
			}
		}
		return Array();
	}

	/**
	*	Return a token for login purpose.
	*	@param boolean $new - To renew a token
	*	@return String
	*/
	static function token($new=false) {
		Liber::loadClass('Session');
		$oSession = new Session('user_login');
		if ( $new ) {
			$oSession->val('token', md5(uniqid()).uniqid());
		}
		return $oSession->val('token');
	}

	/**
	*	Send a mail with instructions to change password.
	*	@param String $email
	*	@return boolean
	*/
	static function sendRecover( $email ) {
		$oMail = Liber::loadClass('Mailer', true);
		$oView = Liber::loadClass('View', true);
		$oUser = new User;
		$users = $oUser->searchBy('login', $email);
		$token = uniqid('recover').md5($email);
		if ( $users ) {
			$oUser->loadFrom( $users[0] );
			$oUser->field('token', $token);
			$oUser->field('status', 'PC');
			if ( $oUser->save() ) {
				$aData['url'] = url_to_('/admin/recover?token='.$token, true);
				$body = $oView->load('admin/email_change_password.html', $aData, true);
				$oMail->to($email);
				$oMail->body( $body );
				$oMail->subject('Recover password from '.Liber::conf('APP_URL'));
				$oMail->html(true);
				return $oMail->send();
			}
		}

		return false;
	}

}

?>