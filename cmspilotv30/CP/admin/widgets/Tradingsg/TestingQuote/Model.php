<?
class CP_Admin_Widgets_Tradingsg_TestingQuote_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');

        /*$SQL = "
        SELECT o.order_date
              ,o.order_id
              ,o.record_type
              ,SUM(oi.unit_price * oi.qty) AS order_amount
        FROM order_item oi
        LEFT JOIN (`order` o) ON (oi.order_id = o.order_id)
        ";*/
	// **** THIS CONDITION HAS BEEN USED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\
		$appendSql = '' ;

		if ($cpCfg['cp.hasMultiUniqueSites']  == 1) {
			$appendSql = ",o.site_id" ;
		}

        $SQL = "
        SELECT r.*
              ,SUM(r.amount) as receipt_amount
              ,o.record_type
              ,SUM(srh.qty_return * srh.price) As sales_return_amount
              {$appendSql}
        FROM receipt r
        LEFT JOIN (`order` o) ON (r.order_id = o.order_id)
        LEFT JOIN (`sales_return_history` srh) ON (r.order_id = srh.order_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'o';

        $start_date 	= $fn->getReqParam('start_date');
        $end_date   	= $fn->getReqParam('end_date');
        $month      	= $fn->getReqParam('month');
        $year       	= $fn->getReqParam('year');
        $current_date 	= date('Y-m-d');
        $month        	= date('m');
        $year		  	= date('Y');
        $record_type    = $fn->getReqParam('record_type');
        $location_id    = $fn->getReqParam('location_id');
        if ($location_id != '') {
            $searchVar->sqlSearchVar[] = "o .site_id = {$location_id}";
        }

        if ($start_date != '' && $end_date == '') {
  	        $searchVar->sqlSearchVar[] = "r.date >= '{$start_date}' AND r.date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
  	        $searchVar->sqlSearchVar[] = "r.date >= '{$start_date}' AND r.date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
  	        $searchVar->sqlSearchVar[] = "r.date >= '{$start_date}' AND r.date <= '{$end_date}'";
        } else {
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $searchVar->sqlSearchVar[] = "r.date >= '{$current_date}' AND r.date <= '{$current_date}'";
        }

        if ($record_type == 'Quote') {
            $searchVar->sqlSearchVar[] = "o.record_type = 'Quote'";
        } else {
            $searchVar->sqlSearchVar[] = "o.record_type = 'POS'";
        }

        $searchVar->sqlSearchVar[] = "r.receipt_status = 'Paid'";

        if($cpCfg['cp.excludeStock'] == 1){
            $searchVar->sqlSearchVar[] = "o.link_stock = 1";
        }

        $searchVar->groupBy = "r.date";
        $searchVar->sortOrder = 'r.date DESC';

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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_dailyCollectionReport');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

    /**
     *
     */
    function getSqlForCount() {
        $db = Zend_Registry::get('db');

        $serial_no   = 0;
        $grand_total = 0;
        $order_amount = 0;

        foreach($this->dataArray as $row){
            $serial_no += 1;
            $order_amount = $row['receipt_amount'] - $row['sales_return_amount'];
            $grand_total += $order_amount;
        }

        $row = array(
                    'grand_total' => $grand_total
                    );

        return $row;
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

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "DailyCollectionReport_" . date("d-m-Y") . ".xls";

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
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $month          = $fn->getReqParam('month');
        $year           = $fn->getReqParam('year');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');
        $record_type    = $fn->getReqParam('record_type');
        $location_id    = $fn->getReqParam('location_id');
        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        if($cpCfg['cp.hasMultiUniqueSites']){
            $location_id    = $fn->getReqParam('location_id');
            $sqlsite="SELECT * FROM site
            ";
            $resultsqlsite = $db->sql_query($sqlsite);
            if($location_id == ''){
                    while ($rowSite= $db->sql_fetchrow($resultsqlsite)) {
                        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowSite['title']);
                    }
                }
            else{
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Location');
                }
         }
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

        $discount_sum_percent = 0;
        $discount_sum_value = 0;

        if ($start_date != '' && $end_date == '') {
            $startDateAppendSql = "r.date >= '{$start_date}' AND r.date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $startDateAppendSql = "r.date >= '{$start_date}' AND r.date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $startDateAppendSql = "r.date >= '{$start_date}' AND r.date <= '{$end_date}'";
        } else {
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $startDateAppendSql = "r.date >= '{$current_date}' AND r.date <= '{$current_date}'";
        }

        $appendSql = '';
        if ($location_id != '') {
            $appendSql = "AND o.site_id = {$location_id}";
        }

        if ($record_type == 'Quote') {
            $recordType = "AND o.record_type = 'Quote'";
        } else {
            $recordType = "AND o.record_type = 'POS'";
        }

        $linkToStock = '' ;

        if($cpCfg['cp.excludeStock'] == 1){
            $linkToStock = "AND o.link_stock = 1";
        }

        $siteTitle = '' ;

        if ($cpCfg['cp.hasMultiUniqueSites']  == 1) {
            $siteTitle = ",o.site_id" ;
        }

        $SQL = "
        SELECT r.*
              ,SUM(r.amount) as receipt_amount
              ,o.record_type
               {$siteTitle}
              ,SUM(srh.qty_return * srh.price) As sales_return_amount
        FROM receipt r
        LEFT JOIN (`order` o) ON (r.order_id = o.order_id)
        LEFT JOIN (`sales_return_history` srh) ON (r.order_id = srh.order_id)
        WHERE
        {$startDateAppendSql}
        {$appendSql}
        {$recordType}
        AND r.receipt_status = 'Paid'
        {$linkToStock}
        GROUP BY r.date
        ORDER BY r.date DESC
        ";

        $result = $db->sql_query($SQL);

        $grand_total = 0 ;
        $grand_totalfrm = 0;

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;

            if ($cpCfg['cp.hasMultiUniqueSites']  == 1) {
                $siteRecSql ="
                SELECT s.title
                FROM site s
                WHERE s.site_id = {$row['site_id']}
                ";

                $resultSiteLocation = $db->sql_query($siteRecSql);
                $rowSite            = $db->sql_fetchrow($resultSiteLocation);
             }

            //TO CHECK IF THE SUM OF DISCOUNT TYPE(%) HAS VALUE OR NOT AND ASSIGN THE QUERY IF IT HAS VALUE ELSE ASSIGN ZERO
            $subSqlForPercentSum = "
            SELECT SUM(((oi.unit_price * oi.discount_percentage )/100)* oi.qty) as discount_sum_percent
            FROM order_item oi
            WHERE oi.order_id = {$row['order_id']}
              AND oi.discount_type = '%'
            ";
            $resultSubSql = $db->sql_query($subSqlForPercentSum);
            $rowSql       = $db->sql_fetchrow($resultSubSql);
            if($rowSql['discount_sum_percent'] > 0){
                $discount_sum_percent = $rowSql['discount_sum_percent'];
            }

            //TO CHECK IF THE SUM OF DISCOUNT TYPE(VALUE) HAS VALUE OR NOT AND ASSIGN THE QUERY IF IT HAS VALUE ELSE ASSIGN ZERO
            $subSqlForValueSum ="
            SELECT SUM(oi.discount_percentage  * oi.qty) as discount_sum_value
            FROM order_item oi
            WHERE oi.order_id = {$row['order_id']}
              AND oi.discount_type = 'Value'
            ";
            $resultSubSql1 = $db->sql_query($subSqlForValueSum);
            $rowSql1       = $db->sql_fetchrow($resultSubSql1);

            if($rowSql1['discount_sum_value'] > 0){
                $discount_sum_value = $rowSql1['discount_sum_value'];
            }

            $discount_percentage_amount_sum = $discount_sum_value + $discount_sum_percent;
            if($row['record_type'] == 'POS'){
                $order_amount = $row['amount'] - $discount_percentage_amount_sum;
            } else {
                $order_amount = $row['amount'];
            }


            $amount = $row['receipt_amount'] - $row['sales_return_amount'];
            $grand_total += $amount;

            $grand_totalfrm = number_format(round($grand_total) ,2);


            $rowc++;
            $colc=0 ;

            if($row['date']){

                $amount = number_format(round($amount), 2);
                $dateFormatted = $dateUtil->formatDate($row['date'], 'DD/MM/YYYY');

                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $dateFormatted);
                if($cpCfg['cp.hasMultiUniqueSites'] == 1){
                    if($location_id ==''){
                        $amountList = '';
                        $sqlsite="SELECT * FROM site
                            ";
                            $resultsqlsite = $db->sql_query($sqlsite);
                        while ($rowSite= $db->sql_fetchrow($resultsqlsite)) {
                        $SQLAmount = "
                                  SELECT r.*
                                          ,SUM(r.amount) as receipt_amount
                                          ,o.record_type
                                          ,o.site_id
                                          ,SUM(srh.qty_return * srh.price) As sales_return_amount
                                    FROM receipt r
                                    LEFT JOIN (`order` o) ON (r.order_id = o.order_id)
                                    LEFT JOIN (`sales_return_history` srh) ON (r.order_id = srh.order_id)
                                    WHERE o .site_id = {$rowSite['site_id']}
                                 AND r.date ='{$row['date']}'
                                 {$recordType}
                                 AND r.receipt_status = 'Paid'
                                 GROUP BY r.date
                                 ORDER BY r.date DESC
                                 ";

                                $resultAmount = $db->sql_query($SQLAmount);
                                $rowAmount = $db->sql_fetchrow($resultAmount);
                                $amountList = $rowAmount['receipt_amount'] - $rowAmount['sales_return_amount'];
                                $amountList = number_format(round($amountList),2);
                                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $amountList);
                        }
                    }
                    else{
                        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowSite['title']);
                    }
                }
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $amount);

            }


        }

            $colc=0 ;
            $rowc++;

            if($cpCfg['cp.hasMultiUniqueSites'] == 1){
                if($location_id ==''){
                    $siteSpace ="
                        SELECT title
                        FROM site
                        ";
                    $result = $db->sql_query($siteSpace);
                    while ($rowSite= $db->sql_fetchrow($result)) {
                        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                    }
                }else{
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                }
            }

            $rowc++;
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Grand Total');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $grand_totalfrm);

            if($cpCfg['cp.hasMultiUniqueSites'] == 1){
                if($location_id ==''){
                    $actSheet->getStyle("A{$rowc}:F{$rowc}")->applyFromArray($headStyle);
                }else{
                    $actSheet->getStyle("A{$rowc}:C{$rowc}")->applyFromArray($headStyle);
                }
            }else {
                $actSheet->getStyle("A{$rowc}:B{$rowc}")->applyFromArray($headStyle);
            }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}