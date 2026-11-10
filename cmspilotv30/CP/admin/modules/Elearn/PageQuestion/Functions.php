<?
class CP_Admin_Modules_ELearn_PageQuestion_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('elearn_pageQuestion');
        $modObj['tableName'] = 'page_question';
        $modObj['keyField']  = 'page_question_id';
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
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
        $linkObj = $inst->getLinksArrayObj('elearn_pageQuestion', 'elearn_questionAnswerLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'question_answer'
           ,'linkRoomTableName'     => 'question_answer'
           ,'keyField'              => 'question_answer_id'
           ,'displayTitleFieldName' => 'a.answer'
           ,'showLinkPanelInEdit'   => 1
           ,'linkingType'           => 'portal'
           ,'hasPortalEdit'         => 1
           ,'hasPortalDelete'       => 1
           ,'portalDialogHeight'    => 265
           ,'hasPortalNew'          => 0
           ,'showAnchorInLinkPortal'=> 0
        ));
    }
}
