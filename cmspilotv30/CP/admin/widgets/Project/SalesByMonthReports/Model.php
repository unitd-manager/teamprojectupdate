<?
class CP_Admin_Widgets_Project_SalesByMonthReports_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT DATE_FORMAT(p.start_date, '%b %Y') AS project_month
              ,SUM(p.project_value_ref) AS project_amount_monthly
        FROM `project` p                                            
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
        
        if ($start_date == '') {
            $start_date = date('Y-m-d', mktime (0,0,0,date("m")-6, date("d"), date("Y")));
        }
        
        if ($end_date == '') {
            $end_date = date('Y-m-d');
        } 

        $searchVar->sqlSearchVar[] = "p.status != 'Cancelled'";
        $searchVar->sqlSearchVar[] = "p.status != 'On Hold'";

        $searchVar->sqlSearchVar[] = "p.start_date BETWEEN '{$start_date}' AND '{$end_date}'";
        $searchVar->groupBy = "DATE_FORMAT(p.start_date, '%Y-%m')";
    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'project_salesByMonthReports');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }

    /**
     *
     */
    function getExportToExcel($dataArray = ''){
        $dbUtil = Zend_Registry::get('dbUtil');
        
        if (!is_array($dataArray)){
            $dataArray = $this->getDataArray();
        }

        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');         
        $fa = array(
              'project_month'           => $phpExcel->getFldObj('Month')
             ,'project_amount_monthly'  => $phpExcel->getFldObj('Amount')
        );

        $file_name = "SalesByMonth_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }

    /**
     *
     */
    function getExportToExcel1(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        
        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Daily-Collection_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Month');
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
        
        $last12Month = date('Y-m-d',mktime (0,0,0,date("m")-12,1, date("Y")));
        $today       = date('Y-m-d');

        $SQL = "
        SELECT DATE_FORMAT(o.order_date, '%b %Y') AS order_month
                      ,(SUM(oi.unit_price*oi.qty)) AS order_amount_monthly
                FROM order_item oi
                LEFT JOIN (`order` o) ON (oi.order_id   = o.order_id)
                WHERE (o.order_date BETWEEN '{$last12Month}' AND '{$today}')
         GROUP BY DATE_FORMAT(o.order_date, '%Y-%m')
        ";                 
        $result = $db->sql_query($SQL);
        $payment_total = '';

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;
        
            $payment_total += $row['order_amount_monthly'];
            
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['order_month']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['order_amount_monthly']);
        }

        $colc = 0;
        $rowc++;

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $payment_total);

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   

    /**
     *
     */
    function getOutstandingAmountForProject($project_id) {
        $db = Zend_Registry::get('db');

        $sql = "
        SELECT SUM(invoice_amount_ref) AS total_amt_paid
        FROM invoice
        WHERE project_id = '{$project_id}'
          AND status = 'Paid'
        ";
        $result = $db->sql_query($sql);
        $row = $db->sql_fetchrow($result);        

        return $row['total_amt_paid'];
    }

    /**
     *
     */
    function getPaidAmountForMonth($project_month) {
        $db = Zend_Registry::get('db');

        $sqlProj = "
        SELECT DISTINCT project_id
        FROM project
        WHERE status != 'Cancelled' 
          AND status != 'On Hold'
          AND DATE_FORMAT(start_date, '%b %Y') = '{$project_month}'
        ORDER BY project_id ASC
        ";
        $resultProj = $db->sql_query($sqlProj);

        $total_amt_due = 0;
        while ($rowProj = $db->sql_fetchrow($resultProj)) {
            $total_amt_due += $this->getOutstandingAmountForProject($rowProj['project_id']);
        }

        return $total_amt_due;
    }
}