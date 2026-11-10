<?
class CP_Admin_Modules_Tradingsg_project_Functions 
{
    //==================================================================//
   function setModuleArray($modules){

        /*$modObj = $modules->getModuleObj('tradingsg_project');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new')
           ,'actBtnsDetail' => array('edit', 'delete', 'duplicate')
           ,'actBtnsEdit'   => array('save', 'apply', 'delete')
           ,'relatedTables' => array('media')
        ));
    }*/
	$cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('tradingsg_project');
        $modules->registerModule($modObj, array(
            'hasMultiLang'  => 1
           ,'hasFlagInList' => 0
           ,'actBtnsList'   => array('new')
           ,'actBtnsDetail' => array('edit', 'delete', 'duplicate')
           ,'actBtnsEdit'   => array('save', 'apply', 'delete')
		   ,'relatedTables' => array('media')
        ));
    }


    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('tradingsg_project', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
	
   /* function setLinksArray($inst) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $linkObj = $inst->getLinksArrayObj('tradingsg_project', 'tradingsg_tasks');

        $inst->registerLinksArray($linkObj, array(
             'historyTableName'      => 'tasks'
           // ,'displayTitleFieldName' => "CONCAT_WS(' ', a.first_name, a.last_name)"
            ,'showLinkPanelInNew'    => 0
            ,'showLinkPanelInEdit'   => 1
            ,'linkingType'           => 'portal'
            ,'hasPortalEdit'         => 1
            ,'hasPortalDelete'       => 1
            ,'portalDialogWidth'     => 700
            ,'portalDialogHeight'    => 500
           // ,'anchorFieldsArr'       => array(
                // 'first_name' => $inst->getLinkAnchorObj('first_name', 'contact_id')
               // ,'last_name' => $inst->getLinkAnchorObj('last_name', 'contact_id'))
            ,'fieldlabel' => array(
                 'task1'
                ,'date'
                ,'status_filter'
                ,'description.'
                
            )
        ));

    }
    /**
     *
     */
   
  
}