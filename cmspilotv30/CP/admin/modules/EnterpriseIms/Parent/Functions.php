<?
class CP_Admin_Modules_EnterpriseIms_Parent_Functions extends CP_Common_Modules_EnterpriseIms_Parent_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('enterpriseIms_parent');
        $modules->registerModule($modObj, array(
            'relatedTables' => array('media')
           ,'actBtnsList'   => array('new', 'parentExportAddress', 'parentExportMobile')
           ,'hasMultiLang'  => 1
           ,'titleField'    => "CONCAT_WS(' ', first_name, last_name)"
        ));
    }

    /**
     *
     */
    function setActionsArray($actArrayObj){
        $cpCfg = Zend_Registry::get('cpCfg');
        $arrayMaster = Zend_Registry::get('arrayMaster');
        $pager = Zend_Registry::get('pager');
        $cpUrl = Zend_Registry::get('cpUrl');
        $tv = Zend_Registry::get('tv');
        
        $searchQueryString = $pager->searchQueryString;
        $searchQueryString = preg_replace('/&_action=[a-zA-Z0-9\. _,]+&?/', "&", $searchQueryString);
        if (substr($searchQueryString, -1) == "&") {
            $searchQueryString = substr($searchQueryString, 0, strlen($searchQueryString) - 1);
        }

        $searchQueryString .= $cpUrl->getQnMarkForUrl($searchQueryString);

        //=============== Print Address =================//
        $actObj = $actArrayObj->getActionObj('parentExportAddress');
        $actArrayObj->registerAction($actObj, array(
            'title' => 'Print Address'
           ,'url' => "{$searchQueryString}&_spAction=exportData&showHTML=0&export=1&hasDB=1"
        ));

        //=============== Print Mobile No =================//
        $actObj = $actArrayObj->getActionObj('parentExportMobile');
        $actArrayObj->registerAction($actObj, array(
            'title' => 'Print Mobile No'
           ,'url' => "{$searchQueryString}&_spAction=exportData&showHTML=0&export=1&hasDB=1&exportType=printMobileNo"
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('enterpriseIms_parent', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('enterpriseIms_parent', 'relatedPicture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
    
    /**
     *
     */
    function setLinksArray($inst) {
        $fn = Zend_Registry::get('fn');

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('enterpriseIms_parent', 'enterpriseIms_contactLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'parent_contact'
           ,'displayTitleFieldName' => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'anchorFieldsArr'       => array(
                 'first_name' => $inst->getLinkAnchorObj('first_name', 'contact_id'))
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'linkingType'           => 'portal'
           ,'hasPortalEdit'         => 1
           ,'hasPortalDelete'       => 1
           ,'portalDialogWidth'     => 700
           ,'portalDialogHeight'    => 500
           ,'fieldlabel'            => array( 'Name'
                                            , 'Date Of Birth'
                                            , 'Age'
                                            , 'Gender'
                                            , 'Reg No'
                                       )
            ));
		}
}