<?
class CP_Admin_Widgets_Pms_StaffAttendanceOverallReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    // Staff Name | No of Leave Days | Avg Time In | Avg Time Out
    function getSQL(){
        $fn = Zend_Registry::get('fn');
        $month = $fn->getReqParam('month');
        $year  = $fn->getReqParam('year');
        
        $SQLYearAppend = '';
        $SQLMonthAppend = '';
        
        if ($year != ''){
            $startYear = $year .'-01-01'; 
            $endYear   = $year .'-12-31';
        
            $SQLYearAppend .= "AND a.record_date BETWEEN '{$startYear}' AND '{$endYear}'";
        }

        if ($month != ''){
            if ($year != '') {
                $startMonth = $year . '-' . $month . '-' . '01';
                $endMonth   = $year . '-' . $month . '-' . '31';
            } else {
                $year = date('Y');
                $startMonth = $year . '-' . $month . '-' . '01';
                $endMonth   = $year . '-' . $month . '-' . '31';
            }
            $SQLMonthAppend .= "AND a.record_date BETWEEN '{$startMonth}' AND '{$endMonth}'";
        }

        $SQL = "
        SELECT DISTINCT sa.staff_id
             , CONCAT(s.first_name, ' ', s.last_name) AS staff_name
             ,(
               SELECT SEC_TO_TIME(AVG(TIME_TO_SEC(a.time_in)))
               FROM staff_attendance a
               WHERE sa.staff_id = a.staff_id
                 AND a.on_leave IS NULL
                 {$SQLYearAppend}
                 {$SQLMonthAppend}
             ) AS avg_time_in
             ,(
               SELECT SEC_TO_TIME(AVG(TIME_TO_SEC(a.leave_time)))
               FROM staff_attendance a
               WHERE sa.staff_id = a.staff_id
                 AND a.on_leave IS NULL
                 {$SQLYearAppend}
                 {$SQLMonthAppend}
             ) AS avg_leave_time
             ,(
               SELECT count(a.staff_attendance_id) 
               FROM staff_attendance a
               WHERE sa.staff_id = a.staff_id
                 {$SQLYearAppend}
                 {$SQLMonthAppend}
              ) as total_attendance_days 
             ,(
               SELECT count(a.staff_attendance_id) 
               FROM staff_attendance a
               WHERE sa.staff_id = a.staff_id
                 AND a.on_leave IS NULL
                 {$SQLYearAppend}
                 {$SQLMonthAppend}
              ) as total_present_days 
        FROM staff_attendance sa
        LEFT JOIN (staff s) ON (sa.staff_id = s.staff_id)
        ";
        return $SQL;
    }
    
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        
        $month     = $fn->getReqParam('month');
        $year      = $fn->getReqParam('year');
        $staff_id  = $fn->getReqParam('staff_id');
        
        if ($month != ''){
            if ($year != '') {
                $startMonth = $year . '-' . $month . '-' . '01';
                $endMonth   = $year . '-' . $month . '-' . '31';
            } else {
                $year = date('Y');
                $startMonth = $year . '-' . $month . '-' . '01';
                $endMonth   = $year . '-' . $month . '-' . '31';
            }
            $searchVar->sqlSearchVar[] = "sa.record_date BETWEEN '{$startMonth}' AND '{$endMonth}'";
        }

        if ($year != ''){
            $startYear = $year .'-01-01'; 
            $endYear   = $year .'-12-31';
        
            $searchVar->sqlSearchVar[] = "sa.record_date BETWEEN '{$startYear}' AND '{$endYear}'";
        }
        
        if ($staff_id != '' ) {
            $searchVar->sqlSearchVar[] = "s.staff_id = {$staff_id}";
        }
        
        
        $searchVar->sortOrder = 'sa.staff_attendance_id DESC';
    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'pms_attendanceReports');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }
    
    /**
     *
     */
    function getExportToExcel(){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');

        $month     = $fn->getReqParam('month');
        $year      = $fn->getReqParam('year');
        $staff_id  = $fn->getReqParam('staff_id');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Attendance_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Serial No.');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Staff Name');
        if ($month) {
            $monthHeader = $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Month');
        }
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'No of Leave Days');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Avg Time In');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Avg Time Out');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Attendance Percentage');
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
        
        $sqlAppend = '';
        $SQLYearAppend = '';
        $SQLMonthAppend = '';
        
        if ($year != ''){
            $startYear = $year .'-01-01'; 
            $endYear   = $year .'-12-31';
        
            $SQLYearAppend .= "AND a.record_date BETWEEN '{$startYear}' AND '{$endYear}'";
            $sqlAppend .= "WHERE sa.record_date BETWEEN '{$startYear}' AND '{$endYear}'";
        }

        if ($month != ''){
            if ($year != '') {
                $startMonth = $year . '-' . $month . '-' . '01';
                $endMonth   = $year . '-' . $month . '-' . '31';
            } else {
                $year = date('Y');
                $startMonth = $year . '-' . $month . '-' . '01';
                $endMonth   = $year . '-' . $month . '-' . '31';
            }
            $SQLMonthAppend .= "AND a.record_date BETWEEN '{$startMonth}' AND '{$endMonth}'";
            
            if ($year == '') {
                $sqlAppend .= "WHERE sa.record_date BETWEEN '{$startMonth}' AND '{$endMonth}'";
            } else {
                $sqlAppend .= "AND sa.record_date BETWEEN '{$startMonth}' AND '{$endMonth}'";
            }
        }

        if ($staff_id != ''){
            if ($year == '' && $month == '') {
                $sqlAppend .= "WHERE sa.staff_id = {$staff_id}";
            } else {
                $sqlAppend .= "AND sa.staff_id = {$staff_id}";
            }
        }
        
        $SQL = "
        SELECT DISTINCT sa.staff_id
             , CONCAT(s.first_name, ' ', s.last_name) AS staff_name
             ,(
               SELECT SEC_TO_TIME(AVG(TIME_TO_SEC(a.time_in)))
               FROM staff_attendance a
               WHERE sa.staff_id = a.staff_id
                 AND a.on_leave IS NULL
                 {$SQLYearAppend}
                 {$SQLMonthAppend}
             ) AS avg_time_in
             ,(
               SELECT SEC_TO_TIME(AVG(TIME_TO_SEC(a.leave_time)))
               FROM staff_attendance a
               WHERE sa.staff_id = a.staff_id
                 AND a.on_leave IS NULL
                 {$SQLYearAppend}
                 {$SQLMonthAppend}
             ) AS avg_leave_time
             ,(
               SELECT count(a.staff_attendance_id) 
               FROM staff_attendance a
               WHERE sa.staff_id = a.staff_id
                 {$SQLYearAppend}
                 {$SQLMonthAppend}
              ) as total_attendance_days 
             ,(
               SELECT count(a.staff_attendance_id) 
               FROM staff_attendance a
               WHERE sa.staff_id = a.staff_id
                 AND a.on_leave IS NULL
                 {$SQLYearAppend}
                 {$SQLMonthAppend}
              ) as total_present_days 
        FROM staff_attendance sa
        LEFT JOIN (staff s) ON (sa.staff_id = s.staff_id)
        {$sqlAppend}
        ORDER BY sa.staff_attendance_id DESC
        ";
        $result = $db->sql_query($SQL);

        $serial_no = 0;
        while ($row = $db->sql_fetchrow($result)) {
            if ($row['staff_name']) {
                $serial_no += 1;
                $SQLAppendInner = '';
                
                if ($year != ''){
                    $startYear = $year .'-01-01'; 
                    $endYear   = $year .'-12-31';
                
                    $SQLAppendInner .= "AND sa.record_date BETWEEN '{$startYear}' AND '{$endYear}'";
                }
        
                if ($month != ''){
                    if ($year != '') {
                        $startMonth = $year . '-' . $month . '-' . '01';
                        $endMonth   = $year . '-' . $month . '-' . '31';
                    } else {
                        $year = date('Y');
                        $startMonth = $year . '-' . $month . '-' . '01';
                        $endMonth   = $year . '-' . $month . '-' . '31';
                    }
                    $SQLAppendInner .= "AND sa.record_date BETWEEN '{$startMonth}' AND '{$endMonth}'";
                }
                
                $SQLLeave = "
                SELECT count(sa.staff_attendance_id) AS total_leave_days
                FROM staff_attendance sa
                WHERE sa.staff_id = {$row['staff_id']}
                  AND sa.on_leave = 1
                  {$SQLAppendInner}
                ";
                $resultLeave = $db->sql_query($SQLLeave);
                $rowLeave = $db->sql_fetchrow($resultLeave);
    
                $colc = 0;
                $rowc++;
            
                switch ($month) {
                    case '01': $monthRow = 'January';
                    break;
    
                    case '02': $monthRow = 'February';
                    break;
    
                    case '03': $monthRow = 'March';
                    break;
    
                    case '04': $monthRow = 'April';
                    break;
    
                    case '05': $monthRow = 'May';
                    break;
    
                    case '06': $monthRow = 'June';
                    break;
    
                    case '07': $monthRow = 'July';
                    break;
    
                    case '08': $monthRow = 'August';
                    break;
    
                    case '09': $monthRow = 'September';
                    break;
    
                    case '10': $monthRow = 'October';
                    break;
    
                    case '11': $monthRow = 'November';
                    break;
    
                    case '12': $monthRow = 'December';
                    break;
                }
    
                $attendance_percent = number_format(($row['total_present_days'] / $row['total_attendance_days']) * 100, 2);
                
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $serial_no);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['staff_name']);
                if ($month) {
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $monthRow);
                }
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowLeave['total_leave_days']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['avg_time_in']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['avg_leave_time']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $attendance_percent);
            }
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   
}