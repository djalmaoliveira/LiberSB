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

        $this->urlPattern = url_to_('/',true).Liber::conf('FUNKY_PATH');
    }

	/**
	*	Override default match pattern and return parts of matched URL.
	*	Match: FUNKY_PATH/[content_type_description]/[permalink].html
	*	@param String $url
	*	@return Array
	*/
    function matchUrl( $url ) {
		list($oContent, $oContType) = Liber::loadModel( Array('Content', 'ContentType'),  true );

		$aUrl = pathinfo( str_replace($this->urlPattern, '', $url) );
		$contents = $oContent->searchBy('permalink', $aUrl['filename'])->fetchAll();

		// detect if has more than one content with the same permalink, but different content_type
		foreach ($contents as $aContent) {
			$oContType->get($aContent['content_type_id']);
			if ( url_clean_($oContType->field('description'), true) == basename($aUrl['dirname'])) {
				break;
			}
		}

		$aContType = $oContType->toArray();
		if ( $aContType['status'] == 'A' ) {
			return Array(
						'content'		=> $aContent,
						'contentType'	=> $aContType
					);
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
		$aData['isSummary'] = false;
		$funky_cache = Liber::controller()->view()->template()->load('list.html', $aData, true);
		Liber::loadClass('Minify');
		$funky_cache = Minify::html($funky_cache);

		$file = str_replace(url_to_('/', true), Liber::conf('APP_ROOT'), rawurldecode($this->url($parts['content'])));
		if ( $this->put($file, $funky_cache ) ) {
			return $funky_cache;
		}
    }

    /**
    *   Return a public URL Content of cached file from $aContent specified.
    *   @param Array $aContent
    *   @return String
    */
    function url($aContent) {
		Liber::loadHelper('Url');
		$oContType = Liber::loadModel('ContentType', true);
		$oContType->get( $aContent['content_type_id'] );

        $url = url_to_('/',true).Liber::conf('FUNKY_PATH').url_clean_($oContType->field('description'),true).'/'.($aContent['permalink']).'.html';
        return $url;
    }

	/**
	*	Clean cached files by $aContent.
	*	@param Array $aContent
	*	@return boolean
	*/
	function cleanCache($aContent) {
		$url = $this->url($aContent);
		parent::clean( str_replace(url_to_('/', true), Liber::conf('APP_ROOT'), rawurldecode($url)) );
		Liber::loadClass('FeedCache', 'APP', true)->cleanCache();
	}

}
?>