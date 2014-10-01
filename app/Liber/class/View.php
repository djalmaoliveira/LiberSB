<?php
/**
*   Class that manipulates html output and html template engine loads.
*   .
*   @package classes
*/
class View {
    private     $template_name;
    private     $template_file;
    private     $module;
    private     $module_path;
    private     $_engine;
    private     $cache_expires  = Array();
    private     $expireTime     = 3600;
    public      $layout         = '';
    private     $_layout_once   = '';


    function __construct($module='', $template_name='') {

        $this->layout = Liber::conf('LAYOUT');
        $this->module = $module;
        $this->template_name = $template_name;
        if ( !empty( $this->module ) ) {
            $this->module_path = Liber::conf('APP_PATH').'module/'.$this->module.'/';
        } else {
            $this->module_path = Liber::conf('APP_PATH');
        }
    }


    /**
    *   Return current setting from instance.
    *   @return mixed
    */
    function current($param) {
        switch ($param) {
            case 'module':
                return $this->module;
            break;
        }
    }


    /**
    *   Set the cache expires.
    *   <code>
    *   Usage:
    *   // return cache expire time in seconds
    *   ->cache();
    *
    *   // enable caching to default value 3600s
    *   ->cache(true);
    *
    *   // disable caching
    *   ->cache(false);
    *
    *   // return current Array data about the specified file
    *   ->cache('filename.html');
    *
    *   // set caching to default value for specified file
    *   ->cache('filename.html', true);
    *
    *   // disable caching for specified file
    *   ->cache('filename.html', false);
    *
    *   // set caching with 2000s to specific file
    *   ->cache('filename.html', 2000);
    *   </code>
    *   @param mixed $arg1  - see above
    *   @param mixed $arg2  - see above
    *   @return mixed
    */
    function cache() {

        if ( func_num_args() == 0 ) {
            return $this->expireTime;
        } elseif ( func_num_args() == 1 ) {
            if ( is_bool(($arg = func_get_arg(0))) ) {
                if ( $arg ) {
                    $this->expireTime = 3600;
                } else {
                    $this->expireTime = 0;
                }
            } elseif ( is_numeric($arg) ) {
                $this->expireTime = $arg;
            } else {
                return isset($this->cache_expires[$arg])?$this->cache_expires[$arg]:0;
            }
        } elseif ( func_num_args() == 2 ) {
            if ( is_numeric(($arg=func_get_arg(1))) ) {
                $this->cache_expires[func_get_arg(0)] = $arg;
            } elseif ( is_bool($arg) ) {
                if ( $arg ) {
                    $this->cache_expires[func_get_arg(0)] = 3600;
                } else {
                    $this->cache_expires[func_get_arg(0)] = 0;
                }
            }
        }
    }


    /**
    *   Set layout that must be used once time.
    *   @param String $layout
    */
    function setLayoutOnce($layout) {
        $this->_layout_once = $layout;
    }


    /**
    *   Return the path of view file specified related from current module.
    *   If specified complete path return it.
    *   If layout is defined, verify if file within it exists.
    *   @param String $fileName
    *   @return String
    */
    function path($fileName) {
        if ($fileName[0] == '/') { return $fileName; }  // if specified complete path
        $file_path = $this->module_path.'view/'.$fileName;
        if ( !empty($this->layout) ) {
            $context = empty($this->module)?'':$this->module.'/';
            if ( $this->layout[0] == '/' ) { // complete path to layout dir
                $file_path = $this->layout.$context.'view/'.$fileName;
            } else { // default app layout dir
                $file_path = Liber::conf('APP_PATH').'layout/'.$this->layout.'/'.$context.'view/'.$fileName;
            }

            if ( !file_exists($file_path) ) { // return original template file
                $file_path = $this->module_path.'view/'.$fileName;
            }
        }
        return $file_path;
    }


    /**
    *   Load and process a view file using Template files and/or Layout dir if previously specified.
    *   <code>
    *   Usage:
    *   // load index.html
    *   view()->load('index.html');
    *
    *   // load index.html with data
    *   view()->load('index.html', array('framework' => 'Liber'));
    *
    *   // load index.html with data and return the output html
    *   view()->load('index.html', array('framework' => 'Liber'), true);
    *
    *   // load index.html and return the output html
    *   view()->load('index.html', true);
    *   </code>
    *   @param  String $fileName    Absolute path to file name or relative to view/ folder.
    *   @param  Array $data         Array of data to view file.
    *   @param  boolean $return     If True return the processed content of view file .
    *   @return String - if output is true
    */
    function load($fileName, $data=null, $return=false) {
        if ( is_bool($data) ) { $return = $data; }
        $file_path = $this->path($fileName);

        // use layout once time.
        if ( !empty($this->_layout_once) ) {
            $file_path = Liber::conf('APP_PATH').'layout/'.$this->_layout_once.'/'.$this->module.'/view/'.$fileName;
            $this->_layout_once = '';
        }

        $cacheId = $_SERVER['REQUEST_URI'].$file_path;
        if ( $this->template_file ) {
            $cacheId   = $cacheId.$this->template_file;
            $data      = Array('content'=> $this->element($file_path, $data, true) );
            $file_path = Liber::conf('APP_PATH').'template/'.$this->template_name.'/'.$this->template_file;
        }

        // by default, in PROD mode all files doesn't have cache
        $out = '';
        if ( Liber::conf('APP_MODE') == 'PROD' and isset($this->cache_expires[$fileName]) ) {
            // caching
            if ( !($out = Liber::cache()->get( $cacheId )) ) {
                $out = $this->element($file_path, $data, true);
                Liber::cache()->put($cacheId, $out, isset($this->cache_expires[$fileName])?$this->cache_expires[$fileName]:$this->expireTime );
            }

        } elseif ( !$out or Liber::conf('APP_MODE') == 'DEV' ) {
            $out = $this->element($file_path, $data, $return);
        }

        if ( $return )  { return $out; }
        echo $out;
    }

    /**
     * Process view file as simple element.
     * @param  string  $fileName Absolute path to file name or relative to view/ folder.
     * @param  array  $data      Array of data to view file.
     * @param  boolean $return   If True return the processed content of view file .
     * @return string | void
     */
    function element($fileName, $data=null, $return=false ) {
        if ( is_bool($data) ) { $return = $data; }
        $fileName = $this->path($fileName);

        if ( is_array($data) )  { extract($data); }

        if ($return) {
            ob_start();
            include "$fileName";
            $data = ob_get_contents();
            ob_end_clean();
            return $data;
        } else {
            include "$fileName";
        }
    }


    /**
    *   Set current View with $template_file and $template_name specified.
    *
    *   <code>
    *
    *   // set current View to use 'admin.html' as template file stored in <b>APP_PATH/template/default/admin.html</b>.
    *   ->template('admin.html');
    *
    *   // set current View to use 'admin.html' as template file and 'admin' as template dir name stored in <b>APP_PATH/template/admin/admin.html</b>.
    *   ->template('admin.html', 'admin');
    *
    *   </code>
    *   @param  string $template_file Template file name stored in $template_name dir.
    *   @param  string $template_name Value 'default' by default if not specified.
    *   @return void
    */
    function template( $template_file, $template_name='default' ) {
        $this->template_name = $template_name;
        $this->template_file = $template_file;
    }



    /**
    *   Return an object instance from class name specified in Liber::conf('TEMPLATE_ENGINE').
    *   @return Object
    */
    function engine() {

        if ( $this->_engine ) {
            return $this->_engine;
        } elseif ( ($template =  Liber::conf('TEMPLATE_ENGINE')) ) {
            // instancing of Template Engine
            Liber::loadClass($template, 'APP');
            $this->_engine = new $template;
        } else {
            $this->_engine = &$this;
        }

        return $this->_engine;
    }

}

?>