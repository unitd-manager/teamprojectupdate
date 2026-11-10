<?
class CP_Admin_Modules_Tradingsg_Enquiry_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

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
           $staff       = $listObj->getListDataCell($row['staff_name']);
            $enquiryType = $listObj->getListDataCell($row['enquiry_type']);
	        $followUpDate = $fn->getCPDate($row['follow_up_date'], 'd-m-Y');


            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$fnModCountry->getCountryValueCellInList($row)}
            {$listObj->getListDataCell($row['email'])}
            {$listObj->getListDataCell($row['status'])}
            {$staff} 
            {$subject}
            {$listObj->getListDataCell($followUpDate)}
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

        $staff = $listObj->getListHeaderCell('Staff', 'staff_name');

        $enquiryType = $listObj->getListHeaderCell('Enquiry Type', 'enquiry_type');

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'e.title')}
        {$listObj->getListHeaderCell($ln->gd('cp.header.lbl.email', 'Email'), 'e.email')}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.header.enquiry.lbl.enquiryStatus', 'Enquiry Status'), 'e.status')}
        {$staff}
        {$listObj->getListHeaderCell('Reminder Date', 'e.follow_up_date')}
        {$listObj->getListHeaderCell($ln->gd('cp.header.lbl.creationDate', 'Creation Date'), 'e.creation_date')}
        {$listObj->getListHeaderCell($ln->gd('cp.header.lbl.id', 'ID'), 'e.enquiry_id')}
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
        {$formObj->getTBRow('Title', 'title')}
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

        $expNoEdit     = array('isEditable' => 1);
        $expNoEditCode = array('isEditable' => 0);
        $sqlStatus = $fn->getValueListSQL('enquiryStatus');
        $expVl     = array('sqlType' => 'OneField');

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_name']);

        $staff = '';
        $clientType = '';
        $country = '';
        $sqlStaff = "
        SELECT s.staff_id
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
        FROM staff s
        ORDER BY staff_name
        ";

        $expStf = array('detailValue' => $row['staff_name'], 'isEditable' => 0);
        $staff = $formObj->getDDRowBySQL('Staff',  'staff_id', $sqlStaff, $row['staff_id'], $expStf);

        //$country_val = ($row['address_country'] == '') ? 'SG' : $row['address_country'];
        if ($cpCfg['countryForCurrency'] == 'India'){
            $country_val = 'IN';
        } else if ($cpCfg['countryForCurrency'] == 'Singapore'){
            $country_val = 'SG';
        } else {
            $country_val = '';
        }

        $country = $formObj->getDDRowBySQL('Country', 'address_country', $sqlCountry, $country_val, $expCountry);

        $prefContactTime = "";
            $expCb = array('commaToArray' => true, 'sqlType' => 'OneField', 'disabled' => false);
            $sqlPrefCont = $fn->getValueListSQL('preferredContact');
            $sqlPrefTime = $fn->getValueListSQL('preferredTime');

            $prefContactTime = "
            {$formObj->getCheckBoxArrRowBySQL('Preferred Contact', 'preferred_contact[]', $sqlPrefCont, $row['preferred_contact'], $expCb)}
            {$formObj->getCheckBoxArrRowBySQL('Preferred Time', 'preferred_time[]', $sqlPrefTime, $row['preferred_time'], $expCb)}
            ";

        $fnModCountry = includeCPClass('ModuleFns', 'common_country');

        $expComp = array('displayText' => $row['c_company_name']);
        //$sqlComp = $fn->getDDSql('tradingsg_company');
        $sqlComp = "
        SELECT c.company_id
              ,c.company_name
        FROM company c
        WHERE c.category = 'Client'
        ORDER BY company_name
        ";
        $companyText = $fn->getRecordDetailLink('tradingsg_company', 'record_id',
                            $row['company_id'], $expComp);
        $expCompDisp = array('detailValue' => $companyText, 'hideFirstOption' => 1);

        $status =  $formObj->getDDRowBySQL($ln->gd('m.webBasic.enquiry.lbl.enquiryStatus', 'Enquiry Status'), 'status', $sqlStatus, $row['status'], $expVl);

        $callRegistryTr = '';
        if ($row['call_registry_id'] != '') {
            $call_registry_code = "
            <a href='index.php?_topRm=enquiry&module=tradingsg_callRegistry&call_registry_id={$row['call_registry_id']}&_action=detail'><u>{$row['call_registry_code']}</u></a>";
            $callRegistryTr = $formObj->getTBRow('Call Registry Code', 'call_registry_id', $call_registry_code, $expNoEditCode);
        }

        $fieldset1 = "
        <!--
        {$formObj->getTBRow($ln->gd('m.webBasic.enquiry.lbl.firstName', 'First Name'), 'first_name', $row['first_name'], $expNoEdit)}
        {$formObj->getTBRow($ln->gd('m.webBasic.enquiry.lbl.lastName', 'Last Name'), 'last_name', $row['last_name'], $expNoEdit)}
        -->
        {$formObj->getTBRow('Enquiry Code', 'enquiry_code', $row['enquiry_code'], $expNoEditCode)}
        {$callRegistryTr}
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$formObj->getDDRowBySQL('Client Name', 'company_id', $sqlComp,
                                 $row['company_id'], $expCompDisp)}
        {$fnModCountry->getCountryDropDown($formObj->mode, $row)}
        {$formObj->getTBRow($ln->gd('cp.lbl.email', 'Email'), 'email', $row['email'], $expNoEdit)}
        {$formObj->getTBRow('Phone', 'phone', $row['phone'])}
        {$formObj->getTARow('Notes', 'comments', $row['comments'])}
        {$country}
        <!-- {$prefContactTime} -->
        ";

        $fieldset2 = "
        {$status}
        {$formObj->getDateRow($ln->gd('m.webBasic.enquiry.lbl.followUpDate', 'Follow up Date'), 'follow_up_date', $row['follow_up_date'])}
        {$formObj->getTARow($ln->gd('m.webBasic.enquiry.lbl.adminCommnets', 'Admin Comments'), 'notes', $row['notes'])}
        {$staff}
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

        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $comment = getCPPluginObj('common_comment');
        $actionButtons = '';

        $record_id = $fn->getIssetParam($row, 'enquiry_id');

        $actionButtons .="
        <div class='floatbox actionBtnsDetail'>
            <div class='float_right button mb5 mr5'>
                <a href='#' id='raiseQuote' enquiry_id='{$row['enquiry_id']}'>Raise Quote</a>
            </div>
        </div>
        ";

        $enquiryProductGroupLink = "";
        if ($cpCfg['m.tradingsg.enquiry.hasEnquiryProductGroupLink'] == 1) {
	        $enquiryProductGroupLink .= $displayLinkData->getLinkPortalMain('tradingsg_enquiry', 'tradingsg_productGroupLink', 'Product Group Linked', $row);
		}

        $text = "
        {$actionButtons}
		{$comment->getView(array(
		     'roomName' => 'tradingsg_enquiry'
		    ,'recordId' => $record_id
		    ,'allowEdit' => false
		    ,'allowDelete' => false
		    ,'addReviewLbl' => 'Add Activity'
		    ,'heading' => 'Activities'
		))}
        {$displayLinkData->getLinkPortalMain('tradingsg_enquiry', 'tradingsg_quoteLink', 'Quote Linked', $row)}
		{$enquiryProductGroupLink}
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
        $followUpDate1  = $fn->getReqParam('followUpDate1');
        $followUpDate2  = $fn->getReqParam('followUpDate2');
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
                    <option value=''>Staff</option>
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
                    <option value=''>Client Type</option>
                    {$dbUtil->getDropDownFromSQLCols1($db, $sqlType, $client_type)}
                </select>
            </td>
            ";
        }

        $fnModCountry = includeCPClass('ModuleFns', 'common_country');
        $yearEnd = date('Y') + 10;
        $text = "
        <td class='dateRange'>
            {$ln->gd('cp.lbl.creationDate', 'Creation Date: ')}
            <input type='text' allowEdit='1' name='creation_date1' class='fld_date'
                   id='fld_creation_date1' value='{$creation_date1}' yearEnd='{$yearEnd}' />
            <input type='text' allowEdit='1' name='creation_date2' class='fld_date'
                   id='fld_creation_date2' value='{$creation_date2}' yearEnd='{$yearEnd}' />
        </td>

        <td class='dateRange'>
            Reminder Date:
            <input type='text' allowEdit='1' name='followUpDate1' class='fld_date'
                   id='fld_followupdate1' value='{$followUpDate1}' yearEnd='{$yearEnd}' />
            <input type='text' allowEdit='1' name='followUpDate2' class='fld_date'
                   id='fld_followupdate2' value='{$followUpDate2}' yearEnd='{$yearEnd}' />
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
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        //$validate->validateData('title', 'Please enter the title');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
}