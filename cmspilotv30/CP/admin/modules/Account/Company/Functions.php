<?
class CP_Admin_Modules_Account_Company_Functions {

    /**
     *
     */
    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('account_company');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new', 'search', 'export')
           ,'actBtnsDetail' => array('edit')
           ,'relatedTables' => array('media')
           ,'titleField'    => 'company_name'
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $linkObj = $inst->getLinksArrayObj('account_company', 'common_contactLink');

        $inst->registerLinksArray($linkObj, array(
             'historyTableName'      => 'contact'
            ,'displayTitleFieldName' => "CONCAT_WS(' ', a.first_name, a.last_name)"
            ,'showLinkPanelInNew'    => 0
            ,'showLinkPanelInEdit'   => 1
            ,'linkingType'           => 'portal'
            ,'hasPortalEdit'         => 1
            ,'hasPortalDelete'       => 1
            ,'portalDialogWidth'     => 700
            ,'portalDialogHeight'    => 500
            ,'anchorFieldsArr'       => array(
                 'first_name' => $inst->getLinkAnchorObj('first_name', 'contact_id')
                ,'last_name' => $inst->getLinkAnchorObj('last_name', 'contact_id'))
            ,'fieldlabel' => array(
                 'First name'
                ,'Last name'
                ,'Email'
                ,'Phone (Direct)'
                ,'Mobile'
                ,'Position'
                ,'Dept.'
            )
        ));

    }


    /**
     *
     */
    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('account_company', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}