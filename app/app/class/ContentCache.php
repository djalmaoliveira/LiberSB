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
        $this->urlPattern = Liber::conf('APP_URL').Liber::conf('FUNKY_PATH');
    }

	/**
	*	Override default match pattern and return parts of matched URL.
	*	Match: FUNKY_PATH/[content_type_description]/[content_title].html
	*	@param String $url
	*	@return Array
	*/
    function matchUrl( $url ) {
		list($oContent, $oContType) = Liber::loadModel( Array('Content', 'ContentType'),  true );

		$aUrl = pathinfo( str_replace($this->urlPattern, '', $url) );
		if ( $oContent->get( rawurldecode($aUrl['filename']) ) ) {
			$oContType->get( $oContent->field('content_type_id') );
			if ($oContType->field('status') == 'A' and $oContType->field('description') == rawurldecode($aUrl['dirname']) ) {
				return Array(
							'content'		=> $oContent->toArray(),
							'contentType'	=> $oContType->toArray()
						);
			}
		}
		return Array();
    }


    /**
    *   Create Content cached file from $url specified.
    *   @param String $url
    *   @return String
    */
    function create($url) {

        if ( !($parts = $this->matchUrl($url)) ) { return ; }

        Liber::loadHelper('Content', 'APP');

		$aData['contents'] = Array( &$parts['content'] );
		$aData['pageName'] = Array(&$parts['contentType']['description'], &$parts['content']['title']);
		$funky_cache = Liber::controller()->view()->template()->load('list.html', $aData, true);

		if ( $this->put(Liber::conf('APP_ROOT').Liber::conf('FUNKY_PATH').$parts['contentType']['description'].'/'.$parts['content']['title'].'.html', $funky_cache ) ) {
			return $funky_cache;
		}
    }

    /**
    *   Return a public URL Content of cached file from $aContent specified.
    *   @param Array $aContent
    *   @return String
    */
    function url($aContent) {
		$oContType = Liber::loadModel('ContentType', true);
		$oContType->get( $aContent['content_type_id'] );
        $url = url_to_('/'.Liber::conf('FUNKY_PATH').rawurlencode($oContType->field('description')).'/'.rawurlencode($aContent['title']).'.html', true);
        return $url;
    }

	/**
	*	Clean cached files by $aContent.
	*	@param Array $aContent
	*	@return boolean
	*/
	function cleanCache($aContent) {
		$url = $this->url($aContent);
		parent::clean( str_replace(Liber::conf('APP_URL'), Liber::conf('APP_ROOT'), rawurldecode($url)) );
		Liber::loadClass('FeedCache', 'APP', true)->cleanCache();
	}

}
?>