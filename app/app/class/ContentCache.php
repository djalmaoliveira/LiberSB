<?php
Liber::loadClass('Funky');
/**
*   @package content
*/


/**
*   Manage the rules os cache files of Content models.
*/
class ContentCache extends Funky {


    function __construct() {
        $this->urlPattern = Liber::conf('APP_URL').Liber::conf('FUNKY_PATH').'content/';
    }

    /**
    *   Create Content cached file from $url specified.
    *   @param String $url
    *   @return String
    */
    function create($url) {

        if ( !$this->matchUrl($url) ) { return ; }

        Liber::loadHelper('Content', 'APP');
        list($oContent, $oContType) = Liber::loadModel(Array('Content', 'ContentType'), true);
        $urlPattern = '/'.str_replace(Liber::conf('APP_URL'), '', $url);

        $aUrl            = pathinfo($urlPattern);
        $content_type_id = basename($aUrl['dirname']);
        $filename        = rawurldecode($aUrl['filename']);
        $title           = rawurldecode( $filename );

        // if match content by title
        if ( $oContent->get( $title ) ) {
            $oContType->get($oContent->field('content_type_id'));
            $aData['contents'] = Array($oContent->toArray());
            $aData['pageName'] = Array($oContType->field('description'), $oContent->field('title'));

            $funky_cache = Liber::controller()->view()->template()->load('list.html', $aData, true);
            if ( $this->put(Liber::conf('APP_ROOT').Liber::conf('FUNKY_PATH').'content/'.$content_type_id.'/'.$filename.'.'.$aUrl['extension'], $funky_cache ) ) {
                return $funky_cache;
            }
        }
    }

    /**
    *   Return a public URL Content of cached file from $aContent specified.
    *   @param Array $aContent
    *   @return String
    */
    function url($aContent) {
        $url = url_to_('/'.Liber::conf('FUNKY_PATH').'content/'.$aContent['content_type_id'].'/'.rawurlencode($aContent['title']).'.html', true);
        return $url;
    }
}
?>