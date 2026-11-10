<?
class CP_Admin_Modules_Labsg_Appointment_Functions {

    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('labsg_appointment');
        $modObj['tableName'] = 'appointment';
        $modObj['keyField']  = 'appointment_id';
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array()
           //,'actBtnsDetail' => array('edit', 'delete')
           //,'actBtnsEdit'   => array('save', 'apply', 'delete')
           //,'relatedTables' => array('media')
          // ,'titleField'    => 'appointment_id'
           ,'title'         => 'Appointments'
        ));
    }

    /**
     *
     */

    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('labsg_appointment', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    function setLinksArray($inst) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $linkObj = $inst->getLinksArrayObj('labsg_appointment', 'labsg_contactLink');

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
                 'Name'
                ,'NRIC'
                ,'Email'
                ,'Mobile'
            )
        ));

    }
}
