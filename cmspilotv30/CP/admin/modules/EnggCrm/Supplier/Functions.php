<?
class CP_Admin_Modules_EnggCrm_Supplier_Functions {

    /**
     *
     */
    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('enggCrm_supplier');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new', 'import')
           ,'actBtnsDetail' => array('edit', 'delete')
           ,'actBtnsEdit'   => array('save', 'apply', 'delete')
           ,'relatedTables' => array('media')
           ,'titleField'    => 'company_name'
           ,'title'         => 'Supplier'
           ,'keyField'      => 'company_id'
           ,'tableName'     => 'company'
        ));
    }

    /**
     *
     */

    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('enggCrm_supplier', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    function setLinksArray($inst) {
        $linkObj = $inst->getLinksArrayObj('enggCrm_supplier', 'enggCrm_contactLink');

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
