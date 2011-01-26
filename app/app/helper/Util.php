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
?>
