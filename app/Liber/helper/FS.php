<?php
/**
 *  File system helpers.
 *
 * @package     helpers_fs
 * @author      djalmaoliveira@gmail.com
 * @copyright   djalmaoliveira@gmail.com
 * @license
 * @link
 * @since       Version 1.0
 */


/**
 *
 * Return a relative path from $dest_path to $source_path specified.
 *
 * The path returned is relative a first parent directory from $dest_path.
 * Example: $dest_path = '/home/user/myfolder/dest_path';
 *          $source_path = '/home/user/oldfolder/source';
 * The path will consider that you are on '/home/user/myfolder' and will return '../oldfolder/source'.
 * @param   string $source_path
 * @param   string $dest_path
 * @return  string
 */
function fs_relative_path_($source_path, $dest_path) {
    $source_path = realpath(trim($source_path).'/');
    $dest_path   = realpath(trim($dest_path).'/');

    $aS = array_filter(explode('/', $source_path));
    $aD = array_filter(explode('/', $dest_path));
    // check if the source has the same path of destination
    $i = 0;
    foreach($aS as $key => $value) {
        if ( isset($aD[$key]) and $value == $aD[$key]) {
            $i++;
        } else {
            break;
        }
    }

    $countBackDest = (count($aD)-$i)-1;
    $relativePath  = implode('/', array_slice($aS, $i));

    if ($i > 0) {
        $rel = str_repeat('../', $countBackDest).$relativePath;
    } else {
        $rel = '/'.$relativePath;
    }

    return $rel;
}

/**
*   Scan the specified $path using callback user function for each file found.
*   The $func accept two parameters: $dir and $file.
*   The return type of $func must be Array or String.
*   If $recursive is true, then will scan recursively the $path.
*   @param String $path Path to scan.
*   @param Function &$func Callback function.
*   @param boolean $recursive Set true to scan recursively.
*   @return mixed - Result of callback function
*/
function fs_scan_($path, &$func, $recursive=false) {
    static $out = null;
    static $count = 0;

    $count++;
    $entries = scandir($path);
    foreach( $entries as $entry ) {
        if ( in_array($entry, Array('.', '..')) ) { continue; }
        $_path = $path.'/'.$entry;

        $o = $func($path, $entry);

        if ( is_array($o) ) {
            if (  key($o) == '0' and count($o) == 1) {
                $o = current($o);
            }
            if ($out===null) { $out=Array(); }
            $out[] = $o;
        } else {
            $out .= $o;
        }

        if ( $recursive  and is_dir($_path)) {
            fs_scan_($_path, $func, $recursive);
        }
    }

    $count--;
    if ( $count == 0 ) { // detect end of recursion
        $buffer = $out;
        $out    = null;
        return $buffer;
    }
    return $out;
}

?>
