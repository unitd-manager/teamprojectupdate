<?
class CP_Admin_Widgets_Tradingsg_OverallSalesSummary_View extends CP_Common_Lib_WidgetViewAbstract
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
						<th class='companyNameLbl'>Client Name</th>
						<th class='totalSalesLbl'>Total Sales</th>
                        <th class='txtRight'>Total Discount</th>
                        <th class='purchaseValLbl'>Total Purchase  (without GST)</th>
						<th class='txtRight'>GST</th>
                        <th class='txtRight'>Profit</th>
					</tr>
				</thead>
				<tbody>
					{$this->getRowsHTML()}
				</tbody>
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
        $gsttaxvalue         = $cpCfg['amtForGSTCalc'] ;
        $appendSql           = '';

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');

        foreach ($this->model->dataArray as $row) {

            $current_date = date('Y-m-d');

            if ($start_date != '' && $end_date == '') {
                $appendSql = "AND o.order_date BETWEEN '{$start_date}' AND '{$current_date}'";
            } else if ($start_date == '' && $end_date != ''){
                $start_date = substr($end_date, 0, 8) . '01';
                $appendSql = "AND o.order_date BETWEEN '{$start_date}' AND '{$end_date}'";
            } else if ($start_date != '' && $end_date != '') {
                $appendSql = "AND o.order_date BETWEEN '{$start_date}' AND '{$end_date}'";
            } else {
                $start_date = date('Y-m-d',mktime (0,0,0,date("m")-1,date("d"), date("Y")));
                $appendSql = "AND o.order_date BETWEEN '{$start_date}' AND '{$current_date}'";
            }

            $SQLDetails = "
            SELECT o.order_id
            ,o.quote_id
            FROM `order` o 
            LEFT JOIN (quote q)   ON (o.quote_id    = q.quote_id)
            WHERE o.company_id = {$row['company_id']}
            AND q.status != 'Cancelled'
            {$appendSql}
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
            $profit             = number_format($profit,2);
            $totalSales         = number_format($totalSales,2);
            $totalDiscountSum   = number_format($totalDiscountSum,2);
            $totalPurchase      = number_format($totalPurchase,2);
            $gstAmountSum       = number_format($gstAmountSum,2);

            $rows .= "
            <tr class='purchaseSalesSummary'>
                <td class='companyName'>{$row['company_name']}</td>
                <td class='txtRight'>{$totalSales}</td>
                <td class='txtRight'>{$totalDiscountSum}</td>
                <td class='txtRight'>{$totalPurchase}</td>
                <td class='txtRight'>{$gstAmountSum}</td>
                <td class='txtRight'>{$profit}</td>
            </tr>
            ";

        }
        
        $format_overall_sales    = number_format($overall_sales, 2);
        $format_overall_purchase = number_format($overall_purchase, 2);
        $format_overall_Discount = number_format(($overall_Discount), 2);
        $format_overall_gst      = number_format(($overall_gst), 2);
        $format_overall_profit   = number_format(($overall_profit), 2);
        
        $rows .= "
        <tr>
            <td colspan='1' class='txtRight'>Overall Amount</td>
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
            //$overall_sales    += $row['total_sales'];
            //$overall_purchase += $row['total_purchase'];
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