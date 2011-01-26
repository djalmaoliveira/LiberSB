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
        $this->oTPL = $this->view()->template();
    }


    public function index() {
        Liber::loadHelper('Content', 'APP');

		$this->oTPL->load('home.html');
    }

    /* missing action */
    function __call($action, $args=Array()) {
        list($oContent, $oContType) = Liber::loadModel( Array('Content', 'ContentType'), true );

        if ( $oContType->get(str_replace('-', ' ', rawurldecode($action))) ) {
            $this->showContentHome($oContType);
        } else {
            parent::__call($action, $args);
        }
    }

    function aa() {
        $o = Liber::loadClass('Fucky', true);
        $o->clean( Liber::conf('ROOT_PATH').Liber::conf('CONTENT_PATH') );
    }

    /* load contents page by content_type */
    protected function showContentHome($oContType) {
        Liber::loadHelper('Content', 'APP');
        $oContent = Liber::loadModel( 'Content', true );

        $aData['contents'] = $oContent->lastContentsByType($oContType->field('content_type_id'));
        $this->oTPL->load('content_home.html', $aData);
    }

    public function setup() {
        // setup application
        if ( !file_exists(Liber::conf('APP_ROOT').Liber::conf('ASSETS_DIR').'app') ) {
            $oSetup = Liber::loadClass('Setup', true);
            $oSetup->publishAsset();

        }

    }

    public function c() {
        print_r($this->params());
    }

}

?>