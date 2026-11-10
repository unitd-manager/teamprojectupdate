<?
class CP_Admin_Widgets_Tradingsg_SummaryPurchaseSalesReport_Model extends CP_Common_Lib_WidgetModelAbstract
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
              ,(SELECT SUM(pop.price * pop.qty)
                FROM po_product pop
                WHERE pop.quote_id   = o.quote_id
                ) AS total_purchase
              ,o.quote_id
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

        $file_name = "SummaryPurchaseSales__" . date("d-m-Y") . ".xls";

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

        $appendSql .= "AND q.status != 'Cancelled'";

        $SQL = "
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
              ,o.quote_id
        FROM `order` o
        LEFT JOIN (quote q)       ON (o.quote_id   = q.quote_id)
        LEFT JOIN (company c)     ON (o.company_id = c.company_id)
        WHERE o.order_date != ''
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
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            /*$balance_amount = $row['total_sales'] - $row['total_purchase'];

            $overall_sales    += $row['total_sales'];
            $overall_purchase += $row['total_purchase'];*/

            $gstAmount = ($row['total_purchase'] * $gsttaxvalue)/100;
            $totAlamount = $row['total_purchase'] + $gstAmount;

            $total_sales    = number_format($row['total_sales'], 2, '.', '');
            $total_purchase = number_format($totAlamount, 2, '.', '');

            $balance_amount = number_format(($row['total_sales'] - $totAlamount), 2, '.', '');

            $overall_gst        +=  $gstAmount;
            $overall_sales      += $row['total_sales'];
            $overall_purchase   += $row['total_purchase'];
            $totAlamount_format = round($totAlamount,2);

            //TO CHECK IF THE SUM OF DISCOUNT TYPE(%) HAS VALUE OR NOT AND ASSIGN THE QUERY IF IT HAS VALUE ELSE ASSIGN ZERO
            $subSqlForPercentSum = "
            SELECT SUM(((qp.selling_price * qp.discount_percentage )/100)* qp.qty) as discount_sum
            FROM quote_product qp
            WHERE qp.quote_id = {$row['quote_id']}
            ";
            $resultSubSql = $db->sql_query($subSqlForPercentSum);
            $rowSql       = $db->sql_fetchrow($resultSubSql);
            if($rowSql['discount_sum'] > 0){
                $totalDiscount = $rowSql['discount_sum'];
            }
            else{
                $totalDiscount = 0;
            }

            $overall_Discount += $totalDiscount;

            $totalPurchaseAmount = number_format($row['total_purchase'],2, '.', '');

            $profit = $row['total_sales'] - $totalDiscount - $totAlamount;

            $overall_profit += $profit;

            $totalDiscount = number_format($totalDiscount,2, '.', '');
            $profit        = number_format($profit,2, '.', '');
            $gstAmount     = number_format($gstAmount,2, '.', '');

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['quote_code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['formatted_order_date']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_sales);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalDiscount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $totalPurchaseAmount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $gstAmount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $profit);

            $colc = 0;
            $rowc++;

            $count = 1;
            $sqlPop = "
            SELECT SUM(pop.price * pop.qty) AS purchase_amount
                   ,c.company_name
            FROM po_product pop
            LEFT JOIN (company c) ON (pop.supplier_id = c.company_id)
            LEFT JOIN (quote q)   ON (pop.quote_id    = q.quote_id)
            WHERE pop.quote_id = {$row['quote_id']}
            GROUP BY pop.supplier_id
            ORDER BY c.company_name ASC
            ";
            $resultPop = $db->sql_query($sqlPop);
            while ($rowPop = $db->sql_fetchrow($resultPop)) {
                $gstAmount_list = ($rowPop['purchase_amount'] * $gsttaxvalue)/100;
                $totAlamount_list = $rowPop['purchase_amount'] + $gstAmount_list;

                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $count);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowPop['company_name']);
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, round($totAlamount_list,2));
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                $count++;

		        $colc = 0;
		        $rowc++;
            }
        }

        $format_overall_sales    = number_format($overall_sales, 2, '.', '');
        $format_overall_purchase = number_format($overall_purchase, 2, '.', '');
        $format_overall_balance  = number_format(($overall_sales - $overall_purchase), 2, '.', '');
        $format_overall_Discount = number_format(($overall_Discount), 2, '.', '');
        $format_overall_gst      = number_format(($overall_gst), 2, '.', '');
        $format_overall_profit   = number_format(($overall_profit), 2, '.', '');

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Overall Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc,  $format_overall_sales);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc,  $format_overall_Discount);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc,  $format_overall_purchase);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc,  $format_overall_gst);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc,  $format_overall_profit);

        $actSheet->getStyle("A{$rowc}:H{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}