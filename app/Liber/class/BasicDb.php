<?php
/**
*   Basic class that manipulates action with a some database.
*   Should be used as a model for future Database Layers.
*   @package classes
*/
class BasicDb {

    /**
    *   Return the instance
    *   @return object PDO
    */
    static function getInstance($connection_name='default') {

        if ( isset(Liber::$aDbConfig[$connection_name]) ) {
            $config = Liber::$aDbConfig[$connection_name];
            switch( $config[4] ) {
                case 'mysql':
                    $host   = ($config[0][0]=='/')?"unix_socket=".$config[0]:"host={$config[0]}";
                    $dsn    = $config[4].":$host;dbname={$config[1]}";
                    $options = array(
                        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
                    );
                break;
                case 'firebird':
                    $dsn = $config[4].":dbname={$config[1]};host={$config[0]}";
                    $options = array();
                break;
                case 'sqlite':
                    $dsn = $config[4].":{$config[1]}";
                    $options = array();
                break;

                default:
            }
            try {
                $o  = new PDO($dsn, $config[2], $config[3], $options);
                return $o;
            } catch(PDOException $e) {
                trigger_error("No database connection. [".$e->getMessage().']'); // Caution: Exception message show password on stack trace.
                return null;
            }
        } else {
            Liber::log()->add('Configure database settings.');
            return null;
        }
    }

}

?>