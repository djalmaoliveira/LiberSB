<?php

/**
*   @package core.class
*/


/**
*   Class that manipulate produced logs in general.
*/
class Log {

    static $aLogMsg = Array();
    static $aLogAll = Array();
    static $debug   = true;
    private static $handler = '';
    private static $instanced;

    function __construct() {
        if ( self::$instanced ) {
            return ;
        } else {
            self::$instanced = true;
            register_shutdown_function ( Array($this, 'post') ) ;
        }
    }


    /**
    *   Do the last action with log messages stored.
    *   The default on error behavior is:   DEV     => open a popup window showing actual information about error and enviroment.
    *                                       PROD    => call to SYS_ERROR controller defined.
    *   Both APP_MODE write a file on /log folder with the pattern [yyyymmdd.log].
    */
    public function post() {

        if ( !empty(self::$handler) and count(self::$aLogMsg) > 0) {
            call_user_func(self::$handler, self::$aLogMsg);
        }

        $this->toFile();
        // action on error
        if ( error_get_last() !== null) {
            if ( Liber::conf('APP_MODE') == 'DEV' ) {
                echo $this->toPopUp(); die();
            } else {
                Liber::loadController(Liber::conf('SYS_ERROR'), true)->index();
            }
        }
    }


    /**
    *   Set a handler function to control the behavior of log messages.
    *   The behavior of this function will be called before the default behavior, if you don't want the default behavior, then call die() or exit on $func.
    *   @param String $func - a function
    */
    function handler($func) {
        if ( !empty($func) ) {
            self::$handler = $func;
        }
    }


    /**
    *   Add a message of current error triggered.
    *   At the end of method, the execution of script will be stopped.
    */
    function handleError() {
        $args       = func_get_args();
        $errorData  = current($args);
        $aError     = Array() ;
        $o          = $errorData[0];

        if ( gettype($o) == 'object' ) {
            $aError['no']   = $o->getCode();
            $aError['msg']  = $o->getMessage();
            $aError['file'] = $o->getFile();
            $aError['line'] = $o->getLine();
            $trace          = $o->getTrace();
        } else {
            $aError['no']   = $errorData[0];
            $aError['msg']  = $errorData[1];
            $aError['file'] = $errorData[2];
            $aError['line'] = $errorData[3];
            $trace          = debug_backtrace();
        }

        $profile = $this->profile($aError, $trace, (is_object($o)?'exception':'error'));

        $this->add("Error: ".$aError['msg'].". \r\n".$profile, 'error');

        trigger_error('Error detected: '.$aError['msg'], E_USER_ERROR);
    }


    /**
    *   Return a String of current error and enviroment information.
    *   @param Array $context - Error context
    *   @param Array $arr - backtrace
    *   @param String $type - 'error' or 'exception'
    *   @return String
    */
    private function profile($context, $arr, $type) {

        $offset = ($type=='exception')?0:2;
        $traces = '';
        for ($i=$offset; $i < count($arr); $i++ ) {
            $t          = $arr[$i];
            $t['file']  = isset($t['file'])?$t['file']:'';
            $class      = (isset($t['class'])?$t['class'].$t['type']:'');
            $function   = (isset($t['function'])?$t['function']:'');
            $args       = (isset($t['args'])?str_replace("\n","",print_r($t['args'], true)):'');

            $traces    .= ' '.$class.$function."( ".$args." ) \r\n    called at [".$t['file']."] line ".(isset($t['line'])?$t['line']:'')."\r\n";
        }
        $traces = "Where: [".$context['file']."] line ".$context['line']." \r\n \r\n".$traces;

        $env = "\r\n_SERVER: ".print_r($_SERVER, true)."\r\n_POST: ".print_r($_POST, true)."\r\n_GET: ".print_r($_GET, true)."\r\n_SESSION: ".print_r($_SESSION, true)."\r\n_COOKIE: ".print_r($_COOKIE, true)."\r\n_FILES: ".print_r($_FILES, true);

        return $traces.$env;
    }


    /**
    *   Adds a message log.
    *   @param String $msg
    *   @param String $level - NameSpace to log like 'ERROR', 'URGENT', etc
    */
    function add($msg, $level='info') {
        if ( !self::$debug ) { return; }
        if ( !array_key_exists($level, self::$aLogMsg) ) { self::$aLogMsg[$level] = Array(); }

        $id = array_push(self::$aLogMsg[$level] , '['. date(DATE_RFC822).'] '.$msg."\n");
        $id--;
        self::$aLogAll[] = &self::$aLogMsg[$level][$id];

    }


    /**
    *   Write current log to file.
    *
    */
    function toFile() {
        if ( !self::$debug or count(self::$aLogMsg)==0 ) { return; }
        // absolute path
        if ( substr(Liber::conf('LOG_PATH'), 0,1) == '/' ) {
            $path = Liber::conf('LOG_PATH');
        // relative path from APP_PATH
        } else {
            $path = Liber::conf('APP_PATH').Liber::conf('LOG_PATH');
        }
		if ( !file_exists($path) ) {
			umask(0007);
			mkdir($path, 0770, true);
		}
        file_put_contents( $path.date('Ymd').'.log', implode("\n", self::$aLogAll), FILE_APPEND | LOCK_EX );
    }


    /**
    *   Return textual stored log.
    *   @return String
    */
    function toString() {
        return implode("\n", self::$aLogAll);
    }


    /**
    *   Generate a html content to show a popup with PHP error.
    *   @return String
    */
    function toPopUp() {
        $html  = "<html><body>";
        $html .="<pre>".implode("\r\n", self::$aLogAll)."</pre>";
        $html .="</body></html>";

        $output = str_replace("'",'"', $html);
        $html   = "
        <script type='text/javascript'>
            var wError = window.open('about:blank', 'profile_window', 'top=10, left=200, width=700, height=200, resizable=yes, scrollbars=yes');
            var output = ".json_encode(Array($output)).";
            wError.document.write(output[0]);
        </script>";
        return $html;
    }
}

?>