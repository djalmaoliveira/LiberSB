<?php
/**
*   Class base from others Template Engines classes.
*   .
*   @package classes
*/
class TemplateEngine {

    function __construct($aSettings=Array()) {

    }

    /**
    *   Load and parse a template from file.
    *   @param String $fileName
    *   @param mixed $data
    *   @param boolean $output -  true, return a processed data, default false.
    *   @return String
    */
    public function load( $fileName, $data=null, $output=false ) {
        if ( is_array($data) )  { extract($data); }
        ob_start();
        include "$fileName";

        if ($output) {
            $data = ob_get_contents();
            ob_end_clean();
            return $data;
        }
    }
}
?>