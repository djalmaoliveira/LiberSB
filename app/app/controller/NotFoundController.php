<?php

class NotFoundController extends Controller{

    function __construct($p) {
        parent::__construct($p);
        Liber::loadHelper(Array('Url', 'HTML'));

    }


    public function index() {

        Liber::loadHelper('Content', 'APP');
        $oContent = Liber::loadModel('Content', true);
        $title =  str_replace('-', ' ', pathinfo(url_current_(true), PATHINFO_FILENAME));

        //
        if ( $oContent->get( $title ) ) {
            $oFucky = Liber::loadClass('Fucky', true);
            $fucky_cache = $this->view()->template()->load('content_page.html', Array('content'=>$oContent->toArray()), true);
            if ( $oFucky->put(Liber::conf('ROOT_PATH').Liber::conf('CONTENT_PATH').basename(url_current_(true)), $fucky_cache ) ) {
                Liber::redirect( '/'.Liber::conf('CONTENT_PATH').basename(url_current_(true)) );
            }
        }

        $this->show404();
    }


    protected function show404() {

        echo '<h1>ERROR 404 not found</h1>';
        echo 'Trying to access: '.$_SERVER['REQUEST_URI'];
        echo '<p>This is handler by an internal Route as defined in "APP_PATH/config/config.php"  $config[\'PAGE_NOT_FOUND\']</p>

                <p>Your error document needs to be more than 512 bytes in length. If not IE will display its default error page.</p>

                <p>Give some helpful comments other than 404 :(
                Also check out the links page for a list of URLs available.</p>';

    }

}
?>