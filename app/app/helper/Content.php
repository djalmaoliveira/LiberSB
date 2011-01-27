<?
/**
*   @package application
*/

/**
*   Return or print the content area filled.
*   @param Array $aContent
*   @param boolean $return
*   @return String
*/
function content_show_($aContent, $return=false) {
    if ( !$aContent ) { return; }
    $html = "
        <div class=\"content_box\">

            <h2><a href='".url_to_('/content/'.$aContent['content_type_id'].'_'.rawurlencode($aContent['title']).'.html', true)."'>".$aContent['title']."</a></h2>
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