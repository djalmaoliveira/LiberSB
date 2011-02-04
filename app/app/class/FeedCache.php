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
        $this->urlPattern = Liber::conf('APP_URL').'feeds/';
    }

    /**
    *   Create Content cached file from $url specified.
    *   @param String $url
    *   @return String
    */
    function create($url) {

        if ( !$this->matchUrl($url) ) { return ; }

        $urlPattern = '/'.str_replace(Liber::conf('APP_URL'), '', $url);

        $aUrl            = pathinfo($urlPattern);
        $filename        = rawurldecode($aUrl['filename']);

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
		if ( $this->put( str_replace(Liber::conf('APP_URL'), Liber::conf('APP_ROOT'), $this->url('rss2')) , $funky_cache) ) {
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
	*	@return boolean
	*/
	function cleanCache() {
		parent::clean( str_replace(Liber::conf('APP_URL'), Liber::conf('APP_ROOT'), rawurldecode($this->urlPattern)) );
	}

}
?>