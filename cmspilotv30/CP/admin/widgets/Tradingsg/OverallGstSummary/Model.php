<?
class CP_Admin_Widgets_Tradingsg_OverallGstSummary_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        
        $SQL = "
        SELECT SUM(pop.price * pop.qty) AS total_purchase
              ,DATE_FORMAT(q.quote_date, '%d-%m-%Y') AS formatted_quote_date
              ,c.company_name
              ,q.company_id
              ,q.quote_code
              ,q.quote_id
        FROM po_product pop
        LEFT JOIN (quote q)   ON (pop.quote_id    = q.quote_id)
        LEFT JOIN (company c) ON (pop.supplier_id = c.company_id)
        LEFT JOIN (`order` o) ON (q.quote_id      = o.quote_id)
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
        $year       = $fn->getReqParam('year');
        $month      = $fn->getReqParam('month');
        $company_id = $fn->getReqParam('company_id');

        $current_date = date('Y-m-d');

        if ($start_date != '' && $end_date == '') {
            $searchVar->sqlSearchVar[] = "o.order_date BETWEEN '{$start_date}' AND '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = substr($end_date, 0, 8) . '01';
            $searchVar->sqlSearchVar[] = "o.order_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $searchVar->sqlSearchVar[] = "o.order_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else {
            if ($year == '') {
                $year = date('Y');
            }
            
            $start_date = $year . '-' . $month . '-01';
            $end_date   = $year . '-' . $month . '-31';;
            
            $searchVar->sqlSearchVar[] = "o.order_date BETWEEN '{$start_date}' AND '{$end_date}'";
        }

        if ($company_id != '') {
            $searchVar->sqlSearchVar[] = "pop.supplier_id = '{$company_id}'";
        }

        $searchVar->sqlSearchVar[] = "c.gst_applied = 1";
        $searchVar->sqlSearchVar[] = "q.status != 'Cancelled'";

        $searchVar->groupBy   = 'pop.supplier_id';
        $searchVar->sortOrder = "q.quote_date DESC";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_summaryByClient');

        $this->dataArray = $dataArray;
        return $this->dataArray;
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

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $year       = $fn->getReqParam('year');
        $month      = $fn->getReqParam('month');
        $company_id = $fn->getReqParam('company_id');

        $rows = '';

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        if($start_date != '' && $end_date != ''){
            $file_name = "Overall_GST_Summary_" .$start_date. "_and_" .$end_date. ".xls";
        }else{
            $file_name = "Overall_GST_Summary_" . date("d-m-Y") . ".xls";
        }

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
        $appendSql = '';
        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Supplier Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Amount (before GST)');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'GST 7 %');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Amount(after GST)');
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

        $appendSql = '';
        $current_date = date('Y-m-d');
        if ($start_date != '' && $end_date == '') {
            $appendSql .= " AND o.order_date BETWEEN '{$start_date}' AND '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = substr($end_date, 0, 8) . '01';
            $appendSql .= " AND o.order_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $appendSql .= " AND o.order_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else {
            if ($year == '') {
                $year = date('Y');
            }
            
            $start_date = $year . '-' . $month . '-01';
            $end_date   = $year . '-' . $month . '-31';;
            
            $appendSql .= " AND o.order_date BETWEEN '{$start_date}' AND '{$end_date}'";
        }

        if ($company_id != '') {
            $appendSql .= " AND pop.supplier_id = '{$company_id}'";
        }

        $SQL = "
        SELECT SUM(pop.price * pop.qty) AS total_purchase
              ,DATE_FORMAT(q.quote_date, '%d-%m-%Y') AS formatted_quote_date
              ,c.company_name
              ,q.quote_code
        FROM po_product pop
        LEFT JOIN (quote q)   ON (pop.quote_id    = q.quote_id)
        LEFT JOIN (company c) ON (pop.supplier_id = c.company_id)
        LEFT JOIN (`order` o) ON (q.quote_id      = o.quote_id)
        WHERE q.status != 'Cancelled'
        AND c.gst_applied = 1
        {$appendSql}
        GROUP BY pop.supplier_id
        ORDER BY q.quote_date DESC
        ";
        $result = $db->sql_query($SQL);

        $overall_purchase   = 0;
        $overall_before_gst = 0;
        $overall_after_gst  = 0;

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $overall_purchase += $row['total_purchase'];

            $before_gst_amt = ($row['total_purchase'] * 7) / 100;
            $overall_before_gst += $before_gst_amt;

            $after_gst_amt  = $row['total_purchase'] + $before_gst_amt;
            $overall_after_gst += $after_gst_amt;

            $before_gst     = number_format($before_gst_amt, 2, '.', '');
            $after_gst      = number_format($after_gst_amt, 2, '.', '');

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['total_purchase']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $before_gst);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $after_gst);
        }
        
        $colc = 0;
        $rowc++;

        $overall_purchase   = number_format($overall_purchase,2, '.', '');
        $overall_before_gst = number_format($overall_before_gst,2, '.', '');
        $overall_after_gst  = number_format($overall_after_gst,2, '.', '');

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Overall Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $overall_purchase);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $overall_before_gst);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $overall_after_gst);

        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}