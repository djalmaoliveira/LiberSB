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
        $CSSmin = Liber::load('CSSmin', Liber::conf('BASE_PATH').'vendor/CSSmin/', true);
        return $CSSmin->run($css);
    }

    /**
    *   Minify a HTML code.
    *   @param String $html
    *   @return String
    */
    static public function html($html) {
        Liber::load('Minify_HTML', Liber::conf('BASE_PATH').'vendor/Minify_HTML/' );
        return Minify_HTML::minify($html);
    }

    /**
    *   Minify a JavaScript code.
    *   @param String $js
    *   @return String
    */
    static public function js($js) {
        Liber::load('JSMinPlus', Liber::conf('BASE_PATH').'vendor/JSMinPlus/');
        return JSMinPlus::minify($js);
    }

}

?>