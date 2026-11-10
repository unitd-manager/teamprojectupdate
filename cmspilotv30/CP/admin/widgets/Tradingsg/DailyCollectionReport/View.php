<?
class CP_Admin_Widgets_Tradingsg_DailyCollectionReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

	// **** THIS CONDITION HAS BEEN USED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\
		$siteLocation = '' ;
		if($cpCfg['cp.hasMultiUniqueSites'] == 1){
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
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $start_date = $current_date;
            $end_date   = $current_date;
        }

        $start_date_formatted = $dateUtil->formatDate($start_date, 'DD/MM/YYYY');
        $end_date_formatted   = $dateUtil->formatDate($end_date, 'DD/MM/YYYY');

        $summaryRec = $this->model->getSqlForCount();
        $grand_total = number_format(round($summaryRec['grand_total']), 2);

        $text = "
        <h2>Daily Collection Report</h2>
        <table class='thinlist summaryTable mb20'>
            <thead>
                <th colspan='6'>Summary</th>
            </thead>
            <tr>
                <td>Start Date : {$start_date_formatted}</td>
                <td>End Date : {$end_date_formatted}</td>
                <td>Grand Total : {$grand_total}</td>
            </tr>
        </table>
		<div class = 'tableOuter scroll-pane'>
			<table class='thinlist'>
				<thead>
					<tr>
						<th>Date</th>
						{$siteLocation}
						<th class='txtRight'>Amount</th>
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
		$siteTitle = '' ;

        foreach($this->model->dataArray as $row){
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
                $order_amount = $row['amount'] - $discount_percentage_amount_sum;
            } else {
                $order_amount = $row['amount'];
            }

				// **** THIS CONDITION HAS BEEN ADDED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\
			if($cpCfg['cp.hasMultiUniqueSites'] == 1){
                $location_id    = $fn->getReqParam('location_id');

                if($location_id != ''){
    			    $siteRec = $fn->getRecordRowById('site', 'site_id', $location_id);

    				$siteTitle = "
    				<td>{$siteRec['title']}</td>
    				";
                }
                else{
                    $rowAmount = '';
                    $sqlsite="SELECT * FROM site
                    ";
                    $resultsqlsite = $db->sql_query($sqlsite);

                    $record_type    = $fn->getReqParam('record_type');

                    if ($record_type == 'Quote') {
                        $recordType = "AND o.record_type = 'Quote'";
                    } else {
                        $recordType = "AND o.record_type = 'POS'";
                    }

                    while ($rowSite= $db->sql_fetchrow($resultsqlsite)) {
                    $SQL = "
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

                        $result = $db->sql_query($SQL);
                        $rowz = $db->sql_fetchrow($result);

                    $receipt_amount_location = $rowz['receipt_amount'] - $rowz['sales_return_amount'];
                    $receipt_amount_location = number_format(round($receipt_amount_location),2);

                    $rowAmount .= "
                    <td class='txtRight'>{$receipt_amount_location}</td>
                    ";
                    }
                    $siteTitle = $rowAmount;
                }
			}

			if($row['date']){
				$creationDate = $fn->getCPDate($row['date'],"d-m-Y");
                if($row['sales_return_amount'] != ''){
                    $amount = $row['receipt_amount'] - $row['sales_return_amount'];
                    $amount = number_format(round($amount), 2);
                }else{
				    $amount = number_format(round($row['receipt_amount']), 2);
                }

			    $rows .= "
				<tr>
					<td>{$creationDate}</td>
					{$siteTitle}
					<td class='txtRight'>{$amount}</td>
				</tr>
				";
			}
        }

        $text = "
        {$rows}
        ";

        return $text;
    }

}