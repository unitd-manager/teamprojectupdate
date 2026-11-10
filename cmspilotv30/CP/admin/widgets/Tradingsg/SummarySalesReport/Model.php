<?
class CP_Admin_Widgets_Tradingsg_SummarySalesReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT o.order_id
              ,DATE_FORMAT(o.order_date, '%d-%m-%Y') AS formatted_order_date
              ,q.quote_code
              ,c.company_name
              ,(SELECT SUM(oi.unit_price * oi.qty)
                FROM order_item oi
                WHERE oi.order_id = o.order_id
                ) AS total_sales
        FROM `order` o
        LEFT JOIN (quote q)       ON (o.quote_id   = q.quote_id)
        LEFT JOIN (company c)     ON (o.company_id = c.company_id)
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
            $searchVar->sqlSearchVar[] = "o.company_id = '{$company_id}'";
        }

        $searchVar->sqlSearchVar[] = "q.status != 'Cancelled'";
        //$searchVar->sqlSearchVar[] = "o.quote_id != ''";
        
        $searchVar->sortOrder = "o.order_date DESC";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_summarySalesReport');

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
        $company_id = $fn->getReqParam('company_id');
        $year       = $fn->getReqParam('year');
        $month       = $fn->getReqParam('month');

        $rows = '';

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "SummarySales__" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Quote Code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Order Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Client Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Sales');
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
            $appendSql .= " AND o.company_id = '{$company_id}'";
        }

        $appendSql .= "AND q.status != 'Cancelled'";

        /*$SQL = "
        SELECT o.order_id
              ,DATE_FORMAT(o.order_date, '%d-%m-%Y') AS formatted_order_date
              ,q.quote_code
              ,c.company_name
              ,(SELECT SUM(oi.unit_price * oi.qty)
                FROM order_item oi
                WHERE oi.order_id = o.order_id
                ) AS total_sales
              ,(SELECT SUM(pop.price * pop.qty)
                FROM po_product pop
                WHERE pop.quote_id   = o.quote_id
                ) AS total_purchase
        FROM `order` o
        LEFT JOIN (quote q)       ON (o.quote_id   = q.quote_id)
        LEFT JOIN (company c)     ON (o.company_id = c.company_id)
        WHERE o.order_date != ''
            {$appendSql}
        ORDER BY o.order_date DESC
        ";*/
        
        $SQL = "
		SELECT o.order_id
              ,DATE_FORMAT(o.order_date, '%d-%m-%Y') AS formatted_order_date
              ,q.quote_code
              ,c.company_name
              ,(SELECT SUM(oi.unit_price * oi.qty)
                FROM order_item oi
                WHERE oi.order_id = o.order_id
                ) AS total_sales
        FROM `order` o
        LEFT JOIN (quote q)       ON (o.quote_id   = q.quote_id)
        LEFT JOIN (company c)     ON (o.company_id = c.company_id)
        WHERE o.order_date != ''
        	{$appendSql}
 		ORDER BY o.order_date DESC
        ";
        $result = $db->sql_query($SQL);

        $overall_sales    = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $overall_sales    += $row['total_sales'];

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['quote_code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['formatted_order_date']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['total_sales']);
        }

        $colc = 0;
        $rowc++;

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Overall Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $overall_sales);

        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}