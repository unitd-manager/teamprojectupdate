<?
class CP_Admin_Widgets_Tradingsg_SummaryPurchaseSalesReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /*
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $company_id = $fn->getReqParam('company_id');

        if ($start_date != '' && $end_date!= '' && ($end_date < $start_date)) {
            return "<div class='txtCenter'>Start date should not be after End date</div>";
        }

        $companyRec = $fn->getRecordRowById('company', 'company_id', $company_id);

        if ($start_date != '' && $end_date == '') {
            $end_date = date('Y-m-d');
        } else if ($start_date == '' && $end_date != ''){
            $start_date = substr($end_date, 0, 8) . '01';
        } else if ($start_date != '' && $end_date != '') {
        } else {
            $start_date = date('Y-m-d',mktime (0,0,0,date("m")-1,date("d"), date("Y")));
            $end_date   = date('Y-m-d');
        }

        $start_date = $dateUtil->formatDate($start_date, 'DD-MMM-YYYY');
        $end_date   = $dateUtil->formatDate($end_date, 'DD-MMM-YYYY');
        //<td class='txtRight'>Balance : {$this->getOverallBalance()}</td>
        $text = "
        <table class='thinlist summaryTable'>
            <thead>
                <th colspan='4'>Summary</th>
            </thead>
            <tr>
                <td>From Date : {$start_date}</td>
                <td>To Date : {$end_date}</td>
                <td>Company : {$companyRec['company_name']}</td>
            </tr>
        </table>
		<div class = 'tableOuter scroll-pane'>
			<table class='thinlist mt10'>
				<thead>
					<tr>
						<th class='quoteCodeLbl'>Quote Code</th>
						<th class='orderDateLbl'>Order Date</th>
						<th class='companyNameLbl'>Client Name</th>
						<th class='totalSalesLbl'>Total Sales</th>
                        <th>Total Discount</th>
                        <th class='purchaseValLbl'>Total Purchase  (without GST)</th>
						<th>GST</th>
                        <th>Profit</th>
					</tr>
				</thead>
				{$this->getRowsHTML()}
			</table>
		</div>
        ";

        //<th class='balanceAmtLbl'>Balance</th>
        return $text;
    }

    /*
     *
     */
    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows                = '';
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

        foreach ($this->model->dataArray as $row) {

            $gstAmount = ($row['total_purchase'] * $gsttaxvalue)/100;
            $totAlamount = $row['total_purchase'] + $gstAmount;

            $total_sales    = number_format($row['total_sales'], 2);
            //$total_purchase = number_format($row['total_purchase'], 2);
            $total_purchase = number_format($totAlamount, 2);
            //$balance_amount = number_format(($row['total_sales'] - $row['total_purchase']), 2);
            $balance_amount = number_format(($row['total_sales'] - $totAlamount), 2);

            $overall_sales    += $row['total_sales'];
            //$overall_purchase += $totAlamount;
            $overall_purchase += $row['total_purchase'];

            $overall_gst +=  $gstAmount;

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

            $totalPurchaseAmount = number_format($row['total_purchase'],2);

            $purchase_details = $this->getPurchaseDetails($row['quote_id']);

            $profit = $row['total_sales'] - $totalDiscount - $totAlamount;

            $overall_profit += $profit;

            $totalDiscount = number_format($totalDiscount,2);
            $profit        = number_format($profit,2);
            $gstAmount     = number_format($gstAmount,2);

            $rows .= "
            <tbody class='purchaseSalesSummary'>
                <tr>
    				<td class='quoteCode'>{$row['quote_code']}</td>
    				<td class='orderDate'>{$row['formatted_order_date']}</td>
    				<td class='companyName'>{$row['company_name']}</td>
    				<td class='totalSales'>{$total_sales}</td>
                    <td class='txtRight'>{$totalDiscount}</td>
    				<td class='purchaseVal'>{$totalPurchaseAmount}</td>
    				<td class='txtRight'>{$gstAmount}</td>
                    <td class='txtRight'>{$profit}</td>
                </tr>

                <tr>
	                <td class='purchaseDetailsMain' colspan='6'>{$purchase_details}</td>
                </tr>
            </tbody>
			";

            //<td class='balanceAmt'>{$balance_amount}</td>
        }

        $format_overall_sales    = number_format($overall_sales, 2);
        $format_overall_purchase = number_format($overall_purchase, 2);
        $format_overall_balance  = number_format(($overall_sales - $overall_purchase), 2);
        $format_overall_Discount = number_format(($overall_Discount), 2);
        $format_overall_gst      = number_format(($overall_gst), 2);
        $format_overall_profit   = number_format(($overall_profit), 2);

        $rows .= "
        <tr>
            <td colspan='3' class='txtRight'>Overall Amount</td>
            <td class='txtRight'>{$format_overall_sales}</td>
            <td class='txtRight'>{$format_overall_Discount}</td>
            <td class='txtRight'>{$format_overall_purchase}</td>
            <td class='txtRight'>{$format_overall_gst}</td>
            <td class='txtRight'>{$format_overall_profit}</td>
        </tr>
        ";

        $text = "
        {$rows}
        ";

        return $text;
    }

    /*
     *
     */
    function getOverallBalance() {
        $overall_sales    = 0;
        $overall_purchase = 0;

        foreach ($this->model->dataArray as $row) {
            $overall_sales    += $row['total_sales'];
            $overall_purchase += $row['total_purchase'];
        }

        $format_overall_balance  = number_format(($overall_sales - $overall_purchase), 2);
        return $format_overall_balance;
    }

    /**
     *
     */
    function getPurchaseDetails($quote_id) {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows  = '';
        $count = 1;
        $gstAmount = 0;
        $totAlamount = 0;
        $totAlamount_format = 0;
        $gsttaxvalue = $cpCfg['amtForGSTCalc'] ;

        $sqlPop = "
        SELECT SUM(pop.price * pop.qty) AS purchase_amount
               ,c.company_name
        FROM po_product pop
        LEFT JOIN (company c) ON (pop.supplier_id = c.company_id)
        LEFT JOIN (quote q)   ON (pop.quote_id    = q.quote_id)
        WHERE pop.quote_id = {$quote_id}
        GROUP BY pop.supplier_id
        ORDER BY c.company_name ASC
        ";
        $resultPop = $db->sql_query($sqlPop);
        while ($rowPop = $db->sql_fetchrow($resultPop)) {

            $gstAmount = ($rowPop['purchase_amount'] * $gsttaxvalue)/100;
            $totAlamount = $rowPop['purchase_amount'] + $gstAmount;
            $totAlamount_format = round($totAlamount,2);

            $rows .= "
            <tr>
                <td>{$count}</td>
                <td class='productTitle'>{$rowPop['company_name']}</td>
                <td class='poAmt'>{$totAlamount_format}</td>
            <tr>
            ";
            //<td class='poAmt'>{$rowPop['purchase_amount']}</td>
            $count++;
        }

        $text = "
        <div class='purchaseDetails mt5'>
            <table class='paymentDetails'>
            <tr>
                <td>S.No</td>
                <td class='productTitle'>Supplier Name</td>
                <td class='poAmt'>Amount</td>
            </tr>
            {$rows}
            </table>
        </div>
        ";

        return $text;
    }
}