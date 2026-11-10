<?
class CP_Admin_Widgets_Tradingsg_DetailVatPercentForInvoice_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');

	// **** THIS CONDITION HAS BEEN USED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\
		$appendSql = '' ;

		if ($cpCfg['cp.hasMultiUniqueSites']  == 1) {
			$appendSql = ",o.site_id" ;
		}

        $SQL = "
        SELECT i.*
              ,date_format(i.invoice_date, '%Y-%m') AS inv_year_mon
              ,date_format(i.invoice_date, '%Y') AS inv_year
              ,o.record_type
              ,o.order_id
              ,c.company_name
              ,c.tin_no
              {$appendSql}
              ,(SELECT SUM(srh.qty_return * srh.price) FROM sales_return_history srh
                WHERE i.invoice_id = srh.invoice_id
                  AND srh.status IS NULL) as sales_return_amount
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        LEFT JOIN (`company` c) ON (c.company_id = o.company_id)
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
            $searchVar->sqlSearchVar[] = "o.site_id = {$location_id}";
        }
        if ($record_type == 'POS') {
            $searchVar->sqlSearchVar[] = "o.record_type = 'POS'";
        } else {
            $searchVar->sqlSearchVar[] = "o.record_type = 'Quote'";
        }

		// FOR THE PREVIOUS MONTH
        $startDateLastMonth = date("Y-m-1", strtotime("first day of previous month") );
        $endDateLastMonth = date("Y-m-t", strtotime("last day of previous month") );


        //$searchVar->sqlSearchVar[] = "c.contact_date BETWEEN '{$startDate}' AND '{$endDate}'";

        if ($start_date != '' && $end_date == '') {
            $searchVar->sqlSearchVar[] = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
	        $searchVar->sqlSearchVar[] = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
  	        $searchVar->sqlSearchVar[] = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else {
            $startDate = $year . '-' . $month . '-' . '01';
            $endDate   = $year . '-' . $month . '-' . '31';
            $searchVar->sqlSearchVar[] = "i.invoice_date BETWEEN '{$startDateLastMonth}' AND '{$endDateLastMonth}'" ;
        }

        $searchVar->sqlSearchVar[] = "i.vat = 1" ;
        $searchVar->sqlSearchVar[] = "i.status != 'Cancelled'" ;
        $searchVar->sortOrder = 'i.invoice_code_vat ASC';

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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_detailVatPercentForInvoice');

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
            $serial_no += 1;
            $grand_total += $row['order_amount'];
        }

        $row = array(
                    'grand_total' => $grand_total
                    );

        return $row;
    }

    /**
     */
    function getExportToExcelOLD(){
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

        $file_name = "DetailVatPercentForInvoice_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Code (VAT)');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Order Code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'VAT Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Amount(VAT Included)');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Status');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Order Type');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Tin No');
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

        $start_date 	= $fn->getReqParam('start_date');
        $end_date   	= $fn->getReqParam('end_date');
        $month      	= $fn->getReqParam('month');
        $year       	= $fn->getReqParam('year');
        $current_date 	= date('Y-m-d');
        $month        	= date('m');
        $year		  	= date('Y');
        $appendSql = '';
        // FOR THE PREVIOUS MONTH
        $startDateLastMonth = date("Y-m-1", strtotime("first day of previous month") );
        $endDateLastMonth = date("Y-m-t", strtotime("last day of previous month") );

        if ($start_date != '' && $end_date == '') {
            $appendSql = " AND i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
	        $appendSql = " AND i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
  	        $appendSql = " AND i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else {
            $startDate = $year . '-' . $month . '-' . '01';
            $endDate   = $year . '-' . $month . '-' . '31';
            $appendSql = " AND i.invoice_date BETWEEN '{$startDateLastMonth}' AND '{$endDateLastMonth}'" ;
        }

        $SQL = "
        SELECT i.*
              ,date_format(i.invoice_date, '%Y-%m') AS inv_year_mon
              ,date_format(i.invoice_date, '%Y') AS inv_year
              ,o.record_type
              ,o.order_id
              ,c.company_name
              ,c.tin_no
              ,(SELECT SUM(((inih.cost_price * inih.vat )/100)* inih.qty)
                FROM invoice_item inih
                WHERE inih.invoice_id = i.invoice_id) AS vat_amount_sum
              ,(SELECT SUM(inih.unit_price * inih.qty)
                FROM invoice_item inih
                WHERE inih.invoice_id = i.invoice_id AND i.vat = 1) AS selling_price_sum
              ,(SELECT SUM(inih.cost_price * inih.qty)
                FROM invoice_item inih
                WHERE inih.invoice_id = i.invoice_id  AND i.vat = 1) AS cost_price_sum
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        LEFT JOIN (`company` c) ON (c.company_id = o.company_id)
        WHERE i.vat = 1
            AND i.status != 'Cancelled'
            {$appendSql}
            ORDER BY i.invoice_code_vat ASC
        ";

        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;
            $invoice_date = $fn->getCPDate($row['invoice_date'],"d-m-Y");
            $invoice_amount = $row['invoice_amount'];
            $invoice_amount = trim($invoice_amount);
            $subSqlForPercentSum = 0;
            $subSqlForValueSum = 0;

            if($row['order_id'] < 10){
                $orderId = '0000' . $row['order_id'];
            }
            else if($row['order_id'] < 99){
                $orderId = '000' . $row['order_id'];
            }
            else if($row['order_id'] < 999){
                $orderId = '00' . $row['order_id'];
            }
            else if($row['order_id'] < 9999){
                $orderId = '0' . $row['order_id'];
            }
            else{
                $orderId = $row['order_id'];
            }

            //TO CHECK IF THE SUM OF DISCOUNT TYPE(%) HAS VALUE OR NOT AND ASSIGN THE QUERY IF IT HAS VALUE ELSE ASSIGN ZERO
            $subSqlForPercentSum = "
            SELECT SUM(round(((ini.cost_price * ini.discount_percentage )/100)* ini.qty,2)) as discount_sum
            FROM invoice_item ini
            WHERE ini.invoice_id = {$row['invoice_id']}
                AND ini.discount_type = '%'
            ";
            $resultSubSql = $db->sql_query($subSqlForPercentSum);
            $rowSql       = $db->sql_fetchrow($resultSubSql);
            if($rowSql['discount_sum'] > 0){
                $subSqlForPercentSum = $rowSql['discount_sum'];
            }

            //TO CHECK IF THE SUM OF DISCOUNT TYPE(VALUE) HAS VALUE OR NOT AND ASSIGN THE QUERY IF IT HAS VALUE ELSE ASSIGN ZERO
            $subSqlForValueSum ="
            SELECT SUM(round(ini.discount_percentage  * ini.qty,2)) as discount_sum
            FROM invoice_item ini
            WHERE ini.invoice_id = {$row['invoice_id']}
                AND ini.discount_type = 'Value'
            ";
            $resultSubSql = $db->sql_query($subSqlForValueSum);
            $rowSql       = $db->sql_fetchrow($resultSubSql);
            if($rowSql['discount_sum'] > 0){
                $subSqlForValueSum = $rowSql['discount_sum'];
            }

            $discountSum = $subSqlForPercentSum + $subSqlForValueSum;

            $SQLInvoiceItem = "
            SELECT vat
                  ,unit_price * qty AS selling_price
                  ,discount_percentage
                  ,discount_type
                  ,qty
                  ,cost_price
            FROM invoice_item
            WHERE invoice_id = {$row['invoice_id']}
            ";
            $resultInvItem = $db->sql_query($SQLInvoiceItem);
            $vatAmountSum = 0;
            while ($rowInvItem = $db->sql_fetchrow($resultInvItem)) {
                $discount_value_for_one_qty = 0;
                $discountValue =0;
                if($rowInvItem['discount_percentage'] > 0){
                    if($rowInvItem['discount_type'] == '%'){
                        $discount_value_for_one_qty  =  $rowInvItem['cost_price'] * ($rowInvItem['discount_percentage']/100);
                        $discountValue = $discount_value_for_one_qty * $rowInvItem['qty'];
                    }
                    else if($rowInvItem['discount_type']  == 'Value'){
                        $discount_value_for_one_qty  =  $rowInvItem['discount_percentage'];
                        $discountValue = $discount_value_for_one_qty * $rowInvItem['qty'];
                    }
                }

                $vat = $rowInvItem['vat'];
                $vatAmountSum += ($rowInvItem['selling_price'] - $discountValue) * $vat / 100;
            }

            if($row['invoice_id'] > 5626){
                if($row['invoice_code_vat_quote'] < 10){
                    $invoice_code_vat_quote = '00' . $row['invoice_code_vat_quote'];
                }
                else if($row['invoice_code_vat_quote'] < 99){
                    $invoice_code_vat_quote = '0' . $row['invoice_code_vat_quote'];
                }
                else{
                    $invoice_code_vat_quote = $row['invoice_code_vat_quote'];
                }

                if($row['record_type'] == 'POS') {
                    $vatSum = $vatAmountSum;
                    $invSeq = "INVT - {$row['invoice_code_vat']}";
                } else if($row['record_type'] == 'Quote'){
                    $vatSum = $row['selling_price_sum'] - ($row['cost_price_sum'] - $discountSum);
                    if(date('Y-m') < $row['inv_year'].'-04'){
                        $currentYear = $row['inv_year'];
                        $previousYear = $row['inv_year'] - 1;
                        $currentYear = substr($currentYear, 2);
                        $invSeq = 'B' . $invoice_code_vat_quote."/{$previousYear}-{$currentYear}";
                    } else {
                        $currentYear = $row['inv_year'];
                        $nextYear = $row['inv_year'] + 1;
                        $nextYear = substr($nextYear, 2);
                        $invSeq = 'B' . $invoice_code_vat_quote."/{$currentYear}-{$nextYear}";
                    }
                }
            } else{
                if($row['record_type'] == 'POS') {
                    $vatSum = $vatAmountSum;
                    $invSeq = "INVT - '{$row['invoice_code_vat']}";
                } else if($row['record_type'] == 'Quote'){
                    $vatSum = $row['selling_price_sum'] - ($row['cost_price_sum'] - $discountSum);
                    $invSeq = "INVQ - {$row['invoice_code_vat_quote']}";
                }
            }
            $vatSum = number_format($vatSum, 2);

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoice_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invSeq);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $orderId);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $vatSum);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoice_amount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['status']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['record_type']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['tin_no']);
        }

        $rowc++;
        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
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
        $location_id    = $fn->getReqParam('location_id');
        $record_type    = $fn->getReqParam('record_type');
        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "DetailVatPercentForInvoice_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Code (VAT)');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Order Code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'VAT %');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'VAT Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Amount(VAT Included)');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Status');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Order Type');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Company Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Tin No');
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

        $start_date 	= $fn->getReqParam('start_date');
        $end_date   	= $fn->getReqParam('end_date');
        $month      	= $fn->getReqParam('month');
        $year       	= $fn->getReqParam('year');
        $current_date 	= date('Y-m-d');
        $month        	= date('m');
        $year		  	= date('Y');
        $appendSql = '';
        $appendSqlsite = '';
        $appendSqltype = '';
        $vatSum = 0;
        $vatSum1 = 0;
        $sumArray = array();

		// FOR THE PREVIOUS MONTH
        $startDateLastMonth = date("Y-m-1", strtotime("first day of previous month") );
        $endDateLastMonth 	= date("Y-m-t", strtotime("last day of previous month") );


        if ($start_date != '' && $end_date == '') {
            $appendSql = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
	        $appendSql = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
  	        $appendSql = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else {
            $startDate = $year . '-' . $month . '-' . '01';
            $endDate   = $year . '-' . $month . '-' . '31';
            $appendSql = "i.invoice_date BETWEEN '{$startDateLastMonth}' AND '{$endDateLastMonth}'" ;
        }

       if ($location_id != '') {
            $appendSqlsite = "AND o.site_id = {$location_id}";
       }
       if ($record_type == 'POS') {
            $appendSqltype = "AND o.record_type = 'POS'";
        } else {
            $appendSqltype = "AND o.record_type = 'Quote'";
        }

	 	$SQL = "
	 	SELECT i.*
              ,date_format(i.invoice_date, '%Y-%m') AS inv_year_mon
              ,date_format(i.invoice_date, '%Y') AS inv_year
              ,o.record_type
              ,o.order_id
              ,c.company_name
              ,c.tin_no
              ,(SELECT SUM(srh.qty_return * srh.price) FROM sales_return_history srh
                WHERE i.invoice_id = srh.invoice_id
                  AND srh.status IS NULL) as sales_return_amount
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        LEFT JOIN (`company` c) ON (c.company_id = o.company_id)
	    WHERE
	    {$appendSql}
        {$appendSqlsite}
        {$appendSqltype}
	 	AND i.vat = 1
	 	AND i.status != 'Cancelled'
	 	ORDER BY i.invoice_code_vat ASC
		";

        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {


            $invoiceDate = $fn->getCPDate($row['invoice_date'],"d-m-Y");
            $urlOrder = "index.php?_topRm=finance&module=tradingin_order&_action=detail&record_id={$row['order_id']}";

            if($row['order_id'] < 10){
                $orderId = '0000' . $row['order_id'];
            }
            else if($row['order_id'] < 99){
                $orderId = '000' . $row['order_id'];
            }
            else if($row['order_id'] < 999){
                $orderId = '00' . $row['order_id'];
            }
            else if($row['order_id'] < 9999){
                $orderId = '0' . $row['order_id'];
            }
            else{
                $orderId = $row['order_id'];
            }


            $invoice_amount = $row['invoice_amount'] - $row['sales_return_amount'] ;

            $invoice_amount = number_format($invoice_amount, 2);

            $subSqlForPercentSum = 0;
            $subSqlForValueSum = 0;

            //TO CHECK IF THE SUM OF DISCOUNT TYPE(%) HAS VALUE OR NOT AND ASSIGN THE QUERY IF IT HAS VALUE ELSE ASSIGN ZERO
            $subSqlForPercentSum = "
            SELECT SUM(round(((ini.cost_price * ini.discount_percentage )/100)* ini.qty,2)) as discount_sum
            FROM invoice_item ini
            WHERE ini.invoice_id = {$row['invoice_id']}
                AND ini.discount_type = '%'
            ";
            $resultSubSql = $db->sql_query($subSqlForPercentSum);
            $rowSql       = $db->sql_fetchrow($resultSubSql);
            if($rowSql['discount_sum'] > 0){
                $subSqlForPercentSum = $rowSql['discount_sum'];
            }

            //TO CHECK IF THE SUM OF DISCOUNT TYPE(VALUE) HAS VALUE OR NOT AND ASSIGN THE QUERY IF IT HAS VALUE ELSE ASSIGN ZERO
            $subSqlForValueSum ="
            SELECT SUM(round(ini.discount_percentage  * ini.qty,2)) as discount_sum
            FROM invoice_item ini
            WHERE ini.invoice_id = {$row['invoice_id']}
                AND ini.discount_type = 'Value'
            ";
            $resultSubSql = $db->sql_query($subSqlForValueSum);
            $rowSql       = $db->sql_fetchrow($resultSubSql);
            if($rowSql['discount_sum'] > 0){
                $subSqlForValueSum = $rowSql['discount_sum'];
            }

            $discountSum = $subSqlForPercentSum + $subSqlForValueSum;

            $SQLInvoiceItem = "
            SELECT vat
                  ,cost_price * qty AS selling_price
                  ,discount_percentage
                  ,discount_type
                  ,qty
                  ,cost_price
            FROM invoice_item
            WHERE invoice_id = {$row['invoice_id']}
            GROUP BY vat
            ORDER BY vat
            ";
            $resultInvItem = $db->sql_query($SQLInvoiceItem);
            $vatAmountSum = 0;
            $vatPercent = '';
            $prevRows = '';
            $currentRow = '';
            $count = 0;

            while ($rowInvItem = $db->sql_fetchrow($resultInvItem)) {
            $colc = 0;
            $rowc++;

                if($row['record_type'] == 'POS') {
                    $invSeq = "INVT - {$row['invoice_code_vat']}";
                } else if($row['record_type'] == 'Quote'){
                    if($row['invoice_id'] > 5626){
                        if($row['invoice_code_vat_quote'] < 10){
                            $invoice_code_vat_quote = '00' . $row['invoice_code_vat_quote'];
                        }
                        else if($row['invoice_code_vat_quote'] < 99){
                            $invoice_code_vat_quote = '0' . $row['invoice_code_vat_quote'];
                        }
                        else{
                            $invoice_code_vat_quote = $row['invoice_code_vat_quote'];
                        }

                        if($row['selling_company'] == 'V-United Exports'){
                            if(date('Y-m') < $row['inv_year'].'-04'){
                                $currentYear = $row['inv_year'];
                                $previousYear = $row['inv_year'] - 1;
                                $currentYear = substr($currentYear, 2);
                                $invSeq = 'X' . $invoice_code_vat_quote."/{$previousYear}-{$currentYear}";
                            } else {
                                $currentYear = $row['inv_year'];
                                $nextYear = $row['inv_year'] + 1;
                                $nextYear = substr($nextYear, 2);
                                $invSeq = 'X' . $invoice_code_vat_quote."/{$currentYear}-{$nextYear}";
                            }
                        }
                        elseif($row['selling_company'] == 'V-United Impex'){
                            $invSeq = 'INVL -' . $row['invoice_code_vat_quote'];
                        }
                        else{
                            if(date('Y-m') < $row['inv_year'].'-04'){
                                $currentYear = $row['inv_year'];
                                $previousYear = $row['inv_year'] - 1;
                                $currentYear = substr($currentYear, 2);
                                $invSeq = 'B' . $invoice_code_vat_quote."/{$previousYear}-{$currentYear}";
                            } else {
                                $currentYear = $row['inv_year'];
                                $nextYear = $row['inv_year'] + 1;
                                $nextYear = substr($nextYear, 2);
                                $invSeq = 'B' . $invoice_code_vat_quote."/{$currentYear}-{$nextYear}";
                            }
                        }
                    } else{
                        if($row['selling_company'] == 'V-United Exports'){
                            $invSeq = "INVX - {$row['invoice_code_vat_quote']}";
                        } elseif($row['selling_company'] == 'V-United Impex'){
                            $invSeq = "INVL - {$row['invoice_code_vat_quote']}";
                        }else{
                            $invSeq = "INVQ - {$row['invoice_code_vat_quote']}";
                        }
                    }
                }

                $sumArray = $this->view->getVatSumForPercent($row['invoice_id'],$rowInvItem['vat'],$row['record_type'], $row['general_discount_percent']);

                if($rowInvItem['vat'] < 1){
                    $vatSum = '';
                }
                else{
                    $vatSum = number_format($sumArray[0], 2);
                }

            $invoice_amount = number_format($sumArray[1], 2);

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoiceDate);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invSeq);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $orderId);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowInvItem['vat']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $vatSum);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoice_amount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['status']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['record_type']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['tin_no']);

        	}
	    }

        $rowc++;


        $actSheet->getStyle("A{$rowc}:J{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     */
    function getExportToExcel2($dataArray = ''){
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        if (!is_array($dataArray)){
            $dataArray = $this->getDataArray();
        }

        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');
        $fa = array(
              'invoice_date'     => $phpExcel->getFldObj('Date')
             ,'invoice_code_vat' => $phpExcel->getFldObj('Invoice Code (VAT)')
             ,'invoice_amount'   => $phpExcel->getFldObj('Amount')
             ,'status'           => $phpExcel->getFldObj('Status')
             ,'record_type'      => $phpExcel->getFldObj('Order Type')
             ,'company_name'     => $phpExcel->getFldObj('Company Name')
             ,'tin_no'           => $phpExcel->getFldObj('Tin No.')
        );

        $file_name = "VatInvoice_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }
}