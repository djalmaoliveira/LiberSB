<?php

/**
 *
 */
class FeedController extends Controller{

    var $oTPL;

    function __construct($p) {
        parent::__construct($p);
        Liber::loadHelper(Array('Url', 'HTML'));
		Liber::loadModel('Config');
        $this->oTPL = $this->view()->template();
    }


    public function index() {
    }

	function rss2() {
		$oFeed 		= Liber::loadClass('Feed', true);
		$oContent 	= Liber::loadModel('Content', true);
		$oCache 	= Liber::loadClass('ContentCache', 'APP', true);
		$contents 	= $oContent->lastContentsFeed();
		$items 		= Array();
		foreach($contents as $content) {
			$items[] = Array(
					'title' 	=> $content['title'],
					'description' 	=> strip_tags($content['body']).'[...]',
					'datetime' 	=> $content['datetime'],
					'url'		=> $oCache->url($content)
			);
		}

		header("Content-type: text/xml; charset=utf-8");
		$oFeed->load( Array('items'=>$items)  );
		$oFeed->show('rss2');
	}

}

?>