<?
class CP_Admin_Widgets_Project_OfficeTimeReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    // Date | Name | Leave Taken | Time In | Time Out
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
        $searchVar->mainTableAlias = 'a';
        
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
  
        if ($staff_id != '' ) {
            $searchVar->sqlSearchVar[] = "s.staff_id = {$staff_id}";
        }        
       
        $searchVar->sortOrder = 'a.attendance_id DESC';


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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'project_officeTimeReport');

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

        $file_name = "OfficeTimeReport_" . date("d-m-Y") . ".xls";

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
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $staff_id       = $fn->getReqParam('staff_id');
        $current_date   = date('Y-m-d');
        $current_year   = date('Y');
        $current_month  = date('m');

        if ($start_date != '' && $end_date == '') {
            $appendSql = "AND a.record_date BETWEEN '{$start_date}' AND '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $appendSql = "AND a.record_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $appendSql = "AND a.record_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else {
            $start_date = $current_year . '-' . $current_month . '-' . '01';
            $end_date = $current_year . '-' . $current_month . '-' . '31';
            $appendSql = "AND a.record_date BETWEEN '{$start_date}' AND '{$end_date}'";
        }

        $SQL = "
        SELECT a.*
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
        FROM attendance a
        LEFT JOIN (staff s) ON (a.staff_id = s.staff_id)
        WHERE s.staff_id = {$staff_id}
        {$appendSql}
        ";
        $result = $db->sql_query($SQL);
        
        $serial_no = 0;
        $total_amount = 0;
        
        while ($row = $db->sql_fetchrow($result)) {

            $time_in = strtotime($row['time_in']);
            $leave_time = strtotime($row['leave_time']);
            
            $hour = date('H', $time_in);
            $mins = date('i', $time_in);

            $amount = 0;
            if ($row['on_leave'] == 0) {
                if($row['staff_id'] == 11) {
                    if ($hour == '10') {
                        if ($mins <= '15') {
                            $amount = 0;
                        } else {
                            $amount = 20;
                        }
                    } else if ($hour >= '11'){
                        $amount = 40;
                    }
                } else {
                    /*if ($hour == '10') {
                        if ($mins <= '15') {
                            $amount = 0;
                        } else {
                            $amount = 50;
                        }
                    } else if ($hour >= '11'){
                        $amount = 100;
                    }*/
                    if ($hour == '10') {
                        if ($mins > '30') {
                            $amount = 50;
                        }
                        
                        if ($mins > '45'){
                            $amount = 100;
                        }

                    } 
                    else if ($hour >= '11'){
                        $amount = 100;
                    }
                }
            }

            $total_amount += $amount;
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
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $amount);
        }
        
        $colc = 0;
        $rowc++;
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_amount);        

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   
}