<?
class CP_Admin_Modules_ELearn_BookPage_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('elearn_bookPage');
        $modObj['tableName'] = 'book_page';
        $modObj['keyField']  = 'book_page_id';
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
           ,'depModulesForJSS' => array('pageQuestion')
        ));
    }

    //==================================================================//
    //==================================================================//
    //==================================================================//
    //==================================================================//
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('elearn_bookPage', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('elearn_bookPage', 'audio', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    /**
     *
     * @return <type>
     */
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('elearn_bookPage', 'elearn_pageQuestionLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'page_question'
           ,'linkRoomTableName'     => 'page_question'
           ,'keyField'              => 'page_question_id'
           ,'displayTitleFieldName' => 'a.question'
           ,'showLinkPanelInEdit'   => 1
           ,'linkingType'           => 'portal'
           ,'hasPortalEdit'         => 1
           ,'hasPortalDelete'       => 1
           ,'portalDialogHeight'    => 225
           ,'hasChildren'           => true
           ,'childLinkKey'          => 'pageQuestion#questionAnswer'
           ,'showAnchorInLinkPortal'=> 0
        ));
    }


}