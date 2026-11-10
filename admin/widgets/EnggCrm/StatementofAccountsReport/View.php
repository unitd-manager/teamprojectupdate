<?
class CPL_Admin_Widgets_EnggCrm_StatementofAccountsReport_View extends CP_Common_Lib_WidgetViewAbstract
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

        $start_date  = $fn->getReqParam('start_date');
        $end_date    = $fn->getReqParam('end_date');
        $company_id  = $fn->getReqParam('company_id');
        $client_type = $fn->getReqParam('client_type');
        
        if ($company_id == '') {
            return "<div align='center'><strong>Please choose Type</strong></div>";
        }

        $rowsHTML = $this->getRowsHTML($company_id, $start_date, $end_date, $client_type);
        $text = '';

        $year           = date('Y');
        $month          = date('m');
        $current_date   = date('Y-m-d');
        
        if($client_type == 'Client') {
            $rowComp = $fn->getRecordRowByID('company', 'company_id', $company_id);
        } else if($client_type == 'Supplier') {
            $rowComp = $fn->getRecordRowByID('supplier', 'supplier_id', $company_id);
        }
        
        if ($start_date) {
            $start_date_formatted = $dateUtil->formatDate($start_date, 'DD-MM-YYYY');
        } else {
            $start_date = $year . '-' . $month . '-' . '01';
            $start_date_formatted = $dateUtil->formatDate($start_date, 'DD-MM-YYYY');
        }
        
        if ($end_date) {
            $end_date_formatted   = $dateUtil->formatDate($end_date, 'DD-MM-YYYY');
        } else {
            $end_date_formatted   = $dateUtil->formatDate($current_date, 'DD-MM-YYYY');
        }

        $outstandingAmt = $this->model->getPreviousOutstandingBalanceAmount($company_id, $start_date, $client_type) + 
                          $this->model->getTotalOutstandingAmount($start_date, $end_date, $company_id, $client_type);
        $outstandingAmt = number_format($outstandingAmt, 2);
        
        if ($rowsHTML != ""){
            $previousBalanceFormatted = number_format($this->model->getPreviousOutstandingBalanceAmount($company_id, $start_date, $client_type), 2);
            
            if($client_type == 'Client') {
                $companyLabel = 'Company';
            } else if($client_type == 'Supplier') {
                $companyLabel = 'Supplier';
            }  

            $text = "
            <table class='thinlist summaryTable mb20'>
                <thead>
                    <th colspan='6'>Summary</th>
                </thead>
                <tr>
                    <td><b>{$companyLabel} :</b> {$rowComp['company_name']}</td>
                    <td><b>Start Date :</b> {$start_date_formatted}</td>
                    <td><b>End Date :</b> {$end_date_formatted}</td>
                    <td class='txtRight'><b>Total Outstanding Amount :</b> {$outstandingAmt}</td>
                </tr>
            </table>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Code</th>
                        <th class='txtRight'>Charges (Invoice Amount)</th>
                        <th class='txtRight'>Credits (Receipt Amount)</th>
                        <th>Payment Mode</th>
                        <th class='txtRight'>Account Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan='7' class='txtRight'><b>Previous Outstanding Amount<b></td>
                        <td class='txtRight'><b>{$previousBalanceFormatted}</b></td>
                    </tr>

                    {$rowsHTML}

                    <tr>
                        <td colspan='7' style='background:#ccc' class='txtRight'><b>Total Outstanding Amount<b></td>
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
        LEFT JOIN (invoice i) ON (i.invoice_id = irh.invoice_id)
        LEFT JOIN (`order` o) ON (o.order_id   = i.order_id)
        WHERE r.receipt_status != 'Cancelled'
            AND o.company_id = {$company_id}
            AND {$appendSql}
        ORDER BY r.date ASC
        ";
        $receiptResult = $db->sql_query($receiptSQL);
        $receiptDetails = '';
        
        while ($receiptRow = $db->sql_fetchrow($receiptResult)) {
            $total_outstanding_amount = $total_outstanding_amount - $receiptRow['receipt_amount'];
            $receipt_date = $fn->getCPDate($receiptRow['receipt_date'],"d-m-Y");
            $total_outstanding_amount_formatted = number_format($total_outstanding_amount, 2);

            $receiptDetails .= "
            <tr>
                <td>{$receipt_date}</td>
                <td>{$receiptRow['receipt_code']}</td>
                <td class='txtRight'>-</td>
                <td class='txtRight'>{$receiptRow['receipt_amount']}</td>
                <td class='txtRight'>{$total_outstanding_amount_formatted}</td>
            </tr>
            ";
        }

        return $receiptDetails;
    }

    /**
     *
     */
    function getRowsHTML($company_id = '', $start_date = '', $end_date = '', $client_type = '') {
        $fn    = Zend_Registry::get('fn');
        $db    = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        
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

        $rows = '';
        
        if($client_type == 'Client') {
            $sql = "
            (
            SELECT i.invoice_amount AS debit_amount
                  ,0 AS credit_amount
                  ,i.invoice_date AS date
                  ,i.invoice_code AS code
                  ,0 AS payment_mode
                  ,0 AS bank_cheque_no
                  ,0 AS bank_cheque_date
                  ,p.title AS project_title
                  ,i.gst_percentage AS GSTPercentage
            FROM invoice i
            LEFT JOIN (`order` o) ON (o.order_id = i.order_id)
            LEFT JOIN (project p) ON (p.project_id = o.project_id)
            WHERE i.status != 'Cancelled'
                AND o.company_id = {$company_id}
                AND i.invoice_date {$appendSql}
            ) UNION (
            SELECT 0 AS debit_amount
                  ,r.amount AS credit_amount
                  ,r.date AS date
                  ,r.receipt_code AS code
                  ,r.mode_of_payment AS payment_mode
                  ,r.cheque_no AS bank_cheque_no
                  ,r.cheque_date AS bank_cheque_date
                  ,p.title AS project_title
                  ,0 AS GSTPercentage
            FROM receipt r
            LEFT JOIN (invoice_receipt_history irh) ON (r.receipt_id = irh.receipt_id)
            LEFT JOIN (invoice i) ON (i.invoice_id = irh.invoice_id)
            LEFT JOIN (`order` o) ON (o.order_id   = i.order_id)
            LEFT JOIN (project p) ON (p.project_id = o.project_id)
            WHERE r.receipt_status != 'Cancelled'
                AND o.company_id = {$company_id}
                AND r.date {$appendSql}
            )
            ORDER BY date ASC
            ";
            $result = $db->sql_query($sql);

            $total_outstanding_amount = $this->model->getPreviousOutstandingBalanceAmount($company_id, $start_date, $client_type);
            $serial_no = 1;
            $bank_cheque_date = '';
            while ($row = $db->sql_fetchrow($result)) {
                $date = $fn->getCPDate($row['date'],"d-m-Y");
                if ($row['bank_cheque_date']) {
                    $bank_cheque_date = $fn->getCPDate($row['bank_cheque_date'],"d-m-Y");
                }
                
                if ($row['GSTPercentage'] > 0) {
                    $debit_amount = $fn->getAmountFractionFormattedForGst($row['debit_amount'], $cpCfg['cp.gstPercentage']);
                } else {
                    $debit_amount = $row['debit_amount'];
                }
                
                $total_outstanding_amount += $debit_amount - $row['credit_amount'];

                if ($row['payment_mode'] == '0') {
                    $payment_mode = '';
                } else {
                    if ($row['payment_mode'] == 'Cheque') {
                        $payment_mode = $row['payment_mode'] . ' - ' . $row['bank_cheque_no'] . ' (' . $bank_cheque_date . ')';
                    } else {
                        $payment_mode = $row['payment_mode'];
                    }
                }

                $debit_amount  = number_format($debit_amount, 2);
                $credit_amount = number_format($row['credit_amount'], 2);
                $total_outstanding_amount_formatted = number_format($total_outstanding_amount, 2);
                
                $rows .= "
                <tr>
                    <td>{$serial_no}</td>
                    <td>{$date}</td>
                    <td>{$row['project_title']}</td>
                    <td>{$row['code']}</td>
                    <td class='txtRight'>{$debit_amount}</td>
                    <td class='txtRight'>{$credit_amount}</td>
                    <td>{$payment_mode}</td>
                    <td class='txtRight'>{$total_outstanding_amount_formatted}</td>
                </tr>
                ";

                $serial_no++;
            }
        } else if($client_type == 'Supplier') {
            $sql = "
            (
            SELECT (IFNULL(e.amount, 0) + IFNULL(e.gst_amount, 0) + IFNULL(e.service_charge, 0)) AS debit_amount
                  ,0 AS credit_amount
                  ,e.date AS date
                  ,e.po_code AS code
                  ,0 AS payment_mode
                  ,0 AS bank_cheque_no
                  ,0 AS bank_cheque_date
                  ,e.description AS project_title
            FROM expense e
            WHERE e.company_id = {$company_id}
                AND e.date {$appendSql}
            ) UNION (
            SELECT 0 AS debit_amount
                  ,p.amount AS credit_amount
                  ,p.date AS date
                  ,p.payment_code AS code
                  ,p.mode_of_payment AS payment_mode
                  ,p.cheque_no AS bank_cheque_no
                  ,p.cheque_date AS bank_cheque_date
                  ,e.description AS project_title
            FROM payment p
            LEFT JOIN (expense e) ON (e.expense_id = p.record_id)
            WHERE e.company_id = {$company_id}
                AND p.payment_status != 'Cancelled'
                AND p.date {$appendSql}
            )
            ORDER BY date ASC
            ";
            $result = $db->sql_query($sql);

            $total_outstanding_amount = $this->model->getPreviousOutstandingBalanceAmount($company_id, $start_date, $client_type);
            $serial_no = 1;
            $bank_cheque_date = '';
            while ($row = $db->sql_fetchrow($result)) {
                $date = $fn->getCPDate($row['date'],"d-m-Y");
                if ($row['bank_cheque_date']) {
                    $bank_cheque_date = $fn->getCPDate($row['bank_cheque_date'],"d-m-Y");
                }
                
                $debit_amount = $row['debit_amount'];
                $total_outstanding_amount += $debit_amount - $row['credit_amount'];

                if ($row['payment_mode'] == '0') {
                    $payment_mode = '';
                } else {
                    if ($row['payment_mode'] == 'Cheque') {
                        $payment_mode = $row['payment_mode'] . ' - ' . $row['bank_cheque_no'] . ' (' . $bank_cheque_date . ')';
                    } else {
                        $payment_mode = $row['payment_mode'];
                    }
                }

                $debit_amount  = number_format($debit_amount, 2);
                $credit_amount = number_format($row['credit_amount'], 2);
                $total_outstanding_amount_formatted = number_format($total_outstanding_amount, 2);
                
                $rows .= "
                <tr>
                    <td>{$serial_no}</td>
                    <td>{$date}</td>
                    <td>{$row['project_title']}</td>
                    <td>{$row['code']}</td>
                    <td class='txtRight'>{$debit_amount}</td>
                    <td class='txtRight'>{$credit_amount}</td>
                    <td>{$payment_mode}</td>
                    <td class='txtRight'>{$total_outstanding_amount_formatted}</td>
                </tr>
                ";

                $serial_no++;
            }
        }

        $text = "
        {$rows}
        ";

        return $text;
    }
}