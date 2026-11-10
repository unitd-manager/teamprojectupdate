<?
class CP_Admin_Modules_Hms_Treatment_Functions {

    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('hms_treatment');
        $modObj['tableName'] = 'treatment';
        $modObj['keyField']  = 'treatment_id';
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new')
           ,'actBtnsDetail' => array('edit', 'delete')
           ,'actBtnsEdit'   => array('save', 'apply', 'delete')
           ,'relatedTables' => array('media')
           ,'title'         => 'Treatment'
        ));
    }

    /**
     *
     */

    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('hms_treatment', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
    /**
     *
     */

    function setLinksArray($inst) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $linkObj = $inst->getLinksArrayObj('hms_treatment', 'hms_treatment_medicine_templateLink');


        $inst->registerLinksArray($linkObj, array(
             'historyTableName'      => 'treatment_medicine_template'
            ,'historyTableKeyField'  => 'treatment_medicine_template_id'
            ,'showLinkPanelInNew'    => 0
            ,'showLinkPanelInEdit'   => 1
            ,'linkingType'           => 'portal'
            ,'hasPortalEdit'         => 1
            ,'hasPortalDelete'       => 1
            ,'portalDialogWidth'     => 700
            ,'portalDialogHeight'    => 500
            ,'fieldlabel' => array(
                 'Title'
                ,'Medicine Name'
                ,'Instruction'
                ,'No of Days'
                ,'Qty'
            )
        ));

    }


}
