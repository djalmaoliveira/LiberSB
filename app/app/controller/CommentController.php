<?php

/**
 *
 *
 */
class CommentController extends Controller {

    function __construct($p=Array()) {
        parent::__construct($p);

        Liber::loadHelper(Array('Url', 'HTML', 'Form'));
    }


    public function index() {

    }

    /* Show Comment form */
    public function comment() {
        Liber::loadHelper('Util', 'APP');
		$oSec = Liber::loadClass('Security', true);

        if ( Liber::requestedMethod() == 'post' ) {
			Liber::loadHelper("Util", 'APP');
			if ( $oSec->validToken(Input::post('token')) ) {
				$oComment = Liber::loadModel('Comment', true);	
				$oComment->loadFrom( Input::post() );
				$oComment->field('datetime', date('Y-m-d H:i:s'));
				$oComment->field('status', "W");
				$oComment->field('netinfo', $_SERVER['REMOTE_ADDR']);	
				if ( $oComment->save() ) {
					$oCache = Liber::loadClass('CommentCache', "APP", true);
					$oCache->clean(Liber::conf('APP_ROOT').Liber::conf('FUNKY_PATH').'comments/'.Input::post('content_id').'/');
					die(jsonout('ok', 'Comment sent successfully.'));
				} else {
					$errors = $oComment->buildFriendlyErrorMsg();
					die(jsonout('error', 'Please fill fields with correct information and try again.<br/>'.implode('<br/>', $errors)));
				}
			} else {
				die(jsonout('error', 'Please, you have to reload this page before send this comment.'));
			}
        }
		
		$aData['token']		 = $oSec->token(true);
		$aData['content_id'] = Input::get('content_id');
        $aData['action']     = url_to_('/comment', true);
        $this->view()->load('comment_form.html', $aData);
    }



}

?>