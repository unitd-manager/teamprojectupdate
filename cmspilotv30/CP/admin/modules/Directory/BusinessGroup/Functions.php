<?
class CP_Admin_Modules_Directory_BusinessGroup_Functions
      extends CP_Common_Modules_Directory_BusinessGroup_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('directory_businessGroup');
        $modules->registerModule($modObj, array(
            'tableName' => 'business_group'
           ,'keyField'  => 'business_group_id'
           ,'hasFlagInList' => 0
           ,'title'  => 'Multiple'
           ,'actBtnsList' => array('new', 'export')
           ,'actBtnsDetail' => array('edit', 'delete', 'import', 'updateBusinesses')
        ));
    }

	function setLinksArray($inst) {
        $fn = Zend_Registry::get('fn');

        $linkObj = $inst->getLinksArrayObj('directory_businessGroup', 'directory_businessLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'business'
           ,'fieldlabel' => array('Source Id', 'Email')
           ,'hasModalChoose' => false
        ));

        //-----------------------//
        $linkObj = $inst->getLinksArrayObj('directory_businessGroup', 'directory_socialMediaLink');
        $extSourceArr = $fn->getDdDataAsArray('directory_socialMedia');

        $inst->registerLinksArray($linkObj, array(
             'historyTableName' => 'bg_social_media'
            ,'linkingType' => 'grid'
            ,'historyTableKeyField' => 'bg_social_media_id'
            ,'showLinkPanelInEdit' => 1
            ,'hasPortalEdit' => 0
            ,'hasPortalDelete' => 1
            ,'fieldlabel' => array('Title', 'URL')
            ,'gridFieldTypeArray' => array(
                 array('type' => 'dropdown', 'ddArr' => $extSourceArr)
                ,array('type' => 'textbox')
            )
            ,'additionalFieldsArray' => array(
                'b.url'
            )
            ,'showAnchorInLinkPortal' => false
        ));

        //-----------------------//
        $linkObj = $inst->getLinksArrayObj('directory_businessGroup', 'directory_paymentLink');
        $extSourceArr = $fn->getDdDataAsArray('directory_payment');

        $inst->registerLinksArray($linkObj, array(
             'historyTableName' => 'bg_payment'
            ,'linkingType' => 'grid'
            ,'historyTableKeyField' => 'bg_payment_id'
            ,'showLinkPanelInEdit' => 1
            ,'hasPortalEdit' => 0
            ,'hasPortalDelete' => 1
            ,'fieldlabel' => array('Title')
            ,'gridFieldTypeArray' => array(
                 array('type' => 'dropdown', 'ddArr' => $extSourceArr)
                ,array('type' => 'textbox')
            )
            ,'additionalFieldsArray' => array(
            )
            ,'showAnchorInLinkPortal' => false
        ));
    }

}