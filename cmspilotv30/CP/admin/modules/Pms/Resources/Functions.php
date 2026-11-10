<?
class CP_Admin_Modules_Pms_Resources_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('pms_resources');
        $modules->registerModule($modObj, array(
        ));
    }

    /**
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('pms_resources', 'pms_contactLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'resources_contact'
           ,'displayTitleFieldName' => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'linkingType'           => 'portal'
           ,'hasPortalEdit'         => 1
           ,'hasPortalDelete'       => 1
           ,'portalDialogWidth'     => 700
           ,'portalDialogHeight'    => 500
           ,'anchorFieldsArr'       => false
           ,'fieldlabel'            => array('Student name'
                                            , 'From Date'
                                            , 'To Date'
                                            , 'Status'
                                       )
        ));
    }
}