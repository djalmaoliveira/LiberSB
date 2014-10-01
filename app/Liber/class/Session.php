<?php
/**
*   Class that manage values stored in session.
*   It manage sessions using a context during its creation.
*   Context is like a namespace to separate the stored data.
*   @package classes
*/
class Session {

    protected $context;

    /**
     * Instance new object and start a session.
     * @param string $context namespace
     * @param string $id      specific session ID
     */
    function __construct( $context = 'default', $id=null ) {

        $this->context = $context;
        if ( !self::started() ) {
            session_start();
        }
        if ( $id ) { session_id($id); }
    }

    /**
     * Return if there is a session started.
     * @return boolean
     */
    static function started() {
        return !(session_id()=='');
    }

    /**
    *   Set field/value to context session.
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
    *   Return array of field/values from current context session.
    *   @return Array
    */
    function toArray() {
        if ( isset($_SESSION[$this->context]) ) {
            return $_SESSION[$this->context];
        }
        return array();
    }


    /**
    *   Clean current context session.
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