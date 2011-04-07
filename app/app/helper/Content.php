<?
/**
*   @package application
*/

/**
*   Return a URL to content by $aContent specified.
*   @param Array $aContent
*   @return String
*/
function content_url_($aContent, $return=false) {
    $url = Liber::loadClass('ContentCache', 'APP',true)->url($aContent);
    if ($return) return $url;
    echo $url;
}

/**
*   Return or print the content area filled.
*   @param Array   $aContent
*   @param boolean $return
*   @return String
*/
function content_show_($aContent, $isSummary=true, $return=false) {
    if ( !$aContent ) { return; }

	$aData['url'] 	  	= content_url_($aContent, true);
	$aData['isSummary'] = &$isSummary;
	$aData['content']   = &$aContent;
	$aData['url_encoded'] = rawurlencode(rawurldecode($aData['url']));
	$v 				    = new View();
	$html 			    = $v->load('content.html', $aData, true);

    if ($return) return $html;
    echo $html;
}

/**
*	Return or print url of comment.
*	@param Array $aContent
*	@param integer $page
*	@param boolean $return
*	@return String
*/
function content_comment_url_($aContent, $page=1, $return=false) {
    $oCache = Liber::loadClass('CommentCache', 'APP', true);
	$url = $oCache->url($aContent, $page);
	if ($return) return $url;
    echo $url;
}

/**
*	Return the html content of comment form.
*	@return String
*/
function content_comment_form_() {
	Liber::loadHelper('Util', 'APP');
	Liber::loadHelper('Form');
	$aData['action']     = url_to_('/comment', true);
	return Liber::controller()->view()->load('comment_form.html', $aData, true);
}


/**
*   Return or print the comment area.
*   @param Array   $aContent
*   @param boolean $return
*   @return String
*/
function content_comment_show_($aComment, $return=false) {
    if ( !$aComment ) { return; }

	$aData['comment']   = &$aComment;
	$v 				    = new View();
	$html 			    = $v->load('comment.html', $aData, true);

    if ($return) return $html;
    echo $html;
}

?>