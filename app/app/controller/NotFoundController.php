<?php

class NotFoundController extends Controller{

    var $oTPL;

    function __construct( $p=Array() ) {
        parent::__construct($p);
        Liber::loadHelper( Array('Url', 'HTML') );
        $this->oTPL = $this->view()->template();
    }


    public function index() {

        Liber::loadHelper('Content', 'APP');
        list($oContent, $oContType) = Liber::loadModel(Array('Content', 'ContentType'), true);
        $aUri     = $this->parse_uri(url_current_(true));

        // if match content by title
        if ( $oContent->get( $aUri['title'] ) ) {
            $oFunky      = Liber::loadClass('Funky', true);
            $oContType->get($oContent->field('content_type_id'));
            $aData['contents'] = Array($oContent->toArray());
            $aData['pageName'] = Array($oContType->field('description'), $oContent->field('title'));
            $funky_cache = $this->oTPL->load('list.html', $aData, true);
            if ( $oFunky->put(Liber::conf('APP_ROOT').Liber::conf('CONTENT_PATH').$aUri['filename'], $funky_cache ) ) {
                die($funky_cache);
            }
        }

        $this->show404();
    }

    /* Detect type o cache from url and return its components */
    protected function parse_uri($uri) {
        $out  = Array(
                'base'      =>'',
                'filename'  =>'',
                'title'     =>'',
                'content_type_id'=>''
                );
        $aUrl            = pathinfo($uri); // yes, it is stored on file system
        $out['base']     = basename($aUrl['dirname']);
        $out['filename'] = rawurldecode(basename($uri));
        if ( $out['base'] == 'content' ) {
            $out['title'] = rawurldecode( substr( $aUrl['filename'], strpos($aUrl['filename'],'_')+1) );
            $out['content_type_id'] = substr($aUrl['filename'], 0, strpos($aUrl['filename'],'_'));
        }
        return $out;
    }

    protected function show404() {
        header('HTTP/1.0 404 Not Found');

        $this->oTPL->load('notfound.html', Array('url'=>url_current_(true)));
    }

}
?>