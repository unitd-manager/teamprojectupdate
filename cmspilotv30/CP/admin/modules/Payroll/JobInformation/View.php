<?
class CP_Admin_Modules_Payroll_JobInformation_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $cpCfg   = Zend_Registry::get('cpCfg');

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
            {$listObj->getGoToDetailText($count, $emp_code)}
            {$listObj->getGoToDetailText($count, $row['first_name'])}
            {$listObj->getListDataCell($row['department'])}
            {$listObj->getListDataCell($row['spass_no'])}
            {$listObj->getListDataCell($row['fin_no'])}
            {$listObj->getListDataCell($row['nric_no'])}
            {$listObj->getListDateCell($row['date_of_birth'])}
            {$listObj->getListDataCell($row['basic_pay'])}
            {$listObj->getListDataCell($row['citizen'])}
            {$listObj->getListDateCell($row['job_information_id'], 'center')}
            {$listObj->getListRowEnd($row['employee_id'])}
            ";

            $count++ ;
        }

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlSite1 = "";
        $appendSqlSite2 = "";
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite1 = "AND e.site_id = {$cpSiteIdSession}";
            $appendSqlSite2 = "AND ji.site_id = {$cpSiteIdSession}";
        }

        /* Find the employees for whom Job information is not created */
        $sqlEmp = "SELECT e.first_name 
                   FROM employee e
                   WHERE e.status = 'Current'
                   {$appendSqlSite1}
                   AND e.employee_id NOT IN 
                   (SELECT ji.employee_id
                    FROM job_information ji
                    WHERE e.employee_id = ji.employee_id
                      AND ji.status = 'Current'
                      {$appendSqlSite2})
                   ";
        $resultEmp = $db->sql_query($sqlEmp);
        $numRowsEmp = $db->sql_numrows($resultEmp);
        $rowsEmp = '';
        $count = 1;
        while($rowEmp = $db->sql_fetchrow($resultEmp)) {
            if ($count == $numRowsEmp) {
                $rowsEmp .= $rowEmp['first_name'];
            } else {
                $rowsEmp .= $rowEmp['first_name'] . ', ';
            }
            $count++;
        }

        $message = '';
        if ($numRowsEmp) {
            $message = "
            <div class='txtCenter'>Please create Job information records for the below employees to make them appear in payroll.<br/>
            {$rowsEmp}
            </div>
            ";
        }

        $text = "
        {$message}
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('EMP Code', 'a.emp_code')}
        {$listObj->getListHeaderCell('Full Name', 'e.first_name')}
        {$listObj->getListHeaderCell('Department', 'j.department')}
        {$listObj->getListHeaderCell('S Pass No', 'e.spass_no')}
        {$listObj->getListHeaderCell('FIN No', 'e.fin_no')}
        {$listObj->getListHeaderCell('NRIC No', 'e.nric_no')}
        {$listObj->getListHeaderCell('DOB', 'e.date_of_birth')}
        {$listObj->getListHeaderCell('Basic Pay', 'j.basic_pay')}
        {$listObj->getListHeaderCell('Pass Type', 'e.citizen')}
        {$listObj->getListHeaderCell('ID', 'j.job_information_id', 'txtCenter')}
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

        $fieldset = "
        {$formObj->getTBRow('Employee Name', 'employee_name')}
        <input type='hidden' name='employee_id' value=''>
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
        $cpCfg   = Zend_Registry::get('cpCfg');
        $tv      = Zend_Registry::get('tv');
        $fn      = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode = $tv['action'];

        $expValuelist   = array('globalForAllSites' => true);
        $sqldepartment  = $fn->getValueListSQL('department', '', $expValuelist);
        $sqldesignation = $fn->getValueListSQL('designation', '', $expValuelist);

        $expNoEdit = array('isEditable' => 0);
        $expVl     = array('sqlType' => 'OneField');

        /*
        $designation  = $fn->getReqParam('designation');
        $designation = array(
              "Programmer"
             ,"Developer"
        );
        */

        $status = array(
              "Current"
             ,"Archive"
             ,"Cancel"
        );
        $emp_type  = $fn->getReqParam('emp_type');

        $emp_typeArray = array(
              "Full Time"
             ,"Part Time"
             ,"Contract"
        );

        $payment_type  = $fn->getReqParam('payment_type');

        $paymenttypeArray = array(
              "Monthly"
             ,"Fortnightly"
             ,"Weekly"
             ,"Daily"
             ,"Hourly"
        );

        $working_days  = $fn->getReqParam('working_days');

        $workingDaysArr = array('useKey' => 1);
        $workingdaysArray = array(
            "5.0" => "5"
           ,"5.5" => "5.5"
           ,"6.0" => "6"
        );

        $mode_of_payment  = $fn->getReqParam('mode_of_payment');

        $paymentArray = array(
              "cheque"
             ,"cash"
             ,"giro payment transfer"
        );

        $govtDonationArray = array(
              "CDAC"
             ,"SINDA"
             ,"MBMF"
             ,"EUCF"
        );

        $expCdac = array('rowCls' => 'hideme');
        if ($row['govt_donation'] == 'CDAC') {
            $expCdac = array();            
        }

        $expSinda = array('rowCls' => 'hideme');
        if ($row['govt_donation'] == 'SINDA') {
            $expSinda = array();            
        }

        $expMbmf = array('rowCls' => 'hideme');
        if ($row['govt_donation'] == 'MBMF') {
            $expMbmf = array();            
        }

        $expEucf = array('rowCls' => 'hideme');
        if ($row['govt_donation'] == 'EUCF') {
            $expEucf = array();            
        }

        $expHide = array('rowCls' => 'hideme');
        if ($row['probationary'] == 1) {
          $expHide = array();
        }

        $bank_name  = $fn->getReqParam('bank_name');

        $BankArray = array(
              "Australia & NewZealand Banking Group Ltd"
             ,"BNP paribas"
             ,"Bangkok Bank Public Company Ltd(SIN)"
             ,"Bank Of America(SIN)"
             ,"Bank Of China(SIN)"
             ,"Bank Of India(SIN)"
             ,"Bank Of Singapore Ltd(SIN)"
             ,"Bank Of Tokyo-Mitsubishi(SIN)"
             ,"CIMB Bank Berhad(SIN)"
             ,"Chung Khiaw Bank(SIN)"
             ,"CityBank(SIN)"
             ,"Credit Agricole Corporate & Investment Bank(SIN)"
             ,"DBS / POSB(SIN)"
             ,"Deutsche Bank AG(SIN)"
             ,"Far Eastern Bank(SIN)"
             ,"HL Bank(SIN)"
             ,"HSBC(SIN)"
             ,"ICIC Bank Ltd"
             ,"Indian Bank"
             ,"Indian Overseas Bank"
             ,"Industrial & Commercial Bank(SIN)"
             ,"Industrial & Commercial Bank Of China"
             ,"J.P.Morgan Chase Bank / Chase Manhattan Bank"
             ,"May Bank / Malayan Banking Berhad(SIN)"
             ,"Mizuho Corporate Bank Ltd"
             ,"National Australia Bank Ltd"
             ,"OCBC(SIN)"
             ,"P.T.Bank Negara Indonesia(Persero) Tbk(SIN)"
             ,"RHB Bank Berhad(SIN)"
             ,"southern Bank Berhad(SIN)"
             ,"Standard Chartered Bank(SIN)"
             ,"State Bank Of India"
             ,"Sumitomo Mitsui Banking Corporation"
             ,"The Bank Of East Asia(SIN)"
             ,"The Royal Bank Of Scotland N.V / ABN AMRO(SIN)"
             ,"UBS AG"
             ,"UCO Bank(SIN)"
             ,"United Overseas Bank Ltd(SIN)"
        );

        if ($row['citizen'] == 'Citizen' || $row['citizen'] == 'PR') {
            $id_no = "NRIC No: {$row['nric_no']}";
        } else {
            $id_no = "FIN No: {$row['fin_no']}";
        }

        $urlPrintLinkPdf  = "index.php?_topRm=payroll&module=payroll_jobInformation&_spAction=kETPdf&job_information_id={$row['job_information_id']}&showHTML=0";
        $urlContract = "index.php?module=payroll_jobInformation&_spAction=printEmploymentContract&job_information_id={$row['job_information_id']}&showHTML=0";
        $printPdf = "
        <div class='floatbox editTopButtonActionDiv'>
            <div class='float_right m5'>
                <a href='{$urlPrintLinkPdf}' target='_blank' class='btn btn-info button ml10'>Print KET</a>
                <a href='{$urlContract}' class='btn btn-info button ml10'>
                    Print Employment Contract
                </a>
            </div>
        </div>";

        $text = "
        {$printPdf}
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>
                        <div>Step 1 (Job Information)</div>
                    </div>
                    <div class='float_left'>
                        <div>Employee Name: {$row['employee_name']}</div>
                    </div>
                    <div class='float_left'>
                        <div>{$id_no}</div>
                    </div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <th colspan='5'>Details of Employment (KET)</th>
                            </tr>
                            <tr>
                                <td>{$formObj->getDateRow('Employment Start/Commencement Date<br/>(YYYY-MM-DD)', 'act_join_date', $row['act_join_date'])}</td>
                                <td>{$formObj->getTBRow('Duties & Responsibility<br><br>', 'duty_responsibility', $row['duty_responsibility'])}</td>
                                <td>{$formObj->getTBRow('Duration of Employment<br>(only for employees on fixed term contract)', 'duration_of_employment', $row['duration_of_employment'])}</td>
                                <td colspan='2'>{$formObj->getTBRow('Place of Work<br>(if different from companys registered address)', 'place_of_work', $row['place_of_work'])}</td>
                            </tr>
                            <tr>
                                <th colspan='5'>Working hours & Rest Days (KET)</th>
                            </tr>
                            <tr>
                                <td colspan='3'>{$formObj->getTARow('Details of Working Hours', 'work_hour_details', $row['work_hour_details'])}</td>
                                <td colspan='2'>{$formObj->getTBRow('Rest day per Week', 'rest_day_per_week', $row['rest_day_per_week'])}</td>
                            </tr>
                            <tr>
                                <th colspan='5'>Leave and Medical Benefits (KET)</th>
                            </tr>
                            <tr>
                                <td>{$formObj->getTBRow('Paid Annual Leave per year', 'paid_annual_leave_per_year', $row['paid_annual_leave_per_year'])}</td>
                                <td>{$formObj->getTBRow('Paid Outpatient Sick Leave per year', 'paid_outpatient_sick_leave_per_year', $row['paid_outpatient_sick_leave_per_year'])}</td>
                                <td>{$formObj->getTBRow('Paid Hospitalisation Leave per year', 'paid_hospitalisation_leave_per_year', $row['paid_hospitalisation_leave_per_year'])}</td>
                                <td colspan='2'>{$formObj->getYesNoRRow('Paid medical examination fee', 'paid_medical_examination_fee', $row['paid_medical_examination_fee'])}</td>
                            </tr>
                            <tr>
                                <td colspan='3'>{$formObj->getTARow('Other types of leave', 'other_type_of_leave', $row['other_type_of_leave'])}</td>
                                <td colspan='2'>{$formObj->getTARow('Other Medical Benefits', 'other_medical_benefits', $row['other_medical_benefits'])}</td>
                            </tr>
                            <tr>
                                <th colspan='5'>Probation Details (KET)</th>
                            </tr>
                            <tr>
                                <td>{$formObj->getYesNoRRow('Under Probation', 'probationary', $row['probationary'])}</td>
                                <td>{$formObj->getTBRow('Length of Probation', 'length_of_probation', $row['length_of_probation'], $expHide)}</td>
                                <td>{$formObj->getDateRow('Probation Start Date<br/>(YYYY-MM-DD)', 'probation_start_date', $row['probation_start_date'], $expHide)}</td>
                                <td>{$formObj->getDateRow('Probation End Date<br/>(YYYY-MM-DD)', 'probation_end_date', $row['probation_end_date'], $expHide)}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>{$formObj->getDDRowByArr('Employment Type', 'emp_type', $emp_typeArray, $row['emp_type'])}</td>
                                <td>{$formObj->getDDRowBySQL('Designation', 'designation', $sqldesignation, $row['designation'], $expVl)}</td>
                                <td>{$formObj->getDDRowBySQL('Department', 'department', $sqldepartment, $row['department'], $expVl)}</td>
                                <td>{$formObj->getDateRow('First Joined Date (YYYY-MM-DD)*', 'join_date', $row['join_date'])}</td>
                                <td width='15%'>{$formObj->getDDRowByArr('Status', 'status', $status, $row['status'])}</td>
                            </tr>
                            <tr>
                                <th colspan='5'>Salary Information</th>
                            </tr>
                            <tr>
                                <td>{$formObj->getDDRowByArr('Salary Period', 'payment_type', $paymenttypeArray, $row['payment_type'])}</td>
                                <td>{$formObj->getTBRow('Date(s) of Salary Payment', 'salary_payment_dates', $row['salary_payment_dates'])}</td>
                                <td>{$formObj->getTBRow('Date(s) of Overtime Payment (if different)', 'overtime_payment_dates', $row['overtime_payment_dates'])}</td>
                                <td colspan='2'></td>
                            </tr>
                            <tr>
                                <td>{$formObj->getDDRowByArr('Working Calendar(No of Days/Week)(KET) *', 'working_days', $workingdaysArray, $row['working_days'], $workingDaysArr)}</td>
                                <td>{$formObj->getTBRow('Basic Pay *', 'basic_pay', $row['basic_pay'])}</td>
                                <td>{$formObj->getYesNoRRow('Overtime Applicable', 'overtime', $row['overtime'])}</td>
                                <td colspan='2'>{$formObj->getTBRow('Overtime Pay Rate/ Hour', 'overtime_pay_rate', $row['overtime_pay_rate'], $expNoEdit)}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getTBRow($cpCfg['m.jobInformation.allowance1Lbl'], 'allowance1', $row['allowance1'])}</td>
                                <td>{$formObj->getTBRow($cpCfg['m.jobInformation.allowance2Lbl'], 'allowance2', $row['allowance2'])}</td>
                                <td>{$formObj->getTBRow($cpCfg['m.jobInformation.allowance3Lbl'], 'allowance3', $row['allowance3'])}</td>
                                <td>{$formObj->getTBRow($cpCfg['m.jobInformation.allowance4Lbl'], 'allowance4', $row['allowance4'])}</td>
                                <td>{$formObj->getTBRow($cpCfg['m.jobInformation.allowance5Lbl'], 'allowance5', $row['allowance5'])}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getTBRow($cpCfg['m.jobInformation.deduction1Lbl'], 'deduction1', $row['deduction1'])}</td>
                                <td>{$formObj->getTBRow($cpCfg['m.jobInformation.deduction2Lbl'], 'deduction2', $row['deduction2'])}</td>
                                <td>{$formObj->getTBRow($cpCfg['m.jobInformation.deduction3Lbl'], 'deduction3', $row['deduction3'])}</td>
                                <td colspan='2'></td>
                            </tr>

                            <tr>
                                <th colspan='5'>CPF Information</th>
                            </tr>
                            <tr>
                                <td>{$formObj->getYesNoRRow('CPF Applicable', 'cpf_applicable', $row['cpf_applicable'])}</td>
                                <td>{$formObj->getDDRowByArr('Govt donation *', 'govt_donation', $govtDonationArray, $row['govt_donation'])}</td>
                                <td>{$formObj->getTBRow('Income Tax No', 'income_tax_id', $row['income_tax_id'])}</td>
                                <td>{$formObj->getTBRow('Income Tax Amount', 'income_tax_amount', $row['income_tax_amount'])}</td>
                                <td>{$formObj->getTBRow('CPF No', 'cpf_account_no', $row['cpf_account_no'])}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getTBRow('Pay CDAC', 'pay_cdac', $row['pay_cdac'], $expCdac)}</td>
                                <td>{$formObj->getTBRow('Pay SINDA', 'pay_sinda', $row['pay_sinda'], $expSinda)}</td>
                                <td>{$formObj->getTBRow('Pay MBMF', 'pay_mbmf', $row['pay_mbmf'], $expMbmf)}</td>
                                <td>{$formObj->getTBRow('Pay EUCF', 'pay_eucf', $row['pay_eucf'], $expEucf)}</td>
                                <td></td>
                            </tr>

                            <tr>
                                <th colspan='5'>Bank Information</th>
                            </tr>
                            <tr>
                                <td>{$formObj->getDDRowByArr('Mode of Payment', 'mode_of_payment', $paymentArray, $row['mode_of_payment'])}</td>
                                <td>{$formObj->getTBRow('Account No', 'account_no', $row['account_no'])}</td>
                                <td>{$formObj->getDDRowByArr('Bank Name', 'bank_name', $BankArray, $row['bank_name'])}</td>
                                <td>{$formObj->getTBRow('Bank Code', 'bank_code', $row['bank_code'])}</td>
                                <td>{$formObj->getTBRow('Branch Code', 'branch_code', $row['branch_code'])}</td>
                            </tr>
                            <tr>
                                <th colspan='5'>Termination Information</th>
                            </tr>
                            <tr>
                                <td>{$formObj->getTARow('Notice Period for Termination', 'notice_period_for_termination', $row['notice_period_for_termination'])}</td>
                                <td>{$formObj->getDateRow('Date of Resignation Notice (YYYY-MM-DD)', 'resignation_notice_date', $row['resignation_notice_date'])}</td>
                                <td>{$formObj->getDateRow('Termination/Cessation Date (YYYY-MM-DD)', 'termination_date', $row['termination_date'])}</td>
                                <td>{$formObj->getTARow('Reason for Termination', 'termination_reason', $row['termination_reason'])}</td>
                                <td>{$formObj->getDateRow('Departure Date (YYYY-MM-DD)', 'departure_date', $row['departure_date'])}</td>
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

        $sqlCompany = $fn->getDDSql('enggCrm_company');

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
        {$formObj->getDDRowByArr('Special Search', 'special_search', $spArray)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Contact Details', $fielset1)}
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
        if( $cpCfg['m.project.jobInformation.showEvent'] == "1"){
            $rows .= $displayLinkData->getLinkPortalMain("payroll_jobInformation", "event_eventLink", "Events Linked", $row);
        }
        */

        $record_id = $fn->getIssetParam($row, 'job_information_id');

        $text = "
        {$media->getRightPanelMediaDisplay('Attachments', 'payroll_jobInformation', 'attachment', $row)}
        {$rows}
        {$comment->getView(array(
             'roomName' => 'payroll_jobInformation'
            ,'recordId' => $record_id
        ))}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db     = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $tv     = Zend_Registry::get('tv');
        $fn     = Zend_Registry::get('fn');

        $special_search  = $fn->getReqParam('special_search');
        $company_id      = $fn->getReqParam('company_id');
        $category        = $fn->getReqParam('category');
        $employee_id     = $fn->getReqParam('employee_id');
        $employee_status = $fn->getReqParam('employee_status');
        $status          = $fn->getReqParam('status');
 
        if ($tv['searchDone'] == 0){
            $status = 'Current';
        }

        //==================================================================//
        $companyText  = "";
        $categoryText = "";
        $interestText = "";

        $expValuelist = array('globalForAllSites' => true);
        //$sqlCompany   = $fn->getDDSql('enggCrm_company');
        $SQLCategory  = $fn->getValueListSQL('contactCategory', '', $expValuelist);

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlSite = "";
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND e.site_id = {$cpSiteIdSession}";
        }

        $sqlEmployeeName = "
        SELECT e.employee_id
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
        FROM employee e
        WHERE e.status = 'Current'
        {$appendSqlSite}
        ORDER BY employee_name
        ";

        /*$companyText = "
        <td>
            <select name='company_id' >
                <option value=''>Company</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCompany, $company_id)}
            </select>
        </td>
        ";*/

        //==================================================================//
        $spArray = array(
              "Flagged"
             ,"Not-Flagged"
        );

        $employeeStatusArr = array(
              "Current"
             ,"Archive"
             ,"Cancel"
        );

        $jobStatusArr = array(
              "Current"
             ,"Archive"
             ,"Cancel"
        );

        if($employee_status == ''){
            $employee_status = 'Current';
        }

        if($status == ''){
            $status = 'Current';
        }

        $text = "
        <td>
            <select name='employee_id' >
                <option value=''>Employee Name</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlEmployeeName, $employee_id)}
            </select>
        </td>
        {$categoryText}
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
            </select>
        </td>
        <td>
            <select name='employee_status'>
                <option value=''>Employee Status</option>
                {$cpUtil->getDropDown1($employeeStatusArr, $employee_status)}
            </select>
        </td>
        <td>
            <select name='status'>
                <option value=''>Job Status</option>
                {$cpUtil->getDropDown1($jobStatusArr, $status)}
            </select>
        </td>
        ";

        return $text;
    }
}