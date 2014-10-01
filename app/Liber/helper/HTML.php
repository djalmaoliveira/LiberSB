<?php
/**
 *  Html helpers.
 *
 * @package     helpers_html
 * @author		djalmaoliveira@gmail.com
 * @copyright	djalmaoliveira@gmail.com
 * @license     license.txt
 * @link
 * @since		Version 1.0
 */

/**
*   Set or print Html tags for external CSS and JS files included in 'head' tag.
*   <code>
*   Usage:
*       // external assets
*       html_header_('css', 'http://abc.com/somefile.css');
*       // absolute path from root path (i.e. www.domain.com/mycss/my.css)
*       html_header_('css', '/mycss/my.css');
*       // relative to default /css folder (i.e. www.domain.com/css/my.css)
*       html_header_('css', 'my.css');
*       // return only css
*       html_header_('css');
*       // print tags to use among html head tags.
*       html_header_();
*   </code>
*   @param String $type - 'css' or 'js'
*   @param String $url - Relative url to assets folder or external url starting with http:// or https://.
*   @return String - Html tags
*/
function html_header_($type=null, $url=null) {
    static $headers = Array('css'=>Array(), 'js'=>Array());
    static $f;
    if ( !$f ) {
        $f = create_function('$type, $urls', '
            $tag = "";
            foreach ($urls as $url) {
                if ( $url[0] == "/" ) {
                    $url = url_asset_($url, true);
                } elseif( (strpos($url, "://") === false ) ) {
                    $url = url_asset_("/$type/", true).$url;
                }
                if ( trim($type) == "css" ) {
                    $tag .= "<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\"  href=\"$url\" />\r\n";
                } else {
                    $tag .= "<script src=\"$url\" type=\"text/javascript\"></script>\r\n";
                }
            }
            return $tag;
        ');
    }

    $nargs = func_num_args();
    if ( $nargs == 0 ) {
        echo $f('css', $headers['css']);
        echo $f('js', $headers['js']);
    } elseif ( $nargs == 1 ) {
        echo $f($type, $headers[$type]);
    } else {
        $headers[$type][] = $url;
    }
}


/**
*   Set or print html meta tags with some data, that should be included in 'head' tag.
*   <code>
*   Usage:
*       // set a meta author content, used inside template files
*       html_meta_( Array('name'=>'author', 'content'=>'Liber framework') );
*       // print tags to use among html head tags.
*       html_meta_();
*   </code>
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
*   <code>
*   Usage:
*       // set title of page, used inside template files
*       html_title_( 'title of page' );
*       // print current stored title
*       html_title_();
*       // return stored title
*       $title = html_title_(true);
*   </code>
*   @param String/boolean $title
*   @return String
*/
function html_title_( $title=false ) {
    static $_title = '';

    if ( is_bool($title) ) {
        if ( $title ) { return $_title;}
        echo $_title;
    } else {
        $_title = $title;
    }
}


/**
*   Set or return script String to use on head tags.
*   This helper could be used inside template files.
*   <code>
*   Usage:
*       // set script content
*       html_script_( '<script>alert("My JS Code");</script>' );
*       // set script content replacing any existent content
*       html_script_( 'alert("Hi!");', true );
*       // return stored content
*       $script = html_script_(true);
*       // print stored content
*       html_script_();
*   </code>
*   @param String $script
*   @param boolean  $replace  If true replace any existent content
*   @return String
*/
function html_script_($script=null, $replace=false) {
    static $_scripts = Array();

    if ( func_num_args() == 0 ) {
        echo implode("\n", $_scripts); return;
    }
    if ( is_bool($script) and $script ) {
        return implode("\n", $_scripts);
    }
    if ( $script ) {
        if ($replace) { $_scripts = Array(); }
        $_scripts[] = $script;
    }
}


/**
*   Set or return CSS style content to use on head tags.
*   This helper could be used inside template files.
*   <code>
*   Usage:
*       // set style content
*       html_style_( '<style>h1 {color:green;}</style>' );
*       // set style content replacing any existent content
*       html_style_( '.alert {color:red;}', true );
*       // return stored content
*       $style = html_style_(true);
*       // print stored content
*       html_style_();
*   </code>
*   @param String $style
*   @param boolean  $replace If true replace any existent content
*   @return String
*/
function html_style_($style=null, $replace=false) {
    static $_styles = Array();

    if ( func_num_args() == 0 ) {
        echo implode("\n", $_styles); return;
    }
    if ( is_bool($style) and $style ) {
        return implode("\n", $_styles);
    }
    if ( $style ) {
        if ($replace) { $_styles = Array(); }
        $_styles[] = $style;
    }
}

?>