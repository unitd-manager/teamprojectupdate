<?
class CP_Admin_Modules_Payroll_Training_Functions {

    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('payroll_training');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new')
           ,'actBtnsDetail' => array('edit')
           ,'relatedTables' => array('media')
           ,'actBtnsEdit'   => array('save', 'apply')
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $linkObj = $inst->getLinksArrayObj('payroll_training', 'payroll_employeeLink');

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
                ,'Position'
            )
        ));

    }


    /**
     *
     */
    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('payroll_training', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}