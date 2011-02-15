<?php

class TableModel {

    var $table;
    var $idField;
    protected $lastErrors = Array();
    protected $errorsValidation = Array();
    protected $errorsFields = Array();

    protected $aFields  = Array();
    protected $aChanges = Array();

    /**
     *  Array of functions for validations
     *  @var Array
     */
    protected $fValidations = Array();

    function TableModel() {
        Liber::loadClass('Validation');
    }

    function setValidation( $field, $switch ) {
        static $aValidation = Array();
        if ( $switch == 'on' ) {
            if ( isset($aValidation[$field]) ) {
              $this->aFields[$field] = $aValidation[$field];
            }
        } elseif ( $switch == 'off' ) {
            $aValidation[$field] = $this->aFields[$field];
            $this->aFields[$field][2] = 0;
        }
    }

    function db() {
        return Liber::db();
    }

    function beforeSave() {
        return true;
    }

    function afterSave() {
        return true;
    }

    /**
    *   Return true if field was changed.
    *   @return boolean;
    */
    function changed($field) {
        return in_array($field, $this->aChanges);
    }

    /**
    *   Insert a new record, returning true if get to do it.
    *   To setup a field with null, fill it with empty ''.
    *   @return boolean
    */
    function insert($data=Array()) {
        if ( count($data) >0 ) { $this->loadFrom($data); }
        $this->beforeSave();
        $this->fieldFilter();
        if ( !$this->validateData() ) { return false; }

        $aFields = $this->toArray();

        $fields  = implode(', ', array_keys($aFields));
        $aParams = Array();
        foreach( $aFields as $k => $v ) {
            $param = ":p$k";
            $aParams[$param] = $v;
        }

        $values = implode(',', array_keys($aParams));
        $sql    = 'insert into '.$this->table.' ('.$fields.') values ('.$values.')';
        $q      = $this->db()->prepare($sql);
        $ret    = $q->execute( $aParams );
        if ( $ret !== false ) {
            $this->field($this->idField, $this->db()->lastInsertId());
            $this->afterSave();;
            $this->aChanges = Array();
            return true;
        }
        Liber::log()->add( get_class($this).'->'.__FUNCTION__.": ".print_r($sql,true)."\n".print_r($q->errorinfo(),true).print_r($aParams,true), 'error' );
        return false;
    }


    /**
    *   Update a existing record, returning true if get to do it.
    *   To setup a field with null, fill it with empty ''.
    *   @param Array $data - $data[field] = value
    *   @return boolean
    */
    function update($data=Array()) {
        if ( count($data) >0 ) { $this->loadFrom($data); }
        $this->beforeSave();
        $this->fieldFilter();
        if ( !$this->validateData() or !is_numeric($this->aFields[$this->idField][0]) ) { return false; }

        $aFields = $this->toArray();

        $aParams = Array();
        $aSets   = Array();
        foreach( $this->aChanges as $k  ) {
            if ( is_null($aFields[$k]) ) {
                $aSets[] = "$k=NULL";
            } else {
                $param = ":$k";
                $aSets[] = "$k=$param";
                $aParams[$param] = trim($aFields[$k]);
            }
        }

        $values = implode(', ', $aSets );
        $sql    = 'update '.$this->table.' set '.$values.' where '.$this->idField.'='.$this->field($this->idField);
        $q      = $this->db()->prepare($sql);
        $ret    = $q->execute($aParams);
        if ( $ret !== false ) {
            $this->afterSave();;
            $this->aChanges = Array();
            return true;
        }

        Liber::log()->add( get_class($this).'->'.__FUNCTION__.': '.print_r($aSets,true)."\n".print_r($q->errorinfo(),true).print_r($aParams, true), 'error' );
        return false;

    }


    /**
    *   Delete a existing record, returning true if get to do it.
    *   @return boolean
    */
    function delete($id=null) {
        if ( !is_numeric($id) ) {
            $id = $this->field($this->idField);
        }
        $sql = "delete from $this->table where ".$this->idField.'='.$id;
        $ret = $this->db()->exec($sql);
        if ( $ret == 1 ) {
            $this->aChanges = Array();
            return true;
        }
        Liber::log()->add( get_class($this).'->'.__FUNCTION__.': '.print_r($sql,true)."\n".print_r($this->db()->errorinfo(),true), 'error' );
        return false;
    }


    /**
    *   Save current data.
    *   @param Array $data
    *   @return boolean
    */
    function save($data=Array()) {

        $id = $this->field($this->idField);
        if ( empty( $id ) ) {
            $retS = $this->insert( $data );
        } else {
            $retS = $this->update( $data );
        }

        return $retS;
    }

    /**
    *   Default strip_tags filter for fields.
    */
    function fieldFilter() {
        $arr = current($this->aFields);
        do  {
			if ( is_null($arr[0]) ) { continue; }
            $this->aFields[key($this->aFields)][0] = strip_tags($arr[0]);
        } while ( ($arr=next($this->aFields)) );
    }

    /**
    *   Set or Get a value from field specified.
    *   @param String $f - Field Name
    *   @param String $v - Field Value
    *   @param boolean $notUpdate - Indicates if field must be set up as changed.
    *   @return mixed
    */
    function field($f, $v=NULL, $notUpdate=false) {
        if ( func_num_args() == 1 ) {
            return ($this->aFields[$f][0]);
        } elseif( is_null($v) ) {
            $this->aFields[$f][0] = $v; // set with null value
            $notUpdate?false:($this->aChanges[] = $f);
        } else {
            $this->aFields[$f][0] = $v;
            $notUpdate?false:($this->aChanges[] = $f);
        }
    }


    /**
     *  Return $f field description.
     *  If $v specified, it will attribute this value to field $f.
     *  @param string $f
     *  @param string $v
     *  @return string
     */
    function desc($f, $v=NULL) {
        if (  func_num_args() == 1 ) {
            return $this->aFields[$f][1];
        } elseif( empty ($v) ) {
            $this->aFields[$f][1] = '';
        } else {
            $this->aFields[$f][1] = $v;
        }
    }

    /**
     *  Return a value of $aFields specified from $id.
     *  @param integer $id
     *  @param Array $aFields
     *  @return Array
     */
    function getField($id, $aFields=Array()) {
        $fields = implode(',', $aFields);
        $sql    = "select $fields from $this->table where  $this->idField=:id";
        $q      = $this->db()->prepare($sql);
        $ret    = $q->execute( Array(':id' => $id) );
        if ( $ret === false ) {
            Liber::log()->add( get_class($this).'->'.__FUNCTION__.": [Table:$this->table => $this->idField :".print_r($val,true)."]  \n".print_r($q->errorinfo(),true), 'error' );
            return false;
        }

        $aRs = $q->fetchAll(PDO::FETCH_ASSOC);
        return $aRs[0];
    }

    /**
     *  Return or load a model from table.
     *  @param mixed $val <integer | Array>
     *  @return mixed <boolean | Array>
     */
    function get($val) {
        $aParams = Array();
        $a = Array();

        if ( is_array($val) ) {
            if ( count($val) == 0 ) {
                return Array();
            }
            $i = 0;
            foreach ( $val as  $id  ) {
                $i++;
                $a[$i]       = ":id$i";
                $aParams[$a[$i]] = $id;
            }
            $where = " $this->idField in (".implode(',', $a).")";

        } else {
            if ( !is_numeric($val) ) {
                return false;
            }
            $where = " $this->idField=:value ";
            $aParams[':value'] = $val;
        }

        $q      = $this->db()->prepare("select * from $this->table where $where");

        $ret    = $q->execute( $aParams );
        if ( $ret === false ) {
            Liber::log()->add( get_class($this).'->'.__FUNCTION__.": [Table:$this->table => $this->idField :".print_r($val,true)."]  \n".print_r($q->errorinfo(),true), 'error' );
            return false;
        }

        $aRs = $q->fetchAll(PDO::FETCH_ASSOC);
        if ( count($aRs) > 0 ) {
            if ( !is_array($val) ) {
                return $this->loadFrom( $aRs[0], true );
            } else {
                return $aRs;
            }
        } else {
            return false;
        }
    }


    /**
     *  Clear fields data, by default use the default model data.
     * If $all specified with true, all field data will receive a empty value.
     *  @param boolean $all
     */
    function clear($all=false) {
        $c = get_class($this);
        $o = new $c;

        foreach( $this->aFields as $field => $value ) {
            $this->aFields[$field][0] = $all?'':$o->field($field);
        }

        $this->aChanges = Array();
    }


    /**
    *   Load current object with $data specified.
    *   If field not found in $data, it will be replace by ''. (clear method called do this)
    *   @param Array $data
    *   @param boolean $notUpdate
    *   @return boolean
    */
    function loadFrom( $data , $notUpdate=false) {
        if ( is_array($data) ) {
            $this->clear();
            foreach ( $this->aFields as $field => $value ) {
                $fullField = $this->table.'.'.$field;
                array_key_exists($field,$data)?$this->field($field, $data[$field], $notUpdate):(array_key_exists($fullField, $data)?$this->field($field, $data[$fullField], $notUpdate):'');
            }
            return true;
        } else {
            return false;
        }
    }


    function toArray($op='field') {
        $a = Array();
        if ( $op == 'field' ) {
            foreach ( $this->aFields as $field => $value ) {
                $a[$field] = $this->field($field);
            }
        } elseif($op == 'desc') {
            foreach ( $this->aFields as $field => $value ) {
                $a[$field] = $this->desc($field);
            }
        }
        return $a;
    }


    /**
    *   Search a specified $field by $value, returning entire records matched.
    *   @param $field String
    *   @param $value mixed
    *   @return Array
    */
    function searchBy($field, $value) {
        $sql = "select * from ".$this->table." where $field=:value ";
        $q   = $this->db()->prepare($sql);
        $ret = $q->execute( Array(':value' => $value)  );
        if ( !$ret  ) {
            Liber::log()->add( get_class($this).'->'.__FUNCTION__.": [Field:$field => Value: $value] \n".print_r($q->errorinfo(),true), 'error' );
            return Array();
        }
        return  $q->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
    *   Search $terms from current table model.
    *   $aOptions possibles: 'from', 'limit', 'start', 'fields', 'order'
    *   'fields' Array of fields which will search with;
    *   'from' if the search must be done on another table/view;
    *   'where' if you want add more filter;
    *   'limit' limits number of rows returned;
    *   'start' set which row number the search must begin;
    *   'order' list of fields separated by comma, can be specified the orientation 'asc' or 'desc';
    *   @param String $terms
    *   @param Array $aOptions
    *   @return Array
    */
    function search($terms, $aOptions=Array() ) {
        $ret     = Array();
        $aParams = Array();
        $from   = !isset($aOptions['from'])?$this->table:$aOptions['from'];
        $limit  = !isset($aOptions['limit'])?'':'limit '.$aOptions['limit'];
        $start  = !isset($aOptions['start'])?'':' offset '.$aOptions['start'];
        $fields = !isset($aOptions['fields'])?array_keys($this->aFields):$aOptions['fields'];
        $order  = !isset($aOptions['order'])?'':'order by '.$aOptions['order'];
        $whereOption = !isset($aOptions['where'])?'':$aOptions['where'];

        $whereFields = "concat_ws(' ', lower(".implode('),lower(', $fields).'))';

        $aTerms = explode(' ', $terms);

        $aTerms = array_filter($aTerms);
        $where  = '';

        foreach ( $aTerms as $i => $v) {
            $where .= '%'.$v;
        }
        $where .= '%';
        $aParams[':1'] = $where;
        $where = $whereFields.' like lower(:1)';

        $sql = "select * from $from where ($where) $whereOption  $order $limit $start ";
        $q   = $this->db()->prepare($sql);
        $ret = $q->execute( $aParams );
        if ( !$ret ) {
            Liber::log()->add( get_class($this).'->'.__FUNCTION__."[SQL: $sql] \n".print_r($q->errorinfo(),true), 'error' );
            return $ret;
        }

        return  $q->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     *  Add a function for validation.
     *  @param function $func
     */
    function addValidation($name, $func) {
        $this->fValidations[$name] = $func;
    }

    /**
    *   Do validation checking on changed fields.
    *   @return boolean
    */
    function validateData() {
        $this->errorsFields = Array();
        // fields
        foreach ($this->aChanges as $fName) {
            if ( count(($aErrors = Validation::validate($this->field($fName), $this->aFields[$fName][2]))) > 0 ) {
                $this->errorsFields[$fName] = $aErrors;
            }
        }

        // functions
        foreach ( $this->fValidations as $name => $func ) {
            if ( count(($out = call_user_func($func, $this))) > 0 ) {
                $this->errorsValidation[$name] = $out;
            }
        }

        return (count($this->errorsFields) == 0 and count($this->errorsValidation) == 0);
    }


    /**
     *  Append error message.
     *  @param String $msg
     */
    function error($msg) {
        $this->lastErrors[] = $msg;
    }


    /**
    *   Return last error, if exists.
    *   @return Array
    */
    function lastErrors() {
        return $this->errorsFields + $this->errorsValidation;
    }


    /*
    *   Return Array of validation errors found into friendly way, where key is a field name and value the message.
    *   @return Array
    */
    function buildFriendlyErrorMsg() {
        $aOut = Array();
        foreach ( $this->errorsFields as $field => $value ) {
            $aOut[$field] = $this->desc($field).': '.implode("\n", $value);
        }

        foreach ( $this->errorsValidation as $name => $value ) {
            $aOut[$name] = implode("\n", $value);
        }

        return $aOut;
    }


    /**
    *   Return Array of associated fields from PDO result set PDOStatement.
    *   The key of array is the first column value.
    *   @param $rs PDOStatement
    *   @return Array
    */
    protected function returnKeys($rs) {
        $out = Array();
        if ( !is_object($rs) ) { return $out; }

        if ( ($row = $rs->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) ) {

            if ( count($row) == 2 ) {
                do  {
                    $out[current($row)] = next($row);
                } while ($row = $rs->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT));
            } else {
                do  {
                    $out[current($row)] = $row;
                } while ($row = $rs->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT));
            }
        }

        return $out;
    }
}

?>