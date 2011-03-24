<?php

/**
 * AdminController
 *
 */
class AdminController extends Controller{

    var $oTPL;

    function __construct($p) {
        parent::__construct($p);
		session_cache_expire (30);

        Liber::loadHelper(Array('Url', 'HTML'));
        $this->oTPL = $this->view()->template();
        $this->oTPL->model('admin.html');

		Liber::loadModel('User');
    }


    public function index(){
		if ( !User::logged() ) {
			$this->login();
			exit;
		}


        list($oContent, $oContType) = Liber::loadModel(Array('Content', 'ContentType'), true);
        $aTotalContents = $oContent->getTotalByContentType();
        $aContType      = $oContType->search('');

        /*
         build array of content_type_id indicating if should have to show  one or two menus.
         format: Array(
                    description => 'description text',
                    type        => 1 or 2
                    content_id  => integer // if only one content
                  )
        */
        $aMenu = Array();
        foreach ( $aContType as $record ) {
            $menu = Array(
                        'description'   =>  $record['description'],
                        'type'          =>  isset($aTotalContents[$record['content_type_id']])?($aTotalContents[$record['content_type_id']]>1?2:1):1
                    );
            if ( $menu['type'] == 1 ) {
                $aContent = current( $oContent->searchBy('content_type_id', $record['content_type_id']) );
                $menu     = array_merge($menu, Array('content_id'=>$aContent['content_id']));
            }
            $aMenu[$record['content_type_id']] = $menu;
        }

        $aData['content_menu'] = &$aMenu;
		$aData['token'] = User::token();
		$this->oTPL->load('admin/admin_home.html', $aData);
    }

	function login() {
		Liber::loadModel('User');
		Liber::loadHelper( Array('Form', 'Url') );
		Liber::loadHelper('Util', 'APP');


		if ( Liber::requestedMethod() == 'post' ) {
			if ( User::login(Input::post('login'), Input::post('hash')) ) {
				die( jsonout('ok', url_to_('/admin', true) ) );
			} else {
				die( jsonout('error', 'Login/Password invalid, try again.') );
			}
		}


		$aData['action']= url_to_('/admin/login', true );
		$aData['token'] = User::token(true);
		$this->oTPL->load('admin/login.html', $aData);
	}

	function logout() {
		Liber::loadModel('User');

		if ( Input::get('t') == User::token() ) {
			User::logout();
			Liber::redirect('/admin');
		}
	}

	function recover() {
		Liber::loadHelper('Util', 'APP');
		$oUser = Liber::loadModel('User', true);
		Liber::loadHelper( Array('Form', 'Url') );


		if ( Liber::requestedMethod() == 'post' ) {

			// send instructions
			$error = '';
			if ( Input::post('login') ) {
				if ( User::sendRecover( Input::post('login') ) ) {
					die( jsonout('ok', url_to_('/admin', true) ) );
				} else {
					$error = "The message can't be sent.";
				}

			} else {
				$error = "Put your email address.";
			}
			die( jsonout('error', $error) );
		} else {

			// show form password
			if ( Input::get('token') ) {
				$users = $oUser->searchBy('token', Input::get('token'));
				if ( $users ) {
					$aUser = &$users[0];
					if ( $aUser['status'] == "PC" ) {
						$aData['action'] = url_to_('/admin/changepass', true );
						$aData['token']  = Input::get('token');
						$aData['user']   = &$aUser;
						$this->oTPL->load('admin/recover_change_password.html', $aData);
						exit;
					} else {
						die( "This resource is not avaiable." );
					}
				}
			}
		}

		$aData['action']= url_to_('/admin/recover', true );
		$aData['token'] = User::token(true);
		$this->oTPL->load('admin/recover_password.html', $aData);
	}

	function changepass() {
		Liber::loadHelper('Util', 'APP');
		$oUser = Liber::loadModel('User', true);
		Liber::loadHelper( Array('Form', 'Url') );
		$error = '';

		if ( Input::post('token') and Input::post('password')) {
			$users = $oUser->searchBy('token', Input::post('token'));
			if ( $users ) {
				$aUser = &$users[0];
				$oUser->loadFrom($users[0]);
				$oUser->field('password', sha1(Input::post('password')) );
				$oUser->field('status', 'A');
				if ( $oUser->save() ) {
					die( jsonout('ok', "ok" ) );
				} else {
					$error=  "Error: Your password didn't change.";
				}
			} else {
				$error = "Invalid token.";
			}
		} else {
			$error = "Invalid request.";
		}

		die( jsonout('error', $error) );
	}
}

?>