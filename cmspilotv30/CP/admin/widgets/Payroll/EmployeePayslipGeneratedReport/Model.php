<?
class CP_Admin_Widgets_Payroll_EmployeePayslipGeneratedReport_Model extends CP_Common_Lib_WidgetModelAbstract
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
        LEFT JOIN (job_information j) ON (j.employee_id = e.employee_id)
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

        $month = $fn->getReqParam('month');
        $year  = $fn->getReqParam('year');
         
        $searchVar->sqlSearchVar[] = "pm.payroll_month = '{$month}'";
        $searchVar->sqlSearchVar[] = "pm.payroll_year = '{$year}'";
        
        
        $searchVar->sortOrder = 'e.first_name ASC';
    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'payroll_employeePayslipGeneratedReport');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }
    
    /**
     */
    function getExportToExcel(){
        $db       = Zend_Registry::get('db');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $tv       = Zend_Registry::get('tv');
        $cpUtil   = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn       = Zend_Registry::get('fn');

        $month = $fn->getReqParam('month');
        $year  = $fn->getReqParam('year');

        $current_date = date('Y-m-d');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "EmployeePayslipGeneratedReport_" . date("d-m-Y") . ".xls";

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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Basic Pay');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'OT Amount');
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
        WHERE pm.payroll_month = '{$month}'
          AND pm.payroll_year = '{$year}'
          {$appendSqlSite}
        ORDER BY e.first_name ASC
        ";
        $result = $db->sql_query($SQL);
        $count = 1;

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

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $count);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['employee_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['citizen']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['employee_status']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $ic_no);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['basic_pay']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $OT);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $cpf_employer);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $cpf_employee);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_allowance);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['reimbursement']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_deduction);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $net_total);

            $count++;
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:K{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}