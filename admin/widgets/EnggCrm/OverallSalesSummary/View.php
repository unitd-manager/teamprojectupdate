<?
class CPL_Admin_Widgets_EnggCrm_OverallSalesSummary_View extends CP_Common_Lib_WidgetViewAbstract
{
    /*
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $dateUtil = Zend_Registry::get('dateUtil');
        
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $company_id = $fn->getReqParam('company_id');

        $companyRec = $fn->getRecordRowById('company', 'company_id', $company_id);
        $companyName = is_array($companyRec) && isset($companyRec['company_name'])
            ? $companyRec['company_name']
            : '';

        $current_date = date('Y-m-d');
        if ($start_date != '' && $end_date == '') {
            $end_date   = $current_date;
        } else if ($start_date == '' && $end_date != ''){
            $start_date = substr($end_date, 0, 8) . '01';
        } else if ($start_date != '' && $end_date != '') {
        } else {
            $start_date = date('Y-m-d',mktime (0,0,0,date("m")-1,date("d"), date("Y")));
            $end_date = $current_date;
        }

        $start_date = $dateUtil->formatDate($start_date, 'DD-MM-YYYY');
        $end_date   = $dateUtil->formatDate($end_date, 'DD-MM-YYYY');
        
        if($tv['module'] == 'common_dashboard'){
            $text = "
            <div id='invoiceSummaryDisplay' class='inner'>
                {$this->getInvoiceSummaryDisplay()}
            </div>
            ";
        } else {
            $text = "
            <table class='thinlist summaryTable'>
                <thead>
                    <th colspan='3' class='txtCenter'>Summary</th>
                </thead>
                <tr>
                    <td><b>Company Name :</b> {$companyName}</td>
                    <td><b>Start Date :</b> {$start_date}</td>
                    <td><b>End Date :</b> {$end_date}</td>
                </tr>
            </table>

    		<div class='tableOuter scroll-pane'>
    			<table class='thinlist mt10'>
    				<thead>
    					<tr>
    						<th class=''>Invoice Date</th>
                            <th class=''>Invoice No</th>
                            <th class='companyNameLbl'>Company Name</th>
    						<th class='totalSalesLbl'>Invoice Period Date</th>
                            <th class='txtRight'>Invoice Amount</th>
    						<th class='txtRight'>GST</th>
                            <th class='txtRight'>Total</th>
                            <th class='txtRight'>Received</th>
                            <th class='txtRight'>Balance</th>
    					</tr>
    				</thead>
    				<tbody>
    					{$this->getRowsHTML()}
    				</tbody>
    			</table>
    		</div>
            ";
        }

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
        $appendSql           = '';

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');

        foreach ($this->model->dataArray as $row) {
            $current_date = date('Y-m-d');
            $invoice_date = $fn->getCPDate($row['invoice_date'], 'd-m-Y');

            if ($row['gst_percentage'] > 0) {
                $total = $fn->getAmountFractionFormattedForGst($row['invoice_amount'], $row['gst_percentage']);
            } else {
                $total = $row['invoice_amount'];
            }

            $gst = $total - $row['invoice_amount'];
            $invoice_code = $row['invoice_code'];

            $sqlRec = "
            SELECT SUM(irh.amount) AS total_invoice_amount_paid
            FROM invoice_receipt_history irh
            LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
            LEFT JOIN (receipt r) ON (irh.receipt_id = r.receipt_id)
            WHERE i.invoice_id = {$row['invoice_id']}
              AND r.receipt_status = 'Paid'
            ";
            $resultRec = $db->sql_query($sqlRec);
            $rowRec    = $db->sql_fetchrow($resultRec);

            $receipt_amount = $rowRec['total_invoice_amount_paid'];
            if ($rowRec['total_invoice_amount_paid'] == '') {
                $receipt_amount = 0;
            }

            $balance = $total - $receipt_amount;

            $rows .= "
            <tr class='purchaseSalesSummary'>
                <td class=''>{$invoice_date}</td>
                <td class=''>{$invoice_code}</td>
                <td class='companyName'>{$row['company_name']}</td>
                <td class=''></td>
                <td class='txtRight'>{$row['invoice_amount']}</td>
                <td class='txtRight'>{$gst}</td>
                <td class='txtRight'>{$total}</td>
                <td class='txtRight'>{$receipt_amount}</td>
                <td class='txtRight'>{$balance}</td>
            </tr>
            ";
        }

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

    /*
     *
     */
    function getInvoiceSummaryDisplay() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');

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
        $appendSql           = '';

        $duration       = $fn->getReqParam('duration');
        $company_id = intval($fn->getReqParam('company_id'));

        $sqlDateAppend = '';

        $durationArray = array(
            "Current Month"  => "Current Month"
           ,"Previous Month" => "Previous Month"
           ,"Last 3 Months"  => "Last 3 Months"
           ,"Last 6 Months"  => "Last 6 Months"
           ,"Last 9 Months"  => "Last 9 Months"
           ,"Last 12 Months" => "Last 12 Months"
        );
        if($duration == "") {
            $duration = "Current Month";
        }
    
        $thisMonth = date('Y-m');
        $lastMonth = date('Y-m',mktime (0,0,0,date("m")-1,date("d"), date("Y")));

        if($duration == 'Current Month' || $duration == ''){
            $sqlDateAppend = "AND DATE_FORMAT(i.invoice_date, '%Y-%m') = '{$thisMonth}'";
        } else if($duration == 'Previous Month') {
            $sqlDateAppend = "AND DATE_FORMAT(i.invoice_date, '%Y-%m') = '{$lastMonth}'";
        } else {
            if($duration == "Last 3 Months"){
                $monthVal = date('Y-m-d',mktime (0,0,0,date("m")-2,1, date("Y")));
            }
            if($duration == "Last 6 Months"){
                $monthVal = date('Y-m-d',mktime (0,0,0,date("m")-5,1, date("Y")));
            }
            if($duration == "Last 9 Months"){
                $monthVal = date('Y-m-d',mktime (0,0,0,date("m")-8,1, date("Y")));
            }
            if($duration == "Last 12 Months"){
                $monthVal = date('Y-m-d',mktime (0,0,0,date("m")-11,1, date("Y")));
            }
            $today       = date('Y-m-d');
            $sqlDateAppend = "AND (i.invoice_date BETWEEN '{$monthVal}' AND '{$today}')";
        }

        $sqlCompany = "
        SELECT c.company_id
              ,c.company_name
        FROM company c
        WHERE c.company_name != ''
        ORDER BY c.company_name ASC
        ";

        $sqlCompanyAppend = '';
        if ($company_id > 0) {
            $sqlCompanyAppend = "AND o.company_id = {$company_id}";
        }

        $SQL = "
        SELECT i.*
              ,c.company_name
              ,o.record_type
        FROM `invoice` i
        LEFT JOIN (`order` o) ON (o.order_id = i.order_id)
        LEFT JOIN (company c) ON (c.company_id = o.company_id)
        WHERE i.status !='Cancelled'
        {$sqlDateAppend}
        {$sqlCompanyAppend}
        ORDER BY i.invoice_date DESC
        ";
        $result  = $db->sql_query($SQL);

        $total_invoice_amount = 0;
        $total_gst_amount = 0;
        $total_invoice_gst_amount = 0;
        $total_receipt_amount = 0;
        $total_balance_amount = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $current_date = date('Y-m-d');
            $invoice_date = $fn->getCPDate($row['invoice_date'], 'd-m-Y');
            $invoice_due_date = $fn->getCPDate($row['invoice_due_date'], 'd-m-Y');

            $datetime1 = new DateTime($current_date);
            $datetime2 = new DateTime($row['invoice_due_date']);
            $difference = $datetime1->diff($datetime2);
            $noOfDays = '';
            if($row['invoice_due_date'] < $current_date AND $difference->d > 0) {
                $noOfDays = "($difference->d)";
            }

            if ($row['gst_percentage'] > 0) {
                $total = $fn->getAmountFractionFormattedForGst($row['invoice_amount'], $row['gst_percentage']);
            } else {
                $total = $row['invoice_amount'];
            }

            $gst = $total - $row['invoice_amount'];
            $invoice_code = $row['invoice_code'];
            if ($row['invoice_manual_code']) {
                $invoice_code = $row['invoice_manual_code'];
            }

            $sqlRec = "
            SELECT SUM(irh.amount) AS total_invoice_amount_paid
            FROM invoice_receipt_history irh
            LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
            LEFT JOIN (receipt r) ON (irh.receipt_id = r.receipt_id)
            WHERE i.invoice_id = {$row['invoice_id']}
              AND r.receipt_status = 'Paid'
            ";
            $resultRec = $db->sql_query($sqlRec);
            $rowRec    = $db->sql_fetchrow($resultRec);

            $receipt_amount = $rowRec['total_invoice_amount_paid'];
            if ($rowRec['total_invoice_amount_paid'] == '') {
                $receipt_amount = 0;
            }

            $balance = $total - $receipt_amount;

            $formatted_invoice_amount = number_format($row['invoice_amount'], 2);
            $formatted_gst_amount = number_format($gst, 2);
            $formatted_total_amount = number_format($total, 2);
            $formatted_receipt_amount = number_format($receipt_amount, 2);
            $formatted_balance = number_format($balance, 2);

            $rows .= "
            <tr class='purchaseSalesSummary'>
                <td class=''>{$invoice_date}</td>
                <td class=''>{$invoice_due_date} {$noOfDays}</td>
                <td class=''>{$invoice_code}</td>
                <td class='companyName'>{$row['company_name']}</td>
                <td class=''></td>
                <td class='txtRight'>{$formatted_invoice_amount}</td>
                <td class='txtRight'>{$formatted_gst_amount}</td>
                <td class='txtRight'>{$formatted_total_amount}</td>
                <td class='txtRight'>{$formatted_receipt_amount}</td>
                <td class='txtRight'>{$formatted_balance}</td>
            </tr>
            ";

            $total_invoice_amount += $row['invoice_amount'];
            $total_gst_amount += $gst;
            $total_invoice_gst_amount += $total;
            $total_receipt_amount += $receipt_amount;
            $total_balance_amount += $balance;
        }

        $formatted_total_invoice_amount = number_format($total_invoice_amount, 2);
        $formatted_total_gst_amount = number_format($total_gst_amount, 2);
        $formatted_total_invoice_gst_amount = number_format($total_invoice_gst_amount, 2);
        $formatted_total_receipt_amount = number_format($total_receipt_amount, 2);
        $formatted_total_balance_amount = number_format($total_balance_amount, 2);

        $rows .= "
        <tr bgcolor='#90ee90'>
            <th colspan='5' class='txtRight'>TOTAL</th>
            <th class='txtRight'>{$formatted_total_invoice_amount}</th>
            <th class='txtRight'>{$formatted_total_gst_amount}</th>
            <th class='txtRight'>{$formatted_total_invoice_gst_amount}</th>
            <th class='txtRight'>{$formatted_total_receipt_amount}</th>
            <th class='txtRight'>{$formatted_total_balance_amount}</th>
        </tr>
        ";

        $text = "
        <h2 class='ui-widget-header ui-corner-top'>
            <div class='floatbox invoiceSummaryfilter'>
                <div class='float_left'>
                    Invoice Summary
                </div>
                <div class='float_right mb5 ml10'>
                    <select name='company_id' class='invoiceByClientFilter ml10'>
                        <option value=''>Select Company Name</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlCompany, $company_id)}
                    </select>
                </div>
                <div class='float_right mb5 ml10'>
                    <select name='duration'>
                        {$cpUtil->getDropDownFromArr($durationArray, $duration)}
                    </select>
                </div>
            </div>
        </h2>
        <div class = 'tableOuter scroll-pane'>
        <table class='thinlist'>
            <thead>
                <tr bgcolor='#90ee90'>
                    <th class=''>Invoice Date</th>
                    <th class=''>Due Date</th>
                    <th class=''>Invoice No</th>
                    <th class='companyNameLbl'>Company Name</th>
                    <th class='totalSalesLbl'>Invoice Period Date</th>
                    <th class='txtRight'>Invoice Amount</th>
                    <th class='txtRight'>GST</th>
                    <th class='txtRight'>Total</th>
                    <th class='txtRight'>Received</th>
                    <th class='txtRight'>Balance</th>
                </tr>
            </thead>
            {$rows}
        </table>
        </div>
        ";

        // Add client-side handler so selecting a company reloads the widget with the filter
        $script = <<<'SCRIPT'
        <script>
        (function($){
            $(document).ready(function(){
                // when company or duration changes, reload page with appropriate query params
                $(document).on('change', '.invoiceByClientFilter, select[name="duration"]', function(){
                    var params = new URLSearchParams(window.location.search);
                    var company = $('.invoiceByClientFilter').val();
                    var duration = $('select[name="duration"]').val();
                    if (company) params.set('company_id', company); else params.delete('company_id');
                    if (duration) params.set('duration', duration); else params.delete('duration');
                    var url = window.location.href.split('?')[0];
                    window.location = url + '?' + params.toString();
                });
            });
        })(jQuery);
        </script>
SCRIPT;

        return $text . $script;
    }
}