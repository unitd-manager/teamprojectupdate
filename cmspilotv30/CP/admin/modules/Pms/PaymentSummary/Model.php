<?
class CP_Admin_Modules_Pms_PaymentSummary_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {

        $SQL = "
        SELECT c.contact_id
              ,c.first_name
              ,c.last_name
              ,c.applied_withdrawal_date
              ,i.order_id
              ,CONCAT_WS(' ', p.first_name, p.last_name ) AS parent_name
              ,p.dda
              ,p.parent_id
              ,i.invoice_code
              ,i.invoice_id
              ,p.mode_of_payment
              ,s.title AS branch_name
        FROM contact c 
        JOIN (invoice i)                ON (i.contact_id = c.contact_id)
        LEFT JOIN (`order` o)           ON (i.order_id = o.order_id)
        LEFT JOIN (parent_contact pc)   ON (c.contact_id = pc.contact_id)
        LEFT JOIN (parent p)            ON (pc.parent_id = p.parent_id)
        LEFT JOIN (site s)              ON (c.site_id = s.site_id)
        ";
        
        return $SQL;
    }

    /**
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'c';
        
        $year           = $fn->getReqParam('year');
        $month          = $fn->getReqParam('month');
        $site_id        = $fn->getReqParam('site_id');
        $contact_id     = $fn->getReqParam('contact_id');
        $mode_of_payment = $fn->getReqParam('mode_of_payment');
        $invoice_status = $fn->getReqParam('invoice_status');
        $keyword        = $fn->getReqParam('keyword');
        $userGroupType  = $fn->getSessionParam('userGroupType');
        $student_status = $fn->getReqParam('student_status');
        
        if ($year == '') {
            $year = date('Y');
        }

        if ($fn->isDeveloper()){
        } else {
            $searchVar->sqlSearchVar[] = "p.parent_id > 0";
        }

        /*$current_month = date('m');
        $searchVar->sqlSearchVar[] = "i.invoice_month <= {$current_month}";
        $searchVar->sqlSearchVar[] = "c.status = 'Active'";*/

        $searchVar->groupBy = "i.contact_id";
        $searchVar->sortOrder = "p.parent_id";

        if ($userGroupType == 'Super Administrator') {
            if ($site_id != '') {
                $searchVar->sqlSearchVar[] = "c.site_id = '{$site_id}'";
            }
        } else {
            $searchVar->sqlSearchVar[] = "c.site_id = {$_SESSION['cp_site_id']}";
        }

        if ($student_status != '' ) {
            $searchVar->sqlSearchVar[] = "c.status = '{$student_status}'";
        } else {
            $searchVar->sqlSearchVar[] = "c.status = 'Active'";
      	}

        if ($mode_of_payment != '') {
            $searchVar->sqlSearchVar[] = "p.mode_of_payment = '{$mode_of_payment}'";
        }
            
        if ($year != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(i.invoice_date, '%Y') = '{$year}'";
        } 
        
        if ($month > 0) {
            $searchVar->sqlSearchVar[] = "i.invoice_month = '{$month}'";
        }
            
        if ($invoice_status != '') {
            $searchVar->sqlSearchVar[] = "i.status = '{$invoice_status}'";
        }
            
        if ($contact_id != "") {
            $searchVar->sqlSearchVar[] = "c.contact_id = '{$contact_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.contact_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.contact_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       c.first_name LIKE '%{$tv['keyword']}%'
                    OR c.last_name  LIKE '%{$tv['keyword']}%'
                    OR p.first_name LIKE '%{$tv['keyword']}%'
                    OR p.last_name  LIKE '%{$tv['keyword']}%'
                    OR p.dda        LIKE '%{$tv['keyword']}%'
                )";
            }
        }        
    }

    /**
     *
     */
    function getGenerateReceiptForParentFormSubmit() {
        /********************************* PROCESS ************************************/
        /*
        ACTION: CREATION OF RECEIPT RECORD FOR THE INVOICES CHOSEN
        STEP 1: UPDATION OF DISCOUNT AMOUNT TO '0' IF NO VALUE IS AVAILABLE
        STEP 2: UPDATION OF DISCOUNT AMOUNT FOR ALL THE INVOICES (FOR UNPAID INVOICES)
        STEP 3: UPDATION OF DISCOUNT AMOUNT FOR THE SELECTED INVOICES ONLY
        STEP 4: FINDING THE DETAILS OF THE INVOICE CHOSEN
        STEP 5: FINDING AND SETTING OF RECEIPT CODE
        STEP 6: FINDING TOTAL AMOUNT PAYABLE
        STEP 7: CREATION OF RECEIPT RECORD
        STEP 8: UPDATION OF RECEIPT CODE IN SETTING TABLE
        STEP 9: UPDATING STATUS FOR THE INVOICE IN INVOICE TABLE
        STEP 10: CREATION OF RECORD IN INVOICE HISTORY TABLE FOR THE RECEIPT (ONE INVOICE CAN HAVE MULTIPLE RECEIPTS)
        */
        /******************************* END PROCESS **********************************/

        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        if (!$this->getGenerateReceiptForParentFormValidate()){
            return $validate->getErrorMessageXML();
        }
        
        $amount          = $fn->getPostParam('amount');
        $date            = $fn->getPostParam('date');
        $mode_of_payment = $fn->getPostParam('mode_of_payment');
        $cheque_no       = $fn->getPostParam('cheque_no');
        $cheque_date     = $fn->getPostParam('cheque_date');
        $bank_name       = $fn->getPostParam('bank_name');
        $remarks         = $fn->getPostParam('remarks');
        $discount_amount = $fn->getPostParam('discount_amount');
        $payment_site_id = $fn->getReqParam('payment_site_id');
        $discount_for_all_months = $fn->getPostParam('discount_for_all_months');
        
        $invoiceCodes    = isset($_SESSION['selectedInvoicesForSummary']) ? $_SESSION['selectedInvoicesForSummary'] : 0;
        
        /********************************** STEP 1 **************************************/
        if ($discount_amount == '') {
            $discount_amount = 0;
        }
        /********************************** STEP 1 ENDS HERE ****************************/
        
            /********************************** STEP 2 **************************************/
        /*if ($discount_for_all_months == 1) {
            $modObj = getCPModuleObj('pms_order');
            $invoice_code = $modObj->model->getSeparationOfInvoiceCodes($invoiceCodes);
            
            $sqlOrder = "
            SELECT DISTINCT o.order_id FROM `order` o
            LEFT JOIN (invoice i) ON (o.order_id = i.order_id)
            WHERE i.invoice_id IN ($invoice_code)
            ";
            $resultOrder = $db->sql_query($sqlOrder);
            while($rowOrder = $db->sql_fetchrow($resultOrder)) {
    
                $orderRec = $fn->getRecordRowByID('order', 'order_id', $rowOrder['order_id']);
                
                $sqlInvoice = "
                SELECT DISTINCT contact_id FROM invoice
                WHERE order_id = {$orderRec['order_id']}
                ";
                $resultInvoice = $db->sql_query($sqlInvoice);
                while($rowInvoice = $db->sql_fetchrow($resultInvoice)) {
                    $subSql = "
                    SELECT MIN(i.invoice_month) AS starting_month FROM invoice i
                    LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
                    WHERE i.contact_id = {$rowInvoice['contact_id']}
                      AND i.order_id = {$orderRec['order_id']}
                      AND o.year_of_enrollment = {$orderRec['year_of_enrollment']}
                      AND i.status = 'Due'
                       OR i.status = 'Partial Payment'
                    ";
                    $resultsubSql = $db->sql_query($subSql);
                    $rowsubSql    = $db->sql_fetchrow($resultsubSql);
                    
                    $sqlUpdate = "
                    UPDATE invoice SET discount_amount = {$discount_amount}
                    WHERE contact_id = {$rowInvoice['contact_id']}
                      AND order_id = {$orderRec['order_id']}
                      AND invoice_month >= {$rowsubSql['starting_month']}
                      AND add_registration_fee IS NULL
                    ";
                    $resultUpdate = $db->sql_query($sqlUpdate);
                }
            }*/
            /********************************** STEP 2 ENDS HERE ****************************/
            /********************************** STEP 3 **************************************/
        /*} else {
            foreach($invoiceCodes AS $invoice_code){
                $invoiceRec = $fn->getRecordByCondition('invoice', "invoice_id = '{$invoice_code}' AND add_registration_fee IS NULL");
                
                $faDiscAmt = array();
                $faDiscAmt['discount_amount'] = $discount_amount;                
                $fn->saveRecord($faDiscAmt, 'invoice', 'invoice_id', $invoiceRec['invoice_id']);
            }
        }*/
            /********************************** STEP 3 ENDS HERE ****************************/
        
        // Updating Discount Amount to Invoice records
        $count = 1;
        $_SESSION['receiptIdForSummary'] = '';
        foreach($invoiceCodes AS $invoice_code){
            /********************************** STEP 4 **************************************/
            $sqlInvRec = "
            SELECT * FROM invoice
            WHERE invoice_id = '{$invoice_code}'
            ORDER BY invoice_id
            ";
            $resultInvRec    = $db->sql_query($sqlInvRec);
            $invoiceRec      = $db->sql_fetchrow($resultInvRec);
            #$invoiceRec     = $fn->getRecordByCondition('invoice', "invoice_code = '{$invoice_code}'");
            $invoice_amount  = $invoiceRec['invoice_amount'];
            $invoice_id      = $invoiceRec['invoice_id'];            
            $order_id        = $invoiceRec['order_id'];            
            $discount_amount = $invoiceRec['discount_amount'];
            $site_id         = $invoiceRec['site_id'];
            /********************************** STEP 4 ENDS HERE ****************************/

            /********************************** STEP 5 **************************************/
            $sqlCodePfxRec = "
            SELECT * FROM setting
            WHERE key_text = 'receiptCodePrefix'
              AND site_id  = '{$site_id}'
            ";
            $resultCodePfxRec   = $db->sql_query($sqlCodePfxRec);
            $receiptCodePfxRec  = $db->sql_fetchrow($resultCodePfxRec);
            #$receiptCodePfxRec = $fn->getRecordByCondition('setting', "key_text = 'receiptCodePrefix' AND site_id = '{$site_id}'");
            $receiptCodePfx     = $receiptCodePfxRec['value'];
            
            $sqlCodeRec = "
            SELECT * FROM setting
            WHERE key_text = 'nextReceiptCode'
              AND site_id  = '{$site_id}'
            ";
            $resultCodeRec       = $db->sql_query($sqlCodeRec);
            $receiptCodeRec     = $db->sql_fetchrow($resultCodeRec);
            #$receiptCodeRec    = $fn->getRecordByCondition('setting', "key_text = 'nextReceiptCode' AND site_id = '{$site_id}'");
            $receiptCode        = $receiptCodeRec['value'];
            
            if($receiptCode < 10) {
                $receipt_code = $receiptCodePfx . '000' . $receiptCode;
            } else if($receiptCode < 99) {
                $receipt_code = $receiptCodePfx . '00' . $receiptCode;
            } else if($receiptCode < 999) {
                $receipt_code = $receiptCodePfx . '0' . $receiptCode;
            } else {
                $receipt_code = $receiptCodePfx . $receiptCode;
            }
            /********************************** STEP 5 ENDS HERE ****************************/
            
            /********************************** STEP 6 **************************************/
            $SQLPaid = "
            SELECT SUM(irh.amount) AS prev_sum
            FROM invoice_receipt_history irh
            LEFT JOIN (receipt r) ON (irh.receipt_id = r.receipt_id)
            WHERE irh.invoice_id = '{$invoice_id}'
            AND r.receipt_status = 'Paid'
            ";
            $resultPaid = $db->sql_query($SQLPaid);
            $rowPaid    = $db->sql_fetchrow($resultPaid);
            
            $amount_due = $invoiceRec['invoice_amount'] - $rowPaid['prev_sum'] - $discount_amount; 
            /********************************** STEP 6 ENDS HERE ****************************/
            
            /********************************** STEP 7 **************************************/
            if ($count == 1) {
                $receipt_amount = $amount;
            }
            
            if ($receipt_amount >= $amount_due) {
                $invoice_amount = $amount_due;
            } else {
                $invoice_amount = $receipt_amount;
            }
            
            $fa = array();
            $fa['amount']           = $invoice_amount;
            $fa['discount_amount']  = $discount_amount;
            $fa['order_id']         = $order_id;
            $fa['receipt_code']     = $receipt_code;
            $fa['mode_of_payment']  = $mode_of_payment;
            $fa['cheque_no']        = $cheque_no;
            $fa['cheque_date']      = $cheque_date;
            $fa['bank_name']        = $bank_name;
            $fa['remarks']          = $remarks;
            $fa['date']             = $date;
            $fa['receipt_status']   = 'Paid';
            $fa['creation_date']    = date("Y-m-d H:i:s");
            $fa['created_by']       = $fn->getSessionParam('userName');
            $fa['site_id']          = $site_id;
            
            if ($site_id != $payment_site_id) {
                $fa['payment_site_id'] = $payment_site_id;
            }
            
            $insertReceiptSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'receipt');
            $resultSQL          = $db->sql_query($insertReceiptSQL);
            $receipt_id         = $db->sql_nextid();
            $invoice_status_due = '';
            $count = 0;
            /********************************** STEP 7 ENDS HERE ****************************/
            
            /********************************** STEP 8 **************************************/
            if ($site_id) {
                $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode' AND site_id = '{$site_id}'";
            } else {
                $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode'";
            }
            $resultUpdate = $db->sql_query($SQLUpdate);
            /********************************** STEP 8 ENDS HERE ****************************/

            /********************************** STEP 9 **************************************/
            if ($invoiceRec['status'] == 'Paid' || $receipt_amount <= 0){
                continue;
            }
            
            $faInv = array();
            $recpInvAmount = 0;
            if ($amount_due <= $receipt_amount){
                $recpInvAmount = $invoice_amount;
                $faInv['status'] = 'Paid';
            } else if ($amount_due > $receipt_amount){
                $recpInvAmount = $receipt_amount;
                $faInv['status'] = 'Partial Payment';
            }
            
            $receipt_amount = $receipt_amount - $recpInvAmount;
            $fn->saveRecord($faInv, 'invoice', 'invoice_id', $invoice_id);
            /********************************** STEP 9 ENDS HERE ****************************/
            
            /*
            $faInv = array();
            $faInv['status'] = 'Paid';
            $fn->saveRecord($faInv, 'invoice', 'invoice_id', $invoice_id);
            */
            //Inserting receipt id in to history table ( one invoice can have multiple receipts)
            /********************************** STEP 10 **************************************/
            $fa = array();
            $fa['receipt_id']    = $receipt_id;
            $fa['invoice_id']    = $invoice_id;
            $fa['amount']        = $recpInvAmount;
            $fa['creation_date'] = date("Y-m-d H:i:s");
            $histId = $fn->addRecord($fa, 'invoice_receipt_history');
            /********************************** STEP 10 ENDS HERE ****************************/

            $_SESSION['receiptIdForSummary'][] = $receipt_id;
        }

        #$cpUtil->redirect("index.php?_topRm=finance&module=pms_order&_spAction=printGroupReceiptInFpdf&showHTML=0");
        //$cpUtil->redirect("index.php?_topRm=finance&module=pms_order&order_id={$order_id}&_action=edit");
        
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getGenerateReceiptForParentFormValidateOld() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        
        $invoice_amount = '';
        $invoice_prev_amount = '';
        $balance_amount = '';
        $amount          = $fn->getPostParam('amount');
        $discount_amount = $fn->getPostParam('discount_amount');
        $invoiceCodes    = isset($_SESSION['selectedInvoicesForSummary']) ? $_SESSION['selectedInvoicesForSummary'] : 0;

        //==================================================================//
        $validate->resetErrorArray();

        $modObj = getCPModuleObj('pms_order');
        $invoice_code = $modObj->model->getSeparationOfInvoiceCodes($invoiceCodes);

        if ($invoice_code != ''){
            /* Finding total invoice amount of selected invoices */
            $SQL = "
            SELECT SUM(invoice_amount) as invoice_sum
            FROM invoice
            WHERE invoice_code IN ($invoice_code)
            ";
            $resultPaid = $db->sql_query($SQL);
            $rowPaid    = $db->sql_fetchrow($resultPaid);
            $total_invoice_amount = $rowPaid['invoice_sum'];

            /* Finding total amount paid earlier of selected invoices */
            $SQLPaid = "
            SELECT SUM(irh.amount) as prev_sum
            FROM invoice_receipt_history irh
            LEFT JOIN (receipt r) ON (irh.receipt_id = r.receipt_id)
            WHERE irh.invoice_id IN (
                SELECT invoice_id
                FROM invoice
                WHERE invoice_code IN ($invoice_code)
                )
            AND r.receipt_status = 'Paid'
            ";
            $resultPaid = $db->sql_query($SQLPaid);
            $rowPaid    = $db->sql_fetchrow($resultPaid);
            $prev_sum   = $rowPaid['prev_sum'];
            
            /* Calculating new discount amount for selected invoices */
            if ($discount_amount == '') {
                $discount_amount = 0;
            }
            
            if ($discount_amount > 0) {
                $sqlMaxInvAmt = "
                SELECT MAX(invoice_amount) AS max_invoice_amount FROM invoice
                WHERE invoice_code IN ($invoice_code)
                ";
                $resultMaxInvAmt = $db->sql_query($sqlMaxInvAmt);
                $rowMaxInvAmt    = $db->sql_fetchrow($resultMaxInvAmt);
                
                if ($discount_amount > $rowMaxInvAmt['max_invoice_amount']) {
                    $validate->errorArray['discount_amount']['name'] = "discount_amount";
                    $validate->errorArray['discount_amount']['msg']  = 'You cannot input the discount amount more than ' . $rowMaxInvAmt['max_invoice_amount'];
                }
                
                $sqlDiscount = "
                SELECT invoice_code FROM invoice
                WHERE invoice_code IN ($invoice_code)
                  AND add_registration_fee IS NULL
                ";
                $resultDiscount  = $db->sql_query($sqlDiscount);
                $numRowsDiscount = $db->sql_numrows($resultDiscount);
                
                $discount_amount = $numRowsDiscount * $discount_amount;                
            }                

            $balance_amount = $total_invoice_amount - $prev_sum;
            $invoice_amount = $balance_amount - $discount_amount;

            $total_receipt_amount = $amount + $discount_amount;
            if($total_receipt_amount > $balance_amount){
                $validate->errorArray['amount']['name'] = "amount";
                $validate->errorArray['amount']['msg']  = 'You can input a maximum of ' . $invoice_amount . ' in amount for chosen invoices';
            }
        }

        $validate->validateData('date', 'Please select the date');
        $validate->validateData('mode_of_payment', 'Please choose mode of payment');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getGenerateReceiptForParentFormValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        
        $invoice_amount = '';
        $invoice_prev_amount = '';
        $balance_amount = '';
        $amount          = $fn->getPostParam('amount');
        $discount_amount = $fn->getPostParam('discount_amount');
        $invoiceCodes    = isset($_SESSION['selectedInvoicesForSummary']) ? $_SESSION['selectedInvoicesForSummary'] : 0;

        //==================================================================//
        $validate->resetErrorArray();

        $modObj = getCPModuleObj('pms_order');
        $invoice_code = $modObj->model->getSeparationOfInvoiceCodes($invoiceCodes);

        if ($invoice_code != ''){
            /* Finding total invoice amount of selected invoices */
            $SQL = "
            SELECT SUM(invoice_amount) as invoice_sum
            FROM invoice
            WHERE invoice_id IN ($invoice_code)
            ";
            $resultPaid = $db->sql_query($SQL);
            $rowPaid    = $db->sql_fetchrow($resultPaid);
            $total_invoice_amount = $rowPaid['invoice_sum'];

            /* Finding total amount paid earlier of selected invoices */
            $SQLPaid = "
            SELECT SUM(irh.amount) as prev_sum
            FROM invoice_receipt_history irh
            LEFT JOIN (receipt r) ON (irh.receipt_id = r.receipt_id)
            WHERE irh.invoice_id IN (
                SELECT invoice_id
                FROM invoice
                WHERE invoice_id IN ($invoice_code)
                )
            AND r.receipt_status = 'Paid'
            ";
            $resultPaid = $db->sql_query($SQLPaid);
            $rowPaid    = $db->sql_fetchrow($resultPaid);
            $prev_sum   = $rowPaid['prev_sum'];
            
            /* Calculating new discount amount for selected invoices */
            if ($discount_amount == '') {
                $discount_amount = 0;
            }
            
            /*if ($discount_amount > 0) {
                $sqlMaxInvAmt = "
                SELECT MAX(invoice_amount) AS max_invoice_amount FROM invoice
                WHERE invoice_id IN ($invoice_code)
                ";
                $resultMaxInvAmt = $db->sql_query($sqlMaxInvAmt);
                $rowMaxInvAmt    = $db->sql_fetchrow($resultMaxInvAmt);
                
                if ($discount_amount > $rowMaxInvAmt['max_invoice_amount']) {
                    $validate->errorArray['discount_amount']['name'] = "discount_amount";
                    $validate->errorArray['discount_amount']['msg']  = 'You cannot input the discount amount more than ' . $rowMaxInvAmt['max_invoice_amount'];
                }
                
                $sqlDiscount = "
                SELECT invoice_code FROM invoice
                WHERE invoice_id IN ($invoice_code)
                  AND add_registration_fee IS NULL
                ";
                $resultDiscount  = $db->sql_query($sqlDiscount);
                $numRowsDiscount = $db->sql_numrows($resultDiscount);
                
                $discount_amount = $numRowsDiscount * $discount_amount;                
            }*/                
            $SQLDiscount = "
            SELECT SUM(discount_amount) AS discount_selected_sum
            FROM invoice
            WHERE invoice_id IN ({$invoice_code})
            ";
            $resultDiscount = $db->sql_query($SQLDiscount);
            $rowDiscount    = $db->sql_fetchrow($resultDiscount);
            $discount_amount = $rowDiscount['discount_selected_sum'];

            $balance_amount = $total_invoice_amount - $prev_sum;
            $invoice_amount = $balance_amount - $discount_amount;

            $total_receipt_amount = $amount + $discount_amount;
            if($total_receipt_amount > $balance_amount){
                $validate->errorArray['amount']['name'] = "amount";
                $validate->errorArray['amount']['msg']  = 'You can input a maximum of ' . $invoice_amount . ' in amount for chosen invoices';
            }
        }

        $validate->validateData('date', 'Please select the date');
        $validate->validateData('mode_of_payment', 'Please choose mode of payment');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
}
