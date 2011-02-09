<?php
Liber::loadModel('TableModel');
/**
*   @package Content
*/



/**
*   Class model for Config table.
*/
class Config extends TableModel {

    function __construct () {
        parent::__construct();
        $this->table   = 'config';
        $this->idField = 'id';

        $this->aFields = Array (
			'id'        		=> Array('', 'ID', 0),
            'site_name'        	=> Array('', 'Site Name', 0),
            'contact_email'    	=> Array('', 'Contact Email', Validation::EMAIL),
            'twitter_url'      	=> Array('', 'Twitter', Validation::URL),
            'facebook_url'     	=> Array('', 'Facebook', Validation::URL)
        );
    }

	function data( $aFields ) {
		if ( !is_array($aFields) ) {
			$aFields = Array( $aFields );
		}

		$sql = "
			select ".implode(',', $aFields)."
			from
				$this->table
			where
				id=1
		";

        $q   = $this->db()->prepare($sql);
        $ret = $q->execute();

		if ( count($aFields) == 1 ) {
			$arr = $q->fetch(PDO::FETCH_ASSOC);
			return current($arr);
		} else {
			return $q->fetch(PDO::FETCH_ASSOC);
		}
	}

	function save( $aData=Array() ) {
		$this->field('id', 1);
		return parent::save($aData);
	}


}

?>