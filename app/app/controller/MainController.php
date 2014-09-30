<?php

/**
 * MainController
 */
class MainController extends Controller{


    function __construct($p) {
        parent::__construct($p);
        Liber::loadHelper(Array('Url', 'HTML'));
		Liber::loadModel('Config');
        $this->view()->template('default.html');
    }


    public function index() {
        Liber::loadHelper('Content', 'APP');
		$oConfig = new Config;
		html_title_($oConfig->data( 'site_name' ));
		$aData['isSummary'] = false;
		$aData['content']	= Liber::loadModel('Content', true)->lastContent();
		$aData['commented'] = Liber::loadModel('Comment', true)->mostCommented();

		$this->view()->load('home.html', $aData);
    }

    public function admin() {
        $args = func_get_args();
//print_r($args);
        $args[1] = !isset($args[1])?'index':$args[1];
        if ( !Liber::processController( "AdminController", $args[0], '', $args) ) {
            switch ( $args[0] ) {
                case 'setting':
                    Liber::processController( "AdminSettingController", $args[1], '', $args);
                break;

                case 'content':
                    Liber::processController( "AdminContentController", $args[1], '', $args);
                break;

                case 'topic':
                    Liber::processController( "AdminTopicController", $args[1], '', $args);
                break;

                case 'comment':
                    Liber::processController( "AdminCommentController", $args[1], '', $args);
                break;

                default:
                    # code...
                    break;
            }
        }


// $route["/admin/setting"]["*"]    = Array("AdminSettingController");
// $route["/admin/content"]["*"]    = Array("AdminContentController");
// $route["/admin/topic"]["*"]      = Array("AdminTopicController");
// $route["/admin/comment"]["*"]    = Array("AdminCommentController");

        return;

    }

    /* missing action */
    function __call($action, $args=Array()) {
        list($oContent, $oContType) = Liber::loadModel( Array('Content', 'ContentType'), true );

        if ( $oContType->get(str_replace('-', ' ', rawurldecode($action))) ) {
            Liber::loadHelper('Content', 'APP');
			$aData['isSummary']   = true;
            $aData['contents']    = $oContent->lastContentsByType( $oContType->field('content_type_id') );
            $aData['pageName']    = Array($oContType->field('description'));
            $this->view()->load('list.html', $aData);
        } else {
            Liber::loadController(Liber::conf('PAGE_NOT_FOUND'), true)->index($action);
        }
    }


    public function search() {
        Liber::loadHelper('Content', 'APP');
        $oContent = Liber::loadModel('Content', true);
		$aData['isSummary']   = true;
        $aData['contents'] = $oContent->searchContent( Http::post('search') );
        $aData['pageName'] = Array('Search results for "'.Http::post('search').'"');
        $this->view()->load('list.html', $aData);
    }

    public function contact() {
        Liber::loadHelper('Form');
        $oSec = Liber::loadClass('Security', true);
        if ( Http::post() ) {
            Liber::loadHelper('Util', 'APP');
			$oConfig = Liber::loadModel('Config', true);
            if ( $oSec->validToken(Http::post('token')) ) {

                if ( !Http::post('email') or !Http::post('text') ) { die( jsonout('error', "Please fill the form fields.") ); }

                $oM = Liber::loadClass('Mailer', true);
                $oM->to( $oConfig->data('contact_email') );
                $oM->subject('Contact from '.Liber::conf('APP_URL'));
                $oM->from( Http::post('email') );
                $oM->body( Http::post('name')." <".Http::post('email').">\n\n".Http::post('text') );
                if ( $oM->send() ) {
                    die( jsonout('ok', "Your message was sent. ") );
                } else {
                    die( jsonout('error', "Your message wasn't sent, please try again.") );
                }

            } else { // some problem with token
                die( jsonout('error', "Your message wasn't sent, please reload the page and try again. ") );
            }
        }

        $aData['token']  = $oSec->token(true);
        $aData['action'] = url_current_(true);
        $this->view()->load('contact.html', $aData);
    }

    public function recents() {
        Liber::loadHelper('DT');
        Liber::loadHelper('Content', 'APP');
        $oContent      = Liber::loadModel('Content', true);
        $aData['list'] = $oContent->lastContents();
        $this->view()->element('sidebar.html', $aData);
    }



	public function token() {
		if ( Http::ajax() ) {
			$oSec = Liber::loadClass('Security', true);
			Liber::loadHelper('Util', 'APP');
			die( jsonout('ok', $oSec->token()) );
		}
	}

    public function setup() {
        // setup application
        if ( !file_exists(Liber::conf('APP_ROOT').Liber::conf('ASSETS_DIR').'app') ) {
            $oSetup = Liber::loadClass('Setup', true);
            $oSetup->publishAsset();
        }
    }

}

?>