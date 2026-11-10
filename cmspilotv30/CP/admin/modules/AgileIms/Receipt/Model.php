<?
class CP_Admin_Modules_AgileIms_Receipt_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
                
        $SQL = "
        SELECT r.*
            ,cont.contact_id
            ,cont.first_name AS contact_name
            ,co.company_id
            ,co.title
        FROM receipt r
        LEFT JOIN (`order` o) ON (r.order_id = o.order_id)
        LEFT JOIN (contact cont) ON (o.contact_id = cont.contact_id)
        LEFT JOIN (company co) ON (o.company_id = co.company_id)
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

        $receipt_id      = $fn->getReqParam('receipt_id');
        $receipt_date1   = $fn->getReqParam('receipt_date1');
        $receipt_date2   = $fn->getReqParam('receipt_date2');
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
    
            if ($receipt_status != "") {
                $searchVar->sqlSearchVar[] = "r.receipt_status = '{$receipt_status}'";
            }

            if ($mode_of_payment != "") {
                $searchVar->sqlSearchVar[] = "r.mode_of_payment = '{$mode_of_payment}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                                        r.order_id LIKE '%{$tv['keyword']}%' 
                                     OR r.receipt_code LIKE '%{$tv['keyword']}%'
                                     OR cont.first_name LIKE '%{$tv['keyword']}%'
                                     OR cont.last_name LIKE '%{$tv['keyword']}%'
                                       )";
            }
        }        
        $searchVar->sortOrder = "r.receipt_id DESC";
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
        $fa = $fn->addToFieldsArray($fa, 'order_id');
        
        return $fa;
    }

    /**
     *
     */
    function getFetchReceiptCode() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        /* Setting of Receipt code */
        $recCodeNo  = $fn->getSettingsValueByKey("nextReceiptCode");   //  eg: 123

        if($recCodeNo < 10) {
            $receipt_code = '000' . $recCodeNo;
        } else if($recCodeNo < 99) {
            $receipt_code = '00' . $recCodeNo;
        } else if($recCodeNo < 999) {
            $receipt_code = '0' . $recCodeNo;
        } else {
            $receipt_code = $recCodeNo;
        }

        return $receipt_code;
    }

    /**
     */
    function getGenerateReceiptFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        
        $receipt_type = $fn->getPostParam('receipt_type');

        if (!$this->getGenerateReceiptFormValidate($receipt_type)){
            return $validate->getErrorMessageXML();
        }

        $invoiceCode     = $fn->getPostParam('invoiceCode', array());
        $receipt_date    = $fn->getPostParam('date');
        $receipt_amount  = $fn->getPostParam('amount');
        $mode_of_payment = $fn->getPostParam('mode_of_payment');
        $cheque_date     = $fn->getPostParam('cheque_date');
        $cheque_no       = $fn->getPostParam('cheque_no');
        $bank_name       = $fn->getPostParam('bank_name');
        $remarks         = $fn->getPostParam('remarks');
        $issued_by       = $fn->getPostParam('issued_by');
        $order_id        = $fn->getReqParam('order_id');
        
        if ($receipt_type == 'misc receipt') {
            $late_fee       = $fn->getPostParam('late_fees');
            $change_fee     = $fn->getPostParam('module_subject_change_fee');
            $review_fee     = $fn->getPostParam('exam_result_review_fee');
            $deferment_fees = $fn->getPostParam('ns_deferment_fees');
            $service_fees   = $fn->getPostParam('credit_card_service_fees');
            $other_charges  = $fn->getPostParam('other_charges');

            $fa = array();
            $fa['amount']         = $amount;
            $fa['order_id']       = $order_id;
            
            //if ($course_type == 'Short Term') {
                
            $rowPrefix = $fn->getRecordByCondition('setting', "key_text = 'miscReceiptCodePrefix'");
            $current_year = date('Y');

            $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode'";
            $resultUpdate = $db->sql_query($SQLUpdate);
            $nextReceiptCode = $fn->getSettingsValueByKey("nextReceiptCode");

            if($nextReceiptCode < 10){
                $fa['receipt_code'] = $rowPrefix['value'] . '-' . $current_year . '-0000' . $nextReceiptCode;
            }
            else if($nextReceiptCode < 100){
                $fa['receipt_code'] = $rowPrefix['value'] . '-' . $current_year . '-000' . $nextReceiptCode;
            }
            else if($nextReceiptCode > 99){
                $fa['receipt_code'] = $rowPrefix['value'] . '-' . $current_year . '-00' . $nextReceiptCode;
            }
            else if($nextReceiptCode > 99 && $nextReceiptCode < 1000){
                $fa['receipt_code'] = $rowPrefix['value'] . '-' . $current_year . '-00' . $nextReceiptCode;
            }
            else if($nextReceiptCode > 999 && $nextReceiptCode < 10000){
                $fa['receipt_code'] = $rowPrefix['value'] . '-' . $current_year . '-0' . $nextReceiptCode;
            }
            else{
                $fa['receipt_code'] = $rowPrefix['value'] . '-' . $current_year . '-' . $nextReceiptCode;
            }
            /*} else {
                $fa['receipt_code']   = $receipt_code;
            }*/
            
            $fa['mode_of_payment']= $mode_of_payment;
            $fa['cheque_date']    = $cheque_date;
            $fa['cheque_no']      = $cheque_no;
            $fa['bank_name']      = $bank_name;
            $fa['remarks']        = $remarks;
            $fa['date']           = $receipt_date;
            $fa['issued_by']      = $issued_by;
            $fa['coi_no']         = $coi_no;
            $fa['approval_code']  = $approval_code;
            $fa['receipt_status'] = 'Paid';
            $fa['creation_date']  = date("Y-m-d H:i:s");
            $fa['created_by']     = $fn->getSessionParam('userName');
            
            $insertReceiptSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'receipt');
            $resultSQL          = $db->sql_query($insertReceiptSQL);
            $receipt_id         = $db->sql_nextid();
            
            //Inserting receipt id in to history table ( one invoice can have multiple receipts)
            $fa = array();
            $fa['receipt_id']    = $receipt_id;
            $fa['invoice_id']    = $invoice_id;
            $fa['amount']        = $amount;
            $fa['invoice_date']  = $receipt_date;
            $fa['creation_date'] = date("Y-m-d H:i:s");
            $fa['created_by']    = $fn->getSessionParam('userName');
            $fa['receipt_type']  = 'misc receipt';
            $histId = $fn->addRecord($fa, 'invoice_receipt_history');
    
            $this->getGenerateMiscReceiptForMediaPvt($invoice_id, $receipt_id, $order_id, $late_fee, $change_fee, $review_fee, $deferment_fees, $service_fees, $other_charges);
        } else {
            $receipt_code = $this->getFetchReceiptCode();

            $faRec = array();
            $faRec['receipt_code']     = $receipt_code;
            $faRec['date']             = $receipt_date;
            $faRec['amount']           = $receipt_amount;
            $faRec['mode_of_payment']  = $mode_of_payment;
            $faRec['cheque_date']      = $cheque_date;
            $faRec['cheque_no']        = $cheque_no;
            $faRec['bank_name']        = $bank_name;
            $faRec['remarks']          = $remarks;
            $faRec['issued_by']        = $issued_by;
            $faRec['order_id']         = $order_id;
            $faRec['creation_date']    = date('Y-m-d H:i:s');
            $faRec['created_by']       = $fn->getSessionParam('userName');
            $faRec['receipt_status']   = 'Paid';
            $receipt_id                = $fn->addRecord($faRec, 'receipt');

            /* Increment of Receipt Code */
            $SQLUpdate    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode'";
            $resultUpdate = $db->sql_query($SQLUpdate);
            
            /* Creating invoice receipt history record */
            foreach ($invoiceCode AS $invoice_code){
                $invoiceRec     = $fn->getRecordByCondition('invoice', "invoice_code = '{$invoice_code}'");
                $invoice_amount = $invoiceRec['invoice_amount'];
                
                if ($invoiceRec['status'] == 'Paid' || $receipt_amount <= 0) {
                    continue;
                }
                
                $SQLPaid = "
                SELECT SUM(amount) AS prev_sum
                FROM invoice_receipt_history
                WHERE invoice_id = '{$invoiceRec['invoice_id']}'
                ";
                $resultPaid = $db->sql_query($SQLPaid);
                $rowPaid    = $db->sql_fetchrow($resultPaid);
                
                $invoice_amount = $invoice_amount - $rowPaid['prev_sum']; 
    
                $faInv = array();
                $recpInvAmount = 0;
                if ($invoice_amount <= $receipt_amount){
                    $recpInvAmount   = $invoice_amount;
                    $faInv['status'] = 'Paid';
                } else if ($invoice_amount > $receipt_amount){
                    $recpInvAmount   = $receipt_amount;
                    $faInv['status'] = 'Partial Payment';
                }
                $faInv['modification_date'] = date('Y-m-d H:i:s');
                $faInv['modified_by']       = $fn->getSessionParam('userName');
                $fn->saveRecord($faInv, 'invoice', 'invoice_code', $invoice_code);
                
                $receipt_amount = $receipt_amount - $recpInvAmount;
                
                //Inserting receipt id in to history table ( one invoice can have multiple receipts)
                $fa = array();
                $fa['invoice_id']    = $invoiceRec['invoice_id'];
                $fa['receipt_id']    = $receipt_id;
                $fa['creation_date'] = date("Y-m-d H:i:s");
                $fa['created_by']    = $fn->getSessionParam('userName');
                $fa['amount']        = $recpInvAmount;
                $histId = $fn->addRecord($fa, 'invoice_receipt_history');
            }
    
            //To create PDF related to receipt and save it in media
            //$this->getGenerateReceiptForMediaPvt($invoice_id, $receipt_id, $order_id, $first_receipt_present);
        }
        
        return $validate->getSuccessMessageXML();
    }

    /**
     */
    function getGenerateReceiptFormValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        
        $invoice_amount      = 0;
        $invoice_prev_amount = 0;
        $balance_amount      = 0;
        $amount          = $fn->getPostParam('amount');
        $invoiceCodes    = $fn->getPostParam('invoiceCode', array());

        //==================================================================//
        $validate->resetErrorArray();

        $count = count($invoiceCodes);
        
        if ($count == '') {
            $msg = 'Please check atleast one invoice';
            $validate->validateData('error_box', $msg);
        } else {
            $modObj = getCPModuleObj('agileIms_order');
            $invoice_code = $modObj->model->getSeparationOfInvoiceCodes($invoiceCodes);

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
            
            $balance_amount = $total_invoice_amount - $prev_sum;

            if ($amount > $balance_amount) {
                $validate->errorArray['amount']['name'] = "amount";
                $validate->errorArray['amount']['msg']  = 'You can input a maximum of ' . $balance_amount . ' in amount for chosen invoices';
            }
        }
        
        if ($amount < 1) {
            $validate->errorArray['amount']['name'] = "amount";
            $validate->errorArray['amount']['msg']  = 'Please enter invoice amount more than 0.';
        }
        
        $validate->validateData('amount', 'Please check the invoice(s) above to populate the amount');
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
    function getCancelReceipt() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $receipt_code = $fn->getReqParam('receipt_code');

        $irh = $fn->getRecordByCondition('receipt', "receipt_code = '{$receipt_code}'");
        
        $sqlIrh = "
        SELECT invoice_id FROM invoice_receipt_history
        WHERE receipt_id = {$irh['receipt_id']}
        ";
        $resultIrh = $db->sql_query($sqlIrh);
        while ($rowIrh = $db->sql_fetchrow($resultIrh)) {
            /* Setting of amount to 0 in history table */
            $SqlInvrec = "
            UPDATE invoice_receipt_history
            SET amount = 0
            WHERE receipt_id = {$irh['receipt_id']}
            ";
            $resultInvrec = $db->sql_query($SqlInvrec);
            
            $sqlInvPayment = "
            SELECT SUM(amount) AS total_amount_paid_for_invoice FROM invoice_receipt_history
            WHERE invoice_id = {$rowIrh['invoice_id']}
            ";
            $resultInvPayment = $db->sql_query($sqlInvPayment);
            $rowInvPayment = $db->sql_fetchrow($resultInvPayment);
            
            /* Updating Invoice status according to the payment */
            $faInv = array();
            if ($rowInvPayment['total_amount_paid_for_invoice'] > 0) {
                $faInv['status'] = 'Partial Payment';
            } else {
                $faInv['status'] = 'Due';
            }
            
            $faInv['modification_date'] = date('Y-m-d H:i:s');
            $faInv['modified_by']       = $fn->getSessionParam('userName');

            $whereCondition = "WHERE invoice_id = {$rowIrh['invoice_id']}";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($faInv, 'invoice', $whereCondition);
            $db->sql_query($SQL);
        }

        $current_date = date('Y-m-d H:i:s');
        /* Updating the status of the receipt in receipt table */
        $sqlRec = "
        UPDATE receipt
        SET receipt_status = 'Cancelled'
           ,modification_date = '{$current_date}'
           ,modified_by = '{$fn->getSessionParam('userName')}'
        WHERE receipt_code = '{$receipt_code}'
        ";
        $resultRec = $db->sql_query($sqlRec);
        
        return;
    }

    /**
     *
     */
    function getEditReceiptFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        
        $receipt_id      = $fn->getReqParam('receipt_id');
        $date            = $fn->getPostParam('date');
        $mode_of_payment = $fn->getPostParam('mode_of_payment');
        $cheque_date     = $fn->getPostParam('cheque_date');
        $cheque_no       = $fn->getPostParam('cheque_no');
        $bank_name       = $fn->getPostParam('bank_name');
        $remarks         = $fn->getPostParam('remarks');

        if (!$this->getEditReceiptFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $faInv = array();
        $faInv['date']            = $date;
        $faInv['mode_of_payment'] = $mode_of_payment;
        $faInv['cheque_date']     = $cheque_date;
        $faInv['cheque_no']       = $cheque_no;
        $faInv['bank_name']       = $bank_name;
        $faInv['remarks']         = $remarks;

        $fn->saveRecord($faInv, 'receipt', 'receipt_id', $receipt_id);

        return $validate->getSuccessMessageXML();
    } 

    /**
     *
     */
    function getEditReceiptFormValidate() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $date = $fn->getPostParam('date');

        $validate->resetErrorArray();
        $validate->validateData('date' , 'Please select receipt date');
        
        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
}
