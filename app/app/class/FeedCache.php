<?php
Liber::loadClass('Funky');
/**
*   @package content
*/


/**
*   Manage the rules os cache files of Feed.
*/
class FeedCache extends Funky {


    function __construct() {
        $this->urlPattern = url_to_('/',true).Liber::conf('FUNKY_PATH');
    }

	/**
	*	Override default match pattern and return parts of matched URL.
	*	Match: FUNKY_PATH/[file].xml
	*	@param String $url
	*	@return Array
	*/
    function matchUrl( $url ) {
		$urlPattern = '/'.str_replace($this->urlPattern, '', $url);
		$aUrl = pathinfo( str_replace($this->urlPattern, '', $urlPattern) );
		if ( in_array($aUrl['filename'], Array('rss2', 'atom')) and $aUrl['extension'] == 'xml') {
			return $aUrl;
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

		$oFeed 		= Liber::loadClass('Feed', true);
		$oContent 	= Liber::loadModel('Content', true);
		$oCache 	= Liber::loadClass('ContentCache', 'APP', true);
		$oConfig	= Liber::loadModel('Config', true);
		$contents 	= $oContent->lastContentsFeed();
		$items 		= Array();
		foreach($contents as $content) {
			$items[] = Array(
					'title' 	=> $content['title'],
					'description' 	=> strip_tags($content['body']).'[...]',
					'datetime' 	=> $content['datetime'],
					'url'		=> $oCache->url($content),
					'uid'		=> $content['content_id'].$content['datetime']
			);
		}

		$oFeed->load( Array(
						'title' 	=> $oConfig->data('site_name'),
						'items'		=> $items,
						'feed_url'	=> $this->url('rss2')
					)  );

		$funky_cache = $oFeed->rss2();
		if ( $this->put( str_replace(url_to_('/',true), Liber::conf('APP_ROOT'), $this->url('rss2')) , $funky_cache) ) {
			return $funky_cache;
		}

    }

    /**
    *   Return a public URL Content of cached file from $aContent specified.
    *   @param String $type
    *   @return String
    */
    function url($type) {
        return $this->urlPattern.$type.'.xml';
    }

	/**
	*	Clean cached files.
	*	If empty $type it will clean all feed files.
	*	@param String $type - Type of feed: rss2, atom
	*	@return boolean
	*/
	function cleanCache($type=null) {
		$path = str_replace(url_to_('/',true), Liber::conf('APP_ROOT'), rawurldecode($this->urlPattern));
		if ( $type ) {
			return parent::clean( $path.$type.'.xml' );
		} else {
			foreach( Array('rss2', 'atom')  as $filename) {
				parent::clean( $path.$filename.'.xml' );
			}
		}
	}

}
?>