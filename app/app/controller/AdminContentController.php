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

        // saving content
        if ( Http::post() ) {
			if ( $oSec->validToken(Http::post('token')) ) {
				$oContent->field('content_id', Http::post('content_id'));
                $oContent->field('content_type_id', Http::post('content_type_id'));
                $oContent->field('title', Http::post('title'));
                $oContent->field('body', Http::post('body'));
                $oContent->field('permalink', Http::post('permalink'));
				$oContent->field('datetime', date('Y-m-d H:i:s'));
				if ( !$oContent->field('content_id') ){
					$oContent->field('create_datetime', $oContent->field('datetime'));
				}
				if ( $oContent->save() ) {
				    $aContent = $oContent->toArray();
					$oCache   = Liber::loadClass('ContentCache', 'APP' , true)->cleanCache( $aContent );
					unset($aContent['body']);
					die( jsonout('ok', Array('text'=>'Document saved at '.date('H:i:s'), 'content' => $aContent)) );
				} else {
					Liber::log()->add('Document can\'t be saved.','error');
					die( jsonout('error', implode($oContent->buildFriendlyErrorMsg()) ) ) ;
				}
			}
			die( jsonout('error', 'Please reload this page.' ) ) ;
        }

        // new content
        if ( !Http::get('id') ) {
			$oContType->get( Http::get('content_type_id') );
            $aData['content_type_id'] = Http::get('content_type_id');
			$aData['tab'] = 'New Topic » '.$oContType->field('description');
        } else {
            $oContent->get( Http::get('id') );
			$oContType->get( $oContent->field('content_type_id') );
			$aData['tab'] = 'Editing Topic » '.$oContType->field('description');
            $aData['content_type_id'] = $oContent->field('content_type_id');
        }

        $aData['content'] = $oContent->toArray();

		// permalink field
		$aData['permalink'] = Liber::loadClass('ContentCache', 'APP', true)->url($aData['content']);
		$input = ' '.form_input_('permalink',rawurldecode($aData['content']['permalink']), '', true);
		if ( empty($aData['content']['permalink']) ) {
    		$aData['permalink'] = rawurldecode(str_replace(
							'[]',
							$input,
							dirname($aData['permalink'])."/[].html"
					));

        } else {
    		$aData['permalink'] = rawurldecode(str_replace(
							$aData['content']['permalink'],
							$input,
							$aData['permalink']
					));
        }

        $aData['action']  = url_to_('/admin/content/edit', true);
		$aData['token']   = $oSec->token();
        $this->view()->load('admin/content_editor.html', $aData);
    }

    /* delete a content, only by POST method */
    public function delete() {
        Liber::loadHelper('Util', 'APP');
		$oSec = Liber::loadClass('Security', true);
        if ( Http::post() ) {
			if ( $oSec->validToken( Http::post('token') ) ) {
				$oContent = Liber::loadModel('Content', true);
				if ( $oContent->delete( Http::post('content_id') ) ) {
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

        $oContType->get( Http::get('content_type_id')?Http::get('content_type_id'):Http::post('content_type_id') );
        $options  = Array('fields'=>Array('title'),'where'=>'and content_type_id='.$oContType->field('content_type_id'),'limit'=>'20', 'start'=>'0', 'order'=>'content_id desc');
        if ( Http::post() ) {
            $aData['list'] = $oContent->search(Http::post('search'), $options);
        } else {
            $aData['list'] = $oContent->search('', $options);
        }

        $aData['content_type_id'] = $oContType->field('content_type_id');
        $aData['content_type'] = $oContType->field('description');
        $aData['search']       = Http::post('search');
        $aData['action']       = url_to_('/admin/content/search', true);
        $aData['url_operation']= url_to_('/admin/content', true);
		$aData['token']		   = $oSec->token();
        $this->view()->load('admin/content_search.html', $aData);
    }

}

?>
