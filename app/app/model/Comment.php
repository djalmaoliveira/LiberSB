<?php
Liber::loadModel('TableModel');
/**
*   @package Content
*/



/**
*   Class model for Comment table.
*/
class Comment extends TableModel {

    function __construct () {
        parent::__construct();
        $this->table   = 'comment';
        $this->idField = 'comment_id';

        $this->aFields = Array (
            'comment_id'        => Array('', 'Comment', 0),
            'content_id'        => Array('', 'Content', Validation::NOTNULL),
            'name'              => Array('', 'Name',    0),
            'email'             => Array('', 'Email',   Validation::EMAIL),
            'comment'           => Array('', 'Comment', Validation::NOTNULL),
            'datetime'          => Array('', 'Date/Time', Validation::NOTNULL),
            'status'            => Array('W','Status', Validation::NOTNULL),
            'netinfo'           => Array('', 'Network Info', Validation::NOTNULL)
        );
    }


    /**
    *   Return lasts comments by content_id.
    *   @param integer $count
    *   @return Array
    */
    function lastCommentsByContent($content_id, $count=10) {
        $sql = "
            select
                *
            from
                $this->table
			where 
				content_id=:content_id
            order by
                comment_id desc
            limit $count
        ";

        $q   = $this->db()->prepare($sql);
        $ret = $q->execute( Array(':content_id' => $content_id) );
        if ( !$ret ) { return Array(); }
        return $this->returnKeys($q);
    }

}

?>