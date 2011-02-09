<?
/**
*   Return json default format string.
*   @param String $status
*   @param mixed $content
*   @return String
*/
function jsonout($status, $content) {
    if ( !is_array($content) ) {
        $content = (Array('text'=>$content));
    }

    return json_encode( Array(
                            'status' => $status,
                            'content'=> $content
                        )
    );
}

/**
*	Move FUNKY_PATH of static cached files to APP_PATH/temp dir.
*	The content of 'temp/' directory can be erased by another process, like cron.
*	@return boolean
*/
function cleancache() {
	$temp_path = Liber::conf('APP_PATH').'temp/';
	if ( !file_exists($temp_path) ) {
		umask(0007);
		mkdir($temp_path, 0770, true);
	}
	$cache_path = Liber::conf('APP_ROOT').Liber::conf('FUNKY_PATH');
	if ( file_exists($cache_path) ) {
		return rename( $cache_path,  $temp_path.'_'.date('Ymdhis'));
	}
	return false;
}
?>