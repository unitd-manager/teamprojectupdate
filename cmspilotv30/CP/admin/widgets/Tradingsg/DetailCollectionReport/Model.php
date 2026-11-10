<?
class CP_Admin_Widgets_Tradingsg_DetailCollectionReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $sumTxt = '';
        if ($cpCfg['m.ecommerce.order.hasDiscount']){
            $sumTxt = "SUM(oi.unit_price * oi.qty) + o.shipping_charge - o.discount";
        } else {
            $sumTxt = "SUM(oi.unit_price * oi.qty) + o.shipping_charge";
        }

	// **** THIS CONDITION HAS BEEN USED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\
		$appendSql = '' ;

		if ($cpCfg['cp.hasMultiUniqueSites']  == 1) {
			$appendSql = ",o.site_id" ;
		}

        $SQL = "
        SELECT o.order_date
        	  ,o.order_status
              ,o.order_id
              ,o.record_type
              ,c.company_name
              {$appendSql}
              ,(SELECT ($sumTxt)
               FROM order_item oi
               WHERE oi.order_id = o.order_id
               ) AS order_amount
               ,(SELECT SUM(srh.qty_return * srh.price) FROM sales_return_history srh
               WHERE o.order_id = srh.order_id
               AND srh.status IS NULL
               ) as sales_return_amount
        FROM `order` o
        LEFT JOIN company c ON (c.company_id = o.company_id)
        ";

        /*$SQL = "
        SELECT o.order_date
              ,o.order_status
              ,o.order_id
              ,o.record_type
              ,c.company_name
              {$appendSql}
              ,(SELECT SUM(r.amount)
               FROM receipt r
               WHERE r.order_id = o.order_id
                 AND r.receipt_status = 'Paid'
               ) AS order_amount
               ,(SELECT SUM(srh.qty_return * srh.price) FROM sales_return_history srh
               WHERE o.order_id = srh.order_id
               AND srh.status IS NULL
               ) as sales_return_amount
        FROM `order` o
        LEFT JOIN company c ON (c.company_id = o.company_id)
        ";*/

        return $SQL;
    }

    /**
        LEFT JOIN (`company` c) ON (c.company_id = o.order_id)
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'o';

        $month      	= $fn->getReqParam('month');
        $year       	= $fn->getReqParam('year');
        $order_status   = $fn->getReqParam('order_status');
        $current_date 	= date('Y-m-d');
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $record_type    = $fn->getReqParam('record_type');
        $location_id    = $fn->getReqParam('location_id');
        if ($location_id != '') {
            $searchVar->sqlSearchVar[] = "o .site_id = {$location_id}";
        }

        if ($month != ''){
            if ($year != '') {
                $startMonth = $year . '-' . $month . '-' . '01';
                $endMonth   = $year . '-' . $month . '-' . '31';
            } else {
                $year = date('Y');
                $startMonth = $year . '-' . $month . '-' . '01';
                $endMonth   = $year . '-' . $month . '-' . '31';
            }
            $searchVar->sqlSearchVar[] = "o.order_date BETWEEN '{$startMonth}' AND '{$endMonth}'";
        }

        if ($year != ''){
            $startYear = $year .'-01-01';
            $endYear   = $year .'-12-31';

            $searchVar->sqlSearchVar[] = "o.order_date BETWEEN '{$startYear}' AND '{$endYear}'";
        }


        if ($start_date != '' && $end_date == '') {
            $searchVar->sqlSearchVar[] = "o.order_date >= '{$start_date}' AND o.order_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
	        $searchVar->sqlSearchVar[] = "o.order_date >= '{$start_date}' AND o.order_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
  	        $searchVar->sqlSearchVar[] = "o.order_date >= '{$start_date}' AND o.order_date <= '{$end_date}'";
        } else {
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $searchVar->sqlSearchVar[] = "o.order_date = '{$current_date}'";
        }

        if ($record_type == 'Quote') {
            $searchVar->sqlSearchVar[] = "o.record_type = 'Quote'";
        } else {
            $searchVar->sqlSearchVar[] = "o.record_type = 'POS'";
        }

		if ($order_status != '') {
			$searchVar->sqlSearchVar[] = "o.order_status = '{$order_status}'";
		}

        if($cpCfg['cp.excludeStock'] == 1){
            $searchVar->sqlSearchVar[] = "o.link_stock = 1";
        }

        $searchVar->sortOrder = 'o.order_date DESC';

        $searchVar->sortOrder = 'o.creation_date DESC';

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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_detailCollectionReport');

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

        foreach($this->dataArray as $row){

            $discount_sum_percent = 0;
            $discount_sum_value = 0;

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
                $order_amount = $row['order_amount'] - $discount_percentage_amount_sum;
            } else {
                $order_amount = $row['order_amount'];
            }

            if($row['sales_return_amount'] !=''){
                $order_amount = $order_amount - $row['sales_return_amount'];
            }

            $serial_no += 1;
            $grand_total += $order_amount;
        }

        $row = array(
                    'grand_total' => $grand_total
                    );

        return $row;
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

        $file_name = "DetailCollectionReport_" . date("d-m-Y") . ".xls";

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
        $order_status   = $fn->getReqParam('order_status');
        $location_id    = $fn->getReqParam('location_id');
        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Order Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Order No');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Order Status');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company Name');
        if($cpCfg['cp.hasMultiUniqueSites'] == 1){
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Location');
        }

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

        $appendSqlDate = '';

        if ($start_date != '' && $end_date == '') {
            $appendSqlDate = "o.order_date >= '{$start_date}' AND o.order_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $appendSqlDate = "o.order_date >= '{$start_date}' AND o.order_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $appendSqlDate = "o.order_date >= '{$start_date}' AND o.order_date <= '{$end_date}'";
        } else {
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $appendSqlDate = "o.order_date = '{$current_date}'";
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

        $sumTxt = '';
        if ($cpCfg['m.ecommerce.order.hasDiscount']){
            $sumTxt = "SUM(oi.unit_price * oi.qty) + o.shipping_charge - o.discount";
        } else {
            $sumTxt = "SUM(oi.unit_price * oi.qty) + o.shipping_charge";
        }

        $statusSql = '';
        if ($order_status != '') {
            $statusSql = "AND o.order_status = '{$order_status}'";
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
        SELECT o.order_date
              ,o.order_status
              ,o.order_id
              ,o.record_type
              ,c.company_name
              {$siteTitle}
              ,(SELECT ($sumTxt)
               FROM order_item oi
               WHERE oi.order_id = o.order_id
               ) AS order_amount
                ,(SELECT SUM(srh.qty_return * srh.price) FROM sales_return_history srh
               WHERE o.order_id = srh.order_id
               AND srh.status IS NULL
               ) as sales_return_amount
        FROM `order` o
        LEFT JOIN company c ON (c.company_id = o.company_id)
        WHERE {$appendSqlDate}
              {$appendSql}
              {$recordType}
              {$statusSql}
              {$linkToStock}
        ORDER BY o.order_date DESC,o.creation_date DESC
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

            $discount_sum_percent = 0;
            $discount_sum_value = 0;

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
                $order_amount = $row['order_amount'] - $discount_percentage_amount_sum;
            } else {
                $order_amount = $row['order_amount'];
            }
            if($row['sales_return_amount'] != ''){
                $order_amount = $order_amount - $row['sales_return_amount'];
            }

            $grand_total += $order_amount;
            $grand_totalfrm = number_format(round($grand_total) ,2);
            $order_amount = number_format($order_amount ,2);


            $rowc++;
            $colc=0 ;
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['order_date']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['order_id']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $order_amount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['order_status']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
            if($cpCfg['cp.hasMultiUniqueSites'] == 1){
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowSite['title']);
            }
        }
            $colc=0 ;
            $rowc++;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
            $rowc++;
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Grand Total');
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $grand_totalfrm);

            if($cpCfg['cp.hasMultiUniqueSites'] == 1){
                $actSheet->getStyle("A{$rowc}:F{$rowc}")->applyFromArray($headStyle);
            }else {
                $actSheet->getStyle("A{$rowc}:E{$rowc}")->applyFromArray($headStyle);
            }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}