<?php
/**
*   Class used for create a update file and to use it for updates remote an application.
*   .
*  @package classes
*/
class FileUpdate {

    /**
    *   Store update data.
    *   @var $updateData
    */
    protected $updateData = Array(
                                'workingDir'    => '',
                                'add'           => Array(),
                                'ignore'        => Array(),
                                'delete'        => Array()
                            );


    /**
    *   Set or Get the working dir, it will be used to make relative path from files added.
    *   @param String $dirPath
    *   @return String
    */
    public function workingDir($dirPath=null) {
        if ( $dirPath === null ) {
            return $this->updateData['workingDir'];
        }

        $this->updateData['workingDir'] = trim($dirPath);
        if ( substr($this->updateData['workingDir'], -1, 1) == '/' ) {
            $this->updateData['workingDir'] = substr($this->updateData['workingDir'],0, strlen($this->updateData['workingDir'])-1);
        }

        return $this->updateData['workingDir'];
    }

    /**
    *   Add a path that can be a file or directory, returning the status of operation.
    *   @param String $path
    *   @param boolean $recursive - only used for directory
    *   @return boolean
    */
    public function add($path, $recursive=false) {
        $_path = str_replace($this->updateData['workingDir'], '', $path);
        if ( is_dir($path) ) {

            if ($recursive) {
                Liber::loadHelper("FS");
                $func = create_function('$dir, $file', '
                    return Array($dir."/".$file);
                ');
                $files = fs_scan_($path, $func, $recursive);
                foreach($files as $file) {
                    $this->add($file);
                }
            } else {
                $this->updateData['add'][$_path] = true;
            }
        } elseif ( file_exists($path) ) {

            if ( $this->_ignore($_path) ) {
                return true;
            }

            $this->updateData['add'][$_path]['data'] = file_get_contents($path);
            $this->updateData['add'][$_path]['sha1'] = sha1($this->updateData['add'][$_path]['data']);
            return (strlen($this->updateData['add'][$_path]['data']) > 0);
        }
        return false;
    }

    /**
    *   Path to ignore, that can be a file or directory.
    *   @param String $filePath
    */
    public function ignore($path) {
       $_path = str_replace($this->updateData['workingDir'], '', $path);
       $this->updateData['ignore'][$_path] = true;
    }

    /**
    *   Path to delete, that can be a file or directory.
    *   @param String $filePath
    *   @param boolean $recursive - only used for directory
    */
    public function delete($path, $recursive=false) {
       $_path = str_replace($this->updateData['workingDir'], '', $path);
       if ($this->_ignore($_path)) { return; }
       $this->updateData['delete'][$_path] = $recursive;
    }


    /**
    *   Search in ignore list if there is a path that match with $path specified.
    *   @param String $path
    *   @return boolean
    */
    protected function _ignore($path) {
        foreach( $this->updateData['ignore'] as $_path => $v) {
            if ( strpos($path, $_path) === 0 ) {
                return true;
            }
        }
        return false;
    }

    /**
    *   Load an update file in current instance, returning the state of operation.
    *   @param String $filePath
    *   @return boolean
    */
    public function loadUpdate($filePath) {
        if ( is_file($filePath) ) {
            return ($this->updateData = unserialize(base64_decode(file_get_contents($filePath))));
        }
        return false;
    }

    /**
    *   Write the current update data to file, returning the state of operation.
    *   @param String $filePath
    *   @return boolean
    */
    public function writeUpdate($filePath) {
        $dir = dirname($filePath);
        if ( !is_dir($dir) ) {
            mkdir($dir, 0700, true);
        }
        return (file_put_contents($filePath, base64_encode(serialize($this->updateData))) !== false);
    }


    /**
    *   Execute the update using current update data, returning the state of operation.
    *   Return true if everything is ok, otherwise Array of files and its problems.
    *   @return mixed
    */
    public function processUpdate() {
        $errors = Array();
        static $msg;
        if ( !is_array($msg) ) {
            $lang = (Liber::conf('LANG')=='')?'en':strtolower( Liber::conf('LANG') );
            $msg = include Liber::conf('BASE_PATH').'i18n/class/FileUpdate.'.$lang.'.php';
        }


        // verify data integrity and target files
        foreach( $this->updateData['add'] as $file => $aFile ) {
            $filePath = $this->updateData['workingDir'].$file;
            if ( is_array($aFile) and sha1($aFile['data']) != $aFile['sha1']) {
                $errors[$file] = $msg['CHECKSUM'];
                continue;
            }
            if ( is_array($aFile) and is_file($filePath) and !is_writeable($filePath)) {
                $errors[$file] = $msg['FILENOTWRITE'];
            }
        }

        // error found
        if ( count($errors) > 0 ) {
            return $errors;
        }

        // write files
        foreach( $this->updateData['add'] as $file => $aFile ) {
            $filePath = $this->updateData['workingDir'].$file;
            $dir = '';
            if ( is_array($aFile) ) {
                if ( !is_dir(dirname($filePath)) ) {
                    $dir = dirname($filePath);
                }
            } else {
                $dir = $aFile;
            }

            if ( !empty($dir) ) {
                try {
                    mkdir($dir, 0700, true);
                } catch(Exception $e) {
                    $errors[$dir] = $msg['DIRNOTWRITE'];
                    break;
                }
            }

            if ( is_array($aFile) and file_put_contents($filePath, $aFile['data']) === false ) {
                $errors[$file] = $msg['NOTWRITED'];
            }

        }

        // error found
        if ( count($errors) > 0 ) {
            return $errors;
        }


        // delete files
        foreach( $this->updateData['delete'] as $file => $recursive ) {
            $filePath = $this->updateData['workingDir'].$file;

            if ( is_dir($filePath) ) {
                if ( $recursive ) {
                    Liber::loadHelper("FS");
                    $func = create_function('$dir, $file', '
                        $path = $dir."/".$file ;
                        return Array($path);
                    ');
                    $files = (fs_scan_($filePath, $func, $recursive));
                    $files = array_flip($files);
                    foreach($files as $file_ => $size) {
                        $files[$file_] = substr_count($file_, '/');
                    }
                    arsort($files); // begin from directory that has the longest path
                    foreach($files as $deletePath => $size ) {
                        if ( is_dir($deletePath) ) {
                            rmdir($deletePath);
                        } else {
                            unlink($deletePath);
                        }
                    }
                }
                rmdir($filePath);
            } else if ( is_file(($filePath)) ) {
                if ( !unlink($filePath) ) {
                    $errors[$file] = $msg['NOTDELETED'];
                }
            }
        }

        // error found
        if ( count($errors) > 0 ) {
            return $errors;
        }

        return true;
    }


    /**
    *   Return an array of current added, ignored and deleted files.
    *   @return Array
    */
    public function files($type=null) {
        if ( $type === null ) {
            return Array(   'add'       =>  $this->updateData['add'],
                            'ignore'    =>  $this->updateData['ignore'],
                            'delete'    =>  $this->updateData['delete']
            );
        } else {
            $_arr = Array("add", "ignore", "delete");
            $out = Array();
            foreach( $_arr as $mode ) {
                foreach( $this->updateData[$mode]  as $fileName => $value ) {
                    $out[$mode][$fileName] = Array(
                                                "size" => strlen($value['data']),
                                                "sha1" => $value['sha1']
                                            );
                }
            }
            return $out;
        }
    }

}
?>
