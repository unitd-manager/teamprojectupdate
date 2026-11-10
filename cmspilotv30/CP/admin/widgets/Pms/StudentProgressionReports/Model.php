<?
class CP_Admin_Widgets_Pms_StudentProgressionReports_Model extends CP_Common_Lib_WidgetModelAbstract
{
    function getSQL(){

        $SQL = "
        SELECT DISTINCT(c.contact_id)
              ,c.registration_no
              ,CONCAT(c.first_name, ' ', c.last_name) AS trainee_name
              ,c.id_card_no
              ,sg.batch_id
              ,sg.marks
              ,sg.grade
              ,sg.exam_type
              ,sg.exam_date
              ,crse.title as course_title
              ,crse.valid_date_from  
              ,crse.valid_date_to 
        FROM batch_history bh
        JOIN contact c ON (c.contact_id = bh.contact_id)
        JOIN course_contact cc ON (cc.contact_id = c.contact_id)
        JOIN course crse ON (cc.course_id = crse.course_id)
        JOIN student_grade sg ON (c.contact_id = sg.contact_id)
        ";

        return $SQL;
    }
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $status     = $fn->getReqParam('status');

        $searchVar->sqlSearchVar[] = "sg.marks IS NOT NULL";

        //$searchVar->groupBy = 'oi.record_id';
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'pms_studentProgressionReports');

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'S/No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Student Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Reg No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'NRIC No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Course');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Start Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'End Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Status');
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
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $course_id  = $fn->getReqParam('course_id');
        $month      = $fn->getReqParam('month');

        $current_year = date('Y');
        $search_date = $current_year . '-' . $month . '-' . '%';

        if($month != '') {
            $sqlAppend = "WHERE att1.date like '{$search_date}'";
        }

        $SQL = "
        SELECT c.contact_id
              ,c.registration_no
              ,CONCAT(c.first_name, ' ', c.last_name) AS trainee_name
              ,c.id_card_no
              ,crse.title as course_title
              ,crse.valid_date_from  
              ,crse.valid_date_to 
              ,cc.course_status
        FROM contact c
        LEFT JOIN course_contact cc ON (c.contact_id = cc.contact_id)
        LEFT JOIN course crse ON (cc.course_id = crse.course_id)
        {$sqlAppend}
        ORDER BY c.registration_no 
        ";

        $result = $db->sql_query($SQL);

        $serial_no = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $serial_no += 1;

            $colc = 0;
            $rowc++;
        
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $serial_no);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['trainee_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['registration_no']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['id_card_no']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['course_title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['valid_date_from']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['valid_date_to']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['course_status']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['gender']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['nationality']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['date_of_birth']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['mobile']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['phone']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['emergency_contact_name']);
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   
}