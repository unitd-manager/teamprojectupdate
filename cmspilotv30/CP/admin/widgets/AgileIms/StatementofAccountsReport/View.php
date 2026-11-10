<?
class CP_Admin_Widgets_AgileIms_StatementofAccountsReport_View extends CP_Common_Lib_WidgetViewAbstract
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

        $start_date         = $fn->getReqParam('start_date');
        $end_date           = $fn->getReqParam('end_date');
        $enrollment_type    = $fn->getReqParam('enrollment_type');
        $company_contact_id = $fn->getReqParam('company_contact_id');

        $rowsHTML = $this->getRowsHTML($enrollment_type, $company_contact_id, $start_date, $end_date);
        $text = '';

        $year           = date('Y');
        $month          = date('m');
        $current_date   = date('Y-m-d');

        if ($enrollment_type == 'Individual') {
            $rowCont = $fn->getRecordRowByID('contact', 'contact_id', $company_contact_id);
            $title_in_summary = $rowCont['first_name'];
        } else {
            $rowComp = $fn->getRecordRowByID('company', 'company_id', $company_contact_id);
            $title_in_summary = $rowComp['title'];
        }

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

        $outstandingAmt = $this->model->getPreviousOutstandingBalanceAmount($enrollment_type, $company_contact_id, $start_date) +
                          $this->model->getTotalOutstandingAmount($start_date, $end_date, $enrollment_type, $company_contact_id);

        $previous_amount_formatted = number_format($this->model->getPreviousOutstandingBalanceAmount($enrollment_type, $company_contact_id, $start_date), 2);
        $outstanding_amount_formatted = number_format($outstandingAmt, 2);
        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist summaryTable mb20'>
                <thead>
                    <th colspan='6'>Summary</th>
                </thead>
                <tr>
                    <td>Student/Company Name : {$title_in_summary}</td>
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
                        <th class='txtRight'>Charges (Invoice Amount)</th>
                        <th class='txtRight'>Credits (Receipt Amount)</th>
                        <th class='txtRight'>Account Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan='5' class='txtRight'><b>Previous Outstanding Amount<b></td>
                        <td class='txtRight'><b>{$previous_amount_formatted}</b></td>
                    </tr>

                    {$rowsHTML}

                    <tr>
                        <td colspan='5' style='background:#ccc' class='txtRight'><b>Total Outstanding Amount<b></td>
                        <td class='txtRight' style='background:#ccc'><b>{$outstanding_amount_formatted}</b></td>
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
    function getRowsHTML($enrollment_type = '', $company_contact_id = '', $start_date = '', $end_date = '') {
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

        if ($enrollment_type == 'Individual') {
            $leftJoin = "LEFT JOIN (contact co) ON (co.contact_id = o.contact_id)";
            $sqlWhere = "AND co.contact_id = {$company_contact_id}";
        } else {
            $leftJoin = "LEFT JOIN (company c) ON (c.company_id = o.company_id)";
            $sqlWhere = "AND c.company_id = {$company_contact_id}";
        }

        $sql = "
        (
        SELECT i.invoice_amount AS debit_amount
              ,0 AS credit_amount
              ,i.invoice_date AS date
              ,i.invoice_code AS code
              ,i.creation_date AS creation_date
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        {$leftJoin}
        WHERE i.status != 'Cancelled'
            {$sqlWhere}
            AND i.invoice_date {$appendSql}
        ) UNION (
        SELECT 0 AS debit_amount
              ,r.amount AS credit_amount
              ,r.date AS date
              ,r.receipt_code AS code
              ,r.creation_date AS creation_date
        FROM receipt r
        LEFT JOIN (invoice_receipt_history irh) ON (r.receipt_id = irh.receipt_id)
        LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        {$leftJoin}
        WHERE r.receipt_status != 'Cancelled'
            {$sqlWhere}
            AND r.date {$appendSql}
        )
        ORDER BY date ASC, creation_date ASC
        ";
        $result = $db->sql_query($sql);

        $rows = '';
        $total_outstanding_amount = $this->model->getPreviousOutstandingBalanceAmount($enrollment_type, $company_contact_id, $start_date);
        $serial_no = 1;
        while ($row = $db->sql_fetchrow($result)) {
			$date = $fn->getCPDate($row['date'],"d-m-Y");

            $total_outstanding_amount += $row['debit_amount'] - $row['credit_amount'];
            $debit_amount_formatted  = number_format($row['debit_amount'], 2);
            $credit_amount_formatted = number_format($row['credit_amount'], 2);
            $total_outstanding_amount_formatted = number_format($total_outstanding_amount, 2);

            $rows .= "
            <tr>
				<td>{$serial_no}</td>
				<td>{$date}</td>
				<td>{$row['code']}</td>
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