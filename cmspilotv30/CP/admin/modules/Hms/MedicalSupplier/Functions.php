<?
class CP_Admin_Modules_Hms_MedicalSupplier_Functions {

    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('hms_medicalSupplier');
        $modObj['tableName'] = 'medical_supplier';
        $modObj['keyField']  = 'medical_supplier_id';
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new', 'import')
           ,'actBtnsDetail' => array('edit', 'delete')
           ,'actBtnsEdit'   => array('save', 'apply', 'delete')
           ,'relatedTables' => array('media')
           ,'title'         => 'Supplier'
        ));
    }

    /**
     *
     */

    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('hms_medicalSupplier', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    function setLinksArray($inst) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $linkObj = $inst->getLinksArrayObj('hms_medicalSupplier', 'hms_contactLink');

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
                ,'Email'
                ,'Phone (Direct)'
                ,'Mobile'
                ,'Position'
                ,'Dept.'
            )
        ));

    }
}
