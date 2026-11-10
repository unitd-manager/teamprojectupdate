<?
class CPL_Admin_Modules_EnggCrm_Receipt_View extends CP_Admin_Modules_EnggCrm_Receipt_View
{
    /**
     *
     */
    function getGenerateReceiptForm() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $order_id = $fn->getReqParam('order_id');

        $rows = '';        
        $today = date('Y-m-d');

        $SQL = "
        SELECT i.*
            ,(
            SELECT SUM(invHist.amount) AS prev_sum
            FROM invoice_receipt_history invHist
            LEFT JOIN receipt r ON (r.receipt_id = invHist.receipt_id)
            WHERE invHist.invoice_id =  i.invoice_id
            AND r.receipt_status != 'Cancelled'
            ) as prev_inv_amount
        FROM invoice i
        LEFT JOIN `order` o ON (i.order_id = o.order_id)
        WHERE i.order_id = {$order_id}
            AND (i.status = 'Due' || i.status = 'Partial Payment' || i.status = 'Late')
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows == 0) {
            return "Sorry no invoice is available or all the invoices are paid" ;
        }

        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {

          
                $total_invoice_amount = round(($row['invoice_amount']), 3);

                if($row['discount'] > 0){
                    $total_invoice_amount = round(($row['invoice_amount'] - $row['discount']), 3);
                }
            
            $prev_payment_amt = number_format($row['prev_inv_amount'],3);

            //$inv_date = $fn->getCPDate($row['invoice_date'], 'ym/');
            //$invoice_code = $inv_date . substr($row['invoice_code'], 2);

            /* Finding total Credit Note Amount from History table */
            $sqlCn = "
            SELECT SUM(icnh.amount) AS total_credit_note_amt FROM invoice_credit_note_history icnh
            WHERE icnh.invoice_id = {$row['invoice_id']}
            ";
            $resultCn   = $db->sql_query($sqlCn);
            $rowCn = $db->sql_fetchrow($resultCn);
            $credit_note_amt = $rowCn['total_credit_note_amt'];

            /* Calculating Average GST percentage for credit note */
            $sqlCnGstCalc = "
            SELECT cn.gst_percentage
            FROM credit_note cn
            LEFT JOIN (invoice_credit_note_history icnh) ON (cn.credit_note_id = icnh.credit_note_id)
            WHERE icnh.invoice_id  = {$row['invoice_id']}
            ";
            $resultCnGstCalc  = $db->sql_query($sqlCnGstCalc);
            $numRowsCnGstCalc = $db->sql_numrows($resultCnGstCalc);
            $gst_amount = 0;
            if ($numRowsCnGstCalc) {
                $total_gst_percentage = 0;
                while ($rowCnGstCalc = $db->sql_fetchrow($resultCnGstCalc)) {
                    $total_gst_percentage += $rowCnGstCalc['gst_percentage'];
                }    

                $gst_percentage = ($total_gst_percentage / $numRowsCnGstCalc);

                $gst_amount = round((($credit_note_amt * $gst_percentage)/100),3);
                /* Taking two decimal values for gst amount */
                $fraction_length = strlen(substr(strrchr($gst_amount, "."), 1)); // Checking the length of the fraction value
                if ($fraction_length > 2) {
                    list($integer, $fraction) = explode(".", (string) $gst_amount);
                    $fraction = substr($fraction, 0, 2);
                    $gst_amount = $integer . "." . $fraction;
                }
            }

            $totalvalueRounded = $credit_note_amt + $gst_amount;

            $rows .= "
            <div class='form-row-wrapper'>
                <div class='floatbox'>
                    <div class='float_left'>
                        <input type='checkbox' order_id='{$order_id}' name='invoiceCode[]' value='{$row['invoice_code']}' class='invoiceCode'>
                    </div>
                    <div class='float_left'>{$row['invoice_code']} ({$total_invoice_amount})</div>
                    <div class=''>Paid: {$prev_payment_amt}</div>
                </div>
            </div>
            ";
            $count++;
        }

        $formAction = "index.php?_topRm=finance&module=enggCrm_receipt&_spAction=generateReceiptFormSubmit&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar receiptForm' method='post' action='{$formAction}'>
            <h3>Please select Invoice</h3>
            {$rows}
            {$formObj->getTBRow('Amount', 'amount')}
            {$formObj->getDateRow('Date', 'receipt_date', date('Y-m-d'))}
            {$formObj->getDDRowByVL('Mode of Payment', 'mode_of_payment',  'paymentType')}
            {$formObj->getTBRow('Cheque No', 'cheque_no', '', array('rowCls' => 'hideme'))}
            {$formObj->getDateRow('Cheque date', 'cheque_date', '', array('rowCls' => 'hideme'))}
            {$formObj->getTBRow('Bank', 'bank_name', '', array('rowCls' => 'hideme'))}
            {$formObj->getTextAreaRow('Note', 'remarks')}
            <input type='hidden' name='order_id' value='{$order_id}' />
        </form>
        ";

        return $text;
    }


    function getGenerateReceiptFormRenewal() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $order_id = $fn->getReqParam('order_id');

        $rows = '';        
        $today = date('Y-m-d');

        $SQL = "
        SELECT i.*
            ,(
            SELECT SUM(invHist.amount) AS prev_sum
            FROM invoice_receipt_history invHist
            LEFT JOIN receipt r ON (r.receipt_id = invHist.receipt_id)
            WHERE invHist.invoice_id =  i.invoice_id
            AND r.receipt_status != 'Cancelled'
            ) as prev_inv_amount
        FROM invoice i
        LEFT JOIN `order` o ON (i.order_id = o.order_id)
        WHERE i.order_id = {$order_id}
            AND (i.status = 'Due' || i.status = 'Partial Payment' || i.status = 'Late')
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows == 0) {
            return "Sorry no invoice is available or all the invoices are paid" ;
        }

        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {

          
                $total_invoice_amount = round(($row['invoice_amount']), 3);

                if($row['discount'] > 0){
                    $total_invoice_amount = round(($row['invoice_amount'] - $row['discount']), 3);
                }
            
            $prev_payment_amt = number_format($row['prev_inv_amount'],3);

            //$inv_date = $fn->getCPDate($row['invoice_date'], 'ym/');
            //$invoice_code = $inv_date . substr($row['invoice_code'], 2);

            /* Finding total Credit Note Amount from History table */
            $sqlCn = "
            SELECT SUM(icnh.amount) AS total_credit_note_amt FROM invoice_credit_note_history icnh
            WHERE icnh.invoice_id = {$row['invoice_id']}
            ";
            $resultCn   = $db->sql_query($sqlCn);
            $rowCn = $db->sql_fetchrow($resultCn);
            $credit_note_amt = $rowCn['total_credit_note_amt'];

            /* Calculating Average GST percentage for credit note */
            $sqlCnGstCalc = "
            SELECT cn.gst_percentage
            FROM credit_note cn
            LEFT JOIN (invoice_credit_note_history icnh) ON (cn.credit_note_id = icnh.credit_note_id)
            WHERE icnh.invoice_id  = {$row['invoice_id']}
            ";
            $resultCnGstCalc  = $db->sql_query($sqlCnGstCalc);
            $numRowsCnGstCalc = $db->sql_numrows($resultCnGstCalc);
            $gst_amount = 0;
            if ($numRowsCnGstCalc) {
                $total_gst_percentage = 0;
                while ($rowCnGstCalc = $db->sql_fetchrow($resultCnGstCalc)) {
                    $total_gst_percentage += $rowCnGstCalc['gst_percentage'];
                }    

                $gst_percentage = ($total_gst_percentage / $numRowsCnGstCalc);

                $gst_amount = round((($credit_note_amt * $gst_percentage)/100),3);
                /* Taking two decimal values for gst amount */
                $fraction_length = strlen(substr(strrchr($gst_amount, "."), 1)); // Checking the length of the fraction value
                if ($fraction_length > 2) {
                    list($integer, $fraction) = explode(".", (string) $gst_amount);
                    $fraction = substr($fraction, 0, 2);
                    $gst_amount = $integer . "." . $fraction;
                }
            }

            $totalvalueRounded = $credit_note_amt + $gst_amount;

            $rows .= "
            <div class='form-row-wrapper'>
                <div class='floatbox'>
                    <div class='float_left'>
                        <input type='checkbox' order_id='{$order_id}' name='invoiceCode[]' value='{$row['invoice_code']}' class='invoiceCode'>
                    </div>
                    <div class='float_left'>{$row['invoice_code']} ({$total_invoice_amount})</div>
                    <div class=''>Paid: {$prev_payment_amt}</div>
                </div>
            </div>
            ";
            $count++;
        }

        $formAction = "index.php?_topRm=finance&module=enggCrm_receipt&_spAction=generateReceiptFormSubmit&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar receiptForm' method='post' action='{$formAction}'>
            <h3>Please select Invoice</h3>
            {$rows}
            {$formObj->getTBRow('Amount', 'amount')}
            {$formObj->getDateRow('Date', 'receipt_date', date('Y-m-d'))}
            {$formObj->getDDRowByVL('Mode of Payment', 'mode_of_payment',  'paymentType')}
            {$formObj->getTBRow('Cheque No', 'cheque_no', '', array('rowCls' => 'hideme'))}
            {$formObj->getDateRow('Cheque date', 'cheque_date', '', array('rowCls' => 'hideme'))}
            {$formObj->getTBRow('Bank', 'bank_name', '', array('rowCls' => 'hideme'))}
            {$formObj->getTextAreaRow('Note', 'remarks')}
            <input type='hidden' name='order_id' value='{$order_id}' />
        </form>
        ";

        return $text;
    }
}