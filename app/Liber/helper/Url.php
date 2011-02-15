<?php

/**
 *
 * @package core.helpers
 * @author		djalmaoliveira@gmail.com
 * @copyright	djalmaoliveira@gmail.com
 * @license
 * @link
 * @since		Version 1.0
 */




/**
 *  Create relative url from APP_URL.
 *
 *
 * @access	public
 * @param string $relative_url
 * @param boolean $return
 * @return string
 */
function url_to_($relative_url = '', $return=false) {
    $url = Liber::redirect($relative_url, true);

    if ($return) {
        return $url;
    } else {
        echo $url;
    }
}



/**
 * Current URL
 *
 * Returns the current URL requested.
 *
 * @access	public
 * @param string $relative_url
 * @param boolean $return
 * @return string
 */
function url_current_($return=false) 	{
    $url = (Liber::isSSL()?'https':'http').'://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
    if ($return) {
        return $url;
    } else {
        echo $url;
    }
}

/**
*   Returns a URL to app asset.
*   @param string $relative_url
*   @param boolean $return
*   @return string
*/
function url_asset_( $relative_url='' , $return=false) {
    $url = Liber::conf('APP_URL').Liber::conf('ASSETS_DIR').'/app'.$relative_url;

    if ( $return ) {
        return $url;
    } else {
        echo $url;
    }
}


/**
*   Returns current module name that will be used by others helper functions that use it.
*   If specified a name, will set up with it.
*   @param string $moduleName
*   @return string
*/
function url_module_name_asset_( $moduleName=null ) {
    static $modName = '';

    if ( $modName =='' ) {
        $modName = Liber::conf('ASSETS_DIR');
    }
    if ( func_num_args() == 0 )  {
        return $modName;
    } else {
        $modName = $moduleName;
    }
}


/**
*   Returns a URL to module asset.
*   @param string $relative_url
*   @param boolean $return
*   @return string
*/
function url_module_asset_($relative_url='', $return=false) {
    $url = Liber::conf('APP_URL').Liber::conf('ASSETS_DIR').'/'.url_module_name_asset_().$relative_url;

    if ( $return ) {
        return $url;
    } else {
        echo $url;
    }
}



/**
*   Return a clean specified URL.
*   Change spaces and others charactes to $separator by default '-'.
*   @param String $url
*   @param String $separator
*   @param boolean $return
*   @return String
*/
function url_clean_($url, $separator="-", $return=false) {
    if ( is_bool($separator) ) {
        $return = $separator;
        $separator = '-';
    }

    $aUrl = parse_url($url);
    $path = isset($aUrl['path'])?$aUrl['path']:'';

    $path = strtr(utf8_decode($path), utf8_decode('ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ'),'AAAAAAACEEEEIIIIDNOOOOOOUUUUYbsaaaaaaaceeeeiiiidnoooooouuuyybyRr');
    $path = urlencode( str_replace(' ', $separator, trim($path)) );
    $path = str_replace('%2F', '/', $path);
    while ( ($pos = strpos($path, '%')) !== false ) {
        $part = substr($path, $pos,3);
        $path  = str_replace($part, $separator, $path);
    }

    $url = (isset($aUrl['scheme'])?$aUrl['scheme'].'://':'').((isset($aUrl['host'])?$aUrl['host']:'')).$path;

    if ( $return ) {
        return $url;
    } else {
        echo $url;
    }
}

?>