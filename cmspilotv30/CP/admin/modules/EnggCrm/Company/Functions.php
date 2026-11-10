<?
class CP_Admin_Modules_EnggCrm_Company_Functions {
    /**
     *
     */
    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('enggCrm_company');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new', 'export', 'import')
           ,'actBtnsDetail' => array('edit')
           ,'relatedTables' => array('media')
           ,'titleField'    => 'company_name'
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('enggCrm_company', 'enggCrm_contactLink');

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
           ,'anchorFieldsArr'       => array('first_name' => $inst->getLinkAnchorObj('first_name', 'contact_id'))
           ,'fieldlabel'            => array('Name'
                                            , 'Email'
                                            , 'Phone (Direct)'
                                            , 'Mobile'
                                            , 'Position'
                                            , 'Dept.'
                                       )
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('enggCrm_company', 'enggCrm_projectLink');

        $fieldlabel = array('Project Code', 'Title', 'Project Value', 'Status');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'project'
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 0
           ,'fieldlabel'            => $fieldlabel
        ));
        
        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('enggCrm_company', 'enggCrm_invoiceLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'invoice'
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 0
           ,'fieldlabel'            => array('Invoice Code', 'Project Code', 'Invoice Type', 'Invoice Date','Due Date', 'Inv. Amount', 'Status')
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('enggCrm_company', 'enggCrm_opportunityLink');

        $fieldlabel = array('Opportunity Code', 'Title', 'Est. Value', 'Status');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'opportunity'
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 0
           ,'fieldlabel'            => $fieldlabel
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('enggCrm_company', 'enggCrm_companyAddressLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'company_address'
           ,'linkingType'           => 'portal'
           ,'displayTitleFieldName' => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'hasPortalEdit'         => 1
           ,'fieldlabel'            => array('Office', 'Street Address', 'Town/Suburb', 'State', 'Country', 'PO Code')
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('enggCrm_company', 'webBasic_productLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'company_product'
           ,'displayTitleFieldName'  => "a.title"
           ,'fieldlabel'             => array('Product Name')
           ,'showAnchorInLinkPortal' => 0
           ,'openExpanded'           => 0
        ));

    }

    /**
     *
     */
    function getEnggCrmCompanyEnggCrmInvoiceLinkPortalSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $sqlStatus = $fn->getValueListSQL('invoiceStatus');

        $text = "
        <select name='invoice_status' class='float_right m5'>
            <option value=''>Status</option>
            {$dbUtil->getDropDownFromSQLCols1($db, $sqlStatus, "Due")}
        </select>
        ";

        return $text;
    }

    /**
     *
     */
    function getEnggCrmCompanyEnggCrmProjectLinkPortalSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $sqlProject = $fn->getValueListSQL('projectStatus');

        $text = "
        <select name='project_status' class='float_right m5'>
            <option value=''>Status</option>
            {$dbUtil->getDropDownFromSQLCols1($db, $sqlProject, "WIP")}
        </select>
        ";

        return $text;
    }

    /**
     *
     */
    function getEnggCrmCompanyEnggCrmOpportunityLinkPortalSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $sqlStatus = $fn->getValueListSQL('opportunityStatus');

        $text = "
        <select name='opp_status' class='float_right m5'>
            <option value=''>Status</option>
            {$dbUtil->getDropDownFromSQLCols1($db, $sqlStatus)}
        </select>
        ";

        return $text;
    }

    //==================================================================//
   function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('enggCrm_company', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        $mediaObj = $mediaArr->getMediaObj('enggCrm_company', 'namecard', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
    
}