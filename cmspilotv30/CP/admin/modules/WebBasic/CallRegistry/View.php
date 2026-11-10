<?
class CP_Admin_Modules_WebBasic_CallRegistry_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['contact_name'])}
            {$listObj->getListDataCell($row['phone'])}
            {$listObj->getListDataCell($row['email'])}
            {$listObj->getListDataCell($row['company_name'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListRowEnd($row['call_registry_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Contact Name', 'c.contact_name')}
        {$listObj->getListHeaderCell('Contact No', 'c.phone')}
        {$listObj->getListHeaderCell('Email', 'c.email')}
        {$listObj->getListHeaderCell('Company Name', 'co.company_name')}
        {$listObj->getListHeaderCell('Status', 'c.status')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        
        $sqlCont = $fn->getDDSql('manPower_candidate', array());

        $fielset = "
        {$formObj->getDDRowBySQL('Contact', 'contact_id', $sqlCont)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');

        $formObj->mode = $tv['action'];

        $expNoEdit = array('isEditable' => 0);
        $sqlStatus = $fn->getValueListSQL('callRegistryStatus');
        $sqlEnquiryType = $fn->getValueListSQL('enquiryType');
        $expVl     = array('sqlType' => 'OneField');

        $sqlComp = $fn->getDDSql('project_company', array('condn' => "category = 'client'"));

        $sqlCont = $fn->getDDSql('project_contact', array());


        $contact  = "<a href='index.php?_topRm=callRegistry&module=project_contact&_action=detail&contact_id={$row['contact_id']}'>{$row['contact_name']}</a>";
        //$company  = "<a href='index.php?_topRm=callRegistry&module=project_company&_action=detail&company_id={$row['company_id']}'>{$row['company_name']}</a>";

        $compLink = '';
        if ($formObj->mode == 'edit'){
            $compLink = "<a class='editLinkSingle' href='' link='{$fn->getOpenLinkUrl('webBasic_callRegistry', 'project_companyLink', 'fld_company_id')}'>Choose</a>";
        }
        $expComp  = array('notesRight' => $compLink, 'detailValue' => $row['company_name']);

        $contLink = '';
        if ($formObj->mode == 'edit'){
            $contLink = "<a class='editLinkSingle' href='' link='{$fn->getOpenLinkUrl('webBasic_callRegistry', 'project_contactLink', 'fld_contact_id')}'>Choose</a>";
        }
        $expCont  = array('notesRight' => $contLink, 'detailValue' => $contact);


        $fieldset1 = "
        {$formObj->getDDRowBySQL('Contact', 'contact_id', $sqlCont, $row['contact_id'], $expCont)}
        {$formObj->getTBRow('Contact No', 'phone', $row['phone'])}
        {$formObj->getTBRow('Fax', 'fax', $row['fax'])}
        {$formObj->getTBRow('Mobile', 'mobile', $row['mobile'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        {$formObj->getDDRowBySQL('Client Company', 'company_id', $sqlComp, $row['company_id'], $expComp)}
        {$formObj->getTARow('Company Address', 'company_address', $row['company_address'])}
        {$formObj->getTBRow('Industry', 'industry', $row['industry'])}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}
        {$formObj->getDDRowBySQL('Enquiry Tpe', 'enquiry_type', $sqlEnquiryType, $row['enquiry_type'], $expVl)}
        {$formObj->getTimeRow('Contacted Time', 'contact_time', $row['contact_time'])}
        {$formObj->getDateRow('Contacted Date', 'contact_date', $row['contact_date'])}
        {$formObj->getYesNoRRow('Reminder', 'reminder', $row['reminder'])}
        {$formObj->getDateRow('Reminder Date', 'follow_up_date', $row['follow_up_date'])}
        {$formObj->getTARow('Description', 'description', $row['description'])}
        ";


        $text = "
        {$formObj->getFieldSetWrapped('Call Registry Details', $fieldset1)}
        {$formObj->getCreationModificationText2($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $fn = Zend_Registry::get('fn');
        $comment = getCPPluginObj('common_comment');

        $record_id = $fn->getIssetParam($row, 'call_registry_id');
        
        $text = "
        {$comment->getView(array(
             'roomName' => 'webBasic_callRegistry'
            ,'recordId' => $record_id
        ))}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        //$status    = $fn->getReqParam('status');
        //$sqlStatus = $fn->getValueListSQL('enquiryStatus');
        
        $text = "
        ";
        
        return $text;
    }
}