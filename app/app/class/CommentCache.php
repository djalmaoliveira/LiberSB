<?php
Liber::loadClass('Funky');
/**
*   @package content
*/


/**
*   Manage the rules os cache files of Comment models.
*/
class CommentCache extends Funky {


    function __construct() {
        $this->urlPattern = Liber::conf('APP_URL').Liber::conf('FUNKY_PATH').'comments/';
    }


    /**
    *   Create Comment cached file from $url specified.
    *   @param String $url
    *   @return String
    */
    function create($url) {

        if ( !$this->matchUrl($url) ) { return ; }

        Liber::loadHelper('Content', 'APP');
        list($oContent, $oComment) = Liber::loadModel(Array('Content', 'Comment'), true);
        $urlPattern = '/'.str_replace(Liber::conf('APP_URL'), '', $url);

        $aUrl            = pathinfo($urlPattern);
        $content_id      = basename($aUrl['dirname']);
        $filename        = rawurldecode($aUrl['filename']);
		$divider		 = strpos($filename,'_');
        $title           = substr(rawurldecode( $filename ), $divider+1);
		$page 		 	 = substr(rawurldecode( $filename ), 0, $divider);

		$comments = $oComment->lastCommentsByContent( $content_id );  
			Liber::loadHelper('DT');
			$oContent->get($content_id);
            $aData['comments'] = &$comments;
            $aData['pageName'] = Array("Comments", $title);
			$aData['content']  = $oContent->toArray();
            $funky_cache = Liber::controller()->view()->template()->load('comments.html', $aData, true);

			if ( $this->put(Liber::conf('APP_ROOT').Liber::conf('FUNKY_PATH').'comments/'.$content_id.'/'.$filename.'.'.$aUrl['extension'], $funky_cache ) ) {
                return $funky_cache;
            }
        
    }

    /**
    *   Return a public URL Comment of cached file from $aComment specified.
    *   @param Array $aContent
    *   @return String
    */
    function url($aContent, $page=1) {
        // pattern: /FUNKY_PATH/ comments / [content_id] / [page]_[title].html
        $url = url_to_('/'.Liber::conf('FUNKY_PATH').'comments/'.$aContent['content_id']."/{$page}_".rawurlencode($aContent['title']).'.html', true);
        return $url;
    }

	/**
	*	Clean cached files by $aComment.	
	*	@param Array $aComment
	*	@return boolean
	*/
	function cleanCache($aComment) {
		parent::clean( str_replace(Liber::conf('APP_URL'), Liber::conf('APP_ROOT'), $this->urlPattern).$aComment['content_id'].'/' );
	}
}
?>