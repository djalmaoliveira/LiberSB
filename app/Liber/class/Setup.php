<?php
/**
*   @package core.class
*/

/**
*   Class that manipulates some jobs, to aid set up application.
*/
class Setup {

    private $app_root;
    private $assets_dir;
    private $app_path;

    function __construct($aParams=null) {
        if ( $aParams === null ) {
            $this->app_root   = Liber::conf('APP_ROOT');
            $this->assets_dir = Liber::conf('ASSETS_DIR');
            $this->app_path   = Liber::conf('APP_PATH');
        } else {
            $this->app_root   = $aParams['APP_ROOT'];
            $this->assets_dir = $aParams['ASSETS_DIR'];
            $this->app_path   = $aParams['APP_PATH'];
        }
    }


    /**
    *   Make public, all assets dir on application, including modules.
    *   Publish assets dir to APP_ROOT/ASSETS_DIR, and modules assets to APP_ROOT/ASSETS_DIR/MODULE_NAME.
    *   After it, these dirs going to have public access by web.
    *   @return Array - of messages about something wrong found.
    */
    function publishAssets() {
        $aOut           = Array();
        clearstatcache ( ) ;
        $this->prepareAssetDir();

        // delete all links from asset root dir
        $path = $this->app_root.$this->assets_dir;
        $aFiles = scandir($path);
        foreach ( $aFiles as $file ) {
            if ( is_link($path.'/'.$file) ) {
                unlink($path.'/'.$file);
            }
        }

        // publish app assets
        $aOut[] = $this->publishAsset();

        // publish modules assets
        $d = dir($this->app_path.'module');
        while (false !== ($entry = $d->read())) {
            if ( $entry == '.' or $entry == '..' ) { continue; }
            $aOut[] = $this->publishAsset($entry);
        }
        $d->close();

        return array_filter($aOut);
    }


    /**
    *   Create a assets dir on www.
    */
    function prepareAssetDir() {
        if ( !file_exists($this->app_root.$this->assets_dir) ) {
			umask(0007);
            @mkdir($this->app_root.$this->assets_dir, 0775, true);
        }
    }


    /**
    *   Publish asset dir from context that can be application dir, module dir or layout dir,  and link to a specified name on www.
    *   @param String $context_path
    *   @param String $to_asset_dir
    */
    function publishContextAsset($context_path, $to_asset_dir) {
        $this->prepareAssetDir();

        $asset_path = $context_path.$this->assets_dir;
        if ( is_dir($asset_path) ) {
            Liber::loadHelper('FS');
            $link_path = $this->app_root.$this->assets_dir.'/'.$to_asset_dir;
            if ( is_link($link_path) ) {
                unlink($link_path);
            }
            $source = fs_relative_path_($asset_path, $link_path);

            symlink ($source , $link_path) ;
        }
    }


    /**
    *   Publish application assets by default or module assets if specified.
    *   @param String $moduleName
    */
    function publishAsset($moduleName='') {
        if ( !empty($moduleName) ) {
            $this->publishContextAsset( $this->app_path.'module/'.$moduleName.'/', $moduleName );
        } else {
            $this->publishContextAsset($this->app_path, 'app');
        }
    }


    /**
    *   Create a layout structure based on current application.
    *   @param String $name  - Layout name
    *   @param mixed $modules  - Array of modules names or true to create all modules
    */
    function createLayout($name, $modules=Array()) {
        // clone application
        $this->_duplicateLayout( $this->app_path,  $this->app_path.'layout/'.$name.'/' );

        // clone modules
        if ( $modules === true ) {
            $modules = scandir( $this->app_path.'module/' );
            $k = array_search('.', $modules);
            if ($k!==false) { unset($modules[$k]); }
            $k = array_search('..', $modules);
            if ($k!==false) { unset($modules[$k]); }
        }

        foreach ( $modules as $moduleName ) {
            $this->_duplicateLayout( $this->app_path.'module/'.$moduleName.'/', $this->app_path.'layout/'.$name.'/module/'.$moduleName.'/' );
        }
    }


    /**
    *   Publish layout assets from specified name, from application or module.
    *   In $layout can be specified a complete path to layout that not follow the convention.
    *   @param String $layout
    *   @param String $moduleName
    */
    function publishLayoutAsset($layout, $moduleName='') {
        if ( $layout[0] == '/' ) {
            $layoutPath = &$layout;
        } else {
            $layoutPath = $this->app_path.'layout/'.$layout.'/';
        }

        if ( !empty($moduleName) ) {
            $path    = $layoutPath.'module/'.$moduleName.'/';
            $context = &$moduleName;
        } else {
            $path    = &$layoutPath;
            $context = 'app';
        }

        $this->publishContextAsset($path, $context);

    }


    /**
    *   Return Array of files found using Array of regex patterns as a filter from specified path.
    *   @param String $path
    *   @param Array $pattern
    *   @param boolean $recursive
    *   @return Array
    */
    private function _listRealFiles($path, $pattern=Array(), $recursive=false) {
        $path = realpath($path);
        if ( count($pattern) == 0 ) {
            $pattern[] = "/./";
        }

        if ( !is_dir($path) ) {
            return false;
        }

        $aOut   = Array('dir'=>Array(), 'file'=>Array());
        $aFiles = Array();
        $aFiles = scandir($path);

        $k = array_search('.', $aFiles);
        if ($k!==false) { unset($aFiles[$k]); }
        $k = array_search('..', $aFiles);
        if ($k!==false) { unset($aFiles[$k]); }

        foreach ($aFiles as $k => $file) {
            $file_path = $path.'/'.$file;
            if ( is_link( $file_path ) ) { unset($aFiles[$k]); continue;}
            if ( is_dir($file_path)  ) {
                $aOut['dir'][] = $file_path;
                if ( $recursive ) {
                    $aTemp = $this->_listRealFiles($file_path, $pattern, $recursive);
                    if ( isset($aTemp['file']) ) {
                        foreach ( $aTemp['file'] as $ffile) {
                            $aOut['file'][] = $ffile;
                        }
                    }
                }
            } else {
                $aOut['file'][] = $file_path;
            }
        }
        return $aOut;
    }


    /**
    *   Duplicate dirs and files for Layout.
    *   @param String $source_path
    *   @param String $dest_path
    */
    public function _duplicateLayout($source_path, $dest_path) {
        // assets
        $this->_duplicateDir($source_path.$this->assets_dir, $dest_path.$this->assets_dir);
        // views
        $this->_duplicateDir($source_path.'view/', $dest_path.'view/');
    }


    /**
    *   Make a copy from $source_path to $dest_path, only .
    *   @param String $source_path
    *   @param String $dest_path
    */
    public function _duplicateDir( $source_path, $dest_path ) {
        $source_path = realpath($source_path);
        if ( $source_path === false ) { return; }
		umask(0007);
        @mkdir($dest_path, 0777, true);
        $dest_path   = realpath($dest_path);
        if (  $dest_path === false) { return ; }

        $aFiles      = $this->_listRealFiles( $source_path, Array(), true);

        foreach ($aFiles['dir'] as $dir) {
            $r_path = str_replace($source_path, '', $dir);
            @mkdir($dest_path.$r_path, 0777, true);
        }

        foreach( $aFiles['file'] as $file ) {
            $r_path = str_replace($source_path, '', $file);
            $file_path = $dest_path.$r_path;
            copy($file, $file_path);
        }
    }

}
?>
