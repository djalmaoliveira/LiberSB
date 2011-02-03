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
		if ( !User::logged() ) {
			$this->login();
			exit;
		}
    }


    public function index(){
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
}

?>