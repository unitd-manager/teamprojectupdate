<?
class CPL_Admin_Widgets_Project_OperationalFinancialReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT e.*
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
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

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        
        $searchVar->sqlSearchVar[] = "(e.citizen = 'WP' OR e.citizen = 'SP')";
        $searchVar->sqlSearchVar[] = "e.employee_type = 'In house'";
        
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
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Fin No.');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Client');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Levy');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Normal Rate');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'OT Rate');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Dorm');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
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

        $SQL = "
        SELECT e.*
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
        FROM employee e
        WHERE (e.citizen = 'WP' OR e.citizen = 'SP')
        AND e.employee_type = 'In house'
        ORDER BY e.first_name ASC
        ";
        $result = $db->sql_query($SQL);
        $counter = 1;
        $overallTotal = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $start_date = $fn->getReqParam('start_date');
            $end_date   = $fn->getReqParam('end_date');
            $current_date = date('Y-m-d');

            if ($row['citizen'] == 'Citizen' || $row['citizen'] == 'PR') {
                $ic_no = $row['nric_no'];
            } else {
                $ic_no = $row['fin_no'];
            }

            if ($start_date != '' && $end_date == '') {
                $start_date = $dateUtil->formatDate($start_date, 'DD-MM-YYYY');
                $end_date = $current_date;
            } else if ($start_date == '' && $end_date != ''){
                $start_date = $current_date;
                $end_date = $dateUtil->formatDate($end_date, 'DD-MM-YYYY');
            } else if ($start_date != '' && $end_date != '') {
                $start_date = $dateUtil->formatDate($start_date, 'DD-MM-YYYY');
                $end_date = $dateUtil->formatDate($end_date, 'DD-MM-YYYY');
            } else {
                $start_date = $current_date;
                $end_date = $current_date;
            }

              //AND (j.act_join_date >= '{$start_date}' AND j.termination_date <= '{$end_date}')
            $sqlJobInfo = "
            SELECT j.*
            FROM job_information j
            WHERE j.employee_id = {$row['employee_id']}
              AND (j.termination_date <= '{$end_date}')
            ORDER BY j.job_information_id DESC
            ";
            $resultJobInfo = $db->sql_query($sqlJobInfo);
            $numRows = $db->sql_numrows($resultJobInfo);
            if($numRows == 0){
                $sqlJobInfo = "
                SELECT j.*
                FROM job_information j
                WHERE j.employee_id = {$row['employee_id']}
                  AND (j.termination_date = '' OR j.termination_date IS NULL)
                ORDER BY j.job_information_id DESC
                ";
                $resultJobInfo = $db->sql_query($sqlJobInfo);                
            }
            $rowJi = $db->sql_fetchrow($resultJobInfo);


            //$basic_pay_formatted = number_format($rowJi['basic_pay'], 2);
            $SQLTimesheet = "
            SELECT *
            FROM employee_timesheet
            WHERE employee_id = {$row['employee_id']}
            AND (date >= '{$start_date}' AND date <= '{$end_date}')
            ";
            $resultTimesheet = $db->sql_query($SQLTimesheet);
            $client = 0;
            $nr_rate = 0;
            $ot_rate = 0;
            while ($rowTimesheet = $db->sql_fetchrow($resultTimesheet)) {
                $nrRate = $rowTimesheet['employee_hours'] * $rowTimesheet['hourly_rate'];
                $otRate = $rowTimesheet['employee_ot_hours'] * $rowTimesheet['ot_hourly_rate'];
                $phRate = $rowTimesheet['employee_ph_hours'] * $rowTimesheet['ph_hourly_rate'];
                $client += $nrRate + $otRate + $phRate; 
                $nr_rate += $rowTimesheet['employee_hours'];
                $ot_rate += $rowTimesheet['employee_ot_hours'] + $rowTimesheet['employee_ph_hours'];
            }

            $normalRate = (($rowJi['basic_pay'] / 30) / 8) * $nr_rate;
            $overtimeRate = ((($rowJi['basic_pay'] / 30) / 8) * $rowJi['over_time_rate']) * $ot_rate;

            $datetime1  = date_create($start_date); 
            $datetime2  = date_create($end_date); 
            $interval   = date_diff($datetime1, $datetime2); 
            $no_of_days = $interval->format('%a') + 1;
            $levy_amount = ($rowJi['levy_amount'] / 30) * $no_of_days;
            $deduction1 = (($rowJi['deduction1'] + 30) / 30) * $no_of_days;
            $total = $client - ($levy_amount + $normalRate + $overtimeRate + $deduction1);
            $overallTotal += $total;
            
            $client = number_format($client, 2);
            $normalRate = number_format($normalRate, 2);
            $overtimeRate = number_format($overtimeRate, 2);
            $levy_amount = number_format($levy_amount, 2);
            $deduction1 = number_format($deduction1, 2);

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $counter);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['employee_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $ic_no);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $client);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $levy_amount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $normalRate);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $overtimeRate);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $deduction1);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total);

            $counter++;
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:J{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}