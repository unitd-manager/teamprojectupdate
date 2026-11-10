<?
class CP_Admin_Widgets_Tradingsg_InvoicesByVatPercent_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg 	= Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

	// **** THIS CONDITION HAS BEEN USED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\
		$siteLocation = '' ;
		if($cpCfg['cp.hasMultiUniqueSites']){
			/*$siteLocation = "
			<th>Location</th>
			";*/
            $location_id    = $fn->getReqParam('location_id');
            $sqlsite="SELECT * FROM site
            ";
            $resultsqlsite = $db->sql_query($sqlsite);
            if($location_id == ''){
                    while ($rowSite= $db->sql_fetchrow($resultsqlsite)) {
                        $siteLocation .= "
                        <th>{$rowSite['title']}</th>
                        ";
                    }
                }
            else{
                     $siteLocation = "
                    <th>Location</th>
                    ";
                }
		}

        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $month          = $fn->getReqParam('month');
        $year           = $fn->getReqParam('year');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');

        $startDateLastMonth = date("Y-m-1", strtotime("first day of previous month") );
        $endDateLastMonth = date("Y-m-t", strtotime("last day of previous month") );

        if ($start_date != '' && $end_date == '') {
            $start_date = $start_date;
            $end_date   = $current_date;
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $start_date = $start_date;
            $end_date   = $end_date;
        } else if ($start_date != '' && $end_date != '') {
            $start_date = $start_date;
            $end_date   = $end_date;
        } else {
            $start_date = $startDateLastMonth;
            $end_date   = $endDateLastMonth;
        }

        $start_date_formatted = $dateUtil->formatDate($start_date, 'DD/MM/YYYY');
        $end_date_formatted   = $dateUtil->formatDate($end_date, 'DD/MM/YYYY');

        $text = "
        <h2>Invoice By VAT %</h2>
        <table class='thinlist summaryTable mb20'>
            <thead>
                <th colspan='6'>Summary</th>
            </thead>
            <tr>
                <td>Start Date : {$start_date_formatted}</td>
                <td>End Date : {$end_date_formatted}</td>
            </tr>
        </table>
		<div class = 'tableOuter scroll-pane'>
			<table class='thinlist'>
				<thead>
					<tr>
						<th>VAT %</th>
						<th>VAT Amount</th>
                        <th>Invoice Amount</th>
                        <th>Order Type</th>
						{$siteLocation}
					</tr>
				</thead>
				<tbody>
					{$this->getRowsHTML()}
				</tbody>
			</table>
		</div>
        ";
        return $text;
    }

    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';
        $vatSum = 0;
		$siteTitle = '' ;
        $appendSqlSite = '';

        $record_type    = $fn->getReqParam('record_type');
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $month          = $fn->getReqParam('month');
        $year           = $fn->getReqParam('year');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');
        $sqlAppend = '';
        $sqlAppendType = '';
        $startDateLastMonth = date("Y-m-1", strtotime("first day of previous month") );
        $endDateLastMonth = date("Y-m-t", strtotime("last day of previous month") );

        if ($start_date != '' && $end_date == '') {
            $sqlAppend = " AND (i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$current_date}')";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $sqlAppend = " AND (i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}')";
        } else if ($start_date != '' && $end_date != '') {
            $sqlAppend = " AND (i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}')";
        } else {
            $startDate = $year . '-' . $month . '-' . '01';
            $endDate   = $year . '-' . $month . '-' . '31';
            $sqlAppend = " AND (i.invoice_date BETWEEN '{$startDateLastMonth}' AND '{$endDateLastMonth}')" ;
        }

        if ($record_type == 'POS') {
            $sqlAppendType = " AND o.record_type = 'POS'";
        } else {
            $sqlAppendType = " AND o.record_type = 'Quote'";
        }

        foreach($this->model->dataArray as $row){
            if($row['vat'] == ''){
                $row['vat'] = 0;
            }

            if ($cpCfg['cp.hasMultiUniqueSites']  == 1) {
                $siteRec = $fn->getRecordRowById('site', 'site_id', $row['site_id']);
                $location_id    = $fn->getReqParam('location_id');

                if($location_id !=''){
                    $appendSqlSite = "AND o.site_id = {$row['site_id']}" ;
                    $siteTitle = "
                    <td>{$siteRec['title']}</td>
                    ";
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
                        {$sqlAppend}
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
                            $vat_amount =  ($rowInvItem['cost_price'] * $rowInvItem['qty']);
                            $vat_amount =  $vat_amount - $discountValue;
                            $vat_amount =  ($vat_amount * $row['vat'] /100);
                            $vat_amount_sum += $vat_amount;
                            $discountValueSum  += $discountValue;

                            if($row['general_discount_percent'] > 0){
                                $invoice_discount = $rowInvItem['cost_price'] - (($rowInvItem['cost_price'] * $row['general_discount_percent'])/100);
                                $invoice_amount += ($invoice_discount * $rowInvItem['qty']);
                            } else {
                                $invoice_amount += ($rowInvItem['cost_price'] * $rowInvItem['qty']);
                            }

                        }
                    }

                    $invoice_amount = $invoice_amount - $discountValueSum;
                    $invoice_amount = number_format($invoice_amount,2);
                    $siteTitle .="<td class='txtRight'>$invoice_amount</td>";
                    //$siteTitle = $amountSite;
                    //print($siteTitle);
                    //print('//');
                }

                }
            }

            $SQLInvItem = "
            SELECT inih.*
            FROM invoice_item inih
            LEFT JOIN (`invoice` i) ON (i.invoice_id = inih.invoice_id)
            LEFT JOIN (`order` o) ON (o.order_id = i.order_id)
            WHERE inih.vat = {$row['vat']}
                AND i.vat = 1
                AND i.status != 'Cancelled'
                {$sqlAppend}
                {$sqlAppendType}
                {$appendSqlSite}
                ";
            $resultInvItem = $db->sql_query($SQLInvItem);

            $discountValueSum = 0;
            $vat_amount_sum   = 0;
            $vat_amount       = 0;
            $invoice_amount   = 0;

			// **** THIS CONDITION HAS BEEN ADDED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\

			/*if($cpCfg['cp.hasMultiUniqueSites'] == 1){
			    $siteRec = $fn->getRecordRowById('site', 'site_id', $row['site_id']);

				$siteTitle = "
				<td>{$siteRec['title']}</td>
				";
			}*/

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

		    $rows .= "
			<tr>
                <td>{$row['vat']}</td>
                <td class='txtRight'>{$vat_amount_sum}</td>
                <td class='txtRight'>{$invoice_amount}</td>
                <td>{$record_type}</td>
				{$siteTitle}
			</tr>
			";
        }

        $text = "
        {$rows}
        ";

        return $text;
    }

    function getRowsHTMLOld() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $rows = '';
        $vatSum = 0;

        foreach($this->model->dataArray as $row){
            $discount_value_for_one_qty = 0;
            $discountValue =0;
            if($row['discount_percentage'] > 0){
                if($row['discount_type'] == '%'){
                    $discount_value_for_one_qty  =  $row['unit_price'] * ($row['discount_percentage']/100);
                    $discountValue = $discount_value_for_one_qty * $row['qty'];
                }
                else if($row['discount_type']  == 'Value'){
                    $discount_value_for_one_qty  =  $row['discount_percentage'];
                    $discountValue = $discount_value_for_one_qty * $row['qty'];
                }
            }

            $invoiceDate = $fn->getCPDate($row['invoice_date'],"d-m-Y");
            $vat_amount_sum = number_format($row['vat_amount_sum'], 2);
            $unit_price = $row['cost_price'] * $row['qty'] - $discountValue;
            $unit_price = number_format($unit_price, 2);

            $rows .= "
            <tr>
                <td>{$invoiceDate}</td>
                <td>{$row['vat']}</td>
                <td class='txtRight'>{$vat_amount_sum}</td>
                <td class='txtRight'>{$unit_price}</td>
                <td>{$row['record_type']}</td>
            </tr>
            ";
        }

        $text = "
        {$rows}
        ";

        return $text;
    }
}