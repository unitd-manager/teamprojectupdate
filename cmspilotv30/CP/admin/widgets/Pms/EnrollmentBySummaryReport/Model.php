<?
class CP_Admin_Widgets_Pms_EnrollmentBySummaryReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT COUNT(*) AS no_of_students
              ,cc.year_of_enrollment
        FROM course_contact cc
        LEFT JOIN (contact c) ON (cc.contact_id = c.contact_id)
        ";

        $SQL = "
        SELECT DISTINCT c.contact_id
              ,c.first_name AS student_name
              ,c.date_of_birth
              ,c.gender
              ,c.registration_no
              ,c.id_card_no
              ,c.year_of_joining
              ,s.title AS branch_name
        FROM contact c
        LEFT JOIN (course_contact cc) ON (c.contact_id = cc.contact_id)
        LEFT JOIN (site s) ON (c.site_id = s.site_id)
        ";

        return $SQL;
    }
    
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;

        $site_id   = $fn->getReqParam('site_id');
        $course_id = $fn->getReqParam('course_id');
        $batch_id  = $fn->getReqParam('batch_id');
        
        if ($site_id) {
            if(is_numeric($site_id)) {
                $searchVar->sqlSearchVar[] = "cc.site_id = '{$site_id}'";
            }
        }

        if ($course_id != '') {
            $searchVar->sqlSearchVar[] = "cc.course_id = '{$course_id}'";
        }

        if ($batch_id != '') {
            $searchVar->sqlSearchVar[] = "cc.batch_id = '{$batch_id}'";
        }

        $current_year = date('Y');
        $searchVar->sqlSearchVar[] = "c.status = 'Active'";
        $searchVar->sqlSearchVar[] = "cc.year_of_enrollment = {$current_year}";

        $searchVar->sortOrder = "c.site_id ASC, c.registration_no ASC";
        //$searchVar->groupBy = "cc.year_of_enrollment ASC";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'pms_enrollmentBySummaryReport');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }

    /**
     */
    function getExportToExcel(){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');

        $sqlAppend = '';

        $site_id   = $fn->getReqParam('site_id');
        $course_id = $fn->getReqParam('course_id');
        $batch_id  = $fn->getReqParam('batch_id');

        if (is_numeric($site_id)) {
            $sqlAppend .= " AND cc.site_id = {$site_id}";
        }

        if ($course_id != '') {
            $sqlAppend .= " AND cc.course_id = '{$course_id}'";
        }

        if ($batch_id != '') {
            $sqlAppend .= " AND cc.batch_id = '{$batch_id}'";
        }

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "EnrollmentBySummaryReport__" . date("d-m-Y") . ".xls";
        
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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Branch');

        if ($course_id) {
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Class');
        }

        if ($batch_id) {
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Session');
        }

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Name of Student');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Reg No.');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'NRIC No.');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Gender');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Year of Joining');
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

        $current_year = date('Y');
        $SQL = "
        SELECT DISTINCT c.contact_id
              ,c.first_name AS student_name
              ,c.date_of_birth
              ,c.gender
              ,c.registration_no
              ,c.id_card_no
              ,c.year_of_joining
              ,s.title AS branch_name
        FROM contact c
        LEFT JOIN (course_contact cc) ON (c.contact_id = cc.contact_id)
        LEFT JOIN (site s) ON (c.site_id = s.site_id)
        WHERE c.status = 'Active'
          AND cc.year_of_enrollment = {$current_year}
          {$sqlAppend}
        ORDER BY c.site_id ASC, c.registration_no ASC
        ";
        
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;          
           
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['branch_name']);

            if ($course_id) {
                $courseRec = $fn->getRecordRowById('course', 'course_id', $course_id);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $courseRec['title']);
            }
    
            if ($batch_id) {
                $batchRec = $fn->getRecordRowById('batch', 'batch_id', $batch_id);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $batchRec['title']);
            }

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['student_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['registration_no']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['id_card_no']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['gender']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['year_of_joining']);
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   

    /**
     *
     */
    function getSqlForCount() {
        $db = Zend_Registry::get('db');
        
        $serial_no = 0;
        foreach($this->dataArray as $row){           
            $serial_no += 1;
        }
        
        return $serial_no;
    }
}