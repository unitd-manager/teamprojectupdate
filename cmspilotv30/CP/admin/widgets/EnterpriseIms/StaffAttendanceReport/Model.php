<?
class CP_Admin_Widgets_EnterpriseIms_StaffAttendanceReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    // Date | Name | Leave Taken | Time In | Time Out
    function getSQL(){
        $SQL = "
        SELECT sa.*
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
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
     * @param <type> $SQL
     * @return <type>
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enterpriseIms_attendanceReports');

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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Leave Taken');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Time In');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Time Out');
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
        $month     = $fn->getReqParam('month');
        $year      = $fn->getReqParam('year');
        $staff_id  = $fn->getReqParam('staff_id');

        if ($year != ''){
            $startYear = $year .'-01-01'; 
            $endYear   = $year .'-12-31';
        
            $sqlAppend .= "WHERE sa.record_date BETWEEN '{$startYear}' AND '{$endYear}'";
        }
        
        if ($month != ''){
            if ($year != '') {
                $startMonth = $year . '-' . $month . '-' . '01';
                $endMonth   = $year . '-' . $month . '-' . '31';
                $sqlAppend .= "AND sa.record_date BETWEEN '{$startMonth}' AND '{$endMonth}'";
            } else {
                $year = date('Y');
                $startMonth = $year . '-' . $month . '-' . '01';
                $endMonth   = $year . '-' . $month . '-' . '31';
                $sqlAppend .= "WHERE sa.record_date BETWEEN '{$startMonth}' AND '{$endMonth}'";
            }
        }

        if ($staff_id != '') {
            if ($year == '' && $month == '') {
                $sqlAppend .= "WHERE s.staff_id = {$staff_id}";
            } else {
                $sqlAppend .= "AND s.staff_id = {$staff_id}";
            }
        }

        $SQL = "
        SELECT sa.*
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
        FROM staff_attendance sa
        LEFT JOIN (staff s) ON (sa.staff_id = s.staff_id)
        {$sqlAppend}
        ORDER BY sa.staff_attendance_id DESC
        ";

        $result = $db->sql_query($SQL);
        $serial_no = 0;
        while ($row = $db->sql_fetchrow($result)) {

            $serial_no += 1;
            $attendance_date = $dateUtil->formatDate($row['record_date'], 'DD-MM-YYYY');
            $on_leave = ($row['on_leave'] == 1) ? "Yes" : "No";

            $colc = 0;
            $rowc++;
        
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $serial_no);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $attendance_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['staff_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $on_leave);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['time_in']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['leave_time']);
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   
}