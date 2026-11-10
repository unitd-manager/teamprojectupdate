<?
class CP_Admin_Modules_Project_Project_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('project_project');
        $modules->registerModule($modObj, array(
            'actBtnsList'      => array('new', 'search', 'export', 'printListPDF', 'reportsMenu')
           ,'actBtnsDetail'    => array('edit', 'duplicateProject', 'raiseInvoice', 'delete')
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

        $repInst->setReportArrayObj('project_project', 'projectList');
        $arr = &$repInst->reportsArray['project_project']['projectList'];
        $arr['jasperFileName'] = 'project_list.jasper';
        $arr['sendRecIds']     = true;
        $arr['sendSortOrder']  = true;
        $arr['outputFileName'] = $cpCfg['cp.companyName'] . '-Projects-' . date('Ymd');

        $repInst->setReportArrayObj('project_project', 'projectSummaryList');
        $arr = &$repInst->reportsArray['project_project']['projectSummaryList'];
        $arr['jasperFileName'] = 'project_summary_list.jasper';
        $arr['sendRecIds']     = true;
        $arr['sendSortOrder']  = true;
        $arr['outputFileName'] = $cpCfg['cp.companyName'] . '-Project-Summary-' . date('Ymd');
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('project_project', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
            'openExpanded' => 0
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('project_project', 'core_staffLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'project_staff'
           ,'displayTitleFieldName'  => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'fieldlabel'             => array('Name', 'Team', 'Type')
           ,'showAnchorInLinkPortal' => 0
           ,'openExpanded'           => 0
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('project_project', 'project_taskLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'    => 'task'
           ,'linkingType'         => 'portal'
           ,'showLinkPanelInNew'  => 0
           ,'showLinkPanelInEdit' => 1
           ,'hasPortalEdit'       => 1
           ,'hasPortalDelete'     => 1
           ,'fieldlabel'          => array('Title', 'Staff Linked', 'Status', 'Due Date', 'Est.', 'Used')
           ,'portalDialogWidth'  => 600
           ,'portalDialogHeight' => 600
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('project_project', 'project_scheduleLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'schedule'
           ,'linkingType'           => 'portal'
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'hasPortalEdit'         => 1
           ,'hasPortalDelete'       => 1
           ,'fieldlabel'            => array('Title', 'Start Date', 'End Date')
           ,'openExpanded'           => 0
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('project_project', 'project_invoiceLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'invoice'
           ,'linkingType'           => 'portal'
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 0
           ,'showLinkPanelInDetail' => 1
           ,'hasPortalEdit'         => 0
           ,'fieldlabel'            => array('Invoice Sequence', 'Invoice Amount', 'Status', 'Invoice Date', 'Due Date', 'Paid Date')
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('project_project', 'project_costingLink');

        $inst->registerLinksArray($linkObj, array(
             'historyTableName'    => 'costing'
            ,'linkingType'         => 'grid'
            ,'showLinkPanelInNew'  => 0
            ,'showLinkPanelInEdit' => 1
            ,'hasPortalEdit'       => 0
            ,'hasPortalDelete'     => 1
            ,'fieldlabel'          => array('Section', 'Category', 'Title', 'Sort', 'Hours')
            ,'fieldClassArray'     => array('w100', 'w100', '', 'w50 txtCenter', 'w50 txtRight')
            ,'gridFieldTypeArray'  => array(
            )
            ,'showAnchorInLinkPortal' => false
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('project_project', 'project_thirdPartyCostLink');

        $fields = array('Item Title', 'Budget Amount', 'Actual Amount');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'third_party_cost'
           ,'keyField'              => 'third_party_cost_id'
           ,'linkingType'           => 'portal'
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'hasPortalEdit'         => 1
           ,'hasPortalDelete'       => 1
           ,'fieldlabel'            => $fields
        ));

        //-------------------------- extra ---------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('project_project', 'project_companyLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'project'
           ,'displayTitleFieldName' => 'a.company_name'
           ,'linkMultiple'          => 0
        ));


        //-------------------------- extra  --------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('project_project', 'project_contactLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'project'
           ,'displayTitleFieldName' => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'linkMultiple'          => 0
        ));

        //-------------------------- extra ----------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('project_project', 'project_serviceLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'project_service'
        ));

    }

    /**
     *
     */
    function beforeDeleteHandler($project_id){
    }
}