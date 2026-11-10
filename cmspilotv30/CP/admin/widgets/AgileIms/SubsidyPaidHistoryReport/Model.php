<?
class CP_Admin_Widgets_AgileIms_SubsidyPaidHistoryReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT sh.*
              ,o.cust_first_name AS name
              ,IF(o.contact_id > 0, 'Student', 'Company') AS enrollment_type
        FROM subsidy_paid_history sh 
        LEFT JOIN (`order` o) ON (sh.order_id = o.order_id)
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
        $status     = $fn->getReqParam('status');

        if ($status != '') {
            $searchVar->sqlSearchVar[] = "sh.status = '{$status}'";
        }

        if ($start_date != '' && $end_date == '') {
            $end_date = date('Y-m-d');
            $searchVar->sqlSearchVar[] = "sh.paid_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = date('Y') . '-01-01';
            $searchVar->sqlSearchVar[] = "sh.paid_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $searchVar->sqlSearchVar[] = "sh.paid_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else {
            $start_date = date('Y') . '-01-01';
            $end_date   = date('Y-m-d');
            $searchVar->sqlSearchVar[] = "sh.paid_date BETWEEN '{$start_date}' AND '{$end_date}'";
        }

        $searchVar->sortOrder = 'sh.paid_date DESC';
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'agileIms_subsidyPaidHistoryReport');

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

        $status     = $fn->getReqParam('status');
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "SubsidyPaidHistory_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Subsidy Code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Enrollment Type');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company/Student Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Status');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Paid Date');
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
        if ($status != '') {
            $sqlAppend .= " AND sh.status = '{$status}'";
        }

        if ($start_date != '' && $end_date == '') {
            $end_date = date('Y-m-d');
            $sqlAppend .= " AND sh.paid_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = date('Y') . '-01-01';
            $sqlAppend .= " AND sh.paid_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $sqlAppend .= " AND sh.paid_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else {
            $start_date = date('Y') . '-01-01';
            $end_date   = date('Y-m-d');
            $sqlAppend .= " AND sh.paid_date BETWEEN '{$start_date}' AND '{$end_date}'";
        }

        $SQL = "
        SELECT sh.*
              ,o.cust_first_name AS name
              ,IF(o.contact_id > 0, 'Student', 'Company') AS enrollment_type
        FROM subsidy_paid_history sh 
        LEFT JOIN (`order` o) ON (sh.order_id = o.order_id)
        WHERE sh.subsidy_history_id > 0
        {$sqlAppend}
        ORDER BY sh.paid_date DESC
        ";
        $result = $db->sql_query($SQL);
        $current_date = date("Ym") . '01';

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;
        
            $paid_date = $fn->getCPDate($row['paid_date'],"d-m-Y");
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['subsidy_code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['enrollment_type']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['status']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $paid_date);
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   
}