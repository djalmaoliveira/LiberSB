<?php
/**
 *  Simple Table Model class for Liber framework.
 *  This class implements a similar Active Record pattern to store, retrieve, manipulate, validate data from a table in database.
 *  The pre-requisite is support to PDO class in PHP installations.
 *  Supported databases: Sqlite, Mysql and Firebird.
 *  Default primary key field name is 'id'.
 *  Default table name is the class name in lower case.
 *  @package classes
 *  @author Djalma Oliveira <djalmaoliveira@gmail.com>
  */
abstract class TableModel {

    /**
     * Table name.
     * @var string
     */
    protected $table;

    /**
     * Primary Key Field key name.
     * @var string
     */
    protected $idField = 'id';

    /**
     * Name of sequence/generator registered on database used to get next ID.
     * @var string
     */
    protected $sequenceName;


    /**
     * Array of stored errors.
     * @var array
     */
    protected $errors = Array();

    /**
     * Array of table fields.
     * @var array
     */
    protected $aFields  = Array();

    /**
     * Array of changed fields value.
     * @var array
     */
    protected $aChanges = Array();

    /**
     * PDO instance
     * @var PDO
     */
    protected $PDO;

    /**
     *  Array of functions for validations
     *  @var Array
     */
    protected $fValidations = Array();

    /**
     * Transaction count, how many times beginTransaction() was called.
     * @var integer
     */
    static protected $transaction_count = 0;

    /**
     * Rollback count, how many times rollback() was called.
     * @var integer
     */
    static protected $rollback_count = 0;


    /**
     * Constructor.
     * @param PDO $PDO
     */
    function TableModel( PDO $PDO = null ) {
        if ( get_class($PDO) != 'PDO' ) { trigger_error("No PDO connection."); }
        $PDO->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
        $PDO->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->meta = Array('concat' => function(){});
        switch ( $PDO->getAttribute(PDO::ATTR_DRIVER_NAME ) ) {

            case 'mysql':
                $this->meta['concat'] = function($fields) {
                    return "concat_ws(' ', lower(".implode('),lower(', $fields).'))';
                };
            break;

            case 'sqlite':
                $this->meta['concat'] = function($fields) {
                    return "(' ' || lower(".implode(') || \' \' || lower(', $fields).'))';
                };
            break;

            case 'firebird':
                $this->meta['concat'] = function($fields) {
                    return "' ' || lower(COALESCE(".implode(', \'\')) || lower(COALESCE(', $fields).', \'\' ))';
                };
            break;
        }
        $this->PDO = $PDO;
        Liber::loadClass('Validation');
        $this->table = strtolower( get_class($this) );
    }


    /**
     * Start a new transaction.
     * If not exist a current transaction it will start one.
     * For each beginTransaction() called must have the same number of commit() or rollback() calls.
     * @return void
     */
    function beginTransaction() {
        $ret = true;
        if ( self::$transaction_count == 0 )  {
            self::$rollback_count    = 0;
            self::$transaction_count = 1;
            if ( !$this->db()->inTransaction() ) {
                $ret = $this->db()->beginTransaction();
            }
        } elseif (  self::$transaction_count  > 0) {
            self::$transaction_count++;
        }

        return $ret;
    }

    /**
     * Commit current transaction.
     *
     * @return boolean
     */
    function commit() {
        $ret = true;

        if (self::$rollback_count > 0 and self::$transaction_count == 1) {
            self::$rollback_count    = 0;
            self::$transaction_count = 0;
            $this->db()->rollback();
            $ret = false;
        }
        if ( self::$transaction_count == 1 )  {
            self::$transaction_count = 0;
            self::$rollback_count    = 0;
            if ( $this->db()->inTransaction() ) {
                $ret = $this->db()->commit();
            }
        } elseif ( self::$transaction_count > 0 ) {
            self::$transaction_count--;
        }

        return $ret;
    }

    /**
     * Rollback current transaction.
     * @return boolean
     */
    function rollback() {
        $ret = true;
        self::$rollback_count++;
        if ( self::$transaction_count == 1 )  {
            self::$transaction_count = 0;
            self::$rollback_count    = 0;
            if ( $this->db()->inTransaction() ) {
                $ret =  $this->db()->rollback();
            }

        } elseif ( self::$transaction_count > 0 ) {
            self::$transaction_count--;
        }

        return $ret;
    }


    /**
     * Return a PDO database connection.
     * @return PDO
     */
    function db() {
        return $this->PDO;
    }


    /**
     *  Add a function for validation.
     *  @param string $name
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
        if ( isset($this->errors['fields']) ) {
            unset($this->errors['fields']);
        }
        // fields
        foreach ($this->aChanges as $fName) {
            if ( count(($aErrors = Validation::validate($this->field($fName), $this->aFields[$fName][2]))) > 0 ) {
                $this->errors['fields'][$fName] = $aErrors;
            }
        }

        if ( isset($this->errors['validation']) ) {
            unset($this->errors['validation']);
        }
        // functions
        foreach ( $this->fValidations as $name => $func ) {
            if ( count(($out = call_user_func($func, $this))) > 0 ) {
                $this->errors['validation'][$name] = $out;
            }
        }

        return ( !isset($this->errors['fields']) and !isset($this->errors['validation']) );
    }

    /**
     * Turn validation field 'on' or 'off'.
     * @param  String $field  Field name
     * @param  String $switch on | off
     * @return void
     */
    function turnValidation( $field, $switch ) {
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

    /**
     * Method called before save data.
     * @return void
     */
    function beforeSave() {
        return true;
    }

    /**
     * Method called after data saved.
     * @return void
     */
    function afterSave() {
        return true;
    }

    /**
    *   Return true if field was changed.
    *   @param string $field
    *   @return boolean
    */
    function changed($field) {
        return in_array($field, $this->aChanges);
    }

    /**
    *   Insert a new record, returning true if get to do it.
    *   To setup a field with null, fill it with empty ''.
    *   @param  array $data
    *   @return boolean
    */
    function insert($data=Array()) {
        if ( count($data) >0 ) { $this->loadFrom($data); }
        $this->beforeSave();
        $this->fieldFilter();
        if ( !$this->validateData() ) { return false; }

        $aFields = $this->toArray();

        // autoincrement value
        if ( empty($aFields[$this->idField]) ) {
            switch ( $this->db()->getAttribute(PDO::ATTR_DRIVER_NAME ) ) {
                case 'firebird':
                    $q  = $this->db()->query("select GEN_ID(".$this->sequenceName.", 1) from rdb\$database");
                    $rs = $q->fetch();
                    $aFields[$this->idField] = current($rs);
                break;

                default:
                    $aFields[$this->idField] = null;
            }
        }

        $fields = implode(', ', array_keys($aFields));
        $params = Array();

        $ret    = $this->prepareParams( $aFields );
        $params = $ret['params'];

        $values = implode(',', array_keys($params));
        $sql    = 'insert into '.$this->table.' ('.$fields.') values ('.$values.')';

        if ( ($q = $this->executePreparedSql($sql, $params)) ) {
            if ( $aFields[$this->idField] ) {
                $this->field($this->idField, $aFields[$this->idField]);
            } else {
                $this->field($this->idField, $this->db()->lastInsertId() );
            }

            $this->afterSave();;
            $this->aChanges = Array();
            return true;
        }
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

        $params = Array();
        $aSets   = Array();
        foreach( $this->aChanges as $k  ) {
            if ( is_null($aFields[$k]) ) {
                $aSets[] = "$k=NULL";
            } else {
                $param = ":$k";
                $aSets[] = "$k=$param";
                $params[$param] = trim($aFields[$k]);
            }
        }

        $values = implode(', ', $aSets );
        $sql    = 'update '.$this->table.' set '.$values.' where '.$this->idField.'='.$this->field($this->idField);

        if ( ($q = $this->executePreparedSql($sql, $params)) ) {
            $this->afterSave();;
            $this->aChanges = Array();
            return true;

        }
        return false;
    }


    /**
    *   Delete a existing record, returning true if get to do it.
    *   @param  integer $id
    *   @return boolean
    */
    function delete($id=null) {
        if ( !is_numeric($id) ) {
            $id = $this->field($this->idField);
        }
        $sql = "delete from $this->table where ".$this->idField.'=:'.$this->idField;
        $params= Array(':'.$this->idField => $id);
        if ( ($q = $this->executePreparedSql($sql, $params)) ) {
            $this->aChanges = Array();
            return true;
        }

        return false;
    }


    /**
    *   Save current data.
    *   @param Array $data Optional
    *   @return boolean
    */
    function save( $data=Array() ) {
        if ( $data and isset($data[$this->idField]) ) {
            $id = $data[$this->idField];
        } else {
            $id = $this->field( $this->idField );
        }

        if ( empty( $id ) ) {
            $retS = $this->insert( $data );
        } else {
            $retS = $this->update( $data );
        }

        return $retS;
    }




    /**
    *   Set or Get a value from field specified.
    *   @param String $f - Field Name
    *   @param String $v - Field Value, optional
    *   @param boolean $notUpdate - Indicates if field must be set up as changed.
    *   @return mixed
    */
    function field($f, $v=NULL, $notUpdate=false) {
        if ( func_num_args() == 1 ) {
            return $this->aFields[$f][0];
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
     *
     *  @param integer $id
     *  @param mixed $aFields    $aFields can be a field name or Array of field names.
     *  @return Array
     */
    function getField($id, $aFields=Array()) {
        if ( !is_array($aFields) ) { $aFields = Array( $aFields ); }
        $fields = implode(',', $aFields);
        $sql    = "select $fields from $this->table where  $this->idField=:id";
        $params = Array(':id' => $id);

        if ( ($q = $this->executePreparedSql($sql, $params)) ) {
            $aRs = $q->fetchAll();
            return $aRs[0];
        }

       return Array();
    }


    /**
     *  Return or load a model from table.
     *  @param  integer | array $val
     *  @return boolean | array
     */
    function get($val) {
        $params = Array();
        $a = Array();

        if ( is_array($val) ) {
            if ( count($val) == 0 ) {
                return Array();
            }
            $i = 0;
            foreach ( $val as  $id  ) {
                $i++;
                $a[$i]       = ":id$i";
                $params[$a[$i]] = $id;
            }
            $where = " $this->idField in (".implode(',', $a).")";

        } else {
            if ( !is_numeric($val) ) {
                return false;
            }
            $where = " $this->idField=:value ";
            $params[':value'] = $val;
        }

        $sql = "select ".implode(',',array_keys($this->aFields))." from $this->table where $where";
        if ( ($q = $this->executePreparedSql($sql, $params)) ) {
            $aRs = $q->fetchAll();
            if ( count($aRs) > 0 ) {
                if ( !is_array($val) ) {
                    return $this->loadFrom( $aRs[0], true );
                } else {
                    return $aRs;
                }
            }
        }

        return false;
    }


    /**
     *  Clear fields data, by default use the default model data values.
     *  If $all specified with true, all field data will receive a empty value.
     *  @param boolean $all
     */
    function clear($all=false) {
        $c = get_class($this);
        $o = new $c($this->PDO);

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

    /**
    *   Return an Array of current fields model values or descriptions.
    *   @param String $op - 'field' (default) - return field values; 'desc' - return description fields
    *   @return Array
    */
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
    *   Search a specified $fields, returning Array  of entire records matched.
    *   <code>
    *   Usage:
    *   searchBy( 'field', 'value' ); // search only one field
    *   searchBy( Array('field' => 'value', ...) ); // search many fields with AND operator
    *   searchBy( Array( 'or' => Array('field' => 'value', ...) ) ); // search many fields with OR operator
    *   searchBy( 'field1', 'value', array('fields' => array('field2')) ); // search by field1 and return field2 as result
    *   </code>
    *   @param string|array $fields
    *   @param string $value
    *   @param  array $options      fields=array('field1', field2) to return only specified fields
    *   @return PDOStatement|false
    */
    function searchBy( $fields, $value=null, $options=array() ) {
        if ( is_array($fields) ) {
            $oper = 'and';
            if  ( in_array( strtolower(key($fields)), Array('or', 'xor')) ) {
                $oper   = key($fields);
                $ret    = $this->prepareParams( $fields[$oper] );
                $params = $ret['params'];
            } else {
                $ret    = $this->prepareParams( $fields );
                $params = $ret['params'];
            }
            $where = implode(" $oper ", $ret['parts']);

        } else {
            $where = "$fields=:value";
            $params = Array(':value' => $value);
        }

        $fields = '*';
        if ( isset($options['fields']) ) {
            $fields = implode(', ', $options['fields']);
        }

        $sql = "select $fields from ".$this->table." where  $where";
        if ( ($q = $this->executePreparedSql($sql, $params)) ) {
            return  $q;
        }
        return false;
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
    *   @return PDOStatement|false
    */
    function search($terms, $aOptions=Array() ) {
        $ret    = Array();
        $params = Array();
        $from   = !isset($aOptions['from'])?$this->table:$aOptions['from'];
        $limit  = !isset($aOptions['limit'])?'':'limit '.$aOptions['limit'];
        $start  = !isset($aOptions['start'])?'':' offset '.$aOptions['start'];
        $fields = !isset($aOptions['fields'])?array_keys($this->aFields):$aOptions['fields'];
        $order  = !isset($aOptions['order'])?'':'order by '.$aOptions['order'];
        $whereOption = !isset($aOptions['where'])?'':$aOptions['where'];

        $whereFields = $this->meta['concat']( $fields );

        $aTerms = explode(' ', $terms);

        $aTerms = array_filter($aTerms);
        $where  = '';

        foreach ( $aTerms as $i => $v) {
            $where .= '%'.$v;
        }
        $where .= '%';
        $params[':1'] = $where;
        $where = $whereFields.' like lower(:1)';

        $sql = "select * from $from where ($where) $whereOption  $order $limit $start ";

        $q   = $this->db()->prepare($sql);

        if ( ($q = $this->executePreparedSql($sql, $params)) ) {
            return  $q;
        }

        return false;
    }

    /**
     * Used to add or return errors on current object.
     * The namespaces 'validation' and 'fields' are reserved words created by internal validation when save data.
     * <code>
     * // Add a new error message using a $namespace.
     * errors('info', 'one error occur.');
     * // Get an errors array of $namespace specified.
     * errors('info');
     * // Get an array of all errors added.
     * errors();
     * </code>
     * @param  String $namespace
     * @param  String $message
     * @return Array
     */
    function errors($namespace=null, $message=null) {
        switch ( func_num_args() ) {
            case 0:
                return $this->errors;
            break;

            case 1:
                return ( array_key_exists($namespace, $this->errors)?$this->errors[$namespace]:'' );
            break;

            case 2:
                $this->errors[$namespace] = $message;
            break;
        }
    }



    /**
    *   Default strip_tags filter for fields.
    */
    protected function fieldFilter() {
        $arr = current($this->aFields);
        do  {
            if ( is_null($arr[0]) ) { continue; }
            $this->aFields[key($this->aFields)][0] = strip_tags($arr[0]);
        } while ( ($arr=next($this->aFields)) );
    }


    /**
     * Return Array() with prepared params and correspondent SQL 'where' part.
     * @param  Array $data Array with key/value data to build prepared statement.
     * @return Array Array('params' => Array(), 'parts' => Array())
     */
    protected function prepareParams( $data ) {

        $params = Array();
        $parts  = Array();
        foreach ($data as $field => $value) {
            $param = ":".$field;
            $params[$param] = $value;
            $parts[] = "$field=$param";
        }
        return Array('params' => $params, 'parts' => $parts);
    }


    /**
     * Execute a SQL and return a PDOStatement object if success, else return false.
     * @param  String   $sql
     * @param  Array    $params     prepared Params to $sql
     * @return boolean|PDOStatement
     */
    protected function executePreparedSql( $sql, $params ) {
        if ( ($q = $this->db()->prepare($sql)) ) {
            if ( ($ret = $q->execute( $params )) !== false ) {
                return $q;
            }
        }
        Liber::log()->add( get_class($this).": ".print_r($sql,true)."\n".(is_object($q)?print_r($q->errorinfo(),true):print_r($this->db()->errorinfo(),true)).print_r($params,true), 'error' );
        return false;
    }


    /**
    *   Return Array of associated fields from PDO result set PDOStatement.
    *   The key of array is the first column value.
    *   @param  PDOStatement $rs
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