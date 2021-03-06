<?php

/**
 *	Controller of Comments in Admin
 */
class AdminCommentController extends Controller {

    function __construct($p=Array()) {
        parent::__construct($p);

        Liber::loadHelper(Array('Url', 'HTML', 'Form'));
		Liber::loadModel('User');
		if ( !User::logged() ) {
			Liber::redirect( url_to_('/admin', true) );
			exit;
		}

    }


    public function index() {

    }

	public function search() {
        Liber::loadHelper('DT');
		$oSec 		= Liber::loadClass('Security', true);
		$oComment 	= Liber::loadModel('Comment', true);
		$aData['list']		   = $oComment->search(Http::post('search'), Array('limit'=>'10'));
        $aData['search']       = Http::post('search');
        $aData['action']       = url_current_(true);
		$aData['context']	   = "Comment Search";
        $aData['url_operation']= url_to_('/admin/comment/', true);
		$aData['token']		   = $oSec->token(true);
        $this->view()->load('admin/comment_search.html', $aData);
	}

	public function waiting() {
        Liber::loadHelper('DT');
		$oSec = Liber::loadClass('Security', true);
		$oComment = Liber::loadModel('Comment', true);
		$aData['list']		   = $oComment->search(Http::post('search'), Array('limit'=>'10','where'=>' and comment.status="W"', 'order'=>'comment_id desc'));
        $aData['search']       = Http::post('search');
        $aData['action']       = url_current_(true);
		$aData['context']	   = "Comments Waiting";
        $aData['url_operation']= url_to_('/admin/comment/', true);
		$aData['token']		   = $oSec->token(true);
        $this->view()->load('admin/comment_search.html', $aData);
	}

	public function delete() {
		Liber::loadHelper('Util', 'APP');
		$oComment = Liber::loadModel('Comment', true );
		$oSec = Liber::loadClass('Security', true);
		if ( $oSec->validToken(Http::post('token')) ) {
			if ( $oComment->get( Http::post('id') ) ) {
				$aComment = $oComment->toArray();
				if ( $oComment->delete() ) {
					Liber::loadClass('CommentCache', 'APP', true)->cleanCache( $aComment );
					die( jsonout('ok', 'Comment deleted.') );
				} else {
					die( jsonout('error', 'Ocurred a problem, try it later.') );
				}
			}
		}
		die( jsonout('error', 'Please reaload search page.') );
	}


	public function approve() {
		$this->changeStatus( 'A' );
	}

	public function suspend() {
		$this->changeStatus( 'S' );
	}

	protected function  changeStatus($status) {
		switch($status) {
			case 'A':
				$msg_ok = 'Comment approved.';
			break;

			case 'S':
				$msg_ok = 'Comment suspended.';
			break;

			default:
				$msg_ok = '';
		}

		Liber::loadHelper('Util', 'APP');
		$oComment = Liber::loadModel('Comment', true );
		$oSec = Liber::loadClass('Security', true);
		if ( $oSec->validToken(Http::post('token')) ) {
			if ( $oComment->get( Http::post('id') ) ) {
				$oComment->field('status', $status);
				if ( $oComment->save() ) {
					Liber::loadClass('CommentCache', 'APP', true)->cleanCache( $oComment->toArray() );
					die( jsonout('ok', $msg_ok) );
				} else {
					die( jsonout('error', 'Ocurred a problem, try it later.') );
				}
			}
		}
		die( jsonout('error', 'Please reaload search page.') );
	}

}

?>