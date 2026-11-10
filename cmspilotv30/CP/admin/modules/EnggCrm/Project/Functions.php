<?
class CP_Admin_Modules_EnggCrm_Project_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('enggCrm_project');
        $modules->registerModule($modObj, array(
            'actBtnsList'      => array('new')
           ,'actBtnsDetail'    => array('edit', 'duplicateProject')
           ,'actBtnsEdit'      => array('save', 'apply', 'cancel')
           ,'relatedTables'    => array('media')
        ));
    }

    /**
     *
     */
    function setReportsArray($repInst) {
        $cpCfg = Zend_Registry::get('cpCfg');

        $repInst->setReportArrayObj('enggCrm_project', 'projectList');
        $arr = &$repInst->reportsArray['enggCrm_project']['projectList'];
        $arr['jasperFileName'] = 'project_list.jasper';
        $arr['sendRecIds']     = true;
        $arr['sendSortOrder']  = true;
        $arr['outputFileName'] = $cpCfg['cp.companyName'] . '-Projects-' . date('Ymd');

        $repInst->setReportArrayObj('enggCrm_project', 'projectSummaryList');
        $arr = &$repInst->reportsArray['enggCrm_project']['projectSummaryList'];
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
        $mediaObj = $mediaArr->getMediaObj('enggCrm_project', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
            'openExpanded' => 1
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('enggCrm_project', 'core_staffLink');
        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'project_staff'
           ,'displayTitleFieldName'  => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'fieldlabel'             => array('Name', 'Team', 'Type')
           ,'showAnchorInLinkPortal' => 0
           ,'openExpanded'           => 0
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('enggCrm_project', 'enggCrm_employeeLink');
        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'project_employee'
           ,'displayTitleFieldName'  => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'fieldlabel'             => array('Name', 'Part Time / Full Time', 'Basic Salary')
           ,'showAnchorInLinkPortal' => 0
           ,'beforeCloseFnName' => "cpm.enggCrm.project.employeeLinkCallBack"
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('enggCrm_project', 'enggCrm_scheduleLink');
        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'schedule'
           ,'linkingType'           => 'portal'
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'hasPortalEdit'         => 1
           ,'hasPortalDelete'       => 1
           ,'fieldlabel'            => array('Title', 'Start Date', 'End Date')
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('enggCrm_project', 'enggCrm_invoiceLink');
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
        $linkObj = $inst->getLinksArrayObj('enggCrm_project', 'enggCrm_costingLink');
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
        $linkObj = $inst->getLinksArrayObj('enggCrm_project', 'enggCrm_thirdPartyCostLink');

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
        $linkObj = $inst->getLinksArrayObj('enggCrm_project', 'enggCrm_companyLink');
        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'project'
           ,'displayTitleFieldName' => 'a.company_name'
           ,'linkMultiple'          => 0
        ));


        //-------------------------- extra  --------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('enggCrm_project', 'enggCrm_contactLink');
        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'project'
           ,'displayTitleFieldName' => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'linkMultiple'          => 0
        ));

        //-------------------------- extra ----------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('enggCrm_project', 'enggCrm_serviceLink');
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