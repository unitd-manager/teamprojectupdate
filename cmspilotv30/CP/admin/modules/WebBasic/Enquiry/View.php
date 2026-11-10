<?
class CP_Admin_Modules_WebBasic_Enquiry_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');

        $rows  = "";
        $rowCounter = 0;
        $fnModCountry = includeCPClass('ModuleFns', 'common_country');

        $staff       = '';
        $clientType  = '';
        $country     = '';
        $enquiryType = '';
        $subject     = '';
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            if ($cpCfg['m.webBasic.enquiry.showStaff'] == 1){
                $staff = $listObj->getListDataCell($row['staff_name']);
            }
            
            if ($cpCfg['m.webBasic.enquiry.showClientType'] == 1){
                $clientType = $listObj->getListDataCell($row['client_type']);
            }

            if ($cpCfg['m.webBasic.enquiry.showEnquiryType']){
                $enquiryType = $listObj->getListDataCell($row['enquiry_type']);
            }

            if ($cpCfg['m.webBasic.enquiry.showSubject']){
                $subject = $listObj->getListDataCell($row['subject']);
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['first_name'])}
            {$listObj->getListDataCell($row['last_name'])}
            {$fnModCountry->getCountryValueCellInList($row)}
            {$listObj->getListDataCell($row['email'])}
            {$listObj->getListDataCell($row['status'])}
            {$staff}
            {$clientType}
            {$enquiryType}
            {$subject}
            {$listObj->getListDataCell($row['creation_date'])}
            {$listObj->getListDataCell($row['enquiry_id'])}
            {$listObj->getListRowEnd($row['enquiry_id'])}
            ";
            $rowCounter++;
        }

        $staff       = '';
        $clientType  = '';
        $enquiryType = '';
        $enquiryType = '';

        if ($cpCfg['m.webBasic.enquiry.showStaff'] == 1){
            $staff = $listObj->getListHeaderCell('Staff', 'staff_name');
        }

        if ($cpCfg['m.webBasic.enquiry.showClientType'] == 1){
            $clientType = $listObj->getListHeaderCell('Client Type', 'client_type');
        }

        if ($cpCfg['m.webBasic.enquiry.showEnquiryType']){
            $enquiryType = $listObj->getListHeaderCell('Enquiry Type', 'enquiry_type');
        }
        
        if ($cpCfg['m.webBasic.enquiry.showSubject']){
            $subject = $listObj->getListHeaderCell('Subject', 'subject');
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.enquiry.lbl.firstName', 'First Name'), 'e.first_name')}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.enquiry.lbl.lastName', 'Last Name'), 'e.last_name')}
        {$fnModCountry->getCountryLabelCellInList()}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.enquiry.lbl.email', 'Email'), 'e.email')}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.header.enquiry.lbl.enquiryStatus', 'Enquiry Status'), 'e.status')}
        {$staff}
        {$clientType}
        {$enquiryType}
        {$subject}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.enquiry.lbl.creationDate', 'Creation Date'), 'e.creation_date')}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.enquiry.lbl.id', 'ID'), 'e.enquiry_id')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getPrint($result){
        return $this->getList($result);
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');

        $fieldset = "
        {$formObj->getTBRow($ln->gd('m.webBasic.enquiry.lbl.firstName', 'First Name'), 'first_name')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
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
        $ln = Zend_Registry::get('ln');

        $formObj->mode = $tv['action'];

        $expNoEdit = array('isEditable' => 0);
        $sqlStatus = $fn->getValueListSQL('enquiryStatus');
        $expVl     = array('sqlType' => 'OneField');

        $staff = '';
        $clientType = '';
        $country = '';
        if ($cpCfg['m.webBasic.enquiry.showStaff'] == 1){
            $sqlStaff = "
            SELECT s.staff_id
                  ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name 
            FROM staff s 
            ORDER BY staff_name
            ";
    
            $expStf = array('detailValue' => $row['staff_name']);

            $staff = $formObj->getDDRowBySQL('Staff',  'staff_id', $sqlStaff, $row['staff_id'], $expStf);
        }
        
        if ($cpCfg['m.webBasic.enquiry.showClientType'] == 1){
            $sqlType = $fn->getValueListSQL('enquiryClientType');
            $clientType = $formObj->getDDRowBySQL('Client Type', 'client_type', $sqlType, $row['client_type'], $expVl);
        }

        if ($cpCfg['m.webBasic.enquiry.showCountryCode'] == 1 && $cpCfg['m.webBasic.enquiry.showCountry'] == 0){
            $country = $formObj->getTBRow('Country', 'country', $row['country'], $expNoEdit);
        }
        
        $prefContactTime = "";
        if ($cpCfg['m.webBasic.enquiry.showPrefContactTime'] == 1){
            $expCb = array('commaToArray' => true, 'sqlType' => 'OneField', 'disabled' => true);
            $sqlPrefCont = $fn->getValueListSQL('preferredContact');
            $sqlPrefTime = $fn->getValueListSQL('preferredTime');

            $prefContactTime = "
            {$formObj->getCheckBoxArrRowBySQL($ln->gd('m.webBasic.enquiry.lbl.preferredContact', 'Preferred Contact'), 'preferred_contact[]', $sqlPrefCont, $row['preferred_contact'], $expCb)}
            {$formObj->getCheckBoxArrRowBySQL($ln->gd('m.webBasic.enquiry.lbl.preferredContact', 'Preferred Time'), 'preferred_time[]', $sqlPrefTime, $row['preferred_time'], $expCb)}
            ";
        }
        
        $fnModCountry = includeCPClass('ModuleFns', 'common_country');

        $fieldset1 = "
        {$formObj->getTBRow($ln->gd('m.webBasic.enquiry.lbl.firstName', 'First Name'), 'first_name', $row['first_name'], $expNoEdit)}
        {$formObj->getTBRow($ln->gd('m.webBasic.enquiry.lbl.lastName', 'Last Name'), 'last_name', $row['last_name'], $expNoEdit)}
        {$country}
        {$fnModCountry->getCountryDropDown($formObj->mode, $row)}
        {$formObj->getTBRow($ln->gd('m.webBasic.enquiry.lbl.email', 'Email'), 'email', $row['email'], $expNoEdit)}
        {$formObj->getTARow($ln->gd('m.webBasic.enquiry.lbl.comments', 'Comments'), 'comments', $row['comments'], $expNoEdit)}
        {$prefContactTime}
        ";

        $fieldset2 = "
        {$formObj->getDDRowBySQL($ln->gd('m.webBasic.enquiry.lbl.enquiryStatus', 'Enquiry Status'), 'status', $sqlStatus, $row['status'], $expVl)}
        {$clientType}
        {$formObj->getDateRow($ln->gd('m.webBasic.enquiry.lbl.followUpDate', 'Follow up Date'), 'follow_up_date', $row['follow_up_date'])}
        {$staff}
        {$formObj->getTARow($ln->gd('m.webBasic.enquiry.lbl.adminCommnets', 'Admin Comments'), 'notes', $row['notes'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.webBasic.enquiry.lbl.enquiryDetails', 'Enquiry Details'), $fieldset1)}
        {$formObj->getFieldSetWrapped($ln->gd('m.webBasic.enquiry.lbl.followUpDate', 'Follow up'), $fieldset2)}
        {$formObj->getCreationModificationText2($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        
        $text = "
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
        $ln = Zend_Registry::get('ln');

        $status         = $fn->getReqParam('status');
        $creation_date1 = $fn->getReqParam('creation_date1');
        $creation_date2 = $fn->getReqParam('creation_date2');
        $sqlStatus      = $fn->getValueListSQL('enquiryStatus');
        
        $clientType = '';
        $staff      = '';
        
        if ($cpCfg['m.webBasic.enquiry.showStaff'] == 1){
            $sqlStaff = "
            SELECT s.staff_id
                  ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name 
            FROM staff s 
            ORDER BY staff_name
            ";
            $staff_id = $fn->getReqParam('staff_id');

            $staff = "
            <td>
                <select name='staff_id'>
                    <option value=''>{$ln->gd('m.webBasic.enquiry.lbl.staff', 'Staff')}</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlStaff, $staff_id)}
                </select>
            </td>
            ";
        }

        if ($cpCfg['m.webBasic.enquiry.showClientType'] == 1){
            $sqlType = $fn->getValueListSQL('enquiryClientType');
            $client_type = $fn->getReqParam('client_type');

            $clientType = "
            <td>
                <select name='client_type'>
                    <option value=''>{$ln->gd('m.webBasic.enquiry.lbl.clientType', 'Client Type')}</option>
                    {$dbUtil->getDropDownFromSQLCols1($db, $sqlType, $client_type)}
                </select>
            </td>
            ";
        }

        $fnModCountry = includeCPClass('ModuleFns', 'common_country');
        $yearEnd = date('Y') + 10;
        $text = "
        <td class='dateRange'>
            {$ln->gd('m.webBasic.enquiry.lbl.creationDate', 'Creation Date: ')}
            <input type='text' allowEdit='1' name='creation_date1' class='fld_date' 
                   id='fld_creation_date1' value='{$creation_date1}' yearEnd='{$yearEnd}' />
            <input type='text' allowEdit='1' name='creation_date2' class='fld_date' 
                   id='fld_creation_date2' value='{$creation_date2}' yearEnd='{$yearEnd}' />
        </td>
        {$clientType}
        {$staff}
        <td>
            <select name='status'>
                <option value=''>{$ln->gd('m.webBasic.enquiry.lbl.status', 'Status')}</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlStatus, $status)}
            </select>
        </td>
        {$fnModCountry->getCountryDropDown('search')}
        ";

        
        return $text;
    }
}