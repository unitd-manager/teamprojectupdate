<?
class CP_Admin_Modules_ManPower_Referral_Functions {
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('manPower_referral');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new')
           ,'actBtnsDetail' => array('edit')
           ,'relatedTables' => array('media')
           ,'titleField'    => 'company_name'
           ,'tableName'     => 'company'
           ,'keyField'      => 'company_id'
           ,'title'         => 'Referral'
           ,'actBtnsEdit'   => array('save', 'apply', 'delete')
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('manPower_company', 'manPower_contactLink');

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
           ,'anchorFieldsArr'       => false
           ,'fieldlabel'            => array('First Name'
                                            , 'Last Name'
                                            , 'Email'
                                            , 'Phone (Direct)'
                                            , 'Mobile'
                                            , 'Position'
                                            , 'Primary Contact'
                                       )
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('manPower_company', 'project_projectLink');

        $fieldlabel = array('Project Code', 'Title', 'Project Value', 'Status');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'project'
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 0
           ,'fieldlabel'            => $fieldlabel
        ));
        
        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('manPower_company', 'project_invoiceLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'invoice'
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 0
           ,'fieldlabel'            => array('Invoice Code', 'Project Code', 'Invoice Type', 'Invoice Date','Due Date', 'Inv. Amount', 'Status')
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('manPower_company', 'project_opportunityLink');

        $fieldlabel = array('Opportunity Code', 'Title', 'Est. Value', 'Status');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'opportunity'
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 0
           ,'fieldlabel'            => $fieldlabel
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('manPower_company', 'project_companyAddressLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'company_address'
           ,'linkingType'           => 'portal'
           ,'displayTitleFieldName' => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'hasPortalEdit'         => 1
           ,'fieldlabel'            => array('Office', 'Street Address', 'Town/Suburb', 'State', 'Country', 'PO Code')
        ));
    }

    /**
     *
     */
    function getCompanyInvoicePortalSearch() {
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
        $mediaObj = $mediaArr->getMediaObj('manPower_company', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_company', 'attachment1', 'attachment1');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_company', 'attachment2', 'attachment2');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_company', 'attachment3', 'attachment3');

        $mediaArr->registerMedia($mediaObj, array(
        ));

    }
}