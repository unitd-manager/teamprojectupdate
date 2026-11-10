<?
class CP_Admin_Widgets_Labsg_DailyCollectionReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidgetOld() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if($tv['module'] == 'common_dashboard'){
            $heading = "Daily Collection Report - Current Month";
        }else {
            $heading = "Daily Collection Report";
        }
        $text = "
        <h2>{$heading}</h2>
		<div class = 'tableOuter scroll-pane'>
			<table class='thinlist'>
				<thead>
					<tr>
						<th>Date</th>
                        <th>Bill To</th>
                        <th>Patient Name</th>
                        <th>Treatment Type</th>
						<th class='txtRight'>Invoice Amount</th>
                        <th class='txtRight'>Amount Paid</th>
                        <th class='txtRight'>Balance</th>
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

    function getRowsHTMLOld() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';
		$siteTitle = '' ;
        $totalAmount = 0;
        $totalBalance = 0;
        $totalInvoiceAmount = 0;
        foreach($this->model->dataArray as $row){
			$creationDate = $fn->getCPDate($row['invoice_date'],"d-m-Y");
			$amount = number_format($row['receipt_amount'], 2);
            $balance = ($row['invoice_amount'] - $row['discount']) - $row['receipt_amount'];

            $invoice_amountDis = $row['invoice_amount'] - $row['discount'];
            $totalInvoiceAmount += $invoice_amountDis;
            $invoice_amountDis = number_format($invoice_amountDis, 2);

            $totalAmount += $row['receipt_amount'];
            $totalBalance += $balance;
            $balance = number_format($balance, 2);

            $SQL = "
            SELECT it.*
            FROM invoice_item it
            WHERE it.invoice_id = '{$row['invoice_id']}'
              AND it.record_type = 'Treatment'
            ";
            $result = $db->sql_query($SQL);
            $treatment = '';

            while ($rowIt = $db->sql_fetchrow($result)) {
                $treatment .= $rowIt['item_title'].', ';
            }
            $treatment = rtrim($treatment, ', ');

            if($row['patient_name'] != ''){
                $name = $row['patient_name'];
            }else{
                $name = $row['company_name'];
            }

		    $rows .= "
			<tr>
				<td>{$creationDate}</td>
                <td>{$row['bill_type']}</td>
                <td>{$name}</td>
                <td>{$treatment}</td>
                <td class='txtRight'>{$invoice_amountDis}</td>
				<td class='txtRight'>{$amount}</td>
                <td class='txtRight'>{$balance}</td>
			</tr>
			";
        }

        $totalAmount = number_format($totalAmount, 2);
        $totalBalance = number_format($totalBalance, 2);
        $totalInvoiceAmount = number_format($totalInvoiceAmount, 2);

        $text = "
        {$rows}
        <tr class=''>
            <td class='txtRight lastRowBgColor' colspan='4'>Total</td>
            <td class='txtRight lastRowBgColor'>{$totalInvoiceAmount}</td>
            <td class='txtRight lastRowBgColor'>{$totalAmount}</td>
            <td class='txtRight lastRowBgColor'>{$totalBalance}</td>
        </tr>
        ";

        return $text;
    }

    /**
     *
     */
    function getWidget() {
        $viewHelper = Zend_Registry::get('viewHelper');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;

        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $year         = $fn->getReqParam('year');
        $month        = $fn->getReqParam('month');
        $payment_mode = $fn->getReqParam('payment_mode');

        if ($payment_mode == '') {
            $payment_mode = 'Cash';
        }

        if ($start_date == '' & $end_date != '') {
            $end_date_year = substr($end_date, 0, 8);
            $start_date = $end_date_year . '01';
        } else if ($start_date != '' & $end_date == '') {
            $start_date_year = substr($start_date, 0, 8);
            $end_date = $start_date_year . '31';
        } else if ($start_date == '' & $end_date == ''){
            if ($year == '' || $year == 'null') {
                $year = date('Y');
            }

            if ($month == '') {
                $month = date('m');
            }

            $month_year = $year . '-' . $month . '-';
            $start_date = $month_year . '01';
            $end_date = $month_year . '31';
        }

        $total_bank_transfer_collection = '0.00';
        if ($payment_mode == 'Bank Transfer' || $payment_mode == 'All') {
            $total_bank_transfer_collection = $this->model->getTotalCollectionForPaymentMode('Bank Transfer', $start_date, $end_date);
        }

        $total_cash_collection = '0.00';
        if ($payment_mode == 'Cash' || $payment_mode == 'All') {
            $total_cash_collection = $this->model->getTotalCollectionForPaymentMode('Cash', $start_date, $end_date);
        }

        $total_cheque_collection = '0.00';
        if ($payment_mode == 'Cheque' || $payment_mode == 'All') {
            $total_cheque_collection = $this->model->getTotalCollectionForPaymentMode('Cheque', $start_date, $end_date);
        }

        $total_nets_collection = '0.00';
        if ($payment_mode == 'Nets' || $payment_mode == 'All') {
            $total_nets_collection = $this->model->getTotalCollectionForPaymentMode('Nets', $start_date, $end_date);
        }

        $summary_grand_total = $total_bank_transfer_collection + $total_cash_collection + $total_cheque_collection + $total_nets_collection;

        /* Formattion for Summary dispaly */
        $total_bank_transfer_collection_formatted = number_format($total_bank_transfer_collection, 2);
        $total_cash_collection_formatted = number_format($total_cash_collection, 2);
        $total_cheque_collection_formatted = number_format($total_cheque_collection, 2);
        $total_nets_collection_formatted = number_format($total_nets_collection, 2);
        $summary_grand_total_formatted = number_format($summary_grand_total, 2);

        $start_date_formatted = $dateUtil->formatDate($start_date, 'DD/MM/YYYY');
        $end_date_formatted   = $dateUtil->formatDate($end_date, 'DD/MM/YYYY');
        
        $rowsHTML = $this->getRowsHTML();
        $text = '';
        
        $summaryRec = $this->model->getSqlForCount();
        $grand_total = number_format($summaryRec['grand_total'], 2);

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist summaryTable'>
                <thead>
                    <th colspan='5'>Summary</th>
                </thead>
                <tr>
                    <td>Mode of Payment : {$payment_mode}</td>
                    <td colspan='2'>Payment Start Date : {$start_date_formatted}</td>
                    <td colspan='2'>Payment End Date : {$end_date_formatted}</td>
                </tr>
                <tr>
                    <td>Bank Transfer Total : {$total_bank_transfer_collection_formatted}</td>
                    <td>Cash Total : {$total_cash_collection_formatted}</td>
                    <td>Cheque Total : {$total_cheque_collection_formatted}</td>
                    <td>Nets Total : {$total_nets_collection_formatted}</td>
                    <td>Grand Total : {$summary_grand_total_formatted}</td>
                </tr>
            </table>
            <table class='thinlist mt10'>
                <thead>
                    <tr>
                        <th>Payment Date</th>
                        <th>Invoice Code</th>
                        <th>Receipt Code</th>
                        <th>Bill To</th>
                        <th>Client Name</th>
                        <th>Mode of Payment</th>
                        <th class='txtRight'>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    {$rowsHTML}
                </tbody>
            </table>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dateUtil = Zend_Registry::get('dateUtil');
        
        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $year         = $fn->getReqParam('year');
        $month        = $fn->getReqParam('month');
        $payment_mode = $fn->getReqParam('payment_mode');

        if ($year == '') {
            $year = date('Y');
        }

        if ($month == '') {
            $month = date('m');
        }

        if ($payment_mode == '') {
            $payment_mode = 'Cash';
        }

        if ($start_date == '' & $end_date != '') {
            $end_date_year = substr($end_date, 0, 8);
            $start_date = $end_date_year . '01';
        } else if ($start_date != '' & $end_date == '') {
            $start_date_year = substr($start_date, 0, 8);
            $end_date = $start_date_year . '31';
        } else if ($start_date == '' & $end_date == ''){
            $month_year = $year . '-' . $month . '-';
            $start_date = $month_year . '01';
            $end_date = $month_year . '31';
        }

        $rows = '';
        $print_total = '';
        $grand_total = 0;
        $mode_of_payment = '';
        $previous_mode_of_payment = '';
        $total_for_payment_mode = 0;

        foreach($this->model->dataArray as $row){

            // Printing total amount for each mode of payment. Eg: Cash, Nets, Giro etc
            if ($mode_of_payment == '') {
                $print_total = 0;
                $mode_of_payment = $row['mode_of_payment'];
                $previous_mode_of_payment = $row['mode_of_payment'];
                $total_for_payment_mode = $row['amount'];
            } else if ($mode_of_payment == $row['mode_of_payment']) {
                $print_total = 0;
                $total_for_payment_mode += $row['amount'];
                $mode_of_payment = $row['mode_of_payment'];
                $previous_mode_of_payment = $row['mode_of_payment'];
            } else if ($mode_of_payment != $row['mode_of_payment']) {
                $print_total = 1;
                $mode_of_payment = $row['mode_of_payment'];
                $payment_total = $total_for_payment_mode;
                $total_for_payment_mode = $row['amount'];
            }

            // If Mode of payment changes, printing the total amount for the specific payment mode.
            // Eg: If earlier payment mode was Cash, and for next receipt it is Giro, then printing the total.
            if ($print_total == 1) {
                $payment_total = number_format($payment_total, 2);
                $print_total = "
                <tr>
                    <td colspan='6' class='highlight'>". $previous_mode_of_payment." Total</td>
                    <td class='txtRight highlight'><strong>{$payment_total}</strong></td>
                </tr>
                ";
                $previous_mode_of_payment = $row['mode_of_payment'];
            } else {
                $print_total = "";
            }

            $amount = '';
            $grand_total += $row['amount'];
            $amount = $row['amount'];

            $date = $dateUtil->formatDate($row['date'], 'DD-MM-YYYY');
            if($row['patient_name'] != ''){
                $name = $row['patient_name'];
            }else{
                $name = $row['company_name'];
            }

            $invoice_code = $this->model->getInvoiceCodeForReceipt($row['receipt_id']);

            $displayRow = '';
            if ($amount > 0) {
                $displayRow = "
                <tr>
                    <td>{$date}</td>
                    <td>{$invoice_code}</td>
                    <td>{$row['receipt_code']}</td>
                    <td>{$row['bill_type']}</td>
                    <td>{$name}</td>
                    <td>{$row['mode_of_payment']}</td>
                    <td class='txtRight'>{$amount}</td>
                </tr>
                ";
            }

            $rows .= "
            {$print_total}
            {$displayRow}
            ";
            
        }

        $total_for_payment_mode = number_format($total_for_payment_mode, 2);

        $grand_total_text = '';
        if ($payment_mode == 'All') {
            $grand_total = number_format($grand_total, 2);
            $grand_total_text = "
            <tr>
                <td colspan='6' class='highlight'><strong>Grand Total</strong></td>
                <td class='txtRight highlight'><strong>{$grand_total}</strong></td>
            </tr>
            ";
        }

        $text = "
        {$rows}
        <tr>
            <td colspan='6' class='highlight'>". $previous_mode_of_payment ." Total</td>
            <td class='txtRight highlight'><strong>{$total_for_payment_mode}</strong></td>
        </tr>
        {$grand_total_text}
        <tr>
            <td colspan='7'></td>
        </tr>
        <tr>
            <td colspan='7' class='highlight'><strong>Unbilled visit revenue for Individual</strong></td>
        </tr>
        {$this->getUnbilledVisitForIndividual($start_date, $end_date)}
        <tr>
            <td colspan='7' class='highlight'><strong>Unbilled visit revenue for Company</strong></td>
        </tr>
        {$this->getUnbilledVisitForCompany($start_date, $end_date)}
        ";

        return $text;
    }

    /**
     *
     */
    function getUnbilledVisitForIndividual($start_date, $end_date) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $site_id = $fn->getSessionParam('cp_site_id');

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND pv.site_id = {$site_id}";
        }

        $sql1 = "
        SELECT pv.patient_visit_id
              ,pi.name
        FROM patient_visit pv
        LEFT JOIN (patient_information pi) ON (pv.patient_information_id = pi.patient_information_id)
        WHERE pv.check_up_date BETWEEN '{$start_date}' AND '{$end_date}'
          AND (pv.order_id IS NULL OR pv.order_id = '')
          AND (pi.company_id IS NULL OR pi.company_id = '')
          AND pv.status != 'Cancelled'
          {$appendSql}
        ";
        $result1  = $db->sql_query($sql1);
        $rows = '';
        $total_individual_unbilled = 0;
        while ($row1 = $db->sql_fetchrow($result1)) {
            $sql2 = "
            SELECT SUM(fees) AS total_fees_amount FROM treatment_visit
            WHERE patient_visit_id = {$row1['patient_visit_id']}
            ";
            $result2  = $db->sql_query($sql2);
            $row2 = $db->sql_fetchrow($result2);

            if ($row2['total_fees_amount']) {
                $total_fees_amount = number_format($row2['total_fees_amount'],2);
                $total_individual_unbilled += $row2['total_fees_amount'];

                $rows .= "
                <tr>
                    <td colspan='3'></td>
                    <td>Individual</td>
                    <td>{$row1['name']}</td>
                    <td></td>
                    <td class='txtRight'>{$total_fees_amount}</td>
                </tr>
                ";
            }
        }

        $total_individual_unbilled = number_format($total_individual_unbilled, 2);
        $text = "
        {$rows}
        <tr>
            <td colspan='6' class='highlight'>Total</td>
            <td class='txtRight highlight'><strong>{$total_individual_unbilled}</strong></td>
        </tr>
        ";

        return $text;
    }

    /**
     *
     */
    function getUnbilledVisitForCompany($start_date, $end_date) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $site_id = $fn->getSessionParam('cp_site_id');

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND pv.site_id = {$site_id}";
        }

        /* Finding list of unbilled companies - START */
        $sql1 = "
        SELECT DISTINCT c.company_id
              ,c.company_name
        FROM company c
        LEFT JOIN (patient_information pi) ON (c.company_id = pi.company_id)
        LEFT JOIN (patient_visit pv) ON (pi.patient_information_id = pv.patient_information_id)
        WHERE pv.check_up_date BETWEEN '{$start_date}' AND '{$end_date}'
          AND (pv.order_id IS NULL OR pv.order_id = '')
          AND pi.company_id != ''
          AND pv.status != 'Cancelled'
          {$appendSql}
        ";
        $result1  = $db->sql_query($sql1);
        $rows = '';
        $overall_company_unbilled = 0;
        while ($row1 = $db->sql_fetchrow($result1)) {
            $total_company_unbilled = 0;
            $sql2 = "
            SELECT pv.patient_visit_id
            FROM patient_visit pv
            LEFT JOIN (patient_information pi) ON (pv.patient_information_id = pi.patient_information_id)
            LEFT JOIN (company c) ON (pi.company_id = c.company_id)
            WHERE pv.check_up_date BETWEEN '{$start_date}' AND '{$end_date}'
              AND (pv.order_id IS NULL OR pv.order_id = '')
              AND pi.company_id = '{$row1['company_id']}'
              {$appendSql}
            ";
            $result2  = $db->sql_query($sql2);        
            while ($row2 = $db->sql_fetchrow($result2)) {
                $sql3 = "
                SELECT SUM(fees) AS total_fees_amount FROM treatment_visit
                WHERE patient_visit_id = {$row2['patient_visit_id']}
                ";
                $result3  = $db->sql_query($sql3);
                $row3 = $db->sql_fetchrow($result3);

                if ($row3['total_fees_amount']) {
                    $total_fees_amount = number_format($row3['total_fees_amount'],2);
                    $total_company_unbilled += $row3['total_fees_amount'];
                }
            }

            if ($total_company_unbilled > 0) {
                $overall_company_unbilled += $total_company_unbilled;
                $total_company_unbilled = number_format($total_company_unbilled, 2);
                $rows .= "
                <tr>
                    <td colspan='3'></td>
                    <td>Company</td>
                    <td>{$row1['company_name']}</td>
                    <td></td>
                    <td class='txtRight'>{$total_company_unbilled}</td>
                </tr>
                ";
            }
        }

        $overall_company_unbilled = number_format($overall_company_unbilled, 2);
        $text = "
        {$rows}
        <tr>
            <td colspan='6' class='highlight'>Total</td>
            <td class='txtRight highlight'><strong>{$overall_company_unbilled}</strong></td>
        </tr>
        ";

        return $text;
    }
}