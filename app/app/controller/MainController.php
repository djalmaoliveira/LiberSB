<?php

/**
 * MainController
 *
 */
class MainController extends Controller{

    var $oTPL;

    function __construct($p) {
        parent::__construct($p);
        Liber::loadHelper(Array('Url', 'HTML'));
		Liber::loadModel('Config');
        $this->oTPL = $this->view()->template();
    }


    public function index() {
        Liber::loadHelper('Content', 'APP');
		$oConfig = new Config;
		$aConfig = $oConfig->data( Array('site_name', 'facebook_url', 'twitter_url') );
		html_title_($aConfig['site_name']);
		$aData['config'] = &$aConfig;
		$this->oTPL->load('home.html', $aData);
    }

    /* missing action */
    function __call($action, $args=Array()) {
        list($oContent, $oContType) = Liber::loadModel( Array('Content', 'ContentType'), true );

        if ( $oContType->get(str_replace('-', ' ', rawurldecode($action))) ) {
            Liber::loadHelper('Content', 'APP');
            $aData['contents']    = $oContent->lastContentsByType( $oContType->field('content_type_id') );
            $aData['pageName']    = Array($oContType->field('description'));
            $this->oTPL->load('list.html', $aData);
        } else {
            parent::__call($action, $args);
        }
    }


    public function search() {
        Liber::loadHelper('Content', 'APP');
        $oContent = Liber::loadModel('Content', true);
        $aData['contents'] = $oContent->search(Input::post('search'), Array('fields'=>Array('title'),'limit'=>5));
        $aData['pageName'] = Array('Search results for "'.Input::post('search').'"');
        $this->oTPL->load('list.html', $aData);
    }

    public function contact() {
        Liber::loadHelper('Form');
        $oSec = Liber::loadClass('Security', true);
        if ( Liber::requestedMethod() == 'post' ) {
            Liber::loadHelper('Util', 'APP');
			$oConfig = Liber::loadModel('Config', true);
            if ( $oSec->validToken(Input::post('token')) ) {

                if ( !Input::post('email') or !Input::post('text') ) { die( jsonout('error', "Please fill the form fields.") ); }

                $oM = Liber::loadClass('Mailer', true);
                $oM->to( $oConfig->data('contact_email') );
                $oM->subject('Contact from '.Liber::conf('APP_URL'));
                $oM->from( Input::post('email') );
                $oM->body( Input::post('name')." <".Input::post('email').">\n\n".Input::post('text') );
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
        $this->oTPL->load('contact.html', $aData);
    }

    public function recents() {
        Liber::loadHelper('DT');
        Liber::loadHelper('Content', 'APP');
        $oContent      = Liber::loadModel('Content', true);
        $aData['list'] = $oContent->lastContents();
        $this->view()->load('sidebar.html', $aData);
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