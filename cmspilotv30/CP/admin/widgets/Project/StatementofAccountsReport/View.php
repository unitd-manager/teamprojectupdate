<?
class CP_Admin_Widgets_Project_StatementofAccountsReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $viewHelper = Zend_Registry::get('viewHelper');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');
        $db = Zend_Registry::get('db');
        $c = &$this->controller;

        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $company_id     = $fn->getReqParam('company_id');

        if ($company_id == '') {
            return "<div align='center'><strong>Please choose company</strong></div>";
        }

        $rowsHTML = $this->getRowsHTML($company_id, $start_date, $end_date);
        $text = '';

        $year           = date('Y');
        $month          = date('m');
        $current_date   = date('Y-m-d');
        
        $rowComp = $fn->getRecordRowByID('company', 'company_id', $company_id);
        
        if ($start_date) {
            $start_date_formatted = $dateUtil->formatDate($start_date, 'DD/MM/YYYY');
        } else {
            $start_date = $year . '-' . $month . '-' . '01';
            $start_date_formatted = $dateUtil->formatDate($start_date, 'DD/MM/YYYY');
        }
        
        if ($end_date) {
            $end_date_formatted   = $dateUtil->formatDate($end_date, 'DD/MM/YYYY');
        } else {
            $end_date_formatted   = $dateUtil->formatDate($current_date, 'DD/MM/YYYY');
        }

        $outstandingAmt = $this->model->getPreviousOutstandingBalanceAmount($company_id, $start_date) + 
                          $this->model->getTotalOutstandingAmount($start_date, $end_date, $company_id);
        
        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist summaryTable mb20'>
                <thead>
                    <th colspan='6'>Summary</th>
                </thead>
                <tr>
                    <td>Company : {$rowComp['company_name']}</td>
                    <td>Start Date : {$start_date_formatted}</td>
                    <td>End Date : {$end_date_formatted}</td>
                    <td>Total Outstanding Amount : {$outstandingAmt}</td>
                </tr>
            </table>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Date</th>
                        <th>Code</th>
                        <th class='txtRight'>Charges (Invoice Amount)</th>
                        <th class='txtRight'>Credits (Receipt Amount)</th>
                        <th>Payment Mode</th>
                        <th class='txtRight'>Account Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan='6' class='txtRight'><b>Previous Outstanding Amount<b></td>
                        <td class='txtRight'><b>{$this->model->getPreviousOutstandingBalanceAmount($company_id, $start_date)}</b></td>
                    </tr>

                    {$rowsHTML}

                    <tr>
                        <td colspan='6' style='background:#ccc' class='txtRight'><b>Total Outstanding Amount<b></td>
                        <td class='txtRight' style='background:#ccc'><b>{$outstandingAmt}</b></td>
                    </tr>
                </tbody>
            </table>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getRowsHTMLOld($company_id, $start_date) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $rows = '';
        $total_outstanding_amount = $this->model->getPreviousOutstandingBalanceAmount($company_id, $start_date);
        $invoiceCount = 1;
        foreach($this->model->dataArray as $row){
			$invoice_date = $fn->getCPDate($row['invoice_date'],"d-m-Y");
            
            $total_outstanding_amount += $row['invoice_amount'];
            
            $rows .= "
            <tr>
				<td>{$invoice_date}</td>
				<td>{$row['invoice_code']}</td>
				<td class='txtRight'>{$row['invoice_amount']}</td>
				<td class='txtRight'>-</td>
				<td class='txtRight'>{$total_outstanding_amount}</td>
            </tr>
            ";
            $invoiceCount ++;
        }
        
        $text = "
        {$rows}
        {$this->getReceiptForSearchDate($total_outstanding_amount)}
        ";

        return $text;
    }

    /**
     *
     */
    function getReceiptForSearchDate($total_outstanding_amount) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $company_id = $fn->getReqParam('company_id');

        $appendSql = '';
        
        $current_date = date('Y-m-d');
        $year  = date('Y');
        $month = date('m');

        if ($start_date != '' && $end_date == '') {
            $appendSql .= "r.date BETWEEN '{$start_date}' AND '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $appendSql .= "r.date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $appendSql .= "r.date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else {
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $appendSql .= "r.date BETWEEN '{$start_date}' AND '{$end_date}'";
        }

        $receiptSQL = "
        SELECT r.receipt_id
              ,r.amount AS receipt_amount
              ,r.receipt_code
              ,r.date AS receipt_date
        FROM receipt r
        LEFT JOIN (invoice_receipt_history irh) ON (r.receipt_id = irh.receipt_id)
        LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
        LEFT JOIN (project p) ON (i.project_id   = p.project_id)
        LEFT JOIN (company c) ON (c.company_id   = p.company_id)
        WHERE r.receipt_status != 'Cancelled'
            AND c.company_id = {$company_id}
            AND {$appendSql}
        ORDER BY r.date ASC
        ";
        $receiptResult = $db->sql_query($receiptSQL);
        $receiptDetails = '';
        
        while ($receiptRow = $db->sql_fetchrow($receiptResult)) {
            $total_outstanding_amount = $total_outstanding_amount - $receiptRow['receipt_amount'];
		    $receipt_date = $fn->getCPDate($receiptRow['receipt_date'],"d-m-Y");           

            $receiptDetails .= "
            <tr>
				<td>{$receipt_date}</td>
				<td>{$receiptRow['receipt_code']}</td>
				<td class='txtRight'>-</td>
				<td class='txtRight'>{$receiptRow['receipt_amount']}</td>
				<td class='txtRight'>{$total_outstanding_amount}</td>
            </tr>
            ";
        }

        return $receiptDetails;
    }

    /**
     *
     */
    function getRowsHTML($company_id = '', $start_date = '', $end_date = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        
        $appendSql = '';
        
        $current_date = date('Y-m-d');
        $year  = date('Y');
        $month = date('m');

        if ($start_date != '' && $end_date == '') {
            $appendSql .= "BETWEEN '{$start_date}' AND '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $appendSql .= "BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $appendSql .= "BETWEEN '{$start_date}' AND '{$end_date}'";
        } else {
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $appendSql .= "BETWEEN '{$start_date}' AND '{$end_date}'";
        }

        $sql = "
        (
        SELECT i.invoice_amount AS debit_amount
              ,0 AS credit_amount
              ,i.invoice_date AS date
              ,i.invoice_code AS code
              ,0 AS payment_mode
              ,0 AS bank_cheque_no
              ,0 AS bank_cheque_date
        FROM invoice i
        LEFT JOIN (project p) ON (i.project_id = p.project_id)
        LEFT JOIN (company c) ON (c.company_id = p.company_id)
        WHERE i.status != 'Cancelled'
            AND c.company_id = {$company_id}
            AND i.invoice_date {$appendSql}
        ) UNION (
        SELECT 0 AS debit_amount
              ,r.amount AS credit_amount
              ,r.date AS date
              ,r.receipt_code AS code
              ,r.mode_of_payment AS payment_mode
              ,r.cheque_no AS bank_cheque_no
              ,r.cheque_date AS bank_cheque_date
        FROM receipt r
        LEFT JOIN (invoice_receipt_history irh) ON (r.receipt_id = irh.receipt_id)
        LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
        LEFT JOIN (project p) ON (i.project_id   = p.project_id)
        LEFT JOIN (company c) ON (c.company_id   = p.company_id)
        WHERE r.receipt_status != 'Cancelled'
            AND c.company_id = {$company_id}
            AND r.date {$appendSql}
        )
        ORDER BY date ASC
        ";
        $result = $db->sql_query($sql);

        $rows = '';
        $total_outstanding_amount = $this->model->getPreviousOutstandingBalanceAmount($company_id, $start_date);
        $serial_no = 1;
        while ($row = $db->sql_fetchrow($result)) {
			$date = $fn->getCPDate($row['date'],"d-m-Y");
            $bank_cheque_date = $fn->getCPDate($row['bank_cheque_date'],"d-m-Y");
            
            $total_outstanding_amount += $row['debit_amount'] - $row['credit_amount'];

            if ($row['payment_mode'] == '0') {
                $payment_mode = '';
            } else {
                if ($row['payment_mode'] == 'Cheque') {
                    $payment_mode = $row['payment_mode'] . ' - ' . $row['bank_cheque_no'] . ' (' . $bank_cheque_date . ')';
                } else {
                    $payment_mode = $row['payment_mode'];
                }
            }
            
            $rows .= "
            <tr>
				<td>{$serial_no}</td>
				<td>{$date}</td>
				<td>{$row['code']}</td>
				<td class='txtRight'>{$row['debit_amount']}</td>
				<td class='txtRight'>{$row['credit_amount']}</td>
                <td>{$payment_mode}</td>
				<td class='txtRight'>{$total_outstanding_amount}</td>
            </tr>
            ";
            $serial_no++;
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}