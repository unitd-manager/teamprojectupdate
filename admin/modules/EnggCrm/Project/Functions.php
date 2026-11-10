<?
class CPL_Admin_Modules_EnggCrm_Project_Functions Extends CP_Admin_Modules_EnggCrm_Project_Functions
{

    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('enggCrm_project');
        $modules->registerModule($modObj, array(
            'actBtnsList'      => array()
             ,'tableName' => 'project'
           ,'keyField' => 'project_id'
           ,'actBtnsDetail'    => array('edit')
           ,'actBtnsEdit'      => array('save', 'apply')
           ,'relatedTables'    => array('media')
           ,'title'         => 'Client Po/Contract'
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
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('enggCrm_project', 'enggCrm_employeeLink');
        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'project_employee'
           ,'portalListLimit'        => '5'
           ,'displayTitleFieldName'  => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'fieldlabel'             => array('Name', 'Status')
           ,'showAnchorInLinkPortal' => 0
           ,'beforeCloseFnName'      => "cpm.enggCrm.project.employeeLinkCallBack"
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
    function setMediaArray($mediaArr) {
      //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('enggCrm_project', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
            'openExpanded' => 1
        ));
        
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('enggCrm_project', 'claimAttachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
            'openExpanded' => 1
        ));
    }
}