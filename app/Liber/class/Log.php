<?php
/**
*   Class that manipulate produced logs in general.
*   .
*  @package classes
*/
class Log {

    static $aLogMsg = Array();
    static $aLogAll = Array();
    static $debug   = true;
    private static $handler = '';
    private static $instanced;
	private static $error = false;

    function __construct() {
        if ( self::$instanced ) {
            return ;
        } else {
            self::$instanced = true;
            register_shutdown_function ( Array($this, 'handlerLog') ) ;
        }
    }


    /**
    *   Do the last action with log messages stored.
    *   <pre>
    *   The default on error behavior is:   DEV     => open a popup window showing actual information about error and enviroment.
    *                                       PROD    => call to SYS_ERROR controller defined.
    *
    *   Both APP_MODE write a file on /log folder with the pattern [yyyymmdd.log].
    *   </pre>
    */
    public function handlerLog() {

        if ( !empty(self::$handler) and count(self::$aLogMsg) > 0) {
            call_user_func(self::$handler, self::$aLogMsg);
        } else {
            $this->toFile();
        }

        // do action on error
        if ( self::$error ) {
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
    *   The $func params is Log::$aLogMsg
    *   @param String $func - a function
    */
    function handler($func) {
        if ( !empty($func) ) {
            self::$handler = $func;
        }
    }


    /**
    *   Add a log message from current error triggered.
    */
    function handlerError() {
		self::$error = true;

		$aError  = error_get_last();

        $profile = $this->profile($aError, debug_backtrace(), 'exception');

        $this->add($profile, 'error');

    }


    /**
    *   Return a String of current error and enviroment information.
    *   @param Array $context - Error context
    *   @return String
    */
    private function profile($context) {

		$env = "\r\n_SERVER: ".print_r($_SERVER, true)."\r\n_POST: ".print_r($_POST, true)."\r\n_GET: ".print_r($_GET, true)."\r\n_SESSION: ".(isset($_SESSION)?print_r($_SESSION, true):'')."\r\n_COOKIE: ".print_r($_COOKIE, true)."\r\n_FILES: ".print_r($_FILES, true);

		$msg = "
			<table border='0' padding='5px'>
				<tr><td style='border:1px solid;'>TYPE:</td> <td style='border:1px solid;' width='30px'>{$context['type']}</td> <td style='border:1px solid;' width='10px'>LINE:</td> <td style='border:1px solid;'>{$context['line']}</td></tr>
				<tr><td style='border:1px solid;'>FILE: </td><td style='border:1px solid;' colspan='3'>".str_replace(dirname(Liber::conf('APP_PATH')),'',$context['file'])."</td></tr>
				<tr><td style='border:1px solid;'>MESSAGE: </td><td style='border:1px solid;' colspan='3'><pre>{$context['message']}</pre></td></tr>
				<tr><td style='border:1px solid;'>STATE: </td><td style='border:1px solid;' colspan='3'><pre>$env</pre></td></tr>
			</table>
		";


        return $msg;
    }


    /**
    *   Adds a message log.
    *   @param String $msg
    *   @param String $level - NameSpace to log like 'ERROR', 'URGENT', etc
    */
    function add($msg, $level='info') {
        if ( !self::$debug ) { return; }
        if ( !array_key_exists($level, self::$aLogMsg) ) { self::$aLogMsg[$level] = Array(); }

        $id = array_push(self::$aLogMsg[$level] , trim($msg)."\n");
        $id--;
        self::$aLogAll[] = &self::$aLogMsg[$level][$id];

    }


    /**
    *   Write current log to file.
    *   @return  void
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
		if ( !is_dir($path) ) {
			umask(0007);
			mkdir($path, 0770, true);
		}
        file_put_contents( $path.date('Ymd').'.log', '['. date(DATE_RFC822).'] '.trim(strip_tags(implode("\n", self::$aLogAll))), FILE_APPEND | LOCK_EX );
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