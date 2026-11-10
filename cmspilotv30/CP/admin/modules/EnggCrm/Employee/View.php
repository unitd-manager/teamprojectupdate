<?
class CP_Admin_Modules_EnggCrm_Employee_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){

            if ($row['employee_work_type'] == 'Part time') {
                $amount = $row['day_rate'];
            } else {
                $amount = $row['salary'];
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['employee_name'])}
            {$listObj->getListDateCell($row['date_of_expiry'])}
            {$listObj->getListDataCell($row['spass_no'])}
            {$listObj->getListDataCell($row['employee_work_type'])}
            {$listObj->getListDataCell($amount)}
            {$listObj->getListRowEnd($row['employee_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Name', 'a.employee_name')}
        {$listObj->getListHeaderCell('Date of Expiry', 'a.date_of_expiry')}
        {$listObj->getListHeaderCell('S Pass No', 'a.spass_no')}
        {$listObj->getListHeaderCell('Part Time / Full Time', 'a.employee_work_type')}
        {$listObj->getListHeaderCell('Hourly Rate / Salary', 'a.employee_name')}
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

        $fielset = "
        {$formObj->getTBRow('Name', 'employee_name')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode  = $tv['action'];

        $chineseName    = '';
        $chinesePos     = '';
        $chineseDept    = '';
        $compAddressDD  = '';
        $companyAddress = '';
        $staffDetail    = '';
        $personalAdd    = '';
        $compLink       = '';

        $sqlCategory            = $fn->getValueListSQL('employeeType');
        $sqlTitle               = $fn->getValueListSQL('contactTitle');
        $sqlEmployeeWorkType    = $fn->getValueListSQL('employeeWorkType');
        $sqlPosition            = $fn->getValueListSQL('positionType','value');
        $sqlComp                = $fn->getDDSql('enggCrm_company');
        
        if ($cpCfg['m.enggCrm.employee.showChineseFields'] == 1){
            $chineseName = $formObj->getTBRow('Name (Chinese)', 'chi_name', $row['chi_name']);
            $chinesePos  = $formObj->getTBRow('Position (Chinese)', 'chi_position', $row['chi_position']);
            $chineseDept = $formObj->getTBRow('Department (Chinese)', 'chi_department', $row['chi_department']);
        }

        if ($tv['action'] == 'edit'){
            if($cpCfg['m.enggCrm.hasMultipleCompanyAddress'] == 1){
                $sqlCombo = "
                SELECT company_address_id
                      ,CONCAT_WS(', ', address_flat, address_street, address_town, address_country) AS address
                FROM  company_address a
                WHERE company_id = '{$row['company_id']}'
                ORDER BY company_address_id
                ";
                $compAddressDD = "
                {$formObj->getDDRowBySQL('Company Address', 'company_address_id', $sqlCombo, $row['company_address_id'])}
                ";
            }
        }

        if ($cpCfg['m.enggCrm.employee.showDetail'] == 1){
            $sqlCombo = "
            SELECT staff_id
                  ,CONCAT_WS(' ', a.first_name, a.last_name) AS staff_name
            FROM staff a
            ORDER BY staff_name";

            $fieldset = "
            {$formObj->getDDRowBySQL("{$cpCfg['m.enggCrm.staffFieldLabel']}", "staff_id", $sqlCombo, $row['staff_id'])}
            ";

            $staffDetail = $formObj->getFieldSetWrapped($cpCfg['m.enggCrm.staffFieldLabel'], $fieldset);
        }

        $expVl = array('sqlType' => 'OneField');
        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_name']);

        $formAddPosition = "index.php?_topRm={$tv['topRm']}&module=enggCrm_employee&_spAction=addNewValuelistForm&valuelist_name=positionType&employee_id={$row['employee_id']}&showHTML=0";
        $expPosition     = array('sqlType' => 'OneField'
                            ,'notesRight' => "<a href='{$formAddPosition}' class='mr20 addNewValue' valuelist_name='positionType'>Add</a>");

        $fielset1 = "
        {$formObj->getDDRowBySQL('Choose Category', 'category', $sqlCategory, $row['category'], $expVl)}
        {$formObj->getDDRowBySQL('Salutation', 'salutation', $sqlTitle, $row['salutation'], $expVl)}
        {$formObj->getTBRow('Name', 'employee_name', $row['employee_name'])}
        {$chineseName}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        {$formObj->getTBRow('Passport No', 'passport', $row['passport'])}
        {$formObj->getTBRow('FIN No *', 'nric_no', $row['nric_no'])}
        {$formObj->getTBRow('S Pass No*', 'spass_no', $row['spass_no'])}
        {$formObj->getDateRow('Date of Birth', 'date_of_birth', $row['date_of_birth'])}
        {$formObj->getDateRow('Date of Expiry', 'date_of_expiry', $row['date_of_expiry'])}
        {$formObj->getDDRowBySQL('Full Time & Part Time *', 'employee_work_type', $sqlEmployeeWorkType, $row['employee_work_type'], $expVl)}
        <div class='addHourlyRate'>{$formObj->getTBRow('Day Rate', 'day_rate', $row['day_rate'])}</div>
        <div class='salaryForFullTime'>{$formObj->getTBRow('Salary', 'salary', $row['salary'])}</div>
        {$formObj->getTBRow('Hourly Rate', 'add_hourly_rate', $row['add_hourly_rate'])}
        {$formObj->getTBRow('OT Rate', 'overtime_rate', $row['overtime_rate'])}
        {$formObj->getDDRowBySQL('Position', 'position', $sqlPosition, $row['position'], $expPosition)}
        ";     

        if ($formObj->mode == 'edit'){
            $compLink = "<a class='editLinkSingle' href='' link='{$fn->getOpenLinkUrl('enggCrm_employee', 'enggCrm_companyLink', 'fld_company_id')}'>Choose</a>";
        }
        $expComp  = array('notesRight' => $compLink, 'detailValue' => $row['c_company_name']);

        $fielset2 = "
        {$formObj->getTBRow('Flat / Building', 'address_flat', $row['address_flat'])}
        {$formObj->getTBRow('Street Address', 'address_street', $row['address_street'])}
        {$formObj->getTBRow('Phone', 'phone_direct', $row['phone_direct'])}
        {$formObj->getTBRow('Fax', 'fax', $row['fax'])}
        {$formObj->getTBRow('Mobile', 'mobile', $row['mobile'])}
        {$formObj->getTBRow('District/ Town', 'address_town', $row['address_town'])}
        {$formObj->getTBRow('State/ Zip', 'address_state', $row['address_state'])}
        {$formObj->getDDRowBySQL('Country', 'address_country', $sqlCountry, $row['address_country'], $expCountry)}
        ";
        
        $text = "
        {$formObj->getFieldSetWrapped('Employee Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Employee Address Details', $fielset2)}
        {$staffDetail}
        {$personalAdd}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getSearch(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');

        $textPublished  = "";

        $sqlCompany = $fn->getDDSql('enggCrm_company');
        $sqlInterest = $fn->getDDSql('common_interest');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $fielset1 = "
        {$formObj->getTBRow('First Name', 'first_name')}
        {$formObj->getTBRow('Last Name', 'last_name')}
        {$formObj->getTBRow('Email', 'email' )}
        ";

        $fielset2 = "
        {$formObj->getDDRowBySQL('Company Name', 'company_id', $sqlCompany)}
        {$formObj->getTBRow('Position', 'position')}
        ";

        $fielset3 = "
        {$formObj->getYesNoDropDownRow('Subscribed', 'subscribe')}
        {$formObj->getDDRowBySQL('Interst Group', 'interest_id', $sqlInterest)}
        {$formObj->getDDRowByArr('Special Search', 'special_search', $spArray)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Employee Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Company Details', $fielset2)}
        {$formObj->getFieldSetWrapped('Other Details', $fielset3)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $comment = getCPPluginObj('common_comment');
        
        $rows = "";

        if( $cpCfg['m.enggCrm.employee.showEvent'] == "1"){
            $rows .= $displayLinkData->getLinkPortalMain("enggCrm_employee", "event_eventLink", "Events Linked", $row);
        }

        $record_id = $fn->getIssetParam($row, 'employee_id');

        $text = "
        {$media->getRightPanelMediaDisplay("Picture", "enggCrm_employee", "picture", $row)}
        {$rows}
        {$comment->getView(array(
             'roomName' => 'enggCrm_employee
'
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
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $special_search     = $fn->getReqParam('special_search');
        $employee_work_type = $fn->getReqParam('employee_work_type');
        
        $sqlEmployeeWorkType = $fn->getValueListSQL('employeeWorkType');

        $spArray = array(
              "Subscribed"
             ,"Not-Subscribed"
             ,"Flagged"
             ,"Not-Flagged"
        );

        $text = "
        <td>
            <select name='employee_work_type' >
                <option value=''>Employee Work Type</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlEmployeeWorkType, $employee_work_type)}
            </select>
        </td>
        <!--<td>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
            </select>
        </td>-->
        ";
        
        return $text;
    }

    /**
     *
     */
    function getAddNewValuelistForm() {
        $tv      = Zend_Registry::get('tv');
        $fn      = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $valuelist_name = $fn->getReqParam('valuelist_name');
        $employee_id    = $fn->getReqParam('employee_id');

        $formAction = "index.php?_topRm={$tv['topRm']}&module=enggCrm_employee&_spAction=addNewValuelistFormSubmit&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar addNewDropdownValueForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Value', 'valuelist_value')}
            <input type='hidden' name='valuelist_name' value='{$valuelist_name}' />
            <input type='hidden' name='employee_id' value='{$employee_id}' />
        </form>
        ";

        return $text;
    }


}