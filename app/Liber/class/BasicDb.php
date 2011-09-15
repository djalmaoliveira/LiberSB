<?php
/**
*   @package core.class
*/

/**
*   Basic class that manipulates action with a some database
*   Should be used as a model for future Database Layers
*/
class BasicDb {

    /**
    *   Return the instance
    *   @return object PDO
    */
    static function getInstance($app_mode=null) {
        if ($app_mode==null) {
            $app_mode = Liber::conf('APP_MODE');
        }
        if ( isset(Liber::$aDbConfig[$app_mode]) ) {
            $config = Liber::$aDbConfig[$app_mode];
            switch( $config[4] ) {
                case 'mysql':
                    $host   = ($config[0][0]=='/')?"unix_socket=".$config[0]:"host={$config[0]}";
                    $dsn    = $config[4].":$host;dbname={$config[1]}";
                    $options = array(
                        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
                    );
                break;
                default:
            }
            try {
                $o  = new PDO($dsn, $config[2], $config[3], $options);
                return $o;
            } catch(PDOException $e) {
                trigger_error("No database connection."); // Caution: Exception message show password on stack trace.
                return null;
            }
        } else {
            Liber::log()->add('Configure database settings.');
            return null;
        }
    }

}

?>