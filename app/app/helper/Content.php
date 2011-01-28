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
function content_show_($aContent, $return=false) {
    if ( !$aContent ) { return; }
    $html = "
        <div class=\"content_box\">

            <h2><a href='".content_url_($aContent, true)."'>".$aContent['title']."</a></h2>
            ".date('m/d/Y',strtotime($aContent['datetime']))."
            <br/><br/>
            <p>".$aContent['body']."</p>

            <div class=\"cleaner\"></div>
        </div><div class=\"content_box_bottom\"></div>
    ";

    if ($return) return $html;
    echo $html;
}



?>