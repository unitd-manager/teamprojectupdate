<?
class CP_Admin_Modules_Payroll_PayrollManagement_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $listObj = Zend_Registry::get('listObj');
        $dateUtil = Zend_Registry::get('dateUtil');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){

            $dob = $fn->getCPDate($row['dob'], 'Y');
            $age = date('Y')- $dob;
            $year = date('Y');
            
            $OT  = $row['ot_hours'] * $row['overtime_pay_rate'];
            $gross_pay = $row['basic_pay'] + $row['ot_amount'] + $row['commission'] + $row['allowance1'] + $row['allowance2'] + $row['allowance3'] + $row['allowance4'] + $row['allowance5'];

            $total_allowance = $row['allowance1'] + $row['allowance2'] + $row['allowance3'] + $row['allowance4'] + $row['allowance5'];
            $total_allowance_display = number_format($total_allowance, 2);

            $total_deduction = round($row['cpf_employee'], 2) + $row['sdl'] + $row['loan_amount'] + $row['income_tax_amount'] + $row['pay_cdac'] + $row['pay_sinda'] + $row['pay_mbmf'] + $row['pay_eucf'] + $row['deduction1'] + $row['deduction2'] + $row['deduction3'] + $row['loan_deduction'];
            $total_deduction_display = number_format($total_deduction, 2);
            
            $net_total = $gross_pay - $total_deduction + $row['reimbursement'] + $row['director_fee'];
            $net_total = number_format($net_total, 2);
            $urlPrintLinkPdf  = "index.php?_topRm=payroll&module=payroll_payrollManagement&_spAction=payslipprintPdf&payroll_management_id={$row['payroll_management_id']}&employee_id={$row['employee_id']}&showHTML=0";

            $dob = '';
            if ($row['dob']) {
                $dob = '(' . $dateUtil->formatDate($row['dob'], 'DD MMM YYYY') . ')';
            }

            $employee_name = "<a href='index.php?_topRm={$tv['topRm']}&module=payroll_employee&record_id={$row['employee_id']}&_action=edit'>{$row['employee_name']} {$dob}</a>";
            
            $payroll_month = $dateUtil->getShortMonthName($row['payroll_month']);

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($employee_name)}
            {$listObj->getListDataCell("<a href='{$urlPrintLinkPdf}' target='_blank'>Payslip print</a>")}
            {$listObj->getListDataCell($payroll_month, 'center')}
            {$listObj->getListDataCell($row['payroll_year'], 'center')}
            {$listObj->getListDataCell($row['basic_pay'], 'right')}
            {$listObj->getListDataCell($OT)}
            {$listObj->getListDataCell(number_format($row['cpf_employer'], 2), 'right')}
            {$listObj->getListDataCell(number_format($row['cpf_employee'], 2), 'right')}
            {$listObj->getListDataCell($total_allowance_display, 'right')}
            {$listObj->getListDataCell($total_deduction_display, 'right')}
            {$listObj->getListDataCell($net_total, 'right')}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDateCell($row['payroll_management_id'], 'center')}
            {$listObj->getListRowEnd($row['payroll_management_id'])}
            ";

            $count++ ;
        }

        $printPdfLink = "<a href='#' class='button printPayslipForAllLink'>
                            Print All Payslip
                        </a>";

        //$search_List    = "index.php?_topRm=main&module=payroll_jobInformation";
        $current_Month = date('m') - 1;
        if ($current_Month <= 9 && $current_Month > 0) {
            $current_Month = 0 . $current_Month;
        } else if ($current_Month == 0) {
            $current_Month = 12;
        } else {
            $current_Month = $current_Month;
        }

        if ($current_Month == 12) {
            $current_Year  = date('Y') - 1;
        } else {
            $current_Year  = date('Y');
        }

        /* For Manual creation */
        //$current_Month = '12';
        //$current_Year  = '2019';
        /* For Manual creation */

        /* Find the employees for whom Job information is not created */
        $sqlEmp = "SELECT e.first_name 
                   FROM employee e
                   WHERE e.status = 'Current'
                   AND e.employee_id NOT IN 
                   (SELECT ji.employee_id
                    FROM job_information ji
                    WHERE e.employee_id = ji.employee_id
                      AND ji.status = 'Current')
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

        $sqlPm = "
        SELECT pm.payroll_management_id FROM payroll_management pm
        LEFT JOIN (employee e) ON (pm.employee_id = e.employee_id)
        LEFT JOIN (job_information ji) ON (e.employee_id = ji.employee_id)
         WHERE pm.payroll_month = '{$current_Month}'
           AND pm.payroll_year = '{$current_Year}'
           AND e.status = 'Current'
           AND ji.termination_date = ''
        ";
        $resultPm = $db->sql_query($sqlPm);
        $recCount = $db->sql_numrows($resultPm);

        //$recCount = $fn->getRecordCount('payroll_management', "payroll_month = '{$current_Month}' AND payroll_year = '{$current_Year}'");
        $text = "
        {$message}
            <div class='floatbox m10'>
                <div class='float_left'>
                    <a current_Month = '{$current_Month}' record_count='{$recCount}' current_Year = '{$current_Year}'  class='button GenerateRecords'>Generate Payslips</a>
                </div>
                <div class='float_left'>
                    {$printPdfLink}
                </div>
                <div class='float_left'>
                    <a class='button GenerateTerminatingRecords'>Generate Terminating Payslips</a>
                </div>
            </div>

        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Employee Name', 'e.first_name')}
        {$listObj->getListHeaderCell('Pay slip print', '$urlPrintLinkPdf')}
        {$listObj->getListHeaderCell('Month', 'pm.payroll_month')}
        {$listObj->getListHeaderCell('Year', 'pm.payroll_year')}
        {$listObj->getListHeaderCell('Basic Pay', 'pm.basic_pay', 'txtRight')}
        {$listObj->getListHeaderCell('OT', '$OT')}
        {$listObj->getListHeaderCell('CPF(Employer)', 'pm.cpf_employer', 'txtRight')}
        {$listObj->getListHeaderCell('CPF(Employee)', 'pm.cpf_employee', 'txtRight')}
        {$listObj->getListHeaderCell('Allowance', 'j.allowance1', 'txtRight')}
        {$listObj->getListHeaderCell('Deductions', 'pm.cpf_deduction', 'txtRight')}
        {$listObj->getListHeaderCell('Net Pay', 'pm.net_total', 'txtRight')}
        {$listObj->getListHeaderCell('Status', 'pm.status')}
        {$listObj->getListHeaderCell('ID', 'pm.payroll_management_id', 'txtCenter')}
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
        {$formObj->getTBRow('Employee ID', 'employee_id')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }
    /**
     *
     */
    function getNew1(){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $sqlEmployeeName = "
        SELECT e.employee_id
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
        FROM employee e
        ORDER BY employee_name
        ";

        $fieldset = "
        {$formObj->getDDRowBySQL('Employee Name', 'employee_id', $sqlEmployeeName)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";


        return $text;
    }

    /**
     *
     */
    function getEdit1($row){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $formObj->mode = $tv['action'];

        $sqlEmployeeName = "
        SELECT e.employee_id
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
        FROM employee e
        ORDER BY employee_name
        ";

        $expStf = array('detailValue' => $row['employee_name']);
        $expNoEdit  = array('isEditable' => 0);

        $expVl = array('sqlType' => 'OneField');

        $status = $fn->getReqParam('status');

        $StatusArray = array(
              "Paid"
             ,"Approved"
             ,"Generated"
             ,"Hold"
             ,"Cancelled"
        );

        $lastMonthRecord = $fn->getRecordCount('payroll_management', "payroll_month > '{$row['payroll_month']}' AND payroll_year = '{$row['payroll_year']}' AND employee_id = {$row['employee_id']}");

        if($lastMonthRecord > 0){
            $exptd = array('isEditable' => 0);
        }else{
            $exptd = array('isEditable' => 1);
        }

            $dob = $fn->getCPDate($row['dob'], 'Y');
            $age = date('Y')- $dob;
            $year = date('Y');

            $OT = $row['ot_hours'] * $row['overtime_pay_rate'];
            $gross_pay = $row['basic_pay'] + $row['ot_amount'] + $row['commission'] + $row['allowance1'] + $row['allowance2'] + $row['allowance3'] + $row['allowance4'] + $row['allowance5'];

            $total_deduction = $row['cpf_employee'] + $row['sdl'] + $row['loan_amount'] + $row['income_tax_amount'] + $row['pay_cdac'] + $row['pay_sinda'] + $row['pay_mbmf'] + $row['pay_eucf'] + $row['deduction1'] + $row['deduction2'] + $row['deduction3'];
            $net_total = $gross_pay - $total_deduction;


        if($row['status'] == 'Paid'){
            $paidDateObjClass = "";
        }else{
            $paidDateObjClass = "displayNone passType";
        }


        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Step 1 (Main Details)</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td width='20%'>{$formObj->getTBRow('Employee Name', '', $row['employee_name'], $expNoEdit)}</td>
                                <td width='20%'>{$formObj->getDateRow('Generated Date', 'generated_date', $row['generated_date'])}</td>
                                <td width='20%'>{$formObj->getTBRow('Basic Pay', '', $row['basic_pay'], $expNoEdit)}</td>
                                <td width='20%'>{$formObj->getDDRowByArr('Status', 'status', $StatusArray, $row['status'], $exptd)}</td>
                                <td width='20%' class='{$paidDateObjClass}'><div >{$formObj->getDateRow('Paid Date', 'paid_date', $row['paid_date'])}</div></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class='linkPortalDataWrapper payDetails'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <th colspan='2'>EARNINGS</th>
                                <th colspan='2'>DEDUCTIONS</th>
                            </tr>
                            <tr>
                                <td>Basic Pay</td>
                                <td class='basicPayRate'>{$row['basic_pay']}</td>
                                <td>CPF-Employee</td>
                                <td class='cpfEmployee'>{$row['cpf_employee']}</td>
                            </tr>
                            <tr>
                                <td>Overtime Pay Rate/ Hour</td>
                                <td class='otPayRate'>{$row['overtime_pay_rate']}</td>
                                <td>SDL</td>
                                <td>{$formObj->getTBRow('', 'sdl', $row['sdl'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td>OT Hours</td>
                                <td>{$formObj->getTBRow('', 'ot_hours', $row['ot_hours'], $exptd)}</td>
                                <td>Advance / Loan 
                                <a class='loanBreakup' link='index.php?module=payroll_payrollManagement&_spAction=editLoanPaymentHistory&payroll_management_id={$row['payroll_management_id']}&showHTML=0'><u>View loan breakup</u></a></td>
                                <td>{$formObj->getTBRow('', 'loan_amount', $row['loan_amount'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td>Overtime Amount</td>
                                <td class='ot_amount'>{$formObj->getTBRow('', 'ot_amount', $row['ot_amount'], $exptd)}</td>
                                <td>Income Tax</td>
                                <td>{$formObj->getTBRow('', 'income_tax_amount', $row['income_tax_amount'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td>Commission</td>
                                <td>{$formObj->getTBRow('', 'commission', $row['commission'], $exptd)}</td>
                                <td>Pay CDAC</td>
                                <td>{$formObj->getTBRow('', 'pay_cdac', $row['pay_cdac'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td>Allowances 1</td>
                                <td>{$formObj->getTBRow('', 'allowance1', $row['allowance1'], $exptd)}</td>
                                <td>Pay SINDA</td>
                                <td>{$formObj->getTBRow('', 'pay_sinda', $row['pay_sinda'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td>Allowances 2</td>
                                <td>{$formObj->getTBRow('', 'allowance2', $row['allowance2'], $exptd)}</td>
                                <td>Pay MBMF</td>
                                <td>{$formObj->getTBRow('', 'pay_mbmf', $row['pay_mbmf'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td>Allowances 3</td>
                                <td>{$formObj->getTBRow('', 'allowance3', $row['allowance3'], $exptd)}</td>
                                <td>Pay EUCF</td>
                                <td>{$formObj->getTBRow('', 'pay_eucf', $row['pay_eucf'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td>Allowances 4</td>
                                <td>{$formObj->getTBRow('', 'allowance4', $row['allowance4'], $exptd)}</td>
                                <td>Deduction 1</td>
                                <td>{$formObj->getTBRow('', 'deduction1', $row['deduction1'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td>Allowances 5</td>
                                <td>{$formObj->getTBRow('', 'allowance5', $row['allowance5'], $exptd)}</td>
                                <td>Deduction 2</td>
                                <td>{$formObj->getTBRow('', 'deduction2', $row['deduction2'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td>Deduction 3</td>
                                <td>{$formObj->getTBRow('', 'deduction3', $row['deduction3'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td>Loan Deduction</td>
                                <td>{$formObj->getTBRow('', 'loan_deduction', $row['loan_deduction'], $exptd)}</td>
                            </tr>
                            <tr>
                                <th>Gross Pay</th>
                                <td class='grossPay'>{$gross_pay}</td>
                                <th>Total Deductions</th>
                                <td class='totalDeduction'>{$total_deduction}</td>
                            </tr>
                            <tr>
                                <th colspan='2'>NET PAY</th>
                                <th colspan='2' class='netTotalPayrollMgmt' align='right'>{$net_total}</th>
                            </tr>
                        </tbody>
                    </table>
                </div>
                {$formObj->getTARow('Notes', 'notes', $row['notes'], $exptd)}
            </div>
            <input type='hidden' name='employee_id' value='{$row['employee_id']}' />
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dateUtil = Zend_Registry::get('dateUtil');

        $formObj->mode = $tv['action'];

        $sqlEmployeeName = "
        SELECT e.employee_id
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
        FROM employee e
        ORDER BY employee_name
        ";
        $expStf = array('detailValue' => $row['employee_name']);
        $expNoEdit  = array('isEditable' => 0);

        if ($row['actual_working_days'] == $row['working_days_in_month']) {
            $expNoEditDate = array('isEditable' => 0);
        } else {
            $expNoEditDate = array();
        }

        $expVl = array('sqlType' => 'OneField');

        $status = $fn->getReqParam('status');

        $StatusArray = array(
              "Paid"
             ,"Approved"
             ,"Generated"
             ,"Hold"
             ,"Cancelled"
        );

        if ($cpCfg['m.payroll.payrollManagement.allowPreviousMonthsEdit'] == 'Yes') {
            $exptd = array('isEditable' => 1);
            $lastMonthRecord = 0;
            $last_month_check = 'No';
        } else {
            $lastMonthRecord = $fn->getRecordCount('payroll_management', "payroll_month > '{$row['payroll_month']}' AND payroll_year = '{$row['payroll_year']}' AND employee_id = {$row['employee_id']}");

            if($lastMonthRecord > 0){
                $exptd = array('isEditable' => 0);
                $last_month_check = 'Yes';
            }else{
                $exptd = array('isEditable' => 1);
                $last_month_check = 'No';
            }
        }


        $dob = $fn->getCPDate($row['dob'], 'Y');
        $age = date('Y')- $dob;
        $year = date('Y');

        /* Terminating employee calculations */
        $sqlBasicPay = "
        SELECT basic_pay, working_days FROM job_information
        WHERE employee_id = '{$row['employee_id']}'
        ORDER BY job_information_id DESC LIMIT 0,1";
        $resultBasicPay = $db->sql_query($sqlBasicPay);
        $rowBasicPay = $db->sql_fetchrow($resultBasicPay);

        $basic_pay = $row['basic_pay'];
        //$terminatingEmployeeFields = '';
        $working_days_in_month = 0;
        //if ($row['employee_status'] == 'Archive') {
            $last_working_month = date("m",strtotime($row['payslip_end_date']));

            if ($rowBasicPay['working_days'] == "5.0") {
                $working_days_in_month = $cpCfg['m.payroll.payrollManagement.monthlyWorkingDaysForFiveDaysWork'][$last_working_month];
            } else if ($rowBasicPay['working_days'] == "5.5") {
                $working_days_in_month = $cpCfg['m.payroll.payrollManagement.monthlyWorkingDaysForFiveHalfDaysWork'][$last_working_month];
            } else {
                $working_days_in_month = $cpCfg['m.payroll.payrollManagement.monthlyWorkingDaysForSixDaysWork'][$last_working_month];
            }


            if($lastMonthRecord > 0){
                $terminatingEmployeeFields = "
                <td>
                    <div class='type-text ym-fbox-text row_working_days_in_month non-editable'>
                        <label for='fld_working_days_in_month'>Working days in month</label>
                        <div class='txt' id='fld_working_days_in_month'><br/>
                            <span class='value'>{$row['working_days_in_month']}</span>
                        </div>
                    </div>
                </td>

                <td>
                    <div class='type-text ym-fbox-text row_actual_working_days non-editable'>
                        <label for='fld_actual_working_days'>Actual worked days in month</label>
                        <div class='txt' id='fld_actual_working_days'><br/>
                            <span class='value'>{$row['actual_working_days']}</span>
                        </div>
                    </div>
                </td>
                <!--<td width=''>{$formObj->getTBRow('Actual worked days in month', 'actual_working_days', $row['actual_working_days'], $exptd)}</td>-->
                ";
            }else{
                $terminatingEmployeeFields = "
                <td>
                <div class='type-text ym-fbox-text row_working_days_in_month non-editable'>
                <label for='fld_working_days_in_month'>Working days in month</label>            
                <div class='txt' id='fld_working_days_in_month'><br/>
                    <span class='value'>{$row['working_days_in_month']}</span>                
                </div>            
                </div>
                </td>

                <!--<td width=''>{$formObj->getTBRow('Working days in month', 'working_days_in_month', $row['working_days_in_month'], $expNoEdit)}</td>-->
                <td width=''>{$formObj->getTBRow('Actual worked days in month', 'actual_working_days', $row['actual_working_days'], $exptd)}</td>
                ";
            }

            /*
            $terminatingEmployeeFields = "
            <td width=''>{$formObj->getTBRow('Working days in month', 'working_days_in_month', $working_days_in_month, $expNoEdit)}</td>
            <td width=''>{$formObj->getTBRow('Actual worked days in month', 'actual_working_days', $row['actual_working_days'], $exptd)}</td>
            ";

            if ($row['actual_working_days']) {
                $month_basic_pay = ($row['basic_pay'] / $row['working_days_in_month']);
                $basic_pay = round($month_basic_pay * $row['actual_working_days']);
            }
            */
        //}

        $OT = $row['ot_hours'] * $row['overtime_pay_rate'];
        $gross_pay = $basic_pay + $row['ot_amount'] + $row['commission'] + $row['allowance1'] + $row['allowance2'] + $row['allowance3'] + $row['allowance4'] + $row['allowance5'];

        $total_deduction = $row['cpf_employee'] + $row['sdl'] + $row['loan_amount'] + $row['income_tax_amount'] + $row['pay_cdac'] + $row['pay_sinda'] + $row['pay_mbmf'] + $row['pay_eucf'] + $row['deduction1'] + $row['deduction2'] + $row['deduction3'];
        $net_total = $gross_pay - $total_deduction + $row['reimbursement'] + $row['director_fee'];
        $net_total_formatted = number_format($net_total, 2);


        if($row['status'] == 'Paid'){
            $paidDateObjClass = "";
        }else{
            $paidDateObjClass = "displayNone passType";
        }

        $donationRow = '';
        if ($row['govt_donation'] == 'CDAC') {
            $donationRow = "
            <tr>
                <td></td>
                <td></td>
                <td>Pay CDAC</td>
                <td>{$formObj->getTBRow('', 'pay_cdac', $row['pay_cdac'], $exptd)}</td>
            </tr>
            ";
        } else if ($row['govt_donation'] == 'SINDA') {
            $donationRow = "
            <tr>
                <td></td>
                <td></td>
                <td>Pay SINDA</td>
                <td>{$formObj->getTBRow('', 'pay_sinda', $row['pay_sinda'], $exptd)}</td>
            </tr>
            ";
        } else if ($row['govt_donation'] == 'MBMF') {
            $donationRow = "
            <tr>
                <td></td>
                <td></td>
                <td>Pay MBMF</td>
                <td>{$formObj->getTBRow('', 'pay_mbmf', $row['pay_mbmf'], $exptd)}</td>
            </tr>
            ";
        } else if ($row['govt_donation'] == 'EUCF') {
            $donationRow = "
            <tr>
                <td></td>
                <td></td>
                <td>Pay EUCF</td>
                <td>{$formObj->getTBRow('', 'pay_eucf', $row['pay_eucf'], $exptd)}</td>
            </tr>
            ";
        }

        /* Leave calculation for employee - START */
        $current_year_month = substr($row['payslip_end_date'], 0, 4);
        $current_year_month_date = $current_year_month . '-01-01';
        
        $total_annual_leave = $this->model->getTotalLeave($row['employee_id'], 'Annual Leave', $current_year_month_date, $row['payslip_end_date']);
        $total_sick_leave = $this->model->getTotalLeave($row['employee_id'], 'Sick Leave', $current_year_month_date, $row['payslip_end_date']);
        $total_hospitalization_leave = $this->model->getTotalLeave($row['employee_id'], 'Hospitalization Leave', $current_year_month_date, $row['payslip_end_date']);
        $total_unpaid_leave = $this->model->getTotalLeave($row['employee_id'], 'Absent', $current_year_month_date, $row['payslip_end_date']);

        //$current_month_annual_leave = $this->model->getTotalLeave($row['employee_id'], 'Annual Leave', $row['payslip_start_date'], $row['payslip_end_date']);
        //$current_month_sick_leave = $this->model->getTotalLeave($row['employee_id'], 'Sick Leave', $row['payslip_start_date'], $row['payslip_end_date']);
        $current_month_annual_leave = $this->model->getTotalLeavePerMonth($row['employee_id'], 'Annual Leave', $row['payslip_start_date'], $row['payslip_end_date']);
        $current_month_sick_leave = $this->model->getTotalLeavePerMonth($row['employee_id'], 'Sick Leave', $row['payslip_start_date'], $row['payslip_end_date']);
        $current_month_hospitalization_leave = $this->model->getTotalLeavePerMonth($row['employee_id'], 'Hospitalization Leave', $row['payslip_start_date'], $row['payslip_end_date']);
        $current_month_unpaid_leave = $this->model->getTotalLeavePerMonth($row['employee_id'], 'Absent', $row['payslip_start_date'], $row['payslip_end_date']);
        /* Leave calculation for employee - END */

        $totalAnnualLeaveUrl = "index.php?module=payroll_leave&_spAction=viewLeaveRecords&leave_type=Annual Leave&start_date={$current_year_month_date}&end_date={$row['payslip_end_date']}&showHTML=0&employee_id={$row['employee_id']}";
        $totalSickLeaveUrl = "index.php?module=payroll_leave&_spAction=viewLeaveRecords&leave_type=Sick Leave&start_date={$current_year_month_date}&end_date={$row['payslip_end_date']}&showHTML=0&employee_id={$row['employee_id']}";
        $totalhospitalizationLeaveUrl = "index.php?module=payroll_leave&_spAction=viewLeaveRecords&leave_type=Hospitalization Leave&start_date={$current_year_month_date}&end_date={$row['payslip_end_date']}&showHTML=0&employee_id={$row['employee_id']}";
        $totalUnpaidLeaveUrl = "index.php?module=payroll_leave&_spAction=viewLeaveRecords&leave_type=Absent&start_date={$current_year_month_date}&end_date={$row['payslip_end_date']}&showHTML=0&employee_id={$row['employee_id']}";

        $annualLeaveThisMonthUrl = "index.php?module=payroll_leave&_spAction=viewLeaveRecords&leave_type=Annual Leave&start_date={$row['payslip_start_date']}&end_date={$row['payslip_end_date']}&showHTML=0&employee_id={$row['employee_id']}";
        $sickLeaveThisMonthUrl = "index.php?module=payroll_leave&_spAction=viewLeaveRecords&leave_type=Sick Leave&start_date={$row['payslip_start_date']}&end_date={$row['payslip_end_date']}&showHTML=0&employee_id={$row['employee_id']}";
        $hospitalizationLeaveThisMonthUrl = "index.php?module=payroll_leave&_spAction=viewLeaveRecords&leave_type=Hospitalization Leave&start_date={$row['payslip_start_date']}&end_date={$row['payslip_end_date']}&showHTML=0&employee_id={$row['employee_id']}";
        $unpaidLeaveThisMonthUrl = "index.php?module=payroll_leave&_spAction=viewLeaveRecords&leave_type=Absent&start_date={$row['payslip_start_date']}&end_date={$row['payslip_end_date']}&showHTML=0&employee_id={$row['employee_id']}";

        $expEdit = array('isEditable' => 0, 'fieldCls' => 'payrollManagementError');
        
        $urlPrintLinkPdf = "index.php?_topRm=payroll&module=payroll_payrollManagement&_spAction=payslipprintPdf&payroll_management_id={$row['payroll_management_id']}&employee_id={$row['employee_id']}&showHTML=0";

        $paymentArray = array(
              "cheque"
             ,"cash"
             ,"giro payment transfer"
        );

        $dob = '';
        if ($row['dob']) {
            $dob = '(' . $dateUtil->formatDate($row['dob'], 'DD MMM YYYY') . ')';
        }
        $employee_name = $row['employee_name'] . ' ' . $dob;

        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Step 1 (Main Details)</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div>{$formObj->getTBRow('', "error_box", '', $expEdit)}</div>
                <div class='float_right mb10'><a href='{$urlPrintLinkPdf}' target='_blank' class='button'>Print Payslip</a></div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <th colspan='5'>Leave Summary</th>
                            </tr>
                            <tr>
                                <td colspan='3'>
                                    <table>
                                        <tr>
                                            <td colspan='3' align='center'><b>ANNUAL LEAVE AS PER MOM</b></td>
                                        </tr>
                                        <tr>
                                            <td>1st year: 7 days</td>
                                            <td>2nd year: 8 days</td>
                                            <td>3rd year: 9 days</td>
                                        </tr>
                                        <tr>
                                            <td>4th year: 10 days</td>
                                            <td>5th year: 11 days</td>
                                            <td>6th year: 12 days</td>
                                        </tr>
                                        <tr>
                                            <td>7th year: 13 days</td>
                                            <td>8th year thereafter: 14 days</td>
                                        </tr>
                                        <tr>
                                            <td colspan='3' align='center'><b>SICK LEAVE AS PER MOM</b></td>
                                        </tr>
                                        <tr>
                                            <td>After 3 months: 5 days</td>
                                            <td>After 4 months: 8 days</td>
                                        </tr>
                                        <tr>
                                            <td>After 5 months: 11 days</td>
                                            <td>6 months and thereafter: 14 days</td>
                                        </tr>
                                    </table>                                
                                </td>
                                <td><b>Total No of leave taken this year</b><br/><br/>
                                Annual leave : <a class='showDetailPortalForm jqui-dialog' href='{$totalAnnualLeaveUrl}'><u>{$total_annual_leave}</u></a><br/><br/>
                                Sick leave : <a class='showDetailPortalForm jqui-dialog' href='{$totalSickLeaveUrl}'><u>{$total_sick_leave}</u><br/><br/>
                                Hospitalization leave : <a class='showDetailPortalForm jqui-dialog' href='{$totalhospitalizationLeaveUrl}'><u>{$total_hospitalization_leave}</u></a><br/><br/>
                                Absent leave : <a class='showDetailPortalForm jqui-dialog' href='{$totalUnpaidLeaveUrl}'><u>{$total_unpaid_leave}</u><br/>
                                </td>
                                <td><b>Total No of leave taken this month</b><br/><br/>
                                Annual leave : <a class='showDetailPortalForm jqui-dialog' href='{$annualLeaveThisMonthUrl}'><u>{$current_month_annual_leave}</u></a><br/><br/>
                                Sick leave : <a class='showDetailPortalForm jqui-dialog' href='{$sickLeaveThisMonthUrl}'><u>{$current_month_sick_leave}</u></a><br/><br/>
                                Hospitalization leave : <a class='showDetailPortalForm jqui-dialog' href='{$hospitalizationLeaveThisMonthUrl}'><u>{$current_month_hospitalization_leave}</u></a><br/><br/>
                                Absent leave : <a class='showDetailPortalForm jqui-dialog' href='{$unpaidLeaveThisMonthUrl}'><u>{$current_month_unpaid_leave}</u></a><br/>
                                </td>
                            </tr>
                            <tr>
                                <th colspan='5'>Payslip Summary</th>
                            </tr>
                            <tr>
                                <td width='20%'>{$formObj->getDateRow('Start Date', 'payslip_start_date', $row['payslip_start_date'], $expNoEditDate)}</td>
                                <td width='20%'>{$formObj->getDateRow('End Date', 'payslip_end_date', $row['payslip_end_date'], $expNoEditDate)}</td>
                                {$terminatingEmployeeFields}
                                <td>{$formObj->getDDRowByArr('Mode of Payment', 'mode_of_payment', $paymentArray, $row['mode_of_payment'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td width='20%'>{$formObj->getTBRow('Employee Name (DOB)', '', $employee_name, $expNoEdit)}</td>
                                <td width='20%'>{$formObj->getDateRow('Generated Date', 'generated_date', $row['generated_date'], $exptd)}</td>
                                <td width='20%'>{$formObj->getTBRow('Basic Pay', '', $row['total_basic_pay_for_month'], $expNoEdit)}</td>
                                <td width='20%'>{$formObj->getDDRowByArr('Status', 'status', $StatusArray, $row['status'], $exptd)}</td>
                                <td width='20%' class='{$paidDateObjClass}'><div >{$formObj->getDateRow('Paid Date', 'paid_date', $row['paid_date'])}</div></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class='linkPortalDataWrapper payDetails'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <th colspan='2'>EARNINGS</th>
                                <th colspan='2'>DEDUCTIONS</th>
                            </tr>
                            <tr>
                                <td>Gross Pay</td>
                                <td class='basicPayRate'>{$row['basic_pay']}</td>
                                <td>CPF-Employee</td>
                                <td class='cpfEmployee'>{$row['cpf_employee']}</td>
                            </tr>
                            <tr>
                                <td>Overtime Pay Rate/ Hour</td>
                                <td class='otPayRate'>{$row['overtime_pay_rate']}</td>
                                <td>SDL</td>
                                <td>{$formObj->getTBRow('', 'sdl', $row['sdl'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td>OT Hours</td>
                                <td>{$formObj->getTBRow('', 'ot_hours', $row['ot_hours'], $exptd)}</td>
                                <td>Advance / Loan 
                                <a class='loanBreakup' link='index.php?module=payroll_payrollManagement&_spAction=editLoanPaymentHistory&payroll_management_id={$row['payroll_management_id']}&showHTML=0'><u>View loan breakup</u></a></td>
                                <td>{$formObj->getTBRow('', 'loan_amount', $row['loan_amount'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td>Overtime Amount</td>
                                <td class='ot_amount'>{$formObj->getTBRow('', 'ot_amount', $row['ot_amount'], $expNoEdit)}</td>
                                <td>Income Tax</td>
                                <td>{$formObj->getTBRow('', 'income_tax_amount', $row['income_tax_amount'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td>{$cpCfg['m.jobInformation.allowance1Lbl']}</td>
                                <td>{$formObj->getTBRow('', 'allowance1', $row['allowance1'], $exptd)}</td>
                                <td>{$cpCfg['m.jobInformation.deduction1Lbl']}</td>
                                <td>{$formObj->getTBRow('', 'deduction1', $row['deduction1'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td>{$cpCfg['m.jobInformation.allowance2Lbl']}</td>
                                <td>{$formObj->getTBRow('', 'allowance2', $row['allowance2'], $exptd)}</td>
                                <td>{$cpCfg['m.jobInformation.deduction2Lbl']}</td>
                                <td>{$formObj->getTBRow('', 'deduction2', $row['deduction2'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td>{$cpCfg['m.jobInformation.allowance3Lbl']}</td>
                                <td>{$formObj->getTBRow('', 'allowance3', $row['allowance3'], $exptd)}</td>
                                <td>{$cpCfg['m.jobInformation.deduction3Lbl']}</td>
                                <td>{$formObj->getTBRow('', 'deduction3', $row['deduction3'], $exptd)}</td>
                            </tr>
                            <tr>
                                <td>{$cpCfg['m.jobInformation.allowance4Lbl']}</td>
                                <td>{$formObj->getTBRow('', 'allowance4', $row['allowance4'], $exptd)}</td>
                                <td colspan='2'></td>
                            </tr>
                            <tr>
                                <td>{$cpCfg['m.jobInformation.allowance5Lbl']}</td>
                                <td>{$formObj->getTBRow('', 'allowance5', $row['allowance5'], $exptd)}</td>
                                <td colspan='2'></td>
                            </tr>
                            {$donationRow}
                            <tr>
                                <th>Gross Pay</th>
                                <td class='grossPay'>{$gross_pay}</td>
                                <th>Total Deductions</th>
                                <td class='totalDeduction'>{$total_deduction}</td>
                            </tr>
                            <tr>
                                <th colspan='4'>Other Additional Payment</th>

                            </tr>
                            <tr>
                                <td>Reimbursement</td>
                                <td>{$formObj->getTBRow('', 'reimbursement', $row['reimbursement'], $exptd)}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Director Fees</td>
                                <td>{$formObj->getTBRow('', 'director_fee', $row['director_fee'], $exptd)}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <th colspan='2'>NET PAY</th>
                                <th colspan='2' class='netTotalPayrollMgmt' align='right'>{$net_total_formatted}</th>
                            </tr>
                        </tbody>
                    </table>
                </div>
                {$formObj->getTARow('Notes', 'notes', $row['notes'], $exptd)}
            </div>
            <input type='hidden' name='employee_id' value='{$row['employee_id']}' />
            <input type='hidden' name='working_days_in_month' value='{$row['working_days_in_month']}' />
            <input type='hidden' name='employee_status' value='{$row['employee_status']}' />
            <input type='hidden' name='overtime_pay_rate' value='{$row['overtime_pay_rate']}' />
            <input type='hidden' name='last_month_check' value='{$last_month_check}' />
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getPrintDetail($row){
        $db = Zend_Registry::get('db');
        return $this->getDetail($row);
    }

    /**
     *
     */
    function getSearch(){
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $sqlCategory = $fn->getValueListSQL('companyCategory');
        $sqlStatus   = $fn->getValueListSQL('companyStatus');
        $expVl = array('sqlType' => 'OneField');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $fielset = "
        {$formObj->getTBRow('Company Name', 'company_name')}
        {$formObj->getDDRowBySQL('Choose Category', 'category', $sqlCategory, 'Client', $expVl)}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, 'Current', $expVl)}
        {$formObj->getDDRowByArr('Special Search', 'special_search', $spArray)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Company Details', $fielset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $comment = getCPPluginObj('common_comment');
        $media = Zend_Registry::get('media');

        $record_id = $fn->getIssetParam($row, 'payroll_management_id');

        $text = "
        {$media->getRightPanelMediaDisplay('Attachments', 'payroll_payrollManagement', 'attachment', $row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db     = Zend_Registry::get('db');
        $tv     = Zend_Registry::get('tv');
        $fn     = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        $employee_id = $fn->getReqParam('employee_id');
        $status      = $fn->getReqParam('status');
        $year        = $fn->getReqParam('year');
        $month       = $fn->getReqParam('month');
        $employee_status    = $fn->getReqParam('employee_status');

        if($employee_status == ''){
            $employee_status = 'Current';
        }

        if ($month == '') {
            $month = date('m') - 1;
            if ($month <= 9 && $month > 0) {
                $month = 0 . $month;
            } else if ($month == 0) {
                $month = 12;
            } else {
                $month = $month;
            }
        }

        if ($year == '') {
            if ($month == 12) {
                $year  = date('Y') - 1;
            } else {
                $year = date('Y');
            }
        }

        /*
        if($month == ''){
            $month = date('m')-1;
            $month = 0 . $month;
        }

        if($year == ''){
            if ($month == 00)
            $year = date('Y');
        }
        */

        $sqlEmployeeName = "
        SELECT e.employee_id
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
        FROM employee e
        WHERE status = '{$employee_status}'
        ORDER BY employee_name
        ";

        $StatusArray = array(
              "Paid"
             ,"Approved"
             ,"Generated"
             ,"Hold"
             ,"Cancelled"
        );

        $spArray = array(
            ""
           ,"Flagged"
           ,"Not-Flagged"
        );

        $arr = array (
                '01' => 'January'
               ,'02' => 'February'
               ,'03' => 'March'
               ,'04' => 'April'
               ,'05' => 'May'
               ,'06' => 'June'
               ,'07' => 'July'
               ,'08' => 'August'
               ,'09' => 'September'
               ,'10' => 'October'
               ,'11' => 'November'
               ,'12' => 'December'
               );

        $status = array(
              "Current"
             ,"Archive"
             ,"Cancel"
        );

        $text = "
        <td>
            <select name='employee_id' >
                <option value=''>Employee Name</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlEmployeeName, $employee_id)}
            </select>
        </td>
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($StatusArray, $tv['status'])}
            </select>
        </td>
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
           </select>
        </td>
        <td>Year
            <select name='year' class='yearFilter'>
                {$cpUtil->getDropDownFromArr($cpCfg['m.payroll.yearArr'], $year)}
            </select>
        </td>
        <td class='ml10 mr10'>Month
            <select name='month'>
                {$cpUtil->getDropDownFromArr($arr, $month)}
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
    function getPrintPayslipForm() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $formAction = "index.php?_topRm=payroll&module=payroll_payrollManagement&_spAction=printPayslipFormSubmit&showHTML=0";

        $exp = array(
            'hideFirstOption' => true
           ,'sqlType' => 'OneField'
        );

        $expmonth = array(
            'hideFirstOption' => true,
            'useKey' => true
        );

        $monthArray = array(
                         '01' => 'January'
                        ,'02' => 'February'
                        ,'03' => 'March'
                        ,'04' => 'April'
                        ,'05' => 'May'
                        ,'06' => 'June'
                        ,'07' => 'July'
                        ,'08' => 'August'
                        ,'09' => 'September'
                        ,'10' => 'October'
                        ,'11' => 'November'
                        ,'12' => 'December'
                      );

        $sqlYear = "SELECT DISTINCT payroll_year FROM payroll_management";
        
        $currentMonth = date('m') - 1;
        if ($currentMonth <= 9 && $currentMonth > 0) {
            $currentMonth = 0 . $currentMonth;
        } else if ($currentMonth == 0) {
            $currentMonth = 12;
        } else {
            $currentMonth = $currentMonth;
        }

        if ($currentMonth == 12) {
            $currentYear  = date('Y') - 1;
        } else {
            $currentYear  = date('Y');
        }

        $text = "
        <form id='portalFormPrintPayslip' class='yform columnar' method='post' action='{$formAction}'>
            <table id='' class='thinlist'>
                {$formObj->getDropDownRowBySQL('Year', 'payroll_year', $sqlYear, $currentYear, $exp)}
                {$formObj->getDropDownRowByArray('Month', 'payroll_Month', $monthArray, $currentMonth, $expmonth)}
            </table>
        </form>
        ";

        return $text;
    }
  
    /**
     *
     */
    function getPrintPaySlipForAllPdfOld() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot2.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();

        $payroll_year  = $fn->getReqParam('payroll_year');
        $payroll_month = $fn->getReqParam('payroll_month');

        $SQL = "
        SELECT pm.*
              ,CONCAT_WS(' ', e.first_name) AS employee_name
              ,e.position AS designation
              ,e.salary
              ,e.fin_no
              ,e.nric_no
              ,e.date_of_birth  AS dob
              ,e.employee_id
              ,e.citizen
              ,e.spr_year
              ,cpf.by_employer
        FROM payroll_management pm
        LEFT JOIN (employee e) ON (e.employee_id = pm.employee_id)
        LEFT JOIN (cpf_calculator cpf) ON (cpf.cpf_calculator_id = pm.employee_id)
        WHERE pm.payroll_month = '{$payroll_month}'
        AND pm.payroll_year = '{$payroll_year}'
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);

        $count = 1;

        While ($Row = $db->sql_fetchrow($result)){
            //============================================================================= //

            $pdf->SetFont('Courier','B',10);

            $today = date("d-m-Y");
            //$payroll_month = $fn->getCPDate($Row['payroll_month'], 'M');
            if($Row['citizen'] == 'PR' || $Row['citizen'] == 'Citizen'){
                $finNo ='
                <td width="21%" style="font-weight:normal;" align="right">Nric No :</td>
                <td width="48%"> '.$Row['nric_no'].'</td>
                ';
            }else {
                $finNo ='
                <td width="21%" style="font-weight:normal;" align="right">Fin No :</td>
                <td width="48%"> '.$Row['fin_no'].'</td>
                ';
            }

            $prefix_month = $dateUtil->getLongMonthName($Row['payroll_month']);

            $tbl1 = '<table border="0" width="100%" style="font-size:14px;">
                    <tr>
                        <td align="center" style="font-weight:bold; font-size:20px;">SALARY SLIP - '.$prefix_month.' '.$Row['payroll_year'].'<br/></td>
                    </tr>';

            $generated_date = $fn->getCPDate($Row['generated_date'], 'd-m-Y');
            $tbl1 = $tbl1.'
                    <tr>
                        <td width="22%" style="font-weight:normal;">Employee Name :</td>
                        <td width="78%">'.$Row['employee_name'].'</td>
                    </tr>
                    <tr>
                        '.$finNo.'
                        <td width="15%" style="font-weight:normal;" align="right">Date:</td>
                        <td width="16%" align="right"> '.$generated_date.'</td>
                    </tr>';

            $tbl1 = $tbl1.'</table>';

            if($count == 1){
                //$pdf->ln(-6);
            }else{
                $pdf->AddPage();
            }
            $pdf->ln(-5);
            $pdf->writeHTML($tbl1, true, false, false, false, '');

            $dob = $fn->getCPDate($Row['dob'], 'Y');
            $age = date('Y')- $dob;
            $year = date('Y');

            $OT  = $Row['ot_hours'] * $Row['overtime_pay_rate'];
            $gross_pay = $Row['basic_pay'] + $Row['ot_amount'] + $Row['commission'] + $Row['allowance1'] + $Row['allowance2'] + $Row['allowance3'];

            $total_deduction = round($Row['cpf_employee'], 2) + $Row['sdl'] + $Row['loan_amount'] + $Row['income_tax_amount'] + $Row['pay_cdac'] + $Row['pay_sinda'] + $Row['pay_mbmf'] + $Row['pay_eucf'] + $Row['deduction1'] + $Row['deduction2'] + $Row['deduction3'] + $Row['loan_deduction'];
            $net_total = $gross_pay - $total_deduction;
            $net_total = number_format($net_total, 2);

            $tbl2 = '
            <table border="1" width="100%" style="border: 2px solid red; font-size:14px;">
                <tr style="background-color: #b6e5f9;">
                    <th colspan="2" height="25px">EARNINGS</th>
                    <th colspan="2" height="25px">DEDUCTIONS</th>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%" height="20px">Basic Pay</td>
                    <td  width="15%" height="20px" align="right">'.$Row['basic_pay'].'</td>
                    <td width="35%" height="20px">CPF-Employee</td>
                    <td width="15%" height="20px" align="right">'.number_format($Row['cpf_employee'], 2).'</td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%">Overtime Pay Rate/ Hour</td>
                     <td width="15%" align="right">'.$Row['overtime_pay_rate'].'</td>
                    <td width="35%">SDL</td>
                     <td width="15%" align="right">'.number_format($Row['sdl'], 2).'</td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%">OT Hours</td>
                    <td width="15%" align="right">'.$Row['ot_hours'].'</td>
                    <td width="35%">Advance / Loan</td>
                    <td width="15%" align="right">'.number_format($Row['loan_amount'], 2).'</td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%">Overtime Amount</td>
                    <td width="15%" align="right">'.$OT.'</td>
                    <td width="35%">Income Tax</td>
                    <td width="15%" align="right">'.number_format($Row['income_tax_amount'], 2).'</td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%">Commission</td>
                    <td width="15%" align="right">'.$Row['commission'].'</td>
                    <td width="35%">Pay CDAC</td>
                    <td width="15%" align="right">'.number_format($Row['pay_cdac'], 2).'</td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%">Allowances 1</td>
                    <td width="15%" align="right">'.$Row['allowance1'].'</td>
                    <td width="35%">Pay SINDA</td>
                    <td width="15%" align="right">'.number_format($Row['pay_sinda'], 2).'</td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%">Allowances 2</td>
                    <td width="15%" align="right">'.$Row['allowance2'].'</td>
                    <td width="35%">Pay MBMF</td>
                    <td width="15%" align="right">'.number_format($Row['pay_mbmf'], 2).'</td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%">Allowances 3</td>
                    <td width="15%" align="right">'.$Row['allowance3'].'</td>
                    <td width="35%">Pay EUCF</td>
                    <td width="15%" align="right">'.number_format($Row['pay_eucf'], 2).'</td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%"></td>
                    <td width="15%" align="right"></td>
                    <td width="35%">Deduction 1</td>
                    <td width="15%" align="right">'.number_format($Row['deduction1'], 2).'</td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%"></td>
                    <td width="15%" align="right"></td>
                    <td width="35%">Deduction 2</td>
                    <td width="15%" align="right">'.number_format($Row['deduction2'], 2).'</td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%"></td>
                    <td width="15%" align="right"></td>
                    <td width="35%">Deduction 3</td>
                    <td width="15%" align="right">'.number_format($Row['deduction3'], 2).'</td>
                </tr>
                <tr style="font-weight:normal;">
                    <td width="35%"></td>
                    <td width="15%" align="right"></td>
                    <td width="35%">Loan Deduction</td>
                    <td width="15%" align="right">'.number_format($Row['loan_deduction'], 2).'</td>
                </tr>
                <tr>
                    <th width="35%" height="25px" style="background-color: #b6e5f9;">(A)Gross Pay</th>
                    <td width="15%" height="25px" align="right">'.number_format($gross_pay,2).'</td>
                    <th width="35%" height="25px" style="background-color: #b6e5f9;">(B)Total Deductions</th>
                    <td width="15%" height="25px" align="right">'.number_format($total_deduction,2).'</td>
                </tr>
                <tr style="background-color: #b6e5f9;">
                    <th colspan="3"  height="25px" align="right">NET PAY (A-B)</th>
                    <td align="right">'.$net_total.'</td>
                </tr>
            </table>
            ';

            $cpf_employee = round($Row['cpf_employee'], 2);
            $cpf_employer = round($Row['cpf_employer'], 2);
            $total_cpf = $cpf_employee + $cpf_employer;

            $tbl3 = '
            <table border="1" width="100%" style="border: 2px solid red; font-size:14px;">
                <tr style="background-color: #b6e5f9;">
                    <th colspan="2" height="25px">CPF CONTRIBUTIONS</th>
                </tr>
                <tr style="font-weight:normal;">
                    <td>CPF-Employee</td>
                    <td align="right">'.number_format($cpf_employee,2).'</td>
                </tr>
                <tr style="font-weight:normal;">
                    <td>CPF-Employer</td>
                    <td align="right">'.number_format($cpf_employer,2).'</td>
                </tr>
                <tr style="background-color: #b6e5f9;">
                    <th height="25px">TOTAL CPF</th>
                    <td align="right">'.number_format($total_cpf,2).'</td>
                </tr>
            </table>
            ';

            $tbl4 = '
            <table border="0" width="100%" style="font-size:14px;">
                <tr>
                    <td width="61%"></td>
                    <td width="39%" text-align="right" style="border-bottom: 2px solid #000;"></td>
                </tr>
                <tr>
                    <td width="61%"></td>
                    <td width="39%" align="right">Signature of Employee</td>
                </tr>
            </table>
            ';

            $pdf->writeHTML($tbl2, true, false, false, false, '');
            $pdf->writeHTML($tbl3, true, false, false, false, '');
            $pdf->ln(5);
            $pdf->writeHTML($tbl4, true, false, false, false, '');
            $count ++;
        }
        $pdf->Output();
    }

    /**
     *
     */
    function getPrintPaySlipForAllPdf() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot2.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();

        $payroll_year  = $fn->getReqParam('payroll_year');
        $payroll_month = $fn->getReqParam('payroll_month');

        $SQL = "
        SELECT DISTINCT pm.payroll_management_id AS pm_payroll_management_id 
              ,pm.*
              ,CONCAT_WS(' ', e.first_name) AS employee_name
              ,e.position AS designation
              ,e.salary
              ,e.fin_no
              ,e.nric_no
              ,e.date_of_birth  AS dob
              ,e.employee_id
              ,e.citizen
              ,e.spr_year
              ,j.mode_of_payment AS job_info_payment_mode
              ,cpf.by_employer
        FROM payroll_management pm
        LEFT JOIN (employee e) ON (e.employee_id = pm.employee_id)
        LEFT JOIN (job_information j) ON (j.employee_id = pm.employee_id)
        LEFT JOIN (cpf_calculator cpf) ON (cpf.cpf_calculator_id = pm.employee_id)
        WHERE pm.payroll_month = '{$payroll_month}'
          AND pm.payroll_year = '{$payroll_year}'
          AND pm.status != 'Cancelled'
        GROUP BY e.employee_id
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);

        $count = 1;

        While ($Row = $db->sql_fetchrow($result)){
            //============================================================================= //
            $pdf->SetFont('Courier','B',10);

            $today = date("d-m-Y");
            //$payroll_month = $fn->getCPDate($Row['payroll_month'], 'M');
            if($Row['citizen'] == 'PR' || $Row['citizen'] == 'Citizen'){
                $finNo ='
                <tr style="background-color: #414042; color:#FFFFFF">
                    <td style="font-weight:bold;"> Nric No</td>
                </tr>
                <tr>
                    <td> '.$Row['nric_no'].'</td>
                </tr>
                ';
            } else {
                $finNo ='
                <tr style="background-color: #414042; color:#FFFFFF">
                    <td style="font-weight:bold;"> Fin No</td>
                </tr>
                <tr>
                    <td> '.$Row['fin_no'].'</td>
                </tr>
                ';
            }

            $generated_date = $fn->getCPDate($Row['generated_date'], 'd M Y');
            $prefix_month = $dateUtil->getLongMonthName($Row['payroll_month']);
            /*
            $start_date ='01'. '&nbsp;' . $prefix_month . '&nbsp;'. $Row['payroll_year'];
            $last_date = date("d", mktime(0, 0, 0, $Row['payroll_month']+1,0,$Row['payroll_year']));
            $end_date = $last_date. '&nbsp;' . $prefix_month . '&nbsp;'. $Row['payroll_year'];
            */
            $start_date = $dateUtil->formatDate($Row['payslip_start_date'], 'DD MMMM YYYY');
            $end_date   = $dateUtil->formatDate($Row['payslip_end_date'], 'DD MMMM YYYY');

            $tbl1 = '
            <table border="0" width="100%" style="font-size:12px;">
                <tr>
                    <td align="center" style="font-size:15px; font-weight:bold;">Payslip for '.$start_date.' to '.$end_date.'<br/></td>
                </tr>
                <tr style="background-color: #414042; color:#FFFFFF">
                    <td style="font-weight:bold;"> Name Of Employee</td>
                </tr>
                <tr>
                    <td height="25px"> '.$Row['employee_name'].'</td>
                </tr>
                '.$finNo.'
            </table>
            ';

            if ($count == 1) {
                //$pdf->ln(-6);
            } else {
                $pdf->AddPage();
            }
            $pdf->ln(-5);
            $pdf->writeHTML($tbl1, true, false, false, false, '');

            /*
            $dob = $fn->getCPDate($Row['dob'], 'Y');
            $age = date('Y')- $dob;
            $year = date('Y');
            */
            
            $gross_pay = $Row['basic_pay'] + $Row['ot_amount'] + $Row['commission'] + $Row['allowance1'] + $Row['allowance2'] + $Row['allowance3'];
            /*
            $cpf = 0;
            $cpfE = 0;
            if($Row['citizen'] == 'PR' || $Row['citizen'] == 'Citizen'){
                $sprCondition = '';
                if($Row['spr_year'] != '' && $Row['citizen'] == 'PR'){
                    $sprCondition = "AND spr_year = {$Row['spr_year']}";
                } else {
                    $sprCondition = "AND spr_year = 3";
                }

                $SQLPercentageCPF = "
                SELECT by_employer
                      ,by_employee
                      ,cap_amount_employer
                      ,cap_amount_employee
                FROM cpf_calculator
                WHERE {$age} BETWEEN from_age AND to_age
                  AND {$gross_pay} BETWEEN from_salary AND to_salary
                  AND year = {$year}
                  {$sprCondition}
                ";
                $resultPercentageCPF  = $db->sql_query($SQLPercentageCPF);
                $rowPercentageCPF     = $db->sql_fetchrow($resultPercentageCPF);

                $cpf = (($gross_pay) * $rowPercentageCPF['by_employer'])/100;
                $cpf = round($cpf, 2);

                if ($cpf > $rowPercentageCPF['cap_amount_employer'] &&
                    $rowPercentageCPF['cap_amount_employer'] != 0){
                    $cpf = $rowPercentageCPF['cap_amount_employer'];
                }

                $cpfE = (($gross_pay) * $rowPercentageCPF['by_employee'])/100;
                $cpfE = round($cpfE, 2);

                if($cpfE > $rowPercentageCPF['cap_amount_employee'] &&
                    $rowPercentageCPF['cap_amount_employee'] != 0){
                    $cpfE = $rowPercentageCPF['cap_amount_employee'];
                }
            }
            */

            $allowanceCount = 0; //adding extra row in allowance for formatting purpose
            $allowance1Row = '';
            if ($Row['allowance1'] > 0) {
                $allowance1Row = '
                <tr>
                    <td width="35%" height="20px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD;"> '. $cpCfg["m.jobInformation.allowance1Lbl"] .'</td>
                    <td width="65%" height="20px" style="border-bottom: 1px solid #BABBBD;"> '.number_format($Row['allowance1'], 2).'</td>
                </tr>
                ';
            } else {
                $allowanceCount = $allowanceCount + 1;
            }

            $allowance2Row = '';
            if ($Row['allowance2'] > 0) {
                $allowance2Row = '
                <tr>
                    <td width="35%" height="20px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD;"> '. $cpCfg["m.jobInformation.allowance2Lbl"] .'</td>
                    <td width="65%" height="20px" style="border-bottom: 1px solid #BABBBD;"> '.number_format($Row['allowance2'], 2).'</td>
                </tr>
                ';
            } else {
                $allowanceCount = $allowanceCount + 1;
            }

            $allowance3Row = '';
            if ($Row['allowance3'] > 0) {
                $allowance3Row = '
                <tr>
                    <td width="35%" height="20px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD;"> '. $cpCfg["m.jobInformation.allowance3Lbl"] .'</td>
                    <td width="65%" height="20px" style="border-bottom: 1px solid #BABBBD;"> '.number_format($Row['allowance3'], 2).'</td>
                </tr>
                ';
            } else {
                $allowanceCount = $allowanceCount + 1;
            }

            $allowance4Row = '';
            if ($Row['allowance4'] > 0) {
                $allowance4Row = '
                <tr>
                    <td width="35%" height="20px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD;"> '.$cpCfg["m.jobInformation.allowance4Lbl"] .'</td>
                    <td width="65%" height="20px" style="border-bottom: 1px solid #BABBBD;"> '.number_format($Row['allowance4'], 2).'</td>
                </tr>
                ';
            } else {
                $allowanceCount = $allowanceCount + 1;
            }

            $allowance5Row = '';
            if ($Row['allowance5'] > 0) {
                $allowance5Row = '
                <tr>
                    <td width="35%" height="20px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD;"> '.$cpCfg["m.jobInformation.allowance5Lbl"] .'</td>
                    <td width="65%" height="20px" style="border-bottom: 1px solid #BABBBD;"> '.number_format($Row['allowance5'], 2).'</td>
                </tr>
                ';
            } else {
                $allowanceCount = $allowanceCount + 1;
            }

            $addExtraAllowanceRowForFormatting = "";
            if ($allowanceCount > 0) {
                for ($i= 1; $i <= $allowanceCount; $i++) {
                    $addExtraAllowanceRowForFormatting .= '
                    <tr>
                        <td width="35%" height="20px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD;"></td>
                        <td width="65%" height="20px" style="border-bottom: 1px solid #BABBBD;"></td>
                    </tr>
                    ';
                }
            }

            $deductionCount = 0; //adding extra row in deduction for formatting purpose
            if ($Row['sdl'] > 0) {
                $sdlRow = '
                <tr>
                    <td width="35%" height="20px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD;"> SDL</td>
                    <td width="65%" height="20px" style="border-bottom: 1px solid #BABBBD;"> '.number_format($Row['sdl'], 2).'</td>
                </tr>
                ';
            } else {
                $sdlRow = '';
                $deductionCount = $deductionCount + 1;
            }


            $govtPayRow = '';
            $govtPayAmt = '';
            if ($Row['govt_donation'] == 'CDAC' && $Row['pay_cdac'] > 0) {
                $govtPayRow = '
                <tr>
                    <td width="35%" height="20px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD;"> CDAC</td>
                    <td width="65%" height="20px" style="border-bottom: 1px solid #BABBBD;"> '.number_format($Row['pay_cdac'], 2).'</td>
                </tr>
                ';
                $govtPayAmt = $Row['pay_cdac'];
            } else if ($Row['govt_donation'] == 'SINDA' && $Row['pay_sinda'] > 0) {
                $govtPayRow = '
                <tr>
                    <td width="35%" height="20px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD;"> SINDA</td>
                    <td width="65%" height="20px" style="border-bottom: 1px solid #BABBBD;"> '.number_format($Row['pay_sinda'], 2).'</td>
                </tr>
                ';
                $govtPayAmt = $Row['pay_sinda'];
            } else if ($Row['govt_donation'] == 'MBMF' && $Row['pay_mbmf'] > 0) {
                $govtPayRow = '
                <tr>
                    <td width="35%" height="20px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD;"> MBMF</td>
                    <td width="65%" height="20px" style="border-bottom: 1px solid #BABBBD;"> '.number_format($Row['pay_mbmf'], 2).'</td>
                </tr>
                ';
                $govtPayAmt = $Row['pay_mbmf'];
            } else if ($Row['govt_donation'] == 'EUCF' && $Row['pay_eucf'] > 0) {
                $govtPayRow = '
                <tr>
                    <td width="35%" height="20px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD;"> EUCF</td>
                    <td width="65%" height="20px" style="border-bottom: 1px solid #BABBBD;"> '.number_format($Row['pay_eucf'], 2).'</td>
                </tr>
                ';
                $govtPayAmt = $Row['pay_eucf'];
            } else {
                $deductionCount = $deductionCount + 1;
            }

            $loanRow = '';
            if ($Row['loan_amount'] > 0) {
                $loanRow = '
                <tr>
                    <td width="35%" height="20px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD;"> Advance / Loan</td>
                    <td width="65%" height="20px" style="border-bottom: 1px solid #BABBBD;"> '.number_format($Row['loan_amount'], 2).'</td>
                </tr>
                ';
            } else {
                $deductionCount = $deductionCount + 1;
            }

            $deduction1Row = '';
            if ($Row['deduction1'] > 0) {
                $deduction1Row = '
                <tr>
                    <td width="35%" height="20px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD;"> '. $cpCfg["m.jobInformation.deduction1Lbl"] .'</td>
                    <td width="65%" height="20px" style="border-bottom: 1px solid #BABBBD;"> '.number_format($Row['deduction1'], 2).'</td>
                </tr>
                ';
            } else {
                $deductionCount = $deductionCount + 1;
            }

            $deduction2Row = '';
            if ($Row['deduction2'] > 0) {
                $deduction2Row = '
                <tr>
                    <td width="35%" height="20px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD;"> '. $cpCfg["m.jobInformation.deduction2Lbl"] .'</td>
                    <td width="65%" height="20px" style="border-bottom: 1px solid #BABBBD;"> '.number_format($Row['deduction2'], 2).'</td>
                </tr>
                ';
            } else {
                $deductionCount = $deductionCount + 1;
            }

            $deduction3Row = '';
            if ($Row['deduction3'] > 0) {
                $deduction3Row = '
                <tr>
                    <td width="35%" height="20px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD;"> '. $cpCfg["m.jobInformation.deduction3Lbl"] .'</td>
                    <td width="65%" height="20px" style="border-bottom: 1px solid #BABBBD;"> '.number_format($Row['deduction3'], 2).'</td>
                </tr>
                ';
            } else {
                $deductionCount = $deductionCount + 1;
            }

            $addExtraDeductionRowForFormatting = "";
            if ($deductionCount > 0) {
                for ($i= 1; $i <= $deductionCount; $i++) {
                    $addExtraDeductionRowForFormatting .= '
                    <tr>
                        <td width="35%" height="20px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD;"></td>
                        <td width="65%" height="20px" style="border-bottom: 1px solid #BABBBD;"></td>
                    </tr>
                    ';
                }
            }

            $TotalAllowanceAmt = $Row['allowance1'] + $Row['allowance2'] + $Row['allowance3'] + $Row['allowance4'] + $Row['allowance5'];
            $TotalAllowance = number_format($TotalAllowanceAmt, 2);
            $total_deductionAmt = $Row['cpf_employee'] + $Row['sdl'] + $govtPayAmt + $Row['loan_amount'] + $Row['deduction1'] + $Row['deduction2'] + $Row['deduction3'];
            $total_deduction = number_format($total_deductionAmt, 2);
            $OTAmt = $Row['ot_hours'] * $Row['overtime_pay_rate'];
            $OT = number_format($OTAmt, 2);
            $net_total_amt = $Row['basic_pay'] + $TotalAllowanceAmt - $total_deductionAmt + $OTAmt + $Row['reimbursement'];
            $net_total = number_format($net_total_amt, 2);

            $mode_of_payment = '';
            if ($Row['mode_of_payment']) {
                if ($Row['mode_of_payment'] == "cash") {
                    $mode_of_payment = "Cash / <del>Cheque</del> / <del>Bank Deposit</del>";
                } else if ($Row['mode_of_payment'] == "cheque") {
                    $mode_of_payment = "<del>Cash</del> / Cheque / <del>Bank Deposit</del>";
                } else if ($Row['mode_of_payment'] == "giro payment transfer") {
                    $mode_of_payment = "<del>Cash</del> / <del>Cheque</del> / Bank Deposit";
                }
            } else {
                if ($Row['job_info_payment_mode'] == "cash") {
                    $mode_of_payment = "Cash / <del>Cheque</del> / <del>Bank Deposit</del>";
                } else if ($Row['job_info_payment_mode'] == "cheque") {
                    $mode_of_payment = "<del>Cash</del> / Cheque / <del>Bank Deposit</del>";
                } else if ($Row['job_info_payment_mode'] == "giro payment transfer") {
                    $mode_of_payment = "<del>Cash</del> / <del>Cheque</del> / Bank Deposit";
                } else {
                    $mode_of_payment = "Cash / Cheque / Bank Deposit";
                }
            }

            $otDateText = '';
            $OTAmtDisplay = '';
            if ($Row['ot_hours'] > 0) {
                $otDateText = $start_date.' to '.$end_date;
                $OTAmtDisplay = $OT;
            }

            $reimbursementLbl = '';
            $reimbursementVal = '';
            $additionalPaymentTotal = 0;
            if ($Row['reimbursement'] > 0) {
                $reimbursementLbl = 'Reimbursement';
                $reimbursementVal = number_format($Row['reimbursement'], 2);
            }

            $directorFeeLbl = '';
            $directorFeeVal = '';
            if ($Row['director_fee'] > 0) {
                $directorFeeLbl = 'Director Fee';
                $directorFeeVal = number_format($Row['director_fee'], 2);
            }
            $total_additional_payment = $Row['reimbursement'] + $Row['director_fee'];
            $additionalPaymentTotal = number_format($total_additional_payment, 2);

            $tbl2 = '
            <table border="0" width="100%" style="font-size:12px;">
                <tr>
                    <th width="35%" style="background-color: #414042; color:#FFFFFF; font-weight:bold;"> Item</th>
                    <th width="65%" style="background-color: #414042; color:#FFFFFF; font-weight:bold;"> Amount (S$)</th>
                </tr>
                <tr>
                    <td width="35%" height="20px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD; background-color: #E6E7E8; font-weight:bold;"> Basic Pay</td>
                    <td width="55%" style="border-bottom: 1px solid #BABBBD;" height="20px"> '.number_format($Row['total_basic_pay_for_month'],2).'</td>
                    <td width="10%" height="20px" align="center" style="border-bottom: 1px solid #BABBBD; background-color: #E6E7E8; font-weight:bold;"></td>
                </tr>
                <tr>
                    <td width="35%" height="20px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD; background-color: #E6E7E8; font-weight:bold;"> Gross Pay</td>
                    <td width="55%" style="border-bottom: 1px solid #BABBBD;" height="20px"> '.number_format($Row['basic_pay'],2).'</td>
                    <td width="10%" height="20px" align="center" style="border-bottom: 1px solid #BABBBD; background-color: #E6E7E8; font-weight:bold;">(A)</td>
                </tr>
                <tr>
                    <td width="35%" height="20px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD; background-color: #E6E7E8; font-weight:bold;"> Total Allowance <br> (Breakdown shown below)</td>
                    <td width="55%" height="20px" style="border-bottom: 1px solid #BABBBD;"> '.$TotalAllowance.'</td>
                    <td width="10%" height="20px" align="center" style="border-bottom: 1px solid #BABBBD; background-color: #E6E7E8; font-weight:bold;">(B)</td>
                </tr>
                '. 
                $allowance1Row .
                $allowance2Row .
                $allowance3Row . 
                $allowance4Row .
                $allowance5Row . 
                $addExtraAllowanceRowForFormatting
                .'
                <tr>
                    <td width="35%" height="25px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD; background-color: #E6E7E8; font-weight:bold;"> Total Deductions <br> (Breakdown shown below)</td>
                    <td width="55%" height="25px" style="border-bottom: 1px solid #BABBBD;"> '.$total_deduction.'</td>
                    <td width="10%" height="25px" align="center" style="border-bottom: 1px solid #BABBBD; background-color: #E6E7E8; font-weight:bold;">(C)</td>
                </tr>
                <tr>
                    <td width="35%" height="20px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD;"> Employee`s CPF deduction</td>
                    <td width="65%" height="20px" style="border-bottom: 1px solid #BABBBD;"> '.$Row['cpf_employee'].'</td>
                </tr>
                '. 
                $sdlRow .
                $govtPayRow .
                $loanRow .
                $deduction1Row .
                $deduction2Row .
                $deduction3Row . 
                $addExtraDeductionRowForFormatting
                .'
                <tr style="background-color: #414042; color:#FFFFFF">
                    <td width="100%" style="font-weight:bold;"> Date Of Payment</td>
                </tr>
                <tr>
                    <td height="25px"> '.$generated_date.'</td>
                </tr>
                <tr style="background-color: #414042; color:#FFFFFF">
                    <td width="100%" style="font-weight:bold;"> Mode Of Payment</td>
                </tr>
                <tr>
                    <td height="25px"> '.$mode_of_payment.'</td>
                </tr>
                <tr style="background-color: #414042; color:#FFFFFF">
                    <th width="100%"> Overtime Details*</th>
                </tr>
                <tr>
                    <td width="40%" height="25px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD; background-color: #E6E7E8; font-weight:bold;"> Overtime Payment Period(s)</td>
                    <td width="50%" height="25px" style="border-bottom: 1px solid #BABBBD;"> '.$otDateText.'</td>
                    <td width="10%" height="25px" align="center" style="border-bottom: 1px solid #BABBBD; background-color: #E6E7E8; font-weight:bold;"></td>
                </tr>
                <tr>
                    <td width="40%" height="25px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD; background-color: #E6E7E8; font-weight:bold;"> Overtime Hours Worked</td>
                    <td width="50%" height="25px" style="border-bottom: 1px solid #BABBBD;"> '.$Row['ot_hours'].'</td>
                    <td width="10%" height="25px" align="center" style="border-bottom: 1px solid #BABBBD; background-color: #E6E7E8; font-weight:bold;"></td>
                </tr>
                <tr>
                    <td width="40%" height="25px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD; background-color: #E6E7E8; font-weight:bold;"> Total Overtime Pay</td>
                    <td width="50%" height="25px" style="border-bottom: 1px solid #BABBBD;"> '.$OTAmtDisplay.'</td>
                    <td width="10%" height="25px" align="center" style="border-bottom: 1px solid #BABBBD; background-color: #E6E7E8; font-weight:bold;"> (D) </td>
                </tr>
                <tr>
                    <th width="40%" style="background-color: #414042; color:#FFFFFF; font-weight:bold;"> Item</th>
                    <th width="60%" style="background-color: #414042; color:#FFFFFF; font-weight:bold;"> Amount (S$)</th>
                </tr>
                <tr>
                    <td width="40%" height="40px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD; background-color: #E6E7E8; font-weight:bold;"> Other Additional Payment (Breakdown shown below)<br/>&nbsp;'.$reimbursementLbl.'<br/>&nbsp;'.$directorFeeLbl.'</td>
                    <td width="50%" style="border-bottom: 1px solid #BABBBD;" height="40px"> '. $additionalPaymentTotal .'<br/><br/>&nbsp;'.$reimbursementVal.'<br/>&nbsp;'.$directorFeeVal.'</td>
                    <td width="10%" height="40px" align="center" style="border-bottom: 1px solid #BABBBD; background-color: #E6E7E8; font-weight:bold;"> (E) </td>
                </tr>
                <tr>
                    <td width="40%" height="25px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD; background-color: #E6E7E8; font-weight:bold;"> Net Pay (A+B-C+D+E)</td>
                    <td width="60%" height="25px" style="border-bottom: 1px solid #BABBBD;"> '.$net_total.'</td>
                </tr>
                <tr>
                    <td height="15px"></td>
                </tr>
                <tr style="background-color: #414042; color:#FFFFFF">
                    <td colspan="2" style="font-weight:bold;"> CPF Details</td>
                </tr>
                <tr>
                    <td width="40%" height="25px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD; background-color: #E6E7E8; font-weight:bold;"> Employer`s Contribution</td>
                    <td width="60%" height="25px" style="border-bottom: 1px solid #BABBBD;"> '.number_format($Row['cpf_employer'],2).'</td>
                </tr>
                <tr>
                    <td width="40%" height="25px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD; background-color: #E6E7E8; font-weight:bold;"> Employee`s Contribution</td>
                    <td width="60%" height="25px" style="border-bottom: 1px solid #BABBBD;"> '.number_format($Row['cpf_employee'],2).'</td>
                </tr>
            </table>
            ';

            $tbl3 = '
            <table border="0" width="100%" style="font-size:12px;">
                <tr>
                    <td width="65%"></td>
                    <td width="35%" text-align="right" style="border-bottom: 2px solid #000;"></td>
                </tr>
                <tr>
                    <td width="61%"></td>
                    <td width="39%" align="right">Signature of Employee</td>
                </tr>
            </table>
            ';

            $pdf->writeHTML($tbl2, true, false, false, false, '');
            $pdf->ln(4);
            if ($cpCfg['m.payrollManagement.hasEmployeeSignature'] == 1) {
                $pdf->writeHTML($tbl3, true, false, false, false, '');
            }
            $count ++;
        }
        $pdf->Output();
    }

    /**
     *
     */
    function getEditLoanPaymentHistory() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dateUtil = Zend_Registry::get('dateUtil');

        $payroll_management_id = $fn->getReqParam('payroll_management_id');

        $sqlLoanHist = "
        SELECT lrh.*
              ,l.type
              ,l.date
              ,l.amount
        FROM loan_repayment_history lrh
        LEFT JOIN (loan l) ON (lrh.loan_id = l.loan_id)
        WHERE lrh.payroll_management_id = '{$payroll_management_id}'
        ";
        $resultLoanHist = $db->sql_query($sqlLoanHist);
        $count = 1;
        $rows = '';
        while($rowLoanHist = $db->sql_fetchrow($resultLoanHist)) {
            $date = $dateUtil->formatDate($rowLoanHist['date'], 'DD-MM-YYYY');
            $total_loan_amt = number_format($rowLoanHist['amount'], 2);

            /* Check previous payment */
            $sqlLoanPrev = "
            SELECT SUM(loan_repayment_amount_per_month) AS total_repaid_amount
            FROM loan_repayment_history
            WHERE loan_id = {$rowLoanHist['loan_id']}
              AND generated_date < '{$rowLoanHist['generated_date']}'
            ";
            $resultLoanPrev = $db->sql_query($sqlLoanPrev);
            $rowLoanPrev = $db->sql_fetchrow($resultLoanPrev);
            $total_repaid_amt = number_format($rowLoanPrev['total_repaid_amount'], 2);

            $total_amount_payable = number_format($rowLoanHist['amount'] - $rowLoanPrev['total_repaid_amount'] - $rowLoanHist['loan_repayment_amount_per_month'],2);
            $expAmount = array('fieldCls' => 'txtRight');
            $pfx = $rowLoanHist['loan_repayment_history_id'] . '_' ;
            $rows .= "
            <tr>
                <td>{$count}</td>
                <td>{$rowLoanHist['type']} / {$date}</td>
                <td class='txtRight'>{$total_loan_amt}</td>
                <td class='txtRight'>{$total_repaid_amt}</td>
                <td>{$formObj->getTBRow('', "{$pfx}loan_repayment_amount_per_month", $rowLoanHist['loan_repayment_amount_per_month'], $expAmount)}</td>
                <td>{$formObj->getTARow('', "{$pfx}remarks", $rowLoanHist['remarks'])}</td>
                <td class='txtRight' class='amtBalance'>{$total_amount_payable}</td>
                <input type='hidden' name='loan_repayment_history_id[]'
                value='{$rowLoanHist['loan_repayment_history_id']}' />
            </tr>
            ";
            $count++;
        }

        $formAction = "index.php?module=payroll_payrollManagement&_spAction=editLoanPaymentHistorySubmit&showHTML=0";
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <table class='thinlist'>
                <thead>
                    <th>S.No</th>
                    <th>Loan Type/Date</th>
                    <th class='txtRight'>Total Loan Amount</th>
                    <th class='txtRight'>Total Amount Paid</th>
                    <th class='txtRight'>Amount paid now</th>
                    <th>Remarks</th>
                    <th class='txtRight'>Amount payable</th>
                </thead>
                <tbody>
                    {$rows}
                </tbody>
            </table>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getTerminatingEmployeeListForm() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $formAction = "index.php?_topRm=payroll&module=payroll_payrollManagement&_spAction=payslipFormSubmitForTerminatingEmployees&showHTML=0";

        $start_date = date('Y') . '-' . date('m') . '-' . '01';
        $end_date = date('Y-m-d');

        $rows = '';
        $sql = "
        SELECT DISTINCT e.employee_id
              ,e.first_name
              ,e.citizen
              ,e.nric_no
              ,e.fin_no
        FROM employee e
        LEFT JOIN (job_information ji) ON (e.employee_id = ji.employee_id)
        WHERE ji.termination_date != ''
          AND e.status = 'Current'
          AND ji.status = 'Archive'
        ";
        $result  = $db->sql_query($sql);
        $numRows = $db->sql_numrows($result);

        if ($numRows == '0') {
            return "<b>Sorry, no employee found for terminating payslips</b>";
        }

        $count = 1;
        while($row = $db->sql_fetchrow($result)) {

            $employeeCount = $fn->getRecordCount('job_information', "employee_id = {$row['employee_id']} AND status = 'Current'");
            if ($employeeCount == 0) {
                $sqlEmp = "
                SELECT j.job_information_id FROM job_information j
                WHERE j.status = 'Current'
                  AND j.employee_id = '{$row['employee_id']}'
                ";
                $resultEmp  = $db->sql_query($sqlEmp);
                $numRowsEmp = $db->sql_numrows($resultEmp);

                if ($numRowsEmp == 0) {
                    $sqlLoan = "
                    SELECT l.loan_id
                          ,l.amount AS total_loan_amount_taken
                    FROM loan l
                    WHERE l.employee_id = {$row['employee_id']}
                      AND l.status = 'Active'
                    ";
                    $resultLoan = $db->sql_query($sqlLoan);
                    $total_loan_amount_taken  = 0;
                    $total_loan_amount_repaid = 0;
                    $total_loan_amount_due = 0;
                    while($rowLoan = $db->sql_fetchrow($resultLoan)) {
                        $total_loan_amount_taken += $rowLoan['total_loan_amount_taken'];

                        $sqlLoanRepayment = "
                        SELECT SUM(loan_repayment_amount_per_month) AS total_loan_amount_repaid
                        FROM loan_repayment_history
                        WHERE employee_id = {$row['employee_id']}
                          AND loan_id = {$rowLoan['loan_id']}
                        ";
                        $resultLoanRepayment = $db->sql_query($sqlLoanRepayment);
                        $rowLoanRepayment = $db->sql_fetchrow($resultLoanRepayment);
                        
                        $total_loan_amount_repaid += $rowLoanRepayment['total_loan_amount_repaid'];
                    }

                    $total_loan_amount_due = $total_loan_amount_taken - $total_loan_amount_repaid;

                    if ($row['citizen'] == 'Citizen' || $row['citizen'] == 'PR') {
                        $id_no = "NRIC : {$row['nric_no']}";
                    } else {
                        $id_no = "FIN : {$row['fin_no']}";
                    }

                    $rows .= "
                    <tr>
                        <td>{$count}</td>
                        <td>{$row['first_name']}</td>
                        <td>{$id_no}</td>
                        <td>{$total_loan_amount_due}</td>
                    </tr>
                    ";
                    $count++;
                }
            }
        }

        $text = "
        <form id='portalFormTerminatingPayslip' class='yform columnar' method='post' action='{$formAction}'>
            <table id='' class='thinlist'>
                <thead>
                <tr>
                    <th>S.No</th>
                    <th>Employee Name</th>
                    <th>NRIC/FIN No</th>
                    <th>Balance Loan</th>
                </tr>
                </thead>
                <tbody>
                    {$rows}
                </tbody>
            </table>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getTotalLeavePerYear1($employee_id, $leave_type, $start_date, $end_date) {
        $db = Zend_Registry::get('db');

        $sqlLeave = "
        SELECT l.* FROM `leave` l
        WHERE l.employee_id = '{$employee_id}'
          AND l.leave_type = '{$leave_type}'
          AND l.from_date BETWEEN '{$start_date}' AND '{$end_date}'
        ";
        $resultLeave = $db->sql_query($sqlLeave);
        $total_leave = 0;
        while($rowLeave = $db->sql_fetchrow($resultLeave)) {
            $recDate_year_month = substr($rowLeave['to_date'], 0, 7);
            $endDate_year_month = substr($end_date, 0, 7);

            $total_leave += $rowLeave['no_of_days'];

            if ($recDate_year_month <= $endDate_year_month) {
                $total_leave += $rowLeave['no_of_days_next_month'];
            }

        }
        return $total_leave;
    }
}