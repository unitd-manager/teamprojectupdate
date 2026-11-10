<?
class CP_Admin_Widgets_EnggCrm_StatementofAccountsReport_View extends CP_Common_Lib_WidgetViewAbstract
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

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $company_id = $fn->getReqParam('company_id');

        if ($company_id != ""){
            $rowsHTML = $this->getRowsHTML($company_id, $start_date, $end_date);

            $year           = date('Y');
            $month          = date('m');
            $current_date   = date('Y-m-d');

            $rowComp = $fn->getRecordRowByID('company', 'company_id', $company_id);
            $title_in_summary = $rowComp['company_name'];
                    
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
            
            $previous_amount_formatted = number_format($this->model->getPreviousOutstandingBalanceAmount($company_id, $start_date), 2);
            $outstanding_amount_formatted = number_format($outstandingAmt, 2);

            $text = "
            <table class='thinlist summaryTable mb20'>
                <thead>
                    <th colspan='6'>Summary</th>
                </thead>
                <tr>
                    <td>Company Name : {$title_in_summary}</td>
                    <td>Start Date : {$start_date_formatted}</td>
                    <td>End Date : {$end_date_formatted}</td>
                    <td class='txtRight'>Total Outstanding Amount : {$outstanding_amount_formatted}</td>
                </tr>
            </table>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Date</th>
                        <th>Code</th>
                        <th>Reference No/ Mode of payment</th>
                        <th class='txtRight'>Charges (Invoice Amount)</th>
                        <th class='txtRight'>Credits (Receipt Amount)</th>
                        <th class='txtRight'>Account Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan='6' class='txtRight'><b>Previous Outstanding Amount<b></td>
                        <td class='txtRight'><b>{$previous_amount_formatted}</b></td>
                    </tr>

                    {$rowsHTML}

                    <tr>
                        <td colspan='6' style='background:#ccc' class='txtRight'><b>Total Outstanding Amount<b></td>
                        <td class='txtRight' style='background:#ccc'><b>{$outstanding_amount_formatted}</b></td>
                    </tr>
                </tbody>
            </table>
            ";
        } else {
            $text = "<div class='txtCenter'><strong>Please choose a company for Statement of account.</strong></div>";
        }

        return $text;
    }

    /**
     *
     */
    function getRowsHTML($company_id, $start_date, $end_date) {
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
        SELECT (i.invoice_amount + 
                        ((i.invoice_amount * i.gst_percentage) / 100)
                    )
               AS debit_amount
              ,0 AS credit_amount
              ,i.invoice_date AS date
              ,i.invoice_code AS code
              ,i.invoice_id AS ref_no
              ,i.creation_date AS creation_date
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        LEFT JOIN (company c) ON (c.company_id = o.company_id)
        WHERE i.status != 'Cancelled'
            AND c.company_id = {$company_id}
            AND i.invoice_date {$appendSql}
        ) UNION (
        SELECT 0 AS debit_amount
              ,r.amount AS credit_amount
              ,r.date AS date
              ,r.receipt_code AS code
              ,r.receipt_id AS ref_no
              ,r.creation_date AS creation_date
        FROM receipt r
        LEFT JOIN (invoice_receipt_history irh) ON (r.receipt_id = irh.receipt_id)
        LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        LEFT JOIN (company c) ON (c.company_id = o.company_id)
        WHERE r.receipt_status != 'Cancelled'
            AND c.company_id = {$company_id}
            AND r.date {$appendSql}
        )
        ORDER BY date ASC, creation_date ASC
        ";
        $result = $db->sql_query($sql);

        $rows = '';
        $total_outstanding_amount = $this->model->getPreviousOutstandingBalanceAmount($company_id, $start_date);
        $serial_no = 1;
        while ($row = $db->sql_fetchrow($result)) {
			$date = $fn->getCPDate($row['date'],"d-m-Y");
            $invoice_code = substr($row['code'], 2);
            
            $total_outstanding_amount += $row['debit_amount'] - $row['credit_amount'];
            $debit_amount_formatted  = number_format($row['debit_amount'], 2);
            $credit_amount_formatted = number_format($row['credit_amount'], 2);
            $total_outstanding_amount_formatted = number_format($total_outstanding_amount, 2);

            $ref_no = '';
            if ($row['credit_amount'] > 0) {
                $sqlReceiptData = "SELECT re.mode_of_payment
                                         ,inv.invoice_id
                                         ,pr.project_code
                                   FROM receipt re
                                   LEFT JOIN (invoice_receipt_history irhr) ON (re.receipt_id = irhr.receipt_id)
                                   LEFT JOIN (invoice inv) ON (irhr.invoice_id = inv.invoice_id)
                                   LEFT JOIN (`order` ord) ON (inv.order_id = ord.order_id)
                                   LEFT JOIN (project pr) ON (ord.project_id = pr.project_id)
                                   WHERE re.receipt_id = {$row['ref_no']}";
                $resultReceiptData = $db->sql_query($sqlReceiptData);
                $rowReceiptData = $db->sql_fetchrow($resultReceiptData);

                $ref_no = '[' . $rowReceiptData['mode_of_payment'] . '] [' . $rowReceiptData['invoice_id'] . '] [' . $rowReceiptData['project_code'] . ']';
            } else {
                $ref_no = $row['ref_no'];
            }
            
            $rows .= "
            <tr>
				<td>{$serial_no}</td>
				<td>{$date}</td>
				<td>PT/{$invoice_code}</td>
                <td>{$ref_no}</td>
				<td class='txtRight'>{$debit_amount_formatted}</td>
				<td class='txtRight'>{$credit_amount_formatted}</td>
				<td class='txtRight'>{$total_outstanding_amount_formatted}</td>
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