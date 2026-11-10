<?
class CP_Admin_Modules_Project_Opportunity_Functions
{
    //==================================================================//
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('project_opportunity');
        $modules->registerModule($modObj, array(
            'actBtnsList'      => array('new', 'search', 'export', 'printListPDF')
           ,'actBtnsDetail'    => array('edit', 'convertOppToProject', 'delete')
           ,'actBtnsEdit'      => array('save', 'apply', 'cancel', 'delete')
           ,'relatedTables'    => array('media')
           ,'depModulesForJSS' => array('project_quote')
        ));
    }

    /**
     *
     */
    function setReportsArray($repInst) {
        $cpCfg = Zend_Registry::get('cpCfg');

        $repInst->setReportArrayObj('project_opportunity', "opportunityList");
        $arr = &$repInst->reportsArray['project_opportunity']['opportunityList'];
        $arr['jasperFileName'] = 'opportunity_list.jasper';
        $arr['sendRecIds']     = true;
        $arr['sendSortOrder']  = true;
        $arr['outputFileName'] = $cpCfg['cp.companyName'] . '-Opportunities-' . date('Ymd');
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('project_opportunity', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('project_opportunity', 'core_staffLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'opportunity_staff'
           ,'displayTitleFieldName'  => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'fieldlabel'             => array('Name', 'Team', 'Type')
           ,'showAnchorInLinkPortal' => 0
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('project_opportunity', 'project_taskLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'    => 'task'
           ,'linkingType'        => 'portal'
           ,'fieldlabel'         => array('Title', 'Staff Linked', 'Status', 'Due Date', 'Est.', 'Used')
           ,'hasPortalEdit'      => 1
           ,'hasPortalDelete'    => 1
           ,'portalDialogWidth'  => 600
           ,'portalDialogHeight' => 600
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('project_opportunity', 'project_companyLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'opportunity'
           ,'linkMultiple'          => 0
        ));

    }
}