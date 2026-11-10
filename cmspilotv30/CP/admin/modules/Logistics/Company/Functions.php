<?
class CP_Admin_Modules_Logistics_Company_Functions {
    /**
     *
     */
    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('logistics_company');
        $modules->registerModule($modObj, array(
             'actBtnsList' => array('new', 'import')
            ,'titleField'    => 'company_name'
            ,'title'         => 'Client'
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $linkObj = $inst->getLinksArrayObj('logistics_company', 'project_projectLink');

        $fieldlabel = array('Project Code', 'Title', 'Project Value', 'Status');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'project'
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 0
           ,'fieldlabel'            => $fieldlabel
        ));
        
        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('logistics_company', 'project_invoiceLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'invoice'
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 0
           ,'fieldlabel'            => array('Invoice Code', 'Project Code', 'Invoice Type', 'Invoice Date','Due Date', 'Inv. Amount', 'Status')
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('logistics_company', 'project_opportunityLink');

        $fieldlabel = array('Opportunity Code', 'Title', 'Est. Value', 'Status');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'opportunity'
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 0
           ,'fieldlabel'            => $fieldlabel
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('logistics_company', 'project_companyAddressLink');

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
        $linkObj = $inst->getLinksArrayObj('logistics_company', 'logistics_contactLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'contact'
            ,'displayTitleFieldName' => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'linkingType'           => 'portal'
           ,'anchorFieldsArr'       => array('first_name' => $inst->getLinkAnchorObj('first_name', 'contact_id')
											,'last_name' => $inst->getLinkAnchorObj('last_name', 'contact_id'))	
           ,'fieldlabel'            => array('First name'
                                            ,'Last name'
                                            ,'Email'
                                       )
        ));


    }

    /**
     *
     */
    /*function getCompanyInvoicePortalSearch() {
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
    function getCompanyProjectPortalSearch() {
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
     function getCompanyOpportunityPortalSearch() {
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
        $mediaObj = $mediaArr->getMediaObj('logistics_company', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}



