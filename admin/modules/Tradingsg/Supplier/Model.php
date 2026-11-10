<?
class CPL_Admin_Modules_Tradingsg_Supplier_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT s.*
              ,gc.name AS country_name
        FROM supplier s
        LEFT JOIN (geo_country gc) ON (s.address_country = gc.country_code)
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
        $searchVar->mainTableAlias = 's';

        $status       = $fn->getReqParam('status');
        $supplier_id   = $fn->getReqParam('supplier_id');
        $company_name = $fn->getReqParam('company_name');

        if ($supplier_id != "") {
            $searchVar->sqlSearchVar[] = "s.supplier_id = '{$supplier_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "s.supplier_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 's.supplier_id');


            if ($status != "") {
                $searchVar->sqlSearchVar[] = "s.status = '{$status}'";
            }

            if ($company_name != "") {
                $searchVar->sqlSearchVar[] = "s.company_name LIKE '%{$company_name}%'";
            }

            if ($_SESSION['userGroupName'] == "Supplier") {
                $searchVar->sqlSearchVar[] = "s.supplier_id = '{$_SESSION['supplier_id']}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    s.company_name  LIKE '%{$tv['keyword']}%'
                    OR s.email      LIKE '%{$tv['keyword']}%'
                )";
            }

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "s.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(s.flag != 1 OR s.flag IS null)";
            }

            $searchVar->sortOrder = "s.company_name";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('company_name', 'Please enter the company name');

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
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        //$fn->returnAfterNewSave($id, $cpCfg['cp.pagetoReturnAfterSave']);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getSaveList(){
        $fn = Zend_Registry::get('fn');
        $fn->getSaveList();
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'company_name');
        $fa = $fn->addToFieldsArray($fa, 'code');
        $fa = $fn->addToFieldsArray($fa, 'website');
        $fa = $fn->addToFieldsArray($fa, 'company_size');
        $fa = $fn->addToFieldsArray($fa, 'industry');
        $fa = $fn->addToFieldsArray($fa, 'source');
        $fa = $fn->addToFieldsArray($fa, 'address_flat');
        $fa = $fn->addToFieldsArray($fa, 'address_street');
        $fa = $fn->addToFieldsArray($fa, 'address_town');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'address_country');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'return_address_flat');
        $fa = $fn->addToFieldsArray($fa, 'return_address_street');
        $fa = $fn->addToFieldsArray($fa, 'return_address_town');
        $fa = $fn->addToFieldsArray($fa, 'return_address_state');
        $fa = $fn->addToFieldsArray($fa, 'return_address_country');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'group_name');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'source');
        $fa = $fn->addToFieldsArray($fa, 'industry');
        $fa = $fn->addToFieldsArray($fa, 'company_size');
        $fa = $fn->addToFieldsArray($fa, 'supplier_type');
        $fa = $fn->addToFieldsArray($fa, 'customer_type');
        $fa = $fn->addToFieldsArray($fa, 'mark_up_percentage');
        $fa = $fn->addToFieldsArray($fa, 'notification_email');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'mobile');

        return $fa;
    }

    /**
     *
     */
    function getCreateLoginFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getCreateLoginFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $email  = $fn->getPostParam('email');
        $pass_word    = $fn->getPostParam('pass_word');
        $supplier_id  = $fn->getPostParam('supplier_id');
        $first_name  = $fn->getPostParam('first_name');
        $last_name  = $fn->getPostParam('last_name');

        $fa = array();
        $fa['user_group_id']   = 10;
        $fa['email']   = $email;
        $fa['published'] = 1;
        $fa['creation_date']     = date("Y-m-d H:i:s");
        $fa['pass_word']   = $pass_word;
        $fa['first_name']   = $first_name;
        $fa['last_name']   = $last_name;
        $fa['supplier_id']   = $supplier_id;
        $fa['status']   = 'Current';

        $staff_id = $fn->addRecord($fa, 'staff');

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getCreateLoginFormValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

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
    function getGeneratePurchaseOrderFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $poCodes    = $fn->getPostParam('poCode', array());
        $amount          = $fn->getPostParam('amount');
        $mode_of_payment = $fn->getPostParam('mode_of_payment');
        $remarks         = $fn->getPostParam('remarks');
        $supplier_id        = $fn->getReqParam('supplier_id');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        if (!$this->getGeneratePurchaseOrderFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $count = count($poCodes);

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND site_id = {$cpSiteIdSession}";
        }

        //To update receipt codes
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode' {$appendSql}";
        $resultUpdate = $db->sql_query($SQLUpdate);
        $receipt_code = $fn->getSettingsValueByKey("nextReceiptCode");

        $fa = array();
        $fa['amount']          = $amount;
        $fa['supplier_id']     = $supplier_id;
        $fa['receipt_code']    = 'RCPT - ' . $receipt_code;
        $fa['mode_of_payment'] = $mode_of_payment;

        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $fa['site_id'] = $cpSiteIdSession;
        }

        $fa['remarks']        = $remarks;
        $fa['date']           = date("Y-m-d H:i:s");
        $fa['receipt_status'] = 'Paid';
        $fa['creation_date']  = date("Y-m-d H:i:s");
        $fa['created_by']     = $fn->getSessionParam('userName');

        $insertReceiptSQL     = $dbUtil->getInsertSQLStringFromArray($fa, 'supplier_receipt');
        $resultSQL            = $db->sql_query($insertReceiptSQL);
        $supplier_receipt_id  = $db->sql_nextid();
        $receipt_amount       = $amount;
        $invoice_status_due   = '';
        $count = 0;

        foreach($poCodes AS $po_code){
            $SQLPO = "
            SELECT *
            FROM `purchase_order`
            WHERE purchase_order_id = '{$po_code}'
            ";
            $resultPO  = $db->sql_query($SQLPO);
            $PORec     = $db->sql_fetchrow($resultPO);
            $purchase_order_id     = $PORec['purchase_order_id'];

            if($count == 0){
                $purchase_order_id_main = $PORec['purchase_order_id'];
            }


            $sqlQty = "
            SELECT SUM(pop.qty*pop.cost_price) AS po_amount
            FROM po_product pop
            WHERE pop.purchase_order_id = {$purchase_order_id}
            ";
            $resultQty = $db->sql_query($sqlQty);
            $rowQty = $db->sql_fetchrow($resultQty);
            $po_amount = $rowQty['po_amount'];


            if ($PORec['payment_status'] == 'Paid' || $receipt_amount <= 0){
                continue;
            }


            $SQLPaid = "
            SELECT SUM(supHist.amount) AS prev_sum
            FROM supplier_receipt_history supHist
            LEFT JOIN supplier_receipt r ON (r.supplier_receipt_id = supHist.supplier_receipt_id)
            WHERE supHist.purchase_order_id =  '{$purchase_order_id}' 
            AND r.receipt_status = 'Paid'
            ";

            $resultPaid = $db->sql_query($SQLPaid);
            $rowPaid    = $db->sql_fetchrow($resultPaid);

            $po_amount = $po_amount - $rowPaid['prev_sum'];

            $faPO = array();
            $recpPOAmount = 0;
            if ($po_amount <= $receipt_amount){
                $recpPOAmount           = $po_amount;
                $faPO['payment_status'] = 'Paid';

            } else if ($po_amount > $receipt_amount){
                $recpPOAmount           = $receipt_amount;
                $faPO['payment_status'] = 'Partially Paid';
            }

            $receipt_amount = $receipt_amount - $recpPOAmount;
            $fn->saveRecord($faPO, 'purchase_order', 'purchase_order_id', $purchase_order_id);

            //Inserting receipt id in to history table 
            $fa = array();
            $fa['supplier_receipt_id']       = $supplier_receipt_id;
            $fa['purchase_order_id']         = $purchase_order_id_main;
            $fa['amount']                    = $recpPOAmount;
            $fa['related_purchase_order_id'] = $purchase_order_id;
            $fa['creation_date']             = date("Y-m-d H:i:s");
            $histId = $fn->addRecord($fa, 'supplier_receipt_history');
            $count++;
        }


        //TO CHECK IF THE SUM OF DISCOUNT TYPE(%) HAS VALUE OR NOT AND ASSIGN THE QUERY IF IT HAS VALUE ELSE ASSIGN ZERO
       /* $subSqlForPercentSum = "
        SELECT o.*
              ,(SELECT SUM(round(oi.unit_price * oi.qty,2))
                FROM order_item oi
                WHERE oi.order_id = {$order_id}
                ) as order_amount
              ,(SELECT SUM(inv.invoice_amount)
                FROM invoice inv
                WHERE inv.order_id = o.order_id AND inv.status = 'Paid'
                  ) as total_invoice_amount
        FROM `order`o
        WHERE o.order_id = {$order_id}
        ";
        $resultSubSql = $db->sql_query($subSqlForPercentSum);
        $rowSql       = $db->sql_fetchrow($resultSubSql);

        $total_invoice_amount = $rowSql['total_invoice_amount'] - $rowSql['discount'];
        $order_amount = $rowSql['order_amount'] - $rowSql['discount'];*/
        //$supplierRec = $fn->getRecordRowByID('supplier', 'supplier_id', $supplier_id);

        //FOR AUTO UPDATING OF supplier STATUS WHEN A RECEIPT IS PAID
        /*if($order_amount == $total_invoice_amount){
            $SQLUpdate = "UPDATE `supplier` SET payment_status = 'Paid' WHERE supplier_id = {$supplier_id}";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }*/

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getGeneratePurchaseOrderFormValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $po_amount = '';
        $balance_amount = '';

        $amount          = $fn->getPostParam('amount');
        $poCodesArr = $fn->getPostParam('poCode', array());

        $validate->resetErrorArray();
        if(count($poCodesArr) == 0){
            $validate->validateData('amount' , 'Please choose the po to be paid');
        }
        //==================================================================
        $poCodes = join(",", $poCodesArr);
        $sessionExplode = explode(',', $poCodes);

        $counter = 1;
        $count = count($sessionExplode);

        $po_code = '';
        foreach ($sessionExplode as $poCode) {
            if ($count == $counter) {
                $po_code .= "'" . $poCode . "'";
            } else {
                $po_code .= "'" . $poCode . "',";
            }
            $counter++;
        }

        if ($poCodes != ''){
            $SQL = "
                SELECT SUM(pop.qty*pop.cost_price) AS po_amount
                FROM purchase_order p
                LEFT JOIN po_product pop ON (pop.purchase_order_id = p.purchase_order_id)
                WHERE p.purchase_order_id IN ({$po_code})
            ";
            $resultPaid = $db->sql_query($SQL);
            $rowPaid    = $db->sql_fetchrow($resultPaid);
            $total_po_amount = $rowPaid['po_amount'];

            $SQLPaid = "
            SELECT SUM(srh.amount) as prev_sum
            FROM supplier_receipt_history srh
            LEFT JOIN supplier_receipt sr ON (sr.supplier_receipt_id = srh.supplier_receipt_id)
            WHERE purchase_order_id IN (
                SELECT purchase_order_id
                FROM purchase_order
                WHERE purchase_order_id IN ({$po_code})
                )
            AND sr.receipt_status != 'Cancelled'
            ";
            $resultPaid = $db->sql_query($SQLPaid);
            $rowPaid    = $db->sql_fetchrow($resultPaid);
            $prev_sum   = $rowPaid['prev_sum'];

            $balance_amount = $total_po_amount - $prev_sum;

            if($amount > $balance_amount){
                $validate->errorArray['amount']['name'] = "amount";
                $validate->errorArray['amount']['msg']  = 'Please enter1 amount less than the balance amount';
            }
        }

        $validate->validateData('poCode' , 'Please check po code');
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
    function getPopulatePOAmount() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $po_code = $fn->getReqParam('po_code');
        $purchase_order_id = $fn->getReqParam('purchase_order_id');
        $checkedVal = $fn->getReqParam('checkedVal');

        if($checkedVal == 1){
            $_SESSION['selectedPOIds'][] = $purchase_order_id;
        }

        else if($checkedVal == 0){
            $s = &$_SESSION['selectedPOIds'];
            if(($key = array_search($purchase_order_id, $s)) !== false){
                unset($s[$key]);
            }
        }

        if(count($_SESSION['selectedPOIds']) == 0){
            return 0;
        }

        $selectPOIds = join(',', $_SESSION['selectedPOIds']);
        $sessionExplode = explode(',', $selectPOIds);

        $counter = 1;
        $count = count($sessionExplode);

        $purchase_order_id = '';
        foreach ($sessionExplode as $purchaseOrderId) {
            if ($count == $counter) {
                $purchase_order_id .= "'" . $purchaseOrderId . "'";
            } else {
                $purchase_order_id .= "'" . $purchaseOrderId . "',";
            }
            $counter++;
        }

        $SQLPaid = "
        SELECT SUM(pop.qty*pop.cost_price) AS po_amount
        FROM purchase_order p
        LEFT JOIN po_product pop ON (pop.purchase_order_id = p.purchase_order_id)
        WHERE p.purchase_order_id IN ({$purchase_order_id})
        ";
        $resultPaid = $db->sql_query($SQLPaid);
        $rowPaid    = $db->sql_fetchrow($resultPaid);

        $SQLPartialPayment = "
        SELECT SUM(srh.amount) AS Po_partial_payment
        FROM supplier_receipt_history srh
        LEFT JOIN (purchase_order p) ON (srh.purchase_order_id = p.purchase_order_id)
        LEFT JOIN supplier_receipt sr ON (sr.supplier_receipt_id = srh.supplier_receipt_id)
        WHERE p.purchase_order_id IN ({$purchase_order_id})
          AND sr.receipt_status != 'Cancelled'
        ";
        $resultPartialPayment = $db->sql_query($SQLPartialPayment);
        $rowPartialPayment    = $db->sql_fetchrow($resultPartialPayment);

        if($rowPartialPayment['Po_partial_payment'] == ''){
            $SQLPartialPayment = "
            SELECT SUM(srh.amount) AS Po_partial_payment
            FROM supplier_receipt_history srh
            LEFT JOIN (purchase_order p) ON (srh.purchase_order_id = p.purchase_order_id)
            LEFT JOIN supplier_receipt sr ON (sr.supplier_receipt_id = srh.supplier_receipt_id)
            WHERE p.purchase_order_id IN ({$purchase_order_id})
              AND sr.receipt_status != 'Cancelled'
            ";
            $resultPartialPayment = $db->sql_query($SQLPartialPayment);
            $rowPartialPayment    = $db->sql_fetchrow($resultPartialPayment);
        }

        if ($rowPartialPayment['Po_partial_payment'] == 0){
            return number_format($rowPaid['po_amount'], 2, '.', '');
        } else {
            return number_format(($rowPaid['po_amount'] - $rowPartialPayment['Po_partial_payment']), 2, '.', '');
        }

    }

    /**
     *
     */
    function getAddSupplier() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getAddSupplierValidate()){
            return $validate->getErrorMessageXML();
        }

        $company_name  = $fn->getPostParam('company_name');
        $website    = $fn->getPostParam('website');
        $phone  = $fn->getPostParam('phone');
        $gst_no  = $fn->getPostParam('gst_no');
        $address_flat  = $fn->getPostParam('address_flat');
        $address_street  = $fn->getPostParam('address_street');
        $address_town  = $fn->getPostParam('address_town');
        $address_state  = $fn->getPostParam('address_state');
        $address_country  = $fn->getPostParam('address_country');

        $fa = array();
        $fa['company_name']   = $company_name;
        $fa['website']   = $website;
        $fa['phone'] = $phone;
        $fa['gst_no']     = $gst_no;
        $fa['address_flat']   = $address_flat;
        $fa['address_street']   = $address_street;
        $fa['address_town']   = $address_town;
        $fa['address_state']   = $address_state;
        $fa['address_country']   = $address_country;

        $supplier_id = $fn->addRecord($fa, 'supplier');

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddSupplierValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

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
    function getSupplierList(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows = "";

        $json  = array();
        
        $SQL = "
        SELECT supplier_id
              ,company_name
        FROM supplier 
        ORDER BY company_name
        ";
        $result   = $db->sql_query($SQL);  

        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['supplier_id'], "caption" => $row['company_name']);
        }
        
        return json_encode($json);
    }

    /**
     *
     */
    function getCancelSupplierReceipt() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $supplier_receipt_id = $fn->getReqParam('supplier_receipt_id');
        $purchase_order_id = $fn->getReqParam('purchase_order_id');

        $sqlInv = "
        UPDATE supplier_receipt
        SET receipt_status = 'Cancelled'
        WHERE supplier_receipt_id = '{$supplier_receipt_id}'
        ";
        $resultInv = $db->sql_query($sqlInv);

        $sqlInv = "
        UPDATE purchase_order
        SET payment_status = 'Due'
        WHERE purchase_order_id = '{$purchase_order_id}'
        ";
        $resultInv = $db->sql_query($sqlInv);
    }
}
