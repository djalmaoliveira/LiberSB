<?php
Liber::loadModel('TableModel');
/**
*   @package Content
*/



/**
*   Class model for ContentType table.
*/
class ContentType extends TableModel {

    function __construct () {
        parent::__construct();
        $this->table   = 'content_type';
        $this->idField = 'content_type_id';

        $this->aFields = Array (
            'content_type_id'   => Array('', 'Type',    Validation::NOTNULL),
            'description'       => Array('', 'Description',   Validation::NOTNULL),
        );
    }


    /**
    *   Get will retrieve by 'description' or 'content_id' field.
    *   @param mixed $id
    *   @return boolean
    */
    function get($id) {
        if ( !is_numeric($id) ) {
            $out = $this->searchBy('description', $id);
            if ( !$out ) { return false; }
            return $this->loadFrom($out[0]);
        } else {
            return parent::get($id);
        }
    }

}

?>