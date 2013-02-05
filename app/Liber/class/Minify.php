<?php
/**
*   Class used to minify HTML, CSS and JavaScript code.
*   .
*  @package classes
*/
class Minify {

    /**
    *   Minify a CSS code.
    *   @param String $css
    *   @return String
    */
    static public function css($css) {
        Liber::loadClass('Minify_CSS_Compressor');
        return Minify_CSS_Compressor::process($css);
    }

    /**
    *   Minify a HTML code.
    *   @param String $html
    *   @return String
    */
    static public function html($html) {
        Liber::loadClass('Minify_HTML');
        return Minify_HTML::minify($html);
    }

    /**
    *   Minify a JavaScript code.
    *   @param String $js
    *   @return String
    */
    static public function js($js) {
        Liber::loadClass('JSMinPlus');
        return JSMinPlus::minify($js);
    }

}

?>