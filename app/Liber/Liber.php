<?php
/**
 * Main framework class file.
 * Copyright (c) 2010-2014 Djalma Oliveira (djalmaoliveira@gmail.com)
 * All rights reserved.
 * @license license.txt
 */

/**
 * Liber is a main class of framework.
 *
 * By default all <i>paths</i> used, must have a final slash '/', like "/my/log/dir/".
 * @author Djalma Oliveira (djalmaoliveira@gmail.com)
 * @package Liber
 * @version 2.2.3
 * @since 1.0
 */
class Liber {

    /**
    *   Framework version
    */
    const VERSION = '2.2.3';


    /**
    *   Store all configurations of application.
    *   Format: $config['config'] = Array - Basic configurations, index are the same properties of Object.
    *           $config['route'] = Array - Configuration about how controller/action/module are invoke.
    *   @var Array
    */
    protected static $aConfig = Array(
                                    'BASE_PATH'         => '',
                                    'APP_PATH'          => '',
                                    'APP_URL'           => '',
                                    'APP_ROOT'          => '',
                                    'APP_MODE'          => 'DEV',
                                    'DB_LAYER'          => 'BasicDb',
                                    'TEMPLATE_ENGINE'   => '',
                                    'ASSETS_DIR'        => 'assets',
                                    'LOG_PATH'          => 'log/',
                                    'CACHE_PATH'        => 'cache/',
                                    'PAGE_NOT_FOUND'    => 'NotFoundController',
                                    'SYS_ERROR'         => 'SysErrorController',
                                    'LANG'              => 'en'
                                );

    /**
    *  The routing system.
    *  <p>In this context a 'segment' of URI is a one of many parts separated by '/'.</p>
    *  <p>In example '/my/route/test' there are three segments.</p>
    *  <pre>
    *  The route processing obey the follow sequence jumping to the next when not found:
    *      1º - Search for direct route; (without URI params);
    *      2º - Search if exist a method in controller of route '/' (with URI params);
    *      3º - Search for controller/action/params name (with URI params);
    *      4º - Search for auto detect route (with URI params);
    *      5º - Search for named params in route;
    *      6º - Load Not Found Controller;
    *
    *  Route format:   $route[URI][METHOD] = Array('ControllerName', 'Action', 'ModuleName');
    *                  $route[URI][METHOD] = '/othet/route';
    *                  METHOD can be: *, get, post, put, delete;
    *                  When to use *, the others for the same URI will be skipped;
    *  uri             => the relative path of resource like: /article/firstArticle ;
    *  ControllerName  => the file name (without extension) of controller inside 'controller/' dir;
    *  Action          => the method name of controller;
    *                     if not specified, means that the action will be a method from Controller ;
    *  ModuleName      => the module name inside 'APP_PATH/module/' dir;
    *                     it means that the controller will be called from this module;
    *  </pre>
    *  Route examples:
    *  <code>
    *      // Module 'Admin' has controller 'UserAdmin' with method 'index'
    *      $route['/admin/user']['*']   = Array('UserAdmin', 'index', 'Admin');
    *
    *      // Controller 'Home' has 'index' method (implicit)
    *      $route['/about']['*']        = Array('Home');
    *
    *      // Controller 'Blog' has 'index' method
    *      $route['/blog']['*']         = Array('Blog', 'index');
    *  </code>
    *
    *  There are a optional behaviour when an additional segments in a URI are put as argument to the method called, example:
    *  <code>
    *      $route['/about']['*']        = Array('Home');
    *      // if called URI '/about/John'
    *      // the part URI (segment) 'John' will be passed as argument to method when the programmer can catch this value
    *      function about($name) {
    *          echo $name;
    *      }
    *
    *  </code>
    *  In this case method param $name is optional, if you need the value inside of called method.
    *
    *  Avoid this routes (don't works or works the wrong way):<code>
    *    /blog/:id::name:   -> params together;
    *    /blog/post/:post:  -> get all, but depends of declared order;
    *    /blog/post/a:post: -> the previous route get this pattern;
    *    /blog/a:id:-:month:  -> allowed only one param per segment;</code>
    *
    *  Take care with routes that have same segments in URI, like:
    *  <code>
    *      $route['/']['*']          = Array('Main');
    *      $route['/buy/cpu']['*']   = Array('Buy');
    *
    *      // If controller 'Main' has 'buy' method, this route won't work as you think
    *      $route['/buy/:component:']['*']  = Array('Components', 'buy');
    *  </code>
    *
    *    @var Array
    */
    public    static $aRoute    = Array();

    /**
    *   Database configuration
    *   @var Array
    */
    public    static $aDbConfig = Array('default'=>Array('localhost','database_name','user','password', 'database_type'));

    /**
    *   Database layer instance
    *   @var Object
    */
    protected static $_db;

    /**
    *   Cache instance
    *   @var Object
    */
    protected static $_cache;

    /**
    *   Current controller instance
    *   @var Controller
    */
    protected static $_controller;

    /**
    *   Returns a database object instance.
    *   @param string $connection_name
    *   @return BasicDb based class,  the database singleton, auto create if the singleton has not been created yet.
    */
    public static function &db( $connection_name='default' ) {
        if ( isset(self::$_db[$connection_name]) ) { return self::$_db[$connection_name]; }

        $c = Liber::conf('DB_LAYER');
        if (  $c == 'BasicDb' ) {
            self::loadClass($c);
        } else {
            self::loadClass(Liber::conf('DB_LAYER'), 'APP');
        }

        self::$_db[$connection_name] = call_user_func($c .'::getInstance', $connection_name);
        return self::$_db[$connection_name];
    }

    /**
    *   Returns a log object instance;
    *   @return object Log
    */
    public static function log() {
        static $Log;

        if ( !is_object($Log) ) {
            $Log = Liber::loadClass('Log', true);
        }
        return $Log;
    }

    /**
    *   Returns a cache instance.
    *   @param String $cacheType Cache type: file.
    *   @return Cache Object.
    */
    public static function cache($class='FileCache') {
        if ( !isset(self::$_cache) ) {
            self::load($class, Liber::conf('BASE_PATH').'cache/');
            self::$_cache = new $class;
        }
        return self::$_cache;
    }


    /**
    *   Start and execute application.
    */
    public static function run() {

        self::processRoute();
        return;
    }

    /**
    *   Set and prepare the enviroment to a Liber application.
    *   @param String $path
    */
    public static function loadApp( $path ) {
        require $path.'config/config.php';

        // set config values
        //
        self::$aConfig    = array_merge(self::$aConfig,  $aConfigs['configs']);
        self::$aDbConfig  = &$aConfigs['db'];
        self::$aRoute     = &$aConfigs['routes'];
        self::$aConfig['APP_PATH']  = $path;
        self::$aConfig['BASE_PATH'] = dirname(__FILE__).DIRECTORY_SEPARATOR;

        self::loadClass('Http');

        // prepare the enviroment
        //
        self::$aConfig['APP_ROOT'] = dirname($_SERVER['SCRIPT_FILENAME']).DIRECTORY_SEPARATOR;
        self::$aConfig['APP_URL']  = ((Http::ssl())?'https':'http').'://'.$_SERVER['SERVER_NAME'].str_replace('//','/',  dirname($_SERVER['SCRIPT_NAME']).'/') ;

        if (  self::$aConfig['APP_MODE'] == 'DEV' ) {
            error_reporting(-1);
        } elseif ( self::$aConfig['APP_MODE'] == 'PROD' ) {
            ini_set('display_errors','Off');
        }

        /**
         * @ignore
         */
        function catchError() {
            if ( !error_get_last() ) {
                return;
            } else {
                Liber::log()->handlerError();
            }
        }

        register_shutdown_function ( 'catchError' ) ;
    }



    /**
     * Set/add config params.
     * Can be added new params if necessary.
     * <code>
     * // How to setup configuration params:
     * Liber::conf('APP_MODE', 'PROD');
     * // and to get params:
     * Liber::conf('APP_MODE');
     * </code>
     * <pre>
     * There are many params that can be used:
     * BASE_PATH        ->  Internal path to Framework;
     * APP_PATH         ->  Internal path to application dir;
     * APP_URL          ->  URL to access aplication (i.e. http://www.domain.com);
     * APP_ROOT         ->  Application path (i.e. /www/app/public_html/);
     * APP_MODE         ->  Values: 'DEV' for development (default) and 'PROD' for production mode;
     * DB_LAYER         ->  Class name used to manipulate the access to database, using BasicDb class by default;
     * TEMPLATE_ENGINE  ->  Class name used to manipulate a Template system;
     * ASSETS_DIR       ->  Default name to assets dir into web public access, APP_ROOT/assets/ by default;
     * LOG_PATH         ->  Default name to log dir where is stored log files,  APP_PATH/log/ by default;
     * CACHE_PATH       ->  Default name to cache dir where is stored cached files, APP_PATH/cache/ by default;
     * PAGE_NOT_FOUND   ->  Application Controller name of default for page not found,  'NotFoundController' by default;
     * SYS_ERROR        ->  Application Controller name of default for system error message,  'SysErrorController' by default;
     * LANG             ->  Application language, used to some system messages. See BASE_PATH/i18n. 'en' by default;
     * </pre>
     *
     *   @param String $p Param name
     *   @param String $v Param value
     *   @return mixed
     */
    public static function conf($p, $v=null) {
        if (  $v === null ) {
            return isset(self::$aConfig[$p])?self::$aConfig[$p]:null;
        }
        elseif( empty ($v) ) {
            self::$aConfig[$p] = NULL;
        } else {
            self::$aConfig[$p] = $v;
        }
    }


    /**
     * Imports the definition of class(es) and tries to create an object/a list of objects from the class.
     *
     * @param String|Array $className Name of the class to be imported, or Array of classNames to load.
     * @param String $path Path to the class file
     * @param bool $createObj Determined whether to create object(s) of the class
     * @return mixed returns true|false by default. If $createObj is TRUE, it creates and return the Object of the class name passed in, if $classname is a Array it will return an Array of objects created in same order.
     */
    public static function load($className, $path, $createObj=FALSE){
        $ret = true;
        if ( is_array($className) ) {
            $out   = Array();
            $class = current($className);
            do  {
                $out[] = self::load($class, $path, $createObj);
            } while ( ($class=next($className)) );
            return $out;
        }
        if ( !class_exists($className)  ) {
            if ( is_file($path . $className.'.php') ) {
                $ret =  (include $path . $className.'.php')!=1?false:true;
            } else {
                $ret = false;
            }
	    }

        if ( $createObj ) {
            return new $className;
        }
        return $ret;
    }


    /**
    *   Detect and return the current configuration about which context should be loaded.
    *   @param String $context  - Module Name
    *   @param boolean $create  - if it must return a object
    *   @return Array
    */
    protected static function prepareLoad($context, $create) {
        $out = Array('context'=>$context, 'create'=>$create);
        if ( is_bool($context) ) {
            $out['create'] = $context;
            $out['context'] = Liber::conf('BASE_PATH');
        } elseif ( $context == 'APP' ) {
            $out['context'] = Liber::conf('APP_PATH');
        } else {
            $out['context'] = Liber::conf('APP_PATH').'module/'.$context.'/';
        }
        return $out;
    }


    /**
    *   Import files of context.
    *   Context can be a Module path or <b>APP_PATH</b> (default).
    *   @param  String $className
    *   @param  mixed $contextORcreateObj  - name of module or boolean
    *   @param  boolean $createObj
    *   @return mixed - object or boolean
    */
    protected static function loadContext($className, $contextORcreateObj='', $createObj=false) {
        $path = Liber::conf('APP_PATH');
        if ( is_bool($contextORcreateObj) ) {
            $createObj = $contextORcreateObj;
        } else {
            if ( !empty($contextORcreateObj) ) {
                $path = (($contextORcreateObj[0]=='/')?'':$path).$contextORcreateObj;
            }
        }
        return self::load($className, $path, $createObj);
    }


    /**
    *   Imports the definition of Controller class. Class file is located at <b>APP_PATH/controller/</b> or <b>APP_PATH/ModuleName/controller/</b>
    *   @param  String $cName
    *   @param  String|boolean  $context  - name of module or boolean
    *   @param  boolean $createObj
    *   @return mixed - object or boolean
    */
    public static function loadController( $cName, $context='', $createObj=false ) {

        if ( is_bool($context) ) {
            $createObj = $context;
            $context   = 'controller/';
        } elseif ( empty($context) ) {
            $context = 'controller/';
        } else {
            $context = ($context[0] == '/'?$context.'controller/':'module/'.$context.'/controller/');
        }

        return self::loadContext($cName, $context, $createObj);
    }

    /**
    *   Imports the definition of Model class. Class file is located at <b>APP_PATH/model/</b> or <b>APP_PATH/ModuleName/model/b>
    *   @param  String $modelName
    *   @param  String|boolean $context  - name of module or boolean
    *   @param  boolean $createObj
    *   @return mixed - object or boolean
    */
    public static function loadModel( $modelName, $context='', $createObj=false ) {
        if ( is_bool($context) ) {
            $createObj = $context;
            $context = 'model/';
        } elseif ( empty($context) ) {
            $context = 'model/';
        } else {
            $context = 'module/'.$context.'/model/';
        }
        return self::loadContext($modelName, $context, $createObj);
    }

    /**
    *   Imports the definition of one Class. Class file is located at <b>BASE_PATH/class/</b> or <b>$context/class/</b>
    *   @param  String $className
    *   @param  String|boolean $context  - name of module or boolean
    *   @param  boolean $createObj
    *   @return mixed - object or boolean
    */
    public static function loadClass( $className, $context=false, $createObj=false ) {
        $out        = self::prepareLoad($context, $createObj);
        $context    = &$out['context'];
        $createObj  = &$out['create'];
        return self::load($className, $context.'class/', $createObj);
    }

    /**
    *   Imports the definition of functions Helper file. File is located at <b>BASE_PATH/helper/</b>  or <b>$context/helper/</b>
    *   @param  String $helperName
    *   @param  String|boolean $context  - name of module or boolean
    *   @return boolean
    */
    public static function loadHelper( $helperName, $context=false) {
        static $loaded = Array();

        if ( !is_array($helperName) ) {
            $helperName = Array($helperName);
        }

        do {
            $i = $context.'helper/'.current($helperName);
            // avoid to include already included file
            if ( !isset($loaded[$i]) ) {
                $out        = self::prepareLoad($context, false);
                $loaded[$i] = true;

                if ( is_file($out['context'].'helper/' . current($helperName).'.php') ) {
                    $ret =  (include $out['context'].'helper/' . current($helperName).'.php')!=1?false:true;
                } else {
                    $ret = false;
                }
            }
        } while (next($helperName));

        return true;
    }

    /**
    *   Imports the definition of Plugin class. Class file is located at <b>BASE_PATH/plugin/</b>  or <b>$context/plugin/</b>
    *   @param  String $pluginName
    *   @param  String|boolean $context  - name of module or boolean
    *   @param  boolean $createObj
    *   @return mixed - object or boolean
    */
    public static function loadPlugin( $pluginName, $context=false, $createObj=false ) {
        $out        = self::prepareLoad($context, $createObj);
        $context    = &$out['context'];
        $createObj  = &$out['create'];
        return self::load($pluginName, $context.'plugin/', $createObj);
    }

    /**
    *   Get named params from uri.
    *   @param Array $rule
    *   @param Array $data
    *   @return Array - Array('paramName' => 'value')
    */
    public static function getParams($rule, $data) {
        // remove empty items
        $ruleItems = array_filter( explode('/',$rule) );
        $dataItems = array_filter( explode('/',$data) );

        $params = array();

        foreach($ruleItems as $ruleKey => $ruleValue) {
            $i = strpos($ruleValue, ':');
            if  ( $i === false ) { continue; }
            $size = strlen($ruleValue);

            // check if the chunk is a key
            if (preg_match('/(:[a-zA-Z0-9]+:)/',$ruleValue)) {
                $f = strpos($ruleValue, ':', $i+1);
                if ( $i === 0 ) {// start at begin
                    // ended before end
                    if ( $f < $size-1 ) {
                        $te = strlen($dataItems[$ruleKey]) - ($size - $f-1);
                        $params[substr($ruleValue, $i+1, $f-$i-1)] = substr($dataItems[$ruleKey],0,$te);
                    } else {
                        $params[substr($ruleValue, $i+1, $f-1)] = $dataItems[$ruleKey];;
                    }

                } else { //when not start at begin
                    // ended before end
                    if ( $f < $size-1 ) {
                        $te = strlen($dataItems[$ruleKey])-($i+($size-($f+1)));
                        $params[substr($ruleValue, $i+1, $f-$i-1)] = substr($dataItems[$ruleKey],$i, $te);;
                    } else {
                        $params[substr($ruleValue, $i+1, $f-1)] = substr($dataItems[$ruleKey],$i);;
                    }
                }
            }
        }
        return $params;
    }


    /**
    *   Redirect client to another URL.
    *   Detect if <b>Liber_URL_REWRITE</b> was set, your WEB Server should be capable to set this param to retrieve from <b>$_SERVER</b> variable.
    *   If this param don't exist, all urls will be used with 'index.php/'.
    *   @param String $url
    *   @param boolean $return  - if must return a url String instead of redirect client
    *   @return String
    */
    public static function redirect($url, $return=false) {

        if ( $url[0] == '/' ) {
            $url[0] = '';
        	if ( !isset($_SERVER['Liber_URL_REWRITE']) ) {
                $url = Liber::conf('APP_URL').'index.php/'.(trim($url));
            } else {
                $url = Liber::conf('APP_URL').(trim($url));
            }
        }
        $url = filter_var($url, FILTER_VALIDATE_URL);
        if ($return) { return $url; }
        header("Location: $url");
        exit;
    }

    /**
    *   Search for configured route and params, returning it.
    *   @param Array  $aRoute
    *   @param String $uri
    *   @return boolean
    */
    protected static function parseRouteParams($aRoute, $uri) {

        foreach ($aRoute as $km => $vm)  {
            if ( strpos($km, ':')===false ) { continue ;}
            if (  substr_count($km, '/') != substr_count($uri, '/') )  { continue; }
            $regex = preg_replace('/(:[a-zA-Z0-9]+:)/', '(.+)', $km);
            $regex = str_replace('/','\/', $regex);
            // there are some route that match ?
            if ( preg_match('/('.$regex.')/', $uri) ) {
                $out['route']  = $km;
                $out['params'] = self::getParams($km, $uri) ;
                return $out;
            }
        }
        return false;
    }

    /**
    *   Detect sort of configuration from route.
    *   If Array return it, if String, it might be a re-routing or redirect to
    *   @param mixed $conf
    *   @return Array
    */
    protected static function getRouteConf( $conf ) {

        if ( is_String($conf) ) {
            // re-routing
            if ( $conf[0]=='/' )  {
                $option = self::getRouteMethod($conf);
                if ( is_array($option)  ) {
                    return $option ;
                } elseif ( is_String($option) ) {
                    return self::getRouteConf($option);
                }

            } else {
                Liber::redirect($conf);
            }
        } else {

            // normalize to 3
            if ( !isset($conf[2]) ) {
                $conf[2] = '';
            }
        }
        return $conf;
    }


    /**
    *   Detects and returns a route options from a specified route, among methods os *.
    *   @param String $route
    *   @return mixed - Array | String or false if don't find.
    */
    protected static function getRouteMethod($route) {
        return isset(Liber::$aRoute[$route]['*'])?Liber::$aRoute[$route]['*']:  (isset(Liber::$aRoute[$route][Http::method()])?Liber::$aRoute[$route][Http::method()]:Array('','',''));
    }


    /**
     * Try to process the controller method's specified.
     * @param  string $controller   Controller name
     * @param  string $method       Method name
     * @param  string $module       Module name
     * @param  Array  $params       List of detected route params
     * @return boolean
     */
    public static function processController( $controller, $method='', $module='', $params=Array() ) {

        if ( !(self::loadController( $controller, $module ) )) {
            return false;
        }

        // get instance kind of Controller and call method (action).
        Liber::$_controller = new $controller( Array('module'=>$module, 'params'=>$params, 'method' => $method) );
        if ( method_exists( Liber::$_controller , $method ) or method_exists( Liber::$_controller , '__call' ) ) {
            call_user_func_array(array(Liber::$_controller, $method), $params);
            return true;
        }
        return false;
    }

    /**
    *   Process route, match and create related controller or redirect to default controller if don't match.
    */
    public static function processRoute() {

        // get URI
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if ( isset($_SERVER['Liber_URL_REWRITE']) ) { // hide index
            if ( strlen($_SERVER['SCRIPT_NAME']) > 10 ) { // with directory
                $uri = substr($path, strlen($_SERVER['SCRIPT_NAME'])-10);
            } else {
                $uri = &$path;
            }
        } else { // without directory
            $uri = substr($path, strlen($_SERVER['SCRIPT_NAME']));
            if (!$uri) { $uri = '/';}
        }

        $uri_parts = array_values(array_filter(explode('/', $uri)));
        if ( !$uri_parts ) { $uri_parts = Array('',''); }

        // Direct match, load pre-configured route (the fast way, recommended)
        $routeOption = Liber::getRouteMethod($uri);
        if ( !self::processController( $routeOption[0], (isset($routeOption[1])?$routeOption[1]:'index'), (isset($routeOption[2])?$routeOption[2]:'') ) ) {
            // Try '/' as a main controller
            $routeOption = Liber::getRouteMethod('/');
            if ( !self::processController( $routeOption[0], $uri_parts[0], (isset($routeOption[2])?$routeOption[2]:''), array_slice($uri_parts, 1) ) ) {
                // Try /controller/action/param1/param2...
                if ( !self::processController( $uri_parts[0], (isset($uri_parts[1])?$uri_parts[1]:'index'), '', array_slice($uri_parts, 2)) ) {
                    // Try to detect route
                    $count_parts = count($uri_parts);
                    $uri_detect = '';
                    for ($i=0; $i < $count_parts; $i++) {
                        $uri_detect .= '/'.$uri_parts[$i];
                        $routeOption = Liber::getRouteMethod( $uri_detect );
                        if ( isset( $uri_parts[($i+1)] )  and ($uri_found = self::processController( $routeOption[0], $uri_parts[($i+1)], (isset($routeOption[2])?$routeOption[2]:''), array_slice($uri_parts, ($i+2)) )) ) {
                            return;
                        }
                    }
                    // Try named params
                    if ( isset($uri_found) and !$uri_found ) {
                        if ( $aParsed = Liber::parseRouteParams(Liber::$aRoute, $uri) ) {
                            $routeOption = Liber::getRouteMethod($aParsed['route']);
                            $routeConf   = Liber::getRouteConf( $routeOption );
                            self::processController( $routeConf[0], $routeConf[1], $routeConf[2], $aParsed['params']);
                            return;
                        }
                    }
                    self::processController( Liber::conf('PAGE_NOT_FOUND'), 'index' ); // force not found
                }
            }
        }
    }


    /**
    *   Return current Controller instance.
    *   @return Controller
    */
    public static function controller() {
        return self::$_controller;
    }


}


/**
*   Class that manipulates Controllers, its creation can be relative a module.
*   All controllers must extends it and have at least the 'index' method.
* @author Djalma Oliveira (djalmaoliveira@gmail.com)
* @package liber
* @since 1.0
*/
class Controller {

    /**
    *   Module name
    *   @var String
    */
    private $module;

    /**
    *   Name of method called
    *   @var String
    */
    private $method;

    /**
    *   Params detected from uri.
    *   @var Array
    */
    private $params;

    /**
    *   View instance
    *   @var View
    */
    private $_view;


    /**
    *   Constructor.
    *   @param Array $args
    *   $args values are:   ['module'] = 'module name' - empty for application controller
    *                       ['params'] = Array() - values of detected named params from route
    */
    public function __construct( $args=Array('module'=>'','params'=>Array(), 'method' => '') ) {
        $this->module = isset($args['module'])?$args['module']:'';
        $this->params = isset($args['params'])?$args['params']:Array();
        $this->method = isset($args['method'])?$args['method']:'';
        header('Content-Type: text/html; charset=utf-8'); // default values
    }

    /**
    *   Load model from application or module if was instanced with it.
    *   @param String $modelName   - Model name
    *   @param String|boolean $createObj  - if must create a object
    *   @return mixed - Model object or boolean
    */
    public function loadModel( $modelName, $createObj=false  ) {
        $module = !empty($this->module)?$this->module:'model/';
        return Liber::loadModel($modelName, $this->module, $createObj);
    }

    /**
    *   Load Class from application or module if was instanced with it.
    *   @param String $className   - Model name
    *   @param String|boolean $createObj boolean - if must create a object
    *   @return mixed - Class object or boolean
    */
    public function loadClass( $className, $createObj=false  ) {
        $module = !empty($this->module)?$this->module.'/':'class/';
        return Liber::loadClass($className, $module, $createObj);
    }

    /**
    *   Load helper from application or module if was instanced with it.
    *   @param String $helperName   - Helper name
    *   @param String|boolean $createObj boolean - if must create a object
    *   @return boolean
    */
    public function loadHelper( $helperName) {
        $module = !empty($this->module)?$this->module:'';
        return Liber::loadHelper($helperName, $module);
    }

    /**
    *   Load plugin from application or module if was instanced with it.
    *   @param String $pluginName - Plugin name
    *   @param boolean $createObj  - if must create a object
    *   @return mixed - Plugin object or boolean
    */
    public function loadPlugin( $pluginName, $createObj=false ) {
        $module = !empty($this->module)?$this->module:'plugin/';
        return Liber::loadPlugin($pluginName, $module, $createObj);
    }

    /**
    *   Get instance of View class.
    *   @return View object
    */
    public function view() {
        if ( !isset($this->_view) ) {
            Liber::loadClass('View');
            $this->_view = new View($this->module);
        }
        return $this->_view;
    }

    /**
    *   Get named or unamed params from uri.
    *   @param String $name  - Param name
    *   @return String
    */
    public function params( $name=null ) {
        if ( !isset($name) ) {
            return $this->params;
        } else {
            return isset($this->params[$name])?urldecode($this->params[$name]):'';
        }
    }

    /**
     * Load a view file based on same Controller::$method name called.
     * The file name loaded format will be: "$method.html"
     * <code>
     * Usage:
     * // print the output of processed view file
     * ->render();
     *
     * // return the output of processed view file
     * ->render(true);
     *
     * // print the output of processed view file with data specified
     * ->render(array('framework'=>'Liber'));
     *
     * // return the output of processed view file with data specified
     * ->render(array('framework'=>'Liber'), true);
     *
     * </code>
     * @param  mixed $data data for view file
     * @param  boolean $return true return the output content
     * @return void
     */
    public function render( $data=array(), $return=false ) {
        $this->view()->load( "$this->method.html", $data, $return );
    }
}

?>