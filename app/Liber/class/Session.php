<?php
/**
*   @package core.class
*/


/**
*   Class that manage values stored in session.
*   It manage sessions using a context during its creation.
*   Context is like a namespace to separate the stored data.
*/
class Session {

    public $context;
    static $started = false;

    function __construct( $context = 'default', $id=null ) {
    
        $this->context = $context;
        if ( $id ) { session_id($id);} 
        if ( !self::$started  ) { 
            session_start(); 
            self::$started = true;
        }
    }


    /**
    *   Set field/value to session.
    *   If don't specified $v, then return the value of $f.
    *   @param String $f  - Field Name
    *   @param mixed $v  - Value
    *   @return mixed
    */
    function val($f, $v=NULL) {
        if (  $v === NULL ) {
            return isset($_SESSION[$this->context][$f])?$_SESSION[$this->context][$f]:null;
        } elseif( empty ($v) ) {
            $_SESSION[$this->context][$f] = NULL;
        } else {
            $_SESSION[$this->context][$f] = $v;
        }
    }

    /**
    *   Return array of field/values from current session.
    *   @return Array
    */
    function toArray() {
        return $_SESSION[$this->context];
    }


    /**
    *   Cleans current session.
    *
    */
    function clear() {
        unset($_SESSION[$this->context]);
    }

    /**
    *   Ends current session.
    *
    */
    function destroy() {
        session_destroy();
    }

}



?>
