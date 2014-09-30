<?php

class NotFoundController extends Controller{

    function __construct( $p=Array() ) {
        parent::__construct($p);
        Liber::loadHelper( Array('Url', 'HTML') );
        $this->view()->template('default.html');
    }


    public function index() {

        $page = Liber::loadClass('ContentCache', 'APP', true)->create(url_current_(true));

        if ($page) { die($page); }

        $page = Liber::loadClass('CommentCache', 'APP', true)->create(url_current_(true));

        if ($page) { die($page); }

        $page = Liber::loadClass('FeedCache', 'APP', true)->create(url_current_(true));

        if ($page) { header("Content-type: text/xml"); die($page); }

		$page = Liber::loadClass('SiteMapCache', 'APP', true)->create(url_current_(true));

        if ($page) { header("Content-type: text/xml"); die($page); }

        $this->show404();
    }

    protected function show404() {
        header('HTTP/1.0 404 Not Found');

        $this->view()->load('notfound.html', Array('url'=>rawurldecode(url_current_(true))));
    }

}
?>