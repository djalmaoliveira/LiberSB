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

        if ( Http::post() ) {
			Liber::loadHelper("Util", 'APP');
			if ( $oSec->validToken(Http::post('token')) ) {
				$oComment = Liber::loadModel('Comment', true);
                $oComment->field('content_id',  Http::post('content_id') );
                $oComment->field('name',  Http::post('name') );
                $oComment->field('email',  Http::post('email') );
                $oComment->field('comment',  Http::post('comment') );
				$oComment->field('datetime', date('Y-m-d H:i:s'));
				$oComment->field('status', "W");
				$oComment->field('netinfo', $_SERVER['REMOTE_ADDR']);
				if ( $oComment->save() ) {
					//send message about comment
					$oConfig = Liber::loadModel('Config', true);
					$oMail = Liber::loadClass("Mailer",true);
					$oMail->to($oConfig->data('contact_email'));
					$oMail->from($oConfig->data('contact_email'));
					$oMail->subject("New comment from ".Liber::conf('APP_URL'));
					$oMail->body("From: ".$oComment->field('name').' <'.$oComment->field('email').'>'."\n\n".$oComment->field('comment'));
					$oMail->send();

					die(jsonout('ok', 'Comment sent successfully.'));
				} else {
					$errors = $oComment->buildFriendlyErrorMsg();
					die(jsonout('error', 'Please fill fields with correct information and try again.<br/>'.implode('<br/>', $errors)));
				}
			} else {
				die(jsonout('error', 'Please, you have to reload this page before send this comment.'));
			}
        }
    }
}

?>