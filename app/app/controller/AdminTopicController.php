<?php

/**
 *
 *
 */
class AdminTopicController extends Controller {

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

    /* Show Topic form */
    public function edit() {
        Liber::loadHelper('Util', 'APP');
		$oSec = Liber::loadClass('Security', true);
        $oContType = Liber::loadModel('ContentType', true);

        if ( Http::post() ) {
			if ( $oSec->validToken(Http::post('token')) ) {
                $oContType->field('content_type_id',  Http::post('content_type_id') );
                $oContType->field('description',  Http::post('description') );
                $oContType->field('status',  Http::post('status') );
				if ( $oContType->save() ) {
					cleancache();

					die( jsonout('ok', Array('text'=>'Document saved at '.date('H:i:s'), 'content_type_id'=>$oContType->field('content_type_id'))) );
				} else {
					Liber::log()->add('Document can\'t be saved.','error');
					die( jsonout('error', implode($oContType->buildFriendlyErrorMsg()) ) ) ;
				}
			}
			die( jsonout('error', 'Please reload this page.' ) ) ;
        }

        // new content
        if ( !Http::get('id') ) {
            $aData['content_type_id'] = '';
			$aData['tab'] = 'New Topic';
        } else {
			$oContType->get( Http::get('id') );
			$aData['tab'] = 'Editing '.$oContType->field('description');
            $aData['content_type_id'] = $oContType->field('content_type_id');
        }

        $aData['topic']   = $oContType->toArray();
		$aData['status']  = $oContType->status();
        $aData['action']  = url_to_('/admin/topic/edit', true);
		$aData['token']   = $oSec->token();
        $this->view()->load('admin/topic_form.html', $aData);
    }

    /* delete a topic, only by POST method */
    public function delete() {
        Liber::loadHelper('Util', 'APP');
		$oSec = Liber::loadClass('Security', true);
        if ( Http::post() ) {
			if ( $oSec->validToken( Http::post('token') ) ) {
				$oContType = Liber::loadModel('ContentType', true);
				if ( $oContType->delete( Http::post('content_type_id') ) ) {
					Liber::loadClass('SiteMapCache', 'APP', true)->cleanCache();
					die( jsonout('ok', 'Document deleted successfully.' ) ) ;
				} else {
					die( jsonout('error', implode($oContType->buildFriendlyErrorMsg()) ) ) ;
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
        $oContType = Liber::loadModel('ContentType', true);

        $options  = Array('fields'=>Array('description'),'limit'=>'20', 'start'=>'0', 'order'=>'content_type_id desc');
        if ( Http::post() ) {
            $aData['list'] = $oContType->search(Http::post('search'), $options);
        } else {
            $aData['list'] = $oContType->search('', $options);
        }
		$aData['status']	   = $oContType->status();
        $aData['search']       = Http::post('search');
        $aData['action']       = url_to_('/admin/topic/search', true);
        $aData['url_operation']= url_to_('/admin/topic', true);
		$aData['token']		   = $oSec->token();
        $this->view()->load('admin/topic_search.html', $aData);
    }

}

?>