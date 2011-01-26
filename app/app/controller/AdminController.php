<?php

/**
 * AdminController
 *
 */
class AdminController extends Controller{

    var $oTPL;

    function __construct($p) {
        parent::__construct($p);

        Liber::loadHelper(Array('Url', 'HTML'));

        $this->oTPL = $this->view()->template();
        $this->oTPL->model('admin.html');
    }


    public function index(){
        list($oContent, $oContType) = Liber::loadModel(Array('Content', 'ContentType'), true);
        $aTotalContents = $oContent->getTotalByContentType();
        $aContType      = $oContType->search('');

        /*
         build array of content_type_id indicating if should have to show  one or two menus.
         format: Array(
                    description => 'description text',
                    type        => 1 or 2
                    content_id  => integer // if only one content
                  )
        */
        $aMenu = Array();
        foreach ( $aContType as $record ) {
            $menu = Array(
                        'description'   =>  $record['description'],
                        'type'          =>  isset($aTotalContents[$record['content_type_id']])?($aTotalContents[$record['content_type_id']]>1?2:1):1
                    );
            if ( $menu['type'] == 1 ) {
                $aContent = current( $oContent->searchBy('content_type_id', $record['content_type_id']) );
                $menu     = array_merge($menu, Array('content_id'=>$aContent['content_id']));
            }
            $aMenu[$record['content_type_id']] = $menu;
        }

        $aData['content_menu'] = &$aMenu;
		$this->oTPL->load('admin_home.html', $aData);
    }


}

?>