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

        $text = "
        <table class='thinlist'>
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Company Name</th>
                    <th class='txtRight'>1-30 Days Due (SGD)</th>
                    <th class='txtRight'>31-60 Days Due (SGD)</th>
                    <th class='txtRight'>Above 60 Days Due (SGD)</th>
                    <th class='txtRight'>Overall Due (SGD)</th>
                    <th class='txtRight'>Export</th>
                </tr>
            </thead>
            <tbody>
                {$this->getRowsHTML()}
            </tbody>
        </table>
        ";

        return $text;
    }

    /**
     *
     */
    function getRowsHTML1() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

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

    /**
     *
     */
    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dateUtil = Zend_Registry::get('dateUtil');

        $rows = '';
        $count = 1;
        foreach($this->model->dataArray as $row){
           if ($this->getOverallDueForCompany($row['company_id']) > 0) {

                $overallDue          = $this->getOverallDueForCompany($row['company_id']);
                $overallDueFormatted = number_format($overallDue, 2);
                $export = "index.php?module=enggCrm_invoice&_spAction=printStatementOfAccount&company_id={$row['company_id']}&showHTML=0";

                $modObj = getCPModuleObj('enggCrm_invoice');
                $current_date    = date('Y-m-d');
                $current_date    = $fn->getCPDate($current_date, 'd M Y');
                #$days30_due      = number_format($modObj->model->getPastBalanceAmountStatementofAccount($row['company_id'], $current_date, 30), 2);
                $days60_due      = $modObj->model->getPastBalanceAmountStatementofAccount($row['company_id'], $current_date, 60);
                $moredays60_due  = $modObj->model->getPastBalanceAmountStatementofAccount($row['company_id'], $current_date, 61);
                $days30_due      = $overallDue - $days60_due - $moredays60_due;

                $days30_due_formatted      = number_format($days30_due, 2);
                $days60_due_formatted      = number_format($days60_due, 2);
                $moredays60_due_formatted  = number_format($moredays60_due, 2);

                $rows .= "
                <tr>
                    <td>{$count}</td>
                    <td>{$row['company_name']}</td>
                    <td class='txtRight'>{$days30_due_formatted}</td>
                    <td class='txtRight'>{$days60_due_formatted}</td>
                    <td class='txtRight'>{$moredays60_due_formatted}</td>
                    <td class='txtRight'>{$overallDueFormatted}</td>
                    <td class='txtRight'><a href='{$export}'>Export to Excel</a></td>
                </tr>
                ";
                $count++;
            }
        }

        $text = "
        {$rows}
        ";

        return $text;
    }

    /**
     *
     */
    function getInvoiceIdsForCompany($company_id) {
        $db = Zend_Registry::get('db');

        $current_date = date('Y-m-d');
        
        $sql = "
        SELECT DISTINCT i.invoice_id
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        WHERE o.company_id = {$company_id}
          AND (i.status = 'Due'
           OR i.status = 'Partial Payment'
           OR i.status = 'Late')
           AND i.invoice_date <= '{$current_date}'
        ";

        $result  = $db->sql_query($sql);
        $numRows = $db->sql_numrows($result);
        $count   = 1;
        
        $rowsInvoice = '';
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == $numRows) {
                $rowsInvoice .= $row['invoice_id'];
            } else {
                $rowsInvoice .= $row['invoice_id'] . ',';
            }
            $count++;
        }
        
        return $rowsInvoice;
    }

    /**
     *
     */
    function getOverallDueForCompany($company_id) {
        $db = Zend_Registry::get('db');
        
        $invoice_ids = $this->getInvoiceIdsForCompany($company_id);
        
        $total_amt_payable = 0;
        if ($invoice_ids) {
            $sqlInv = "
            SELECT i.*
            FROM invoice i
            WHERE i.invoice_id IN ({$invoice_ids})
            ";
            $resultInv = $db->sql_query($sqlInv);
            $total_inv_amt_after_discount = 0;
            $balance_amount = 0;
            while ($rowInv = $db->sql_fetchrow($resultInv)) {
                $invoice_amount_after_disc = $rowInv['invoice_amount'] - $rowInv['discount'];
                $gst_amount = 0;
                if ($rowInv['gst_percentage']) {
                    $gst_amount = round((($invoice_amount_after_disc * $rowInv['gst_percentage']) / 100), 2);
                }

                // Finding total Credit Note Amount from History table
                $sqlCn = "
                SELECT SUM(icnh.amount) AS total_credit_note_amount FROM invoice_credit_note_history icnh
                WHERE icnh.invoice_id = '{$rowInv['invoice_id']}'
                ";
                $resultCn  = $db->sql_query($sqlCn);
                $numRowsCn = $db->sql_numrows($resultCn);
                $rowCn = $db->sql_fetchrow($resultCn);
                $credit_note_amt = $rowCn['total_credit_note_amount'];

                // Calculating Average GST percentage for credit note
                $sqlCnGstCalc = "
                SELECT cn.gst_percentage
                FROM credit_note cn
                LEFT JOIN (invoice_credit_note_history icnh) ON (cn.credit_note_id = icnh.credit_note_id)
                WHERE icnh.invoice_id  = {$rowInv['invoice_id']}
                ";
                $resultCnGstCalc  = $db->sql_query($sqlCnGstCalc);
                $numRowsCnGstCalc = $db->sql_numrows($resultCnGstCalc);
                $gst_amount_cn = 0;
                if ($numRowsCnGstCalc) {
                    $total_gst_percentage_cn = 0;
                    while ($rowCnGstCalc = $db->sql_fetchrow($resultCnGstCalc)) {
                        $total_gst_percentage_cn += $rowCnGstCalc['gst_percentage'];
                    }            
                    $gst_percentage_cn = ($total_gst_percentage_cn/$numRowsCnGstCalc);

                    $gst_amount_cn = round((($credit_note_amt * $gst_percentage_cn)/100),2);
                    // Taking two decimal values for gst amount
                    $fraction_length = strlen(substr(strrchr($gst_amount_cn, "."), 1)); // Checking the length of the fraction value
                    if ($fraction_length > 2) {
                        list($integer, $fraction) = explode(".", (string) $gst_amount_cn);
                        $fraction = substr($fraction, 0, 2);
                        $gst_amount_cn = $integer . "." . $fraction;
                    }
                }

                $total_inv_amt_after_discount += $invoice_amount_after_disc + $gst_amount - $credit_note_amt - $gst_amount_cn;
            }
            
            $sqlRec = "
            SELECT SUM(irh.amount) AS total_invoice_amount_paid
            FROM invoice_receipt_history irh
            LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
            LEFT JOIN (receipt r) ON (irh.receipt_id = r.receipt_id)
            WHERE i.invoice_id IN ({$invoice_ids})
              AND r.receipt_status = 'Paid'
            ";
            $resultRec = $db->sql_query($sqlRec);
            $rowRec    = $db->sql_fetchrow($resultRec);
    
            $total_amt_payable = $total_inv_amt_after_discount - $rowRec['total_invoice_amount_paid'];
        }            

        return $total_amt_payable;
    }
}