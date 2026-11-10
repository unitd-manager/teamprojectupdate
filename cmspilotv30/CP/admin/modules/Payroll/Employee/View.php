<?
class CP_Admin_Modules_Payroll_Employee_View extends CP_Common_Lib_ModuleViewAbstract
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

            $emp_code = '';
                if($row['emp_code'] != ''){
                    $emp_code = 'EMP-'.$row['emp_code'];
                }

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($emp_code)}
            {$listObj->getListDataCell($row['first_name'])}
            {$listObj->getListDataCell($row['mobile'])}
            {$listObj->getListDataCell($row['citizen'])}
            {$listObj->getListDataCell($row['nric_no'])}
            {$listObj->getListDataCell($row['fin_no'])}
            {$listObj->getListDateCell($row['date_of_expiry'])}
            {$listObj->getListDateCell($row['date_of_birth'])}
            {$listObj->getListDateCell($row['employee_id'], 'center')}
            {$listObj->getListRowEnd($row['employee_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('EMP Code', 'a.emp_code')}
        {$listObj->getListHeaderCell('Full Name', 'a.first_name')}
        {$listObj->getListHeaderCell('HP/Mobile No.', 'a.mobile')}
        {$listObj->getListHeaderCell('Pass Type', 'a.citizen')}
        {$listObj->getListHeaderCell('NRIC', 'a.position')}
        {$listObj->getListHeaderCell('Fin No', 'a.fin_no')}
        {$listObj->getListHeaderCell('Date of Expiry', 'a.date_of_expiry')}
        {$listObj->getListHeaderCell('Date of Birth', 'a.date_of_birth')}
        {$listObj->getListHeaderCell('ID', 'a.employee_id', 'txtCenter')}
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

        $citizenArray = array(
              "Citizen"
             ,"PR"
             ,"EP"
             ,"SP"
             ,"WP"
             ,"DP"
        );

        $status = array(
              "Current"
             ,"Archive"
             ,"Cancel"
        );

        $exp = array('hideFirstOption' => 1);

        //{$formObj->getYesNoRRow('Citizen / PR', 'is_citizen')}
        $fieldset = "
        <div class='noteHighlight'>Please note * indicates mandatory fields</div>
        {$formObj->getTBRow('Full Name *', 'first_name')}
        {$formObj->getDropDownRowByArray('Pass Type *', 'citizen', $citizenArray, '')}
        {$formObj->getTBRow('NRIC No *', 'nric_no')}
        {$formObj->getTBRow('Fin No *', 'fin_no')}
        {$formObj->getTBRow('Work Permit No', 'work_permit')}
        {$formObj->getDDRowByArr('Status *', 'status', $status,'Current',$exp)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }
    /**
     *
     */
    function getEdit($row){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $tv      = Zend_Registry::get('tv');
        $fn      = Zend_Registry::get('fn');

        $formObj->mode = $tv['action'];

        $expVL   = array('sqlType' => 'OneField');
        $sprYear = array('1'
                        ,'2'
                        ,'3');
        
        $expValuelist           = array('globalForAllSites' => true);
        $sqlGender              = $fn->getValueListSQL('gender', 'value', $expValuelist);
        //$sqlNationality         = $fn->getValueListSQL('nationality', 'value');
        $sqlRelegion            = $fn->getValueListSQL('religion', 'value', $expValuelist);
        $sqlRace                = $fn->getValueListSQL('race', 'value', $expValuelist);
        $sqlMaritalStatus       = $fn->getValueListSQL('maritalStatus', 'value', $expValuelist);
        $sqlEmployeeGroup       = $fn->getValueListSQL('employeeGroup', 'value', $expValuelist);
        $sqlQualification       = $fn->getValueListSQL('candidateQualification', 'value', $expValuelist);
        $sqlCategory            = $fn->getValueListSQL('employeeType', 'value', $expValuelist);
        $sqlSalutation          = $fn->getValueListSQL('salutation', 'value', $expValuelist);
        $sqlEmployeeWorkType    = $fn->getValueListSQL('employeeWorkType', 'value', $expValuelist);
        $sqlPosition            = $fn->getValueListSQL('positionType', 'value', $expValuelist);

        $sqlNationality         = "SELECT title FROM nationality ORDER BY title ASC";

        $degree1     = $fn->getReqParam('degree1');

        /*$spArray = array(
              "M.A"
             ,"M.Sc"
             ,"M.B.A"
        );*/

        $citizenArray = array(
              "Citizen"
             ,"PR"
             ,"EP"
             ,"SP"
             ,"WP"
             ,"DP"
        );

        $emp_code = '';
            if($row['emp_code'] != ''){
                $emp_code = 'EMP-'.$row['emp_code'];
            }

        $expDisableNric = '';
        $expDisableFin  = '';
        if($row['is_citizen'] == 0){
            $expDisableNric = array('disabled' => 1);
        }elseif($row['is_citizen'] == 1){
            $expDisableFin = array('disabled' => 1);
        }

        $expNoEdit  = array('isEditable' => 0);
        $expVl = array('sqlType' => 'OneField');
        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_name'], 'isEditable' => 0);

        if ($row['address_country'] == ''){
            $country = 'SG';
        } else {
            $country = $row['address_country'];
        }

        $formAddGroup = "index.php?_topRm={$tv['topRm']}&module=payroll_employee&_spAction=addNewValuelistForm&valuelist_name=employeeGroup&employee_id={$row['employee_id']}&showHTML=0";
        $expGroup     = array('sqlType' => 'OneField'
                            ,'notesRight' => "<a href='{$formAddGroup}' class='mr20 addNewValue' valuelist_name='employeeGroup'>Add</a>");


        $classNric='';
        $classFin='';
        $classWp='';
        $classSpr = '';
        if($row['citizen'] == 'PR' || $row['citizen'] == 'Citizen'){
            $classNric = "displayNone passType";
        }
        if($row['citizen'] == 'EP' || $row['citizen'] == 'SP' || $row['citizen'] == 'DP'){
            $classFin = "displayNone passType";
        }
        if ($row['citizen'] == 'WP') {
            $classWp = "displayNone passType";
        }

        if($row['citizen'] != 'PR'){
            $classSpr = "displayNone passType";
        }

        $expHideFirst = array('hideFirstOption' => 1);

        $status = array(
              "Current"
             ,"Archive"
             ,"Cancel"
        );

        $ir21filed = '';
        if ($row['status'] == 'Archive') {
            $ir21filed = $formObj->getYesNoRRow('IR 21 Filed', 'ir21_filed', $row['ir21_filed']);
        }

        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Step 1 (Personal Information)</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td colspan='4' class='noteHighlight'>Please note * indicates mandatory fields</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getTBRow('Code', 'emp_code', $emp_code,  $expNoEdit)}</td>
                                <td>{$formObj->getTBRow('Full Name *', 'first_name', $row['first_name'])}</td>
                                <td>{$formObj->getDDRowBySQL('Salutation', 'salutation', $sqlSalutation, $row['salutation'], $expVL)}</td>
                                <td>{$formObj->getDDRowBySQL('Gender *', 'gender', $sqlGender, $row['gender'] , $expVL)}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getDDRowByArr('Status', 'status', $status, $row['status'], $expHideFirst)}</td>
                                <td>{$formObj->getDateRow('Date of Birth (YYYY-MM-DD) *', 'date_of_birth', $row['date_of_birth'], array('yearStart' => 1950, 'yearEnd' => date('Y') + 10))}</td>
                                <td>{$formObj->getTBRow('Passport No', 'passport', $row['passport'])}</td>
                                <td>{$formObj->getDateRow('Passport Expiry (YYYY-MM-DD)', 'date_of_expiry', $row['date_of_expiry'], array('yearStart' => date('Y'), 'yearEnd' => date('Y') + 20))}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getDDRowBySQL('Marital Status', 'marital_status', $sqlMaritalStatus, $row['marital_status'], $expVL)}</td>
                                <td>{$formObj->getDDRowBySQL('Nationality *', 'nationality', $sqlNationality, $row['nationality'], $expVL)}</td>
                                <td>{$formObj->getDDRowBySQL('Race', 'race', $sqlRace, $row['race'], $expVL)}</td>
                                <td>{$formObj->getDDRowBySQL('Religion', 'religion', $sqlRelegion, $row['religion'], $expVL)}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getDDRowBySQL('Employee Group', 'employee_group', $sqlEmployeeGroup, $row['employee_group'], $expGroup)}</td>
                                <td>{$ir21filed}</td>
                            </tr>
                            <tr>
                                <th colspan='4'>Pass Type</th>
                            </tr>
                            <tr>
                                <td>{$formObj->getDropDownRowByArray('Pass Type *', 'citizen', $citizenArray, $row['citizen'])}</td>
                                <td><div class='{$classNric}'>{$formObj->getTBRow('Fin No *', 'fin_no', $row['fin_no'])}</div>
                                <div class='{$classFin} {$classWp}'>{$formObj->getTBRow('NRIC No *', 'nric_no', $row['nric_no'])}</div>
                                <div class='{$classNric} {$classFin}'>{$formObj->getTBRow('Work Permit No', 'work_permit', $row['work_permit'])}</div></td>
                                <td>
                                    <div class='{$classSpr}'>{$formObj->getDropDownRowByArray('SPR Year', 'spr_year', $sprYear, $row['spr_year'])}</div>
                                    <div class='{$classNric}'>{$formObj->getDateRow('Fin Expiry Date', 'fin_no_expiry_date', $row['fin_no_expiry_date'])}</div>
                                    <div class='{$classNric} {$classFin}'>{$formObj->getDateRow('Work Permit No Expiry Date', 'work_permit_expiry_date', $row['work_permit_expiry_date'])}</div>
                                </td>
                            </tr>

                            <tr>
                                <th colspan='4'>Educational Qualification</th>
                            </tr>
                            <tr>
                                <td>{$formObj->getDDRowBySQL('Qualification 1', 'degree1', $sqlQualification, $row['degree1'], $expVl)}</td>
                                <td>{$formObj->getTBRow('Degree', 'educational_qualitifcation1', $row['educational_qualitifcation1'])}</td>
                                <td>{$formObj->getDateRow('Year of completion', 'year_of_completion1', $row['year_of_completion1'], array('yearStart' => 1950, 'yearEnd' => date('Y')+5))}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getDDRowBySQL('Qualification 2', 'degree2', $sqlQualification, $row['degree2'], $expVl)}</td>
                                <td>{$formObj->getTBRow('Degree', 'educational_qualitifcation2', $row['educational_qualitifcation2'])}</td>
                                <td>{$formObj->getDateRow('Year of completion', 'year_of_completion2', $row['year_of_completion2'], array('yearStart' => 1950, 'yearEnd' => date('Y')+5))}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getDDRowBySQL('Qualification 3', 'degree3', $sqlQualification, $row['degree3'], $expVl)}</td>
                                <td>{$formObj->getTBRow('Degree', 'educational_qualitifcation3', $row['educational_qualitifcation3'])}</td>
                                <td>{$formObj->getDateRow('Year of completion', 'year_of_completion3', $row['year_of_completion3'], array('yearStart' => 1950, 'yearEnd' => date('Y')+5))}</td>
                            </tr>

                            <tr>
                                <th colspan='4'>Contact Information (For Citizen)</th>
                            </tr>
                            <tr>
                                <td>{$formObj->getTBRow('Address 1', 'address_area', $row['address_area'])}</td>
                                <td>{$formObj->getTBRow('Address 2', 'address_street', $row['address_street'])}</td>
                                <td>{$formObj->getTBRow('Postal Code', 'address_po_code', $row['address_po_code'])}</td>
                                <td>{$formObj->getTBRow('Country', 'address_country', 'Singapore',  $expNoEdit)}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getTBRow('HP/Mobile No.', 'mobile', $row['mobile'])}</td>
                                <td>{$formObj->getTBRow('Alternate Contact number', 'phone', $row['phone'])}</td>
                                <td>{$formObj->getTBRow('Email', 'email', $row['email'])}</td>
                            </tr>

                            <tr>
                                <th colspan='4'>Contact Information (Overseas, For Non-Citizen Or Permanent Resident)</th>
                            </tr>
                            <tr>
                                <td>{$formObj->getTBRow('Address 1', 'foreign_addrs_area', $row['foreign_addrs_area'])}</td>
                                <td>{$formObj->getTBRow('Address 2', 'foreign_addrs_street', $row['foreign_addrs_street'])}</td>
                                <td>{$formObj->getDDRowBySQL('Country', 'foreign_addrs_country', $sqlCountry, $row['foreign_addrs_country'], $expCountry)}</td>
                                <td>{$formObj->getTBRow('Postal Code', 'foreign_addrs_postal_code', $row['foreign_addrs_postal_code'])}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getTBRow('HP/Mobile No.', 'foreign_mobile', $row['foreign_mobile'])}</td>
                                <td>{$formObj->getTBRow('Alternate Contact number', 'phone_direct', $row['phone_direct'])}</td>
                                <td>{$formObj->getTBRow('Email', 'foreign_email', $row['foreign_email'])}</td>
                            </tr>

                            <tr>
                                <th colspan='4'>Emergency Contact</th>
                            </tr>
                            <tr>
                                <td>{$formObj->getTBRow('Name', 'emergency_contact_name', $row['emergency_contact_name'])}</td>
                                <td>{$formObj->getTBRow('Phone 1', 'emergency_contact_phone', $row['emergency_contact_phone'])}</td>
                                <td>{$formObj->getTBRow('Phone 2', 'emergency_contact_phone2', $row['emergency_contact_phone2'])}</td>
                                <td>{$formObj->getTBRow('Address', 'emergency_contact_address', $row['emergency_contact_address'])}</td>
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
    */
    function getEditold($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode  = $tv['action'];

       // $chineseName    = '';
        //$chinesePos     = '';
        //$chineseDept    = '';
        $compAddressDD  = '';
        $companyAddress = '';
        $staffDetail    = '';
        $personalAdd    = '';
        $compLink       = '';

        $expVL = array('sqlType' => 'OneField');
        $expValuelist           = array('globalForAllSites' => true);
        $sqlGender              = $fn->getValueListSQL('gender', '', $expValuelist);
        $sqlNationality         = $fn->getValueListSQL('nationality', '', $expValuelist);
        $sqlRelegion            = $fn->getValueListSQL('relegion', '', $expValuelist);
        $sqlRace                = $fn->getValueListSQL('race', '', $expValuelist);
        $sqlMaritalStatus       = $fn->getValueListSQL('marital_status', '', $expValuelist);
        $sqlCategory            = $fn->getValueListSQL('employeeType', '', $expValuelist);
        $sqlSalutation          = $fn->getValueListSQL('Salutation', '', $expValuelist);
        $sqlEmployeeWorkType    = $fn->getValueListSQL('employeeWorkType', '', $expValuelist);
        $sqlPosition            = $fn->getValueListSQL('positionType', 'value', $expValuelist);
        $sqlComp                = $fn->getDDSql('enggCrm_company');

       /* if ($cpCfg['m.enggCrm.employee.showChineseFields'] == 1){
            $chineseName = $formObj->getTBRow('Name (Chinese)', 'chi_name', $row['chi_name']);
            $chinesePos  = $formObj->getTBRow('Position (Chinese)', 'chi_position', $row['chi_position']);
            $chineseDept = $formObj->getTBRow('Department (Chinese)', 'chi_department', $row['chi_department']);
        }*/

        $degree1     = $fn->getReqParam('degree1');

        $spArray = array(
              "M.A"
             ,"M.Sc"
             ,"M.B.A"
        );

        if ($tv['action'] == 'edit'){
            if($cpCfg['m.project.hasMultipleCompanyAddress'] == 1){
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

        if ($cpCfg['m.project.employee.showDetail'] == 1){
            $sqlCombo = "
            SELECT staff_id
                  ,CONCAT_WS(' ', a.first_name, a.last_name) AS staff_name
            FROM staff a
            ORDER BY staff_name";

            /*$fieldset = "
            {$formObj->getDDRowBySQL("{$cpCfg['m.enggCrm.staffFieldLabel']}", "staff_id", $sqlCombo, $row['staff_id'])}
            ";

            $staffDetail = $formObj->getFieldSetWrapped($cpCfg['m.enggCrm.staffFieldLabel'], $fieldset);*/
        }


        $expNoEdit  = array('isEditable' => 0);
        $expVl = array('sqlType' => 'OneField');

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_name']);

        if ($row['foreign_addrs_country'] == ''){
            $country = 'SG';
        } else {
            $country = $row['foreign_addrs_country'];
        }

        //$formAddPosition = "index.php?_topRm={$tv['topRm']}&module=payroll_employee&_spAction=addNewValuelistForm&valuelist_name=positionType&employee_id={$row['employee_id']}&showHTML=0";
        /*$expPosition     = array('sqlType' => 'OneField'
                            ,'notesRight' => "<a href='{$formAddPosition}' class='mr20 addNewValue' valuelist_name='positionType'>Add</a>");*/

        $fielset1 = "
        {$formObj->getTBRow('Code', 'emp_code', $row['emp_code'],  $expNoEdit)}
        {$formObj->getTBRow('Name', 'employee_name', $row['employee_name'])}
        {$formObj->getDDRowBySQL('Salutation', 'salutation', $sqlSalutation, $row['salutation'], $expVL)}
        {$formObj->getDDRowBySQL('Gender', 'gender', $sqlGender, $row['gender'] , $expVL)}
        {$formObj->getDateRow('Date of Birth', 'date_of_birth', $row['date_of_birth'])}
        {$formObj->getDDRowBySQL('Marital Status', 'marital_status', $sqlMaritalStatus, $row['marital_status'], $expVL)}
        {$formObj->getYesNoRRow('Citizen / PR', 'is_citizen', $row['is_citizen'])}
        {$formObj->getTBRow('Fin/Work Permit No', 'fin_no', $row['fin_no'])}
        {$formObj->getTBRow('NRIC No', 'nric_no', $row['nric_no'])}
        {$formObj->getTBRow('Passport No', 'passport', $row['passport'])}
        {$formObj->getDDRowBySQL('Nationality', 'nationality', $sqlNationality, $row['nationality'], $expVL)}
        {$formObj->getDDRowBySQL('Relegion', 'religion', $sqlRelegion, $row['religion'], $expVL)}
        {$formObj->getDDRowBySQL('Race', 'race', $sqlRace, $row['race'], $expVL)}
        ";

        /*if ($formObj->mode == 'edit'){
            $compLink = "<a class='editLinkSingle' href='' link='{$fn->getOpenLinkUrl('payroll_employee', 'payroll_companyLink', 'fld_company_id')}'>Choose</a>";
        }
        $expComp  = array('notesRight' => $compLink, 'detailValue' => $row['c_company_name']);*/

        $fielset2 = "
        {$formObj->getDDRowByArr('Qualification 1', 'degree1', $spArray, $row['degree1'])}
        {$formObj->getTBRow('Degree', 'educational_qualitifcation1', $row['educational_qualitifcation1'])}
        {$formObj->getDateRow('Year of completion', 'year_of_completion1', $row['year_of_completion1'])}
        {$formObj->getDDRowByArr('Qualification 2', 'degree2', $spArray, $row['degree2'])}
        {$formObj->getTBRow('Degree', 'educational_qualitifcation2', $row['educational_qualitifcation2'])}
        {$formObj->getDateRow('Year of completion', 'year_of_completion2', $row['year_of_completion2'])}
        {$formObj->getDDRowByArr('Qualification 3', 'degree3', $spArray, $row['degree3'])}
        {$formObj->getTBRow('Degree', 'educational_qualitifcation3', $row['educational_qualitifcation3'])}
        {$formObj->getDateRow('Year of completion', 'year_of_completion3', $row['year_of_completion3'])}
        ";

        $fielset3 = "
        {$formObj->getTBRow('Address 1', 'foreign_addrs_area', $row['foreign_addrs_area'])}
        {$formObj->getTBRow('Address 2', 'foreign_addrs_street', $row['foreign_addrs_street'])}
        {$formObj->getDDRowBySQL('Country', 'foreign_addrs_country', $sqlCountry, $country, $expCountry)}
        {$formObj->getTBRow('Postal Code', 'address_po_code', $row['address_po_code'])}
        {$formObj->getTBRow('HP/Mobile No.', 'mobile', $row['mobile'])}
        {$formObj->getTBRow('Alternate Contact number', 'phone', $row['phone'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
       ";

        $fielset4 = "
        {$formObj->getTBRow('Address 1', 'address_flat', $row['address_flat'])}
        {$formObj->getTBRow('Address 2', 'address_street', $row['address_street'])}
        {$formObj->getDDRowBySQL('Country', 'address_country', $sqlCountry, $country, $expCountry)}
        {$formObj->getTBRow('Postal Code', 'address_po_code', $row['address_po_code'])}
        {$formObj->getTBRow('HP/Mobile No.', 'mobile', $row['mobile'])}
        {$formObj->getTBRow('Alternate Contact number', 'phone', $row['phone'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
       ";

        $fielset5 = "
        {$formObj->getTBRow('Name', 'emergency_contact_name', $row['emergency_contact_name'])}
        {$formObj->getTBRow('Phone 1', 'emergency_contact_phone', $row['emergency_contact_phone'])}
        {$formObj->getTBRow('Phone 2', 'emergency_contact_phone2', $row['emergency_contact_phone2'])}
        {$formObj->getTBRow('Address', 'emergency_contact_address', $row['emergency_contact_address'])}
       ";

        $text = "
        {$formObj->getFieldSetWrapped('Personal Information', $fielset1)}
        {$formObj->getFieldSetWrapped('Educational Qualification', $fielset2)}
        {$formObj->getFieldSetWrapped('Contact Information (In Citizen)', $fielset3)}
        {$formObj->getFieldSetWrapped('Contact Information (Overseas, For Non-Citizen Or Permanent Resident)', $fielset4)}
        {$formObj->getFieldSetWrapped('Emergency Contact:', $fielset5)}
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

        /*
        if( $cpCfg['m.project.employee.showEvent'] == "1"){
            $rows .= $displayLinkData->getLinkPortalMain("payroll_employee", "event_eventLink", "Events Linked", $row);
        }
        */

        $record_id = $fn->getIssetParam($row, 'employee_id');

        $text = "
        {$media->getRightPanelMediaDisplay("Picture", "payroll_employee", "picture", $row)}
        {$displayLinkData->getLinkPortalMain("payroll_employee", "payroll_jobInformationLink", "Job Information History", $row)}
        {$displayLinkData->getLinkPortalMain("payroll_employee", "payroll_trainingLink", "Training Linked", $row)}
        {$rows}
        {$comment->getView(array(
             'roomName' => 'payroll_employee'
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

        $special_search      = $fn->getReqParam('special_search');
        $employee_work_type  = $fn->getReqParam('employee_work_type');
        $employee_status     = $fn->getReqParam('employee_status');
        $pass_type           = $fn->getReqParam('pass_type');
        
        //$sqlEmployeeWorkType = $fn->getValueListSQL('employeeWorkType');
        $empTypeArray = array(
              "Full Time"
             ,"Part Time"
             ,"Contract"
        );
        $spArray = array(
              "Subscribed"
             ,"Not-Subscribed"
             ,"Flagged"
             ,"Not-Flagged"
        );
        $status = array(
              "Current"
             ,"Archive"
             ,"Cancel"
        );
        $citizenArray = array(
              "Citizen"
             ,"PR"
             ,"EP"
             ,"SP"
             ,"WP"
             ,"DP"
        );

        if($employee_status == ''){
            $employee_status = 'Current';
        }

        $text = "
        <td>
            <select name='pass_type'>
                <option value=''>Pass Type</option>
                {$cpUtil->getDropDown1($citizenArray, $pass_type)}
            </select>
        </td>
        <td>
            <select name='employee_work_type' >
                <option value=''>Employee Work Type</option>
                {$cpUtil->getDropDown1($empTypeArray, $employee_work_type)}
            </select>
        </td>
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
            </select>
        </td>
        <td>
            <select name='employee_status'>
                <option value=''>Employee Status</option>
                {$cpUtil->getDropDown1($status, $employee_status)}
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

        $formAction = "index.php?_topRm={$tv['topRm']}&module=payroll_employee&_spAction=addNewValuelistFormSubmit&showHTML=0";

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