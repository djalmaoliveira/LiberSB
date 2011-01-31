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
    *   Return lasts comments by content_id and status.
	*	@param integer $content_id
	*	@param String $status - A => active, W => waiting be moderated
    *   @param integer $count - Max number records returned.
	*	@param integer $start - Start offset
    *   @return Array
    */
    function lastCommentsByContent($content_id, $status="A", $count=10, $start=0) {
        $sql = "
            select
                *
            from
                $this->table
			where
				content_id = :content_id
				and
				status = :status
            order by
                comment_id desc
            limit $count offset $start
        ";

        $q   = $this->db()->prepare($sql);
        $ret = $q->execute( Array(':content_id' => $content_id, ':status'=>$status) );
        if ( !$ret ) { return Array(); }
        return $this->returnKeys($q);
    }


	/**
	*	Customized search comments.
	*
	*/
	function search($terms, $aOptions=Array() ) {

		$sql = "
			select
				cm.comment_id,
				cm.datetime,
				cm.name,
				cm.email,
				cm.status,
				cn.title,
				cn.content_type_id,
				ct.description
			from
				comment cm left join content cn on (cn.content_id=cm.content_id)
				left join content_type ct on (cn.content_type_id=ct.content_type_id)
			where
				(cm.name like :name
				or
				cm.email like :email
				or
				cn.title like :title
				or
				ct.description like :description)
				".(isset($aOptions['where'])?$aOptions['where']:'')."
			order by cm.comment_id desc

			limit ".(isset($aOptions['limit'])?$aOptions['limit']:'10')." offset ".(isset($aOptions['start'])?$aOptions['start']:'0')."
		";

		$aParams = Array(
			':name'  => "%$terms%",
			':email' => "%$terms%",
			':title' => "%$terms%",
			':description' => "%$terms%",
		);

        $q   = $this->db()->prepare($sql);
        $ret = $q->execute( $aParams );
        if ( !$ret ) { return Array(); }
        return $this->returnKeys($q);
	}

}

?>