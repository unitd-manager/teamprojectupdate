<?
class CP_Admin_Modules_Hms_Employee_View extends CP_Common_Lib_ModuleViewAbstract
{
    var $jssKeys = array('jscolor-1.4.4');
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
            $company = "<a href='index.php?_topRm=project&module=hms_company&_action=edit&company_id={$row['company_id']}'>{$row['c_company_name']}</a>";
            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($row['salutation'])}
            {$listObj->getGoToDetailText($count, $row['first_name'].' ' .$row['middle_name'].' '.$row['last_name'])}
            {$listObj->getListDataCell($row['position'])}
            {$listObj->getListDataCell("<a href='mailto:{$row['email']}'>{$row['email']}</a>")}
            {$listObj->getListDataCell($row['mobile'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListRowEnd($row['employee_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Salutation', 'a.salutation')}
        {$listObj->getListHeaderCell('Name', 'first_name')}
        {$listObj->getListHeaderCell('Position', 'a.position')}
        {$listObj->getListHeaderCell('Email', 'a.email')}
        {$listObj->getListHeaderCell('Mobile', 'a.mobile')}
        {$listObj->getListHeaderCell('Status', 'a.status')}
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
        {$formObj->getTBRow('First Name *', 'first_name')}
        {$formObj->getTBRow('Middle Name', 'middle_name')}
        {$formObj->getTBRow('Last Name', 'last_name')}
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

        $sqlCategory            = $fn->getValueListSQL('employeeCategory');
        $sqlTitle               = $fn->getValueListSQL('contactTitle');
        $sqlEmployeeWorkType    = $fn->getValueListSQL('employeeWorkType');
        $sqlPosition            = $fn->getValueListSQL('positionType','value');
        $sqlComp                = $fn->getDDSql('hms_company');


        if ($tv['action'] == 'edit'){
            if($cpCfg['m.hms.hasMultipleCompanyAddress'] == 1){
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

        $status = array(
              "Current"
             ,"Archive"
             ,"Cancel"
        );

        $expVl = array('sqlType' => 'OneField');
        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        //$expCountry = array('detailValue' => $row['country_name']);

        $formAddPosition = "index.php?_topRm={$tv['topRm']}&module=hms_employee&_spAction=addNewValuelistForm&valuelist_name=positionType&employee_id={$row['employee_id']}&showHTML=0";
        $expPosition     = array('sqlType' => 'OneField'
                            ,'notesRight' => "<a href='{$formAddPosition}' class='mr20 addNewValue' valuelist_name='positionType'>Add</a>");

        $fielset1 = "
        {$formObj->getDDRowBySQL('Choose Category', 'category', $sqlCategory, $row['category'], $expVl)}
        {$formObj->getDDRowBySQL('Salutation', 'salutation', $sqlTitle, $row['salutation'], $expVl)}
        {$formObj->getTBRow('Name', 'employee_name', $row['employee_name'])}
        {$chineseName}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        {$formObj->getTBRow('Passport *', 'passport', $row['passport'])}
        {$formObj->getTBRow('Nric No *', 'nric_no', $row['nric_no'])}
        {$formObj->getDDRowBySQL('Full Time & Part Time *', 'employee_work_type', $sqlEmployeeWorkType, $row['employee_work_type'], $expVl)}
        <div class='addHourlyRate'>{$formObj->getTBRow('Add Hourly Rate', 'add_hourly_rate', $row['add_hourly_rate'])}</div>
        <div class='salaryForFullTime'>{$formObj->getTBRow('Salary', 'salary', $row['salary'])}</div>
        {$formObj->getDDRowBySQL('Position', 'position', $sqlPosition, $row['position'], $expPosition)}
        <div class='type-text ym-fbox-text'>
            <label>Event Color</label>
            <input name='color' class='color {hash:true}' type='text' value='{$row['color']}'>
        </div>
        ";

        if ($formObj->mode == 'edit'){
            $compLink = "<a class='editLinkSingle' href='' link='{$fn->getOpenLinkUrl('hms_employee', 'hms_companyLink', 'fld_company_id')}'>Choose</a>";
        }

        $expHideFirst = array('hideFirstOption' => 1);
        $expComp  = array('notesRight' => $compLink, 'detailValue' => $row['c_company_name']);

        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Employee Details</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td>{$formObj->getDDRowBySQL('Salutation', 'salutation', $sqlTitle, $row['salutation'], $expVl)}</td>
                                <td>{$formObj->getTBRow('First Name *', 'first_name', $row['first_name'])}</td>
                                <td>{$formObj->getTBRow('Middle Name', 'middle_name', $row['middle_name'])}</td>
                                <td>{$formObj->getTBRow('Last Name', 'last_name', $row['last_name'])}</td>
                                <td>{$formObj->getTBRow('Email', 'email', $row['email'])}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getTBRow('Passport *', 'passport', $row['passport'])}</td>
                                <td>{$formObj->getTBRow('Nric No *', 'nric_no', $row['nric_no'])}</td>
                                <td>{$formObj->getDDRowBySQL('Full Time & Part Time *', 'employee_work_type', $sqlEmployeeWorkType, $row['employee_work_type'], $expVl)}</td>
                                <td>{$formObj->getTBRow('Consulting Fees', 'consultation_fees', $row['consultation_fees'])}</td>
                                <td><div class='addHourlyRate'>{$formObj->getTBRow('Add Hourly Rate', 'add_hourly_rate', $row['add_hourly_rate'])}</div></td>
                            </tr>
                            <tr>
                                <td><div class='salaryForFullTime'>{$formObj->getTBRow('Salary', 'salary', $row['salary'])}</div></td>
                                <td>{$formObj->getDDRowBySQL('Position', 'position', $sqlPosition, $row['position'], $expPosition)}</td>
                                <td><div class='type-text ym-fbox-text'>
                                        <label>Event Color</label>
                                        <input name='color' class='color {hash:true}' type='text' value='{$row['color']}'>
                                    </div>
                                </td>
                                <td class='highlightedTdForNote'>{$formObj->getDDRowByArr('Status', 'status', $status, $row['status'], $expHideFirst)}</td>
                                <td class='highlightedTdForNote'>{$formObj->getYesNoRRow('Add in Payroll', 'add_in_payroll', $row['add_in_payroll'])}</td>
                            </tr>
                            <tr>
                                <th colspan='5'>Employee Address Details</th>
                            </tr>
                            <tr>
                                <td>{$formObj->getTBRow('Flat / Building', 'address_flat', $row['address_flat'])}</td>
                                <td>{$formObj->getTBRow('Street Address', 'address_street', $row['address_street'])}</td>
                                <td>{$formObj->getTBRow('Phone', 'phone_direct', $row['phone_direct'])}</td>
                                <td>{$formObj->getTBRow('Fax', 'fax', $row['fax'])}</td>
                                <td>{$formObj->getTBRow('Mobile', 'mobile', $row['mobile'])}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getTBRow('District/ Town', 'address_town', $row['address_town'])}</td>
                                <td>{$formObj->getTBRow('State/ Zip', 'address_state', $row['address_state'])}</td>
                                <td>{$formObj->getDDRowBySQL('Country', 'address_country', $sqlCountry, $row['address_country'])}</td>
                            </tr>
                            <tr>
                                <td class= 'creationModificationText' colspan = '5'>{$formObj->getCreationModificationText($row)}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
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

        $sqlCompany = $fn->getDDSql('hms_company');
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

        if( $cpCfg['m.hms.employee.showInterest'] == "1"){
            $rows .= $displayLinkData->getLinkPortalMain("hms_employee", "common_interestLink", "Interests Linked", $row);
        }

        /*if( $cpCfg['m.hms.employee.showEvent'] == "1"){
            $rows .= $displayLinkData->getLinkPortalMain("hms_employee", "event_eventLink", "Events Linked", $row);
        }*/

        $record_id = $fn->getIssetParam($row, 'employee_id');

        $text = "
        {$media->getRightPanelMediaDisplay("Picture", "hms_employee", "picture", $row)}
        {$rows}
        {$comment->getView(array(
             'roomName' => 'hms_employee
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

        $interest_id    = $fn->getReqParam('interest_id');
        $subscribe      = $fn->getReqParam('subscribe');
        $special_search = $fn->getReqParam('special_search');
        $company_id     = $fn->getReqParam('company_id');
        $position       = $fn->getReqParam('position');
        $status         = $fn->getReqParam('status');

        if ($tv['searchDone'] == 0){
            $status = 'Current';
        }

        //==================================================================//
        $companyText  = "";
        $categoryText = "";
        $interestText = "";

        $sqlCompany     = $fn->getDDSql('hms_company');
        $SQLStatus      = $fn->getValueListSQL('companyStatus');
        $sqlCategory    = $fn->getValueListSQL('employeeCategory');
        $sqlInterest    = $fn->getDDSql('common_interest');
        $sqlPosition    = $fn->getValueListSQL('positionType','value');

        $companyText = "
        <td>
            <select name='company_id' >
                <option value=''>Company</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCompany, $company_id)}
            </select>
        </td>
        ";

        $categoryText = "
        <td>
            <select name='position'>
                <option value=''>Position</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlPosition, $position)}
            </select>
        </td>
        ";

        $spArray = array(
              "Subscribed"
             ,"Not-Subscribed"
             ,"Flagged"
             ,"Not-Flagged"
        );

        $text = "
        {$categoryText}
        {$interestText}
        <td>
            <select name='status' >
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $SQLStatus, $status)}
            </select>
        </td>
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
            </select>
        </td>
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

        $formAction = "index.php?_topRm={$tv['topRm']}&module=hms_employee&_spAction=addNewValuelistFormSubmit&showHTML=0";

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