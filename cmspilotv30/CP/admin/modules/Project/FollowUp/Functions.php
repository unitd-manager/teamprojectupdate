<?
class CP_Admin_Modules_Project_FollowUp_Functions {

    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('project_followUp');
        $modObj['tableName'] = 'opportunity';
        $modObj['keyField']  = 'opportunity_id';
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array()
           //,'actBtnsDetail' => array('edit', 'delete')
           //,'actBtnsEdit'   => array('save', 'apply', 'delete')
           //,'relatedTables' => array('media')
          // ,'titleField'    => 'appointment_id'
           ,'title'         => 'Follow Up'
        ));
    }

    /**
     *
     */

    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('project_followUp', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    /**
     *
     */

    function setLinksArray($inst) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $linkObj = $inst->getLinksArrayObj('project_followUp', 'project_commentLink');


        $inst->registerLinksArray($linkObj, array(
             'historyTableName'      => 'comment'
            ,'historyTableKeyField'  => 'comment_id'
            ,'showLinkPanelInNew'    => 0
            ,'showLinkPanelInEdit'   => 1
            ,'linkingType'           => 'portal'
            ,'hasPortalEdit'         => 1
            ,'hasPortalDelete'       => 1
            ,'portalDialogWidth'     => 700
            ,'portalDialogHeight'    => 500
            ,'fieldlabel' => array(
                 'Notes'
            )
        ));

    }

}
