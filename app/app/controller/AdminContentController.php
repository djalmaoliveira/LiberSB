<?php

/**
 *
 *
 */
class AdminContentController extends Controller {

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

    /* Show Content Editor */
    public function edit() {
        Liber::loadHelper('Util', 'APP');
		$oSec = Liber::loadClass('Security', true);
        list($oContent, $oContType) = Liber::loadModel(Array('Content', 'ContentType'), true);

        if ( Liber::requestedMethod() == 'post' ) {
			if ( $oSec->validToken(Input::post('token')) ) {
				$oContent->loadFrom( Input::post() );
				$oContent->field('datetime', date('Y-m-d H:i:s'));
				if ( $oContent->save() ) {
					$oCache = Liber::loadClass('ContentCache', 'APP' , true)->cleanCache($oContent->toArray());
					die( jsonout('ok', Array('text'=>'Document saved at '.date('H:i:s'), 'content_id'=>$oContent->field('content_id'))) );
				} else {
					Liber::log()->add('Document can\'t be saved.','error');
					die( jsonout('error', implode($oContent->buildFriendlyErrorMsg()) ) ) ;
				}
			}
			die( jsonout('error', 'Please reload this page.' ) ) ;
        }

        // new content
        if ( !Input::get('id') ) {
			$oContType->get( Input::get('content_type_id') );
            $aData['content_type_id'] = Input::get('content_type_id');
			$aData['tab'] = 'New '.$oContType->field('description');
        } else {
            $oContent->get( Input::get('id') );
			$oContType->get( $oContent->field('content_type_id') );
			$aData['tab'] = 'Editing '.$oContType->field('description');
            $aData['content_type_id'] = $oContent->field('content_type_id');
        }

        $aData['content'] = $oContent->toArray();
        $aData['action']  = url_to_('/admin/content/edit', true);
		$aData['token']   = $oSec->token();
        $this->view()->load('admin/content_editor.html', $aData);
    }

    /* delete a content, only by POST method */
    public function delete() {
        Liber::loadHelper('Util', 'APP');
		$oSec = Liber::loadClass('Security', true);
        if ( Liber::requestedMethod() == 'post' ) {
			if ( $oSec->validToken( Input::post('token') ) ) {
				$oContent = Liber::loadModel('Content', true);
				if ( $oContent->delete( Input::post('content_id') ) ) {
					die( jsonout('ok', 'Document deleted successfully.' ) ) ;
				} else {
					die( jsonout('error', implode($oContent->buildFriendlyErrorMsg()) ) ) ;
				}
			} else {
				die( jsonout('error', 'Please reload search page.' ) ) ;
			}
        }

    }

    /* Search or Show form search. */
    public function search() {
        Liber::loadHelper('DT');
		$oSec = Liber::loadClass('Security', true);
        list($oContent, $oContType) = Liber::loadModel(Array('Content', 'ContentType'), true);

        $oContType->get( Input::get('content_type_id')?Input::get('content_type_id'):Input::post('content_type_id') );
        $options  = Array('fields'=>Array('title'),'where'=>'and content_type_id='.$oContType->field('content_type_id'),'limit'=>'20', 'start'=>'0', 'order'=>'content_id desc');
        if ( Liber::requestedMethod() == 'post' ) {
            $aData['list'] = $oContent->search(Input::post('search'), $options);
        } else {
            $aData['list'] = $oContent->search('', $options);
        }

        $aData['content_type_id'] = $oContType->field('content_type_id');
        $aData['content_type'] = $oContType->field('description');
        $aData['search']       = Input::post('search');
        $aData['action']       = url_to_('/admin/content/search', true);
        $aData['url_operation']= url_to_('/admin/content', true);
		$aData['token']		   = $oSec->token();
        $this->view()->load('admin/content_search.html', $aData);
    }

}

?>