<?
class CP_Admin_Widgets_AceIms_AttendanceReports_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     * Reg No | Student Name | Course Title | Percentage Attended
     * SQL to find the list of contacts and their attendance percentage for different courses
     */
    function getSQL(){
        $SQL = "
        SELECT c.contact_id
              ,c.registration_no
              ,c.first_name AS trainee_name
              ,crse.title as course_title
              ,(
                SELECT count(a.attendance_id) 
                FROM attendance a
                JOIN batch bat ON (bat.batch_id = a.batch_id)
                WHERE a.contact_id = contact_id 
                  AND a.status = 'Present'
                  AND a.contact_id = att.contact_id
                  AND bat.course_id = b.course_id
               ) as total_present_days 
              ,(
                SELECT count(a.attendance_id) 
                FROM attendance a
                JOIN batch bat ON (bat.batch_id = a.batch_id)
                WHERE a.contact_id = contact_id 
                  AND a.contact_id = att.contact_id
                  AND bat.course_id = b.course_id
               ) as total_attendance_days 
        FROM attendance att
        JOIN batch b ON (att.batch_id = b.batch_id)
        JOIN course crse ON (b.course_id = crse.course_id)
        JOIN contact c ON (c.contact_id = att.contact_id)
        ";
        return $SQL;
    }
    
    /**
     * Search var condition if course selected
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        
        $course_id  = $fn->getReqParam('course_id');

        if ($course_id != ''){
            $searchVar->sqlSearchVar[] = "crse.course_id = {$course_id}";
        }
        
        $searchVar->groupBy = 'att.contact_id, b.course_id';
        $searchVar->sortOrder = 'c.registration_no';
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'aceIms_attendanceReports');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }
    
    /**
     * Reg No | Student Name | Course Title | Percentage Attended
     * Export to excel function to find the list of contacts and their attendance percentage for different courses
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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'S/No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Reg No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Student Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Course');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Percentage Attended');
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
        $course_id = $fn->getReqParam('course_id');

        if ($course_id != '') {
            $sqlAppend = "WHERE crse.course_id = {$course_id}";
        }

        $SQL = "
        SELECT c.contact_id
              ,c.registration_no
              ,c.first_name AS trainee_name
              ,crse.title as course_title
              ,(
                SELECT count(a.attendance_id) 
                FROM attendance a
                JOIN batch bat ON (bat.batch_id = a.batch_id)
                WHERE a.contact_id = contact_id 
                  AND a.status = 'Present'
                  AND a.contact_id = att.contact_id
                  AND bat.course_id = b.course_id
               ) as total_present_days 
              ,(
                SELECT count(a.attendance_id) 
                FROM attendance a
                JOIN batch bat ON (bat.batch_id = a.batch_id)
                WHERE a.contact_id = contact_id 
                  AND a.contact_id = att.contact_id
                  AND bat.course_id = b.course_id
               ) as total_attendance_days 
        FROM attendance att
        JOIN batch b ON (att.batch_id = b.batch_id)
        JOIN course crse ON (b.course_id = crse.course_id)
        JOIN contact c ON (c.contact_id = att.contact_id)
        {$sqlAppend}
        GROUP BY att.contact_id, b.course_id
        ORDER BY c.registration_no 
        ";

        $result = $db->sql_query($SQL);

        $serial_no = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $serial_no += 1;
            //$subjectRec['title'] = '';
            if($row['total_present_days'] == 0){
                $percent = 0;
            }
            else{
                $percent = ($row['total_present_days'] / $row['total_attendance_days']) * 100;
                $percent = number_format($percent,2);
            }

            $colc = 0;
            $rowc++;
        
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $serial_no);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['registration_no']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['trainee_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['course_title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $percent . '%');
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   
}