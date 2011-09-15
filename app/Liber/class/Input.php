<?php
/**
*   @package core.class
*/


/**
*   Class that manipulates input data from client.
*   Parse some filters to clean that values.
*/
class Input {

    /**
    *   Do a cleaning .
    *
    */
    protected static function clean($value) {
        if ( $value !== NULL ) {
            return $value;
        }

    }

    /**
    *   Return a identified index from _GET var, or all _GET if not specified.
    *   @param String $field 
    *   @return mixed
    */
    public static function get($field=null) {
        return self::clean( isset($_GET[$field])?$_GET[$field]:($field==null?$_GET:NULL) );
    }

    /**
    *   Return a identified index from _POST var, or all _POST if not specified.
    *   @param String $field 
    *   @return mixed
    */
    public static function post($field=null) {
        return self::clean( isset($_POST[$field])?$_POST[$field]:($field==null?$_POST:NULL) );    
    }
    
    /**
    *   Return a identified index from _COOKIE var, or all _COOKIE if not specified.
    *   @param String $field 
    *   @return mixed
    */
    public static function cookie($field=null) {
        return self::clean( isset($_COOKIE[$field])?$_COOKIE[$field]:($field==null?$_COOKIE:NULL) );
    }
    
    /**
    *   Return a identified index from _FILES var, or all _FILES if not specified.
    *   @param String $field
    *   @return mixed
    */
    public static function file($field=null) {
        return self::clean( isset($_FILES[$field])?$_FILES[$field]:($field==null?$_FILES:NULL) );
    }

}

?>
