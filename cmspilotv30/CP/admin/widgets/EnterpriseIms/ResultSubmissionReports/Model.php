<?
class CP_Admin_Widgets_EnterpriseIms_ResultSubmissionReports_Model extends CP_Common_Lib_WidgetModelAbstract
{
    //Course Name |Total No of Students| Income | Paid | Due
    function getSQL(){
        /*
        $SQL = "
        SELECT DISTINCT(c.contact_id)
              ,c.registration_no
              ,CONCAT(c.first_name, ' ', c.last_name) AS trainee_name
              ,crse.title as course_title
              ,(SELECT count((date)) 
               FROM attendance at
               WHERE at.contact_id = c.contact_id
               GROUP BY at.contact_id) as total_attendance_days 
              ,(SELECT count((date)) 
               FROM attendance at
               WHERE at.contact_id = c.contact_id 
               AND at.status = 'Present' 
               GROUP BY at.contact_id) as total_present_days 
               ,b.batch_id
        FROM contact c
        JOIN attendance att1 ON (c.contact_id = att1.contact_id)
        JOIN batch b ON (att1.batch_id = b.batch_id)
        JOIN course crse ON (b.course_id = crse.course_id)
        ";
        */
        $SQL = "
        SELECT c.*
              ,co.reg_number
              ,co.title
              ,CONCAT(c.first_name, ' ', c.last_name) AS trainee_name
        FROM contact c
        JOIN company co ON (c.company_id = co.company_id)
        ";
        return $SQL;
    }
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $course_id  = $fn->getReqParam('course_id');
        $month      = $fn->getReqParam('month');

        //$searchVar->sqlSearchVar[] = "att1.batch_id = 3";
        $current_year = date('Y');
        $search_date = $current_year . '-' . $month . '-' . '%';

        /*if ($month != ''){
            $searchVar->sqlSearchVar[] = "att.date like '{$search_date}'";
        }

        if ($start_date != ''){
            $searchVar->sqlSearchVar[] = "att.date >= '{$start_date}'";
        }
        
        if ($end_date != ''){
            $searchVar->sqlSearchVar[] = "att.date <= '{$end_date}'";
        }*/
                
        $searchVar->groupBy = 'c.contact_id';
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enterpriseIms_resultSubmissionReports');

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

        $file_name = "Result_Submission_" . date("d-m-Y") . ".xls";

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
        SELECT c.*
              ,co.reg_number
              ,co.title
              ,CONCAT(c.first_name, ' ', c.last_name) AS trainee_name
        FROM contact c
        JOIN company co ON (c.company_id = co.company_id)
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