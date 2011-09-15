<?php
/**
*   @package core.class
*/

/**
*   Class that manipulates html output and html template engine loads.
*/
class View {

    private     $module;
    private     $module_path;
    private     $_engine;
    private     $_template;
    private     $cache_expires  = Array();
    private     $expireTime     = 3600;
    public      $layout         = '';
    private     $_layout_once   = '';

    function __construct($params=Array()) {
        // set object

        $this->layout = Liber::conf('LAYOUT');
        $this->module = &$params['module'];
        if ( isset($params['module']) and !empty($params['module']) ) {
            $this->module_path = Liber::conf('APP_PATH').'module/'.$params['module'].'/';
        } else {
            $this->module_path = Liber::conf('APP_PATH');
        }

        if ( $this->_engine == null ) {
            // instancing of Template Engine
            $template =  Liber::conf('TEMPLATE_ENGINE');
            Liber::loadClass($template);

            $engineSettings = Array('cache_expires'=>&$this->cache_expires );

            if ( empty( $template ) ) {
                Liber::loadClass('TemplateEngine');
                $this->_engine = new TemplateEngine( $engineSettings );
            } else {
                Liber::loadClass($template, 'APP');
                $this->_engine = new $template( $engineSettings );
            }
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
    *   Uses:
    *   ->cache();          // return cache expire time in seconds
    *   ->cache(true);      // enable caching to default value 3600s
    *   ->cache(false);     // disable caching
    *   ->cache('filename.html'); // return current Array data about the specified file
    *   ->cache('filename.html', true); // set caching to default value for specified file
    *   ->cache('filename.html', false); // disable caching for specified file
    *   ->cache('filename.html', 2000); // set caching with 2000s to specific file
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
                    $this->cache_expires[func_get_arg(0)] = 0;
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
        $this->layout_once = $layout;
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
    *   Call 'load' method from template engine instance.
    *   @param  String $fileName
    *   @param  Array $data
    *   @param  boolean $output
    *   @return String - if output is true
    */
    function load($fileName, $data=null, $output=false) {

        $file_path = $this->path($fileName);

        // use layout once time.
        if ( !empty($this->_layout_once) ) {
            $file_path = Liber::conf('APP_PATH').'layout/'.$this->_layout_once.'/'.$this->module.'/view/'.$fileName;
            $this->_layout_once = '';
        }

        // by default, in PROD mode all files doesn't have cache
        if ( $this->cache($fileName) > 0 and Liber::conf('APP_MODE') == 'PROD' ) {

            // caching
            $cacheId = $_SERVER['REQUEST_URI'].$file_path;
            if ( !($out = Liber::cache()->get( $cacheId )) ) {
                $out = $this->_engine->load($file_path, $data, true);
                Liber::cache()->put($cacheId, $out, isset($this->cache_expires[$fileName]['expires'])?$this->cache_expires[$fileName]['expires']:$this->expireTime );
            }

        } elseif ( empty($out) or Liber::conf('APP_MODE') == 'DEV' ) {
            $out = $this->_engine->load($file_path, $data, $output);
        }

        if ( $output )  { return $out; }
        echo $out;
    }


    /**
    *   Return a GlobalTemplate instance base on current view context.
    *   @return GlobaTemplate
    */
    function template( $module = null ) {
        if (!is_object($this->_template)) {
            Liber::loadClass('GlobalTemplate');
            $this->_template = new GlobalTemplate($module,$this);
        }

        return $this->_template;
    }


    /**
    *   Return current TemplateEngine instance.
    *   @return Object
    */
    function engine() {
        return $this->_engine;
    }

}

?>