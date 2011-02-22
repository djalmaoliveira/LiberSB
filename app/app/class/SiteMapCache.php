<?php
Liber::loadClass('Funky');
/**
*   @package content
*/


/**
*   Manage the rules of sitemap cache file.
*/
class SiteMapCache extends Funky {


    function __construct() {
        $this->urlPattern = Liber::conf('APP_URL').'sitemap.xml';
    }

	/**
	*	Override default match pattern and return parts of matched URL.
	*	Match: APP_URL/sitemap.xml
	*	@param String $url
	*	@return Array
	*/
    function matchUrl( $url ) {

		if ( $this->urlPattern == $url ) {
			return pathinfo($url);
		}

		return Array();
    }


    /**
    *   Create Content cached file from $url specified.
    *   @param String $url
    *   @return String
    */
    function create($url) {
        if ( !($aUrl = $this->matchUrl($url)) ) { return ; }

		$oSMap 		= Liber::loadClass('SiteMap', true);
		$oContType 	= Liber::loadModel('ContentType', true);


		// home
		$oSMap->url(Array(
			'loc' => Liber::conf('APP_URL'),
			'lastmod'	=> date('Y-m-d')
		));
		// contact
		$oSMap->url(Array(
			'loc' => Liber::conf('APP_URL').'contact',
			'lastmod'	=> date('Y-m-d')
		));

		// list os topics
		$list = $oContType->listByStatus("A");
		foreach ($list as $ctype) {
			$oSMap->url(Array(
				'loc'	 	=> Liber::conf('APP_URL').rawurlencode($ctype['description']),
				'lastmod'	=> date('Y-m-d')
			));
		}


		$funky_cache = $oSMap->xml();
		if ( $this->put( str_replace(Liber::conf('APP_URL'), Liber::conf('APP_ROOT'), $url) , $funky_cache) ) {
			return $funky_cache;
		}
    }


    /**
    *   Return a public URL Content of cached file.
    *   @return String
    */
    function url($url='') {
        return $this->urlPattern;
    }

	/**
	*	Clean cached file.
	*	@return boolean
	*/
	function cleanCache() {
		$path = str_replace(Liber::conf('APP_URL'), Liber::conf('APP_ROOT'), ($this->urlPattern));
		return parent::clean( $path );
	}

}
?>