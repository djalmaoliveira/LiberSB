<?php
/**
 * Default class of Caching features.
 * @author Djalma Oliveira <djalmaoliveira@gmail.com>
 * @copyright Copyright &copy; 2010 Djalma Oliveira
 * @license license.txt
 * @package cache
 */
class FileCache {
    private $context;
    private $contextPath;

    function __construct($context='default') {
        $this->context($context);
    }


    /**
    *   Set or get context of instance.
    *   The context is a named folder where cache data will be stored.
    *   Return current context if not specified or a new if to.
    *   @param String $context
    *   @return String
    */
    public function context($context=null) {
        if ( $context != null ) {
            $this->context = &$context;
            $this->contextPath = ($this->context[0] == '/')?$this->context : Liber::conf('APP_PATH').Liber::conf('CACHE_PATH').$this->context.'/';

            if ( !file_exists($this->contextPath) ) {
                mkdir($this->contextPath, 0777, true);
            }
        }
        return $this->context;
    }


    /**
    *   Get stored cache by $id specified.
    *   Return String of stored cache or false if cache have expired time or not exist.
    *   @param mixed $id
    *   @return String/False
    */
    public function get($id) {
        $file = md5($id);
        if ( file_exists($this->contextPath.$file) ) {
            $time = filemtime( $this->contextPath.$file );
            if ( $time !== false and time() < $time ) {
                return file_get_contents($this->contextPath.$file);
            }
        }
        return false;
    }


    /**
    *   Put $data on cache by $id specified.
    *   You can set a $expire time of this cached $id, use seconds to.
    *   @param mixed $id
    *   @param String $data
    *   @param Integer $expire
    *   @return String - File name of stored content.
    */
    public function put($id, $data=null, $expire=3600) {
        $file = md5( $id );

        file_put_contents( $this->contextPath.$file, $data, LOCK_EX );
        if ( $expire == 0 ) {
            $expire = 315360000;
        }

        touch($this->contextPath.$file, (time()+$expire) );
        return $this->contextPath.$file;
    }


    /**
    *   Clear all stored cache from current context or a specified $id only.
    *   @param mixed $id
    */
    public function clear( $id = null ) {
        if ( $id != null ) {
            unlink( $this->contextPath.md5($id) );
        } else {
            $aFiles = scandir($this->contextPath);
            unset($aFiles[array_search('..', $aFiles)]);
            unset($aFiles[array_search('.',  $aFiles)]);
            foreach ( $aFiles as $filename ) {
                unlink( $this->contextPath.$filename );
            }
        }
    }
}



?>
