<?
class CP_Admin_Widgets_Tradingsg_InvoicesByVatPercent_Model extends CP_Common_Lib_WidgetModelAbstract
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

        /*$SQL = "
        SELECT ini.*
              ,i.invoice_date
              ,o.record_type
              ,(SELECT SUM(((inih.cost_price * inih.vat )/100)* inih.qty)
                FROM invoice_item inih
                WHERE inih.invoice_item_id = ini.invoice_item_id) AS vat_amount_sum
        FROM invoice_item ini
        LEFT JOIN (`invoice` i) ON (i.invoice_id = ini.invoice_id)
        LEFT JOIN (`order` o) ON (o.order_id = i.order_id)
        ";*/

        $SQL = "
        SELECT ini.vat
              ,i.invoice_date
              ,i.general_discount_percent
              {$appendSql}
              ,o.record_type
        FROM invoice_item ini
        LEFT JOIN (`invoice` i) ON (i.invoice_id = ini.invoice_id)
        LEFT JOIN (`order` o) ON (o.order_id = i.order_id)
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

        $order_type     = $fn->getReqParam('order_type');
        $start_date     = $fn->getReqParam('start_date');
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

		// FOR THE PREVIOUS MONTH
        $startDateLastMonth = date("Y-m-1", strtotime("first day of previous month") );
        $endDateLastMonth = date("Y-m-t", strtotime("last day of previous month") );


        //$searchVar->sqlSearchVar[] = "c.contact_date BETWEEN '{$startDate}' AND '{$endDate}'";

        if ($start_date != '' && $end_date == '') {
            $searchVar->sqlSearchVar[] = "(i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$current_date}')";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
	        $searchVar->sqlSearchVar[] = "(i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}')";
        } else if ($start_date != '' && $end_date != '') {
  	        $searchVar->sqlSearchVar[] = "(i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}')";
        } else {
            $startDate = $year . '-' . $month . '-' . '01';
            $endDate   = $year . '-' . $month . '-' . '31';
            $searchVar->sqlSearchVar[] = "(i.invoice_date BETWEEN '{$startDateLastMonth}' AND '{$endDateLastMonth}')" ;
        }
        if ($record_type == 'POS') {
            $searchVar->sqlSearchVar[] = "o.record_type = 'POS'";
        } else {
            $searchVar->sqlSearchVar[] = "o.record_type = 'Quote'";
        }

        $searchVar->sqlSearchVar[] = "i.vat = 1" ;
        $searchVar->sqlSearchVar[] = "i.status != 'Cancelled'" ;
        $searchVar->sortOrder = 'ini.vat ASC';
        $searchVar->groupBy = "ini.vat";
        //$searchVar->sortOrder = 'i.invoice_date DESC';

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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_invoicesByVatPercent');

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
        $location_id    = $fn->getReqParam('location_id');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "InvoiceByVat%_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'VAT %');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'VAT Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Invoice Amount');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Order Type');
        if ($cpCfg['cp.hasMultiUniqueSites']  == 1) {
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
        $location = '';
        $record_type    = $fn->getReqParam('record_type');
        $startDateLastMonth = date("Y-m-1", strtotime("first day of previous month") );
        $endDateLastMonth = date("Y-m-t", strtotime("last day of previous month") );
        $location_id    = $fn->getReqParam('location_id');


        if ($start_date != '' && $end_date == '') {
            $appendSql = " AND i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
	        $appendSql = " AND i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
  	        $appendSql = "AND i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        } else {
            $startDate = $year . '-' . $month . '-' . '01';
            $endDate   = $year . '-' . $month . '-' . '31';
            $appendSql = "AND (i.invoice_date BETWEEN '{$startDateLastMonth}' AND '{$endDateLastMonth}')" ;
        }

        if ($record_type == 'POS') {
            $sqlAppendType = " AND o.record_type = 'POS'";
        } else {
            $sqlAppendType = " AND o.record_type = 'Quote'";
        }

        $appendSqlsite = '' ;
        $appendSqlSiteid = '' ;

        if ($cpCfg['cp.hasMultiUniqueSites']  == 1) {
            $appendSqlsite = ",o.site_id" ;
            if($location_id !=''){
            $appendSqlSiteid = "AND o.site_id = {$location_id}" ;
            }
            else{
                $appendSqlSiteid = '';
            }
        }

        /*$SQL = "
        SELECT ini.vat
              ,o.record_type
        FROM invoice_item ini
        LEFT JOIN (`invoice` i) ON (i.invoice_id = ini.invoice_id)
        LEFT JOIN (`order` o) ON (o.order_id = i.order_id)
        WHERE i.vat = 1
            AND i.status != 'Cancelled'
            {$appendSql}
            {$sqlAppendType}
            {$location}
            GROUP BY ini.vat
            ORDER BY ini.vat ASC
        ";*/

        $SQL = "
        SELECT ini.vat
              ,i.invoice_date
              ,i.general_discount_percent
              {$appendSqlsite}
              ,o.record_type
        FROM invoice_item ini
        LEFT JOIN (`invoice` i) ON (i.invoice_id = ini.invoice_id)
        LEFT JOIN (`order` o) ON (o.order_id = i.order_id)
        WHERE i.vat = 1
            AND i.status != 'Cancelled'
            {$appendSql}
            {$sqlAppendType}
            {$appendSqlSiteid}
            GROUP BY ini.vat
            ORDER BY ini.vat ASC ,i.invoice_date DESC
        ";

        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            if($row['vat'] == ''){
                $row['vat'] = 0;
            }
            if($record_type == ''){
                $record_type = 'POS';
                $sqlAppendType = " AND o.record_type = '{$record_type}'";
            }
            else{
                $sqlAppendType = " AND o.record_type = '{$record_type}'";
            }

            $SQLInvItem = "
            SELECT inih.*
            FROM invoice_item inih
            LEFT JOIN (`invoice` i) ON (i.invoice_id = inih.invoice_id)
            LEFT JOIN (`order` o) ON (o.order_id = i.order_id)
            WHERE inih.vat = {$row['vat']}
                AND i.vat = 1
                AND i.status != 'Cancelled'
                {$appendSql}
                {$sqlAppendType}
                {$appendSqlSiteid}
                ";
            $resultInvItem = $db->sql_query($SQLInvItem);
            $discountValueSum = 0;
            $vat_amount_sum   = 0;
            $vat_amount       = 0;
            $invoice_amount   = 0;

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

                if($row['record_type'] == 'POS'){
                    $vat_amount =  ($rowInvItem['unit_price'] * $rowInvItem['qty']);
                    $vat_amount =  $vat_amount - $discountValue;
                    $vat_amount =  ($vat_amount * $row['vat'] /100);
                    $vat_amount_sum += $vat_amount;
                    $discountValueSum  += $discountValue;
                    $invoice_amount += ($rowInvItem['unit_price'] * $rowInvItem['qty']);
                } else {
                    if($row['general_discount_percent'] > 0){
                        $invoice_discount = $rowInvItem['cost_price'] - (($rowInvItem['cost_price'] * $row['general_discount_percent'])/100);
                        $invoice_amount += ($invoice_discount * $rowInvItem['qty']);
                        $vat_amount =  ($invoice_discount * $rowInvItem['qty']);
                    } else {
                        $invoice_amount += ($rowInvItem['cost_price'] * $rowInvItem['qty']);
                        $vat_amount =  ($rowInvItem['cost_price'] * $rowInvItem['qty']);
                    }
                    $vat_amount =  $vat_amount - $discountValue;
                    $vat_amount =  ($vat_amount * $row['vat'] /100);
                    $vat_amount_sum += $vat_amount;
                    $discountValueSum  += $discountValue;
                }
            }

            //$invoiceDate = $fn->getCPDate($row['invoice_date'],"d-m-Y");
            $vat_amount_sum = number_format($vat_amount_sum, 2);
            $invoice_amount = $invoice_amount - $discountValueSum;
            $invoice_amount = number_format($invoice_amount, 2);

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['vat']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $vat_amount_sum);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoice_amount);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $record_type);

            if ($cpCfg['cp.hasMultiUniqueSites']  == 1) {
                    $siteRec = $fn->getRecordRowById('site', 'site_id', $row['site_id']);
                    $location_id    = $fn->getReqParam('location_id');

                if($location_id !=''){
                    $siteTitle = $siteRec['title'];
                    $appendSqlSite = "AND o.site_id = {$row['site_id']}" ;
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $siteTitle);
                }
                else{
                    $appendSqlSite = '';

                    $sqlsite="SELECT * FROM site
                    ";
                    $resultsqlsite = $db->sql_query($sqlsite);
                    $siteTitle = '' ;
                    while ($rowSite= $db->sql_fetchrow($resultsqlsite)) {
                    $amountSite = '';
                    $SQLInvItem = "
                    SELECT inih.*
                    FROM invoice_item inih
                    LEFT JOIN (`invoice` i) ON (i.invoice_id = inih.invoice_id)
                    LEFT JOIN (`order` o) ON (o.order_id = i.order_id)
                    WHERE inih.vat = {$row['vat']}
                        AND i.vat = 1
                        AND i.status != 'Cancelled'
                        {$appendSql}
                        {$sqlAppendType}
                        AND o.site_id = {$rowSite['site_id']}
                        ";
                    $resultInvItem = $db->sql_query($SQLInvItem);
                    $discountValueSum = 0;
                    $vat_amount_sum   = 0;
                    $vat_amount       = 0;
                    $invoice_amount   = 0;

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
                        if($row['record_type'] == 'POS'){
                            $vat_amount =  ($rowInvItem['unit_price'] * $rowInvItem['qty']);
                            $vat_amount =  $vat_amount - $discountValue;
                            $vat_amount =  ($vat_amount * $row['vat'] /100);
                            $vat_amount_sum += $vat_amount;
                            $discountValueSum  += $discountValue;
                            $invoice_amount += ($rowInvItem['unit_price'] * $rowInvItem['qty']);
                        } else {
                            if($row['general_discount_percent'] > 0){
                                $invoice_discount = $rowInvItem['cost_price'] - (($rowInvItem['cost_price'] * $row['general_discount_percent'])/100);
                                $invoice_amount += ($invoice_discount * $rowInvItem['qty']);
                                $vat_amount =  ($invoice_discount * $rowInvItem['qty']);
                            } else {
                                $invoice_amount += ($rowInvItem['cost_price'] * $rowInvItem['qty']);
                                $vat_amount =  ($rowInvItem['cost_price'] * $rowInvItem['qty']);
                            }
                            $vat_amount =  $vat_amount - $discountValue;
                            $vat_amount =  ($vat_amount * $row['vat'] /100);
                            $vat_amount_sum += $vat_amount;
                            $discountValueSum  += $discountValue;
                        }
                    }
                    $invoice_amount = $invoice_amount - $discountValueSum;
                    $invoice_amount = number_format($invoice_amount,2);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $invoice_amount);
                    //$siteTitle = $amountSite;
                    //print($siteTitle);
                    //print('//');
                }

                }
            }
        }

        $rowc++;
        if($cpCfg['cp.hasMultiUniqueSites'] == 1){
            if($location_id ==''){
                $actSheet->getStyle("A{$rowc}:F{$rowc}")->applyFromArray($headStyle);
            }
            else{
                $actSheet->getStyle("A{$rowc}:E{$rowc}")->applyFromArray($headStyle);
            }
        }
        else{
            $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);
        }
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}