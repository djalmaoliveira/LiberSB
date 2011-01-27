<?php
Liber::loadModel('TableModel');
/**
*   @package Content
*/



/**
*   Class model for Content table.
*/
class Content extends TableModel {

    function __construct () {
        parent::__construct();
        $this->table   = 'content';
        $this->idField = 'content_id';

        $this->aFields = Array (
            'content_id'        => Array('', 'Content', 0),
            'content_type_id'   => Array('', 'Type',    Validation::NOTNULL),
            'title'             => Array('', 'Title',   Validation::NOTNULL),
            'body'              => Array('', 'Body',    Validation::NOTNULL),
            'datetime'          => Array('', 'Date/Time', Validation::NOTNULL),
        );
    }

    /**
    *   Special filter rules for 'body' field, allowing only specified tags according with html editor features.
    */
    function fieldFilter() {
        foreach ( $this->aFields as $field => $arr ) {
            if ( $field == 'body'  ) {
                // allowed tags
                $allow_tags = "<a><strong><em><ul><li><ol><span><p><br><img><table><tr><td><hr><object><embed><div>";
                $this->aFields[$field][0] = strip_tags($arr[0], $allow_tags);

            } else {
                $this->aFields[$field][0] = strip_tags($arr[0]);
            }
        }
    }

    /**
    *   Get will retrieve by 'title' or 'content_id' field.
    *   @param mixed $id
    *   @return boolean
    */
    function get($id) {
        if ( !is_numeric($id) ) {
            $out = $this->searchBy('title', $id);
            if ( !$out ) { return false; }
            return $this->loadFrom($out[0]);
        } else {
            return parent::get($id);
        }
    }

    /**
    *   Return the total of records by content_type_id.
    *   The Array returned has 'content_type_id' and 'total' fields.
    *   @return Array
    */
    function getTotalByContentType() {
        $sql = "
            select
                content_type_id,
                count(content_type_id) as total
            from
                content
            group by
                content_type_id
        ";
        $q   = $this->db()->prepare($sql);
        $ret = $q->execute( );
        if (!$ret) { return Array(); }
        return $this->returnKeys($q);
    }


    /**
    *   Return lasts contents by type.
    *   @param integer $content_type_id
    *   @param integer $count
    *   @param integer $sizeText
    *   @return Array
    */
    function lastContentsByType($content_type_id, $count=10, $sizeText=200) {
        $sql = "
            select
                content_id,
                content_type_id,
                title,
                substring(body, 1,:sizeText) as body,
                datetime
            from
                $this->table
            where
                content_type_id=:content_type_id
            order by
                content_id desc
            limit $count
        ";

        $q   = $this->db()->prepare($sql);
        $ret = $q->execute( Array(
                                ':sizeText' =>  $sizeText,
                                ':content_type_id' =>   $content_type_id )
                            );
        if ( !$ret ) { return Array();}
        return $this->returnKeys($q);
    }


    /**
    *   Return titles of lasts contents.
    *   @param integer $count
    *   @return Array
    */
    function lastContents($count=5) {
        $sql = "
            select
                content_id,
                content_type_id,
                title,
                datetime
            from
                $this->table
            order by
                content_id desc
            limit $count
        ";

        $q   = $this->db()->prepare($sql);
        $ret = $q->execute();
        if ( !$ret ) { return Array();}
        return $this->returnKeys($q);
    }

}

?>