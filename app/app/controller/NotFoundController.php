<?php

class NotFoundController extends Controller{

    function __construct($p=Array()) {
        parent::__construct($p);
        Liber::loadHelper(Array('Url', 'HTML'));

    }


    public function index() {

        Liber::loadHelper('Content', 'APP');
        $oContent = Liber::loadModel('Content', true);
        $file  = (pathinfo(url_current_(true), PATHINFO_FILENAME));
        $title = rawurldecode($file);

        // if match content by title
        if ( $oContent->get( $title ) ) {
            $oFunky = Liber::loadClass('Funky', true);
            $funky_cache = $this->view()->template()->load('content_page.html', Array('content'=>$oContent->toArray()), true);
            if ( $oFunky->put(Liber::conf('APP_ROOT').Liber::conf('CONTENT_PATH').$title.'.html', $funky_cache ) ) {
                //$url = Liber::conf('APP_URL').Liber::conf('CONTENT_PATH').rawurlencode($title).'.html';
                die($funky_cache);
            }
        }

        $this->show404();
    }


    protected function show404() {
        header('HTTP/1.0 404 Not Found');

        echo '
            <h1>ERROR 404 not found</h1>
            Trying to access: '.$_SERVER['REQUEST_URI'].'
            <p>This is handler by an internal Route as defined in "APP_PATH/config/config.php"  $config[\'PAGE_NOT_FOUND\']</p>

                <p>Your error document needs to be more than 512 bytes in length. If not IE will display its default error page.</p>

                <p>Give some helpful comments other than 404 :(
                Also check out the links page for a list of URLs available.</p>';

    }

}
?>