<?
class CP_Admin_Modules_EnggCrm_PurchaseOrder_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        /*
        $SQL = "
        SELECT po.*
              ,com.company_name AS supplier_name
              ,clnt.company_name
              ,CONCAT_WS(' ', c.first_name, c.last_name) AS contact_name_supplier
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
              ,q.title AS quote_title
              ,q.quote_code
        FROM purchase_order po
        LEFT JOIN company com ON po.company_id_supplier = com.company_id
        LEFT JOIN contact c ON po.contact_id_supplier = c.contact_id
        LEFT JOIN staff s ON po.staff_id = s.staff_id
        LEFT JOIN quote q ON po.quote_id = q.quote_id
        LEFT JOIN company clnt ON clnt.company_id = q.company_id
        ";
        */

        $SQL = "
        SELECT po.*
              ,com.company_name AS supplier_name
              ,CONCAT_WS(' ', c.first_name, c.last_name) AS contact_name_supplier
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
        FROM purchase_order po
        LEFT JOIN company com ON po.supplier_id = com.company_id
        LEFT JOIN contact c ON po.contact_id_supplier = c.contact_id
        LEFT JOIN staff s ON po.staff_id = s.staff_id
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
        $searchVar->mainTableAlias = 'po';

        $status 	   	   = $fn->getReqParam('status');
        $company_id 	   = $fn->getReqParam('company_id');
        $purchase_order_id 	   = $fn->getReqParam('purchase_order_id');

        if ($purchase_order_id != "") {
            $searchVar->sqlSearchVar[] = "po.purchase_order_id = '{$purchase_order_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "po.purchase_order_id = '{$tv['record_id']}'";
        } else {

            if ($status != "") {
                $searchVar->sqlSearchVar[] = "po.status = '{$status}'";
            }
            if ($company_id != "") {
                $searchVar->sqlSearchVar[] = "po.company_id_supplier = '{$company_id}'";
            }

            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "po.flag = 1";
            }
            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(po.flag != 1 OR po.flag IS null)";
            }
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       po.po_code  LIKE '%{$tv['keyword']}%'
                    OR so.so_code  LIKE '%{$tv['keyword']}%'
                    OR com.company_name LIKE '%{$tv['keyword']}%'
                    OR s.first_name LIKE '%{$tv['keyword']}%'
                    OR s.last_name LIKE '%{$tv['keyword']}%'
                    OR po.status LIKE '%{$tv['keyword']}%'
                )";
            }
        }

        $searchVar->sortOrder = "po.creation_date DESC";

    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('company_id_supplier', 'Please choose the Supplier');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    function getDefaultValuesForAdd(){
        $fn = Zend_Registry::get('fn');
        $po_code = 'PO-' . $fn->getSequenceFromSettings('purchaseOrderNextCode');

        $fa['po_code'] = $po_code;
        $fa['purchase_order_date'] = date('Y-m-d');

        return $fa;
    }

    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $fa = array_merge($this->getFields(), $this->getDefaultValuesForAdd());

        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id, 'detail');
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('buy_currency', 'Please choose buy currency');

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

        $row = $fn->getRecordRowByID('purchase_order', 'purchase_order_id', $id);
        $prevStatus = $row['status'];
        $newStatus = $fa['status'];
        $createInventoryRecords = false;
        if ($prevStatus != $newStatus && $newStatus == 'confirmed') {
            $createInventoryRecords = true;
        }

        if ($createInventoryRecords) {
            $this->createInventoryRecords($id);
        }

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
        $fa = $fn->addToFieldsArray($fa, 'po_code');
        $fa = $fn->addToFieldsArray($fa, 'company_id_supplier');
        $fa = $fn->addToFieldsArray($fa, 'contact_id_supplier');
        $fa = $fn->addToFieldsArray($fa, 'payment_terms');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'purchase_order_date');
        $fa = $fn->addToFieldsArray($fa, 'buy_currency');
        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'delivery_address');
        $fa = $fn->addToFieldsArray($fa, 'consignee_name');
        $fa = $fn->addToFieldsArray($fa, 'consignee_address');
        $fa = $fn->addToFieldsArray($fa, 'consignee_phone');
        $fa = $fn->addToFieldsArray($fa, 'consignee_contact_person');
        $fa = $fn->addToFieldsArray($fa, 'deposit_paid');
        $fa = $fn->addToFieldsArray($fa, 'port_of_origin');
        $fa = $fn->addToFieldsArray($fa, 'deposit_note');
        $fa = $fn->addToFieldsArray($fa, 'shipment_no');
        $fa = $fn->addToFieldsArray($fa, 'required_delivery_date');
        $fa = $fn->addToFieldsArray($fa, 'priority');
        $fa = $fn->addToFieldsArray($fa, 'delivery_terms');
        $fa = $fn->addToFieldsArray($fa, 'follow_up_date');
        $fa = $fn->addToFieldsArray($fa, 'freight_cost');

        return $fa;
    }

    /**
     *
     */
    function getEnggCrmPurchaseOrderTradingsgProductLinkSQL($id) {

        $SQL = "
        SELECT p.product_id
              ,p.part_number
              ,p.title AS product_name
              ,po.price
              ,po.qty
              ,po.base_price
              ,po.xrate
        FROM po_product po
        JOIN product p ON (p.product_id = po.product_id)
        WHERE po.purchase_order_id = {$id}
        ";

        return $SQL;
    }


    /**
     *
     */
    function createInventoryRecords($purchase_order_id) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //create inventory records
        $SQL = "
        SELECT poi.sales_order_items_id
              ,so.sales_order_id
              ,poi.purchase_order_id
              ,poi.purchase_order_items_id
              ,poi.quote_items_id
              ,poi.product_id
              ,poi.quantity
              ,so.company_id_customer
              ,po.company_id_supplier
              ,soi.sell_unit_price
        FROM purchase_order_items poi
        JOIN purchase_order po ON po.purchase_order_id = poi.purchase_order_id
        JOIN sales_order so ON so.sales_order_id = po.sales_order_id
        JOIN sales_order_items soi ON soi.sales_order_items_id = poi.sales_order_items_id
        JOIN company c ON c.company_id = so.company_id_customer
        WHERE poi.purchase_order_id = {$purchase_order_id}
        ORDER BY poi.line_no
        ";
        $result = $db->sql_query($SQL);

        while ($rowPOI = $db->sql_fetchrow($result)) {
            $quantity = $rowPOI['quantity'];
            $count = 1;

            $SQL = "
            SELECT MAX(serial_no) AS max_serial_no
            FROM inventory
            WHERE product_id = {$rowPOI['product_id']}
            ";
            $row = $fn->getRecordBySQL($SQL);

            $serial = $row['max_serial_no'] + 1;
            while ($count <= $quantity) {
                $status   = 'available';
                $location = 'in production';

                $fa = array();
                $fa['purchase_order_id']       = $rowPOI['purchase_order_id'];
                $fa['product_id']              = $rowPOI['product_id'];
                $fa['sales_order_id']          = $rowPOI['sales_order_id'];
                $fa['sales_order_items_id']    = $rowPOI['sales_order_items_id'];
                $fa['purchase_order_id']       = $rowPOI['purchase_order_id'];
                $fa['purchase_order_items_id'] = $rowPOI['purchase_order_items_id'];
                $fa['company_id_customer']     = $rowPOI['company_id_customer'];
                $fa['company_id_supplier']     = $rowPOI['company_id_supplier'];
                $fa['serial_no']               = str_pad($serial, 5, '0', STR_PAD_LEFT);
                $fa['status']                  = $status;
                $fa['location']                = $location;
                $fa['retail_unit_price']       = $rowPOI['sell_unit_price'];
                $fa['creation_date']           = date('Y-m-d H:i:s');
                $SQL= $dbUtil->getInsertSQLStringFromArray($fa, 'inventory');
                $db->sql_query($SQL);
                $count++;
                $serial++;
            } //while 2
        } //while 1

    }

    /**
     *
     */
    function getSaveInventory() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $listObj = Zend_Registry::get('listObj');
        $formObj = Zend_Registry::get('formObj');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $purchase_order_items_id = $fn->getReqParam('purchase_order_items_id');
        $statusArr = $fn->getReqParam('inv_status', array());
        $locationArr = $fn->getReqParam('inv_location', array());

        foreach ($locationArr as $inventory_id => $location) {
            $status = $statusArr[$inventory_id];
            $SQL = "
            UPDATE inventory
            SET status = '{$status}'
               ,location = '{$location}'
            WHERE inventory_id = {$inventory_id}
            ";
            $db->sql_query($SQL);
        }

        return $cpUtil->getJsonText('success', 'Saved');
    }

    /**
     *
     */
    function getValidateEditProductItemLink() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');

        $purchase_order_items_id = $fn->getReqParam('purchase_order_items_id');

        $SQL = "
        SELECT 1
        FROM inventory i
        WHERE i.purchase_order_items_id = {$purchase_order_items_id}
        ";
        $row = $fn->getRecordBySQL($SQL);
        $status = 'success';
        $errorMsg = '';
        if ($row) {
            $status = 'error';
            $errorMsg = 'Inventory records already created you cannot edit this any more';
        }

        return $cpUtil->getJsonText($status, '', $errorMsg);
    }

    /**
     *
     */
    function getRaiseInvoiceFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        if (!$this->getRaiseInvoiceFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $poProductIds       = $fn->getReqParam('poProductId', array());
        $invoice_amount     = $fn->getPostParam('invoice_amount');
        $invoice_date       = $fn->getPostParam('invoice_date');
        $purchase_order_id  = $fn->getReqParam('purchase_order_id');
        $qty_arr            = $fn->getReqParam('qty', array());
        $qty_balance        = $fn->getReqParam('qty_balance');


        //To update invoice code
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode'";
        $resultUpdate = $db->sql_query($SQLUpdate);
        $invoice_code = $fn->getSettingsValueByKey("nextInvoiceCode");

        $fa = array();
        $fa['invoice_code']     = $invoice_code;
        $fa['invoice_amount']   = $invoice_amount;
        $fa['invoice_date']     = $invoice_date;
        $fa['purchase_order_id'] = $purchase_order_id;
        $fa['status']           = 'Due';
        $fa['staff_id']         = $_SESSION['staff_id'];
        $fa['creation_date']    = date("Y-m-d H:i:s");
        $fa['created_by']       = $fn->getSessionParam('userName');
        $fa['invoice_type']     = 'Supplier';

        $insertInvoiceSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'invoice');
        $resultSQL          = $db->sql_query($insertInvoiceSQL);
        $invoice_id         = $db->sql_nextid();

        $count = count($poProductIds);
        $recCount = 0;
        for ($i= 0; $i< $count; $i++){
            $po_product_id = $poProductIds[$i];
            $qty = $qty_arr[$i];

            $poProductRec = $fn->getRecordRowByID('po_product', 'po_product_id', $po_product_id);
            $productRec = $fn->getRecordRowByID('product', 'product_id', $poProductRec['product_id']);

            //to update the qty delivered in po_product
            $fa1 = array();
            $fa1['qty_delivered']  =  $poProductRec['qty_delivered'] + $qty;

            $whereCondition = "
            WHERE po_product_id = {$po_product_id}
            ";
            $SQLPoProduct = $dbUtil->getUpdateSQLStringFromArray($fa1, 'po_product', $whereCondition);
            $db->sql_query($SQLPoProduct);

            if ($invoice_id > 0){
                $fa = array();
                $fa['invoice_id']   = $invoice_id;
                $fa['record_id']    = $poProductRec['product_id'];
                $fa['qty']          = $qty;
                $fa['unit_price']   = $poProductRec['price'];
                //$fa['cost_price']   = $orderItemRec['cost_price'];
                $fa['item_title']   = $productRec['title'];
                $fa['supplier_id']  = $poProductRec['supplier_id'];
                $fa['po_product_id']  = intval($po_product_id);

                $invoice_item_id = $fn->addRecord($fa, 'invoice_item');

                $recCount++;
            }
        }

        $sql ="
        SELECT SUM(it.qty * it.unit_price) As amount
        FROM invoice_item it
        LEFT JOIN (invoice i) ON (i.invoice_id = it.invoice_id)
        WHERE it.invoice_id = {$invoice_id}
          AND i.invoice_type = 'Supplier'
        ";
        $result = $db->sql_query($sql);
        $row = $db->sql_fetchrow($result);

        $fa2 = array();
        $fa2['invoice_amount']  = $row['amount'];

        $whereCondition = "
        WHERE invoice_id = {$invoice_id}
        ";
        $SQLInvoice = $dbUtil->getUpdateSQLStringFromArray($fa2, 'invoice', $whereCondition);
        $db->sql_query($SQLInvoice);

        //$this->getGenerateInvoiceForMedia($invoice_id);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getRaiseInvoiceFormValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $qty = $fn->getReqParam('qty');
        $qty_balance = $fn->getReqParam('qty_balance');

        $validate->resetErrorArray();
        $validate->validateData('qty', 'Please enter the qty');

        /*if($qty_balance < $qty){
            $validate->errorArray['qty']['name'] = "qty";
            $validate->errorArray['qty']['msg']  = 'Please enter less qty';
        }*/

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getEditInvoiceFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        if (!$this->getRaiseInvoiceFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $invoiceItemIds     = $fn->getPostParam('invoiceItemId', array());
        $invoice_amount     = $fn->getPostParam('invoice_amount');
        $invoice_date       = $fn->getPostParam('invoice_date');
        $purchase_order_id  = $fn->getReqParam('purchase_order_id');
        $qty_arr            = $fn->getReqParam('qty', array());
        $qty_balance        = $fn->getReqParam('qty_balance');
        $status             = $fn->getReqParam('status');
        $invoice_id         = $fn->getReqParam('invoice_id');

        $fa = array();
        $fa['invoice_amount']   = $invoice_amount;
        $fa['invoice_date']     = $invoice_date;
        $fa['status']           = $status;
        $fa['staff_id']         = $_SESSION['staff_id'];
        $fa['created_by']       = $fn->getSessionParam('userName');
        $fa['modification_date']= date("Y-m-d H:i:s");

        $whereCondition = "WHERE invoice_id = {$invoice_id}";
        $sqlUpdate = $dbUtil->getUpdateSQLStringFromArray($fa, "invoice", $whereCondition);
        $resultUpdate      = $db->sql_query($sqlUpdate);

        $count = count($invoiceItemIds);
        $recCount = 0;
        for ($i= 0; $i< $count; $i++){
            $invoice_item_id = $invoiceItemIds[$i];
            $qty = $qty_arr[$i];

            $fa = array();
            $fa['qty']          = $qty;

            $whereCondition = "WHERE invoice_item_id = {$invoice_item_id}";
            $sqlUpdate = $dbUtil->getUpdateSQLStringFromArray($fa, "invoice_item", $whereCondition);
            $resultUpdate      = $db->sql_query($sqlUpdate);

            $invoiceItemRec = $fn->getRecordRowByID('invoice_item', 'invoice_item_id', $invoice_item_id);
            //$poProductRec = $fn->getRecordRowByID('po_product', 'po_product_id', $invoiceItemRec['po_product_id']);

            $sql ="
            SELECT SUM(qty) As qty
            FROM invoice_item
            WHERE po_product_id = {$invoiceItemRec['po_product_id']}
            ";
            $result = $db->sql_query($sql);
            $row = $db->sql_fetchrow($result);

            //to update the qty delivered in po_product
            $fa1 = array();
            $fa1['qty_delivered']  =  $row['qty'];

            $whereCondition = "
            WHERE po_product_id = {$invoiceItemRec['po_product_id']}
            ";
            $SQLPoProduct = $dbUtil->getUpdateSQLStringFromArray($fa1, 'po_product', $whereCondition);
            $db->sql_query($SQLPoProduct);

            $recCount++;
        }

        $sql ="
        SELECT SUM(it.qty * it.unit_price) As amount
        FROM invoice_item it
        LEFT JOIN (invoice i) ON (i.invoice_id = it.invoice_id)
        WHERE it.invoice_id = {$invoice_id}
          AND i.invoice_type = 'Supplier'
        ";
        $result = $db->sql_query($sql);
        $row = $db->sql_fetchrow($result);

        $fa2 = array();
        $fa2['invoice_amount']  = $row['amount'];

        $whereCondition = "
        WHERE invoice_id = {$invoice_id}
        ";
        $SQLInvoice = $dbUtil->getUpdateSQLStringFromArray($fa2, 'invoice', $whereCondition);
        $db->sql_query($SQLInvoice);

        //$this->getGenerateInvoiceForMedia($invoice_id);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getPopulateReceiptAmount() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $po_product_id = $fn->getReqParam('po_product_id');
        $checkedVal = $fn->getReqParam('checkedVal');

        if($checkedVal == 1){
            $_SESSION['selectedPoProductIds'][] = $po_product_id;
        }
        else if($checkedVal == 0){
            $s = &$_SESSION['selectedPoProductIds'];
            if(($key = array_search($po_product_id, $s)) !== false){
                unset($s[$key]);
            }
        }

        if(count($_SESSION['selectedPoProductIds']) == 0){
            return 0;
        }
        $selectedPoProductIds = join(',', $_SESSION['selectedPoProductIds']);

        $SQLPaid = "
        SELECT SUM(invoice_amount) AS invoice_selected_sum
        FROM po_product
        WHERE invoice_code IN ({$selectedPoProductIds})
          AND invoice_type = 'Supplier'
        ";
        $resultPaid = $db->sql_query($SQLPaid);
        $rowPaid    = $db->sql_fetchrow($resultPaid);

        $SQLPartialPayment = "
        SELECT SUM(irh.amount) AS invoice_partial_payment
        FROM invoice_receipt_history irh
        LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
        WHERE i.invoice_code IN ({$selectInvoiceIds})
        ";
        $resultPartialPayment = $db->sql_query($SQLPartialPayment);
        $rowPartialPayment    = $db->sql_fetchrow($resultPartialPayment);

        if ($rowPartialPayment['invoice_partial_payment'] == 0){
            return $rowPaid['invoice_selected_sum'];
        } else {
            return $rowPaid['invoice_selected_sum'] - $rowPartialPayment['invoice_partial_payment'];
        }

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
             ,'company_id_supplier' => $phpExcel->getImportFldObj('Supplier')
             ,'qty'                 => $phpExcel->getImportFldObj('Qty')
             ,'xrate'               => $phpExcel->getImportFldObj('Xrate')
             ,'fc_price'            => $phpExcel->getImportFldObj('Fc Price')
             ,'price'               => $phpExcel->getImportFldObj('Price')
             ,'base_price'          => $phpExcel->getImportFldObj('Base Price')
             ,'category'            => $phpExcel->getImportFldObj('Main Category')
             ,'sub_category'        => $phpExcel->getImportFldObj('Sub Category')

             ,'item_code'           => $phpExcel->getImportFldObj('Item Code')
             ,'part_number'         => $phpExcel->getImportFldObj('Item Ref Code')
             ,'fc_price_code'       => $phpExcel->getImportFldObj('Fc price code')
             ,'title'               => $phpExcel->getImportFldObj('Product Title')
             ,'model'               => $phpExcel->getImportFldObj('Model')
             ,'carton_no'           => $phpExcel->getImportFldObj('Carton No')
             ,'batch_no'            => $phpExcel->getImportFldObj('Batch No')
             ,'unit'                => $phpExcel->getImportFldObj('Unit')
             ,'commodity_code'      => $phpExcel->getImportFldObj('Commodity Code')
             ,'vat'                 => $phpExcel->getImportFldObj('VAT %')
        );
        $fa['qty']['refOnly'] = true;
        $fa['xrate']['refOnly'] = true;
        $fa['fc_price']['refOnly'] = true;
        $fa['price']['refOnly'] = true;
        $fa['base_price']['refOnly'] = true;
        $fa['category']['refOnly'] = true;
        $fa['sub_category']['refOnly'] = true;

        $fa['item_code']['refOnly'] = true;
        $fa['part_number']['refOnly'] = true;
        $fa['fc_price_code']['refOnly'] = true;
        $fa['title']['refOnly'] = true;
        $fa['model']['refOnly'] = true;
        $fa['carton_no']['refOnly'] = true;
        $fa['batch_no']['refOnly'] = true;
        $fa['unit']['refOnly'] = true;
        $fa['commodity_code']['refOnly'] = true;
        $fa['vat']['refOnly'] = true;
        $fa['batch_import']['defaultValue'] = $row['batch_import'] + 1;
        $fa['po_code']['defaultValue'] = $this->getUpdatePOCode();

        $fa['company_id_supplier']['specialType'] = 'fetchIdFromRefModule';
        $fa['company_id_supplier']['exp']['refModule'] = 'tradingsg_supplier';

        /****************************************/
        $config = array(
             'module'              => 'enggCrm_purchaseOrder'
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
        $xrate = $fa['xrate'];
        $fc_price = $fa['fc_price'];
        $price = $fa['price'];
        $base_price = $fa['base_price'];
        $supplier_id = $fa['company_id_supplier'];
        $batch_import = $fa['batch_import'];
        $category = $fa['category'];
        $sub_category = $fa['sub_category'];

        $item_code = $fa['item_code'];
        $part_number = $fa['part_number'];
        $fc_price_code = $fa['fc_price_code'];
        $title = $fa['title'];
        $model = $fa['model'];
        $carton_no = $fa['carton_no'];
        $batch_no = $fa['batch_no'];
        $unit = $fa['unit'];
        $commodity_code = $fa['commodity_code'];
        $vat = $fa['vat'];

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
        $fa2['part_number'] = $part_number;
        $fa2['fc_price_code']  = $fc_price_code;
        $fa2['title']  = $title;
        $fa2['model']  = $model;
        $fa2['carton_no']  = $carton_no;
        $fa2['batch_no']  = $batch_no;
        $fa2['unit']  = $unit;
        $fa2['commodity_code']  = $commodity_code;
        $fa2['vat']  = $vat;
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

        //$recCount = $fn->getRecordCount('po_product', "product_id = '{$product_id}' AND purchase_order_id = '{$purchase_order_id}'");

        //if (is_numeric ($product_id) && $recCount == 0) {
            $fa3 = array();
            $fa3['product_id'] = $product_id;
            $fa3['purchase_order_id']  = $purchase_order_id;
            $fa3['supplier_id']  = $supplier_id;
            $fa3['qty']  = $qty;
            $fa3['xrate']  = $xrate;
            $fa3['fc_price']  = $fc_price;
            $fa3['base_price']  = $base_price;
            $fa3['price']  = $price;
            $fa3 = $fn->addCreationDetailsToFieldsArray($fa3, 'po_product');

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa3, 'po_product');
            $result = $db->sql_query($SQL);
        //}
    }

    /**
     *
     */
    function getUpdateProductCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of Product Code */
        $nextProductItemCode = $fn->getSettingsValueByKey("nextProductItemCode");

        if($nextProductItemCode < 10){
            $ProCode = $fn->getSettingsValueByKey('productCodePrefix') . '00' . $nextProductItemCode;
        }
        else if($nextProductItemCode < 100){
            $ProCode = $fn->getSettingsValueByKey('productCodePrefix') . '0' . $nextProductItemCode;
        }
        /*else if($nextProductItemCode < 1000){
            $ProCode = $fn->getSettingsValueByKey('productCodePrefix') . '0' . $nextProductItemCode;
        }*/
        else{
            $ProCode = $nextProductItemCode;
        }

        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextProductItemCode'";
        $result = $db->sql_query($SQL);

        return $ProCode;
    }

    /**
     *
     */
    function getUpdatePOCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of Purchase order Code */
        $poCode = $fn->getSettingsValueByKey("poCode");

        $POCode = $fn->getSettingsValueByKey('poCodePrefix') . $poCode;

        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'poCode'";
        $result = $db->sql_query($SQL);

        return $POCode;
    }
}
