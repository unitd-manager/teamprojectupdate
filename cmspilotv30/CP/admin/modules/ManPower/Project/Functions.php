<?
class CP_Admin_Modules_ManPower_Project_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('manPower_project');
        $modules->registerModule($modObj, array(
            'actBtnsList'      => array()
           ,'actBtnsDetail'    => array('edit', 'delete')
           ,'actBtnsEdit'      => array('save', 'apply', 'delete')
           ,'relatedTables'    => array('media')
           ,'depModulesForJSS' => array('project_quote')
        ));
    }

    /**
     *
     */
    function setReportsArray($repInst) {
        $cpCfg = Zend_Registry::get('cpCfg');

        $repInst->setReportArrayObj('project', "projectList");
        $arr = &$repInst->reportsArray['project']['projectList'];
        $arr['jasperFileName'] = 'project_list.jasper';
        $arr['sendRecIds']     = true;
        $arr['sendSortOrder']  = true;
        $arr['outputFileName'] = $cpCfg['cp.companyName'] . '-Projects-' . date('Ymd');

        $repInst->setReportArrayObj('project', "projectSummaryList");
        $arr = &$repInst->reportsArray['project']['projectSummaryList'];
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
        $mediaObj = $mediaArr->getMediaObj('manPower_project', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
            'openExpanded' => 0
        ));

       //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_project', 'attachment1', 'attachment1');

        $mediaArr->registerMedia($mediaObj, array(
        ));

       //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_project', 'attachment2', 'attachment2');

        $mediaArr->registerMedia($mediaObj, array(
        ));

       //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_project', 'attachment3', 'attachment3');

        $mediaArr->registerMedia($mediaObj, array(
        ));

       //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_project', 'attachment4', 'attachment4');

        $mediaArr->registerMedia($mediaObj, array(
        ));

       //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_project', 'attachment5', 'attachment5');

        $mediaArr->registerMedia($mediaObj, array(
        ));

       //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_project', 'attachment1', 'attachment1');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('manPower_project', 'core_staffLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'project_staff'
           ,'displayTitleFieldName'  => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'fieldlabel'             => array('Name', 'Team', 'Type')
           ,'showAnchorInLinkPortal' => 0
           ,'openExpanded'           => 0
           ,'anchorFieldsArr'       => array(
                'title' => $inst->getLinkAnchorObj(
                     'title'
                    ,'staff_id'
                    ,false
                    ,''
                    ,array('showLinkInEdit' => true)
                )

           )
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('manPower_project', 'manPower_taskLink');

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
           ,'anchorFieldsArr'    => array(
                'title' => $inst->getLinkAnchorObj(
                     'title'
                    ,'task_id'
                    ,false
                    ,''
                    ,array('showLinkInEdit' => true)
                )

           )
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('manPower_project', 'project_scheduleLink');

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
        $linkObj = $inst->getLinksArrayObj('manPower_project', 'manPower_invoiceLink');

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
        $linkObj = $inst->getLinksArrayObj('manPower_project', 'project_costingLink');

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
        $linkObj = $inst->getLinksArrayObj('manPower_project', 'project_thirdPartyCostLink');

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
        $linkObj = $inst->getLinksArrayObj('manPower_project', 'project_companyLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'project'
           ,'displayTitleFieldName' => 'a.company_name'
           ,'linkMultiple'          => 0
        ));

        //-------------------------- extra  --------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('manPower_project', 'project_contactLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'project'
           ,'displayTitleFieldName' => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'linkMultiple'          => 0
        ));

        //-------------------------- extra ----------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('manPower_project', 'project_serviceLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'project_service'
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('manPower_project', 'manPower_candidateLink');
        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'project_candidate'
           ,'historyTableKeyField'  => 'project_candidate_id'
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'hasPortalEdit' => 0
           ,'hasPortalNew'=> 0
           ,'linkingType'           => 'portal'
           ,'anchorFieldsArr'       => array(
                'candidate_name' => $inst->getLinkAnchorObj(
                     'candidate_name'
                    ,'candidate_id'
                    ,false
                    ,''
                    ,array('showLinkInEdit' => true)
                )
           )
           ,'hideFieldsArray'       => array('candidate_id')
           ,'fieldlabel'            => array('Candidate name')
        ));

        //-------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('manPower_project', 'manPower_expenseLink', array(
            'historyTableName'      => 'expense'
           ,'historyTableKeyField'  => 'expense_id'
           ,'hasPortalEdit'         => 0
           ,'hasPortalDelete'       => 1
           ,'linkingType'           => 'portal'
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'hasPortalEdit'         => 1
           ,'hasPortalDelete'       => 1
           ,'portalDialogWidth'     => 650
           ,'portalDialogHeight'    => 350
           ,'fieldlabel'            => array('Date', 'Description', 'Amount')
           , 'fieldClassArray' => array(
                 0 => ''
               , 1 => ''
               , 2 => 'al-right'
           )
           ,'anchorFieldsArr'       => array(
                'date' => $inst->getLinkAnchorObj(
                     'date'
                    ,'expense_id'
                    ,false
                    ,''
                    ,array('showLinkInEdit' => true)
                )

           )
        ));        
        $inst->registerLinksArray($linkObj);

    }

    /**
     *
     */
    function beforeDeleteHandler($project_id){
    }
}