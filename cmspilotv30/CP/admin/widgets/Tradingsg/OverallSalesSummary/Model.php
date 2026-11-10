<?
class CP_Admin_Widgets_Tradingsg_OverallSalesSummary_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT DISTINCT o.company_id
        ,c.company_name
        FROM `order` o
        LEFT JOIN (company c) ON (o.company_id = c.company_id)
        LEFT JOIN (quote q) ON (q.quote_id = o.quote_id)
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
            $start_date = date('Y-m-d',mktime (0,0,0,date("m")-1,date("d"), date("Y")));
            $searchVar->sqlSearchVar[] = "o.order_date BETWEEN '{$start_date}' AND '{$current_date}'";
        }

        if ($company_id != '') {
            $searchVar->sqlSearchVar[] = "o.company_id = '{$company_id}'";
        }

        $searchVar->sqlSearchVar[] = "q.status !='Cancelled'";

        //$searchVar->sqlSearchVar[] = "o.quote_id != ''";
        //$searchVar->groupBy   = 'c.company_name';
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_summaryPurchaseSalesReport');

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

        $rows = '';

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Overall_Sales_Summary__" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Client Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Sales');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Discount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Purchase  (without GST)');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'GST');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Profit');
        
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
            $start_date = date('Y-m-d',mktime (0,0,0,date("m")-1,date("d"), date("Y")));
            $appendSql .= " AND o.order_date BETWEEN '{$start_date}' AND '{$current_date}'";
        }

        if ($company_id != '') {
            $appendSql .= " AND o.company_id = '{$company_id}'";
        }

        $SQL = "
        SELECT DISTINCT o.company_id
        ,c.company_name
        FROM `order` o
        LEFT JOIN (company c) ON (o.company_id = c.company_id)
        LEFT JOIN (quote q) ON (q.quote_id = o.quote_id)
        WHERE q.status !='Cancelled' 
        {$appendSql}
        ORDER BY o.order_date DESC
        ";
        $result = $db->sql_query($SQL);

        $overall_sales       = 0;
        $overall_purchase    = 0;
        $gstAmount           = 0;
        $totAlamount         = 0;
        $totalPurchaseAmount = 0;
        $overall_Discount    = 0;
        $profit              = 0;
        $overall_gst         = 0;
        $overall_profit      = 0;
        $gsttaxvalue = $cpCfg['amtForGSTCalc'] ;
        $appendSqlOther = '';
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $current_date = date('Y-m-d');

            if ($start_date != '' && $end_date == '') {
                $appendSqlOther = "AND o.order_date BETWEEN '{$start_date}' AND '{$current_date}'";
            } else if ($start_date == '' && $end_date != ''){
                $start_date = substr($end_date, 0, 8) . '01';
                $appendSqlOther = "AND o.order_date BETWEEN '{$start_date}' AND '{$end_date}'";
            } else if ($start_date != '' && $end_date != '') {
                $appendSqlOther = "AND o.order_date BETWEEN '{$start_date}' AND '{$end_date}'";
            } else {
                $start_date = date('Y-m-d',mktime (0,0,0,date("m")-1,date("d"), date("Y")));
                $appendSqlOther = "AND o.order_date BETWEEN '{$start_date}' AND '{$current_date}'";
            }

            $SQLDetails = "
            SELECT o.order_id
            ,o.quote_id
            FROM `order` o 
            LEFT JOIN (quote q)   ON (o.quote_id    = q.quote_id)
            WHERE o.company_id = {$row['company_id']}
            AND q.status != 'Cancelled'
            {$appendSqlOther}
            ";
            $resultDetails = $db->sql_query($SQLDetails);
            
            $totalSales    = 0;
            $totalPurchase = 0;
            $totalDiscount = 0;
            $totalDiscountSum = 0;
            $gstAmount     = 0;
            $gstAmountSum  = 0;
            while ($rowDetails = $db->sql_fetchrow($resultDetails)){

                $Sqlsales = "
                SELECT SUM(unit_price * qty) AS total_sales
                FROM order_item 
                WHERE order_id = {$rowDetails['order_id']}
                ";
                $resultsales = $db->sql_query($Sqlsales);
                $rowsales    = $db->sql_fetchrow($resultsales);

                $totalSales += $rowsales['total_sales'];

                $Sqlpurchase = "
                SELECT SUM(price * qty) AS total_purchase
                FROM po_product
                WHERE quote_id   = {$rowDetails['quote_id']}
                ";
                $resultpurchase = $db->sql_query($Sqlpurchase);
                $rowpurchase    = $db->sql_fetchrow($resultpurchase);

                $totalPurchase += $rowpurchase['total_purchase'];
                $gstAmount      = ($rowpurchase['total_purchase'] * $gsttaxvalue)/100;
                $gstAmountSum  += $gstAmount;

                $subSqlForPercentSum = "
                SELECT SUM(((qp.selling_price * qp.discount_percentage )/100)* qp.qty) as discount_sum
                FROM quote_product qp
                WHERE qp.quote_id = {$rowDetails['quote_id']}
                ";
                $resultSubSql = $db->sql_query($subSqlForPercentSum);
                $rowSql       = $db->sql_fetchrow($resultSubSql);
                if($rowSql['discount_sum'] > 0){
                    $totalDiscount = $rowSql['discount_sum'];
                    $totalDiscountSum += $totalDiscount;
                }
                else{
                    $totalDiscount = 0;
                }
            }

            $totAlamount = $totalPurchase + $gstAmountSum;
            $profit      = $totalSales - $totalDiscountSum - $totAlamount;

            $overall_sales     += $totalSales;
            $overall_purchase  += $totalPurchase;
            $overall_Discount  += $totalDiscountSum;
            $overall_profit    += $profit;
            $overall_gst       += $gstAmountSum;
            $profit             = number_format($profit,2, '.', '');
            $totalSales         = number_format($totalSales,2, '.', '');
            $totalDiscountSum   = number_format($totalDiscountSum,2, '.', '');
            $totalPurchase      = number_format($totalPurchase,2, '.', '');
            $gstAmountSum       = number_format($gstAmountSum,2, '.', '');

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalSales);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalDiscountSum);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalPurchase);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $gstAmountSum);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $profit);
        }
        $colc = 0;
        $rowc++;

        $format_overall_sales    = number_format($overall_sales, 2, '.', '');
        $format_overall_purchase = number_format($overall_purchase, 2, '.', '');
        $format_overall_Discount = number_format(($overall_Discount), 2, '.', '');
        $format_overall_gst      = number_format(($overall_gst), 2, '.', '');
        $format_overall_profit   = number_format(($overall_profit), 2, '.', '');

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Overall Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc,  $format_overall_sales);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc,  $format_overall_Discount);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc,  $format_overall_purchase);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc,  $format_overall_gst);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc,  $format_overall_profit);

        $actSheet->getStyle("A{$rowc}:F{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}