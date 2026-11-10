<?
class CP_Admin_Widgets_Tradingsg_InvoicesForVat_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');
        $cpCfg 	= Zend_Registry::get('cpCfg');

	// **** THIS CONDITION HAS BEEN USED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\
		$siteLocation = '' ;
		if($cpCfg['cp.hasMultiUniqueSites']){
			$siteLocation = "
			<th>Location</th>
			";
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
        <h2>VAT Invoice</h2>
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
						<th>Date</th>
						<th>Invoice Code (VAT)</th>
						<th>Order Code</th>
						<th>VAT Amount</th>
                        <th>Amount (VAT Included)</th>
                        <th>Status</th>
						<th>Order Type</th>
                        <th>Company Name</th>
                        <th>Tin no.</th>
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
        $cpCfg 	= Zend_Registry::get('cpCfg');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $rows = '';
        $vatSum = 0;
		$siteTitle = '' ;

        foreach($this->model->dataArray as $row){

			$invoiceDate = $fn->getCPDate($row['invoice_date'],"d-m-Y");
            //$urlOrder = "index.php?_topRm=finance&module=tradingin_order&_action=detail&record_id={$row['order_id']}";

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
            SELECT invitem.vat
                  ,invitem.cost_price * invitem.qty AS selling_price
                  ,invitem.discount_percentage
                  ,invitem.discount_type
                  ,invitem.qty
                  ,invitem.cost_price
                ,(SELECT SUM(srh.qty_return * srh.price) FROM sales_return_history srh
                WHERE invitem.invoice_item_id = srh.invoice_item_id
                  AND srh.status IS NULL
                ) as sales_return_amount_from_invItem
            FROM invoice_item invitem
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
                $vatAmountSum += ($rowInvItem['selling_price'] - $discountValue -  $rowInvItem['sales_return_amount_from_invItem']) * $vat / 100;
            }
            $invSeq = '';
            if($row['record_type'] == 'POS') {
                $vatSum = $vatAmountSum;
                $invSeq = "INVT - {$row['invoice_code_vat']}";
            } else if($row['record_type'] == 'Quote'){
                $vatSum = $vatAmountSum;
                //need to check if the below condition is required (Syed - 14-7-2014)
                //$vatSum = $row['selling_price_sum']- ($row['cost_price_sum'] - $discountSum) ;
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
                    } else if($row['selling_company'] == 'V-United Impex'){
                        $invSeq = "INVL - {$row['invoice_code_vat_quote']}";
                    }else{
                        $invSeq = "INVQ - {$row['invoice_code_vat_quote']}";
                    }
                }
            }

            $vatSum = number_format($vatSum, 2);

			// **** THIS CONDITION HAS BEEN ADDED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\

			if($cpCfg['cp.hasMultiUniqueSites'] == 1){
			    $siteRec = $fn->getRecordRowById('site', 'site_id', $row['site_id']);

				$siteTitle = "
				<td>{$siteRec['title']}</td>
				";

                if($row['site_id'] == $cpSiteIdSession){
                    $urlOrder = "<a target = '_blank' href = 'index.php?_topRm=finance&module=tradingin_order&_action=detail&record_id={$row['order_id']}'>";
                }
                else{
                    $urlOrder ='';
                }
			}
            else{
                $urlOrder = "<a target = '_blank' href = 'index.php?_topRm=finance&module=tradingin_order&_action=detail&record_id={$row['order_id']}'>";
            }

		    $rows .= "
			<tr>
				<td>{$invoiceDate}</td>
				<td>{$invSeq}</td>
                <td>{$urlOrder}{$orderId}</td>
				<td class='txtRight'>{$vatSum}</td>
                <td class='txtRight'>{$invoice_amount}</td>
                <td>{$row['status']}</td>
				<td>{$row['record_type']}</td>
                <td>{$row['company_name']}</td>
                <td>{$row['tin_no']}</td>
				{$siteTitle}
			</tr>
			";
        }

        $text = "
        {$rows}
        ";

        return $text;
    }

}