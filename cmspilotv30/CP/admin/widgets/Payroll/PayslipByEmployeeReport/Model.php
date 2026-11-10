<?
class CP_Admin_Widgets_Payroll_PayslipByEmployeeReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT pm.*
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
              ,e.date_of_birth AS dob
              ,e.citizen
              ,e.nric_no
              ,e.fin_no
              ,e.status AS employee_status
        FROM payroll_management pm
        LEFT JOIN (employee e) ON (pm.employee_id = e.employee_id)
        ";

        return $SQL;
    }
    
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        
        $searchVar->mainTableAlias = 'pm';

        $employee_id = $fn->getReqParam('employee_id');
        $year        = $fn->getReqParam('year');

        $searchVar->sqlSearchVar[] = "pm.employee_id = {$employee_id}";
        $searchVar->sqlSearchVar[] = "pm.payroll_year = '{$year}'";
        $searchVar->sqlSearchVar[] = "(pm.status = 'Paid' OR pm.status = 'Approved' OR pm.status = 'Generated')";
        
        $searchVar->sortOrder = 'pm.payroll_month ASC';
    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'payroll_payslipByEmployeeReport');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }
    
    /**
     */
    function getExportToExcel(){
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $tv       = Zend_Registry::get('tv');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $cpUtil   = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');

        $employee_id = $fn->getReqParam('employee_id');
        $year        = $fn->getReqParam('year');

        $current_date = date('Y-m-d');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "PayslipByEmployeeReport_" . date("d-m-Y") . ".xls";

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment;filename={$file_name}");
        header("Content-Transfer-Encoding: binary ");

        $objPHPExcel = new PHPExcel();

        //--------------------------------------------------//
        $rowc = 1;
        $colc = 0;
        $row  = 1;
        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'S.No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Employee Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Pass Type');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Employee Status');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'NRIC/FIN No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Payroll Month');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Payroll Year');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Basic Pay');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'OT Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Director Fee');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'CPF(Employer)');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'CPF(Employee)');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Allowance');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Reimbursement');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Deductions');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Net Pay');
       /******************** FORMAT HEADER *******************/
        $headStyle = array(
            'font' => array('bold' => true)
        );

        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A1:{$lastCol}1")->applyFromArray($headStyle);

        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlSite = "";
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND pm.site_id = {$cpSiteIdSession}";
        }

        $SQL = "
        SELECT pm.*
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
              ,e.date_of_birth AS dob
              ,e.citizen
              ,e.nric_no
              ,e.fin_no
              ,e.status AS employee_status
        FROM payroll_management pm
        LEFT JOIN (employee e) ON (pm.employee_id = e.employee_id)
        WHERE pm.employee_id = {$employee_id}
          AND pm.payroll_year = '{$year}'
          AND (pm.status = 'Paid' OR pm.status = 'Approved' OR pm.status = 'Generated')
          {$appendSqlSite}
        ORDER BY pm.payroll_month ASC
        ";
        $result = $db->sql_query($SQL);
        $count = 1;
        $total_basic_pay = 0;
        $total_ot_amount = 0;
        $total_director_amount = 0;
        $total_cpf_employer = 0;
        $total_cpf_employee = 0;
        $total_allowance_summary = 0;
        $total_reimbursement = 0;
        $total_deduction_summary = 0;
        $total_net_pay = 0;

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            if ($row['citizen'] == 'Citizen' || $row['citizen'] == 'PR') {
                $ic_no = $row['nric_no'];
            } else {
                $ic_no = $row['fin_no'];
            }

            $OT  = $row['ot_hours'] * $row['overtime_pay_rate'];
            $gross_pay = $row['basic_pay'] + $row['ot_amount'] + $row['commission'] + $row['allowance1'] + $row['allowance2'] + $row['allowance3'] + $row['allowance4'] + $row['allowance5'];

            $total_allowance = $row['allowance1'] + $row['allowance2'] + $row['allowance3'] + $row['allowance4'] + $row['allowance5'];
            $total_deduction = round($row['cpf_employee'], 2) + $row['sdl'] + $row['loan_amount'] + $row['income_tax_amount'] + $row['pay_cdac'] + $row['pay_sinda'] + $row['pay_mbmf'] + $row['pay_eucf'] + $row['deduction1'] + $row['deduction2'] + $row['deduction3'] + $row['loan_deduction'];            
            $net_total = $gross_pay + $row['reimbursement'] - $total_deduction;

            $cpf_employer = 0.00;
            if ($row['cpf_employer']) {
                $cpf_employer = $row['cpf_employer'];
            }

            $cpf_employee = 0.00;
            if ($row['cpf_employee']) {
                $cpf_employee = $row['cpf_employee'];
            }

            $month = $dateUtil->getLongMonthName($row['payroll_month']);

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $count);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['employee_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['citizen']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['employee_status']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $ic_no);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $month);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $year);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['basic_pay']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $OT);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['director_fee']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $cpf_employer);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $cpf_employee);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_allowance);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['reimbursement']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_deduction);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $net_total);

            $total_basic_pay += $row['basic_pay'];
            $total_ot_amount += $OT;
            $total_director_amount += $row['director_fee'];
            $total_cpf_employer += $cpf_employer;
            $total_cpf_employee += $cpf_employee;
            $total_allowance_summary += $total_allowance;
            $total_reimbursement += $row['reimbursement'];
            $total_deduction_summary += $total_deduction;
            $total_net_pay += $net_total;

            $count++;
        }

        $colc = 0;
        $rowc++;
        $actSheet->getStyle("A{$rowc}:P{$rowc}")->applyFromArray($headStyle);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_basic_pay);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_ot_amount);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_director_amount);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_cpf_employer);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_cpf_employee);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_allowance_summary);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_reimbursement);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_deduction_summary);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_net_pay);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}