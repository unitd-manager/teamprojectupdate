<?
class CP_Admin_Widgets_AceIms_TeacherAttendanceReportEnt_Model extends CP_Common_Lib_WidgetModelAbstract
{
    // Date | Time In | Time Out | Teaching Hours | Amount
    function getSQL(){

        $SQL = "
        SELECT ta.*
              ,CONCAT_WS(' ', t.first_name, t.last_name ) AS teacher_name
              ,t.amount
        FROM teacher_attendance ta
        LEFT JOIN (teacher t) ON (ta.teacher_id = t.teacher_id)
        ";
        return $SQL;
    }
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'ta';

        /*$start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');*/
        $teacher_id  = $fn->getReqParam('teacher_id');

        /*$current_year = date('Y');
        $search_date = $current_year . '-' . $month . '-' . '%';*/

        if ($teacher_id != ''){
            $searchVar->sqlSearchVar[] = "ta.teacher_id = '{$teacher_id}'";
        }

        /*if ($start_date != ''){
            $searchVar->sqlSearchVar[] = "att.date >= '{$start_date}'";
        }
        
        if ($end_date != ''){
            $searchVar->sqlSearchVar[] = "att.date <= '{$end_date}'";
        }
        
        $searchVar->groupBy = 'att.contact_id, b.course_id';*/
        $searchVar->sortOrder = 'ta.teacher_id';
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'aceIms_teacherAttendanceReportEnt');

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

        $teacher_id = $fn->getReqParam('teacher_id');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Teacher_Attendance_" . date("d-m-Y") . ".xls";

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
        
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'S/No');
        if ($teacher_id == '') {
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Teacher Name');
        }
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Time-In');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Time-Out');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Teaching hrs');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Amount');
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
        $teacher_id  = $fn->getReqParam('teacher_id');

        /*$start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $course_id  = $fn->getReqParam('course_id');
        $month      = $fn->getReqParam('month');

        $current_year = date('Y');
        $search_date = $current_year . '-' . $month . '-' . '%';*/

        if ($teacher_id != '') {
            $sqlAppend = "WHERE ta.teacher_id = '{$teacher_id}'";
        }

        $SQL = "
        SELECT ta.*
              ,CONCAT_WS(' ', t.first_name, t.last_name ) AS teacher_name
              ,t.amount
        FROM teacher_attendance ta
        LEFT JOIN (teacher t) ON (ta.teacher_id = t.teacher_id)
        {$sqlAppend}
        ORDER BY ta.teacher_id 
        ";

        $result = $db->sql_query($SQL);

        $serial_no = 0;
        $total_amount = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $serial_no += 1;

            $date = $fn->getCPDate($row['date'], 'd-m-Y');

            $colc = 0;
            $rowc++;
        
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $serial_no);
            if ($teacher_id == '') {
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['teacher_name']);
            } else {
                $total_amount += $row['amount'];
            }
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['time_in']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['time_out']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['amount']);
        }

        $colc = 0;
        $rowc++;
        
        if ($teacher_id != '') {
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_amount);
        }

        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   
}