<?php
Liber::loadClass('TableModel');
/**
*   @package Content
*/



/**
*   Class model for ContentType table.
*/
class ContentType extends TableModel {

    function __construct () {
        parent::__construct( Liber::db('default') );
        $this->table   = 'content_type';
        $this->idField = 'content_type_id';

        $this->aFields = Array (
            'content_type_id'   => Array('', 'Type',    0),
            'description'       => Array('', 'Description',   Validation::NOTNULL),
            'status'       		=> Array('', 'Status',   Validation::NOTNULL)
        );
    }


    /**
    *   Get will retrieve by 'description' or 'content_id' field.
    *   @param mixed $id
    *   @return boolean
    */
    function get($id) {
        if ( !is_numeric($id) ) {
            $out = $this->searchBy('description', $id)->fetchAll();
            if ( !$out ) { return false; }
            return $this->loadFrom($out[0]);
        } else {
            return parent::get($id);
        }
    }

	/**
	*	Return Status description by $type specified.
	*	Return Array os status if $type is not specified.
	*	@param String $type
	*	@return mixed
	*/
	function status($type=null) {
		static $types = Array(
						'A'	=> 'Active',
						'S' => 'Suspended'
						);
		if ( $type ) {
			return $types[$type];
		} else {
			return $types;
		}
	}

	/**
	*	Return list by $status.
	*	@param String $status
	*	@return Array
	*/
	function listByStatus($status) {
        $sql = "
            select
                content_type_id,
                description
            from
                $this->table
			where
				status=:status
        ";

        $q   = $this->db()->prepare($sql);
        $ret = $q->execute( Array(':status' => $status) );
        if ( !$ret ) { return Array();}
        return $q->fetchAll();
	}
}

?>