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
	$url = content_url_($aContent, true);
    $html = "
        <div class=\"content_box\">
            <h3><a href='".$url."'>".$aContent['title']."</a></h3>
            ".date('Y/m/d',strtotime($aContent['create_datetime']))."
            <br/><br/>
            <div class='content_body'>".($isSummary?strip_tags($aContent['body'])."...<br/> <a href=\"$url\" title=\"see the entire content\"> read more...</a>":$aContent['body'])." </div>
            <div class=\"cleaner\"></div>
            <div id=\"comment_nav\">
                <a href='javascript:void(0)' onmouseover=\"leaveComment('".url_to_('/comment', true)."?content_id=".$aContent['content_id']."', this)\">Leave a comment</a>
                <a href='".content_comment_url_($aContent)."'>See comments</a>
            </div>

        </div>
        <div class=\"content_box_bottom\" >
        </div>
    ";

    if ($return) return $html;
    echo $html;
}

function content_comment_url_($aContent, $page=1) {
    $oCache = Liber::loadClass('CommentCache', 'APP', true);
    return $oCache->url($aContent, $page);
}



/**
*   Return or print the comment area.
*   @param Array   $aContent
*   @param boolean $return
*   @return String
*/
function content_comment_show_($aComment, $return=false) {
    if ( !$aComment ) { return; }

    $html = "
		<div class='comment_box'>
			<div class=\"content_box\">
				<h4>".$aComment['name']."</h4>
				".dt_timesince_($aComment['datetime'])." ago
				<br/><br/>
				<div class='comment_comment'>".nl2br($aComment['comment'])."</div>
				<div class=\"cleaner\"></div>
			</div>
		</div>
		<br/>
    ";

    if ($return) return $html;
    echo $html;
}

?>