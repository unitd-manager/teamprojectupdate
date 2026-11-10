<?
class CPL_Admin_Modules_Tradingsg_SubCon_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT s.*
              ,gc.name AS country_name
        FROM sub_con s
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
        $sub_con_id   = $fn->getReqParam('sub_con_id');
        $company_name = $fn->getReqParam('company_name');

        if ($sub_con_id != "") {
            $searchVar->sqlSearchVar[] = "s.sub_con_id = '{$sub_con_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "s.sub_con_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 's.sub_con_id');


            if ($status != "") {
                $searchVar->sqlSearchVar[] = "s.status = '{$status}'";
            }

            if ($company_name != "") {
                $searchVar->sqlSearchVar[] = "s.company_name LIKE '%{$company_name}%'";
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
        $sub_con_id  = $fn->getPostParam('sub_con_id');
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
        $fa['sub_con_id']   = $sub_con_id;
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
    function getGenerateWorkOrderFormSubmit() {
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
        $sub_con_id        = $fn->getReqParam('sub_con_id');
        $project_id        = $fn->getReqParam('project_id');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        if (!$this->getGenerateWorkOrderFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $count = count($poCodes);

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND site_id = {$cpSiteIdSession}";
        }

        $fa = array();
        $fa['amount']          = $amount;
        $fa['sub_con_id']     = $sub_con_id;
        $fa['project_id']     = $project_id;
        $fa['mode_of_payment'] = $mode_of_payment;

        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $fa['site_id'] = $cpSiteIdSession;
        }

        $fa['remarks']        = $remarks;
        $fa['status'] = 'Paid';
        $fa['creation_date']  = date("Y-m-d H:i:s");
        $fa['created_by']     = $fn->getSessionParam('userName');

        $insertReceiptSQL     = $dbUtil->getInsertSQLStringFromArray($fa, 'sub_con_payments');
        $resultSQL            = $db->sql_query($insertReceiptSQL);
        $sub_con_payments_id  = $db->sql_nextid();
        $receipt_amount       = $amount;
        $invoice_status_due   = '';
        $count = 0;

        foreach($poCodes AS $po_code){
            $SQLPO = "
            SELECT *
            FROM `sub_con_work_order`
            WHERE sub_con_worker_code = '{$po_code}'
            ";
            $resultPO  = $db->sql_query($SQLPO);
            $PORec     = $db->sql_fetchrow($resultPO);
            $sub_con_work_order_id     = $PORec['sub_con_work_order_id'];

            if($count == 0){
                $sub_con_work_order_id_main = $PORec['sub_con_work_order_id'];
            }

            $sqlQty = "
            SELECT SUM(pop.amount) AS po_amount
            FROM work_order_line_items pop
            WHERE pop.sub_con_work_order_id = {$sub_con_work_order_id}
            ";
            $resultQty = $db->sql_query($sqlQty);
            $rowQty = $db->sql_fetchrow($resultQty);
            $po_amount = $rowQty['po_amount'];

            if ($PORec['status'] == 'Paid' || $receipt_amount <= 0){
                continue;
            }

            $SQLPaid = "
            SELECT SUM(supHist.amount) AS prev_sum
            FROM sub_con_payments_history supHist
            LEFT JOIN sub_con_payments r ON (r.sub_con_payments_id = supHist.sub_con_payments_id)
            WHERE supHist.sub_con_work_order_id =  '{$sub_con_work_order_id}' 
            AND r.status = 'Paid'
            ";

            $resultPaid = $db->sql_query($SQLPaid);
            $rowPaid    = $db->sql_fetchrow($resultPaid);

            $po_amount = $po_amount - $rowPaid['prev_sum'];

            $faPO = array();
            $recpPOAmount = 0;
            if ($po_amount <= $receipt_amount){
                $recpPOAmount           = $po_amount;
                $faPO['status'] = 'Paid';

            } else if ($po_amount > $receipt_amount){
                $recpPOAmount           = $receipt_amount;
                $faPO['status'] = 'Partially Paid';
            }

            $receipt_amount = $receipt_amount - $recpPOAmount;
            $fn->saveRecord($faPO, 'sub_con_work_order', 'sub_con_work_order_id', $sub_con_work_order_id);

            //Inserting receipt id in to history table 
            $fa = array();
            $fa['sub_con_payments_id']       = $sub_con_payments_id;
            $fa['sub_con_work_order_id']     = $sub_con_work_order_id_main;
            $fa['amount']                    = $recpPOAmount;
            $fa['related_sub_con_work_order_id'] = $sub_con_work_order_id;
            $fa['creation_date']             = date("Y-m-d H:i:s");
            $histId = $fn->addRecord($fa, 'sub_con_payments_history');
            $count++;
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getGenerateWorkOrderFormValidate() {
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
            $validate->validateData('amount' , 'Please choose the work order to be paid');
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
                SELECT SUM(pop.amount) AS po_amount
                FROM sub_con_work_order p
                LEFT JOIN work_order_line_items pop ON (pop.sub_con_work_order_id = p.sub_con_work_order_id)
                WHERE p.sub_con_worker_code IN ({$po_code})
            ";
            $resultPaid = $db->sql_query($SQL);
            $rowPaid    = $db->sql_fetchrow($resultPaid);
            $total_po_amount = $rowPaid['po_amount'];

            $SQLPaid = "
            SELECT SUM(srh.amount) as prev_sum
            FROM sub_con_payments_history srh
            LEFT JOIN sub_con_payments sr ON (sr.sub_con_payments_id = srh.sub_con_payments_id)
            WHERE sub_con_work_order_id IN (
                SELECT sub_con_work_order_id
                FROM sub_con_work_order
                WHERE sub_con_worker_code IN ({$po_code})
                )
            AND sr.status != 'Cancelled'
            ";
            $resultPaid = $db->sql_query($SQLPaid);
            $rowPaid    = $db->sql_fetchrow($resultPaid);
            $prev_sum   = $rowPaid['prev_sum'];

            $balance_amount = $total_po_amount - $prev_sum;

            if($amount > $balance_amount){
                $validate->errorArray['amount']['name'] = "amount";
                $validate->errorArray['amount']['msg']  = 'Please enter amount less than the balance amount';
            }
        }

        $validate->validateData('poCode' , 'Please check po code');
        $validate->validateData('amount' , 'Please enter the amount');
        //$validate->validateData('mode_of_payment' , 'Please select mode of payment');

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
        $sub_con_work_order_id = $fn->getReqParam('sub_con_work_order_id');
        $checkedVal = $fn->getReqParam('checkedVal');

        if($checkedVal == 1){
            $_SESSION['selectedWOIds'][] = $sub_con_work_order_id;
        }

        else if($checkedVal == 0){
            $s = &$_SESSION['selectedWOIds'];
            if(($key = array_search($sub_con_work_order_id, $s)) !== false){
                unset($s[$key]);
            }
        }

        if(count($_SESSION['selectedWOIds']) == 0){
            return 0;
        }

        $selectWOIds = join(',', $_SESSION['selectedWOIds']);
        $sessionExplode = explode(',', $selectWOIds);

        $counter = 1;
        $count = count($sessionExplode);

        $sub_con_work_order_id = '';
        foreach ($sessionExplode as $workOrderId) {
            if ($count == $counter) {
                $sub_con_work_order_id .= "'" . $workOrderId . "'";
            } else {
                $sub_con_work_order_id .= "'" . $workOrderId . "',";
            }
            $counter++;
        }

        $SQLPaid = "
        SELECT SUM(pop.amount) AS po_amount
        FROM sub_con_work_order p
        LEFT JOIN work_order_line_items pop ON (pop.sub_con_work_order_id = p.sub_con_work_order_id)
        WHERE p.sub_con_work_order_id IN ({$sub_con_work_order_id})
        ";
        $resultPaid = $db->sql_query($SQLPaid);
        $rowPaid    = $db->sql_fetchrow($resultPaid);

        $SQLPartialPayment = "
        SELECT SUM(srh.amount) AS Po_partial_payment
        FROM sub_con_payments_history srh
        LEFT JOIN (sub_con_work_order p) ON (srh.sub_con_work_order_id = p.sub_con_work_order_id)
        LEFT JOIN sub_con_payments sr ON (sr.sub_con_payments_id = srh.sub_con_payments_id)
        WHERE p.sub_con_work_order_id IN ({$sub_con_work_order_id})
          AND sr.status != 'Cancelled'
        ";
        $resultPartialPayment = $db->sql_query($SQLPartialPayment);
        $rowPartialPayment    = $db->sql_fetchrow($resultPartialPayment);

        if($rowPartialPayment['Po_partial_payment'] == ''){
            $SQLPartialPayment = "
            SELECT SUM(srh.amount) AS Po_partial_payment
            FROM sub_con_payments_history srh
            LEFT JOIN (sub_con_work_order p) ON (srh.sub_con_work_order_id = p.sub_con_work_order_id)
            LEFT JOIN sub_con_payments sr ON (sr.sub_con_payments_id = srh.sub_con_payments_id)
            WHERE p.sub_con_work_order_id IN ({$sub_con_work_order_id})
              AND sr.status != 'Cancelled'
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

        $sub_con_id = $fn->addRecord($fa, 'supplier');

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
        SELECT sub_con_id
              ,company_name
        FROM sub_con 
        ORDER BY company_name
        ";
        $result   = $db->sql_query($SQL);  

        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['sub_con_id'], "caption" => $row['company_name']);
        }
        
        return json_encode($json);
    }

    /**
     *
     */
    function getCancelSupplierReceipt() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $sub_con_payments_id = $fn->getReqParam('sub_con_payments_id');
        $sub_con_work_order_id = $fn->getReqParam('sub_con_work_order_id');

        $sqlInv = "
        UPDATE sub_con_payments
        SET status = 'Cancelled'
        WHERE sub_con_payments_id = '{$sub_con_payments_id}'
        ";
        $resultInv = $db->sql_query($sqlInv);

        $sqlInv = "
        UPDATE sub_con_work_order
        SET status = 'Due'
        WHERE sub_con_work_order_id = '{$sub_con_work_order_id}'
        ";
        $resultInv = $db->sql_query($sqlInv);
    }
}
