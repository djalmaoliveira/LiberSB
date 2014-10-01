<?php
/**
*   Class that provide some informations from HTTP request and do some action to HTTP response.
*   .
*  @package classes
*/
class Http {

    /**
     * Response a status code header specified.
     * @param  integer $number
     * @return void
     */
    public static function statusCode($number) {
        header($number, true, $number);
    }


    /**
     * Response a content type specified.
     * Usage:
     * <code>
     * // by shortcuts:
     *     Http::contentType( 'xml' );
     * // by specified content-type:
     *     Http::contentType( 'application/vnd.ms-excel' );
     * // charset:
     *     Http::contentType( 'json', 'utf-16' );
     * </code>
     * @param  string $type    Shortcuts avaliable: pdf, gzip, zip, json, xml, gif, jpeg, png, css, plain, html.
     * @param  string $charset Default: utf-8
     * @return void
     */
    public static function contentType($type, $charset='utf-8') {

        switch( $type ) {
            case 'pdf':
            case 'gzip':
            case 'zip':
            case 'json':
            case 'xml':
                $content_type = "application/".$type;
            break;

            case 'gif':
            case 'jpeg':
            case 'png':
                $content_type = "image/".$type;
            break;

            case 'css':
            case 'plain':
            case 'html':
                $content_type = "text/".$type;
            break;

            default:
                $content_type = $type;
        }

        header("Content-Type: ".trim($content_type)."; charset=$charset", true);
    }

    /**
    *  Check if is a SSL connection.
    *  @return boolean determined if it is a SSL connection
    */
    public static function ssl() {
        if(!isset($_SERVER['HTTPS']))
            return FALSE;

        //Apache
        if($_SERVER['HTTPS'] === 1) {
            return TRUE;
        }
        //IIS
        elseif ($_SERVER['HTTPS'] === 'on') {
            return TRUE;
        }
        //other servers
        elseif ($_SERVER['SERVER_PORT'] == 443){
            return TRUE;
        }
        return FALSE;
    }

    /**
    *   Check if the request is an AJAX request.
    *   @return boolean
    */
    public static function ajax(){
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }

    /**
     * Return the requested method name in lowercase.
     * @return string
     */
    public static function method() {
        static $method;
        if  (empty($method)) {
            $method = strtolower($_SERVER['REQUEST_METHOD']);
        }
        return $method;
    }

    /**
    *   Do a cleaning .
    *   @return  string [description]
    */
    protected static function clean($value) {
        if ( $value !== NULL ) {
            return $value;
        }
    }

    /**
    *   Return a query value sent by method GET from <b>$_GET</b> var.
    *   Usage:
    *   <code>
    *   // get a query value
    *       Http::get('name');
    *   // indicate if method request is GET
    *       Http::get();
    *   </code>
    *   @param String $field
    *   @return mixed|boolean
    */
    public static function get($field=null) {
        if ( func_num_args() == 0 ) {
            return (self::method() == 'get');
        }

        return self::clean( isset($_GET[$field])?$_GET[$field]:null );
    }

    /**
    *   Return a post value sent by method POST from <b>$_POST</b> var.
    *   Usage:
    *   <code>
    *   // get a post value
    *       Http::post('name');
    *   // indicate if method request is POST
    *       Http::post();
    *   </code>
    *   @param String $field
    *   @return mixed|boolean
    */
    public static function post($field=null) {
        if ( func_num_args() == 0 ) {
            return (self::method() == 'post');
        }

        return self::clean( isset($_POST[$field])?$_POST[$field]:null );
    }

    /**
    *   Return a identified index from <b>$_COOKIE</b> var.
    *   Usage:
    *   <code>
    *   // get a cookie value
    *       Http::cookie('name');
    *   // indicate if there are cookie values
    *       Http::cookie();
    *   </code>
    *   @param String $field
    *   @return mixed|boolean
    */
    public static function cookie($field=null) {
        if ( func_num_args() == 0 ) {
            return ($_COOKIE?true:false);
        }

        return self::clean( isset($_COOKIE[$field])?$_COOKIE[$field]:null );
    }

    /**
    *   Return a specifield uploaded file from <b>$_FILES</b> var.
    *   Usage:
    *   <code>
    *   // get a file uploaded
    *       Http::file('name');
    *   // indicate if there are file uploaded
    *       Http::file();
    *   </code>
    *   @param String $field
    *   @return mixed|boolean
    */
    public static function file($field=null) {
        if ( func_num_args() == 0 ) {
            return ($_FILES?true:false);
        }

        return ( isset($_FILES[$field])?$_FILES[$field]:null );
    }


    /**
     * Initiate the download of a file.
     *
     * Usage:
     * <code>
     *     Http::download('/path/to/file');
     *     Http::download('/path/to/file.pdf', array('type' => 'pdf', 'name' => 'report.pdf'));
     *     Http::download(array('content' => 'this is the content file.', 'type' => 'plain', 'name' => 'report.txt'));
     * </code>
     * Options:
     *     'type' is a content type of file;
     *     'name' is a file name;
     * @param  <string|array> $data
     * @param  array  $options
     * @return void
     */
    public static function download( $data, $options=array() ) {
        $opt       = array('type' => '', 'name' => '');
        $file_path = '';

        if ( is_string($data) ) {
            $opt       = array_merge($opt, $options);
            $file_path = $data;
        } elseif( is_array($data) ) {
            $opt       = array_merge($opt, $data);
            $file_path = sys_get_temp_dir().'/'.uniqid('down');
            file_put_contents($file_path, $data['content']);
        }

        $opt['name'] = $opt['name']?$opt['name']:basename($file_path);
        if ( !$opt['type'] ) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $opt['type'] = $finfo->file($file_path);
        }

        if ( !is_file($file_path) ) { return; }

        self::contentType($opt['type']);
        header("Content-Disposition: attachment; filename=".$opt['name'].";");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: ".filesize($file_path));
        @readfile($file_path);
        exit;
    }

}

?>