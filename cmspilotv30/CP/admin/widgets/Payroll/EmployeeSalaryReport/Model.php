<?
class CP_Admin_Widgets_Payroll_EmployeeSalaryReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT e.*
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
              ,e.date_of_birth AS dob
        FROM employee e
        ";

        return $SQL;
    }
    
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        
        $searchVar = $this->searchVar;        
        $searchVar->mainTableAlias = 'e';

        $employee_status = $fn->getReqParam('employee_status');
        
        $searchVar->sqlSearchVar[] = "e.status = '{$employee_status}'";
        $searchVar->sortOrder = 'e.first_name ASC';
    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'payroll_employeeSalaryReport');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }
    
    /**
     */
    function getExportToExcel(){
        $tv       = Zend_Registry::get('tv');
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $cpUtil   = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');

        $employee_status = $fn->getReqParam('employee_status');

        $rows = '';

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "EmployeeSalaryReport_" . date("d-m-Y") . ".xls";

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
        $actSheet = &$objPHPExcel->getActiveSheet();
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'S.No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Employee Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'NRIC');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'DOB');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Age');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Designation');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Department');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Basic Pay');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Allowances');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Deductions');
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

        $appendSql = '';       
        $employee_status = $fn->getReqParam('employee_status');
        
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlSite = "";
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND e.site_id = {$cpSiteIdSession}";
        }

        $SQL = "
        SELECT e.*
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
              ,e.date_of_birth AS dob
        FROM employee e
        WHERE e.status = '{$employee_status}'
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

            $age = '';
            if ($row['dob']) {
                $dob = $fn->getCPDate($row['dob'], 'Y');
                $age = date('Y')- $dob;
            }

            if ($row['status'] == 'Current') {
                $sqlAppend = "j.status = 'Current'";                
            } else {
                $sqlAppend = "j.status != 'Current'";
            }

            $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
            $appendSqlSite1 = "";
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSqlSite1 = "AND j.site_id = {$cpSiteIdSession}";
            }

            $sqlJobInfo = "
            SELECT j.designation
                  ,j.department
                  ,j.basic_pay
                  ,j.allowance1
                  ,j.allowance2
                  ,j.allowance3
                  ,j.deduction1
                  ,j.deduction2
                  ,j.deduction3
            FROM job_information j
            WHERE j.employee_id = {$row['employee_id']}
              AND {$sqlAppend}
              {$appendSqlSite1}
            ORDER BY j.job_information_id DESC
            LIMIT 0,1
            ";
            $resultJobInfo = $db->sql_query($sqlJobInfo);
            $rowJi = $db->sql_fetchrow($resultJobInfo);

            $total_allowance = $rowJi['allowance1'] + $rowJi['allowance2'] + $rowJi['allowance3'];
            $total_deduction = $rowJi['deduction1'] + $rowJi['deduction2'] + $rowJi['deduction3'];
            $net_pay =$rowJi['basic_pay'] + $total_allowance - $total_deduction;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $count);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['employee_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $ic_no);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['dob']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $age);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowJi['designation']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowJi['department']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowJi['basic_pay']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_allowance);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_deduction);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $net_pay);

            $count++;
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:J{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}