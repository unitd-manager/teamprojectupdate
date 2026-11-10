<?
class CPL_Admin_Modules_Payroll_PayrollManagement_Model extends CP_Admin_Modules_Payroll_PayrollManagement_Model
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT pm.*
              ,CONCAT_WS(' ', e.first_name) AS employee_name
              ,e.position AS designation
              ,e.salary
              ,e.date_of_birth AS dob
              ,e.spr_year
              ,e.citizen
              ,e.status AS employee_status
        FROM payroll_management pm
        LEFT JOIN (employee e) ON (e.employee_id = pm.employee_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');

        $payroll_management_id = $fn->getReqParam('payroll_management_id');
        $status                = $fn->getReqParam('status');
        $employee_id           = $fn->getReqParam('employee_id');
        $year                  = $fn->getReqParam('year');
        $month                 = $fn->getReqParam('month');
        $employee_status       = $fn->getReqParam('employee_status');

        if ($payroll_management_id != "") {
            $searchVar->sqlSearchVar[] = "pm.payroll_management_id = '{$payroll_management_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "pm.payroll_management_id = '{$tv['record_id']}'";
        } else {
           /* $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'a.payroll_management_id');

            if ($status != "") {
                $searchVar->sqlSearchVar[] = "c.status = '{$status}'";
            }

            if ($category != "") {
                $searchVar->sqlSearchVar[] = "c.category = '{$category}'";
            }

            if ($company_name != "") {
                $searchVar->sqlSearchVar[] = "c.company_name LIKE '%{$company_name}%'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    c.company_name  LIKE '%{$tv['keyword']}%'
                    OR c.group_name LIKE '%{$tv['keyword']}%'
                    OR c.email      LIKE '%{$tv['keyword']}%'
                )";
            }*/

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

            $searchVar->sqlSearchVar[] = "pm.payroll_year = '{$year}'";
            $searchVar->sqlSearchVar[] = "pm.payroll_month = '{$month}'";

            if ($employee_id != "") {
                $searchVar->sqlSearchVar[] = "pm.employee_id = '{$employee_id}'";
            }

            if ($status != "") {
                $searchVar->sqlSearchVar[] = "pm.status = '{$status}'";
            }

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "pm.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(pm.flag != 1 OR pm.flag IS null)";
            }

            if ($employee_status != "") {
                $searchVar->sqlSearchVar[] = "e.status = '{$employee_status}'";
            }else{
                $searchVar->sqlSearchVar[] = "e.status = 'Current'";
            }

            $searchVar->sortOrder = "e.first_name ASC";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('employee_id', 'Please enter the employee name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $working_days_in_month = $fn->getPostParam('working_days_in_month');
        $actual_working_days = $fn->getPostParam('actual_working_days');
        $employee_status = $fn->getPostParam('employee_status');
        $ot_hours = $fn->getPostParam('ot_hours');

        $validate->resetErrorArray();
        /* Validation for entering actual working days - START */
        if ($employee_status == 'Archive') {
            $validate->validateData('actual_working_days', 'Please enter actual working days');            
        }

        if ($actual_working_days > $working_days_in_month) {
            $validate->errorArray['actual_working_days']['name'] = "actual_working_days";
            $validate->errorArray['actual_working_days']['msg']  = 'Please enter actual working days less than ' . $working_days_in_month;            
        }
        /* Validation for entering actual working days - END */

        if ($ot_hours > 72) {
            $msg = '&nbsp; Please enter OT hours less than 72 hours';
            $validate->validateData('error_box', $msg);
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn       = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $db       = Zend_Registry::get('db');
        $dateUtil       = Zend_Registry::get('dateUtil');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $payroll_management_id = $fn->getReqParam('payroll_management_id');
        $last_month_check = $fn->getReqParam('last_month_check');
        
        $sqlPayroll = "
        SELECT pm.*
        FROM payroll_management pm
        WHERE pm.payroll_management_id = '{$payroll_management_id}'
        ";
        $resultPayroll = $db->sql_query($sqlPayroll);
        $rowPayroll    = $db->sql_fetchrow($resultPayroll);

        $ot_hours           = $fn->getPostParam('ot_hours'); 
        $overtime_pay_rate  = $fn->getReqParam('overtime_pay_rate'); 
        $ot_amount          = $fn->getPostParam('ot_amount'); 
        $Commission         = $fn->getPostParam('commission'); 
        $allowance1         = $fn->getPostParam('allowance1'); 
        $allowance2         = $fn->getPostParam('allowance2');
        $allowance3         = $fn->getPostParam('allowance3');
        $allowance4         = $fn->getPostParam('allowance4');
        $allowance5         = $fn->getPostParam('allowance5');
        $actual_working_days = $fn->getPostParam('actual_working_days');
        $last_month_check  = $fn->getReqParam('last_month_check'); 

        /* Basic Pay for Terminating employees */
        $sqlBasicPay = "
        SELECT ji.basic_pay, ji.working_days, ji.status
        FROM job_information ji
        LEFT JOIN (employee e) ON (ji.employee_id = e.employee_id)
        WHERE ji.employee_id = '{$rowPayroll['employee_id']}'
        ORDER BY ji.job_information_id DESC LIMIT 0,1";
        $resultBasicPay = $db->sql_query($sqlBasicPay);
        $rowBasicPay = $db->sql_fetchrow($resultBasicPay);

        $basic_pay = $rowBasicPay['basic_pay'];
        /*
        $basic_pay = 0;
        if ($rowBasicPay['status'] == 'Current') {
            $basic_pay = $rowBasicPay['basic_pay'];
        } else if ($rowBasicPay['status'] == 'Archive'){
            $last_working_month = date("m",strtotime($rowPayroll['payslip_end_date']));

            if ($rowBasicPay['working_days'] == "5.0") {
                $working_days_in_month = $cpCfg['m.payroll.payrollManagement.monthlyWorkingDaysForFiveDaysWork'][$last_working_month];
            } else if ($rowBasicPay['working_days'] == "5.5") {
                $working_days_in_month = $cpCfg['m.payroll.payrollManagement.monthlyWorkingDaysForFiveHalfDaysWork'][$last_working_month];
            } else {
                $working_days_in_month = $cpCfg['m.payroll.payrollManagement.monthlyWorkingDaysForSixDaysWork'][$last_working_month];
            }

            $month_basic_pay = ($rowBasicPay['basic_pay'] / $working_days_in_month);
            $basic_pay = round($month_basic_pay * $actual_working_days);
        }
        */

        $last_working_month = date("m",strtotime($rowPayroll['payslip_end_date']));

        if ($rowBasicPay['working_days'] == "5.0") {
            $working_days_in_month = $cpCfg['m.payroll.payrollManagement.monthlyWorkingDaysForFiveDaysWork'][$last_working_month];
        } else if ($rowBasicPay['working_days'] == "5.5") {
            $working_days_in_month = $cpCfg['m.payroll.payrollManagement.monthlyWorkingDaysForFiveHalfDaysWork'][$last_working_month];
        } else {
            $working_days_in_month = $cpCfg['m.payroll.payrollManagement.monthlyWorkingDaysForSixDaysWork'][$last_working_month];
        }

        if ($last_month_check == 'Yes') {
            $actual_working_days = $rowPayroll['actual_working_days'];
        } else {
            $actual_working_days = $actual_working_days;
        }
        
        $month_basic_pay = ($rowPayroll['total_basic_pay_for_month'] / $working_days_in_month);
        $basic_pay = round($month_basic_pay * $actual_working_days);

        if ($actual_working_days) {
            $basic_pay_for_gross_pay = $basic_pay;
        } else {
            $basic_pay_for_gross_pay = $rowPayroll['total_basic_pay_for_month'];
        }

        // Added by ARIF for local employees not working
        $ot_amount = $ot_hours * $overtime_pay_rate;

        $gross_pay   = $basic_pay_for_gross_pay + $ot_amount + $Commission + $allowance1 + $allowance2 + $allowance3 + $allowance4 + $allowance5;
        $EmployeeRec = $fn->getRecordByCondition('employee', "employee_id = '{$rowPayroll['employee_id']}'");
        
        $total_contribution_amount = 0;
        $cpf = 0;
        $cpfE = 0;
        if($EmployeeRec['citizen'] == 'PR' || $EmployeeRec['citizen'] == 'Citizen'){
            $sprCondition = '';
            if($EmployeeRec['spr_year'] != '' && $EmployeeRec['citizen'] == 'PR'){
                $sprCondition = "AND spr_year = {$EmployeeRec['spr_year']}";
            } else {
                $sprCondition = "AND spr_year = 3";
            }

            $year = $rowPayroll['payroll_year'];
            $payslip_current_month_for_age = $rowPayroll['payroll_month'];
            //$payslip_current_month_for_age = $rowPayroll['payroll_month'] + 1;
            /* Latest commented by Arif 31-01-18 for Al-ansar payroll
            if ($payslip_current_month_for_age > 12) {
                $year = $year + 1; // next year
                $payslip_date_for_age = $year . '-01-01';
            } else {
                $payslip_date_for_age = $year . '-' . $payslip_current_month_for_age . '-' . '01';
            }
            */

            $payslip_date_for_age = $year . '-' . $payslip_current_month_for_age . '-' . '01';

            /* Find difference of age - START */
            //$age = $this->getAgeofEmployee($EmployeeRec['date_of_birth'], $payslip_date_for_age);
            $dob_for_age = $dateUtil->formatDate($EmployeeRec['date_of_birth'], 'DD-MM-YYYY');
            $dob_for_age = "01-" . substr($dob_for_age, 3);
            $payslipdate_for_age = "01-" . $payslip_current_month_for_age . '-' . $year;

            $age = $this->getFindage($dob_for_age, $payslipdate_for_age);
            /* Find difference of age - END */

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

            if ($gross_pay >= 501 && $gross_pay <= 749) {
                if ($age >= 0 && $age <= 55) {
                    $cpf_employee = (0.6*($gross_pay - 500)); // 0.6(Total wage - 500)
                } else if ($age >= 56 && $age <= 60) {
                    $cpf_employee = (0.39*($gross_pay - 500)); // 0.39(Total wage - 500)
                } else if ($age >= 61 && $age <= 65) {
                    $cpf_employee = (0.225*($gross_pay - 500)); // 0.225(Total wage - 500)
                } else if ($age >= 66 && $age <= 200) {
                    $cpf_employee = (0.15*($gross_pay - 500)); // 0.225(Total wage - 500)
                }

                $cpf_employer = (($gross_pay) * $rowPercentageCPF['by_employer'])/100;
                $total_contribution = $cpf_employer + $cpf_employee;
                $total_contribution_amount = round($total_contribution); // Total CPF contribution

                // CPF Employee contribution
                if (strpos($cpf_employee, "." ) !== false) {
                    list($int, $dec) = explode('.', $cpf_employee);
                    $cpfE = $int;
                } else {
                    $cpfE = $cpf_employee;
                }


                //list($int, $dec) = explode('.', $cpf_employee);
                //$cpfE = $int;

                // CPF Employer contribution
                $cpf = $total_contribution_amount - $cpfE;
            } else {

                /* CPF Total Calculation */
                $total_cpf_percent = $rowPercentageCPF['by_employee'] + $rowPercentageCPF['by_employer'];
                $total_contribution = (($gross_pay) * $total_cpf_percent)/100;
                $total_contribution_amount = round($total_contribution); // Total CPF contribution

                // CPF Employee contribution
                $cpfE = (($gross_pay) * $rowPercentageCPF['by_employee'])/100;
                if (strpos($cpfE, "." ) !== false) {
                    list($int, $dec) = explode('.', $cpfE);
                    $cpfE = $int;
                } else {
                    $cpfE = $cpfE;
                }

                // CPF Employer contribution
                $cpf = $total_contribution_amount - $cpfE;
                if ($cpf > $rowPercentageCPF['cap_amount_employer'] &&
                    $rowPercentageCPF['cap_amount_employer'] != 0){
                    $cpf = $rowPercentageCPF['cap_amount_employer'];
                }

                if($cpfE > $rowPercentageCPF['cap_amount_employee'] &&
                    $rowPercentageCPF['cap_amount_employee'] != 0){
                    $cpfE = $rowPercentageCPF['cap_amount_employee'];
                }
            }
        }

        $ot_amount = round($rowPayroll['overtime_pay_rate'] * $ot_hours, 2);

        if ($last_month_check == 'No') {
            $fa = $this->getFields();
            $fa['total_cpf_contribution']   = $total_contribution_amount;
            $fa['cpf_employer']             = $cpf;
            $fa['cpf_employee']             = $cpfE;
            $fa['ot_amount']                = $ot_amount;
            $fa['basic_pay']                = $basic_pay;
        } else {
            $fa = array();
        }

        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getSaveList(){
        $fn = Zend_Registry::get('fn');
        $fn->getSaveList();
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'employee_id');
        $fa = $fn->addToFieldsArray($fa, 'ot_hours');
        $fa = $fn->addToFieldsArray($fa, 'additional_wages');
        $fa = $fn->addToFieldsArray($fa, 'cpf_deduction');
        $fa = $fn->addToFieldsArray($fa, 'statutary_deduction');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'net_total');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'payroll_year');
        $fa = $fn->addToFieldsArray($fa, 'payroll_month');
        $fa = $fn->addToFieldsArray($fa, 'address_country');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'group_name');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'source');
        $fa = $fn->addToFieldsArray($fa, 'industry');
        $fa = $fn->addToFieldsArray($fa, 'company_size');
        $fa = $fn->addToFieldsArray($fa, 'supplier_type');
        $fa = $fn->addToFieldsArray($fa, 'loan_amount');
        $fa = $fn->addToFieldsArray($fa, 'loan_description');
        $fa = $fn->addToFieldsArray($fa, 'commission');
        $fa = $fn->addToFieldsArray($fa, 'sdl');
        $fa = $fn->addToFieldsArray($fa, 'ot_amount');
        $fa = $fn->addToFieldsArray($fa, 'basic_pay');
        $fa = $fn->addToFieldsArray($fa, 'overtime_pay_rate');
        $fa = $fn->addToFieldsArray($fa, 'department');
        $fa = $fn->addToFieldsArray($fa, 'cpf_account_no');
        $fa = $fn->addToFieldsArray($fa, 'pay_cdac');
        $fa = $fn->addToFieldsArray($fa, 'pay_sinda');
        $fa = $fn->addToFieldsArray($fa, 'pay_mbmf');
        $fa = $fn->addToFieldsArray($fa, 'pay_eucf');
        $fa = $fn->addToFieldsArray($fa, 'allowance1');
        $fa = $fn->addToFieldsArray($fa, 'allowance2');
        $fa = $fn->addToFieldsArray($fa, 'allowance3');
        $fa = $fn->addToFieldsArray($fa, 'allowance4');
        $fa = $fn->addToFieldsArray($fa, 'allowance5');
        $fa = $fn->addToFieldsArray($fa, 'deduction1');
        $fa = $fn->addToFieldsArray($fa, 'deduction2');
        $fa = $fn->addToFieldsArray($fa, 'deduction3');
        $fa = $fn->addToFieldsArray($fa, 'income_tax_amount');
        $fa = $fn->addToFieldsArray($fa, 'generated_date');
        $fa = $fn->addToFieldsArray($fa, 'paid_date');
        $fa = $fn->addToFieldsArray($fa, 'loan_deduction');
        $fa = $fn->addToFieldsArray($fa, 'actual_working_days');
        $fa = $fn->addToFieldsArray($fa, 'working_days_in_month');
        $fa = $fn->addToFieldsArray($fa, 'reimbursement');
        $fa = $fn->addToFieldsArray($fa, 'director_fee');
        $fa = $fn->addToFieldsArray($fa, 'mode_of_payment');

        return $fa;
    }

    /**
     *
     */

    function getpayslipprintPdf1() {
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

        $employee_id = $fn->getReqParam('employee_id');
        $payroll_management_id = $fn->getReqParam('payroll_management_id');

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
        WHERE pm.employee_id = '{$employee_id}'
        AND pm.payroll_management_id ='{$payroll_management_id}'
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $Row = $db->sql_fetchrow($result2);
        $company = $db->sql_fetchrow($result2);
        //============================================================================= //

        $pdf->SetFont('Courier','B',10);

        $today = date("d-m-Y");
        //$payroll_month = $fn->getCPDate($Row['payroll_month'], 'M');
        if($Row['citizen'] == 'PR' || $Row['citizen'] == 'Citizen'){
            $finNo ='
            <td width="21%" style="font-weight:normal;" align="right">Nric No  :</td>
            <td width="48%"> '.$Row['nric_no'].'</td>
            ';
        }else {
            $finNo ='
            <td width="21%" style="font-weight:normal;" align="right">Fin No  :</td>
            <td width="48%"> '.$Row['fin_no'].'</td>
            ';
        }

        $generated_date = $fn->getCPDate($Row['generated_date'], 'd-m-Y');
        $prefix_month = $dateUtil->getLongMonthName($Row['payroll_month']);

        $tbl1 = '
        <table border="0" width="100%" style="font-size:14px;">
            <tr>
                <td align="center" style="font-weight:bold; font-size:20px;">SALARY SLIP - '.$prefix_month.' '.$Row['payroll_year'].'<br/></td>
            </tr>
            <tr>
                <td width="22%" style="font-weight:normal;">Employee Name :</td>
                <td width="78%">'.$Row['employee_name'].'</td>
            </tr>
            <tr>
                '.$finNo.'
                <td width="15%" style="font-weight:normal;" align="right">Date:</td>
                <td width="16%" align="right"> '.$generated_date.'</td>
            </tr>
        </table>
        ';

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
                <td width="35%">MBF</td>
                 <td width="15%" align="right">'.number_format($Row['mbf'], 2).'</td>
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

        $pdf->ln(-5);
        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->ln(5);
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $pdf->Output();
    }

    /**
     *
     */
    function getpayslipprintPdf() {
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

        $employee_id = $fn->getReqParam('employee_id');
        $payroll_management_id = $fn->getReqParam('payroll_management_id');

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
              ,j.mode_of_payment AS job_info_payment_mode
              ,cpf.by_employer
              ,c.company_name
        FROM payroll_management pm
        LEFT JOIN (employee e) ON (e.employee_id = pm.employee_id)
        LEFT JOIN (job_information j) ON (j.employee_id = pm.employee_id)
        LEFT JOIN (cpf_calculator cpf) ON (cpf.cpf_calculator_id = pm.employee_id)
        LEFT JOIN (company c) ON (c.company_id = e.company_id)
        WHERE pm.employee_id = '{$employee_id}'
        AND pm.payroll_management_id ='{$payroll_management_id}'
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $Row = $db->sql_fetchrow($result2);
        $company = $db->sql_fetchrow($result2);
        //============================================================================= //

        $pdf->SetFont('Courier','B',10);

        $today = date("d-m-Y");
        if($Row['citizen'] == 'PR' || $Row['citizen'] == 'Citizen'){
            $finNo ='
            <tr style="background-color: #414042; color:#FFFFFF">
                <td style="font-weight:bold;"> Nric No</td>
            </tr>
            <tr>
                <td> '.$Row['nric_no'].'</td>
            </tr>
            ';
        }else {
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

        $year = date('Y');
        $payslip_current_month_for_age = $Row['payroll_month'] + 1;
        if ($payslip_current_month_for_age > 12) {
            $year = $year + 1; // next year
            $payslip_date_for_age = $year . '-01-01';
        } else {
            $payslip_date_for_age = $year . '-' . $payslip_current_month_for_age . '-' . '01';
        }

        // Find difference of age - START
        //$age = $this->getAgeofEmployee($EmployeeRec['date_of_birth'], $payslip_date_for_age);
        /*
        $age = 0;
        if ($Row['dob']) {
            $dob_for_age = $dateUtil->formatDate($Row['dob'], 'DD-MM-YYYY');
            $dob_for_age = "01-" . substr($dob_for_age, 3);
            $payslipdate_for_age = "01-" . $payslip_current_month_for_age . '-' . $year;

            $age = $this->getFindage($dob_for_age, $payslipdate_for_age);
        }
        */
        // Find difference of age - END
        
        $gross_pay = $Row['basic_pay'] + $Row['ot_amount'] + $Row['commission'] + $Row['allowance1'] + $Row['allowance2'] + $Row['allowance3'] + $Row['allowance4'] + $Row['allowance5'];
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
                <td width="35%" height="20px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD;"> '.$cpCfg["m.jobInformation.allowance2Lbl"] .'</td>
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
                <td width="35%" height="20px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD;"> '.$cpCfg["m.jobInformation.allowance3Lbl"] .'</td>
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
                <td width="35%" height="20px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD;"> '.$cpCfg["m.jobInformation.deduction2Lbl"] .'</td>
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
                <td width="35%" height="20px" style="border-bottom: 1px solid #BABBBD; border-right: 1px solid #BABBBD;"> '.$cpCfg["m.jobInformation.deduction3Lbl"] .'</td>
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
        $net_total_amt = $Row['basic_pay'] + $TotalAllowanceAmt - $total_deductionAmt + $OTAmt + $Row['reimbursement'] + $Row['director_fee'];
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

        $pdf->ln(-5);
        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(4);
        if ($cpCfg['m.payrollManagement.hasEmployeeSignature'] == 1) {
            $pdf->writeHTML($tbl3, true, false, false, false, '');
        }
        $pdf->Output(); // Open pdf in browser as new tab
        //$pdf->Output('payslip.pdf', 'D'); // Download pdf in local computer
    }  

    /**
     *
     */
    function getUpdateRecords() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        $current_Month = $fn->getReqParam('current_Month');
        $current_Year  = $fn->getReqParam('current_Year');

        $sqlemployee = "
        SELECT j.*
        FROM job_information j
        LEFT JOIN (employee e) ON (j.employee_id = e.employee_id)
        WHERE e.status = 'Current'
          AND j.status = 'Current'
          AND j.termination_date IS NULL
        ";
        /*
        $sqlemployee = "
        SELECT j.*
        FROM job_information j
        LEFT JOIN (employee e) ON (j.employee_id = e.employee_id)
        WHERE j.status = 'Current'
          AND j.termination_date IS NULL
          AND e.flag = 1
        ";
        */
        $resultemployee = $db->sql_query($sqlemployee);

        while ($rowemployee = $db->sql_fetchrow($resultemployee)) {

            $payMgmtRec = $fn->getRecordByCondition('payroll_management', "employee_id = '{$rowemployee['employee_id']}' AND payroll_month={$current_Month} AND payroll_year={$current_Year}");

            if($payMgmtRec['payroll_management_id'] == '') {

                $EmployeeRec = $fn->getRecordByCondition('employee', "employee_id = '{$rowemployee['employee_id']}'");
                
                $gross_pay = $rowemployee['basic_pay'] + $rowemployee['allowance1'] + $rowemployee['allowance2'] + $rowemployee['allowance3'] + $rowemployee['allowance4'] + $rowemployee['allowance5'];

                $year = $current_Year;
                $payslip_current_month_for_age = $current_Month + 1;
                if ($payslip_current_month_for_age > 12) {
                    $year = $year + 1; // next year
                    $payslip_date_for_age = $year . '-01-01';
                } else {
                    $payslip_date_for_age = $year . '-' . $payslip_current_month_for_age . '-' . '01';
                }
                
                /* Find difference of age - START */
                //$age = $this->getAgeofEmployee($EmployeeRec['date_of_birth'], $payslip_date_for_age);
                $dob_for_age = $dateUtil->formatDate($EmployeeRec['date_of_birth'], 'DD-MM-YYYY');
                $dob_for_age = "01-" . substr($dob_for_age, 3);
                $payslipdate_for_age = "01-" . $payslip_current_month_for_age . '-' . $year;

                $age = $this->getFindage($dob_for_age, $payslipdate_for_age);
                /* Find difference of age - END */

                $cpf = 0;
                $cpfE = 0;
                $total_contribution_amount = 0;
                $total_contribution_amount_correction = 0;
                if($EmployeeRec['citizen'] == 'PR' || $EmployeeRec['citizen'] == 'Citizen'){
                    $sprCondition = '';
                    if($EmployeeRec['spr_year'] != '' && $EmployeeRec['citizen'] == 'PR'){
                        $sprCondition = "AND spr_year = {$EmployeeRec['spr_year']}";
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
                      AND year = {$current_Year}
                      {$sprCondition}
                    ";
                    $resultPercentageCPF  = $db->sql_query($SQLPercentageCPF);
                    $rowPercentageCPF     = $db->sql_fetchrow($resultPercentageCPF);

                    if ($gross_pay >= 501 && $gross_pay <= 749) {
                        if ($age >= 0 && $age <= 55) {
                            $cpf_employee = (0.6*($gross_pay - 500)); // 0.6(Total wage - 500)
                        } else if ($age >= 56 && $age <= 60) {
                            $cpf_employee = (0.39*($gross_pay - 500)); // 0.39(Total wage - 500)
                        } else if ($age >= 61 && $age <= 65) {
                            $cpf_employee = (0.225*($gross_pay - 500)); // 0.225(Total wage - 500)
                        } else if ($age >= 66 && $age <= 200) {
                            $cpf_employee = (0.15*($gross_pay - 500)); // 0.225(Total wage - 500)
                        }

                        $cpf_employer = (($gross_pay) * $rowPercentageCPF['by_employer'])/100;
                        $total_contribution = $cpf_employer + $cpf_employee;
                        $total_contribution_amount = round($total_contribution); // Total CPF contribution

                        // CPF Employee contribution
                        list($int, $dec) = explode('.', $cpf_employee);
                        $cpfE = $int;

                        // CPF Employer contribution
                        $cpf = $total_contribution_amount - $cpfE;
                    } else {

                        /* CPF Total Calculation */
                        $total_cpf_percent = $rowPercentageCPF['by_employee'] + $rowPercentageCPF['by_employer'];
                        $total_contribution = (($gross_pay) * $total_cpf_percent)/100;
                        $total_contribution_amount = round($total_contribution); // Total CPF contribution

                        /*
                        $cpf = (($gross_pay) * $rowPercentageCPF['by_employer'])/100;
                        $cpf = round($cpf, 2);
                        */

                        // CPF Employee contribution
                        $cpfE = (($gross_pay) * $rowPercentageCPF['by_employee'])/100;
                        list($int, $dec) = explode('.', $cpfE);
                        $cpfE = $int;

                        $total_cap_amount_cpf = $rowPercentageCPF['cap_amount_employer'] + $rowPercentageCPF['cap_amount_employee'];
                        if ($total_contribution_amount > $total_cap_amount_cpf) {
                            $total_contribution_amount_correction = $total_cap_amount_cpf;
                        } else {
                            $total_contribution_amount_correction = $total_contribution_amount;
                        }

                        // CPF Employer contribution
                        $cpf = $total_contribution_amount - $cpfE;
                        if ($cpf > $rowPercentageCPF['cap_amount_employer'] &&
                            $rowPercentageCPF['cap_amount_employer'] != 0){
                            $cpf = $rowPercentageCPF['cap_amount_employer'];
                        }

                        if($cpfE > $rowPercentageCPF['cap_amount_employee'] &&
                            $rowPercentageCPF['cap_amount_employee'] != 0){
                            $cpfE = $rowPercentageCPF['cap_amount_employee'];
                        }
                    }
                }

                $fa = array();
                $fa['employee_id']        = $rowemployee['employee_id'];
                $fa['payroll_month']      = $current_Month;
                $fa['payroll_year']       = $current_Year;
                $fa['payslip_start_date'] = $current_Year . '-' . $current_Month . '-' . '01';

                /* Finding last date of the month */
                $a_date = $fa['payslip_start_date'];
                $date = new DateTime($a_date);
                $date->modify('last day of this month');
                $payslip_last_date = $date->format('d');

                $fa['payslip_end_date']   = $current_Year . '-' . $current_Month . '-' . $payslip_last_date;
                $fa['creation_date']      = date("Y-m-d H:i:s");
                $fa['created_by']         = $fn->getSessionParam('userName');
                $fa['status']             = "Generated";
                $fa['mode_of_payment']    = $rowemployee['mode_of_payment'];

                $fa['generated_date'] = date("Y-m-d");
                if ($cpCfg['m.payrollManagement.AutoChangePayslipDate'] == 1) {
                    $current_date = date("d");
                    if ($current_date > 7) {
                        $generated_date = date("Y-m-") . "7";
                        $fa['generated_date'] = $generated_date;                        
                    }
                }

                $fa['basic_pay']          = $rowemployee['basic_pay']; // gross pay
                $fa['total_basic_pay_for_month'] = $rowemployee['basic_pay']; // basic pay
                $fa['overtime_pay_rate']  = $rowemployee['overtime_pay_rate'];
                $fa['department']         = $rowemployee['department'];
                $fa['cpf_account_no']     = $rowemployee['cpf_account_no'];
                $fa['govt_donation']      = $rowemployee['govt_donation'];

                if ($rowemployee['govt_donation'] == 'CDAC') {
                    $fa['pay_cdac']      = $rowemployee['pay_cdac'];
                } else if ($rowemployee['govt_donation'] == 'SINDA') {
                    $fa['pay_sinda']     = $rowemployee['pay_sinda'];
                } else if ($rowemployee['govt_donation'] == 'MBMF') {
                    $fa['pay_mbmf']      = $rowemployee['pay_mbmf'];
                } else if ($rowemployee['govt_donation'] == 'EUCF') {
                    $fa['pay_eucf']      = $rowemployee['pay_eucf'];
                }

                /* SDL => Minimum is 2$ and maximum is 11.25$.
                   SDL is 0.25% of Basic pay, whichever is highest
                   SDL Calculation => (Basic pay * 0.25)/100
                */

                $sdl_amount = 0;
                if ($cpCfg['m.payrollManagement.hasSdlDeduction'] == 1) {
                    $sdl = (($rowemployee['basic_pay']*0.25)/100);
                    if ($sdl < 2) {
                        $sdl_amount = "2.00";
                    } else if ($sdl > 11.25) {
                        $sdl_amount = "11.25";
                    } else {
                        $sdl_amount = number_format($sdl, 2);
                    }
                } else if ($cpCfg['m.payrollManagement.sdlDeductionForLocalEmployeesOnly'] == 1) {
                    if($EmployeeRec['citizen'] == 'PR' || $EmployeeRec['citizen'] == 'Citizen') {
                        $sdl = (($rowemployee['basic_pay']*0.25)/100);
                        if ($sdl < 2) {
                            $sdl_amount = "2.00";
                        } else if ($sdl > 11.25) {
                            $sdl_amount = "11.25";
                        } else {
                            $sdl_amount = number_format($sdl, 2);
                        }
                    }
                }
                
                $fa['sdl']               = $sdl_amount;
                $fa['total_cpf_contribution'] = $total_contribution_amount_correction;
                $fa['cpf_employee']      = $cpfE;
                $fa['cpf_employer']      = $cpf;
                $fa['allowance1']        = $rowemployee['allowance1'];
                $fa['allowance2']        = $rowemployee['allowance2'];
                $fa['allowance3']        = $rowemployee['allowance3'];
                $fa['allowance4']        = $rowemployee['allowance4'];
                $fa['allowance5']        = $rowemployee['allowance5'];
                $fa['deduction1']        = $rowemployee['deduction1'];
                $fa['deduction2']        = $rowemployee['deduction2'];
                $fa['deduction3']        = $rowemployee['deduction3'];

                if ($rowemployee['working_days'] == '5.0') {
                    $working_days_in_month = $cpCfg['m.payroll.payrollManagement.monthlyWorkingDaysForFiveDaysWork'][$current_Month];
                } else if ($rowemployee['working_days'] == '5.5') {
                    $working_days_in_month = $cpCfg['m.payroll.payrollManagement.monthlyWorkingDaysForFiveHalfDaysWork'][$current_Month];
                } else {
                    $working_days_in_month = $cpCfg['m.payroll.payrollManagement.monthlyWorkingDaysForSixDaysWork'][$current_Month];
                }
                $fa['working_days_in_month'] = $working_days_in_month;
                $fa['actual_working_days']   = $working_days_in_month;

                $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'payroll_management');
                $result = $db->sql_query($SQL);
                $payroll_management_id = $db->sql_nextid();

                //Insert loan repayment details in history
                $loan_amount = $this->getTotalLoanAmountForEmployee($rowemployee['employee_id'], $payroll_management_id);
            }
        }
    }

    /**
     *
     */
    function getPayslipFormSubmitForTerminatingEmployees() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');
        $dateUtil = Zend_Registry::get('dateUtil');

        $sqlemployee = "
        SELECT ji.*
        FROM job_information ji
        LEFT JOIN (employee e) ON (ji.employee_id = e.employee_id)
        WHERE ji.termination_date != ''
          AND e.status = 'Current'
          AND ji.status = 'Archive'
        ";
        $resultemployee = $db->sql_query($sqlemployee);
        $numRows = $db->sql_numrows($resultemployee);
        while ($rowemployee = $db->sql_fetchrow($resultemployee)) {

            $current_Month = date("m",strtotime($rowemployee['termination_date']));
            $current_Year  = date("Y",strtotime($rowemployee['termination_date']));

            $payMgmtRec = $fn->getRecordByCondition('payroll_management', "employee_id = '{$rowemployee['employee_id']}' AND payroll_month={$current_Month} AND payroll_year={$current_Year}");

            // Checking if payslip already generated or not
            if($payMgmtRec['payroll_management_id'] == '') {

                $EmployeeRec = $fn->getRecordByCondition('employee', "employee_id = '{$rowemployee['employee_id']}'");
                
                $gross_pay = $rowemployee['basic_pay'] + $rowemployee['allowance1'] + $rowemployee['allowance2'] + $rowemployee['allowance3'] + $rowemployee['allowance4'] + $rowemployee['allowance5'];

                $year = date('Y');
                $payslip_current_month_for_age = $current_Month + 1;
                if ($payslip_current_month_for_age > 12) {
                    $year = $year + 1; // next year
                    $payslip_date_for_age = $year . '-01-01';
                } else {
                    $payslip_date_for_age = $year . '-' . $payslip_current_month_for_age . '-' . '01';
                }

                /* Find difference of age - START */
                //$age = $this->getAgeofEmployee($EmployeeRec['date_of_birth'], $payslip_date_for_age);
                $dob_for_age = $dateUtil->formatDate($EmployeeRec['date_of_birth'], 'DD-MM-YYYY');
                $dob_for_age = "01-" . substr($dob_for_age, 3);
                $payslipdate_for_age = "01-" . $payslip_current_month_for_age . '-' . $year;

                $age = $this->getFindage($dob_for_age, $payslipdate_for_age);
                /* Find difference of age - END */
                
                $cpf = 0;
                $cpfE = 0;
                $total_contribution_amount = 0;
                if($EmployeeRec['citizen'] == 'PR' || $EmployeeRec['citizen'] == 'Citizen'){
                    $sprCondition = '';
                    if($EmployeeRec['spr_year'] != '' && $EmployeeRec['citizen'] == 'PR'){
                        $sprCondition = "AND spr_year = {$EmployeeRec['spr_year']}";
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
                      AND year = {$current_Year}
                      {$sprCondition}
                    ";
                    $resultPercentageCPF  = $db->sql_query($SQLPercentageCPF);
                    $rowPercentageCPF     = $db->sql_fetchrow($resultPercentageCPF);

                    if ($gross_pay >= 501 && $gross_pay <= 749) {
                        if ($age >= 0 && $age <= 55) {
                            $cpf_employee = (0.6*($gross_pay - 500)); // 0.6(Total wage - 500)
                        } else if ($age >= 56 && $age <= 60) {
                            $cpf_employee = (0.39*($gross_pay - 500)); // 0.39(Total wage - 500)
                        } else if ($age >= 61 && $age <= 65) {
                            $cpf_employee = (0.225*($gross_pay - 500)); // 0.225(Total wage - 500)
                        } else if ($age >= 66 && $age <= 200) {
                            $cpf_employee = (0.15*($gross_pay - 500)); // 0.225(Total wage - 500)
                        }

                        $cpf_employer = (($gross_pay) * $rowPercentageCPF['by_employer'])/100;
                        $total_contribution = $cpf_employer + $cpf_employee;
                        $total_contribution_amount = round($total_contribution); // Total CPF contribution

                        // CPF Employee contribution
                        list($int, $dec) = explode('.', $cpf_employee);
                        $cpfE = $int;

                        // CPF Employer contribution
                        $cpf = $total_contribution_amount - $cpfE;
                    } else {
                        /* CPF Total Calculation */
                        $total_cpf_percent = $rowPercentageCPF['by_employee'] + $rowPercentageCPF['by_employer'];
                        $total_contribution = (($gross_pay) * $total_cpf_percent)/100;
                        $total_contribution_amount = round($total_contribution); // Total CPF contribution

                        // CPF Employee contribution
                        $cpfE = (($gross_pay) * $rowPercentageCPF['by_employee'])/100;
                        if (strpos($cpfE, "." ) !== false) {
                            list($int, $dec) = explode('.', $cpfE);
                            $cpfE = $int;
                        } else {
                            $cpfE = $cpfE;
                        }

                        // CPF Employer contribution
                        $cpf = $total_contribution_amount - $cpfE;
                        if ($cpf > $rowPercentageCPF['cap_amount_employer'] &&
                            $rowPercentageCPF['cap_amount_employer'] != 0){
                            $cpf = $rowPercentageCPF['cap_amount_employer'];
                        }

                        if($cpfE > $rowPercentageCPF['cap_amount_employee'] &&
                            $rowPercentageCPF['cap_amount_employee'] != 0){
                            $cpfE = $rowPercentageCPF['cap_amount_employee'];
                        }
                    }
                }

                $fa = array();
                $fa['employee_id']        = $rowemployee['employee_id'];
                $fa['payroll_month']      = $current_Month;
                $fa['payroll_year']       = $current_Year;
                $fa['payslip_start_date'] = $current_Year . '-' . $current_Month . '-' . '01';
                $fa['payslip_end_date']   = $rowemployee['termination_date'];
                $fa['creation_date']      = date("Y-m-d H:i:s");
                $fa['created_by']         = $fn->getSessionParam('userName');
                $fa['status']             = "Generated";
                $fa['generated_date']     = date("Y-m-d");
                $fa['basic_pay']          = $rowemployee['basic_pay']; // gross pay
                $fa['total_basic_pay_for_month'] = $rowemployee['basic_pay']; // basic pay
                $fa['overtime_pay_rate']  = $rowemployee['overtime_pay_rate'];
                $fa['department']         = $rowemployee['department'];
                $fa['cpf_account_no']     = $rowemployee['cpf_account_no'];
                $fa['govt_donation']      = $rowemployee['govt_donation'];

                if ($rowemployee['govt_donation'] == 'CDAC') {
                    $fa['pay_cdac']      = $rowemployee['pay_cdac'];
                } else if ($rowemployee['govt_donation'] == 'SINDA') {
                    $fa['pay_sinda']     = $rowemployee['pay_sinda'];
                } else if ($rowemployee['govt_donation'] == 'MBMF') {
                    $fa['pay_mbmf']      = $rowemployee['pay_mbmf'];
                } else if ($rowemployee['govt_donation'] == 'EUCF') {
                    $fa['pay_eucf']      = $rowemployee['pay_eucf'];
                }

                /* SDL => Minimum is 2$ and maximum is 11.25$.
                   SDL is 0.25% of Basic pay, whichever is highest
                   SDL Calculation => (Basic pay * 0.25)/100
                */

                $sdl_amount = 0;
                if ($cpCfg['m.payrollManagement.hasSdlDeduction']) {
                    $sdl = (($rowemployee['basic_pay']*0.25)/100);
                    if ($sdl < 2) {
                        $sdl_amount = "2.00";
                    } else if ($sdl > 11.25) {
                        $sdl_amount = "11.25";
                    } else {
                        $sdl_amount = number_format($sdl, 2);
                    }
                }
                
                $fa['sdl']               = $sdl_amount;
                $fa['total_cpf_contribution'] = $total_contribution_amount;
                $fa['cpf_employee']      = $cpfE;
                $fa['cpf_employer']      = $cpf;
                $fa['allowance1']        = $rowemployee['allowance1'];
                $fa['allowance2']        = $rowemployee['allowance2'];
                $fa['allowance3']        = $rowemployee['allowance3'];
                $fa['allowance4']        = $rowemployee['allowance4'];
                $fa['allowance5']        = $rowemployee['allowance5'];
                $fa['deduction1']        = $rowemployee['deduction1'];
                $fa['deduction2']        = $rowemployee['deduction2'];
                $fa['deduction3']        = $rowemployee['deduction3'];

                if ($rowemployee['working_days'] == '5.0') {
                    $working_days_in_month = $cpCfg['m.payroll.payrollManagement.monthlyWorkingDaysForFiveDaysWork'][$current_Month];
                } else if ($rowemployee['working_days'] == '5.5') {
                    $working_days_in_month = $cpCfg['m.payroll.payrollManagement.monthlyWorkingDaysForFiveHalfDaysWork'][$current_Month];
                } else {
                    $working_days_in_month = $cpCfg['m.payroll.payrollManagement.monthlyWorkingDaysForSixDaysWork'][$current_Month];
                }
                $fa['working_days_in_month'] = $working_days_in_month;

                $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'payroll_management');
                $result = $db->sql_query($SQL);
                $payroll_management_id = $db->sql_nextid();

                //Insert loan details
                $loan_amount = $this->getTotalLoanAmountForTerminatingEmployee($rowemployee['employee_id'], $payroll_management_id);

                //Update Employee & Job Information Status to Archive
                /*
                $sqlUpdate = "
                UPDATE job_information SET status = 'Archive'
                WHERE employee_id = '{$rowemployee['employee_id']}'
                  AND status = 'Current'
                ";
                $resultUpdate = $db->sql_query($sqlUpdate);
                */

                $faEmployee = array();
                $faEmployee['status'] = 'Archive';
                $fn->saveRecord($faEmployee, 'employee', 'employee_id', $rowemployee['employee_id']);
            }
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
     function getUpdateOverTimeAmount() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $hours = $fn->getReqParam('hours');
        $payRate = $fn->getReqParam('payRate');

        $ot_amount = $hours * $payRate;

        return $ot_amount;
    }

    /**
     *
     */
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'company_id'      => $phpExcel->getFldObj('Company ID')
             ,'company_name'    => $phpExcel->getFldObj('Company Name')
             ,'category'        => $phpExcel->getFldObj('Category')
             ,'company_size'    => $phpExcel->getFldObj('Company Size')
             ,'industry'        => $phpExcel->getFldObj('Industry')
             ,'source'          => $phpExcel->getFldObj('Source')
             ,'website'         => $phpExcel->getFldObj('Website')
             ,'phone'           => $phpExcel->getFldObj('Phone')
             ,'fax'             => $phpExcel->getFldObj('Fax')

             ,'address_flat'    => $phpExcel->getFldObj('Address Flat')
             ,'address_street'  => $phpExcel->getFldObj('Address Street')
             ,'address_town'    => $phpExcel->getFldObj('Address Town')
             ,'address_state'   => $phpExcel->getFldObj('Address State')
             ,'address_country' => $phpExcel->getFldObj('Address Country')

             ,'status'          => $phpExcel->getFldObj('Status')
             ,'comment_by'      => $phpExcel->getFldObj('Comment By')
             ,'notes'           => $phpExcel->getFldObj('Notes')
        );

        $config = array(
             'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }

    /**
     *
     */
    function getAccountCompanyPayrollContactLinkSQL($id) {

        return "
        SELECT a.contact_id
              ,a.first_name
              ,a.last_name
              ,a.email
              ,a.phone_direct
              ,a.mobile
              ,a.position
              ,a.department
        FROM company b, contact a
        WHERE a.company_id = b.company_id
          AND b.company_id = {$id}
        ";
    }

    /**
     *
     */
    function getPrintPayslipFormValidate(){
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $validate->resetErrorArray();
        $validate->validateData('payroll_year', 'Please Select Year');
        $validate->validateData('payroll_Month', 'Please Select Month');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getPrintPayslipFormSubmit(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getPrintPayslipFormValidate()){
            return $validate->getErrorMessageXML();
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getCPFCalculatorValueUpdate(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        //http://mcbicrm.localhost/admin/index.php?_topRm=payroll&module=payroll_payrollManagement&_spAction=CPFCalculatorValueUpdate&showHTML=0

        $sqlPayroll = "
        SELECT pm.*
        FROM payroll_management pm
        ";
        $resultPayroll = $db->sql_query($sqlPayroll);
        while($rowPayroll    = $db->sql_fetchrow($resultPayroll)){
            $gross_pay   = $rowPayroll['basic_pay'] + $rowPayroll['ot_amount'] + $rowPayroll['commission'] + $rowPayroll['allowance1'] + $rowPayroll['allowance2'] + $rowPayroll['allowance3'] + $rowPayroll['allowance4'] + $rowPayroll['allowance5'];
            $EmployeeRec = $fn->getRecordByCondition('employee', "employee_id = '{$rowPayroll['employee_id']}'");
            
            $cpf = 0;
            $cpfE = 0;
            if($EmployeeRec['citizen'] == 'PR' || $EmployeeRec['citizen'] == 'Citizen'){
                $sprCondition = '';
                if($EmployeeRec['spr_year'] != '' && $EmployeeRec['citizen'] == 'PR'){
                    $sprCondition = "AND spr_year = {$EmployeeRec['spr_year']}";
                } else {
                    $sprCondition = "AND spr_year = 3";
                }

                $year = date('Y');
                $payslip_current_month_for_age = $current_Month + 1;
                if ($payslip_current_month_for_age > 12) {
                    $year = $year + 1; // next year
                    $payslip_date_for_age = $year . '-01-01';
                } else {
                    $payslip_date_for_age = $year . '-' . $payslip_current_month_for_age . '-' . '01';
                }

                /* Find difference of age - START */
                //$age = $this->getAgeofEmployee($EmployeeRec['date_of_birth'], $payslip_date_for_age);
                $dob_for_age = $dateUtil->formatDate($EmployeeRec['date_of_birth'], 'DD-MM-YYYY');
                $dob_for_age = "01-" . substr($dob_for_age, 3);
                $payslipdate_for_age = "01-" . $payslip_current_month_for_age . '-' . $year;

                $age = $this->getFindage($dob_for_age, $payslipdate_for_age);
                /* Find difference of age - END */

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

            $fa = array();
            $fa['cpf_employer']   = $cpf;
            $fa['cpf_employee']   = $cpfE;
            
            $whereCondition  = "WHERE payroll_management_id = {$rowPayroll['payroll_management_id']} AND employee_id = {$rowPayroll['employee_id']}";
            $sqlUpdate       = $dbUtil->getUpdateSQLStringFromArray($fa, "payroll_management", $whereCondition);
            $resultUpdate    = $db->sql_query($sqlUpdate);
            print($sqlUpdate).'<br/>';
        }
    }

    /**
     *
     */
    function getTotalLoanAmountForEmployee($employee_id, $payroll_management_id){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        /* Find loans status which are active */
        $sqlLoan = "
        SELECT * FROM loan
        WHERE employee_id = {$employee_id}
          AND status = 'Active'";
        $resultLoan = $db->sql_query($sqlLoan);

        $total_loan_amount_payable = 0;
        while ($rowLoan = $db->sql_fetchrow($resultLoan)) {
            /* Find total amount paid earlier */
            $sqlLoanHist = "
            SELECT SUM(loan_repayment_amount_per_month) AS total_loan_amount_paid 
            FROM loan_repayment_history
            WHERE loan_id = {$rowLoan['loan_id']}
              AND employee_id = {$employee_id}
            ";
            $resultLoanHist = $db->sql_query($sqlLoanHist);
            $rowLoanHist = $db->sql_fetchrow($resultLoanHist);

            /* Check whether total amount has been paid */
            if ($rowLoan['amount'] == $rowLoanHist['total_loan_amount_paid']) {
                $total_loan_amount_payable += 0;
            } else {
                //Check loan month less than amount payable
                $total_balance_amount = $rowLoan['amount'] - $rowLoanHist['total_loan_amount_paid'];
                if ($total_balance_amount >= $rowLoan['month_amount']) {
                    $amount_paying = $rowLoan['month_amount'];
                } else {
                    $amount_paying = $total_balance_amount;
                }

                //$total_loan_amount_payable += $rowLoan['month_amount'];
                $total_loan_amount_payable += $amount_paying;

                $faLoanHist = array();
                $faLoanHist['payroll_management_id'] = $payroll_management_id;
                $faLoanHist['loan_id'] = $rowLoan['loan_id'];
                $faLoanHist['creation_date'] = date("Y-m-d H:i:s");
                $faLoanHist['created_by'] = $fn->getSessionParam('userName');
                $faLoanHist['loan_repayment_amount_per_month'] = $amount_paying;
                $faLoanHist['employee_id'] = $rowLoan['employee_id'];
                $faLoanHist['generated_date'] = date("Y-m-d");

                $sqlInsert = $dbUtil->getInsertSQLStringFromArray($faLoanHist, 'loan_repayment_history');
                $resultInsert = $db->sql_query($sqlInsert);
            }

            //Update loan status to closed if paid full amount
            $total_amount_paid_for_loan = $rowLoanHist['total_loan_amount_paid'] + $amount_paying;
            if ($rowLoan['amount'] == $total_amount_paid_for_loan) {
                $faLoan = array();
                $faLoan['status'] = 'Closed';
                $faLoan['loan_closing_date'] = date("Y-m-d");
                $fn->saveRecord($faLoan, 'loan', 'loan_id', $rowLoan['loan_id']);
            }
        }

        /* Updating payroll managament table with total loan amount */
        if ($total_loan_amount_payable > 0) {
            $SQLUpdate = "
            UPDATE payroll_management SET loan_amount = '{$total_loan_amount_payable}' 
            WHERE payroll_management_id = {$payroll_management_id}
                AND employee_id = '{$employee_id}'
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }
    }

    /**
     *
     */
    function getTotalLoanAmountForTerminatingEmployee($employee_id, $payroll_management_id){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        /* Find loans status which are active */
        $sqlLoan = "
        SELECT * FROM loan
        WHERE employee_id = {$employee_id}
          AND status = 'Active'";
        $resultLoan = $db->sql_query($sqlLoan);

        $total_loan_amount_payable = 0;
        while ($rowLoan = $db->sql_fetchrow($resultLoan)) {
            /* Find total amount paid earlier */
            $sqlLoanHist = "
            SELECT SUM(loan_repayment_amount_per_month) AS total_loan_amount_paid 
            FROM loan_repayment_history
            WHERE loan_id = {$rowLoan['loan_id']}
              AND employee_id = {$employee_id}
            ";
            $resultLoanHist = $db->sql_query($sqlLoanHist);
            $rowLoanHist = $db->sql_fetchrow($resultLoanHist);

            /* Check whether total amount has been paid */
            if ($rowLoan['amount'] == $rowLoanHist['total_loan_amount_paid']) {
                $total_loan_amount_payable += 0;
            } else {
                //Check loan month less than amount payable
                $total_balance_amount = $rowLoan['amount'] - $rowLoanHist['total_loan_amount_paid'];
                $amount_paying = $total_balance_amount;

                //$total_loan_amount_payable += $rowLoan['month_amount'];
                $total_loan_amount_payable += $amount_paying;

                $faLoanHist = array();
                $faLoanHist['payroll_management_id'] = $payroll_management_id;
                $faLoanHist['loan_id'] = $rowLoan['loan_id'];
                $faLoanHist['creation_date'] = date("Y-m-d H:i:s");
                $faLoanHist['created_by'] = $fn->getSessionParam('userName');
                $faLoanHist['loan_repayment_amount_per_month'] = $amount_paying;
                $faLoanHist['employee_id'] = $rowLoan['employee_id'];
                $faLoanHist['generated_date'] = date("Y-m-d");

                $sqlInsert = $dbUtil->getInsertSQLStringFromArray($faLoanHist, 'loan_repayment_history');
                $resultInsert = $db->sql_query($sqlInsert);
            }

            //Update loan status to closed if paid full amount
            $faLoan = array();
            $faLoan['status'] = 'Closed';
            $faLoan['loan_closing_date'] = date("Y-m-d");
            $fn->saveRecord($faLoan, 'loan', 'loan_id', $rowLoan['loan_id']);
        }

        /* Updating payroll managament table with total loan amount */
        if ($total_loan_amount_payable > 0) {
            $SQLUpdate = "
            UPDATE payroll_management SET loan_amount = '{$total_loan_amount_payable}' 
            WHERE payroll_management_id = {$payroll_management_id}
                AND employee_id = '{$employee_id}'
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }
    }

    /**
     *
     */
    function getEditLoanPaymentHistorySubmit() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $loan_repayment_history_id_arr  = $fn->getPostParam('loan_repayment_history_id', array());
        $count = count($loan_repayment_history_id_arr);

        $change_in_amount_paying = 0;
        for ($i= 0; $i< $count; $i++){
            $loan_repayment_history_id = $loan_repayment_history_id_arr[$i];
            $pfx = $loan_repayment_history_id . '_' ;
            $amount  = $fn->getPostParam("{$pfx}loan_repayment_amount_per_month");
            $remarks = $fn->getPostParam("{$pfx}remarks");

            $change_in_amount_paying += $amount; //adding total amount of loan amount

            $loanHistRec = $fn->getRecordRowById('loan_repayment_history', 'loan_repayment_history_id', $loan_repayment_history_id);
            $payroll_management_id = $loanHistRec['payroll_management_id'];
            // Any change in loan amount
            if (($loanHistRec['loan_repayment_amount_per_month'] != $amount) || $remarks != '') {
                $fa = array();
                $fa['loan_repayment_amount_per_month'] = $amount;
                $fa['remarks'] = $remarks;
                $fn->saveRecord($fa, 'loan_repayment_history', 'loan_repayment_history_id', $loan_repayment_history_id);
            }
        }

        //Checking and updating loan amount in payroll management table
        $payrollManagementRec = $fn->getRecordRowById('payroll_management', 'payroll_management_id', $payroll_management_id);
        if ($change_in_amount_paying != $payrollManagementRec['loan_amount']) {
            $fa1 = array();
            $fa1['loan_amount'] = $change_in_amount_paying;
            $fn->saveRecord($fa1, 'payroll_management', 'payroll_management_id', $payroll_management_id);
        }

        //Update loan status for payment
        /* Find total amount paid */
        $loanHistRec = $fn->getRecordRowById('loan_repayment_history', 'loan_repayment_history_id', $loan_repayment_history_id);
        $loanRec = $fn->getRecordRowById('loan', 'loan_id', $loanHistRec['loan_id']);
        $sqlLoanHist = "
        SELECT SUM(loan_repayment_amount_per_month) AS total_loan_amount_paid 
        FROM loan_repayment_history
        WHERE loan_id = {$loanHistRec['loan_id']}
        ";
        $resultLoanHist = $db->sql_query($sqlLoanHist);
        $rowLoanHist = $db->sql_fetchrow($resultLoanHist);

        $total_amount_paid_for_loan = $rowLoanHist['total_loan_amount_paid'];
        if ($loanRec['amount'] == $total_amount_paid_for_loan) {
            $faLoan = array();
            $faLoan['status'] = 'Closed';
            $faLoan['loan_closing_date'] = date("Y-m-d");
            $fn->saveRecord($faLoan, 'loan', 'loan_id', $loanRec['loan_id']);
        } else if (($loanRec['amount'] > $total_amount_paid_for_loan) 
          && ($loanRec['status'] == 'Closed')) {
            $faLoan = array();
            $faLoan['status'] = 'Active';
            $faLoan['loan_closing_date'] = '';
            $fn->saveRecord($faLoan, 'loan', 'loan_id', $loanRec['loan_id']);
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getPrintIr8aForm() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');

        //-----------------------------------------------------------------//
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/tbs_class.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_opentbs.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_html.php');

        $TBS = new clsTinyButStrong;
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);

        $employee_id = $fn->getReqParam('employee_id');
        $year = $fn->getReqParam('year');

        $template = 'ir8a form.docx';
        $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
        $TBS->LoadTemplate($templatePath);
        $rnd_no = mt_rand();
        $file_name = 'IR8A_' . $employee_id . '_' . $rnd_no . '.doc';
        $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);

        $path = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        $file_name_save = $path . '\\' . $file_name;
        $sourceFilePath = $file_name_save;

        $SQL = "
        SELECT e.citizen
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
              ,e.nric_no
              ,e.fin_no
              ,e.work_permit
              ,e.date_of_birth AS dob
              ,e.gender
              ,e.nationality
              ,e.address_area
              ,e.address_street
              ,gc.name AS employee_country
              ,e.address_po_code
        FROM employee e
        LEFT JOIN (payroll_management pm) ON (e.employee_id = pm.employee_id)
        LEFT JOIN (geo_country gc) ON (e.address_country = gc.country_code)
        WHERE pm.payroll_year = '{$year}'
          AND e.employee_id = '{$employee_id}'
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $sqlDesignation = "
        SELECT designation FROM job_information
        WHERE employee_id = '{$employee_id}'
          AND status = 'Current'
        ";
        $resultDesignation = $db->sql_query($sqlDesignation);
        $rowDesignation = $db->sql_fetchrow($resultDesignation);

        $sqlPmMonth = "
        SELECT SUM(basic_pay) AS total_amount
             , SUM(cpf_employee) AS total_cpf_amount
             , SUM(pay_cdac) AS total_cdac
             , SUM(pay_sinda) AS total_sinda
             , SUM(pay_mbmf) AS total_mbmf
             , SUM(pay_eucf) AS total_eucf
        FROM payroll_management
        WHERE payroll_year = '{$year}'
          AND employee_id = '{$employee_id}'
        ";
        $resultPmMonth = $db->sql_query($sqlPmMonth);
        $rowPmMonth = $db->sql_fetchrow($resultPmMonth);

        $total_govt_contributions = $rowPmMonth['total_cdac'] + $rowPmMonth['total_sinda'] + $rowPmMonth['total_mbmf'] + $rowPmMonth['total_eucf'];

        $dob = $fn->getCPDate($row['dob'], 'd/m/Y');
        if ($row['citizen'] == 'SP' || $row['citizen'] == 'WP'){
            $id_no = $row['work_permit'];
        } else if ($row['citizen'] == 'PR' || $row['citizen'] == 'Citizen'){
            $id_no = $row['nric_no'];
        } else {
            $id_no = $row['fin_no'];
        }

        $employee_address = $row['address_area'] . ' ' . $row['address_street'] . ' ' . $row['employee_country'] . ' ' . $row['address_po_code'];

        $valArr = array();
        $valArr['company_uen_no']   = $cpCfg['cp.companyUENNo'];
        $valArr['nric_fin_no']      = $id_no;
        $valArr['employee_name']    = $row['employee_name'];
        $valArr['dob']              = $dob;
        $valArr['gender']           = strtoupper($row['gender']);
        $valArr['nationality']      = strtoupper($row['nationality']);
        $valArr['employee_address'] = strtoupper($employee_address);
        $valArr['designation']      = strtoupper($rowDesignation['designation']);
        $valArr['total_amount']     = number_format($rowPmMonth['total_amount'],2);
        $valArr['total_cpf_amount'] = number_format($rowPmMonth['total_cpf_amount'],2);
        $valArr['total_govt']       = number_format($total_govt_contributions,2);
        $valArr['company_name']     = strtoupper($cpCfg['cp.companyName']);
        $valArr['company_address']  = strtoupper($cpCfg['cp.addressPdf1']);

        $blkMain   = array();
        $blkMain[] = $valArr;

        $TBS->MergeBlock('blkMain', $blkMain);
        $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
    }

    /**
     *
     */
    function getFindage($date_of_birth, $today) {

        /* Reference link
        https://stackoverflow.com/questions/10410877/how-can-i-calculate-the-age-of-a-person-in-year-month-days-in-php
        */
        $dob_a = explode("-", $date_of_birth);
        $today_a = explode("-", $today);
        $dob_d = $dob_a[0];$dob_m = $dob_a[1];$dob_y = $dob_a[2];
        $today_d = $today_a[0];$today_m = $today_a[1];$today_y = $today_a[2];
        $years = $today_y - $dob_y;
        $months = $today_m - $dob_m;
        if ($today_m.$today_d < $dob_m.$dob_d) {
            $years--;
            $months = 12 + $today_m - $dob_m;
        }

        if ($today_d < $dob_d) {
            $months--;
        }

        $firstMonths=array(1,3,5,7,8,10,12);
        $secondMonths=array(4,6,9,11);
        $thirdMonths=array(2);

        if($today_m - $dob_m == 1) {
            if(in_array($dob_m, $firstMonths)) {
                array_push($firstMonths, 0);
            } else if(in_array($dob_m, $secondMonths)) {
                array_push($secondMonths, 0);
            } else if(in_array($dob_m, $thirdMonths)) {
                array_push($thirdMonths, 0);
            }
        }

        $age = $years;
        $return_month = $months;

        // Increasing age to 1 if age month is more than 1
        if (($age == '55') || ($age == '60') || ($age == '65')) {
            if ($return_month > 0) {
                $age = $age + 1;
            }
        }

        return $age;
    }

    /**
     *
     */
    function getImportDataOld(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper', 'PhpExcelImportWrapper');
        $fa = array(
              'payslip_start_date' => $phpExcel->getImportFldObj('Payslip Start date')
             ,'payslip_end_date'   => $phpExcel->getImportFldObj('Payslip End date')
             ,'first_name'         => $phpExcel->getImportFldObj('Name of Employee')
             ,'fin_no'             => $phpExcel->getImportFldObj('Nric No')
             ,'passport'           => $phpExcel->getImportFldObj('Nric No')
             ,'status'             => $phpExcel->getImportFldObj('Employee Status')
             ,'basic_pay'          => $phpExcel->getImportFldObj('Basic Pay')
             ,'mode_of_payment'    => $phpExcel->getImportFldObj('Mode of Payment')
             ,'ot_hours'           => $phpExcel->getImportFldObj('OT Hours')
             ,'overtime_pay_rate'  => $phpExcel->getImportFldObj('OT rate/hour')
             ,'allowance1'         => $phpExcel->getImportFldObj('Allowance 1')
             ,'allowance2'         => $phpExcel->getImportFldObj('Allowance 2')
             ,'allowance3'         => $phpExcel->getImportFldObj('Allowance 3')
             ,'loan_amount'        => $phpExcel->getImportFldObj('Advance / Loan')
             ,'deduction1'         => $phpExcel->getImportFldObj('Deduction 1')
             ,'deduction2'         => $phpExcel->getImportFldObj('Deduction 2')
             ,'deduction3'         => $phpExcel->getImportFldObj('Deduction 3')
             ,'generated_date'     => $phpExcel->getImportFldObj('Date of Payment')
        );

        //$fa['address_country']['specialType'] = 'geo_country';
        $fa['date_of_birth']['defaultValue'] = "1985-01-01";
        $fa['payslip_start_date']['refOnly'] = true;
        $fa['payslip_end_date']['refOnly'] = true;
        $fa['basic_pay']['refOnly'] = true;
        $fa['mode_of_payment']['refOnly'] = true;
        $fa['ot_hours']['refOnly'] = true;
        $fa['overtime_pay_rate']['refOnly'] = true;
        $fa['allowance1']['refOnly'] = true;
        $fa['allowance2']['refOnly'] = true;
        $fa['allowance3']['refOnly'] = true;
        $fa['loan_amount']['refOnly'] = true;
        $fa['deduction1']['refOnly'] = true;
        $fa['deduction2']['refOnly'] = true;
        $fa['deduction3']['refOnly'] = true;
        $fa['generated_date']['refOnly'] = true;

        $config = array(
             'module'              => 'payroll_employee'
            ,'matchFieldArr'       => array('fin_no')
            ,'fldsArr'             => $fa
            ,'callbackAfterInsert' => 'importDataRowCallbackForPayslip'
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     */
    function getImportData(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper', 'PhpExcelImportWrapper');
        $fa = array(
              'payslip_start_date' => $phpExcel->getImportFldObj('Payslip Start date')
             ,'payslip_end_date'   => $phpExcel->getImportFldObj('Payslip End date')
             ,'first_name'         => $phpExcel->getImportFldObj('Name of Employee')
             ,'nric_no'            => $phpExcel->getImportFldObj('Nric No')
             ,'fin_no'             => $phpExcel->getImportFldObj('Fin No')
             ,'basic_pay'          => $phpExcel->getImportFldObj('Basic Pay')
             ,'reimbursement'      => $phpExcel->getImportFldObj('Reimbursement')
             ,'final_pay'          => $phpExcel->getImportFldObj('Final Pay')
             ,'generated_date'     => $phpExcel->getImportFldObj('Payment Date')
             ,'citizen'            => $phpExcel->getImportFldObj('Pass Type')
             ,'spr_year'           => $phpExcel->getImportFldObj('PR Year')
             ,'date_of_birth'      => $phpExcel->getImportFldObj('Date of Birth')
             ,'working_days'       => $phpExcel->getImportFldObj('Working days in week')
             ,'no_working_days'    => $phpExcel->getImportFldObj('No of Working days')
        );

        //$fa['address_country']['specialType'] = 'geo_country';
        $fa['status']['defaultValue'] = "Current";        
        $fa['payslip_start_date']['refOnly'] = true;
        $fa['payslip_end_date']['refOnly'] = true;
        $fa['basic_pay']['refOnly'] = true;
        $fa['reimbursement']['refOnly'] = true;
        $fa['final_pay']['refOnly'] = true;
        $fa['generated_date']['refOnly'] = true;
        $fa['working_days']['refOnly'] = true;  // working days in a week
        $fa['no_working_days']['refOnly'] = true; // for terminating employees

        $config = array(
             'module'              => 'payroll_employee'
            ,'matchFieldArr'       => array('fin_no', 'nric_no')
            ,'fldsArr'             => $fa
            ,'callbackAfterInsert' => 'importDataRowCallbackForPayslip'
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     */
    function getTotalLeave($employee_id, $leave_type, $start_date, $end_date) {
        $db = Zend_Registry::get('db');

        $sqlLeave = "
        SELECT l.* FROM `leave` l
        WHERE l.employee_id = '{$employee_id}'
          AND l.status = 'Approved'
          AND l.leave_type = '{$leave_type}'
          AND (l.from_date BETWEEN '{$start_date}' AND '{$end_date}'
            OR l.to_date BETWEEN '{$start_date}' AND '{$end_date}')
        ";
        $resultLeave = $db->sql_query($sqlLeave);
        $total_leave = 0;
        while($rowLeave = $db->sql_fetchrow($resultLeave)) {
            $fromDate_year_month = substr($rowLeave['from_date'], 0, 7);
            $toDate_year_month = substr($rowLeave['to_date'], 0, 7);
            $endDate_year_month = substr($end_date, 0, 7);

            if ($leave_type == 'Sick Leave') {
                if ($fromDate_year_month <= $endDate_year_month) {
                    $total_leave += $rowLeave['no_of_days'];
                }
            } else if ($leave_type == 'Annual Leave') {
                if ($fromDate_year_month <= $toDate_year_month) {
                    $total_leave += $rowLeave['no_of_days'];
                }
            } else if ($leave_type == 'Hospitalization Leave') {
                if ($fromDate_year_month <= $toDate_year_month) {
                    $total_leave += $rowLeave['no_of_days'];
                }
            } else if ($leave_type == 'Absent') {
                if ($fromDate_year_month <= $toDate_year_month) {
                    $total_leave += $rowLeave['no_of_days'];
                }
            }
            
            if ($toDate_year_month <= $endDate_year_month) {
                $total_leave += $rowLeave['no_of_days_next_month'];
            }

        }
        return $total_leave;
    }

    /**
     *
     */
    function getTotalLeavePerMonth($employee_id, $leave_type, $start_date, $end_date) {
        $db = Zend_Registry::get('db');

        $sqlLeave = "
        SELECT l.* FROM `leave` l
        WHERE l.employee_id = '{$employee_id}'
          AND l.status = 'Approved'
          AND l.leave_type = '{$leave_type}'
          AND (l.from_date BETWEEN '{$start_date}' AND '{$end_date}'
            OR l.to_date BETWEEN '{$start_date}' AND '{$end_date}')
        ";
        $resultLeave = $db->sql_query($sqlLeave);
        $total_leave = 0;
        while($rowLeave = $db->sql_fetchrow($resultLeave)) {
            $fromDate_year_month = substr($rowLeave['from_date'], 0, 7);
            $toDate_year_month = substr($rowLeave['to_date'], 0, 7);
            $endDate_year_month = substr($end_date, 0, 7);

            if ($fromDate_year_month == $endDate_year_month) {
                $total_leave += $rowLeave['no_of_days'];
            }

            if ($toDate_year_month == $endDate_year_month) {
                $total_leave += $rowLeave['no_of_days_next_month'];
            }

        }
        return $total_leave;
    }

    /**
     *
     */
    function getPrintIr8aFormInPdf() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

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

        $employee_id = $fn->getReqParam('employee_id');
        $year = $fn->getReqParam('year');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER,0);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 0);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();
        $SQL = "
        SELECT e.citizen
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
              ,e.nric_no
              ,e.fin_no
              ,e.work_permit
              ,e.date_of_birth AS dob
              ,e.gender
              ,e.nationality
              ,e.address_area
              ,e.address_street
              ,gc.name AS employee_country
              ,e.address_po_code
        FROM employee e
        LEFT JOIN (payroll_management pm) ON (e.employee_id = pm.employee_id)
        LEFT JOIN (geo_country gc) ON (e.address_country = gc.country_code)
        WHERE pm.payroll_year = '{$year}'
          AND e.employee_id = '{$employee_id}'
          AND (pm.status = 'Generated' OR pm.status = 'Approved' OR pm.status = 'Paid')
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $sqlDesignation = "
        SELECT designation FROM job_information
        WHERE employee_id = '{$employee_id}'
          AND status = 'Current'
        ";
        $resultDesignation = $db->sql_query($sqlDesignation);
        $rowDesignation = $db->sql_fetchrow($resultDesignation);

        $sqlPmMonth = "
        SELECT SUM(basic_pay) AS total_amount
             , SUM(cpf_employee) AS total_cpf_amount
             , SUM(pay_cdac) AS total_cdac
             , SUM(pay_sinda) AS total_sinda
             , SUM(pay_mbmf) AS total_mbmf
             , SUM(pay_eucf) AS total_eucf
             , SUM(director_fee) AS total_director_fee
             , SUM(allowance1) AS total_allowance1
             , SUM(allowance2) AS total_allowance2
             , SUM(allowance3) AS total_allowance3
             , SUM(allowance4) AS total_allowance4
             , SUM(allowance5) AS total_allowance5
        FROM payroll_management
        WHERE payroll_year = '{$year}'
          AND employee_id = '{$employee_id}'
          AND (status = 'Generated' OR status = 'Approved' OR status = 'Paid')
        ";
        $resultPmMonth = $db->sql_query($sqlPmMonth);
        $rowPmMonth = $db->sql_fetchrow($resultPmMonth);

        $total_overtime_amount = $this->getCalculateOvertimeAmountForIR8A($year, $employee_id);

        $gross_salary = $rowPmMonth['total_amount'] + $total_overtime_amount;

        $total_govt_contributions = $rowPmMonth['total_cdac'] + $rowPmMonth['total_sinda'] + $rowPmMonth['total_mbmf'] + $rowPmMonth['total_eucf'];
        $total_allowances345 = $rowPmMonth['total_allowance3'] + $rowPmMonth['total_allowance4'] + $rowPmMonth['total_allowance5'];
        $overallAllowances = $rowPmMonth['total_allowance1'] + $rowPmMonth['total_allowance2'] + $rowPmMonth['total_allowance3'] + $rowPmMonth['total_allowance4'] + $rowPmMonth['total_allowance5'];

        $dob = $fn->getCPDate($row['dob'], 'd/m/Y');
        /*
        if ($row['citizen'] == 'SP' || $row['citizen'] == 'WP'){
            $id_no = $row['work_permit'];
        } else 
        */
        if ($row['citizen'] == 'PR' || $row['citizen'] == 'Citizen'){
            $id_no = $row['nric_no'];
        } else {
            $id_no = $row['fin_no'];
        }

        $employee_address = $row['address_area'] . ' ' . $row['address_street'] . ' ' . $row['employee_country'] . ' ' . $row['address_po_code'];

        /* Date of Commencement check */
        $commencement_year_val = '';
        $sqlCommencement = "
        SELECT act_join_date FROM job_information
        WHERE employee_id = '{$employee_id}'
        ORDER BY job_information_id ASC LIMIT 0,1
        ";
        $resultCommencement = $db->sql_query($sqlCommencement);
        $rowCommencement = $db->sql_fetchrow($resultCommencement);
        if ($rowCommencement['act_join_date']) {
            $commencement_year = $fn->getCPDate($rowCommencement['act_join_date'], 'Y');
            if ($commencement_year == $cpCfg['cp.ir8aFormForYear']) {
                $commencement_year_val = $fn->getCPDate($rowCommencement['act_join_date'], 'd/m/Y');
            }
        }

        /* Date of Cessation check */
        $termination_year_val = '';
        $sqlTermination = "
        SELECT ji.termination_date FROM job_information ji
        LEFT JOIN (employee e) ON (ji.employee_id = e.employee_id)
        WHERE ji.employee_id = '{$employee_id}'
          AND ji.status = 'Archive'
          AND e.status = 'Archive'
        ORDER BY ji.job_information_id DESC LIMIT 0,1
        ";
        $resultTermination = $db->sql_query($sqlTermination);
        $rowTermination = $db->sql_fetchrow($resultTermination);
        if ($rowTermination['termination_date']) {
            $termination_year = $fn->getCPDate($rowTermination['termination_date'], 'Y');
            if ($termination_year == $cpCfg['cp.ir8aFormForYear']) {
                $termination_year_val = $fn->getCPDate($rowTermination['termination_date'], 'd/m/Y');
            }
        }

        $pdf->SetFont('helvetica');

        $tbl1 ='
        <table border="0" width="100%" cellpadding="2">
            <tr>
                <td width="40%" style="font-size:20px;font-weight:bold;">2019</td>
                <td width="60%" style="font-size:16px;font-weight:bold;">FORM IR8A</td>
            </tr>
            <tr align="center" style="background-color:#000; color:#fff; font-size:9px;">
                <td colspan="2"><b>Return of Employee’s Remuneration for the Year Ended 31 Dec 2018<br/>
                Fill in this form and give it to your employee by 1 Mar 2019<br/>
                (DO NOT SUBMIT THIS FORM TO IRAS UNLESS REQUESTED TO DO SO)</b>
                </td>
            </tr>
        </table>
        ';

        $tbl2 = '
        <table border="0" width="100%" cellpadding="2">
            <tr>
                <td style="font-size:9px;font-weight:bold;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This Form will take about 10 minutes to complete. Please get ready the employee’s personal particulars and details of his/her employment<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;income. Please read the explanatory notes when completing this form.
                </td>
            </tr>
        </table>
        ';

        $tbl3 = '
        <table border="1" width="100%" cellpadding="0" style="font-size:9px;">
            <tr>
                <td width="50%">&nbsp;&nbsp;&nbsp;&nbsp;Employer’s Tax Ref. No. / UEN<br/>&nbsp;&nbsp;&nbsp;&nbsp;'. $cpCfg['cp.companyUENNo'] .'</td>
                <td width="50%">&nbsp;&nbsp;&nbsp;&nbsp;Employee’s Tax Ref. No.&nbsp;&nbsp;: &nbsp;&nbsp;*NRIC / FIN (Foreign Identification No.)<br/>&nbsp;&nbsp;&nbsp;&nbsp;'. $id_no .'</td>
            </tr>
            <tr>
                <td width="50%">&nbsp;&nbsp;&nbsp;&nbsp;Full Name of Employee as per NRIC / FIN<br/>&nbsp;&nbsp;&nbsp;&nbsp;'. strtoupper($row['employee_name']) .'</td>
                <td width="26%">&nbsp;&nbsp;&nbsp;&nbsp;Date of Birth<br/>&nbsp;&nbsp;&nbsp;&nbsp;'. $dob .'</td>
                <td width="8%">&nbsp;&nbsp;&nbsp;&nbsp;Sex<br/>&nbsp;&nbsp;&nbsp;&nbsp;'. strtoupper($row['gender']) .'</td>
                <td width="16%">&nbsp;&nbsp;&nbsp;&nbsp;Nationality<br/>&nbsp;&nbsp;&nbsp;&nbsp;'. strtoupper($row['nationality']) .'</td>
            </tr>
            <tr>
                <td width="50%">&nbsp;&nbsp;&nbsp;&nbsp;Residential Address<br/>&nbsp;&nbsp;&nbsp;&nbsp;'. strtoupper($employee_address) .'</td>
                <td width="26%">&nbsp;&nbsp;&nbsp;&nbsp;Designation<br/>&nbsp;&nbsp;&nbsp;&nbsp;'. strtoupper($rowDesignation['designation']) .'</td>
                <td width="24%">&nbsp;&nbsp;&nbsp;&nbsp;Bank to which salary is credited</td>
            </tr>
            <tr>
                <td width="50%">&nbsp;&nbsp;&nbsp;&nbsp;If employment commenced and/or ceased during the year, state:<br/>&nbsp;&nbsp;&nbsp;&nbsp;<b>(See Explanatory Note 5)</b></td>
                <td width="26%">&nbsp;&nbsp;&nbsp;&nbsp;Date of Commencement<br/>&nbsp;&nbsp;&nbsp;&nbsp;'.$commencement_year_val .'</td>
                <td width="24%">&nbsp;&nbsp;&nbsp;&nbsp;Date of Cessation<br/>&nbsp;&nbsp;&nbsp;&nbsp;'.$termination_year_val .'</td>
            </tr>
        </table>
        ';

        $tbl4 ='
        <table border="0" width="100%" cellpadding="1" style="font-size:9px;">
            <tr style="background-color:#000; color:#fff;">
                <td colspan="2" style="font-weight:bold;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;INCOME (See Explanatory Note 9 unless otherwise specified)</td>
            </tr>
            <tr>
                <td style="line-height:0.5px;">&nbsp;</td>
            </tr>
            <tr>
                <td width="88%">a)&nbsp;&nbsp;&nbsp;<b>Gross Salary, Fees, Leave Pay, Wages and Overtime Pay</b></td>
                <td width="12%" style="border:1px solid #333;text-align:right;">'. number_format($gross_salary,2).'</td>
            </tr>
            <tr>
                <td style="line-height:0.5px;">&nbsp;</td>
            </tr>            
            <tr>
                <td width="88%">b)&nbsp;&nbsp;&nbsp;<b>Bonus</b> (non-contractual bonus paid in 2018 and/or contractual bonus) </td>
                <td width="12%" style="border:1px solid #333;text-align:right;">0.00</td>
            </tr>
            <tr>
                <td style="line-height:0.5px;">&nbsp;</td>
            </tr>            
            <tr>
                <td width="88%">c)&nbsp;&nbsp;&nbsp;<b>Director’s fees</b> (approved at the company’s AGM/EGM on '. $cpCfg['cp.ir8aForm.DirectorFeeAgmDate'] .') </td>
                <td width="12%" style="border:1px solid #333;text-align:right;">'. number_format($rowPmMonth['total_director_fee'],2).'</td>
            </tr>
            <tr>
                <td style="line-height:0.5px;">&nbsp;</td>
            </tr>            
            <tr>
                <td width="88%">d)&nbsp;&nbsp;&nbsp;<b>Others:</b></td>
                <td width="12%"></td>
            </tr>
            <table border="0" width="100%" cellpadding="2">
                <tr>
                    <td width="38%"><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        1. Allowances:  (i) Transport &nbsp;&nbsp;  $ <u>&nbsp;'. number_format($rowPmMonth['total_allowance1'], 2).'&nbsp;</u>
                    </td>
                    <td width="25%">
                        (ii) Entertainment &nbsp;&nbsp;  $ <u>&nbsp;'. number_format($rowPmMonth['total_allowance2'], 2).'&nbsp;</u> 
                    </td>
                    <td width="25%">
                        (iii) Others &nbsp;&nbsp;  $ <u>&nbsp;'. number_format($total_allowances345, 2).'&nbsp;</u> 
                    </td>
                    <td width="12%" style="border-bottom:1px solid #333;text-align:right;">'. number_format($overallAllowances,2).'</td>
                </tr>
                <tr>
                    <td width="58%"><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        2. Gross Commission for the period '. $cpCfg['cp.ir8aForm.grossCommissionFromDate'] .'  to '. $cpCfg['cp.ir8aForm.grossCommissionToDate'] .'   
                    </td>
                    <td width="30%">
                        * Monthly and/or other adhoc payment       
                    </td>
                    <td width="12%" style="border-bottom:1px solid #333;text-align:right;">0.00</td>
                </tr>
                <tr>
                    <td width="88%"><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        3. Pension
                    </td>
                    <td width="12%" style="border-bottom:1px solid #333;text-align:right;">0.00</td>
                </tr>
                <tr>
                    <td width="88%"><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        4. Lump sum payment                                                                                                                                                                              
                    </td>
                    <td width="12%" style="border-bottom:1px solid #333;text-align:right;">0.00</td>
                </tr>

                <tr>
                    <td colspan="2"><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        <table border="1" width="95%" cellpadding="3">
                            <tr>
                                <td width="30%">
                                    (i) Gratuity $&nbsp; 0.00
                                </td>
                                <td width="30%">
                                    (ii) Notice Pay $&nbsp; 0.00
                                </td>
                                <td width="40%">
                                    (iii) Ex-gratia payment $&nbsp; 0.00
                                </td>
                            </tr> 
                            <tr>
                                <td width="100%">
                                    (iv) Others (please state nature) $&nbsp; 0.00
                                </td>
                            </tr>
                            <tr>
                                <td width="100%">
                                    (v) Compensation for loss of office $&nbsp; 0.00 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Approval obtained from IRAS: *Yes/No &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  Date of approval: 
                                </td>
                            </tr> 
                            <tr>
                                <td width="50%">
                                    <b>Reason for payment:</b>
                                </td>
                                <td width="50%">
                                    <b>Length of service within the company/group:</b>
                                </td>
                            </tr> 
                            <tr>
                                <td width="100%">
                                    <b>Basis of arriving at the payment:</b>  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;    (Give details separately if space is insufficient)       
                                </td>
                            </tr>   
                        </table>
                    </td>
                </tr>
                <tr>
                    <td width="88%" style="line-height:11px;"><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        5. Retirement benefits including gratuities/pension/commutation of pension/lump sum payments, etc from <br/>
                        <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pension/Provident Fund:  Name of Fund</span><br/>
                        <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(Amount accrued up to 31 Dec 1992  $&nbsp;0.00)</span><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Amount accrued from 1993:</span>
                    </td>
                    <td width="12%" style="border-bottom:1px solid #333;text-align:right;"><br/><br/><br/>0.00</td>
                </tr>
                <tr>
                    <td width="88%" style="line-height:11px;"><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        6. Contributions made by employer to any Pension/Provident Fund constituted outside Singapore <u>without</u> tax concession:  
                           <br/><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Contributions made by employer to any Pension/Provident Fund constituted outside Singapore <u>with</u> tax concession:</span>

                    </td>
                    <td width="12%" style="border-bottom:1px solid #333;text-align:right;"><br/><br/>0.00</td>
                </tr>
                <tr>
                    <td width="88%"><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        <table border="1" width="100%" cellpadding="2">
                            <tr>
                                <td width="100%">&nbsp;&nbsp;&nbsp;Name of the overseas pension/provident fund:</td>
                            </tr>
                            <tr>
                                <td width="50%">&nbsp;&nbsp;&nbsp;Full Amount of the contributions :</td>
                                <td width="50%">&nbsp;&nbsp;&nbsp;Are contributions mandatory: &nbsp;&nbsp;*Yes/No</td>
                            </tr>
                            <tr>
                                <td width="100%">&nbsp;&nbsp;&nbsp;Were contributions charged / deductions claimed by a Singapore permanent establishment: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*Yes/No</td>
                            </tr>
                        </table>
                    </td>
                    <td width="12%">
                    </td>
                </tr>
                <tr>
                    <td width="88%"><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        7. Excess/Voluntary contribution to CPF by employer (less amount refunded/to be refunded): <br/>
                        <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>[Complete the Form IR8S]</b></span>
                    </td>
                    <td width="12%" style="border-bottom:1px solid #333;text-align:right;"><br/><br/>0.00</td>
                </tr>
                <tr>
                    <td width="88%"><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        8. Gains or profits from Employee Stock Option (ESOP)/other forms of Employee Share Ownership (ESOW) Plans:
                        <br/><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>[Complete the Appendix 8B]</b></span>
                    </td>
                    <td width="12%" style="border-bottom:1px solid #333;text-align:right;"><br/><br/>0.00</td>
                </tr>
                <tr>
                    <td width="88%"><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        9. Value of Benefits-in-kind <b>[See Explanatory Note 12 and complete Appendix 8A]</b>
                    </td>
                    <td width="12%">
                    </td>
                </tr>
                <tr>
                    <td width="50%">
                    </td>
                    <td width="38%"><b>TOTAL (items d1 to d9)</b>
                    </td>
                    <td width="12%" style="border:1px solid #333;text-align:right;">0.00</td>
                </tr>
            </table>
            <tr>
                <td width="88%">e)&nbsp;&nbsp;&nbsp;1. Remission: Amount of Income $&nbsp;0.00
                                   <br/><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2. Overseas Posting: *Full Year/Part of the Year <b>(See Explanatory Note 8a)</b></span>
                                    <br/><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3. Exempt Income: $&nbsp;0.00 <b>(See Explanatory Note 8b)</b></span></td>
                <td width="12%"></td>
            </tr>
            <tr>
                <td width="100%">f)<span>&nbsp;&nbsp;&nbsp;</span>
                    <table border="1" cellpadding="4" width="100%" style="font-size:9px;">
                        <tr>
                            <td width="20%" rowspan="3" style="line-height:13px;">Employee’s income<br/>tax borne by<br/>employer?<br/>* YES / NO </td>
                            <td width="80%">If tax is fully borne by employer, DO NOT enter any amount in (i) and (ii)</td>                                    
                        </tr>
                        <tr>
                            <td width="68%">(i) If tax is partially borne by employer, state the amount of income for which tax is borne by employer   </td>
                            <td width="12%" style="text-align:right;">0.00</td>
                        </tr>
                        <tr>
                            <td width="68%">(ii) If a fixed amount of tax is borne by employee, state the amount of tax to be paid by employee</td>
                            <td width="12%" style="text-align:right;">0.00</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        '; 

        $tbl5 ='<table border="1" width="100%" cellpadding="3">
                    <tr style="background-color:#333; color:#fff; font-size:9px;">
                        <td width="100%"><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><b>DEDUCTIONS (See Explanatory Note 10 - Deductions)</b></td>
                    </tr>
                    <tr style="font-size:9px;">
                        <td width="88%"><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>EMPLOYEE’S COMPULSORY contribution to *CPF/Designated Pension or Provident Fund <b>(less amount refunded/to be refunded)</b><br/><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>Name of Fund :
                            <br/><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>(Apply the appropriate CPF rates published by CPF Board on its website ‘www.cpf.gov.sg’.  Do not include excess/voluntary<br/><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>contributions to CPF,voluntary contributions to Medisave Account, voluntary contributions to Retirement Sum Topping-up Scheme,<br/><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>SRS contributions and contributions to Overseas Pension or Provident Fund in this item) 
                        </td>
                        <td width="12%" style="text-align:right;"><br/><br/><br/>'. number_format($rowPmMonth['total_cpf_amount'],2) .'</td>
                    </tr>
                    <tr style="font-size:9px;">
                        <td width="88%"><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><b>Donations</b> deducted from salaries for:
                            <br/><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>*Yayasan Mendaki Fund/Community Chest of Singapore/SINDA/CDAC/ECF/Other tax exempt donations
                        </td>
                        <td width="12%" style="text-align:right;"><br/><br/>'. number_format($total_govt_contributions,2) .'</td>
                    </tr>
                    <tr style="font-size:9px;">
                        <td width="88%">
                            &nbsp;&nbsp;&nbsp;&nbsp;<b>Contributions</b> deducted from salaries to Mosque Building Fund :
                        </td>
                        <td width="12%" style="text-align:right;">0.00</td>
                    </tr>
                    <tr style="font-size:9px;">
                        <td width="88%">
                            &nbsp;&nbsp;&nbsp;&nbsp;<b>Life Insurance premiums</b> deducted from salaries:
                        </td>
                        <td width="12%" style="text-align:right;">0.00</td>
                    </tr>
                </table>
                <table border="0" width="100%" cellpadding="3">
                    <tr style="background-color:#333; color:#fff; font-size:9px;">
                        <td colspan="3" style="border:1px solid #000;"><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><b>DECLARATION (See Explanatory Note 2)</b></td>
                    </tr>
                    <tr style="font-size:9px;">
                        <td width="14%" style="border-left:1px solid #000;">Name of Employer :</td>
                        <td width="83%" style="border-bottom:1px solid #000;">&nbsp;'.$cpCfg['cp.companyName'].'</td>
                        <td width="3%" style="border-right:1px solid #000;"></td>
                    </tr>
                    <tr style="font-size:9px;">
                        <td width="15%" style="border-left:1px solid #000;">Address of Employer :</td>
                        <td width="82%" style="border-bottom:1px solid #000;">&nbsp;'.$cpCfg['cp.ir8aForm.employerAddress'].'</td>
                        <td width="3%" style="border-right:1px solid #000;"></td>
                    </tr>
                    <tr style="font-size:9px;text-align:center;">
                        <td width="35%" style="border-left:1px solid #000;border-bottom:1px solid #000;text-align:left;"><u>'. $cpCfg['cp.ir8aForm.authorisedPerson'] .'</u><br/>Name of authorised person making the declaration</td>
                        <td width="28%" style="border-bottom:1px solid #000;"><u>'. $cpCfg['cp.ir8aForm.designation'] .'</u><br/><span>Designation</span></td>
                        <td width="12%" style="border-bottom:1px solid #000;"><u>'. $cpCfg['cp.ir8aForm.telephone'] .'</u><br/>Tel. No.</td>
                        <td width="16%" style="border-bottom:1px solid #000;"><u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u><br/>Signature</td>
                        <td width="9%" style="border-right:1px solid #000;border-bottom:1px solid #000;"><u>'. $cpCfg['cp.ir8aForm.date'] .'</u><br/>Date</td>
                    </tr>
                </table>
                <table border="0" width="100%" cellpadding="2" style="font-size:9px;">
                    <tr align="center">
                        <td width="100%"><b>There are penalties for failing to give a return or furnishing an incorrect or late return.</b></td>
                    </tr>
                    <tr align="center">
                        <td align="left" width="50%"><b>IR8A (1/2019)</b></td>
                        <td align="right" width="50%"><b>* Delete where applicable</b></td>
                    </tr>
                </table>
                ';

        $pdf->ln(-27);
        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-5);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(-5);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->ln(-5);
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $pdf->ln(-5);
        $pdf->writeHTML($tbl5, true, false, false, false, '');
        $download_title = 'Print.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getCalculateOvertimeAmountForIR8A($year, $employee_id){
        $db = Zend_Registry::get('db');

        $sqlOverTime = "
        SELECT ot_amount
        FROM payroll_management
        WHERE payroll_year = '{$year}'
          AND employee_id = '{$employee_id}'
          AND (status = 'Generated' OR status = 'Approved' OR status = 'Paid')
        ";
        $resultOverTime = $db->sql_query($sqlOverTime);
        $total_overtime_amount = 0;
        while ($rowOverTime = $db->sql_fetchrow($resultOverTime)) {
            $total_overtime_amount += $rowOverTime['ot_amount'];
        }

        return $total_overtime_amount;
    }

    /**
    *
    */
    function getGenerateAisTxtFile() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $text =  '';

        //$leftContent = 'WTF Hello this is not working';
        $text .= $this->getTextFileForAisHeader();
        $text .= $this->getTextFileForAisBody();
        $template = 'ais_import.txt';
        $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
        $templatefile = fopen("{$templatePath}","w");
        fwrite($templatefile, $text);
        //fclose($templatefile);
        header ("Content-Type: application/download");
        header ("Content-Disposition: attachment; filename=$templatePath");
        header("Content-Length: " . filesize("$templatePath"));
        $fp = fopen("$templatePath", "r");
        fpassthru($fp);
        //header("Location: $templatePath");
    }

    /**
    *
    */
    function getTextFileForAisHeader() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = '0'; //0 - default value
        $text .= '6'; // private sector
        $text .= '2018'; // income earned year to be reported
        $text .= '08'; // deafult value
        $text .= '8'; // Local Company Registration number issued by ACRA

        /*
        $company_registration_number = $cpCfg['cp.companyUENNo'];
        $arr1 = substr($company_registration_number, 0, 2);
        $arr2 = substr($company_registration_number, 2);

        if ($arr1 == '20') {
            $text .= 'T'; // if year starts with 20(05 or 18)
        } else {
            $text .= 'S'; // if year starts with 19(95 or 65)
        }
        */

        $text .= $cpCfg['cp.companyUENNo'];
        $spaces = 2;
        $text .= str_repeat(" ",$spaces);

        // authorised person making declaration
        $authorisedPersonNameLength = strlen($cpCfg['cp.ir8aForm.authorisedPerson']);
        if ($authorisedPersonNameLength > 30) {
            $authorisedPersonNameTruncate = substr($cpCfg['cp.ir8aForm.authorisedPerson'], 0, 30); 
            $text .= strtoupper($authorisedPersonNameTruncate);
        } else {
            $nameSpace = 30 - strlen($cpCfg['cp.ir8aForm.authorisedPerson']);
            $text .= strtoupper($cpCfg['cp.ir8aForm.authorisedPerson']);
            if($nameSpace){
                $text .=  str_repeat(" ",$nameSpace);
            }
        }

        // authorised person designation
        $authorisedPersonDesignationLength = strlen($cpCfg['cp.ir8aForm.designation']);
        if ($authorisedPersonDesignationLength > 30) {
            $authorisedPersonDesignationTruncate = substr($cpCfg['cp.ir8aForm.designation'], 0, 30); 
            $text .= strtoupper($authorisedPersonDesignationTruncate);
        } else {
            $designationSpace = 30 - strlen($cpCfg['cp.ir8aForm.designation']);
            $text .= strtoupper($cpCfg['cp.ir8aForm.designation']);
            if($designationSpace){
                $text .=  str_repeat(" ",$designationSpace);
            }
        }

        // organization name
        $orgNameLength = strlen($cpCfg['cp.companyName']);
        if ($orgNameLength > 60) {
            $orgNameTruncate = substr($cpCfg['cp.companyName'], 0, 60); 
            $text .= strtoupper($orgNameTruncate);
        } else {
            $orgNameSpace = 60 - strlen($cpCfg['cp.companyName']);
            $text .= strtoupper($cpCfg['cp.companyName']);
            if($orgNameSpace){
                $text .=  str_repeat(" ",$orgNameSpace);
            }
        }

        // telephone number
        $telNoLength = strlen($cpCfg['cp.ir8aForm.telephone']);
        if ($telNoLength > 20) {
            $telNoTruncate = substr($cpCfg['cp.ir8aForm.telephone'], 0, 20); 
            $text .= $telNoTruncate;
        } else {
            $telNoSpace = 20 - strlen($cpCfg['cp.ir8aForm.telephone']);
            $text .= $cpCfg['cp.ir8aForm.telephone'];
            if($telNoSpace){
                $text .=  str_repeat(" ",$telNoSpace);
            }
        }

        // email address of authorised person
        $emailLength = strlen($cpCfg['cp.ir8aForm.emailAddress']);
        if ($emailLength > 60) {
            $emailTruncate = substr($cpCfg['cp.ir8aForm.emailAddress'], 0, 60); 
            $text .= $emailTruncate;
        } else {
            $emailSpace = 60 - strlen($cpCfg['cp.ir8aForm.emailAddress']);
            $text .= $cpCfg['cp.ir8aForm.emailAddress'];
            if($emailSpace){
                $text .=  str_repeat(" ",$emailSpace);
            }
        }

        $text .= $cpCfg['cp.ir8aForm.aisFileType'];
        $text .= date('Ymd'); // Current date

        $spaces = 30;
        $text .= str_repeat(" ",$spaces);

        $text .= "IR8A";

        $spaces = 936;
        $text .= str_repeat(" ",$spaces) ."\n";

        return $text;
    }

    /**
    *
    */
    function getTextFileForAisBody1() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT DISTINCT pm.employee_id
              ,e.first_name
              ,e.citizen
              ,e.nric_no
              ,e.fin_no
              ,e.work_permit
              ,e.date_of_birth
              ,e.address_area
              ,e.address_street
              ,e.address_po_code
              ,e.address_country
              ,e.nationality
              ,e.gender
        FROM employee e
        LEFT JOIN (payroll_management pm) ON (e.employee_id = pm.employee_id)
        WHERE pm.payroll_year = '{$cpCfg['cp.ir8aFormForYear']}'
          AND (pm.status = 'Generated' OR pm.status = 'Approved' OR pm.status = 'Paid')
        ORDER BY e.first_name ASC
        ";
        $result   = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);

        $text = '';
        $summaryFor10 = '0';
        $summaryForSalary = '0';
        $summaryForDirectorFees = '0';
        $summaryForOthers = '0';
        $summaryForDonation = '0';
        $summaryForCPF = '0';
        while ($row = $db->sql_fetchrow($result)) {

            $rowPm = $this->getCalculateGrossSalaryAndOTPay($cpCfg['cp.ir8aFormForYear'], $row['employee_id']);
            $total_overtime_amount = $this->getCalculateOvertimeAmountForIR8A($cpCfg['cp.ir8aFormForYear'], $row['employee_id']);

            $gross_salary = $rowPm['total_amount'] + $total_overtime_amount;
            $director_fee = $rowPm['total_director_fee'];
            $cpfAmount = $rowPm['total_cpf_amount'];
            $transportAllowance = $rowPm['total_allowance1'];
            $entertainmentAllowance = $rowPm['total_allowance2'];
            $totalAllowances345 = $rowPm['total_allowance3'] + $rowPm['total_allowance4'] + $rowPm['total_allowance5'];
            $total_govt_contributions = $rowPm['total_cdac'] + $rowPm['total_sinda'] + $rowPm['total_mbmf'] + $rowPm['total_eucf'];

            $othersAmount = $transportAllowance + $entertainmentAllowance + $totalAllowances345; // for 19
            $amountFor10 = $gross_salary + $director_fee + $othersAmount; // 16+18+19

            // 1 Record type
            $text .=   "1"; // hard coded

            // 2 & 3 ID type of employee and ID No. of employee
            if ($row['citizen'] == 'PR' || $row['citizen'] == 'Citizen'){
                $text .=   "1"; // Locals
                $text .= $row['nric_no'];
            } else {
                $text .=   "2"; // foreigners
                $text .= $row['fin_no'];
            }

            $spacesForIC = 3;
            $text .= str_repeat(" ",$spacesForIC);

            // 4 Full Name of employee
            $nameLength = strlen($row['first_name']);
            if ($nameLength > 40) {
                $nameTruncate = substr($row['first_name'], 0, 40); 
                $text .= strtoupper($nameTruncate);
            } else {
                $nameSpace = 40 - strlen($row['first_name']);
                $text .= strtoupper($row['first_name']);
                if($nameSpace){
                    $text .=  str_repeat(" ",$nameSpace);
                }
            }

            // 4 Full Name of employee Line 2
            $spacesForName = 40;
            $text .= str_repeat(" ",$spacesForName);

            // 5 Address type
            $text .=   "C"; // Local address

            // 6a to 6e Formateed address
            $spacesForAddressFormatted = 56;
            $text .= str_repeat(" ",$spacesForAddressFormatted);

            // 6f Line 1 Address
            $add1Length = strlen($row['address_area']);
            if ($add1Length > 30) {
                $add1Truncate = substr($row['address_area'], 0, 30); 
                $text .= strtoupper($add1Truncate);
            } else {
                $add1Space = 30 - strlen($row['address_area']);
                $text .= strtoupper($row['address_area']);
                if($add1Space){
                    $text .=  str_repeat(" ",$add1Space);
                }
            }

            // 6g Line 2 Address
            $add2Length = strlen($row['address_street']);
            if ($add2Length > 30) {
                $add2Truncate = substr($row['address_street'], 0, 30); 
                $text .= strtoupper($add2Truncate);
            } else {
                $add2Space = 30 - strlen($row['address_street']);
                $text .= strtoupper($row['address_street']);
                if($add2Space){
                    $text .=  str_repeat(" ",$add2Space);
                }
            }

            // 6h Line 3 Address
            $spacesForAdd3 = 30;
            $text .= str_repeat(" ",$spacesForAdd3);

            // 6i Postal code for unformatted address
            if ($row['address_po_code']) {
                $text .= $row['address_po_code'];
            } else {
                $spacesForPostalCode = 6;
                $text .= str_repeat(" ",$spacesForPostalCode);
            }

            // 6j Country code of address
            /*
            if ($row['address_area']) {
                $text .= "301"; // Singapore Country code from IRAS guide
            } else {
                $spacesForCountryCode = 3;
                $text .= str_repeat(" ",$spacesForCountryCode);
            }
            */
            $spacesForCountryCode = 3;
            $text .= str_repeat(" ",$spacesForCountryCode);

            // 7 Nationality code from IRAS guide
            if ($row['nationality'] == 'Indian') {
                $text .= "354"; 
            } else if ($row['nationality'] == 'Malaysian') {
                $text .= "304";
            } else {
                $text .= "301"; // Singaporean
            }

            // 8 Sex
            if ($row['gender'] == 'Male'){
                $text .= "M"; // Male
            } else {
                $text .= "F"; // Female
            }

            // 9 Date of birth
            if ($row['date_of_birth']) {
                $dob = $fn->getCPDate($row['date_of_birth'], 'Ymd');
                $text .= $dob;
            } else {
                $spacesForDob = 8;
                $text .= str_repeat(" ",$spacesForDob);
            }

            // 10 Amount (Sum of Salary, Bonus, Director Fees and Others)
            $amountFor10Int = (int)$amountFor10;
            $amountFor10Space = 9 - strlen($amountFor10Int);
            $text .= $amountFor10Int;
            if($amountFor10Space){
                $text .= str_repeat(" ",$amountFor10Space);
            }
            // Used in Footer
            $summaryFor10 += $amountFor10Int;

            // 11a From date - Date of Commencement check
            $commencement_year_val = $cpCfg['cp.ir8aForm.fromDate'];
            /*
            $date_of_commencement = '';
            $sqlCommencement = "
            SELECT act_join_date FROM job_information
            WHERE employee_id = '{$row['employee_id']}'
            ORDER BY job_information_id ASC LIMIT 0,1
            ";
            $resultCommencement = $db->sql_query($sqlCommencement);
            $rowCommencement = $db->sql_fetchrow($resultCommencement);
            if ($rowCommencement['act_join_date']) {
                $commencement_year = $fn->getCPDate($rowCommencement['act_join_date'], 'Y');
                if ($commencement_year == $cpCfg['cp.ir8aFormForYear']) {
                    $commencement_year_val = $fn->getCPDate($rowCommencement['act_join_date'], 'Ymd');
                    $date_of_commencement = $fn->getCPDate($rowCommencement['act_join_date'], 'Ymd');
                }
            }
            */
            $text .= $commencement_year_val;

            // 11b Date of Cessation check
            $termination_year_val = $cpCfg['cp.ir8aForm.toDate'];
            /*
            $date_of_cessation = '';
            $cessationIndicator = '';
            $sqlTermination = "
            SELECT ji.termination_date FROM job_information ji
            LEFT JOIN (employee e) ON (ji.employee_id = e.employee_id)
            WHERE ji.employee_id = '{$row['employee_id']}'
              AND ji.status = 'Archive'
              AND e.status = 'Archive'
            ORDER BY ji.job_information_id DESC LIMIT 0,1
            ";
            $resultTermination = $db->sql_query($sqlTermination);
            $rowTermination = $db->sql_fetchrow($resultTermination);
            if ($rowTermination['termination_date']) {
                $termination_year = $fn->getCPDate($rowTermination['termination_date'], 'Y');
                if ($termination_year == $cpCfg['cp.ir8aFormForYear']) {
                    $termination_year_val = $fn->getCPDate($rowTermination['termination_date'], 'Ymd');
                    $date_of_cessation = $fn->getCPDate($rowTermination['termination_date'], 'Ymd');
                    $cessationIndicator = 'Yes';
                }
            }
            */
            $text .= $termination_year_val;

            // 12 Mosque Building Fund
            $text .= '0';
            $spacesForMBF = 4;
            $text .= str_repeat(" ",$spacesForMBF);

            // 13 Donation
            if ($total_govt_contributions){
                $govtContributionInt = (int)$total_govt_contributions;
                $govtContributionSpace = 5 - strlen($govtContributionInt);
                $text .= $govtContributionInt;
                if($govtContributionSpace){
                    $text .= str_repeat(" ",$govtContributionSpace);
                }                

                // Used in Footer
                $summaryForDonation += $govtContributionInt;
            } else {
                $text .= '0';
                $spacesForDonation = 4;
                $text .= str_repeat(" ",$spacesForDonation);
            }

            // 14 CPF / Designated pention or Provident fund (Total CPF Amount)
            if ($cpfAmount) {
                $cpfAmountInt = (int)$cpfAmount;
                $cpfAmountSpace = 7 - strlen($cpfAmountInt);
                $text .= $cpfAmountInt;
                if($cpfAmountSpace){
                    $text .= str_repeat(" ",$cpfAmountSpace);
                }

                // Used in Footer
                $summaryForCPF += $cpfAmountInt;
            } else {
                $text .= '0';
                $spacesForCpfAmount = 6;
                $text .= str_repeat(" ",$spacesForCpfAmount);
            }

            // 15 Insurance
            $text .= '0';
            $spacesForInsurance = 4;
            $text .= str_repeat(" ",$spacesForInsurance);

            // 16 Salary
            $grossSalaryInt = (int)$gross_salary;
            $grossSalarySpace = 9 - strlen($grossSalaryInt);
            $text .= $grossSalaryInt;
            if($grossSalarySpace){
                $text .= str_repeat(" ",$grossSalarySpace);
            }
            // Used in Footer
            $summaryForSalary += $grossSalaryInt;

            // 17 Bonus
            $text .= '0';
            $spacesForBonus = 8;
            $text .= str_repeat(" ",$spacesForBonus);

            // 18 Director Fees
            if ($director_fee) {
                $directorFeeInt = (int)$director_fee;
                $directorFeeSpace = 9 - strlen($directorFeeInt);
                $text .= $directorFeeInt;
                if($directorFeeSpace){
                    $text .= str_repeat(" ",$directorFeeSpace);
                }

                // Used in Footer
                $summaryForDirectorFees += $directorFeeInt;
            } else {
                $text .= '0';
                $directorFeeSpace = 8;
                $text .= str_repeat(" ",$directorFeeSpace);
            }

            // 19 Others
            if ($othersAmount) {
                $othersAmountFormatted = (int)$othersAmount;
                $othersSpace = 9 - strlen($othersAmountFormatted);
                $text .= $othersAmountFormatted;
                if($othersSpace){
                    $text .= str_repeat(" ",$othersSpace);
                }

                // Used in Footer
                $summaryForOthers += $othersAmountFormatted;
            } else {
                $text .= '0';
                $spacesForOthers = 8;
                $text .= str_repeat(" ",$spacesForOthers);
            }

            // 19a Gains and Profit
            $text .= '0';
            $spacesForGains = 8;
            $text .= str_repeat(" ",$spacesForGains);

            // 20 Excempt Income
            $text .= '0';
            $spacesForExcemptIncome = 8;
            $text .= str_repeat(" ",$spacesForExcemptIncome);

            // 21 Tax borne by Employer
            $text .= '0';
            $spacesForEmployerTax = 8;
            $text .= str_repeat(" ",$spacesForEmployerTax);

            // 22 Tax borne by Employee
            $text .= '0';
            $spacesForEmployeeTax = 8;
            $text .= str_repeat(" ",$spacesForEmployeeTax);

            // 23 Appendix 8A indicator
            // 24 Section 45 indicator
            // 25 Employee Income Tax borne by employer indicator
            // 26 Gratuity / Notice Pay / Ex-Gratia payment / Others indicator
            $spaceForCombinedPage9 = 4;
            $text .= str_repeat(" ",$spaceForCombinedPage9);

            // 27 Compensation for loss of office indicator
            // 27a Approval obtained from IRAS indicator
            // 27b Date of Approval
            $spaceForCombinedPage10a = 10;
            $text .= str_repeat(" ",$spaceForCombinedPage10a);

            // 28 Cessation Provisions indicator
            /*if ($cessationIndicator == 'Yes') {
                $text .= 'Y';
            } else {
                $spaceForCessation = 1;
                $text .= str_repeat(" ",$spaceForCessation);
            }
            */
            $spaceForCessation = 1;
            $text .= str_repeat(" ",$spaceForCessation);

            // 29 Form IR8S Indicator
            // 30 Remission / Overseas Posting / Exempt Indicator
            $spaceForCombinedPage10b = 2;
            $text .= str_repeat(" ",$spaceForCombinedPage10b);

            // 30a Compensation & Gratuity
            $spaceForCombinedPage11a = 1;
            $text .= str_repeat(" ",$spaceForCombinedPage11a);

            // 31 Gross Commission
            $text .= '0';
            $spacesForGrossCommission = 10;
            $text .= str_repeat(" ",$spacesForGrossCommission);

            // 32a From Date
            // 32b To Date
            // 33 Gross Commission indicator
            $spacesFor32ab = 17;
            $text .= str_repeat(" ",$spacesFor32ab);

            // 34 Pension
            $text .= '0';
            $spacesForPension = 10;
            $text .= str_repeat(" ",$spacesForPension);

            // 35 Transport Allowance
            if ($transportAllowance > 0) {
                $transportAllowanceInt = (int)$transportAllowance;
                $transportAllowanceFormatting = $transportAllowanceInt . '00';

                $transportAllowanceSpace = 11 - strlen($transportAllowanceFormatting);
                $text .= $transportAllowanceFormatting;
                if($transportAllowanceSpace){
                    $text .= str_repeat(" ",$transportAllowanceSpace);
                }
            } else {
                $text .= '0';
                $transportAllowanceSpace = 10;
                $text .= str_repeat(" ",$transportAllowanceSpace);
            }

            // 36 Entertainment Allowance
            if ($entertainmentAllowance > 0) {
                $entertainmentAllowanceInt = (int)$entertainmentAllowance;
                $entertainmentAllowanceFormatting = $entertainmentAllowanceInt . '00';

                $entertainmentAllowanceSpace = 11 - strlen($entertainmentAllowanceFormatting);
                $text .= $entertainmentAllowanceFormatting;
                if($entertainmentAllowanceSpace){
                    $text .= str_repeat(" ",$entertainmentAllowanceSpace);
                }
            } else {
                $text .= '0';
                $entertainmentAllowanceSpace = 10;
                $text .= str_repeat(" ",$entertainmentAllowanceSpace);
            }

            // 37 Other Allowance
            if ($totalAllowances345 > 0) {
                $totalAllowances345Int = (int)$totalAllowances345;
                $totalAllowances345Formatting = $totalAllowances345Int . '00';

                $totalAllowances345Space = 11 - strlen($totalAllowances345Formatting);
                $text .= $totalAllowances345Formatting;
                if($totalAllowances345Space){
                    $text .= str_repeat(" ",$totalAllowances345Space);
                }
            } else {
                $text .= '0';
                $totalAllowances345Space = 10;
                $text .= str_repeat(" ",$totalAllowances345Space);
            }

            // 38 Gratuity / Notice Pay / Ex-gratia payment / Others
            $text .= '0';
            $spacesForGratuity = 10;
            $text .= str_repeat(" ",$spacesForGratuity);

            // 38a Compensation for loss of office
            $text .= '0';
            $spacesForLossofOffice = 10;
            $text .= str_repeat(" ",$spacesForLossofOffice);

            // 39 Retirement benefits accured up to 31.12.92
            $text .= '0';
            $spacesForRetirementBenefits1 = 10;
            $text .= str_repeat(" ",$spacesForRetirementBenefits1);

            // 40 Retirement benefits accured from 1993
            $text .= '0';
            $spacesForRetirementBenefits2 = 10;
            $text .= str_repeat(" ",$spacesForRetirementBenefits2);

            // 41 Contribution by employer for Pension / Provident Fund
            $text .= '0';
            $spacesForPensionPp = 10;
            $text .= str_repeat(" ",$spacesForPensionPp);

            // 42 Excess / Voluntary contribution to CPF by employer
            $text .= '0';
            $spacesForVoluntaryCPF = 10;
            $text .= str_repeat(" ",$spacesForVoluntaryCPF);

            // 43 Gains and profits from share
            $text .= '0';
            $spacesForVoluntaryCPF = 10;
            $text .= str_repeat(" ",$spacesForVoluntaryCPF);

            // 44 Value of benefits-in-kinds
            $text .= '0';
            $spacesForBenefits = 10;
            $text .= str_repeat(" ",$spacesForBenefits);

            // 45 Employees voluntary contribution
            $text .= '0';
            $spacesForEmployeesVoluntaryContribution = 6;
            $text .= str_repeat(" ",$spacesForEmployeesVoluntaryContribution);

            // 46 Designation
            $sqlDesignation = "
            SELECT designation FROM job_information
            WHERE employee_id = '{$row['employee_id']}'
              AND status = 'Current'
            ORDER BY job_information_id DESC LIMIT 0,1
            ";
            $resultDesignation = $db->sql_query($sqlDesignation);
            $rowDesignation = $db->sql_fetchrow($resultDesignation);

            if ($rowDesignation['designation']) {
                $designationLength = strlen($rowDesignation['designation']);
                if ($designationLength > 30) {
                    $designationTruncate = substr($rowDesignation['designation'], 0, 30); 
                    $text .= strtoupper($designationTruncate);
                } else {
                    $designationSpace = 30 - $designationLength;
                    $text .= strtoupper($rowDesignation['designation']);
                    if($designationSpace){
                        $text .=  str_repeat(" ",$designationSpace);
                    }
                }
            } else {
                $spacesForDesignation = 30;
                $text .= str_repeat(" ",$spacesForDesignation);
            }

            // 47 Date of Commencement
            /*
            if ($date_of_commencement) {
                $text .= $date_of_commencement;
            } else {
                $spacesForCommencementDate = 8;
                $text .= str_repeat(" ",$spacesForCommencementDate);
            }
            */
            $spacesForCommencementDate = 8;
            $text .= str_repeat(" ",$spacesForCommencementDate);

            // 48 Date of Cessation
            /*
            if ($date_of_cessation) {
                $text .= $date_of_cessation;
            } else {
                $spacesForCessationDate = 8;
                $text .= str_repeat(" ",$spacesForCessationDate);
            }
            */
            $spacesForCessationDate = 8;
            $text .= str_repeat(" ",$spacesForCessationDate);

            // 49 Date of declaration of bonus
            $spacesForBonusDeclaration = 8;
            $text .= str_repeat(" ",$spacesForBonusDeclaration);

            // 50 Date of approval of director's fees
            if ($rowPm['total_director_fee'] > 0) {
                $director_fee_date = $fn->getCPDate($cpCfg['cp.ir8aForm.DirectorFeeAgmDateInAis'], 'Ymd');
                $text .= $director_fee_date;
            } else {
                $spacesForDirectorFeeDate = 8;
                $text .= str_repeat(" ",$spacesForDirectorFeeDate);
            }

            // 51 Name of find for retirement benefits
            $spacesForRetirementBenefits = 60;
            $text .= str_repeat(" ",$spacesForRetirementBenefits);

            // 52 Name of designated person
            $spacesForDesignatedPerson = 60;
            $text .= str_repeat(" ",$spacesForDesignatedPerson);

            // 53 Name of bank
            $spacesForBankName = 1;
            $text .= str_repeat(" ",$spacesForBankName);

            // 54 Date of Payroll
            $spacesForPayrollDate = 8;
            $text .= str_repeat(" ",$spacesForPayrollDate);

            // 55 Filler
            $spacesForFiller = 393;
            $text .= str_repeat(" ",$spacesForFiller);

            // 56 Field reserved
            $spacesForReservedField = 50;
            $text .= str_repeat(" ",$spacesForReservedField);

            $text .=   "\n";
        }

        // 1 footer code comes here.
        $text .= "2"; // Hardcoded

        // 2 No. of Records
        $numrowsLength = strlen($numRows);
        $numrowsSpace = 6 - $numrowsLength;
        $text .= $numRows;
        if($numrowsSpace){
            $text .=  str_repeat(" ",$numrowsSpace);
        }

        // 3 Total amount of Payment (Item 10)
        $summaryFor10Space = 12 - strlen($summaryFor10);
        $text .= $summaryFor10;
        if($summaryFor10Space){
            $text .= str_repeat(" ",$summaryFor10Space);
        }

        // 4 Total amount of Salary (Item 16)
        $summaryForSalarySpace = 12 - strlen($summaryForSalary);
        $text .= $summaryForSalary;
        if($summaryForSalarySpace){
            $text .= str_repeat(" ",$summaryForSalarySpace);
        }

        // 5 Total amount of Bonus (Item 17)
        $text .= '0';
        $spacesForBonusSummary = 11;
        $text .= str_repeat(" ",$spacesForBonusSummary);

        // 6 Total amount of Director Fees (Item 18)
        $summaryForDirectorFeesSpace = 12 - strlen($summaryForDirectorFees);
        $text .= $summaryForDirectorFees;
        if($summaryForDirectorFeesSpace){
            $text .= str_repeat(" ",$summaryForDirectorFeesSpace);
        }

        // 7 Total amount of Others (Item 19)
        $summaryForOthersSpace = 12 - strlen($summaryForOthers);
        $text .= $summaryForOthers;
        if($summaryForOthersSpace){
            $text .= str_repeat(" ",$summaryForOthersSpace);
        }

        // 8 Total amount of exempt income (Item 20)
        $text .= '0';
        $spacesForExemptIncome = 11;
        $text .= str_repeat(" ",$spacesForExemptIncome);

        // 9 Total amount of tax borne by employer (Item 21)
        $text .= '0';
        $spacesForTaxEmployer = 11;
        $text .= str_repeat(" ",$spacesForTaxEmployer);

        // 10 Total amount of tax borne by employee (Item 22)
        $text .= '0';
        $spacesForTaxEmployee = 11;
        $text .= str_repeat(" ",$spacesForTaxEmployee);

        // 11 Total Donation (Item 13)
        $summaryForDonationSpace = 12 - strlen($summaryForDonation);
        $text .= $summaryForDonation;
        if($summaryForDonationSpace){
            $text .= str_repeat(" ",$summaryForDonationSpace);
        }

        // 12 Total CPF (Item 14)
        $summaryForCPFSpace = 12 - strlen($summaryForCPF);
        $text .= $summaryForCPF;
        if($summaryForCPFSpace){
            $text .= str_repeat(" ",$summaryForCPFSpace);
        }

        // 13 Total amount of insurance (Item 15)
        $text .= '0';
        $spacesForInsurance = 11;
        $text .= str_repeat(" ",$spacesForInsurance);

        // 14 Total amount of MBF (Item 12)
        $text .= '0';
        $spacesForMBF = 11;
        $text .= str_repeat(" ",$spacesForMBF);

        // 15 Filler
        $spacesForFillerSummary = 1049;
        $text .= str_repeat(" ",$spacesForFillerSummary);

        return $text;
    }

    /**
    *
    */
    function getTextFileForAisBody() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT DISTINCT pm.employee_id
              ,e.first_name
              ,e.citizen
              ,e.nric_no
              ,e.fin_no
              ,e.work_permit
              ,e.date_of_birth
              ,e.address_area
              ,e.address_street
              ,e.address_po_code
              ,e.address_country
              ,e.nationality
              ,e.gender
        FROM employee e
        LEFT JOIN (payroll_management pm) ON (e.employee_id = pm.employee_id)
        WHERE pm.payroll_year = '{$cpCfg['cp.ir8aFormForYear']}'
          AND (pm.status = 'Generated' OR pm.status = 'Approved' OR pm.status = 'Paid')
        ORDER BY e.first_name ASC
        ";
        $result   = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);

        $text = '';
        $summaryFor10 = '0';
        $summaryForSalary = '0';
        $summaryForDirectorFees = '0';
        $summaryForOthers = '0';
        $summaryForDonation = '0';
        $summaryForCPF = '0';
        while ($row = $db->sql_fetchrow($result)) {

            $rowPm = $this->getCalculateGrossSalaryAndOTPay($cpCfg['cp.ir8aFormForYear'], $row['employee_id']);
            $total_overtime_amount = $this->getCalculateOvertimeAmountForIR8A($cpCfg['cp.ir8aFormForYear'], $row['employee_id']);

            $gross_salary = $rowPm['total_amount'] + $total_overtime_amount;
            $director_fee = $rowPm['total_director_fee'];
            $cpfAmount = $rowPm['total_cpf_amount'];
            $transportAllowance = $rowPm['total_allowance1'];
            $entertainmentAllowance = $rowPm['total_allowance2'];
            $totalAllowances345 = $rowPm['total_allowance3'] + $rowPm['total_allowance4'] + $rowPm['total_allowance5'];
            $total_govt_contributions = $rowPm['total_cdac'] + $rowPm['total_sinda'] + $rowPm['total_mbmf'] + $rowPm['total_eucf'];

            $othersAmount = $transportAllowance + $entertainmentAllowance + $totalAllowances345; // for 19
            $amountFor10 = $gross_salary + $director_fee + $othersAmount; // 16+18+19

            // 1 Record type
            $text .=   "1"; // hard coded

            // 2 & 3 ID type of employee and ID No. of employee
            if ($row['citizen'] == 'PR' || $row['citizen'] == 'Citizen'){
                $text .=   "1"; // Locals
                $text .= $row['nric_no'];
            } else {
                $text .=   "2"; // foreigners
                $text .= $row['fin_no'];
            }

            $spacesForIC = 3;
            $text .= str_repeat(" ",$spacesForIC);

            // 4 Full Name of employee
            $nameLength = strlen($row['first_name']);
            if ($nameLength > 40) {
                $nameTruncate = substr($row['first_name'], 0, 40); 
                $text .= strtoupper($nameTruncate);
            } else {
                $nameSpace = 40 - strlen($row['first_name']);
                $text .= strtoupper($row['first_name']);
                if($nameSpace){
                    $text .=  str_repeat(" ",$nameSpace);
                }
            }

            // 4 Full Name of employee Line 2
            $spacesForName = 40;
            $text .= str_repeat(" ",$spacesForName);

            // 5 Address type
            $text .=   "C"; // Local address

            // 6a to 6e Formateed address
            $spacesForAddressFormatted = 56;
            $text .= str_repeat(" ",$spacesForAddressFormatted);

            // 6f Line 1 Address
            $add1Length = strlen($row['address_area']);
            if ($add1Length > 30) {
                $add1Truncate = substr($row['address_area'], 0, 30); 
                $text .= strtoupper($add1Truncate);
            } else {
                $add1Space = 30 - strlen($row['address_area']);
                $text .= strtoupper($row['address_area']);
                if($add1Space){
                    $text .=  str_repeat(" ",$add1Space);
                }
            }

            // 6g Line 2 Address
            $add2Length = strlen($row['address_street']);
            if ($add2Length > 30) {
                $add2Truncate = substr($row['address_street'], 0, 30); 
                $text .= strtoupper($add2Truncate);
            } else {
                $add2Space = 30 - strlen($row['address_street']);
                $text .= strtoupper($row['address_street']);
                if($add2Space){
                    $text .=  str_repeat(" ",$add2Space);
                }
            }

            // 6h Line 3 Address
            $spacesForAdd3 = 30;
            $text .= str_repeat(" ",$spacesForAdd3);

            // 6i Postal code for unformatted address
            if ($row['address_po_code']) {
                $text .= $row['address_po_code'];
            } else {
                $spacesForPostalCode = 6;
                $text .= str_repeat(" ",$spacesForPostalCode);
            }

            // 6j Country code of address
            /*
            if ($row['address_area']) {
                $text .= "301"; // Singapore Country code from IRAS guide
            } else {
                $spacesForCountryCode = 3;
                $text .= str_repeat(" ",$spacesForCountryCode);
            }
            */
            $spacesForCountryCode = 3;
            $text .= str_repeat(" ",$spacesForCountryCode);

            $sqlNationality = "SELECT nationality_code FROM nationality WHERE title = '{$row['nationality']}'";
            $resultNationality = $db->sql_query($sqlNationality);
            while ($rowNationality = $db->sql_fetchrow($resultNationality)) {
                $text .= $rowNationality['nationality_code']; 
            }

            // 7 Nationality code from IRAS guide
            /*
            if ($row['nationality'] == 'Indian') {
                $text .= "354"; 
            } else if ($row['nationality'] == 'Malaysian') {
                $text .= "304";
            } else if ($row['nationality'] == 'Chinese') {
                $text .= "336";
            } else if ($row['nationality'] == 'Sri Lankan') {
                $text .= "358";
            } else {
                $text .= "301"; // Singaporean
            }
            */

            // 8 Sex
            if ($row['gender'] == 'Male'){
                $text .= "M"; // Male
            } else {
                $text .= "F"; // Female
            }

            // 9 Date of birth
            if ($row['date_of_birth']) {
                $dob = $fn->getCPDate($row['date_of_birth'], 'Ymd');
                $text .= $dob;
            } else {
                $spacesForDob = 8;
                $text .= str_repeat(" ",$spacesForDob);
            }

            // 10 Amount (Sum of Salary, Bonus, Director Fees and Others)
            $amountFor10Int = (int)$amountFor10;
            $amountFor10Space = 9 - strlen($amountFor10Int);
            $text .= $amountFor10Int;
            if($amountFor10Space){
                $text .= str_repeat(" ",$amountFor10Space);
            }
            // Used in Footer
            $summaryFor10 += $amountFor10Int;

            // 11a From date - Date of Commencement check
            $commencement_year_val = $cpCfg['cp.ir8aForm.fromDate'];
            /*
            $date_of_commencement = '';
            $sqlCommencement = "
            SELECT act_join_date FROM job_information
            WHERE employee_id = '{$row['employee_id']}'
            ORDER BY job_information_id ASC LIMIT 0,1
            ";
            $resultCommencement = $db->sql_query($sqlCommencement);
            $rowCommencement = $db->sql_fetchrow($resultCommencement);
            if ($rowCommencement['act_join_date']) {
                $commencement_year = $fn->getCPDate($rowCommencement['act_join_date'], 'Y');
                if ($commencement_year == $cpCfg['cp.ir8aFormForYear']) {
                    $commencement_year_val = $fn->getCPDate($rowCommencement['act_join_date'], 'Ymd');
                    $date_of_commencement = $fn->getCPDate($rowCommencement['act_join_date'], 'Ymd');
                }
            }
            */
            $text .= $commencement_year_val;

            // 11b Date of Cessation check
            $termination_year_val = $cpCfg['cp.ir8aForm.toDate'];
            /*
            $date_of_cessation = '';
            $cessationIndicator = '';
            $sqlTermination = "
            SELECT ji.termination_date FROM job_information ji
            LEFT JOIN (employee e) ON (ji.employee_id = e.employee_id)
            WHERE ji.employee_id = '{$row['employee_id']}'
              AND ji.status = 'Archive'
              AND e.status = 'Archive'
            ORDER BY ji.job_information_id DESC LIMIT 0,1
            ";
            $resultTermination = $db->sql_query($sqlTermination);
            $rowTermination = $db->sql_fetchrow($resultTermination);
            if ($rowTermination['termination_date']) {
                $termination_year = $fn->getCPDate($rowTermination['termination_date'], 'Y');
                if ($termination_year == $cpCfg['cp.ir8aFormForYear']) {
                    $termination_year_val = $fn->getCPDate($rowTermination['termination_date'], 'Ymd');
                    $date_of_cessation = $fn->getCPDate($rowTermination['termination_date'], 'Ymd');
                    $cessationIndicator = 'Yes';
                }
            }
            */
            $text .= $termination_year_val;

            // 12 Mosque Building Fund
            $text .= '0';
            $spacesForMBF = 4;
            $text .= str_repeat(" ",$spacesForMBF);

            // 13 Donation
            if ($total_govt_contributions){
                $govtContributionInt = (int)$total_govt_contributions;
                $govtContributionSpace = 5 - strlen($govtContributionInt);
                $text .= $govtContributionInt;
                if($govtContributionSpace){
                    $text .= str_repeat(" ",$govtContributionSpace);
                }                

                // Used in Footer
                $summaryForDonation += $govtContributionInt;
            } else {
                $text .= '0';
                $spacesForDonation = 4;
                $text .= str_repeat(" ",$spacesForDonation);
            }

            // 14 CPF / Designated pention or Provident fund (Total CPF Amount)
            if ($cpfAmount) {
                $cpfAmountInt = (int)$cpfAmount;
                $cpfAmountSpace = 7 - strlen($cpfAmountInt);
                $text .= $cpfAmountInt;
                if($cpfAmountSpace){
                    $text .= str_repeat(" ",$cpfAmountSpace);
                }

                // Used in Footer
                $summaryForCPF += $cpfAmountInt;
            } else {
                $text .= '0';
                $spacesForCpfAmount = 6;
                $text .= str_repeat(" ",$spacesForCpfAmount);
            }

            // 15 Insurance
            $text .= '0';
            $spacesForInsurance = 4;
            $text .= str_repeat(" ",$spacesForInsurance);

            // 16 Salary
            $grossSalaryInt = (int)$gross_salary;
            $grossSalarySpace = 9 - strlen($grossSalaryInt);
            $text .= $grossSalaryInt;
            if($grossSalarySpace){
                $text .= str_repeat(" ",$grossSalarySpace);
            }
            // Used in Footer
            $summaryForSalary += $grossSalaryInt;

            // 17 Bonus
            $text .= '0';
            $spacesForBonus = 8;
            $text .= str_repeat(" ",$spacesForBonus);

            // 18 Director Fees
            if ($director_fee) {
                $directorFeeInt = (int)$director_fee;
                $directorFeeSpace = 9 - strlen($directorFeeInt);
                $text .= $directorFeeInt;
                if($directorFeeSpace){
                    $text .= str_repeat(" ",$directorFeeSpace);
                }

                // Used in Footer
                $summaryForDirectorFees += $directorFeeInt;
            } else {
                $text .= '0';
                $directorFeeSpace = 8;
                $text .= str_repeat(" ",$directorFeeSpace);
            }

            // 19 Others
            if ($othersAmount) {
                $othersAmountFormatted = (int)$othersAmount;
                $othersSpace = 9 - strlen($othersAmountFormatted);
                $text .= $othersAmountFormatted;
                if($othersSpace){
                    $text .= str_repeat(" ",$othersSpace);
                }

                // Used in Footer
                $summaryForOthers += $othersAmountFormatted;
            } else {
                $text .= '0';
                $spacesForOthers = 8;
                $text .= str_repeat(" ",$spacesForOthers);
            }

            // 19a Gains and Profit
            $text .= '0';
            $spacesForGains = 8;
            $text .= str_repeat(" ",$spacesForGains);

            // 20 Excempt Income
            $text .= '0';
            $spacesForExcemptIncome = 8;
            $text .= str_repeat(" ",$spacesForExcemptIncome);

            // 21 Tax borne by Employer
            $text .= '0';
            $spacesForEmployerTax = 8;
            $text .= str_repeat(" ",$spacesForEmployerTax);

            // 22 Tax borne by Employee
            $text .= '0';
            $spacesForEmployeeTax = 8;
            $text .= str_repeat(" ",$spacesForEmployeeTax);

            // 23 Appendix 8A indicator
            // 24 Section 45 indicator
            // 25 Employee Income Tax borne by employer indicator
            // 26 Gratuity / Notice Pay / Ex-Gratia payment / Others indicator
            $spaceForCombinedPage9 = 4;
            $text .= str_repeat(" ",$spaceForCombinedPage9);

            // 27 Compensation for loss of office indicator
            // 27a Approval obtained from IRAS indicator
            // 27b Date of Approval
            $spaceForCombinedPage10a = 10;
            $text .= str_repeat(" ",$spaceForCombinedPage10a);

            // 28 Cessation Provisions indicator
            /*if ($cessationIndicator == 'Yes') {
                $text .= 'Y';
            } else {
                $spaceForCessation = 1;
                $text .= str_repeat(" ",$spaceForCessation);
            }
            */
            $spaceForCessation = 1;
            $text .= str_repeat(" ",$spaceForCessation);

            // 29 Form IR8S Indicator
            // 30 Remission / Overseas Posting / Exempt Indicator
            $spaceForCombinedPage10b = 2;
            $text .= str_repeat(" ",$spaceForCombinedPage10b);

            // 30a Compensation & Gratuity
            $spaceForCombinedPage11a = 1;
            $text .= str_repeat(" ",$spaceForCombinedPage11a);

            // 31 Gross Commission
            $text .= '0';
            $spacesForGrossCommission = 10;
            $text .= str_repeat(" ",$spacesForGrossCommission);

            // 32a From Date
            // 32b To Date
            // 33 Gross Commission indicator
            $spacesFor32ab = 17;
            $text .= str_repeat(" ",$spacesFor32ab);

            // 34 Pension
            $text .= '0';
            $spacesForPension = 10;
            $text .= str_repeat(" ",$spacesForPension);

            // 35 Transport Allowance
            if ($transportAllowance > 0) {
                $transportAllowanceInt = (int)$transportAllowance;
                $transportAllowanceFormatting = $transportAllowanceInt . '00';

                $transportAllowanceSpace = 11 - strlen($transportAllowanceFormatting);
                $text .= $transportAllowanceFormatting;
                if($transportAllowanceSpace){
                    $text .= str_repeat(" ",$transportAllowanceSpace);
                }
            } else {
                $text .= '0';
                $transportAllowanceSpace = 10;
                $text .= str_repeat(" ",$transportAllowanceSpace);
            }

            // 36 Entertainment Allowance
            if ($entertainmentAllowance > 0) {
                $entertainmentAllowanceInt = (int)$entertainmentAllowance;
                $entertainmentAllowanceFormatting = $entertainmentAllowanceInt . '00';

                $entertainmentAllowanceSpace = 11 - strlen($entertainmentAllowanceFormatting);
                $text .= $entertainmentAllowanceFormatting;
                if($entertainmentAllowanceSpace){
                    $text .= str_repeat(" ",$entertainmentAllowanceSpace);
                }
            } else {
                $text .= '0';
                $entertainmentAllowanceSpace = 10;
                $text .= str_repeat(" ",$entertainmentAllowanceSpace);
            }

            // 37 Other Allowance
            if ($totalAllowances345 > 0) {
                $totalAllowances345Int = (int)$totalAllowances345;
                $totalAllowances345Formatting = $totalAllowances345Int . '00';

                $totalAllowances345Space = 11 - strlen($totalAllowances345Formatting);
                $text .= $totalAllowances345Formatting;
                if($totalAllowances345Space){
                    $text .= str_repeat(" ",$totalAllowances345Space);
                }
            } else {
                $text .= '0';
                $totalAllowances345Space = 10;
                $text .= str_repeat(" ",$totalAllowances345Space);
            }

            // 38 Gratuity / Notice Pay / Ex-gratia payment / Others
            $text .= '0';
            $spacesForGratuity = 10;
            $text .= str_repeat(" ",$spacesForGratuity);

            // 38a Compensation for loss of office
            $text .= '0';
            $spacesForLossofOffice = 10;
            $text .= str_repeat(" ",$spacesForLossofOffice);

            // 39 Retirement benefits accured up to 31.12.92
            $text .= '0';
            $spacesForRetirementBenefits1 = 10;
            $text .= str_repeat(" ",$spacesForRetirementBenefits1);

            // 40 Retirement benefits accured from 1993
            $text .= '0';
            $spacesForRetirementBenefits2 = 10;
            $text .= str_repeat(" ",$spacesForRetirementBenefits2);

            // 41 Contribution by employer for Pension / Provident Fund
            $text .= '0';
            $spacesForPensionPp = 10;
            $text .= str_repeat(" ",$spacesForPensionPp);

            // 42 Excess / Voluntary contribution to CPF by employer
            $text .= '0';
            $spacesForVoluntaryCPF = 10;
            $text .= str_repeat(" ",$spacesForVoluntaryCPF);

            // 43 Gains and profits from share
            $text .= '0';
            $spacesForVoluntaryCPF = 10;
            $text .= str_repeat(" ",$spacesForVoluntaryCPF);

            // 44 Value of benefits-in-kinds
            $text .= '0';
            $spacesForBenefits = 10;
            $text .= str_repeat(" ",$spacesForBenefits);

            // 45 Employees voluntary contribution
            $text .= '0';
            $spacesForEmployeesVoluntaryContribution = 6;
            $text .= str_repeat(" ",$spacesForEmployeesVoluntaryContribution);

            // 46 Designation
            $sqlDesignation = "
            SELECT designation FROM job_information
            WHERE employee_id = '{$row['employee_id']}'
              AND status = 'Current'
            ORDER BY job_information_id DESC LIMIT 0,1
            ";
            $resultDesignation = $db->sql_query($sqlDesignation);
            $rowDesignation = $db->sql_fetchrow($resultDesignation);

            if ($rowDesignation['designation']) {
                $designationLength = strlen($rowDesignation['designation']);
                if ($designationLength > 30) {
                    $designationTruncate = substr($rowDesignation['designation'], 0, 30); 
                    $text .= strtoupper($designationTruncate);
                } else {
                    $designationSpace = 30 - $designationLength;
                    $text .= strtoupper($rowDesignation['designation']);
                    if($designationSpace){
                        $text .=  str_repeat(" ",$designationSpace);
                    }
                }
            } else {
                $spacesForDesignation = 30;
                $text .= str_repeat(" ",$spacesForDesignation);
            }

            // 47 Date of Commencement
            /*
            if ($date_of_commencement) {
                $text .= $date_of_commencement;
            } else {
                $spacesForCommencementDate = 8;
                $text .= str_repeat(" ",$spacesForCommencementDate);
            }
            */
            $spacesForCommencementDate = 8;
            $text .= str_repeat(" ",$spacesForCommencementDate);

            // 48 Date of Cessation
            /*
            if ($date_of_cessation) {
                $text .= $date_of_cessation;
            } else {
                $spacesForCessationDate = 8;
                $text .= str_repeat(" ",$spacesForCessationDate);
            }
            */
            $spacesForCessationDate = 8;
            $text .= str_repeat(" ",$spacesForCessationDate);

            // 49 Date of declaration of bonus
            $spacesForBonusDeclaration = 8;
            $text .= str_repeat(" ",$spacesForBonusDeclaration);

            // 50 Date of approval of director's fees
            if ($rowPm['total_director_fee'] > 0) {
                $director_fee_date = $fn->getCPDate($cpCfg['cp.ir8aForm.DirectorFeeAgmDateInAis'], 'Ymd');
                $text .= $director_fee_date;
            } else {
                $spacesForDirectorFeeDate = 8;
                $text .= str_repeat(" ",$spacesForDirectorFeeDate);
            }

            // 51 Name of find for retirement benefits
            $spacesForRetirementBenefits = 60;
            $text .= str_repeat(" ",$spacesForRetirementBenefits);

            // 52 Name of designated person
            $spacesForDesignatedPerson = 60;
            $text .= str_repeat(" ",$spacesForDesignatedPerson);

            // 53 Name of bank
            $spacesForBankName = 1;
            $text .= str_repeat(" ",$spacesForBankName);

            // 54 Date of Payroll
            $spacesForPayrollDate = 8;
            $text .= str_repeat(" ",$spacesForPayrollDate);

            // 55 Filler
            $spacesForFiller = 393;
            $text .= str_repeat(" ",$spacesForFiller);

            // 56 Field reserved
            $spacesForReservedField = 50;
            $text .= str_repeat(" ",$spacesForReservedField);

            $text .=   "\n";
        }

        // 1 footer code comes here.
        $text .= "2"; // Hardcoded

        // 2 No. of Records
        $numrowsLength = strlen($numRows);
        $numrowsSpace = 6 - $numrowsLength;
        $text .= $numRows;
        if($numrowsSpace){
            $text .=  str_repeat(" ",$numrowsSpace);
        }

        // 3 Total amount of Payment (Item 10)
        $summaryFor10Space = 12 - strlen($summaryFor10);
        $text .= $summaryFor10;
        if($summaryFor10Space){
            $text .= str_repeat(" ",$summaryFor10Space);
        }

        // 4 Total amount of Salary (Item 16)
        $summaryForSalarySpace = 12 - strlen($summaryForSalary);
        $text .= $summaryForSalary;
        if($summaryForSalarySpace){
            $text .= str_repeat(" ",$summaryForSalarySpace);
        }

        // 5 Total amount of Bonus (Item 17)
        $text .= '0';
        $spacesForBonusSummary = 11;
        $text .= str_repeat(" ",$spacesForBonusSummary);

        // 6 Total amount of Director Fees (Item 18)
        $summaryForDirectorFeesSpace = 12 - strlen($summaryForDirectorFees);
        $text .= $summaryForDirectorFees;
        if($summaryForDirectorFeesSpace){
            $text .= str_repeat(" ",$summaryForDirectorFeesSpace);
        }

        // 7 Total amount of Others (Item 19)
        $summaryForOthersSpace = 12 - strlen($summaryForOthers);
        $text .= $summaryForOthers;
        if($summaryForOthersSpace){
            $text .= str_repeat(" ",$summaryForOthersSpace);
        }

        // 8 Total amount of exempt income (Item 20)
        $text .= '0';
        $spacesForExemptIncome = 11;
        $text .= str_repeat(" ",$spacesForExemptIncome);

        // 9 Total amount of tax borne by employer (Item 21)
        $text .= '0';
        $spacesForTaxEmployer = 11;
        $text .= str_repeat(" ",$spacesForTaxEmployer);

        // 10 Total amount of tax borne by employee (Item 22)
        $text .= '0';
        $spacesForTaxEmployee = 11;
        $text .= str_repeat(" ",$spacesForTaxEmployee);

        // 11 Total Donation (Item 13)
        $summaryForDonationSpace = 12 - strlen($summaryForDonation);
        $text .= $summaryForDonation;
        if($summaryForDonationSpace){
            $text .= str_repeat(" ",$summaryForDonationSpace);
        }

        // 12 Total CPF (Item 14)
        $summaryForCPFSpace = 12 - strlen($summaryForCPF);
        $text .= $summaryForCPF;
        if($summaryForCPFSpace){
            $text .= str_repeat(" ",$summaryForCPFSpace);
        }

        // 13 Total amount of insurance (Item 15)
        $text .= '0';
        $spacesForInsurance = 11;
        $text .= str_repeat(" ",$spacesForInsurance);

        // 14 Total amount of MBF (Item 12)
        $text .= '0';
        $spacesForMBF = 11;
        $text .= str_repeat(" ",$spacesForMBF);

        // 15 Filler
        $spacesForFillerSummary = 1049;
        $text .= str_repeat(" ",$spacesForFillerSummary);

        return $text;
    }

    /**
    *
    */
    function getCalculateGrossSalaryAndOTPay($year, $employee_id) {
        $db = Zend_Registry::get('db');

        $sqlPmMonth = "
        SELECT SUM(basic_pay) AS total_amount
             , SUM(cpf_employee) AS total_cpf_amount
             , SUM(pay_cdac) AS total_cdac
             , SUM(pay_sinda) AS total_sinda
             , SUM(pay_mbmf) AS total_mbmf
             , SUM(pay_eucf) AS total_eucf
             , SUM(director_fee) AS total_director_fee
             , SUM(allowance1) AS total_allowance1
             , SUM(allowance2) AS total_allowance2
             , SUM(allowance3) AS total_allowance3
             , SUM(allowance4) AS total_allowance4
             , SUM(allowance5) AS total_allowance5
        FROM payroll_management
        WHERE payroll_year = '{$year}'
          AND employee_id = '{$employee_id}'
          AND (status = 'Generated' OR status = 'Approved' OR status = 'Paid')
        ";
        $resultPmMonth = $db->sql_query($sqlPmMonth);
        $rowPmMonth = $db->sql_fetchrow($resultPmMonth);

        return $rowPmMonth;
    }

    /**
     *
     */
    function getUpdateOtHours() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $rec_id  = $fn->getReqParam('rec_id');
        $ot_hours  = $fn->getReqParam('ot_hours');
        $payRate  = $fn->getReqParam('payRate');
        $citizen  = $fn->getReqParam('citizen');

        $ot_amount = round($ot_hours * $payRate, 2);

        $SQL    = "UPDATE payroll_management SET ot_hours = '{$ot_hours}', ot_amount = '{$ot_amount}' WHERE payroll_management_id = {$rec_id}";
        $result = $db->sql_query($SQL);

        return $ot_amount;
    }

    /**
     *
     */
    function getUpdateAllowance() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rec_id  = $fn->getReqParam('rec_id');
        $allowance1  = $fn->getReqParam('allowance1');
        $allowance2  = $fn->getReqParam('allowance2');
        $allowance3  = $fn->getReqParam('allowance3');
        $allowance4  = $fn->getReqParam('allowance4');
        $allowance5  = $fn->getReqParam('allowance5');
        $deduction1  = $fn->getReqParam('deduction1');
        $deduction2  = $fn->getReqParam('deduction2');
        $deduction3  = $fn->getReqParam('deduction3');

        $fa = array();
        if($allowance1 != ''){
            $fa['allowance1']      = $allowance1;
        }
        if($allowance2 != ''){
            $fa['allowance2']      = $allowance2;
        }
        if($allowance3 != ''){
            $fa['allowance3']      = $allowance3;
        }
        if($allowance4 != ''){
            $fa['allowance4']      = $allowance4;
        }
        if($allowance5 != ''){
            $fa['allowance5']      = $allowance5;
        }
        if($deduction1 != ''){
            $fa['deduction1']      = $deduction1;
        }
        if($deduction2 != ''){
            $fa['deduction2']      = $deduction2;
        }
        if($deduction3 != ''){
            $fa['deduction3']      = $deduction3;
        }
        $whereCondition = "WHERE payroll_management_id = {$rec_id}";
        $SQLUpdate = $dbUtil->getUpdateSQLStringFromArray($fa, 'payroll_management', $whereCondition);
        $db->sql_query($SQLUpdate);

        return $ot_amount;
    }
}    