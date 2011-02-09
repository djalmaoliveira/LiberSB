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
        $this->urlPattern = Liber::conf('APP_URL').Liber::conf('FUNKY_PATH');
    }

	/**
	*	Override default match pattern and return parts of matched Url.
	*	Match: FUNCKY_PATH/[content_type_description]/[content_title]/comments_page[nr].html
	*	@param String $url
	*	@return Array
	*/
    function matchUrl( $url ) {
		$aUrl = explode('/', str_replace($this->urlPattern, '', rawurldecode($url)) );
		if ( count($aUrl) != 3 or strpos($aUrl[2], 'comments_') !== 0 ) { return Array(); }
		list($oContent, $oContType) = Liber::loadModel( Array('Content', 'ContentType'),  true );
		if ( $oContent->get( $aUrl[1] ) ) {
			$oContType->get( $oContent->field('content_type_id') );
			if ($oContType->field('status') == 'A' and $oContType->field('description') == $aUrl[0] ) {

				return Array(
						'content'		=>	$oContent->toArray(),
						'contentType'	=>	$oContType->toArray(),
						'page'			=>  str_replace('.html', '', str_replace('comments_page', '', $aUrl[2]))
						);
			}
		}
		return Array();
    }


    /**
    *   Create Comment cached file from $url specified.
    *   @param String $url
    *   @return String
    */
    function create($url) {

        if ( !($parts = $this->matchUrl($url)) ) { return ; }

        Liber::loadHelper('Content', 'APP');
		Liber::loadHelper('DT');

        $oComment = Liber::loadModel('Comment', true);
		$aData['comments'] = $oComment->lastCommentsByContent( $parts['content']['content_id'] );
		$aData['pageName'] = Array("Comments", $parts['content']['title']);
		$aData['content']  = &$parts['content'];
		$funky_cache = Liber::controller()->view()->template()->load('comments.html', $aData, true);

		if ( $this->put(Liber::conf('APP_ROOT').Liber::conf('FUNKY_PATH').$parts['contentType']['description'].'/'.$parts['content']['title']."/comments_page{$parts['page']}.html", $funky_cache ) ) {
			return $funky_cache;
		}

    }

    /**
    *   Return a public URL Comment of cached file from $aComment specified.
	*	Pattern: /FUNKY_PATH/ [content_type_description] / [content_title] / comments_page[nr].html
    *   @param Array $aContent
    *   @return String
    */
    function url($aContent, $page=1) {
        $oContType = Liber::loadModel('ContentType', true);
		$oContType->get($aContent['content_type_id']);
        $url = url_to_('/'.Liber::conf('FUNKY_PATH').rawurlencode($oContType->field('description')).'/'.rawurlencode($aContent['title'])."/comments_page{$page}.html", true);
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