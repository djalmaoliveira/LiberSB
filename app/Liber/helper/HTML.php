<?php
/**
 *
 * @package core.helpers
 * @author		djalmaoliveira@gmail.com
 * @copyright	djalmaoliveira@gmail.com
 * @license     license.txt
 * @link
 * @since		Version 1.0
 */

/**
*   Set or print Html tags for external CSS and JS files included in 'head' tag.
*   Usage:  html_header_('css', 'my.css'); // register a css file in template file, used inside template files
*           html_header_(); // print tags to use among html head tags.
*   @param String $type - 'css' or 'js'
*   @param String $url - Relative url to assets folder.
*   @return String - Html tags
*/
function html_header_($type=null, $url=null) {
    static $headers = Array('css'=>Array(), 'js'=>Array());
    if ( $type == null ) {
        $tag = '';
        foreach ($headers['css'] as $url) {
            $url = ($url[0] != '/')?url_asset_('/css/', true).$url:url_asset_($url, true);
            $tag .= '<link rel="stylesheet" type="text/css" media="screen"  href="'.$url.'" />'."\r\n";
        }
        foreach ($headers['js'] as $url) {
            $url = ($url[0] != '/')?url_asset_('/js/', true).$url:url_asset_($url, true);
            $tag .= '<script src="'.$url.'" type="text/javascript"></script>'."\r\n";
        }
        echo $tag;
    } else {
        $headers[$type][] = $url;
    }
}

/**
*   Set or print html meta tags with some data, that should be included in 'head' tag.
*   Usage:  html_meta_( Array('name'=>'author', 'content'=>'Liber framework') ); // set a meta author content, used inside template files
*           html_meta_(); // print tags to use among html head tags.
*   @param Array $aData
*   @return String - Html meta tags
*/
function html_meta_( $aData=null ) {
    static $metas = Array();
    $tag = '';
    if ( is_array($aData) ) {
        foreach( $aData as $attr => $value) {
            $tag .= $attr.'="'.$value.'" ';
        }
        $metas[] = "<meta $tag />";
    } else {
        echo implode("\r\n", $metas);
    }
}


/**
*   Set or get the text to html title tag.
*   Usage:  html_title_( 'title of page' ); // set title of page, used inside template files
*           html_title_(); // get title
*   @param String $title
*   @return String
*/
function html_title_( $title=null ) {
    static $_title = '';

    if ( !empty($title) ) {
        $_title = $title;
    } else {
        return $_title;
    }
}

/**
*   Set or return script String to use on head tags.
*   @param String $script
*   @return String
*/
function html_script_($script=null) {
    static $_scripts = Array();

    if ( $script ) {
        $_scripts[] = $script;
    } else {
        return implode("\n", $_scripts);
    }
}
?>