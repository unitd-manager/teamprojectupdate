<?
class CP_Admin_Widgets_Project_TaskHoursByStaffReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        
        $SQL = "
        SELECT a.*
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
        FROM attendance a
        LEFT JOIN (staff s) ON (a.staff_id = s.staff_id) 
        ";
        return $SQL;

    }
    
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'o';

        
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $month      = $fn->getReqParam('month');
        $year       = $fn->getReqParam('year');
        $staff_id   = $fn->getReqParam('staff_id');
        $current_date = date('Y-m-d');
        $current_year = date('Y');
        $current_month = date('m');
        
        if ($start_date != '' && $end_date == '') {
            $searchVar->sqlSearchVar[] = "a.record_date BETWEEN '{$start_date}' AND '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $searchVar->sqlSearchVar[] = "a.record_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $searchVar->sqlSearchVar[] = "a.record_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else {
            $start_date = $current_year . '-' . $current_month . '-' . '01';
            $end_date = $current_year . '-' . $current_month . '-' . '31';
            $searchVar->sqlSearchVar[] = "a.record_date BETWEEN '{$start_date}' AND '{$end_date}'";
        }
            $searchVar->groupBy = "a.record_date";
            $searchVar->sortOrder = 'a.record_date DESC';
  
        if ($staff_id != '' ) {
            $searchVar->sqlSearchVar[] = "s.staff_id = {$staff_id}";
        }        

    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'project_taskHoursByStaffReport');

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

        $rows = '';

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "TaskHoursByStaffReport_" . date("d-m-Y") . ".xls";

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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Moinudeen');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Thamim');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Arif');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Ansari');
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
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $staff_id       = $fn->getReqParam('staff_id');
        $current_date   = date('Y-m-d');
        $current_year   = date('Y');
        $current_month  = date('m');

        if ($start_date != '' && $end_date == '') {
            $appendSql = "a.record_date BETWEEN '{$start_date}' AND '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $appendSql = "a.record_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $appendSql = "a.record_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else {
            $start_date = $current_year . '-' . $current_month . '-' . '01';
            $end_date = $current_year . '-' . $current_month . '-' . '31';
            $appendSql = "a.record_date BETWEEN '{$start_date}' AND '{$end_date}'";
        }
  
        if ($staff_id != '' ) {
            $appendSql = "AND s.staff_id = {$staff_id}";
        }     

        $SQL = "
        SELECT a.*
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
        FROM attendance a
        LEFT JOIN (staff s) ON (a.staff_id = s.staff_id)
         WHERE
        {$appendSql}
        GROUP BY a.record_date
        ";
        
        $result = $db->sql_query($SQL);

        $totalmoinhrs = 0;
        $totalthamimhrs = 0;
        $totalarifhrs = 0;
        $totalansarihrs = 0;
        $serial_no = 0;
        while ($row = $db->sql_fetchrow($result)) {

        $record_date = $fn->getCPDate($row['record_date'],"d-m-Y");           
        $moinhrs = $this->view->gethours($row['record_date'], 16);
        $thamimhrs = $this->view->gethours($row['record_date'], 32);
        $arifhrs = $this->view->gethours($row['record_date'], 11);
        $ansarihrs = $this->view->gethours($row['record_date'], 22);
        $totalmoinhrs += $moinhrs;
        $totalthamimhrs += $thamimhrs;
        $totalarifhrs += $arifhrs;
        $totalansarihrs += $ansarihrs;
        $totalmoinhrs = number_format($totalmoinhrs, 2);
        $totalthamimhrs = number_format($totalthamimhrs, 2);
        $totalarifhrs = number_format($totalarifhrs, 2);
        $totalansarihrs = number_format($totalansarihrs, 2);
        $serial_no += 1;
        $colc = 0;
        $rowc++;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $serial_no);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $record_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $moinhrs);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $thamimhrs);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $arifhrs);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $ansarihrs); 


        }

        $colc = 0;
        $rowc++;

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Hours :');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalmoinhrs);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalthamimhrs);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalarifhrs);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalansarihrs);

        $actSheet->getStyle("A{$rowc}:F{$rowc}")->applyFromArray($headStyle);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   
  }