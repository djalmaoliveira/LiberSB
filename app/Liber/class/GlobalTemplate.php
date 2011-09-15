<?php
/**
*   @package core.class
*/


/**
*   Class that manipulates global template system.
*/
class GlobalTemplate {
    /**
    *   @var Current template folder name.
    */
    private $templateFolderName = 'default';

    /**
    *   @var Current file name of template.
    */
    private $modelName    = 'default.html';

    /**
    *   @var Current context path.
    */
    private $contextPath   ;

    /**
    *   @var Current module.
    */
    private $module   ;

    /**
    *   @var View instance
    */
    private $_view;


    /**
    *   Create a new instance.
    *   If $view specified, then the behaviour of load() method will use this $view instance, else will create a $_view relative to $module specified.
    *   @param String $module
    *   @param View $view
    */
    function __construct( $module=null, $view=null) {
        if ( $view instanceof View ) {
            $this->_view = $view;
            $this->module = &$module;
        } else {
            Liber::loadClass('View');
            $this->_view = new View(Array('module'=>$module));
        }
        $this->context($module);
    }


    /**
    *   Set ou Get context path of template.
    *   Can be specified a ModuleName, non value for default application folder or full path.
    *   @param String $context  - empty to get
    *   @return String - Path of current template context
    */
    function context( $context=null ) {

        if ( $context[0] == '/' ) { // complete path specified
            $this->contextPath = $context;
        } elseif ( empty($context) ) { // app
            $this->contextPath = Liber::conf('APP_PATH').'template/';
        } else {    // module
            $this->contextPath = Liber::conf('APP_PATH')."module/$context/template/";
        }
        $this->module = &$context;

        return $this->contextPath;
    }


    /**
    *   Same functionality of View::load() method, but using current template.
	*	If application set a LAYOUT, then it will check if exists a layout template file.
    *   @param String $fileName
    *   @param Array $aData
    *   @param boolean $return
    *   @return mixed <null | String>
    */
    function load($fileName, $aData=Array(), $return=false) {
        $viewFile = $this->_view->path($fileName);
		$template_path = $this->contextPath.$this->templateFolderName.'/'.$this->modelName;
		// detect if exists layout template file
		$layout = Liber::conf('LAYOUT');
		if ( $layout ) {
			// detect if hsa a absolute path
			if ( $layout[0] != '/' ) {
				$layout = Liber::conf('APP_PATH').'layout/'.Liber::conf('LAYOUT');
			}

			$layout_template_path = $layout.'/template/'.$this->templateFolderName.'/'.$this->modelName;
			if ( file_exists($layout_template_path) ) {
				$template_path = &$layout_template_path;
			}
		}

        if ($this->_view->cache($fileName) > 0 and  Liber::conf('APP_MODE') == 'PROD'  ) {

            $cacheId  = $_SERVER['REQUEST_URI'].$viewFile.$this->modelName;
            if ( !($out = Liber::cache()->get( $cacheId )) ) {

                $out = $this->_view->engine()->load($template_path, Array('content'=> $this->_view->engine()->load($viewFile, $aData, true) ), true);
                Liber::cache()->put($cacheId, $out, is_numeric( $this->_view->cache($fileName) )?$this->_view->cache($fileName):3600 );
            }

        } elseif ( empty($out) or Liber::conf('APP_MODE') == 'DEV' ) {
            $out = $this->_view->engine()->load($template_path, Array('content'=> $this->_view->engine()->load($viewFile, $aData, true) ), true);
        }

        if ($return) { return $out; }
        echo $out;
    }


    /**
    *   Set or Get the name of template folder on current context.
    *   @param String $name
    *   @return String - Current template folder name.
    */
    function name($name=null) {
        if ($name !== null) {
            $this->templateFolderName = $name;
        }

        return $this->templateFolderName;
    }

    /**
    *   Set or Get file name of model name into template folder.
    *   @param String $model
    *   @return String - Current file name of model template.
    */
    function model($model=null) {
        if ($model !== null) {
            $this->modelName = $model;
        }

        return $this->modelName;
    }
}

?>