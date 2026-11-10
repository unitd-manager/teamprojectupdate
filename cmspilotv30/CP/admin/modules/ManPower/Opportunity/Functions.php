<?
class CP_Admin_Modules_ManPower_Opportunity_Functions
{
    //==================================================================//
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('manPower_opportunity');        
        $modules->registerModule($modObj, array(
            'actBtnsList'      => array('new')
           ,'actBtnsDetail'    => array('edit', 'duplicate', 'convertOppToProject', 'delete')
           ,'actBtnsEdit'      => array('save', 'apply', 'delete')
           ,'relatedTables'    => array('media')
           ,'depModulesForJSS' => array('project_quote')
        ));
    }

    /**
     *
     */
    function setActionsArray($actArrayObj){
        $cpCfg = Zend_Registry::get('cpCfg');
        $arrayMaster = Zend_Registry::get('arrayMaster');
        $tv = Zend_Registry::get('tv');
        
        //=============== Convert to Opportunity =================//
        $actObj = $actArrayObj->getActionObj('convertOppToProject');
        $actArrayObj->registerAction($actObj, array(
            'title' => 'Convert to Project'
        ));

        //=============== Duplicate Opportunity =================//
        $actObj = $actArrayObj->getActionObj('duplicate');
        $actArrayObj->registerAction($actObj, array(
            'title' => 'Duplicate'
           ,'url' => "javascript:Opportunity.duplicateOpportunity('{$tv['topRm']}');"
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
        $mediaObj = $mediaArr->getMediaObj('manPower_opportunity', 'attachment', 'attachment');

            //'count' => 'single'
        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_opportunity', 'attachment1', 'attachment1');

        $mediaArr->registerMedia($mediaObj, array(
        ));

    }

    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('manPower_opportunity', 'manPower_staffLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'opportunity_staff'
           ,'displayTitleFieldName'  => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'fieldlabel'             => array('Name', 'Team', 'Type')
           ,'showAnchorInLinkPortal' => 0
           ,'anchorFieldsArr'        => array(
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
        $linkObj = $inst->getLinksArrayObj('manPower_opportunity', 'manPower_companyLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'opportunity'
           ,'linkMultiple'          => 0
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('manPower_opportunity', 'manPower_candidateLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'opportunity_candidate'
           ,'historyTableKeyField'  => 'opportunity_candidate_id'
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'linkingType'           => 'portal'
           ,'hasPortalEdit'         => 1
           ,'hasPortalDelete'       => 1
           ,'portalDialogWidth'     => 700
           ,'portalDialogHeight'    => 500
           ,'anchorFieldsArr'       => array(
                 'agent_name' => $inst->getLinkAnchorObj('agent_name', 'agent_id', '', 'manPower_agent')
                ,'candidate_name' => $inst->getLinkAnchorObj(
                     'candidate_name'
                    ,'candidate_id'
                    ,false
                    ,''
                    ,array('showLinkInEdit' => true)
                )
           )
           ,'hideFieldsArray'       => array('agent_id', 'candidate_id')
           ,'fieldlabel'            => array('Candidate name'
                                            , 'Process Status'
                                            , 'Response Status'
                                            , 'Percent'
                                            , 'Agent Code'
                                            , ''
                                            , ''
                                            , 'Resume'
                                       )
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('manPower_opportunity', 'manPower_taskLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'    => 'task'
           ,'linkingType'         => 'portal'
           ,'portalListLimit' => 15
           ,'showLinkPanelInNew'  => 0
           ,'showLinkPanelInEdit' => 1
           ,'hasPortalEdit'       => 1
           ,'hasPortalDelete'     => 1
           ,'fieldlabel'          => array('Title', 'Staff Linked', 'Status', 'From Date', 'Due Date')
           ,'portalDialogWidth'  => 600
           ,'portalDialogHeight' => 600
           ,'anchorFieldsArr'       => array(
                'title' => $inst->getLinkAnchorObj(
                     'title'
                    ,'task_id'
                    ,false
                    ,''
                    ,array('showLinkInEdit' => true)
                )

           )
        ));

        //-------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('manPower_opportunity', 'manPower_expenseLink', array(
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
           ,'fieldlabel'          => array('Date', 'Description', 'Amount')
           ,'anchorFieldsArr'       => array(
                'date' => $inst->getLinkAnchorObj(
                     'date'
                    ,'expense_id'
                    ,false
                    ,''
                    ,array('showLinkInEdit' => true)
                )

           )
           , 'fieldClassArray' => array(
                 0 => ''
               , 1 => ''
               , 2 => 'al-right'
           )
        ));
        $inst->registerLinksArray($linkObj);
    }
}