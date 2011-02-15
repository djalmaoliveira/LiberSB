<?php

/**
*   @package core.class
*/


/**
*   Class that manipulates extraction from databases.
*   Current suport: MySQL
*/
class SqlExtract {
    private $db_app_mode = '';
    private $_tb_status  = Array();
    private $db;
    
    function __construct($db_app_mode='PROD') {
        $this->db_app_mode = $db_app_mode;
        Liber::loadClass('BasicDb');
        $this->db = BasicDb::getInstance($this->db_app_mode);
        
        switch( Liber::$aDbConfig[$this->db_app_mode][4] ) {
            case 'mysql':
                $this->_tb_status = $this->db->query("show table status")->fetchAll(PDO::FETCH_GROUP);            
            break;
        }
    }


    /**
    *   Return the SQL code of table creation.
    *   Return Array of tables names with its SQL code.
    *   @param String | Array $tables
    *   @return Array
    */
    public function tableScheme($tables, $withoutKeys=true) {
        if ( !is_array($tables) ) {
            $tables = Array($tables);
        }
        $aList = Array();

        foreach( $tables as $tableName ) {
            if ( $this->isTable($tableName) ) {
                switch ( Liber::$aDbConfig[$this->db_app_mode][4] ) {
                    case 'mysql':
                        $sql = "SHOW CREATE TABLE ".$tableName;
                        $rs  = $this->db->query($sql)->fetch();
                        if ( $withoutKeys ) {
                            $lines = explode("\n", $rs[1]);
                            $keys  = (preg_grep('/[\s]*(CONSTRAINT|FOREIGN[\s]+KEY|PRIMARY[\s]+KEY)/', $lines));
                            $lines = array_diff_key($lines, $keys);
                            end($lines);
                            $line = prev($lines);
                            $lines[key($lines)] = ($line[strlen($line)-1]==','?substr($line, 0, strlen($line)-1):$line);
                            $rs[1] = implode("\n", $lines);
                        }
                        $aList[$tableName] = $rs[1];
                    break;
                }
            }
        }
        return $aList;
    }


    /**
    *   Return the SQL code of view creation specified.
    *   Return Array of view names with its SQL code.
    *   @param String | Array $views
    *   @return Array
    */
    public function viewScheme($views) {
        if ( !is_array($views) ) {
            $views = Array($views);
        }
        $aList = Array();

        foreach( $views as $viewName ) {
            if ( !$this->isTable($viewName) ) {
                switch ( Liber::$aDbConfig[$this->db_app_mode][4] ) {
                    case 'mysql':
                        $sql = "SHOW CREATE VIEW ".$viewName;
                        $rs  = $this->db->query($sql)->fetch();
                        $aList[$viewName] = $rs[1];
                    break;
                }
            }
        }
        return $aList;
    }


    
    /**
    *   Return the constraints(PRIMARY and FOREIGN KEYS) of $tables specified.
    *   @param String | Array $tables
    *   @return Array
    */
    public function tableConstraints($tables) {
        if ( !is_array($tables) ) {
            $tables = Array($tables);
        }
        $aList = Array();

        foreach( $tables as $tableName ) {
            $create = current($this->tableScheme($tableName, false));
            switch ( Liber::$aDbConfig[$this->db_app_mode][4] ) {
                case 'mysql':
                    $aKeys = preg_grep('/[\s]*(CONSTRAINT|FOREIGN[\s]+KEY|PRIMARY[\s]+KEY)/', explode("\n", $create));
                    foreach($aKeys as $key) {
                        $aList[$tableName][] = "ALTER TABLE $tableName ADD ".($key[(strlen($key)-1)]==','?substr($key, 0, strlen($key)-1):$key).' ;';
                    }
                break;
            }
        }
        return $aList;
    }


    /**
    *   Return an array of SQL Insert code of each data retrieved from $tables specified, by table name.
    *   If specified $destFolder, each table data will be written on a file like 'tableName.sql' on this folder and return the path to this file instead of data.
    *   Remember that you have to remove the files written after its use.
    *   Usage:  ->tableData('customer'); // return Array('customer'=>'INSERT ...');
    *           ->tableData('customer', '/home/user/sql/'); // written on '/home/user/sql/customer.sql'
    *           ->tableData('customer', 'temp/'); // written on 'APP_PATH/temp/customer.sql'  
    *   @param String | Array $tables
    *   @param String $destFolder
    *   @return Array
    */
    public function tableData($tables, $destFolder='') {
        if ( !is_array($tables) ) {
            $tables = Array($tables);
        }
        $destFolder = trim(str_replace('/', DIRECTORY_SEPARATOR, $destFolder));
        if ( $destFolder[strlen($destFolder)-1] != DIRECTORY_SEPARATOR ) { $destFolder .= DIRECTORY_SEPARATOR; }
        if ( !is_dir($destFolder) ) { mkdir ( $destFolder, 0760, true); }
        
        $aList = Array();
        $funcValue = create_function('$value','
            if ( is_null($value) ) {
                return "NULL";
            }
            $value = filter_var($value, FILTER_SANITIZE_MAGIC_QUOTES);
            return "\'$value\'";
        ');
        
        foreach( $tables as $tableName ) {
            $aList[$tableName] = '';
            $q = $this->db->query("SELECT * FROM $tableName");
            if ( empty($destFolder) ) {
                $rs = $q->fetchAll(PDO::FETCH_ASSOC);
                if ( $rs ) {
                    $fields = array_keys($rs[0]);
                    foreach( $rs as $row) {
                        $values = array_map($funcValue, array_values($row));
                        $aList[$tableName] .= "INSERT INTO $tableName (".implode(', ', $fields).") VALUES (".implode(', ', $values).") ;\r\n";
                    }
                }
            } else {
                $buffer = Array();
                $aList[$tableName] = ($destFolder.$tableName.'.sql');
                file_put_contents($aList[$tableName], '');
                $row    = $q->fetch(PDO::FETCH_ASSOC);
                $fields = array_keys($row);
                do {
                    $values   = array_map($funcValue, array_values($row));
                    $buffer[] = "INSERT INTO $tableName (".implode(', ', $fields).") VALUES (".implode(', ', $values).") ;";
                    if ( count($buffer) > 20 ) {
                        file_put_contents($aList[$tableName], implode("\r\n", $buffer) ,FILE_APPEND);
                        $buffer = Array();
                    }
                } while ( $row = $q->fetch(PDO::FETCH_ASSOC) );
                file_put_contents($aList[$tableName], implode("\r\n", $buffer) ,FILE_APPEND);
            }
        }        
        
        return $aList;
    }
        
    
    /**
    *   Return if $table is table or not.
    *   The entity $table can be a view.
    *   @param String
    *   @return boolean
    */
    protected function isTable($table) {
        switch ( Liber::$aDbConfig[$this->db_app_mode][4] ) {
            case 'mysql':    
                return empty($this->_tb_status[$table][0]['Engine'])?false:true;
            break;
        }
        return false;
    }
    
        
    
}
?>
