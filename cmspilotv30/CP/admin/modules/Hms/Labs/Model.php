<?
class CP_Admin_Modules_Hms_Labs_Model extends CP_Common_Lib_ModuleModelAbstract
{

    /**
     *
     */
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $appendSQL = '';

        $joinTable = '';
        if ($_SESSION['userGroupType'] == 'User'){
            $joinTable = "
                LEFT JOIN (product_group_staff pgs) ON (p.product_group_id = pgs.product_group_id)
            ";
        }

        /*$SQL = "
        SELECT l.*
              ,ls.title AS suppliername 
              ,ls.category 
              ,com.company_name AS supplier_name
              ,clnt.company_name
              ,CONCAT_WS(' ', c.first_name, c.last_name) AS contact_name_supplier
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name

        FROM labs l
        LEFT JOIN company com ON l.supplier_id = com.company_id
        LEFT JOIN labs_supplier ls ON l.supplier_id = ls.labs_supplier_id 
        LEFT JOIN contact c ON l.contact_id_supplier = c.contact_id
        LEFT JOIN staff s ON l.staff_id = s.staff_id
        LEFT JOIN company clnt ON clnt.company_id = c.company_id
        ";*/

        $SQL = "
        SELECT l.*
              ,ls.title AS suppliername 
              ,ls.category 
              ,CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) AS Patient_Name
              ,p.nric
              ,ls.title AS supplier_name
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
        FROM labs l
        LEFT JOIN labs_supplier ls ON l.supplier_id = ls.labs_supplier_id 
        LEFT JOIN contact c ON l.contact_id_supplier = c.contact_id
        LEFT JOIN patient_information p ON (p.patient_information_id = l.patient_information_id)
        LEFT JOIN staff s ON l.staff_id = s.staff_id
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $searchVar = Zend_Registry::get('searchVar');
        $fn = Zend_Registry::get('fn');
        $searchVar->mainTableAlias = 'l';

        $status            = $fn->getReqParam('status');
        $company_id        = $fn->getReqParam('company_id');
        $labs_id     = $fn->getReqParam('labs_id');

        if ($labs_id != "") {
            $searchVar->sqlSearchVar[] = "l.labs_id = '{$labs_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "l.labs_id = '{$tv['record_id']}'";
        } else {

            if ($status != "") {
                $searchVar->sqlSearchVar[] = "l.status = '{$status}'";
            }
            if ($company_id != "") {
                $searchVar->sqlSearchVar[] = "l.supplier_id = '{$company_id}'";
            }

            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "l.flag = 1";
            }
            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(l.flag != 1 OR l.flag IS null)";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       s.first_name LIKE '%{$tv['keyword']}%'
                    OR s.last_name LIKE '%{$tv['keyword']}%'
                    OR p.first_name LIKE '%{$tv['keyword']}%'
                    OR p.middle_name LIKE '%{$tv['keyword']}%'
                    OR p.last_name LIKE '%{$tv['keyword']}%'
                    OR p.nric LIKE '%{$tv['keyword']}%'
                    OR l.labs_code LIKE '%{$tv['keyword']}%'
                )";
            }
        }

        $searchVar->sortOrder = "l.creation_date DESC";

    }

    /**
     *
     */
    function getNewValidate() {
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('supplier_category', 'Please choose the category');
        $validate->validateData('supplier_id', 'Please choose the Supplier');

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
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $labs_code = $fn->getSettingsValueByKey("nextLabsCode");

        $fa = $this->getFields();

        $fa['labs_code'] = $labs_code;
        $fa['labs_date'] = date('Y-m-d');
        $id = $fn->addRecord($fa);

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND site_id = {$cpSiteIdSession}";
        }
        
        //To update patient code
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextLabsCode' {$appendSql}";
        $resultUpdate = $db->sql_query($SQLUpdate);

        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');
        $tv = Zend_Registry::get('tv');

        $validate->resetErrorArray();

        //$validate->validateData('buy_currency', 'Please choose buy currency');


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
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id, $cpCfg['cp.pagetoReturnAfterSave']);
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
    function getFields() {
        $fn = Zend_Registry::get('fn');
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'labs_code');
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'supplier_id');
        $fa = $fn->addToFieldsArray($fa, 'contact_id_supplier');
        $fa = $fn->addToFieldsArray($fa, 'payment_terms');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'labs_date');
        $fa = $fn->addToFieldsArray($fa, 'buy_currency');
        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'priority');
        $fa = $fn->addToFieldsArray($fa, 'delivery_terms');
        $fa = $fn->addToFieldsArray($fa, 'follow_up_date');


        return $fa;
    }

    /**
     *
     */
    function getAddMultipleLineItemSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');


        $labs_id            = $fn->getPostParam('labs_id');
        $product_arr        = $fn->getPostParam('product_id', array());
        $supplier_name_arr  = $fn->getPostParam('supplier_id', array());
        $price_arr          = $fn->getPostParam('price', array());
        $qty_arr            = $fn->getPostParam('qty', array());
        $qty_delivered_arr  = $fn->getPostParam('qty_delivered', array());
        $statusprod_arr     = $fn->getPostParam('prod_status', array());

        $count = count($price_arr);
        for ($i= 0; $i < $count; $i++) {
            $product_id         = $product_arr[$i];
            $supplier_id        = $supplier_name_arr[$i];
            $price              = $price_arr[$i];
            $qty                = $qty_arr[$i];
            $qty_delivered      = $qty_delivered_arr[$i];
            $prod_status        = $statusprod_arr[$i];

            if ($price) {
                $fa = array();

                $fa['product_id']       = $product_id;
                $fa['supplier_id']      = $supplier_id;
                $fa['price']            = $price;
                $fa['qty']              = $qty;
                $fa['qty_delivered']    = $qty_delivered;
                $fa['status']           = $prod_status;
                $fa['labs_id']          = $labs_id;
                $fa['creation_date']    = date("Y-m-d H:i:s");

                $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'labs_product');
                $result = $db->sql_query($insert);
                $labs_product_id = $db->sql_nextid();
            }
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */

    function getHMSlabsHMSProductLinkSQL($id) {

        $SQL = "
        SELECT lp.labs_product_id
              ,p.title AS product_name
              ,lp.price
              ,lp.qty
              ,lp.qty_delivered
              ,lp.status
        FROM labs_product lp
        JOIN product p ON (p.product_id = lp.product_id)
        WHERE lp.labs_id = {$id}
        ";

        return $SQL;
    }
    /**
     *
     */

    function getImportData(){
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');
        $db = Zend_Registry::get('db');

        $SQL = "
        SELECT max(batch_import) as batch_import
        FROM purchase_order
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $fa = array(
              'purchase_order_date' => $phpExcel->getImportFldObj('Purchase Date')
             ,'supplier_id' => $phpExcel->getImportFldObj('Supplier')
             ,'qty'                 => $phpExcel->getImportFldObj('Qty')
             ,'price'               => $phpExcel->getImportFldObj('Price')
             ,'category'            => $phpExcel->getImportFldObj('Main Category')
             ,'sub_category'        => $phpExcel->getImportFldObj('Sub Category')
             ,'title'               => $phpExcel->getImportFldObj('Product Title')
             ,'unit'                => $phpExcel->getImportFldObj('Unit')
             ,'item_code'           => $phpExcel->getImportFldObj('Item Code')
        );
        $fa['qty']['refOnly'] = true;
        $fa['price']['refOnly'] = true;
        $fa['category']['refOnly'] = true;
        $fa['sub_category']['refOnly'] = true;
        $fa['title']['refOnly'] = true;
        $fa['unit']['refOnly'] = true;
        $fa['item_code']['refOnly'] = true;

        $fa['batch_import']['defaultValue'] = $row['batch_import'] + 1;
        $fa['po_code']['defaultValue'] = $this->getUpdatePOCode();

        $fa['supplier_id']['specialType'] = 'fetchIdFromRefModule';
        $fa['supplier_id']['exp']['refModule'] = 'tradingsg_supplier';

        /****************************************/
        $config = array(
             'module'              => 'tradingsg_purchaseOrder'
            ,'matchFieldArr'       => array('purchase_order_date')
            ,'fldsArr'             => $fa
            ,'callbackAfterInsert' => 'importDataRowCallback'
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     */
    function importDataRowCallback($purchase_order_id, $fa) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $qty = $fa['qty'];
        $price = $fa['price'];
        $supplier_id = $fa['supplier_id'];
        $batch_import = $fa['batch_import'];
        $category = $fa['category'];
        $sub_category = $fa['sub_category'];

        $title = $fa['title'];
        $unit = $fa['unit'];
        $item_code = $fa['item_code'];

        $sqlcount1 = "
                SELECT COUNT(*)
                FROM `category`
                WHERE title = '{$category}'
                ";
            $resultcount1 = $db->sql_query($sqlcount1);
            $catRecCount    = $db->sql_fetchrow($resultcount1);

            //$catRecCount = $fn->getRecordCount('category', "title = '{$category}'");

        if ($catRecCount == 0 && $category != '') {
            $fa1 = array();
            $fa1['title'] = $category;
            $fa1['section_id'] = 13;
            $fa1['published'] = 1;
            $fa1['category_type'] = 'Content';

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa1, 'category');
            $result = $db->sql_query($SQL);
            $category_id  = $db->sql_nextid();
        } else {
            $sqlcat = "
            SELECT category_id
                   ,title FROM category
                   WHERE title = '{$category}'
            ";
            $resultcat = $db->sql_query($sqlcat);
            $catRec    = $db->sql_fetchrow($resultcat);

            //$catRec = $fn->getRecordByCondition('category', "title = '{$category}'");
            $category_id  = $catRec['category_id'];
        }

        $sqlcount2 = "
                SELECT COUNT(*)
                FROM `sub_category`
                WHERE title = '{$sub_category}'
                ";
            $resultcount2 = $db->sql_query($sqlcount2);
            $subCatRecCount    = $db->sql_fetchrow($resultcount2);

            //$subCatRecCount = $fn->getRecordCount('sub_category', "title = '{$sub_category}'");

        if ($subCatRecCount == 0 && $sub_category != '') {
            $fa1 = array();
            $fa1['title'] = $sub_category;
            $fa1['category_id'] = $category_id;
            $fa1['published'] = 1;
            $fa1['sub_category_type'] = 'Content';

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa1, 'sub_category');
            $result = $db->sql_query($SQL);
            $sub_category_id  = $db->sql_nextid();
        } else {
            $sqlcat1 = "
            SELECT sub_category_id
                   ,title FROM sub_category
                   WHERE title = '{$sub_category}'
            ";
            $resultcat1 = $db->sql_query($sqlcat1);
            $subCatRec    = $db->sql_fetchrow($resultcat1);

            //$subCatRec = $fn->getRecordByCondition('sub_category', "title = '{$sub_category}'");
            $sub_category_id  = $subCatRec['sub_category_id'];
        }

        $fa2 = array();
        $fa2['title']  = $title;
        $fa2['unit']  = $unit;
        $fa2['price']  = $price;
        $fa2['published']  = 1;
        $fa2['category_id']  = $category_id;
        $fa2['sub_category_id']  = $sub_category_id;
        $fa2['batch_import']  = $batch_import;
        $fa2 = $fn->addCreationDetailsToFieldsArray($fa2, 'product');

        if($item_code == ''){
            $fa2['item_code'] = $this->getUpdateProductCode();

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa2, 'product');
            $result = $db->sql_query($SQL);
            $product_id  = $db->sql_nextid();
        } else {
            $whereCondition = "WHERE item_code = '{$item_code}'";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa2, 'product', $whereCondition);
            $result = $db->sql_query($SQL);

            $rowProduct = $fn->getRecordRowByID('product', 'item_code', $item_code);
            $product_id  = $rowProduct['product_id'];
        }

        $fa3 = array();
        $fa3['product_id'] = $product_id;
        $fa3['purchase_order_id']  = $purchase_order_id;
        $fa3['supplier_id']  = $supplier_id;
        $fa3['qty']  = $qty;
        $fa3['price']  = $price;
        $fa3 = $fn->addCreationDetailsToFieldsArray($fa3, 'po_product');

        $SQL = $dbUtil->getInsertSQLStringFromArray($fa3, 'po_product');
        $result = $db->sql_query($SQL);
    }

    /**
     *
     */
    function getLabsSupplierJSON(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows = "";

        $supplier_category   = $fn->getReqParam('supplier_category');

        $json  = array();
        
        if ($supplier_category == ""){
            $json[] = array("value" => "", "caption" => "Please Select");
            return json_encode($json);
        }

        $SQL = "
        SELECT labs_supplier_id
              ,title
        FROM labs_supplier 
        WHERE category = '{$supplier_category}'
        ORDER BY title
        ";
        $result   = $db->sql_query($SQL);  

        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
                $json[] = array("value" => $row['labs_supplier_id'], "caption" => $row['title']);
        }
        
        return json_encode($json);
    }

    /**
     *
     */
    function getPopulateReceiptAmount() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $invoice_code = $fn->getReqParam('invoice_code');
        $checkedVal = $fn->getReqParam('checkedVal');

        if($checkedVal == 1){
            $_SESSION['selectedInvoiceIds'][] = $invoice_code;
        }
        else if($checkedVal == 0){
            $s = &$_SESSION['selectedInvoiceIds'];
            if(($key = array_search($invoice_code, $s)) !== false){
                unset($s[$key]);
            }
        }

        if(count($_SESSION['selectedInvoiceIds']) == 0){
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
        SELECT SUM(payments_amount - discount) AS invoice_selected_sum
        FROM payments
        WHERE payments_code IN ({$invoice_code})
        ";
        $resultPaid = $db->sql_query($SQLPaid);
        $rowPaid    = $db->sql_fetchrow($resultPaid);

        $SQLPartialPayment = "
        SELECT SUM(irh.amount) AS invoice_partial_payment
        FROM payments_receipt_history irh
        LEFT JOIN (payments i) ON (irh.payments_id = i.payments_id)
        LEFT JOIN payments_receipt r ON (r.payments_receipt_id = irh.payments_receipt_id)
        WHERE i.payments_code IN ({$invoice_code})
          AND r.receipt_status != 'Cancelled'
        ";
        $resultPartialPayment = $db->sql_query($SQLPartialPayment);
        $rowPartialPayment    = $db->sql_fetchrow($resultPartialPayment);

        if($rowPartialPayment['invoice_partial_payment'] == ''){
            $SQLPartialPayment = "
            SELECT SUM(invHist.amount) AS invoice_partial_payment
            FROM payments_receipt_history invHist
            LEFT JOIN (payments i) ON (invHist.payments_id = i.payments_id)
            LEFT JOIN payments_receipt r ON (r.payments_receipt_id = invHist.payments_receipt_id)
            WHERE i.payments_code IN ({$invoice_code})
            AND r.receipt_status != 'Cancelled'
            ";
            $resultPartialPayment = $db->sql_query($SQLPartialPayment);
            $rowPartialPayment    = $db->sql_fetchrow($resultPartialPayment);
        }

        if ($rowPartialPayment['invoice_partial_payment'] == 0){
            return $rowPaid['invoice_selected_sum'];
        } else {
            return $rowPaid['invoice_selected_sum'] - $rowPartialPayment['invoice_partial_payment'];
        }

    }

    /**
     *
     */
    function getCreateInvoiceLabs() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $patient_visit_id  = $fn->getReqParam('patient_visit_id');
        $order_id          = $fn->getReqParam('order_id');
        $labs_id           = $fn->getReqParam('labs_id');
        $date              = $fn->getCurrentDate();
        $due_date          = date('Y-m-d', strtotime("+14 days"));

        $labsRec = $fn->getRecordRowByID('labs', 'labs_id', $labs_id);
        
        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND site_id = {$cpSiteIdSession}";
        }

        //To update invoice code
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextPatmentsInvoiceCode' {$appendSql}";
        $resultUpdate = $db->sql_query($SQLUpdate);
        $invoice_code = $fn->getSettingsValueByKey("nextPatmentsInvoiceCode");

        $invoiceRec = $fn->getRecordByCondition('payments', "order_id = '{$order_id}' AND labs_id = '{$labs_id}' AND status != 'Cancelled'");

        $fa = array();
        if(is_array($invoiceRec)){
            $fa['payments_amount']   = $labsRec['amount'];
            $fa['modification_date'] = date("Y-m-d H:i:s");
            $fa['modified_by']       = $fn->getSessionParam('userName');

            $whereCondition = "WHERE payments_id = {$invoiceRec['payments_id']}";
            $sqlUpdate = $dbUtil->getUpdateSQLStringFromArray($fa, "payments", $whereCondition);
            $resultUpdate = $db->sql_query($sqlUpdate);
            $invoice_id      = $invoiceRec['payments_id'];
        }else{
            $fa['payments_code']     = 'INV - ' . $invoice_code;
            $fa['payments_amount']   = $labsRec['amount'];
            $fa['payments_date']     = $date;
            $fa['payments_due_date'] = $due_date;

            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $fa['site_id'] = $cpSiteIdSession;
            }

            $fa['labs_id']          = $labs_id;
            $fa['order_id']         = $order_id;
            $fa['status']           = 'Due';
            $fa['creation_date']    = date("Y-m-d H:i:s");
            $fa['created_by']       = $fn->getSessionParam('userName');

            $insertInvoiceSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'payments');
            $resultSQL          = $db->sql_query($insertInvoiceSQL);
            $invoice_id         = $db->sql_nextid();
        }

        return $invoice_id;
    }

    /**
     *
     */
    function getGenerateReceiptFormSubmit() {
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
        $labs_id         = $fn->getReqParam('labs_id');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        if (!$this->getGenerateReceiptFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $count = count($invoiceCodes);

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND site_id = {$cpSiteIdSession}";
        }

        //To update receipt codes
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextPaymentsReceiptCode' {$appendSql}";
        $resultUpdate = $db->sql_query($SQLUpdate);
        $receipt_code = $fn->getSettingsValueByKey("nextPaymentsReceiptCode");

        $fa = array();
        $fa['amount']                = $amount;
        $fa['order_id']              = $order_id;
        $fa['payments_receipt_code'] = 'RCPT - ' . $receipt_code;
        $fa['mode_of_payment']       = $mode_of_payment;
        $fa['remarks']               = $remarks;
        $fa['labs_id']               = $labs_id;
        $fa['date']                  = date("Y-m-d H:i:s");
        $fa['receipt_status']        = 'Paid';
        $fa['creation_date']         = date("Y-m-d H:i:s");
        $fa['created_by']            = $fn->getSessionParam('userName');

        $insertReceiptSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'payments_receipt');
        $resultSQL          = $db->sql_query($insertReceiptSQL);
        $receipt_id         = $db->sql_nextid();
        $receipt_amount     = $amount;
        $invoice_status_due = '';
        $count = 0;

        foreach($invoiceCodes AS $invoice_code){
            $SQLInvoice = "
            SELECT *
            FROM `payments`
            WHERE payments_code = '{$invoice_code}'
            AND status != 'Cancelled'
            ";
            $resultInvoice  = $db->sql_query($SQLInvoice);
            $invoiceRec     = $db->sql_fetchrow($resultInvoice);
            $invoice_amount = $invoiceRec['payments_amount'];
            $invoice_id     = $invoiceRec['payments_id'];

            if ($invoiceRec['status'] == 'Paid' || $receipt_amount <= 0){
                continue;
            }

            $SQLPaid = "
            SELECT SUM(invHist.amount) AS prev_sum
            FROM payments_receipt_history invHist
            LEFT JOIN (payments_receipt rec) ON (invHist.payments_receipt_id = rec.payments_receipt_id)
            WHERE invHist.payments_id =  '{$invoice_id}' and rec.receipt_status = 'Paid'
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
            $fn->saveRecord($faInv, 'payments', 'payments_id', $invoice_id);

            //Inserting receipt id in to history table ( one invoice can have multiple receipts)
            $fa = array();
            $fa['payments_receipt_id'] = $receipt_id;
            $fa['payments_id']         = $invoice_id;
            $fa['amount']              = $recpInvAmount;
            $fa['creation_date']       = date("Y-m-d H:i:s");
            $histId = $fn->addRecord($fa, 'payments_receipt_history');
        }

        $subSqlForPercentSum = "
        SELECT l.labs_id
               ,(SELECT SUM(inv.payments_amount)
                FROM payments inv
                WHERE inv.labs_id = l.labs_id AND inv.status = 'Paid'
                  ) as total_invoice_amount
                ,(SELECT SUM(invHist.amount) AS prev_sum
                    FROM payments_receipt_history invHist
                    LEFT JOIN payments_receipt r ON (r.payments_receipt_id = invHist.payments_receipt_id)
                    LEFT JOIN `payments` i ON (i.labs_id = {$labs_id})
                    WHERE invHist.payments_id =  i.payments_id
                    AND r.receipt_status != 'Cancelled'
                    AND i.status != 'Cancelled'
                ) as Amount_Paid
        FROM `labs` l
        WHERE l.labs_id = {$labs_id}
        ";
        $resultSubSql = $db->sql_query($subSqlForPercentSum);
        $rowSql       = $db->sql_fetchrow($resultSubSql);

        $total_invoice_amount = $rowSql['total_invoice_amount'];
        $paid_invoice_amount  = $rowSql['Amount_Paid'];

        //FOR AUTO UPDATING OF ORDER STATUS WHEN A RECEIPT IS PAID
        if($total_invoice_amount == $paid_invoice_amount){
            $SQLUpdate = "UPDATE `labs` SET status = 'Paid' WHERE labs_id = {$labs_id}";
            $resultUpdate = $db->sql_query($SQLUpdate);
        } else {
            $SQLPVUpdate = "UPDATE labs SET status = 'Partial Receipt' WHERE labs_id = {$labs_id}";
            $resultPVUpdate = $db->sql_query($SQLPVUpdate);
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
                SELECT SUM(payments_amount) as invoice_sum
                FROM payments
                WHERE payments_code IN ({$invoice_code})
            ";
            $resultPaid = $db->sql_query($SQL);
            $rowPaid    = $db->sql_fetchrow($resultPaid);
            $total_invoice_amount = $rowPaid['invoice_sum'];

            $SQLPaid = "
            SELECT SUM(irh.amount) as prev_sum
            FROM payments_receipt_history irh
            LEFT JOIN payments_receipt r ON (r.payments_receipt_id = irh.payments_receipt_id)
            WHERE payments_id IN (
                SELECT payments_id
                FROM payments
                WHERE payments_code IN ({$invoice_code})
                )
            AND r.receipt_status != 'Cancelled'
            ";
            $resultPaid = $db->sql_query($SQLPaid);
            $rowPaid    = $db->sql_fetchrow($resultPaid);
            $prev_sum   = $rowPaid['prev_sum'];

            $balance_amount = $total_invoice_amount - $prev_sum;

            if($amount > $balance_amount){
                $validate->errorArray['amount']['name'] = "amount";
                $validate->errorArray['amount']['msg']  = 'Please enter amount less than the balance amount';
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
    function getCancelReceipt() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $receipt_code = $fn->getReqParam('receipt_code');
        $order_id     = $fn->getReqParam('order_id');

        //to update the status of invoice to Due for related receipts.
        $sqlRecPayments = "
        UPDATE payments
        SET status = 'Due'
        WHERE payments_id IN
        (SELECT payments_id
         FROM payments_receipt_history
         WHERE payments_receipt_id = (SELECT payments_receipt_id FROM payments_receipt
            WHERE payments_receipt_code = '{$receipt_code}')
         )
        ";
        $resultRecPayments = $db->sql_query($sqlRecPayments);

        $sqlRec = "
        UPDATE payments_receipt
        SET receipt_status = 'Cancelled'
        WHERE payments_receipt_code = '{$receipt_code}'
        ";
        $resultRec = $db->sql_query($sqlRec);
    }

}
