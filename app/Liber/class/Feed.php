<?php
/**
*   Generic class create feeds.
*	Supported types: rss2
*  @package classes
*/
class Feed {
	/**
	*	Default feed fields
	*
	*	@var $aFeed
	*/
	protected $aFeed = Array(
						'encoding' 		=> 'utf-8',
						'title'	   		=> 'Feed',
						'feed_url'		=> '',
						'description' 	=> '',
						'items'			=> Array()
					);
	/**
	*	Default feed item fields.
	*	'uid' is a unique identification of item.
	*	If 'permanent_url' has a real URL, then 'uid' will be replaced by it.
	*	'datetime' field should have 'YYYY-MM-DD HH:II:SS' format.
	*	@var $aItem
	*/
	protected $aItem = Array(
						'title' 		=> '',
						'url'			=> '',
						'datetime' 		=> '',
						'description' 	=> '',
						'uid'			=> '',
						'permanent_url' => ''
					);

	/**
	*	Load data feed.
	*	@param Array $aFeed
	*
	*/
	function load($aFeed=Array()) {
		if ( $aFeed ) {
			$this->aFeed = array_merge($this->aFeed, $aFeed);
		}
	}

	/**
	*	Return a RSS2 XML code of feed from loaded data.
	*	@param Array $aFeed - Can be specified a feed content.
	*	@return String
	*/
	function rss2( $aFeed=Array() ) {
		$this->load($aFeed);

		$items = Array();
		foreach( $this->aFeed['items'] as $item ) {
			$item = array_merge($this->aItem, $item);
			$item['uid'] = $item['permanent_url']?$item['permanent_url']:$item['uid'];
			$items[] = '
				<item>
					<title>'.$item['title'].'</title>
					<link>'.$item['url'].'</link>
					<pubDate>'.date('r', strtotime($item['datetime'])).'</pubDate>
					<description><![CDATA['.$item['description'].']]></description>
					<guid isPermaLink="'.($item['permanent_url']?'true':'false').'">'.$item['uid'].'</guid>
				</item>
			';
		}

		$feed = '
			<?xml version="1.0" encoding="'.$this->aFeed['encoding'].'"?'.'>
			<rss version="2.0"
			xmlns:content="http://purl.org/rss/1.0/modules/content/"
			xmlns:wfw="http://wellformedweb.org/CommentAPI/"
			xmlns:atom="http://www.w3.org/2005/Atom"
			>
			<channel>
				<title>'.$this->aFeed['title'].'</title>
				<atom:link href="'.$this->aFeed['feed_url'].'" rel="self" type="application/rss+xml" />
				<link>'.Liber::conf('APP_URL').'</link>
				<description>'.$this->aFeed['description'].'</description>
				<pubDate>'.date('r').'</pubDate>
				<language>'.Liber::conf('LANG').'</language>

			'.implode("\n",$items).'

			</channel>
			</rss>
		';

		return trim($feed);
	}


	/**
	*	Show/print the xml data of type of feed specified, setting the correct header content-type.
	*	@param String $type
	*/
	function show($type) {
		header("Content-type: text/xml; charset=".$this->aFeed['encoding']);
		switch ($type) {
			case 'rss2':
				echo $this->rss2();
			break;
		}
	}

}


?>