<?
class CP_Admin_Modules_EnterpriseIms_GiroPayment_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {

        $SQL = "
        SELECT c.contact_id
              ,c.first_name
              ,c.last_name
              ,i.order_id
              ,CONCAT_WS(' ', p.first_name, p.last_name ) AS parent_name
              ,p.dda
              ,i.invoice_code
              ,i.invoice_id
              ,s.title AS branch_name
        FROM contact c 
        JOIN (invoice i)                ON (i.contact_id = c.contact_id)
        LEFT JOIN (parent_contact pc)   ON (c.contact_id = pc.contact_id)
        LEFT JOIN (parent p)            ON (pc.parent_id = p.parent_id)
        LEFT JOIN (site s)              ON (c.site_id    = s.site_id)
        ";
        
        return $SQL;
    }

    /**
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        #$searchVar->mainTableAlias = 'c';

        $site_id         = $fn->getReqParam('site_id');
        $contact_id      = $fn->getReqParam('contact_id');
        $invoice_status  = $fn->getReqParam('invoice_status');
        $year            = $fn->getReqParam('year');
        $month           = $fn->getReqParam('month');
        $keyword         = $fn->getReqParam('keyword');
        $userGroupType   = $fn->getSessionParam('userGroupType');

        $searchVar->sqlSearchVar[] = "i.status != 'Cancelled'";
        $searchVar->sqlSearchVar[] = "c.status = 'Active'";
        $searchVar->sqlSearchVar[] = "p.mode_of_payment = 'Giro'";

        $searchVar->groupBy = "i.contact_id";
        $searchVar->sortOrder = "i.status ASC, p.parent_id";

        if ($userGroupType == 'Super Administrator') {
            if ($site_id != '') {
                $searchVar->sqlSearchVar[] = "c.site_id = '{$site_id}'";
            }
        } else {
            $searchVar->sqlSearchVar[] = "c.site_id = {$_SESSION['cp_site_id']}";
        }

        if ($invoice_status != '') {
            $searchVar->sqlSearchVar[] = "i.status = '{$invoice_status}'";
        }

        if ($year != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(i.invoice_date, '%Y') = '{$year}'";
        } 
        
        if ($month != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(i.invoice_date, '%m') = '{$month}'";
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
    function getGiroPaymentSubmit() {
        /********************************* PROCESS ************************************/
        /*
        ACTION: PROCESS WHEN GIRO PAYMENT IS SUBMITTED
        STEP 1: SEPARATION OF INVOICE CODES
        STEP 2: UPDATION OF GIRO FAILED FIELD VALUE TO YES FOR CHECKED INVOICES
        STEP 3: SQL TO FIND THE LIST OF INVOICES WHOSE INVOICES ARE NOT CHECKED
        STEP 4: FINDING THE RECEIPT CODE ACCORDING TO THE INVOICE
        STEP 5: CREATION OF RECEIPT RECORD
        STEP 6: UPDATION OF RECEIPT CODE IN SETTING TABLE
        STEP 7: UPDATION OF INVOICE STATUS TO PAID
        STEP 8: CREATION OF INVOICE RECEIPT HISTORY RECORD
        */
        /******************************* END PROCESS **********************************/

        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $mode_of_payment = 'Giro';
        $month           = $fn->getReqParam('month');
        $enrollment_year = $fn->getReqParam('enrollment_year');

        /********************************** STEP 1 **************************************/
        $invoiceCodes = $_SESSION['selectedInvoiceCodes'];
        $count = count($invoiceCodes);
        
        $_SESSION['createdInvoice'] = 1;
        unset($_SESSION['selectedInvoiceCodes']);
        
        $invoiceCodes = join(',', $invoiceCodes);
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
        
        /********************************** STEP 1 ENDS HERE ****************************/
        
        /********************************** STEP 2 **************************************/
        if ($mode_of_payment == 'Giro') {
            $SQLInvFailure = "
            UPDATE invoice SET giro_failed = 'Yes' 
            WHERE invoice_code IN ($invoice_code)
            ";
            $resultInvFailure = $db->sql_query($SQLInvFailure);
            
            $SQLInvSuccess = "
            UPDATE invoice SET giro_failed = 'No' 
            WHERE invoice_code NOT IN ($invoice_code)
              AND invoice_month = {$month}
            ";
            $resultInvSuccess = $db->sql_query($SQLInvSuccess);  
        }
        /********************************** STEP 2 ENDS HERE ****************************/
        
        /********************************** STEP 3 **************************************/
        $sqlAppend = "";
        if ($count > 0) {
        $sqlAppend = "
        AND i.invoice_code NOT IN ($invoice_code)
        ";
        }        

        $sqlInv = "
        SELECT i.invoice_code
              ,i.invoice_id
              ,i.invoice_amount
              ,i.order_id
              ,i.site_id
        FROM invoice i
        LEFT JOIN `order` o ON (i.order_id = o.order_id)
        LEFT JOIN contact cont ON (i.contact_id = cont.contact_id)
        LEFT JOIN parent_contact pc ON (cont.contact_id = pc.contact_id)
        LEFT JOIN parent p ON (pc.parent_id = p.parent_id)
        WHERE i.invoice_month = {$month}
        AND i.invoice_id != ''
        AND p.mode_of_payment = '{$mode_of_payment}'
        AND i.status = 'Due'
        AND o.year_of_enrollment = '{$enrollment_year}'
        {$sqlAppend}
        AND cont.status = 'Active'
        ORDER BY i.invoice_code
        ";
        $resultInv = $db->sql_query($sqlInv);
        /********************************** STEP 3 ENDS HERE ****************************/

        while ($row = $db->sql_fetchrow($resultInv)) {

            /********************************** STEP 4 **************************************/
            $invoice_amount = $row['invoice_amount'];
            $invoice_id     = $row['invoice_id'];
            $order_id       = $row['order_id'];
            $site_id        = $row['site_id'];
            
            $modObj         = getCPModuleObj('pms_order');
            $receiptCode    = $modObj->model->getFindReceiptCodeWithSite($site_id);
            $receiptCodePfx = $modObj->model->getReceiptCodePrefixWithSite($site_id);

            if($receiptCode < 10) {
                $receipt_code = $receiptCodePfx . '000' . $receiptCode;
            } else if($receiptCode < 99) {
                $receipt_code = $receiptCodePfx . '00' . $receiptCode;
            } else if($receiptCode < 999) {
                $receipt_code = $receiptCodePfx . '0' . $receiptCode;
            } else {
                $receipt_code = $receiptCodePfx . $receiptCode;
            }
            /********************************** STEP 4 ENDS HERE ****************************/
            
            /********************************** STEP 5 **************************************/
            $fa = array();
            $fa['order_id']       = $order_id;
            $fa['amount']         = $invoice_amount;
            $fa['receipt_code']   = $receipt_code;
            $fa['mode_of_payment']= $mode_of_payment;
            $fa['date']           = date("Y-m-d H:i:s");
            $fa['receipt_status'] = 'Paid';
            $fa['site_id']        = $site_id;
            $fa['creation_date']  = date("Y-m-d H:i:s");
            $fa['created_by']     = $fn->getSessionParam('userName');
            
            $insertReceiptSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'receipt');
            $resultSQL          = $db->sql_query($insertReceiptSQL);
            $receipt_id         = $db->sql_nextid();
            /********************************** STEP 5 ENDS HERE ****************************/

            /********************************** STEP 6 **************************************/
            if ($site_id) {
                $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode' AND site_id = '{$site_id}'";
            } else {
                $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode'";
            }
            $resultUpdate = $db->sql_query($SQLUpdate);
            /********************************** STEP 6 ENDS HERE ****************************/

            /********************************** STEP 7 **************************************/
            $invoice_code = "'" . $row['invoice_code'] . "'";
            $modification_date = date('Y-m-d H:i:s');
            $SQL = "
            UPDATE invoice SET
            status = 'Paid',
            modification_date = '{$modification_date}'
            WHERE invoice_code = {$invoice_code}
            ";
            $result = $db->sql_query($SQL);
            /********************************** STEP 7 ENDS HERE ****************************/
            
            /********************************** STEP 8 **************************************/
            $fa = array();
            $fa['receipt_id']    = $receipt_id;
            $fa['invoice_id']    = $invoice_id;
            $fa['amount']        = $invoice_amount;
            $fa['creation_date'] = date("Y-m-d H:i:s");
            $histId = $fn->addRecord($fa, 'invoice_receipt_history');
            /********************************** STEP 8 ENDS HERE ****************************/
        }

        return $validate->getSuccessMessageXML();
    }
    
    /**
    *
    */
    function getGenerateDBSTxtFile() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $text =  '';
        
        $month = $fn->getReqParam('month');
        $year  = $fn->getReqParam('year');

        //$leftContent = 'WTF Hello this is not working';
        $text .= $this->getTextFileForDBSHeader();
        $text .= $this->getTextFileForDBS($month, $year);
        $template = 'dbs_import.txt';
        $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
        $templatefile = fopen("{$templatePath}","w");
        fwrite($templatefile, $text);
        //fclose($templatefile);
        header ("Content-Type: application/download");
        header ("Content-Disposition: attachment; filename=$templatePath");
        header("Content-Length: " . filesize("$templatePath"));
        $fp = fopen("$templatePath", "r");
        fpassthru($fp);
        //header("Location: $templatePath");
    }
    
    /**
    *
    */
    function getTextFileForDBSHeader() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $text = '';
        $today = date('ymd');
        $text .= $today;
        $spaces = 45;
        $text .= str_repeat(" ",$spaces). '71710270279022981';
        $spaces = 3;
        $text .= str_repeat(" ",$spaces). 'AS SIDDIQ CENTRE';
        $spaces = 4;
        $text .= str_repeat(" ",$spaces). '00001ASSIDD01';
        $spaces = 9;
        $text .= str_repeat(" ",$spaces). '0'."\n";
        
        return $text;
    }
    
    /**
    *
    */
    function getTextFileForDBS($month, $year) {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $text = '';
        $total_amount_in_cents = 0;
        $transaction_code = 30;
        $spaces = 58;
        $record_type = 1;
        $total_hash_amount = 0;
        
        if ($year == '') {
            $year = date('Y');
        }

        $SQL = "
        SELECT i.*
              ,p.account_no 
              ,p.bank_code 
              ,p.branch 
              ,p.account_name 
              ,p.dda 
              ,p.parent_id
              ,SUM(i.invoice_amount) AS total_invoice_amount
              ,SUM(i.discount_amount) AS total_discount_amount
        FROM invoice i
        LEFT JOIN `order` o ON (i.order_id = o.order_id)
        LEFT JOIN contact cont ON (i.contact_id = cont.contact_id)
        LEFT JOIN parent_contact pc ON (cont.contact_id = pc.contact_id)
        LEFT JOIN parent p ON (pc.parent_id = p.parent_id)
        WHERE i.invoice_month = {$month}
        AND cont.status = 'Active'
        AND p.mode_of_payment = 'Giro'
        AND p.giro_process_done = 1
        AND i.status = 'Due'
        AND o.year_of_enrollment = '{$year}'
        GROUP BY p.parent_id
        ";
        $result   = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);
        while ($row = $db->sql_fetchrow($result)) {
            $acNo = '';
            $acNoSpace = 0;
            $name = '';
            $nameSpace = 0;
            $amount = '';
            $hash_amount = 0;
            
            //checking ac no has 11 characters and truncating the rest of the character
            if($row['account_no']){
                $acNo  = substr($row['account_no'], 0, 10);
            }
            
            # checking whether bank code and branch code are available or not
            if($row['bank_code'] && $row['branch']){
                $text .= $row['bank_code']. $row['branch'] . $acNo;
                $prefix_no_for_ac = substr($row['account_no'], 0, 6);
                
                $suffix_ac_no = substr($row['account_no'], 6, 10);
                $suffix_no_for_ac = $this->getAddingZerosForAccountNo($suffix_ac_no);
                
                $account_calc = (($prefix_no_for_ac) - $suffix_no_for_ac);
                $hash_amount = $account_calc + 1908;
                
                # To check whether the first character is - or +
                $symbol_check = substr($hash_amount, 0, 1);
                if ($symbol_check == '-') {
                    $hash_amount = substr($hash_amount, 1, 5);
                } else {
                    $hash_amount = $hash_amount;
                }
                
            } else {
                $bank_spaces = 7;
                $text .= str_repeat(" ",$bank_spaces);;
            }
            
            //adding space if there is less than 11 character
            $acNoSpace = 11 - strlen($acNo);
            $text     .= str_repeat(" ",$acNoSpace);
            
            //checking name if it has 20 characters and truncating the rest of the character
            if($row['account_name']){
                $name      = substr($row['account_name'], 0, 19);
            }
            
            //adding space if there is less than 20 character
            if($name){
                $text .=  strtoupper($name);
                $nameSpace = 20 - strlen($name);
                if($nameSpace){
                    $text .=  str_repeat(" ",$nameSpace);
                }
            } else {
                $name_spaces = 20;
                $text .= str_repeat(" ",$name_spaces);                
            }

            $text .=  $transaction_code;
            
            //to get the invoice amount and convert in to space, it should be of 11 characters like  00000012000 - ( Cents)
            if ($row['total_discount_amount'] > 0) {
                $total_invoice_amt = $row['total_invoice_amount'] - $row['total_discount_amount'];
            } else {
                $total_invoice_amt = $row['total_invoice_amount'];
            }
            
            $amount =  $total_invoice_amt * 100;

            //$amount =  120 * 100;
            
            $text .=  $this->getAddingZerosForAmount($amount);
            
            # checking whether dda number is available or not
            if ($row['dda']) {
                $text .=  str_repeat(" ",$spaces). $row['dda']. $record_type ;
            } else {
                $dda_spaces = 4;
                $text .= str_repeat(" ",$spaces) . str_repeat(" ",$dda_spaces). $record_type ;                
            }
            
            $text .=   "\n";
            
            # Finding total of all the student records
            $total_amount_in_cents += $amount;
            # Finding total hash amount for all the students
            $total_hash_amount += $hash_amount;
        }
        
        //footer code comes here.
        $zeros = 19;
        $text .=  str_repeat("0",$zeros);

        $footer_spaces = 5;
        $text .=  str_repeat(" ",$footer_spaces);
        
        $text .= $this->getAddingZerosForTotalRecord($numRows);
        $text .= $this->getAddingZerosForAmount($total_amount_in_cents);
        
        $footer_spaces_left = 26;
        $text .=  str_repeat(" ",$footer_spaces_left);
        
        $text .= $this->getAddingZerosForAmount($total_hash_amount);
        
        $footer_spaces_right = 33;
        $text .=  str_repeat(" ",$footer_spaces_right);

        $text .= "9";
        
        return $text;
    }

    /**
    *
    */
    function getAddingZerosForAccountNo($suffix_ac_no) {

        if(strlen($suffix_ac_no) == 1){
            $suffix_no = $suffix_ac_no . '0000';
        } 
        else if(strlen($suffix_ac_no) == 2) {
            $suffix_no = $suffix_ac_no . '000';
        }
        else if(strlen($suffix_ac_no) == 3) {
            $suffix_no = $suffix_ac_no . '00';
        }
        else if(strlen($suffix_ac_no) == 4) {
            $suffix_no = $suffix_ac_no . '0';
        }
        else{
            $suffix_no = $suffix_ac_no;
        }
            
        return $suffix_no;
            
    }
    
    /**
    *
    */
    function getAddingZerosForAmount($amount) {

        if(strlen($amount) == 1){
            $amount = '0000000000' . $amount;
        } 
        else if(strlen($amount) == 2) {
            $amount = '000000000' . $amount;
        }
        else if(strlen($amount) == 3) {
            $amount = '00000000' . $amount;
        }
        else if(strlen($amount) == 4) {
            $amount = '0000000' . $amount;
        }
        else if(strlen($amount) == 5){
            $amount = '000000' . $amount;
        }
        else if(strlen($amount) == 6){
            $amount = '00000' . $amount;
        }
        else if(strlen($amount) == 7){
            $amount = '0000' . $amount;
        }
        else if(strlen($amount)== 8){
            $amount = '000' . $amount;
        }
        else if(strlen($amount) == 9){
            $amount = '00' . $amount;
        }
        else if(strlen($amount) == 10){
            $amount = '0' . $amount;
        }
        else if(strlen($amount) == 11){
            $amount = $amount;
        }
        else{
            $amount = '00000000000';
        }
            
        return $amount;
            
    }
    
    /**
    *
    */
    function getAddingZerosForTotalRecord($numRows) {

        if(strlen($numRows) == 1){
            $num_rows = '0000000' . $numRows;
        } 
        else if(strlen($numRows) == 2){
            $num_rows = '000000' . $numRows;
        } 
        else if(strlen($numRows) == 3){
            $num_rows  = '00000' . $numRows;
        } 
        else if(strlen($numRows) == 4) {
            $num_rows = '0000' . $numRows;
        }
        else if(strlen($numRows) == 5){
            $num_rows = '000' . $numRows;
        }
        else if(strlen($numRows) == 6){
            $num_rows = '00' . $numRows;
        }
        else if(strlen($numRows) == 7){
            $num_rows = '0' . $numRows;
        }
        else if(strlen($numRows)== 8){
            $num_rows = $numRows;
        }
        else{
            $num_rows = '00000000';
        }
            
        return $num_rows;            
    }
}
