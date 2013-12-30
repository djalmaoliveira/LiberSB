<?php
/**
*   Class that manage funky cache files.
*   Funky cache simply write a raw html content to a file to improve speed access.
*   This files used to be put on a public url path and auto-created when it's missing, using NotFoundController for example.
*	Some methods can be overrided by a extended class to adapt to current features of application.
*  @package classes
*/
class Funky {
    /**
    *   Store the pattern String that is used to match against a URL.
    *   Usefull to match if a random URL is a funky cache directory.
    *   @var String
    */
    protected $urlPattern;

    function __contruct() {
        $this->urlPattern = Liber::conf('APP_URL');
    }

    /**
    *   Try match a $url specified against 'urlPattern', returning true or false.
    *   @param String $url
    *   @return boolean
    */
    function matchUrl( $url ) {
        return strpos($url, $this->urlPattern)===0?true:false;
    }

    /**
    *   This method is used for return a public URL to cached page.
    *   With it you can create your own rules how your cache should work.
    *   Work together with create() method.
    *   @param mixed
    *   @return mixed
    */
    function url($file) {
        return Liber::conf('APP_URL').'cache/'.$file;
    }

    /**
    *   Create a cache data from $aData specified.
    *   With it you can create your own rules how your cache should be created.
    *   Work together with url() method.
    *   @param mixed
    *   @return mixed
    */
    function create($aData) {
        return $this->put(Liber::conf('APP_ROOT').'cache/'.$aData['file'], $aData['content']);
    }

    /**
    *   Put a raw file $data to a specified $path.
    *   Return a boolean value indicating if it did.
    *   @param String $path
    *   @param String $data
    *   @return boolean
    */
    function put($path, $data) {
        umask(0007);
        $aPath = pathinfo($path);
        if ( !file_exists($aPath['dirname']) ) {
            mkdir($aPath['dirname'], 0777, true);
        }
        return (file_put_contents($aPath['dirname'].'/'.$aPath['basename'], $data , LOCK_EX) !== false);
    }

    /**
    *   Clean a specified $path, recursively or not.
    *   @param String  $path
    *   @param boolean $recursive
    */
    function clean($path, $recursive=false) {
        $path = rawurldecode($path);
        if ( file_exists($path) ) {
            if ( is_file($path) ) {
                unlink($path);
            } else {
                Liber::loadHelper('FS');
                $f = create_function('$dir, $file','
                    unlink($dir.$file);
                    return $file;
                ');
                fs_scan_($path, $f, $recursive);
            }
        }
    }

}

?>