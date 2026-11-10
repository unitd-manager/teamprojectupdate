<?
class CP_Admin_Modules_ManPower_Receipt_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT DISTINCT r.receipt_id
              ,r.receipt_code
              ,r.amount
              ,r.mode_of_payment
              ,r.receipt_status
              ,r.receipt_type
              ,r.order_id
              ,r.date
              ,r.flag
              ,r.creation_date
              ,r.modification_date
              ,c.company_name
        FROM receipt r
        LEFT JOIN (invoice_receipt_history irh) ON (r.receipt_id = irh.receipt_id)
        LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
        LEFT JOIN (`order` o) ON (o.order_id = r.order_id)
        LEFT JOIN (company c) ON (c.company_id = o.company_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'r';

        $receipt_id     = $fn->getReqParam('receipt_id');
        $receipt_date1  = $fn->getReqParam('receipt_date1');
        $receipt_date2  = $fn->getReqParam('receipt_date2');
        $mode_of_payment = $fn->getReqParam('mode_of_payment');
        $receipt_status  = $fn->getReqParam('receipt_status');

        if ($receipt_id != "") {
            $searchVar->sqlSearchVar[] = "r.receipt_id = '{$receipt_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "r.receipt_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'r.receipt_id');

            if ($receipt_date1 != "" && $receipt_date2 != "") {
                $searchVar->sqlSearchVar[] = "(r.date BETWEEN '{$receipt_date1} 00:00:00' AND '{$receipt_date2} 23:59:59')";
            }

            if ($mode_of_payment != "") {
                $searchVar->sqlSearchVar[] = "r.mode_of_payment = '{$mode_of_payment}'";
            }

            if ($receipt_status != "") {
                $searchVar->sqlSearchVar[] = "r.receipt_status = '{$receipt_status}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                                        r.order_id LIKE '%{$tv['keyword']}%'
                                     OR r.receipt_code LIKE '%{$tv['keyword']}%'
                                     OR c.company_name LIKE '%{$tv['keyword']}%'
                                       )";
            }
        }
    }


    /**
     *
     */
    function getGenerateReceiptFormSubmitClient() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $invoiceCodes    = $fn->getPostParam('invoiceCode', array());
        $amount          = $fn->getPostParam('amount');
        $mode_of_payment = $fn->getPostParam('mode_of_payment');
        $remarks         = $fn->getPostParam('remarks');
        $order_id        = $fn->getReqParam('order_id');
        $cheque_no       = $fn->getPostParam('cheque_no');
        $receipt_date    = $fn->getPostParam('receipt_date');

        if (!$this->getGenerateReceiptFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $count = count($invoiceCodes);

        //To update receipt codes
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode'";
        $resultUpdate = $db->sql_query($SQLUpdate);
        $receipt_code = $fn->getSettingsValueByKey("nextReceiptCode");

        $fa = array();
        $fa['amount']         = $amount;
        $fa['order_id']       = $order_id;
        $fa['receipt_code']   = 'RCPT - ' . $receipt_code;
        $fa['mode_of_payment']= $mode_of_payment;
        $fa['remarks']        = $remarks;
        $fa['date']           = $receipt_date;
        $fa['receipt_status'] = 'Paid';
        $fa['receipt_type']   = 'Client';
        $fa['creation_date']  = date("Y-m-d H:i:s");
        $fa['created_by']     = $fn->getSessionParam('userName');
        $fa['cheque_no']      = $cheque_no;

        $insertReceiptSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'receipt');
        $resultSQL          = $db->sql_query($insertReceiptSQL);
        $receipt_id         = $db->sql_nextid();
        $receipt_amount     = $amount;
        $invoice_status_due = '';
        $count = 0;

        foreach($invoiceCodes AS $invoice_code){
            $SQLInvoice = "
            SELECT *
            FROM `invoice`
            WHERE invoice_code = '{$invoice_code}'
            ";
            $resultInvoice  = $db->sql_query($SQLInvoice);
            $invoiceRec     = $db->sql_fetchrow($resultInvoice);
            $invoice_amount = $invoiceRec['invoice_amount'];
            $invoice_id     = $invoiceRec['invoice_id'];

            if ($invoiceRec['status'] == 'Paid' || $receipt_amount <= 0){
                continue;
            }

            $SQLPaid = "
            SELECT SUM(amount) AS prev_sum
            FROM invoice_receipt_history
            WHERE invoice_id = '{$invoice_id}'
            ";
            //CHANGED BY SYED 8-3-2014 , TO EXCLUDE CANCEL RECEIPTS
            $SQLPaid = "
            SELECT SUM(invHist.amount) AS prev_sum
            FROM invoice_receipt_history invHist
            LEFT JOIN (receipt rec) ON (invHist.receipt_id = rec.receipt_id)
            WHERE invHist.invoice_id =  '{$invoice_id}' and rec.receipt_status = 'Paid'
            ";
            $resultPaid = $db->sql_query($SQLPaid);
            $rowPaid    = $db->sql_fetchrow($resultPaid);

            $invoice_amount = $invoice_amount - $rowPaid['prev_sum'];

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
            $fn->saveRecord($faInv, 'invoice', 'invoice_id', $invoice_id);

            //Inserting receipt id in to history table ( one invoice can have multiple receipts)
            $fa = array();
            $fa['receipt_id']    = $receipt_id;
            $fa['invoice_id']    = $invoice_id;
            $fa['amount']        = $recpInvAmount;
            $fa['creation_date'] = date("Y-m-d H:i:s");
            $histId = $fn->addRecord($fa, 'invoice_receipt_history');
        }

        //$this->getGenerateReceiptForMedia($receipt_id, $invoiceCodes);


        //FOR AUTO UPDATING OF ORDER STATUS WHEN A RECEIPT IS PAID
        $SQLInv = "
        SELECT SUM(invoice_amount) AS order_amount
        FROM `invoice`
        WHERE order_id = {$order_id}
          AND status != 'Cancelled'
        ";
        $resultInv  = $db->sql_query($SQLInv);
        $invRec     = $db->sql_fetchrow($resultInv);

        $SQLInvTot = "
        SELECT SUM(invoice_amount) AS total_invoice_amount
        FROM `invoice`
        WHERE order_id = {$order_id}
          AND status = 'Paid'
        ";
        $resultInvTot  = $db->sql_query($SQLInvTot);
        $invRecTot     = $db->sql_fetchrow($resultInvTot);

        if($invRec['order_amount'] == $invRecTot['total_invoice_amount']){
            $SQLUpdate = "UPDATE `order` SET order_status = 'Paid' WHERE order_id = {$order_id}";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getGenerateReceiptFormSubmitCandidate() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $invoiceCodes               = $fn->getPostParam('invoiceCode', array());
        $amount                     = $fn->getPostParam('amount');
        $mode_of_payment            = $fn->getPostParam('mode_of_payment');
        $remarks                    = $fn->getPostParam('remarks');
        $order_id                   = $fn->getReqParam('order_id');
        $cheque_no                  = $fn->getPostParam('cheque_no');
        $candidate_receipt_date     = $fn->getPostParam('candidate_receipt_date');

        if (!$this->getGenerateReceiptFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $count = count($invoiceCodes);

        //To update receipt codes
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode'";
        $resultUpdate = $db->sql_query($SQLUpdate);
        $receipt_code = $fn->getSettingsValueByKey("nextReceiptCode");

        $fa = array();
        $fa['amount']         = $amount;
        $fa['order_id']       = $order_id;
        $fa['receipt_code']   = 'RCPT - ' . $receipt_code;
        $fa['mode_of_payment']= $mode_of_payment;
        $fa['remarks']        = $remarks;
        $fa['date']           = $candidate_receipt_date;
        $fa['receipt_status'] = 'Paid';
        $fa['receipt_type']   = 'Candidate';
        $fa['creation_date']  = date("Y-m-d H:i:s");
        $fa['created_by']     = $fn->getSessionParam('userName');
        $fa['cheque_no']      = $cheque_no;

        $insertReceiptSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'receipt');
        $resultSQL          = $db->sql_query($insertReceiptSQL);
        $receipt_id         = $db->sql_nextid();
        $receipt_amount     = $amount;
        $invoice_status_due = '';
        $count = 0;

        foreach($invoiceCodes AS $invoice_code){
            $SQLInvoice = "
            SELECT *
            FROM `invoice`
            WHERE invoice_code = '{$invoice_code}'
            ";
            $resultInvoice  = $db->sql_query($SQLInvoice);
            $invoiceRec     = $db->sql_fetchrow($resultInvoice);
            $invoice_amount = $invoiceRec['invoice_amount'];
            $invoice_id     = $invoiceRec['invoice_id'];

            if ($invoiceRec['status'] == 'Paid' || $receipt_amount <= 0){
                continue;
            }

            $SQLPaid = "
            SELECT SUM(amount) AS prev_sum
            FROM invoice_receipt_history
            WHERE invoice_id = '{$invoice_id}'
            ";
            //CHANGED BY SYED 8-3-2014 , TO EXCLUDE CANCEL RECEIPTS
            $SQLPaid = "
            SELECT SUM(invHist.amount) AS prev_sum
            FROM invoice_receipt_history invHist
            LEFT JOIN (receipt rec) ON (invHist.receipt_id = rec.receipt_id)
            WHERE invHist.invoice_id =  '{$invoice_id}' and rec.receipt_status = 'Paid'
            ";
            $resultPaid = $db->sql_query($SQLPaid);
            $rowPaid    = $db->sql_fetchrow($resultPaid);

            $invoice_amount = $invoice_amount - $rowPaid['prev_sum'];

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
            $fn->saveRecord($faInv, 'invoice', 'invoice_id', $invoice_id);

            //Inserting receipt id in to history table ( one invoice can have multiple receipts)
            $fa = array();
            $fa['receipt_id']    = $receipt_id;
            $fa['invoice_id']    = $invoice_id;
            $fa['amount']        = $recpInvAmount;
            $fa['creation_date'] = date("Y-m-d H:i:s");
            $histId = $fn->addRecord($fa, 'invoice_receipt_history');
        }

        //$this->getGenerateReceiptForMedia($receipt_id, $invoiceCodes);

        //FOR AUTO UPDATING OF ORDER STATUS WHEN A RECEIPT IS PAID
        $SQLInv = "
        SELECT SUM(invoice_amount) AS order_amount
        FROM `invoice`
        WHERE order_id = {$order_id}
          AND status != 'Cancelled'
        ";
        $resultInv  = $db->sql_query($SQLInv);
        $invRec     = $db->sql_fetchrow($resultInv);

        $SQLInvTot = "
        SELECT SUM(invoice_amount) AS total_invoice_amount
        FROM `invoice`
        WHERE order_id = {$order_id}
          AND status = 'Paid'
        ";
        $resultInvTot  = $db->sql_query($SQLInvTot);
        $invRecTot     = $db->sql_fetchrow($resultInvTot);

        if($invRec['order_amount'] == $invRecTot['total_invoice_amount']){
            $SQLUpdate = "UPDATE `order` SET order_status = 'Paid' WHERE order_id = {$order_id}";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getGenerateReceiptFormSubmitReferral() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $invoiceCodes               = $fn->getPostParam('invoiceCode', array());
        $amount                     = $fn->getPostParam('amount');
        $mode_of_payment            = $fn->getPostParam('mode_of_payment');
        $remarks                    = $fn->getPostParam('remarks');
        $order_id                   = $fn->getReqParam('order_id');
        $cheque_no                  = $fn->getPostParam('cheque_no');
        $referral_receipt_date      = $fn->getPostParam('referral_receipt_date');

        if (!$this->getGenerateReceiptFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $count = count($invoiceCodes);

        //To update receipt codes
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode'";
        $resultUpdate = $db->sql_query($SQLUpdate);
        $receipt_code = $fn->getSettingsValueByKey("nextReceiptCode");

        $fa = array();
        $fa['amount']         = $amount;
        $fa['order_id']       = $order_id;
        $fa['receipt_code']   = 'RCPT - ' . $receipt_code;
        $fa['mode_of_payment']= $mode_of_payment;
        $fa['remarks']        = $remarks;
        $fa['date']           = $referral_receipt_date;
        $fa['receipt_status'] = 'Paid';
        $fa['receipt_type']   = 'Referral';
        $fa['creation_date']  = date("Y-m-d H:i:s");
        $fa['created_by']     = $fn->getSessionParam('userName');
        $fa['cheque_no']      = $cheque_no;

        $insertReceiptSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'receipt');
        $resultSQL          = $db->sql_query($insertReceiptSQL);
        $receipt_id         = $db->sql_nextid();
        $receipt_amount     = $amount;
        $invoice_status_due = '';
        $count = 0;

        foreach($invoiceCodes AS $invoice_code){
            $SQLInvoice = "
            SELECT *
            FROM `invoice`
            WHERE invoice_code = '{$invoice_code}'
            ";
            $resultInvoice  = $db->sql_query($SQLInvoice);
            $invoiceRec     = $db->sql_fetchrow($resultInvoice);
            $invoice_amount = $invoiceRec['invoice_amount'];
            $invoice_id     = $invoiceRec['invoice_id'];

            if ($invoiceRec['status'] == 'Paid' || $receipt_amount <= 0){
                continue;
            }

            $SQLPaid = "
            SELECT SUM(amount) AS prev_sum
            FROM invoice_receipt_history
            WHERE invoice_id = '{$invoice_id}'
            ";
            //CHANGED BY SYED 8-3-2014 , TO EXCLUDE CANCEL RECEIPTS
            $SQLPaid = "
            SELECT SUM(invHist.amount) AS prev_sum
            FROM invoice_receipt_history invHist
            LEFT JOIN (receipt rec) ON (invHist.receipt_id = rec.receipt_id)
            WHERE invHist.invoice_id =  '{$invoice_id}' and rec.receipt_status = 'Paid'
            ";
            $resultPaid = $db->sql_query($SQLPaid);
            $rowPaid    = $db->sql_fetchrow($resultPaid);

            $invoice_amount = $invoice_amount - $rowPaid['prev_sum'];

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
            $fn->saveRecord($faInv, 'invoice', 'invoice_id', $invoice_id);

            //Inserting receipt id in to history table ( one invoice can have multiple receipts)
            $fa = array();
            $fa['receipt_id']    = $receipt_id;
            $fa['invoice_id']    = $invoice_id;
            $fa['amount']        = $recpInvAmount;
            $fa['creation_date'] = date("Y-m-d H:i:s");
            $histId = $fn->addRecord($fa, 'invoice_receipt_history');
        }

        //$this->getGenerateReceiptForMedia($receipt_id, $invoiceCodes);

        //FOR AUTO UPDATING OF ORDER STATUS WHEN A RECEIPT IS PAID
        $SQLInv = "
        SELECT SUM(invoice_amount) AS order_amount
        FROM `invoice`
        WHERE order_id = {$order_id}
          AND status != 'Cancelled'
        ";
        $resultInv  = $db->sql_query($SQLInv);
        $invRec     = $db->sql_fetchrow($resultInv);

        $SQLInvTot = "
        SELECT SUM(invoice_amount) AS total_invoice_amount
        FROM `invoice`
        WHERE order_id = {$order_id}
          AND status = 'Paid'
        ";
        $resultInvTot  = $db->sql_query($SQLInvTot);
        $invRecTot     = $db->sql_fetchrow($resultInvTot);

        if($invRec['order_amount'] == $invRecTot['total_invoice_amount']){
            $SQLUpdate = "UPDATE `order` SET order_status = 'Paid' WHERE order_id = {$order_id}";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getGenerateReceiptFormEmployerTaxSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $amount             = $fn->getPostParam('amount');
        $mode_of_payment    = $fn->getPostParam('mode_of_payment');
        $remarks            = $fn->getPostParam('remarks');
        $order_id           = $fn->getPostParam('order_id');
        $invoice_id         = $fn->getPostParam('invoice_id');
        $cheque_no          = $fn->getPostParam('cheque_no');
        $tax_receipt_date   = $fn->getPostParam('tax_receipt_date');

        /*if (!$this->getGenerateReceiptFormValidate()){
            return $validate->getErrorMessageXML();
        }*/

        //To update receipt codes
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode'";
        $resultUpdate = $db->sql_query($SQLUpdate);
        $receipt_code = $fn->getSettingsValueByKey("nextReceiptCode");

        $fa = array();
        $fa['amount']         = $amount;
        $fa['order_id']       = $order_id;
        $fa['receipt_code']   = 'RCPT - ' . $receipt_code;
        $fa['mode_of_payment']= $mode_of_payment;
        $fa['remarks']        = $remarks;
        $fa['date']           = $tax_receipt_date;
        $fa['receipt_status'] = 'Paid';
        $fa['receipt_type']   = 'Employer Tax';
        $fa['creation_date']  = date("Y-m-d H:i:s");
        $fa['created_by']     = $fn->getSessionParam('userName');
        $fa['cheque_no']      = $cheque_no;
        $fa['tax_invoice_id'] = $invoice_id;

        $insertReceiptSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'receipt');
        $resultSQL          = $db->sql_query($insertReceiptSQL);
        $receipt_id         = $db->sql_nextid();
        $receipt_amount     = $amount;

        $SQLInvoice = "
        SELECT *
        FROM `invoice`
        WHERE invoice_id = '{$invoice_id}'
        ";
        $resultInvoice  = $db->sql_query($SQLInvoice);
        $invoiceRec     = $db->sql_fetchrow($resultInvoice);
        $invoice_amount = $invoiceRec['invoice_amount'];

        $recpInvAmount = 0;
        if ($invoice_amount <= $receipt_amount){
            $recpInvAmount = $invoice_amount;
            $faInv['status'] = 'Paid';
        } else if ($invoice_amount > $receipt_amount){
            $recpInvAmount = $receipt_amount;
            $faInv['status'] = 'Partial Payment';
        }

        $receipt_amount = $receipt_amount - $recpInvAmount;
        $fn->saveRecord($faInv, 'invoice', 'invoice_id', $invoice_id);

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
        $invoiceCodesArr = $fn->getPostParam('invoiceCode', array());

        $validate->resetErrorArray();
        if(count($invoiceCodesArr) == 0){
            $validate->validateData('amount' , 'Please choose the invoice(s) to be paid');
        }
        //==================================================================
        $invoiceCodes = join(",", $invoiceCodesArr);
        $sessionExplode = explode(',', $invoiceCodes);

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

        if ($invoiceCodes != ''){
            $SQL = "
                SELECT SUM(invoice_amount) as invoice_sum
                FROM invoice
                WHERE invoice_code IN ({$invoice_code})
            ";
            $resultPaid = $db->sql_query($SQL);
            $rowPaid    = $db->sql_fetchrow($resultPaid);
            $total_invoice_amount = $rowPaid['invoice_sum'];

            $SQLPaid = "
            SELECT SUM(irh.amount) as prev_sum
            FROM invoice_receipt_history irh
            LEFT JOIN receipt r ON (r.receipt_id = irh.receipt_id)
            WHERE invoice_id IN (
                SELECT invoice_id
                FROM invoice
                WHERE invoice_code IN ({$invoice_code})
                )
            AND r.receipt_status != 'Cancelled'
            ";
            $resultPaid = $db->sql_query($SQLPaid);
            $rowPaid    = $db->sql_fetchrow($resultPaid);
            $prev_sum   = $rowPaid['prev_sum'];

            $balance_amount = $total_invoice_amount - $prev_sum;

            if($amount > $balance_amount){
                $validate->errorArray['amount']['name'] = "amount";
                $validate->errorArray['amount']['msg']  = 'Please enter amount less than or equal to the actual or balance amount';
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
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'receipt_date');
        $fa = $fn->addToFieldsArray($fa, 'amount');
        $fa = $fn->addToFieldsArray($fa, 'cheque_no');
        $fa = $fn->addToFieldsArray($fa, 'order_id');

        return $fa;
    }


    /**
     */
    function getGenerateReceiptFormFromInvoiceSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        if (!$this->getGenerateReceiptFormFromInvoiceValidate()){
            return $validate->getErrorMessageXML();
        }

        $invoice_id      = $fn->getReqParam('invoice_id');
        $issued_by       = $fn->getReqParam('issued_by');

        $amount          = $fn->getPostParam('amount');
        $receipt_date    = $fn->getPostParam('receipt_date');
        $mode_of_payment = $fn->getPostParam('mode_of_payment');
        $cheque_no       = $fn->getPostParam('cheque_no');
        $cheque_date     = $fn->getPostParam('cheque_date');
        $bank_name       = $fn->getPostParam('bank_name');
        $remarks         = $fn->getPostParam('remarks');

        $receiptCode = $fn->getSettingsValueByKey("nextReceiptCode");
        if($receiptCode < 10) {
            $receipt_code = $fn->getSettingsValueByKey('receiptCodePrefix') . '000' . $receiptCode;
        } else if($receiptCode < 99) {
            $receipt_code = $fn->getSettingsValueByKey('receiptCodePrefix') . '00' . $receiptCode;
        } else if($receiptCode < 999) {
            $receipt_code = $fn->getSettingsValueByKey('receiptCodePrefix') . '0' . $receiptCode;
        } else {
            $receipt_code = $fn->getSettingsValueByKey('receiptCodePrefix') . $receiptCode;
        }

        $fa = array();
        $fa['receipt_code']   = $receipt_code;
        $fa['amount']         = $amount;
        $fa['invoice_id']     = $invoice_id;
        $fa['mode_of_payment']= $mode_of_payment;
        $fa['cheque_no']      = $cheque_no;
        $fa['cheque_date']    = $cheque_date;
        $fa['bank_name']      = $bank_name;
        $fa['remarks']        = $remarks;
        $fa['date']           = $receipt_date;
        $fa['issued_by']      = $issued_by;
        $fa['receipt_status'] = 'Paid';
        $fa['creation_date']  = date("Y-m-d H:i:s");
        $fa['created_by']     = $fn->getSessionParam('userName');

        $insertReceiptSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'receipt');
        $resultSQL          = $db->sql_query($insertReceiptSQL);
        $receipt_id         = $db->sql_nextid();

        $SQLUpdate       = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode'";
        $resultUpdate    = $db->sql_query($SQLUpdate);
        $nextReceiptCode = $fn->getSettingsValueByKey("nextReceiptCode");

        //Inserting receipt id in to history table (one invoice can have multiple receipts)
        $fa = array();
        $fa['receipt_id']    = $receipt_id;
        $fa['invoice_id']    = $invoice_id;
        $fa['amount']        = $amount;
        $fa['creation_date'] = date("Y-m-d H:i:s");
        $fa['created_by']    = $fn->getSessionParam('userName');
        $histId = $fn->addRecord($fa, 'invoice_receipt_history');

        //$this->getGenerateReceiptForMedia($receipt_id);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getGenerateReceiptFormFromInvoiceValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $invoice_amount      = '';
        $invoice_prev_amount = '';
        $balance_amount      = '';
        $amount              = $fn->getPostParam('amount');
        $invoice_id          = $fn->getReqParam('invoice_id');

        $validate->resetErrorArray();

        if ($amount != ''){
            $sqlInv = "
            SELECT invoice_amount
            FROM invoice
            WHERE invoice_id = {$invoice_id}
            ";
            $resultInv = $db->sql_query($sqlInv);
            $rowInv    = $db->sql_fetchrow($resultInv);
            $total_invoice_amount = $rowInv['invoice_amount'];

            $SQLPaid = "
            SELECT SUM(amount) as prev_sum
            FROM invoice_receipt_history
            WHERE invoice_id IN (
                SELECT invoice_id
                FROM invoice
                WHERE invoice_id = {$invoice_id}
                )
            ";
            $resultPaid = $db->sql_query($SQLPaid);
            $rowPaid    = $db->sql_fetchrow($resultPaid);
            $prev_sum   = $rowPaid['prev_sum'];

            $balance_amount = $total_invoice_amount - $prev_sum;

            if ($amount > $balance_amount) {
                $validate->errorArray['amount']['name'] = "amount";
                $validate->errorArray['amount']['msg']  = 'Please enter amount less than or equal to the actual or balance amount';
            }
        } else {
            $validate->validateData('amount' , 'Please enter the amount');
        }

        $validate->validateData('mode_of_payment' , 'Please choose mode of payment');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getEditReceiptFormFromInvoiceSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $amount          = $fn->getPostParam('amount');
        $receipt_date    = $fn->getPostParam('date');
        $mode_of_payment = $fn->getPostParam('mode_of_payment');
        $cheque_no       = $fn->getPostParam('cheque_no');
        $cheque_date     = $fn->getPostParam('cheque_date');
        $bank_name       = $fn->getPostParam('bank_name');
        $issued_by       = $fn->getPostParam('issued_by');
        $remarks         = $fn->getPostParam('remarks');
        $receipt_id      = $fn->getReqParam('receipt_id');
        $invoice_id      = $fn->getReqParam('invoice_id');

        if (!$this->getGenerateReceiptFormFromInvoiceValidate()){
            return $validate->getErrorMessageXML();
        }

        /* Setting the amount & date empty for the previously paid records in invoice receipt history related to this receipt */
        $sqlIRHUpdate = "
        UPDATE invoice_receipt_history
        SET amount = ''
        WHERE receipt_id = {$receipt_id}";
        $resultIRHUpdate = $db->sql_query($sqlIRHUpdate);

        //To update the existing receipt record with input values made in edit receipt.
        $fa = array();
        $fa['amount']         = $amount;
        $fa['date']           = $receipt_date;
        $fa['mode_of_payment']= $mode_of_payment;
        $fa['cheque_no']      = $cheque_no;
        $fa['cheque_date']    = $cheque_date;
        $fa['bank_name']      = $bank_name;
        $fa['issued_by']      = $issued_by;
        $fa['remarks']        = $remarks;
        $fa['modification_date']  = date("Y-m-d H:i:s");
        $fa['modified_by']     = $fn->getSessionParam('userName');

        $whereCondition = "WHERE receipt_id = {$receipt_id}";
        $updateSQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'receipt', $whereCondition);
        $resultSQL = $db->sql_query($updateSQL);

        $faIrh = array();
        $faIrh['receipt_id']    = $receipt_id;
        $faIrh['invoice_id']    = $invoice_id;
        $faIrh['amount']        = $amount;
        $faIrh['creation_date'] = date("Y-m-d H:i:s");
        $faIrh['created_by']    = $fn->getSessionParam('userName');
        $histId = $fn->addRecord($faIrh, 'invoice_receipt_history');

        //$this->getGenerateReceiptForMedia($receipt_id);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getCancelReceipt() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $receipt_code = $fn->getReqParam('receipt_code');

        $sqlReceipt = "
        SELECT *
        FROM receipt
        WHERE receipt_code = '{$receipt_code}'
        ";
        $resultReceipt = $db->sql_query($sqlReceipt);
        $numRowsReceipt = $db->sql_numrows($resultReceipt);
        $irh = $db->sql_fetchrow($resultReceipt);

        if ($numRowsReceipt > 0) {
            $sqlIrh = "
            SELECT invoice_id FROM invoice_receipt_history
            WHERE receipt_id = {$irh['receipt_id']}
            ";
            $resultIrh = $db->sql_query($sqlIrh);
            while ($rowIrh = $db->sql_fetchrow($resultIrh)) {

                /* Updating of status in invoice table */
                $SQLInvHist = "
                SELECT *
                FROM invoice_receipt_history
                WHERE invoice_id = {$rowIrh['invoice_id']}
                  AND amount > 0
                ";

                $resultInvHist = $db->sql_query($SQLInvHist);
                $numRowsInvHist = $db->sql_numrows($resultInvHist);
                $rowInvHist = $db->sql_fetchrow($resultInvHist);

                /* Updating status to due if one record in hist table for the invoice */
                if ($numRowsInvHist > 1) {
                } else {
                    $sqlIn = "
                    UPDATE invoice
                    SET status = 'Due'
                    WHERE invoice_id = {$rowIrh['invoice_id']}
                    ";
                    $resultIn = $db->sql_query($sqlIn);
                }

                /* Setting of amount to 0 in history table */
                $SqlInvrec = "
                UPDATE invoice_receipt_history
                SET amount = 0
                WHERE receipt_id = {$irh['receipt_id']}
                ";
                $resultInvrec = $db->sql_query($SqlInvrec);

            }

            /* Updating the status of the receipt in receipt table */
            $sqlRec = "
            UPDATE receipt
            SET receipt_status = 'Cancelled'
            WHERE receipt_code = '{$receipt_code}'
            ";
            $resultRec = $db->sql_query($sqlRec);
        }

        return;
    }

    /**
     *
     */
    function getGenerateReceiptForMedia($receipt_id='') {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');

        ini_set('memory_limit', '512M');

        $receipt_id = $fn->getReqParam('receipt_id');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        $SQL = "
        SELECT r.*
        FROM receipt r
        WHERE r.receipt_id = {$receipt_id}
        ";
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);
        $today = date("Y-m-d");
		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Invoice and print the PDF");
			$pdf->Output();
			return;
		}

        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";
        $lineItemNumber = 1;  // To increment the line item in receipt

        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {
            $invRecHistRec  = $fn->getRecordRowByID('invoice_receipt_history', 'receipt_id', $row['receipt_id']);
            $invoiceRec     = $fn->getRecordRowByID('invoice', 'invoice_id', $invRecHistRec['invoice_id']);

            $projectRec     = $fn->getRecordRowByID('project', 'project_id', $invoiceRec['project_id']);
            $projectCandidateRec     = $fn->getRecordRowByID('project_candidate', 'project_id', $projectRec['project_id']);

            $candRec     = $fn->getRecordRowByID('candidate', 'candidate_id', $projectCandidateRec['candidate_id']);

            $agentRec       = $fn->getRecordRowByID('agent', 'agent_id', $candRec['agent_id']);
            $companyRec     = $fn->getRecordRowByID('company', 'company_id', $projectRec['company_id']);

            $countryNameRecForCompany = $fn->getRecordRowByID('geo_country', 'country_code', "'{$companyRec['address_country_code']}'");
            $countryNameRecForAgent = $fn->getRecordRowByID('geo_country', 'country_code', "'{$agentRec['address_country_code']}'");

            /* Company address */
            $pdf->Image('images/logo-print.jpg',10,5,45);

            $pdf->SetXY(135,1);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetFont('Arial','B',10);
            $pdf->SetX(135);
            $pdf->Cell(50, 20, $cpCfg['cp.companyName']);
            $pdf->SetFont('Arial','',7);
            $pdf->Ln(5);
            $pdf->SetX(135);
            $pdf->Cell(50, 20, $cpCfg['cp.companyAddress1'] . ' ' . $cpCfg['cp.companyAddress2']);
            $pdf->Ln(5);
            $pdf->SetX(135);
            $pdf->Cell(50, 20, $cpCfg['cp.companyAddress3']);
            $pdf->Ln(5);
            $pdf->SetX(135);
            $pdf->Cell(50, 20, $cpCfg['printTelephoneAndFax']);
            $pdf->Ln(18);

            $pdf->SetFont('Arial','B',10);
            $pdf->SetX(90);
            $pdf->Cell(50, 20, "OFFICIAL RECEIPT" );
            $pdf->Ln(10);

            if($invoiceRec['invoice_to'] == 'Agent'){
                $name = $agentRec['first_name'];
            } else {
                $name = $companyRec['company_name'];
            }

            /* Address of the Company */
            $pdf->SetXY(10, 40);
            $pdf->Cell(50, 20, "Received from");
            $pdf->SetFillColor(224,235,255);
            $pdf->Rect(10, 53, 72, 30, 'D');
            $pdf->SetXY(10, 46);
            $pdf->SetFont('Arial','',10);
            $pdf->Cell(50, 20, $name);
            $pdf->SetXY(10, 50);
            /*
            $pdf->Cell(50, 20, $companyRec['company_name']);
            $pdf->SetXY(10, 50);
            $pdf->Cell(50, 20, $companyRec['address_street']);
            $pdf->SetXY(10, 55);
            $pdf->Cell(50, 20, $companyRec['address_town']);
            $pdf->SetXY(10, 60);
            $pdf->Cell(50, 20, $countryNameRec['name'] . ' ' . $companyRec['address_po_code']);
            $pdf->SetXY(10, 65);
            $pdf->Cell(50, 20, 'Phone: ' . $companyRec['phone']);
            */


            if($invoiceRec['invoice_to'] == 'Client'){
                if ($companyRec['address_flat']){
                    $pdf->Cell(50, 20, $companyRec['address_flat']);
                    $pdf->SetXY(10, 55);
                }
                if ($companyRec['address_street']){
                    $pdf->Cell(50, 20, $companyRec['address_street']);
                    $pdf->SetXY(10, 60);
                }
                if ($companyRec['address_town']){
                    $pdf->Cell(50, 20, $companyRec['address_town']);
                    $pdf->SetXY(10, 65);
                }
                if ($companyRec['address_state']){
                    $pdf->Cell(50, 20, $companyRec['address_state'] . ' ' . $companyRec['address_po_code']);
                    $pdf->Ln(5);
                }
            } else {
                if ($agentRec['company_address_flat']){
                    $pdf->Cell(50, 20, $agentRec['company_address_flat']);
                    $pdf->SetXY(10, 55);
                }
                if ($agentRec['company_address_street']){
                    $pdf->Cell(50, 20, $agentRec['company_address_street']);
                    $pdf->SetXY(10, 60);
                }
                if ($agentRec['company_address_town']){
                    $pdf->Cell(50, 20, $agentRec['company_address_town']);
                    $pdf->SetXY(10, 65);
                }
                if ($agentRec['company_address_country']){
                    $pdf->Cell(50, 20, $agentRec['company_address_country'] . ' ' . $agentRec['address_po_code']);
                    $pdf->SetXY(10, 70);
                    $pdf->Ln(5);
                }
            }

            $pdf->drawTextBox('', 73, 35, 'L', 'T', 0);

            /* Recepit code and date */
            //$pdf->SetFont('Arial', 'B', 22);
            //$pdf->SetX(135);
            //$pdf->Cell(40, 20, "Receipt");
            $code = 'Receipt No : '. $row['receipt_code'];
            $pdf->SetFont('Arial','B',10);
            $pdf->SetXY(135, 40);
            $pdf->Cell(50, 20, $code );
            $pdf->Ln(5);

            $pdf->SetX(135);
            $date = $fn->getCPDate($row['date'], 'd-M-Y');
            $pdf->Cell(11, 20, "Date : ");
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(50, 20, $date);
            $pdf->Ln(35);

            /* List of invoice items header */
            #$pdf->SetFont('Arial','B',10);
            #$pdf->SetFillColor(254,203,156);
            #$pdf->Cell(20,8,"Item No", 0,0, 'C', 1);
            #$pdf->Cell(135,8,"Description", 0,0, 'L', 1);
            #$pdf->Cell(35,8,"Sub Total (SGD)", 0,0, 'R', 1);
            #$pdf->Ln();

            /* List of invoice items for the invoice */
            #$pdf->SetFont('Arial','',10);
            #$pdf->SetFillColor(255,255,255);
            #$pdf->Cell(20, 10, $lineItemNumber, 0, 0, 'C', 1);
            #$pdf->Cell(135, 10, $invoiceItemRec['item_title']);
            #$pdf->Cell(35, 10, $invoiceItemRec['unit_price'], 0, 0, 'R', 1);
            #$pdf->Ln(7);

            #$subsidy = number_format($invoiceItemRec['subsidy'], 2);
            #$pdf->Cell(20, 10, $lineItemNumber, 0, 0, 'C', 1);
            #$pdf->Cell(135, 10, 'Rebate');
            #$pdf->Cell(35, 10, $subsidy, 0, 0, 'R', 1);
            #$pdf->Ln();

            $sqlPreviousPayment = "
            SELECT SUM(amount) AS total_amount_paid
            FROM invoice_receipt_history
            WHERE invoice_id = {$invoiceRec['invoice_id']}
              AND receipt_id != {$row['receipt_id']}
            ";
            $resultPreviousPayment = $db->sql_query($sqlPreviousPayment);
            $rowPreviousPayment = $db->sql_fetchrow($resultPreviousPayment);

            $invoice_code = $invoiceRec['invoice_code'];
            $amount_paid = $row['amount'];
            $invoice_amt = $invoiceRec['invoice_amount'];
            $previous_paid_amount = $rowPreviousPayment['total_amount_paid'];
            $balance_due = $invoice_amt - $previous_paid_amount - $amount_paid;

            $invoice_amt = number_format($invoice_amt, 2);
            $previous_paid_amount = number_format($previous_paid_amount, 2);
            $amount_paid = number_format($amount_paid, 2);
            $balance_due = number_format($balance_due, 2);

            $remarks         = $row['remarks'];
            $receipt_code    = $row['receipt_code'];
            $mode_of_payment = $row['mode_of_payment'];

            $count++;
        }

        /* List of invoice items header */
        $pdf->Ln(10);
        $pdf->SetFont('Arial','B',10);
        $pdf->SetFillColor(255,255,255);
        $pdf->Cell(135, 8, "Description", 1, 0, 'L', 1);
        $pdf->Cell(55, 8, "Sub Total (SGD)", 1, 0, 'R', 1);
        $pdf->Ln();

        /* Total amount to be paid */
        $pdf->SetFont('Arial','',10);
        $label = 'Invoice Amount (Invoice Code : ' . $invoice_code . ')';
        $pdf->Cell(135, 8, $label, 1, 0, 'L', 1);
        $pdf->Cell(55, 8, $invoice_amt, 1, 0, 'R');
        $pdf->Ln();

        /* Total amount paid earlier */
        $pdf->Cell(135, 8,'Amount already Paid ', 1, 0, 'L', 1);
        $pdf->Cell(55, 8, $previous_paid_amount, 1, 0, 'R');
        $pdf->Ln();

        /* Total amount paid */
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(135, 8,'Amount Received Now', 1, 0, 'L', 1);
        $pdf->Cell(55, 8, $amount_paid, 1, 0, 'R');
        $pdf->Ln();

        /* Total amount paid */
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(135, 8,'Balance Amount to be Paid', 1, 0, 'L', 1);
        $pdf->Cell(55, 8, $balance_due, 1, 0, 'R');
        $pdf->Ln(15);

        /* Cheque Details */
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(20, 8, 'Payment Method');
        $pdf->Ln(5);

        $pdf->SetFont('Arial','',8);
        $pdf->Cell(130, 8, $mode_of_payment);
        $pdf->Ln(10);

        /* Notes */
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(150, 8, 'Notes:');
        $pdf->Ln(4);

        $pdf->SetFont('Arial','',8);
        $pdf->Cell(150, 8, $remarks);
        $pdf->Ln();

        /* Creation of media record of the invoice */
        $file_name = 'Receipt_' . $receipt_code . '_' . date('Y-m-d') .'.pdf';

        /* Condition for folder path with regards to local and other sites */
        /*if ($config['local']['site'] == 'local') {
            $outputPath = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        } else {
            $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';
        }*/
        $pdf->Output();

        /*
        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/' .'temp';
        $outputFileName = $outputPath . '/'. $file_name;
        $pdf->Output($outputFileName , "F");

        $currentDate  = date("Y-m-d H:i:s");
        $fa = array();
        $fa['record_id']        = $receipt_id;
        $fa['content_type']     = 'application/octet-stream';
        $fa['media_type']       = 'attachment';
        $fa['record_type']      = 'attachment';
        $fa['room_name']        = 'manPower_receipt';
        $fa['lang']             = 'eng';
        $fa['creation_date']    = $currentDate;
        $fa['actual_file_name'] = $file_name;

        $SQL      = $dbUtil->getInsertSQLStringFromArray($fa, "media");
        $result   = $db->sql_query($SQL);
        $media_id = $db->sql_nextid();

        $media_file_name   = $media_id . "_" . $file_name;

        $fa = array();
        $fa['file_name']   = $media_file_name;

        $whereCondition = "WHERE media_id = {$media_id}";
        $SQL            = $dbUtil->getUpdateSQLStringFromArray($fa, "media", $whereCondition);
        $result         = $db->sql_query($SQL);
        */
        /* Condition for folder path with regards to local and other sites */
        /*if ($config['local']['site'] == 'local') {
            $dest = realpath($cpCfg['cp.mediaFolder']) . '\normal/' . $media_file_name;
        } else {
            $dest = realpath($cpCfg['cp.mediaFolder']) . '/normal/' . $media_file_name;
        }*/
        /*
        $dest = realpath($cpCfg['cp.mediaFolder']) . '/normal/' . $media_file_name;

        copy($outputFileName, $dest);
        unlink($outputFileName);
        */
    }

    /**
     *
     */
    function getEditReceiptFormValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $validate->resetErrorArray();
        $validate->validateData('mode_of_payment_edit' , 'Please select mode of payment');
        $validate->validateData('candidate_receipt_date' , 'Please select receipt date');
        
        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getEditCandidateReceiptFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $mode_of_payment        = $fn->getPostParam('mode_of_payment_edit');
        $remarks                = $fn->getPostParam('remarks');
        $receipt_id             = $fn->getReqParam('receipt_id');
        $cheque_no              = $fn->getPostParam('cheque_no');
        $candidate_receipt_date = $fn->getPostParam('candidate_receipt_date');

        if (!$this->getEditReceiptFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = array();
        $fa['mode_of_payment']   = $mode_of_payment;
        $fa['remarks']           = $remarks;
        $fa['date']              = $candidate_receipt_date;
        $fa['modification_date'] = date("Y-m-d H:i:s");
        $fa['modified_by']       = $fn->getSessionParam('userName');
        $fa['cheque_no']         = $cheque_no;

        $whereCondition = "WHERE receipt_id = {$receipt_id}";
        $updateSQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'receipt', $whereCondition);
        $resultSQL = $db->sql_query($updateSQL);

        return $validate->getSuccessMessageXML();
    }
}
