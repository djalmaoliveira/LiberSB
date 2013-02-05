<?php
/**
*   Generic class create sitemaps to Search Engine.
*	Protocol at http://sitemaps.org/
*   @package classes
*/
class SiteMap {
	/**
	*	Store list of urls.
	*	@var Array
	*/
	private $aUrls = Array();


	/**
	*	Set or get urls.
	*	Usage:
	*	<code>
	*		// to set one url;
	*	 	url( Array('loc'=>'http://someurl.com') );
	*
	*	 	// put a set of urls replacing current set;
	*		url( Array(Array('loc'=>'http://someurl.com'), Array('loc'=>'http://someurl2.com')) );
	*
	*		// to return Array of urls currently stored;
	*		url();
	*	</code>
	*	@param Array $arr
	*	@return Array
	*/
	function url($arr=Array()) {
		if ( $arr ) {
			if ( is_array(current($arr)) ) {
				$this->$aUrls = $arr;
			} else {
				$this->aUrls[] = $arr;
			}
		} else {
			return $this->aUrls;
		}
	}

	/**
	*	Return XML content.
	*	@return String
	*/
	function xml() {
		return $this->buildSiteMap();
	}

	/**
	*	Create a XML content of stores $this->aUrls
	*	Return NULL if doesn't exist any URL
	*	@return String
	*/
	protected function buildSiteMap() {
		$urls = Array();
		$url  = current( $this->aUrls );
		do {
			if ( !isset($url['loc']) or !filter_var($url['loc'], FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) { continue; }
			$temp  = "<loc>{$url['loc']}</loc>";
			$temp .= isset($url['lastmod'])?"<lastmod>{$url['lastmod']}</lastmod>":'';
			$temp .= isset($url['changefreq'])?"<changefreq>{$url['changefreq']}</changefreq>":'';
			$temp .= isset($url['priority'])?"<priority>{$url['priority']}</priority>":'';
			$urls[] = "<url>".$temp."</url>";
		} while ( $url = next($this->aUrls) );

		if ( !$urls ) { return NULL; }

		$xml = trim('
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"
         xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
'.implode("\n", $urls).'
</urlset>
		');

		return $xml;
	}

}
?>