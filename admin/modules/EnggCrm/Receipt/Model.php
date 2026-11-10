<?
class CPL_Admin_Modules_EnggCrm_Receipt_Model extends CP_Admin_Modules_EnggCrm_Receipt_Model
{
    /**
     */
    function getGenerateReceiptFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        if (!$this->getGenerateReceiptFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $order_id        = $fn->getReqParam('order_id');
        $invoiceCodes    = $fn->getPostParam('invoiceCode', array());
        $amount          = $fn->getPostParam('amount');
        $receipt_date    = $fn->getPostParam('receipt_date');
        $mode_of_payment = $fn->getPostParam('mode_of_payment');
        $cheque_no       = $fn->getPostParam('cheque_no');
        $cheque_date     = $fn->getPostParam('cheque_date');
        $bank_name       = $fn->getPostParam('bank_name');
        $remarks         = $fn->getPostParam('remarks');

        $receipt_code = $this->getGenerateReceiptCode();
        
        $current_date = date("Y-m-d H:i:s");

        $fa = array();
        $fa['order_id']       = $order_id;
        $fa['receipt_code']   = $receipt_code;
        $fa['amount']         = $amount;
        $fa['date']           = $receipt_date;
        $fa['mode_of_payment']= $mode_of_payment;
        $fa['cheque_no']      = $cheque_no;
        $fa['cheque_date']    = $cheque_date;
        $fa['bank_name']      = $bank_name;
        $fa['remarks']        = $remarks;
        $fa['receipt_status'] = 'Paid';
        $fa['creation_date']  = $current_date;
        $fa['created_by']     = $fn->getSessionParam('userName');
        
        $insertReceiptSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'receipt');
        $resultSQL          = $db->sql_query($insertReceiptSQL);
        $receipt_id         = $db->sql_nextid();
        $receipt_amount     = $amount;

        // $SQLUpdate       = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode'";
        // $resultUpdate    = $db->sql_query($SQLUpdate);
        $nextReceiptCode = $fn->getSettingsValueByKey("nextReceiptCode");
        
        foreach($invoiceCodes AS $invoice_code){
            $invoiceRec = $fn->getRecordByCondition('invoice', "invoice_code = '{$invoice_code}' AND order_id = '{$order_id}'");

            if ($invoiceRec['gst_percentage']) {
                $gst_amount = ($invoiceRec['invoice_amount']*$invoiceRec['gst_percentage'])/100;
                /* Taking two decimal values for gst amount */
                $fraction_length = strlen(substr(strrchr($gst_amount, "."), 1)); // Checking the lingth of the fraction value
                if ($fraction_length > 2) {
                    list($integer, $fraction) = explode(".", (string) $gst_amount);
                    $fraction = substr($fraction, 0, 2);
                    $gst_amount = $integer . "." . $fraction;
                }
                //$gst_amount     = round($gst_amount, 2);
                $invoice_amount = round(($invoiceRec['invoice_amount']), 2);

                if($invoiceRec['discount'] > 0){
                    $gst_amount = (($invoiceRec['invoice_amount'] - $invoiceRec['discount']) * $invoiceRec['gst_percentage'])/100;
                    /* Taking two decimal values for gst amount */
                    $fraction_length = strlen(substr(strrchr($gst_amount, "."), 1)); // Checking the lingth of the fraction value
                    if ($fraction_length > 2) {
                        list($integer, $fraction) = explode(".", (string) $gst_amount);
                        $fraction = substr($fraction, 0, 2);
                        $gst_amount = $integer . "." . $fraction;
                    }
                    //$gst_amount     = round($gst_amount, 2);
                    $invoice_amount = round(($invoiceRec['invoice_amount'] - $invoiceRec['discount']), 2);
                }

            } else {
                $invoice_amount = round(($invoiceRec['invoice_amount']), 2);

                if($invoiceRec['discount'] > 0){
                    $invoice_amount = round(($invoiceRec['invoice_amount'] - $invoiceRec['discount']), 2);
                }
            }

            //$invoice_amount  = $invoiceRec['invoice_amount'];
            $invoice_id      = $invoiceRec['invoice_id'];
            
            if ($invoiceRec['status'] == 'Paid') {
                continue;
            }

            $SQLPaid = "
            SELECT SUM(irh.amount) AS prev_sum
            FROM invoice_receipt_history irh
            LEFT JOIN (receipt r) ON (irh.receipt_id = r.receipt_id)
            WHERE irh.invoice_id = '{$invoice_id}'
            AND r.receipt_status = 'Paid'
            ";
            $resultPaid = $db->sql_query($SQLPaid);
            $rowPaid    = $db->sql_fetchrow($resultPaid);
            
            /* Finding total Credit Note Amount from History table */
            $sqlCn = "
            SELECT SUM(icnh.amount) AS total_credit_note_amt FROM invoice_credit_note_history icnh
            LEFT JOIN (invoice i) ON (icnh.invoice_id = i.invoice_id)
            WHERE icnh.invoice_id = '{$invoice_id}'
            ";
            $resultCn   = $db->sql_query($sqlCn);
            $rowCn = $db->sql_fetchrow($resultCn);
            $credit_note_amt = $rowCn['total_credit_note_amt'];

            /* Calculating Average GST percentage for credit note */
            $sqlCnGstCalc = "
            SELECT cn.gst_percentage
            FROM credit_note cn
            LEFT JOIN (invoice_credit_note_history icnh) ON (cn.credit_note_id = icnh.credit_note_id)
            LEFT JOIN (invoice i) ON (icnh.invoice_id = i.invoice_id)
            WHERE icnh.invoice_id  = {$invoice_id}
            ";
            $resultCnGstCalc  = $db->sql_query($sqlCnGstCalc);
            $numRowsCnGstCalc = $db->sql_numrows($resultCnGstCalc);
            $numRowsCnGstCalc = $db->sql_numrows($resultCnGstCalc);
            $gst_amount_cn = 0;
            if ($numRowsCnGstCalc) {
                $total_gst_percentage_cn = 0;
                while ($rowCnGstCalc = $db->sql_fetchrow($resultCnGstCalc)) {
                    $total_gst_percentage_cn += $rowCnGstCalc['gst_percentage'];
                }            
                $gst_percentage_cn = ($total_gst_percentage_cn/$numRowsCnGstCalc);

                $gst_amount_cn = round((($credit_note_amt * $gst_percentage_cn)/100),2);
                /* Taking two decimal values for gst amount */
                $fraction_length = strlen(substr(strrchr($gst_amount_cn, "."), 1)); // Checking the length of the fraction value
                if ($fraction_length > 2) {
                    list($integer, $fraction) = explode(".", (string) $gst_amount_cn);
                    $fraction = substr($fraction, 0, 2);
                    $gst_amount_cn = $integer . "." . $fraction;
                }
            }

            $minus_amount = $rowPaid['prev_sum'] + $credit_note_amt + $gst_amount_cn;

            $invoice_amount = $invoice_amount - $minus_amount;

            $faInv = array();
            $recpInvAmount = 0;
            if ($invoice_amount <= $receipt_amount){
                $recpInvAmount = $invoice_amount;
                $faInv['status'] = 'Paid';
            } else if ($invoice_amount > $receipt_amount){
                $recpInvAmount = $receipt_amount;
                $faInv['status'] = 'Partial Payment';
            }
            $receipt_amount = $receipt_amount - $recpInvAmount;
            $receipt_amount = round($receipt_amount, 2);
            $fn->saveRecord($faInv, 'invoice', 'invoice_id', $invoice_id);

            //Inserting receipt id in to history table (one invoice can have multiple receipts)
            $fa = array();
            $fa['receipt_id']    = $receipt_id;
            $fa['invoice_id']    = $invoice_id;
            $fa['amount']        = $recpInvAmount;
            $fa['creation_date'] = date("Y-m-d H:i:s");
            $histId = $fn->addRecord($fa, 'invoice_receipt_history');
        }

        /* Updating Order Status to Paid */
        // Finding total order amount
        $sqlOrderAmt = "
        SELECT SUM(oi.qty * oi.unit_price) AS total_order_amount
        FROM order_item oi
        WHERE oi.order_id = {$order_id}
        ";
        $resultOrderAmt = $db->sql_query($sqlOrderAmt);
        $rowOrderAmt    = $db->sql_fetchrow($resultOrderAmt);

        // Finding total amount paid for order
        $sqlRecAmt = "
        SELECT SUM(amount) AS total_receipt_amount
        FROM receipt
        WHERE order_id = {$order_id}
          AND receipt_status = 'Paid'
        ";
        $resultRecAmt = $db->sql_query($sqlRecAmt);
        $rowRecAmt    = $db->sql_fetchrow($resultRecAmt);

        if ($rowOrderAmt['total_order_amount'] == $rowRecAmt['total_receipt_amount']) {
            $updateSql = "
            UPDATE `order`
            SET order_status = 'Paid'
               ,modification_date = '{$current_date}'
               ,modified_by = '{$fn->getSessionParam('userName')}'
            WHERE order_id = {$order_id}
            ";
            $db->sql_query($updateSql);
        }

        return $validate->getSuccessMessageXML();
    }


     /**
     */
    function getGenerateReceiptFormSubmitRenewal() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        if (!$this->getGenerateReceiptFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $order_id        = $fn->getReqParam('order_id');
        $invoiceCodes    = $fn->getPostParam('invoiceCode', array());
        $amount          = $fn->getPostParam('amount');
        $receipt_date    = $fn->getPostParam('receipt_date');
        $mode_of_payment = $fn->getPostParam('mode_of_payment');
        $cheque_no       = $fn->getPostParam('cheque_no');
        $cheque_date     = $fn->getPostParam('cheque_date');
        $bank_name       = $fn->getPostParam('bank_name');
        $remarks         = $fn->getPostParam('remarks');

        $receipt_code = $this->getGenerateReceiptCode();
        
        $current_date = date("Y-m-d H:i:s");

        $fa = array();
        $fa['order_id']       = $order_id;
        $fa['receipt_code']   = $receipt_code;
        $fa['amount']         = $amount;
        $fa['date']           = $receipt_date;
        $fa['mode_of_payment']= $mode_of_payment;
        $fa['cheque_no']      = $cheque_no;
        $fa['cheque_date']    = $cheque_date;
        $fa['bank_name']      = $bank_name;
        $fa['remarks']        = $remarks;
        $fa['receipt_status'] = 'Paid';
        $fa['creation_date']  = $current_date;
        $fa['created_by']     = $fn->getSessionParam('userName');
        
        $insertReceiptSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'receipt');
        $resultSQL          = $db->sql_query($insertReceiptSQL);
        $receipt_id         = $db->sql_nextid();
        $receipt_amount     = $amount;

        $SQLUpdate       = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode'";
        $resultUpdate    = $db->sql_query($SQLUpdate);
        $nextReceiptCode = $fn->getSettingsValueByKey("nextReceiptCode");
        
        foreach($invoiceCodes AS $invoice_code){
            $invoiceRec = $fn->getRecordByCondition('invoice', "invoice_code = '{$invoice_code}' AND order_id = '{$order_id}'");

            if ($invoiceRec['gst_percentage']) {
                $gst_amount = ($invoiceRec['invoice_amount']*$invoiceRec['gst_percentage'])/100;
                /* Taking two decimal values for gst amount */
                $fraction_length = strlen(substr(strrchr($gst_amount, "."), 1)); // Checking the lingth of the fraction value
                if ($fraction_length > 2) {
                    list($integer, $fraction) = explode(".", (string) $gst_amount);
                    $fraction = substr($fraction, 0, 2);
                    $gst_amount = $integer . "." . $fraction;
                }
                //$gst_amount     = round($gst_amount, 2);
                $invoice_amount = round(($invoiceRec['invoice_amount'] + $gst_amount), 2);

                if($invoiceRec['discount'] > 0){
                    $gst_amount = (($invoiceRec['invoice_amount'] - $invoiceRec['discount']) * $invoiceRec['gst_percentage'])/100;
                    /* Taking two decimal values for gst amount */
                    $fraction_length = strlen(substr(strrchr($gst_amount, "."), 1)); // Checking the lingth of the fraction value
                    if ($fraction_length > 2) {
                        list($integer, $fraction) = explode(".", (string) $gst_amount);
                        $fraction = substr($fraction, 0, 2);
                        $gst_amount = $integer . "." . $fraction;
                    }
                    //$gst_amount     = round($gst_amount, 2);
                    $invoice_amount = round(($invoiceRec['invoice_amount'] - $invoiceRec['discount'] + $gst_amount), 2);
                }

            } else {
                $invoice_amount = round(($invoiceRec['invoice_amount']), 2);

                if($invoiceRec['discount'] > 0){
                    $invoice_amount = round(($invoiceRec['invoice_amount'] - $invoiceRec['discount']), 2);
                }
            }

            //$invoice_amount  = $invoiceRec['invoice_amount'];
            $invoice_id      = $invoiceRec['invoice_id'];
            
            if ($invoiceRec['status'] == 'Paid') {
                continue;
            }

            $SQLPaid = "
            SELECT SUM(irh.amount) AS prev_sum
            FROM invoice_receipt_history irh
            LEFT JOIN (receipt r) ON (irh.receipt_id = r.receipt_id)
            WHERE irh.invoice_id = '{$invoice_id}'
            AND r.receipt_status = 'Paid'
            ";
            $resultPaid = $db->sql_query($SQLPaid);
            $rowPaid    = $db->sql_fetchrow($resultPaid);
            
            /* Finding total Credit Note Amount from History table */
            $sqlCn = "
            SELECT SUM(icnh.amount) AS total_credit_note_amt FROM invoice_credit_note_history icnh
            LEFT JOIN (invoice i) ON (icnh.invoice_id = i.invoice_id)
            WHERE icnh.invoice_id = '{$invoice_id}'
            ";
            $resultCn   = $db->sql_query($sqlCn);
            $rowCn = $db->sql_fetchrow($resultCn);
            $credit_note_amt = $rowCn['total_credit_note_amt'];

            /* Calculating Average GST percentage for credit note */
            $sqlCnGstCalc = "
            SELECT cn.gst_percentage
            FROM credit_note cn
            LEFT JOIN (invoice_credit_note_history icnh) ON (cn.credit_note_id = icnh.credit_note_id)
            LEFT JOIN (invoice i) ON (icnh.invoice_id = i.invoice_id)
            WHERE icnh.invoice_id  = {$invoice_id}
            ";
            $resultCnGstCalc  = $db->sql_query($sqlCnGstCalc);
            $numRowsCnGstCalc = $db->sql_numrows($resultCnGstCalc);
            $numRowsCnGstCalc = $db->sql_numrows($resultCnGstCalc);
            $gst_amount_cn = 0;
            if ($numRowsCnGstCalc) {
                $total_gst_percentage_cn = 0;
                while ($rowCnGstCalc = $db->sql_fetchrow($resultCnGstCalc)) {
                    $total_gst_percentage_cn += $rowCnGstCalc['gst_percentage'];
                }            
                $gst_percentage_cn = ($total_gst_percentage_cn/$numRowsCnGstCalc);

                $gst_amount_cn = round((($credit_note_amt * $gst_percentage_cn)/100),2);
                /* Taking two decimal values for gst amount */
                $fraction_length = strlen(substr(strrchr($gst_amount_cn, "."), 1)); // Checking the length of the fraction value
                if ($fraction_length > 2) {
                    list($integer, $fraction) = explode(".", (string) $gst_amount_cn);
                    $fraction = substr($fraction, 0, 2);
                    $gst_amount_cn = $integer . "." . $fraction;
                }
            }

            $minus_amount = $rowPaid['prev_sum'] + $credit_note_amt + $gst_amount_cn;

            $invoice_amount = $invoice_amount - $minus_amount;

            $faInv = array();
            $recpInvAmount = 0;
            if ($invoice_amount <= $receipt_amount){
                $recpInvAmount = $invoice_amount;
                $faInv['status'] = 'Paid';
            } else if ($invoice_amount > $receipt_amount){
                $recpInvAmount = $receipt_amount;
                $faInv['status'] = 'Partial Payment';
            }
            $receipt_amount = $receipt_amount - $recpInvAmount;
            $receipt_amount = round($receipt_amount, 2);
            $fn->saveRecord($faInv, 'invoice', 'invoice_id', $invoice_id);

            //Inserting receipt id in to history table (one invoice can have multiple receipts)
            $fa = array();
            $fa['receipt_id']    = $receipt_id;
            $fa['invoice_id']    = $invoice_id;
            $fa['amount']        = $recpInvAmount;
            $fa['creation_date'] = date("Y-m-d H:i:s");
            $histId = $fn->addRecord($fa, 'invoice_receipt_history');
        }

        /* Updating Order Status to Paid */
        // Finding total order amount
        $sqlOrderAmt = "
        SELECT SUM(oi.qty * oi.unit_price) AS total_order_amount
        FROM order_item oi
        WHERE oi.order_id = {$order_id}
        ";
        $resultOrderAmt = $db->sql_query($sqlOrderAmt);
        $rowOrderAmt    = $db->sql_fetchrow($resultOrderAmt);

        // Finding total amount paid for order
        $sqlRecAmt = "
        SELECT SUM(amount) AS total_receipt_amount
        FROM receipt
        WHERE order_id = {$order_id}
          AND receipt_status = 'Paid'
        ";
        $resultRecAmt = $db->sql_query($sqlRecAmt);
        $rowRecAmt    = $db->sql_fetchrow($resultRecAmt);

        if ($rowOrderAmt['total_order_amount'] == $rowRecAmt['total_receipt_amount']) {
            $updateSql = "
            UPDATE `order`
            SET order_status = 'Paid'
               ,modification_date = '{$current_date}'
               ,modified_by = '{$fn->getSessionParam('userName')}'
            WHERE order_id = {$order_id}
            ";
            $db->sql_query($updateSql);
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getGenerateReceiptFormValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $invoice_amount = '';
        $invoice_prev_amount = '';
        $balance_amount = '';
        $amount          = $fn->getPostParam('amount');
        $discount_amount = $fn->getPostParam('discount_amount');
        $invoiceCodes    = $fn->getPostParam('invoiceCode', array());
        $order_id        = $fn->getReqParam('order_id');

        $validate->resetErrorArray();
        if(count($invoiceCodes) == 0){
            $validate->validateData('amount' , 'Please choose the invoice(s) to be paid');
        }
        //==================================================================
        $invoiceCodesArr = join(',', $invoiceCodes);
        $sessionExplode  = explode(',', $invoiceCodesArr);
        
        $counter = 1;
        $count = count($sessionExplode);
        
        $invoice_code = '';
        foreach ($sessionExplode as $invoiceCode) {
            if ($count == $counter) {
                $invoice_code .= "'" . $invoiceCode . "'";
            } else {
                $invoice_code .= "'" . $invoiceCode . "',";
            }
            $counter++;
        }

        if ($invoice_code != ''){
            /* Finding total invoice amount of selected invoices */
            $SQL = "
            SELECT SUM(invoice_amount) as invoice_sum
            FROM invoice
            WHERE order_id = {$order_id}
              AND invoice_code IN ($invoice_code)
            ";
            $resultPaid = $db->sql_query($SQL);
            $rowPaid    = $db->sql_fetchrow($resultPaid);

            /* Finding average gst percentage for the invoices chosen */
            $sqlGstCalc = "
            SELECT SUM(gst_percentage) AS total_gst_percentage
            FROM invoice
            WHERE invoice_code IN ({$invoice_code})
              AND order_id = {$order_id}
            ";
            $resultGstCalc  = $db->sql_query($sqlGstCalc);
            $rowGstCalc     = $db->sql_fetchrow($resultGstCalc);
            $gst_percentage = ($rowGstCalc['total_gst_percentage']/$count);
            $gst_amount = round((($rowPaid['invoice_sum'] * $gst_percentage)/100),2);
            /* Taking two decimal values for gst amount */
            $fraction_length = strlen(substr(strrchr($gst_amount, "."), 1)); // Checking the lingth of the fraction value
            if ($fraction_length > 2) {
                list($integer, $fraction) = explode(".", (string) $gst_amount);
                $fraction = substr($fraction, 0, 2);
                $gst_amount = $integer . "." . $fraction;
            }
            $total_invoice_amount = $rowPaid['invoice_sum'] + $gst_amount;

            /* Finding total amount paid earlier of selected invoices */
            $SQLPaid = "
            SELECT SUM(irh.amount) as prev_sum
            FROM invoice_receipt_history irh
            LEFT JOIN (receipt r) ON (irh.receipt_id = r.receipt_id)
            WHERE invoice_id IN (
                SELECT invoice_id
                FROM invoice
                WHERE order_id = {$order_id}
                  AND invoice_code IN ($invoice_code)
                )
            AND r.receipt_status = 'Paid'
            ";
            $resultPaid = $db->sql_query($SQLPaid);
            $rowPaid    = $db->sql_fetchrow($resultPaid);
            $prev_sum   = $rowPaid['prev_sum'];
            
            /* FINDING CREDIT NOTE AMOUNT AND GST AMOUNT */
            /* Finding total Credit Note Amount from History table */
            $sqlCn = "
            SELECT SUM(icnh.amount) AS total_credit_note_amt FROM invoice_credit_note_history icnh
            LEFT JOIN (invoice i) ON (icnh.invoice_id = i.invoice_id)
            WHERE invoice_code IN ({$invoice_code})
            ";
            $resultCn   = $db->sql_query($sqlCn);
            $rowCn = $db->sql_fetchrow($resultCn);
            $credit_note_amt = $rowCn['total_credit_note_amt'];

            /* Calculating Average GST percentage for credit note */
            $sqlCnGstCalc = "
            SELECT cn.gst_percentage
            FROM credit_note cn
            LEFT JOIN (invoice_credit_note_history icnh) ON (cn.credit_note_id = icnh.credit_note_id)
            LEFT JOIN (invoice i) ON (icnh.invoice_id = i.invoice_id)
            WHERE invoice_code IN ({$invoice_code})
            ";
            $resultCnGstCalc  = $db->sql_query($sqlCnGstCalc);
            $numRowsCnGstCalc = $db->sql_numrows($resultCnGstCalc);
            $numRowsCnGstCalc = $db->sql_numrows($resultCnGstCalc);
            $gst_amount_cn = 0;
            if ($numRowsCnGstCalc) {
                $total_gst_percentage_cn = 0;
                while ($rowCnGstCalc = $db->sql_fetchrow($resultCnGstCalc)) {
                    $total_gst_percentage_cn += $rowCnGstCalc['gst_percentage'];
                }            
                $gst_percentage_cn = ($total_gst_percentage_cn/$numRowsCnGstCalc);

                $gst_amount_cn = round((($credit_note_amt * $gst_percentage_cn)/100),2);
                /* Taking two decimal values for gst amount */
                $fraction_length = strlen(substr(strrchr($gst_amount_cn, "."), 1)); // Checking the length of the fraction value
                if ($fraction_length > 2) {
                    list($integer, $fraction) = explode(".", (string) $gst_amount_cn);
                    $fraction = substr($fraction, 0, 2);
                    $gst_amount_cn = $integer . "." . $fraction;
                }
            }
            $total_credit_note_amount = $credit_note_amt + $gst_amount_cn;

            $invoice_amount = round(($total_invoice_amount - $prev_sum - $total_credit_note_amount), 2);
            $total_receipt_amount = $amount;

            if ($total_receipt_amount > $invoice_amount){
                $validate->errorArray['amount']['name'] = "amount";
                $validate->errorArray['amount']['msg']  = 'You can input a maximum of ' . $invoice_amount . ' in amount for chosen invoices';
            }
        }
        
        $validate->validateData('invoiceCode' , 'Please check invoice code');
        $validate->validateData('amount' , 'Please enter the amount');
        $validate->validateData('mode_of_payment' , 'Please select mode of payment');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */

    function getGenerateReceiptCode()
{
    $db = Zend_Registry::get('db');
    $fn = Zend_Registry::get('fn');

    $site_id = $fn->getSessionParam('cp_site_id');

    // Main Receipt Prefix
    $SQL = "
        SELECT value
        FROM setting
        WHERE key_text = 'receiptCodePrefix'
        LIMIT 1
    ";
    $result = $db->sql_query($SQL);
    $row = $db->sql_fetchrow($result);

    $receiptPrefix = !empty($row['value']) ? $row['value'] : 'REC-';

    // Country Prefix
    switch ($site_id) {

        case 1:
            // Kuwait
            $prefixKey = 'receiptCodePrefixKWT';
            break;

        case 2:
            // Saudi Arabia
            $prefixKey = 'receiptCodePrefixKSA';
            break;

        default:
            $prefixKey = 'receiptCodePrefixDefault';
            break;
    }

    // Get Country Prefix
    $SQL = "
        SELECT value
        FROM setting
        WHERE key_text = '{$prefixKey}'
        LIMIT 1
    ";
    $result = $db->sql_query($SQL);
    $row = $db->sql_fetchrow($result);

    $countryPrefix = !empty($row['value']) ? $row['value'] : '';

    // Get Common Next Receipt Number
    $SQL = "
        SELECT value
        FROM setting
        WHERE key_text = 'nextReceiptCode'
        LIMIT 1
    ";
    $result = $db->sql_query($SQL);
    $row = $db->sql_fetchrow($result);

    $nextReceiptCode = !empty($row['value']) ? $row['value'] : 1;

    // Format number
    $formattedNumber = str_pad($nextReceiptCode, 4, '0', STR_PAD_LEFT);

    // Generate Receipt Code
    $receiptCode = $receiptPrefix . $countryPrefix . $formattedNumber;

    // Increment Common Counter
    $SQL = "
        UPDATE setting
        SET value = value + 1
        WHERE key_text = 'nextReceiptCode'
    ";
    $db->sql_query($SQL);

    return $receiptCode;
}
 /**
     *
     */
    function getPopulateReceiptAmount() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $invoice_code = $fn->getReqParam('invoice_code');
        $checkedVal = $fn->getReqParam('checkedVal');
        $order_id = $fn->getReqParam('order_id');

        if ($checkedVal == 1) {
            $_SESSION['selectedInvoiceIds'][] = $invoice_code;
        } else if ($checkedVal == 0) {
            $s = &$_SESSION['selectedInvoiceIds'];
            if(($key = array_search($invoice_code, $s)) !== false){
                unset($s[$key]);
            }
        }

        if(count($_SESSION['selectedInvoiceIds']) == 0) {
            return 0;
        }

        $selectInvoiceIds = join(',', $_SESSION['selectedInvoiceIds']);
        $sessionExplode = explode(',', $selectInvoiceIds);

        $counter = 1;
        $count = count($sessionExplode);

        $invoice_code = '';
        foreach ($sessionExplode as $invoiceCode) {
            if ($count == $counter) {
                $invoice_code .= "'" . $invoiceCode . "'";
            } else {
                $invoice_code .= "'" . $invoiceCode . "',";
            }
            $counter++;
        }

        $SQLPaid = "
        SELECT SUM(invoice_amount) as total
              ,SUM(discount) AS discount
        FROM invoice
        WHERE invoice_code IN ({$invoice_code})
          AND order_id = {$order_id}
        ";
        $resultPaid = $db->sql_query($SQLPaid);
        $rowPaid    = $db->sql_fetchrow($resultPaid);

        /* Finding average gst percentage for the invoices chosen */
        $sqlGstCalc = "
        SELECT SUM(gst_percentage) AS total_gst_percentage
        FROM invoice
        WHERE invoice_code IN ({$invoice_code})
          AND order_id = {$order_id}
        ";
        $resultGstCalc  = $db->sql_query($sqlGstCalc);
        $rowGstCalc     = $db->sql_fetchrow($resultGstCalc);
        $gst_percentage = ($rowGstCalc['total_gst_percentage']/$count);
        $gst_amount     = round((($rowPaid['total'] * $gst_percentage)/100),2);
        /* Taking two decimal values for gst amount */
        $fraction_length = strlen(substr(strrchr($gst_amount, "."), 1)); // Checking the lingth of the fraction value
        if ($fraction_length > 2) {
            list($integer, $fraction) = explode(".", (string) $gst_amount);
            $fraction = substr($fraction, 0, 2);
            $gst_amount = $integer . "." . $fraction;
        }

        /* FINDING CREDIT NOTE AMOUNT AND GST AMOUNT */
        /* Finding total Credit Note Amount from History table */
        $sqlCn = "
        SELECT SUM(icnh.amount) AS total_credit_note_amt FROM invoice_credit_note_history icnh
        LEFT JOIN (invoice i) ON (icnh.invoice_id = i.invoice_id)
        WHERE invoice_code IN ({$invoice_code})
        ";
        $resultCn   = $db->sql_query($sqlCn);
        $rowCn = $db->sql_fetchrow($resultCn);
        $credit_note_amt = $rowCn['total_credit_note_amt'];

        /* Calculating Average GST percentage for credit note */
        $sqlCnGstCalc = "
        SELECT cn.gst_percentage
        FROM credit_note cn
        LEFT JOIN (invoice_credit_note_history icnh) ON (cn.credit_note_id = icnh.credit_note_id)
        LEFT JOIN (invoice i) ON (icnh.invoice_id = i.invoice_id)
        WHERE invoice_code IN ({$invoice_code})
        ";
        $resultCnGstCalc  = $db->sql_query($sqlCnGstCalc);
        $numRowsCnGstCalc = $db->sql_numrows($resultCnGstCalc);
        $numRowsCnGstCalc = $db->sql_numrows($resultCnGstCalc);
        $gst_amount_cn = 0;
        if ($numRowsCnGstCalc) {
            $total_gst_percentage_cn = 0;
            while ($rowCnGstCalc = $db->sql_fetchrow($resultCnGstCalc)) {
                $total_gst_percentage_cn += $rowCnGstCalc['gst_percentage'];
            }            
            $gst_percentage_cn = ($total_gst_percentage_cn/$numRowsCnGstCalc);

            $gst_amount_cn = round((($credit_note_amt * $gst_percentage_cn)/100),2);
            /* Taking two decimal values for gst amount */
            $fraction_length = strlen(substr(strrchr($gst_amount_cn, "."), 1)); // Checking the length of the fraction value
            if ($fraction_length > 2) {
                list($integer, $fraction) = explode(".", (string) $gst_amount_cn);
                $fraction = substr($fraction, 0, 2);
                $gst_amount_cn = $integer . "." . $fraction;
            }
        }

        $invoiceTotal = $rowPaid['total']  - $rowPaid['discount'];
        $creditNoteTotal = $credit_note_amt;

        $total_payment_amount = $invoiceTotal - $creditNoteTotal;

        $SQLPartialPayment = "
        SELECT SUM(irh.amount) AS invoice_partial_payment
        FROM invoice_receipt_history irh
        LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
        LEFT JOIN receipt r ON (r.receipt_id = irh.receipt_id)
        WHERE i.invoice_code IN ({$invoice_code})
          AND i.order_id = {$order_id}
          AND r.receipt_status != 'Cancelled'
        ";
        $resultPartialPayment = $db->sql_query($SQLPartialPayment);
        $rowPartialPayment    = $db->sql_fetchrow($resultPartialPayment);

        if ($rowPartialPayment['invoice_partial_payment'] == 0){
            return $total_payment_amount;
        } else {
            return $total_payment_amount - $rowPartialPayment['invoice_partial_payment'];
        }
    }
}
