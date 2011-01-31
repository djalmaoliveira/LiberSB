<?php

/**
 *
 *
 */
class ContentController extends Controller {

    function __construct($p=Array()) {
        parent::__construct($p);

        Liber::loadHelper(Array('Url', 'HTML', 'Form'));
    }


    public function index() {

    }

    /* Show Content Editor */
    public function edit() {
        Liber::loadHelper('Util', 'APP');

        $oContent = Liber::loadModel('Content', true);
        if ( Liber::requestedMethod() == 'post' ) {
            $oContent->loadFrom( Input::post() );
            $oContent->field('datetime', date('Y-m-d H:i:s'));
            if ( $oContent->save() ) {
                $oCache = Liber::loadClass('ContentCache', 'APP' , true)->cleanCache($oContent->toArray());
                die( jsonout('ok', Array('text'=>'Documento salvo às '.date('H:i:s'), 'content_id'=>$oContent->field('content_id'))) );
            } else {
                Liber::log()->add('Context não foi salvo.','error');
                die( jsonout('error', implode($oContent->buildFriendlyErrorMsg()) ) ) ;
            }
        }

        // new content
        if ( Input::get('content_type_id') ) {
            $aData['content_type_id'] = Input::get('content_type_id');
        } else {
            $oContent->get( Input::get('id') );
            $aData['content_type_id'] = $oContent->field('content_type_id');
        }

        $aData['content'] = $oContent->toArray();
        $aData['action']  = url_to_('/admin/content/edit', true);
        $this->view()->load('content_editor.html', $aData);
    }

    /* delete a content, only by POST method */
    public function delete() {
        Liber::loadHelper('Util', 'APP');
        if ( Liber::requestedMethod() == 'post' ) {
            $oContent = Liber::loadModel('Content', true);
            if ( $oContent->delete( Input::post('content_id') ) ) {
                die( jsonout('ok', 'Conteúdo apagado com sucesso.' ) ) ;
            } else {
                die( jsonout('error', implode($oContent->buildFriendlyErrorMsg()) ) ) ;
            }
        }

    }

    /* Search or Show form search. */
    public function search() {
        Liber::loadHelper('DT');
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
        $this->view()->load('content_search.html', $aData);
    }

}

?>