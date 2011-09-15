<?php
/**
*   @package core.class
*/


/**
*   Generic class to manipulates some items like 'shop cart'.
*   It uses session to store this items.
*   Context is like a namespace to separate the stored data.
*/
class Cart {

    protected $session;

    function __construct( $context = 'defaultCart' ) {
        Liber::loadClass('Session', true);
        $this->session = &$_SESSION[$context];
    }


    /**
    *   Insert a item and return id of it.
    *   If $id specified, $data will be replace current data stored.
    *   @param mixed $data
    *   @param integer $id
    *   @return integer
    */
    public function insert($data, $id=null) {
        if ( $id == null ) {
            if ( count($this->session) == 0 ) {
                $id = 1;
            } else {
                end($this->session);
                $id = (key($this->session))+1;
            }
        }
        $this->session[$id] = $data;
        return $id;
    }



    /**
    *   Get a item from cart by a specified $id.
    *   If you specify a $field, it will return the value of it stored on $item.
    *   If not specified any args, it will return all Array of items.
    *   @param integer $id
    *   @param String $field
    *   @return mixed
    */
    public function get($id=null, $field=null) {
        $args = func_num_args();
        if ( $args == 0 ) {
            return $this->session;
        } elseif ( $args == 1 ) {
            return $this->session[$id];
        } else {
            return $this->getField($this->session[$id], $field);
        }
    }


    /**
    *   Clear all Cart or specified $id on stored cart.
    *   @param integer $id
    */
    public function clear($id=null) {
        if ( $id == null ) {
            $this->session = Array();
        } else {
            unset($this->session[$id]);
        }
    }


    /**
    *   Search if a item exists in cart.
    *   You have to specify a key/value Array to search on cart and will return id of it if matched.
    *   @param Array $aSearch
    *   @return integer
    */
    public function search($aSearch=null) {
        if ( is_array($aSearch) ) {
            foreach( $this->session as $id => $data ) {
                if ( $this->getField($data, key($data)) != null ) {
                    return $id;
                }
            }
        }
        return null;
    }


    /**
    *   Check and retrieve value of $data from specified $field.
    *   $data must be Array or Object to get it.
    *   @param mixed $data <Array|Object>
    *   @param String $field
    *   @return mixed
    */
    private function getField($data, $field) {
        if ( is_array($data) ) {
            return isset($data[$field])?$data[$field]:null;
        } elseif ( is_object($data) ) {
            return isset($data->$field)?$data->$field:null;
        }
        return null;
    }

}

?>