<?php

class NotFoundController extends Controller{

    var $oTPL;

    function __construct( $p=Array() ) {
        parent::__construct($p);
        Liber::loadHelper( Array('Url', 'HTML') );
        $this->oTPL = $this->view()->template();
    }


    public function index() {

        $oCache = Liber::loadClass('ContentCache', 'APP', true);
        $page = $oCache->create(url_current_(true));
        if ($page) {
            die($page);
        }

        $this->show404();
    }

    protected function show404() {
        header('HTTP/1.0 404 Not Found');

        $this->oTPL->load('notfound.html', Array('url'=>url_current_(true)));
    }

}
?>