<?
class CPL_Admin_Widgets_EnggCrm_ProjectPurchaseOrder_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "";

        return $SQL;
    }
    
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;        
    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enggCrm_projectPurchaseOrder');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }

    /**
     * Add Purchase order submit in new window
     */
    function getAddMultiplePurchaseOrderSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $project_id      = $fn->getReqParam('project_id');
        $supplier_id     = $fn->getPostParam('supplier_id');
        $po_date         = $fn->getPostParam('po_date');
        $po_code         = $fn->getPostParam('po_code');
        $gst             = $fn->getPostParam('gst');
        $title_arr       = $fn->getPostParam('title', array());
        $quantity_arr    = $fn->getPostParam('quantity', array());
        $unit_arr        = $fn->getPostParam('unit', array());
        $amount_arr      = $fn->getPostParam('amount', array());
        // $description_arr = $fn->getPostParam('description', array());
       $product_id_arr          = $fn->getReqParam('product_id', array());

        if (!$this->getAddMultiplePurchaseOrderValidate()){
            return $validate->getErrorMessageXML();
        }

        $count = count($title_arr);
        $totalAmount = 0;
        for ($i= 0; $i < $count; $i++) {
            $title  = $title_arr[$i];
            $quantity    = $quantity_arr[$i];
            $unit        = $unit_arr[$i];
            $amount      = $amount_arr[$i];
            // $description = $description_arr[$i];

            if ($title) {
                /* Checking whether the supplier record is available for the project in purchase_order */
                $purchaseOrderRec = $fn->getRecordByCondition('purchase_order',
                                    "company_id_supplier = '{$supplier_id}' AND project_id = {$project_id} AND po_code = '{$po_code}'");
                if (is_array($purchaseOrderRec)){
                    $purchase_order_id = $purchaseOrderRec['purchase_order_id'];
                } else {
                    $faPo = array();
                    $faPo['project_id']          = $project_id;
                    $faPo['company_id_supplier'] = $supplier_id;
                    $faPo['po_date']             = $po_date;
                    $faPo['po_code']             = $this->getUpdateAddQuoteCode();
                    $faPo['gst']                 = $gst;

                    if ($gst == 1) {
                        $faPo['gst_percentage'] = $cpCfg['cp.gstPercentage'];
                    } else {
                        $faPo['gst_percentage'] = '0.00';
                    }

                    $faPo['delivery_date']       = date('Y-m-d');
                    $faPo['creation_date']       = date('Y-m-d H:i:s');
                    $faPo['created_by']          = $fn->getSessionParam('userName');

                    if ($cpCfg['m.enggCrm.project.addShippingAddressInPO'] == 1) {
                        $country_po_code = explode("-", $cpCfg['cp.companyAddress3']);

                        $faPo['shipping_address_flat']     = $cpCfg['cp.companyAddress1'];
                        $faPo['shipping_address_street']   = $cpCfg['cp.companyAddress2'];
                        $faPo['shipping_address_country']  = $country_po_code[0];
                        $faPo['shipping_address_po_code']  = $country_po_code[1];
                    }

                    $SQLInsert = $dbUtil->getInsertSQLStringFromArray($faPo, 'purchase_order');
                    $resultInsert = $db->sql_query($SQLInsert);
                    $purchase_order_id = $db->sql_nextid();
                }

                // $SQLUpdateProduct = "
                // UPDATE product SET qty_in_stock = '{$quantity}', price = '{$amount}', unit = '{$unit}', description = '{$description}'
                // WHERE product_id = '{$product_id}'
                // ";
                // $resultUpdateProduct  = $db->sql_query($SQLUpdateProduct);

                // $rowProduct = $fn->getRecordRowByID('product', 'product_id', $product_id);

                /* Saving Items for the purchase order in po_product */
                $fa = array();
                $fa['purchase_order_id'] = $purchase_order_id;
                $fa['item_title']        = $title;
                //$fa['quantity']          = $quantity;
                $fa['qty']               = $quantity;
                $fa['unit']              = $unit;
                //$fa['amount']            = $amount;
                $fa['cost_price']        = $amount;
                // $fa['description']       = $description;
                //$fa['product_id']        = $product_id;
                $fa['creation_date']     = date('Y-m-d H:i:s');
                $fa['status']            = 'In Progress';
                $fa['created_by']        = $fn->getSessionParam('userName');

                $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'po_product');
                $result = $db->sql_query($insert);
                $po_product_id = $db->sql_nextid();

                $totalAmount += $quantity * $amount;
            }
        }

        $comRec = $fn->getRecordRowByID('company', 'company_id', $supplier_id);
        $supRec = $fn->getRecordByCondition('supplier', "company_name = '{$comRec['company_name']}'");
        if (is_array($supRec)) {
            $supplier_id_expense = $supRec['supplier_id'];
        } else {
            $fa2 = array();
            $fa2['company_name'] = $comRec['company_name'];
            $insertSupplier = $dbUtil->getInsertSQLStringFromArray($fa2, 'supplier');
            $resultSupplier = $db->sql_query($insertSupplier);
            $supplier_id_expense = $db->sql_nextid();
        }
        
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getUpdateAddQuoteCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of Quote Code */
        $purchaseordercode = $fn->getSettingsValueByKey("purchaseordercode");

        if($purchaseordercode < 10){
            $quoteCode = $fn->getSettingsValueByKey('purchaseorderprefix') . $purchaseordercode . '/' . date("Y");
        }
        else if($purchaseordercode < 99){
            $quoteCode = $fn->getSettingsValueByKey('purchaseorderprefix'). $purchaseordercode . '/' . date("Y");
        }
      
        else{
            $quoteCode = $fn->getSettingsValueByKey('purchaseorderprefix')  . $purchaseordercode . '/' . date("Y");
        }

        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'purchaseordercode'";
        $result = $db->sql_query($SQL);

        return $quoteCode;
    }
    /**
     *
     */
    function getEditMultiplePurchaseOrderValidate() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $validate->validateData('po_date', 'Please select date');

        // $product_arr = $fn->getReqParam('product_id', array());

        // $filterArray = array_filter($product_arr);
        // if (count($filterArray) == 0){
        //     $validate->errorArray['error_box']['name'] = "error_box1";
        //     $validate->errorArray['error_box']['msg']  = "Please select product";
        // }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    function getEditMultiplePurchaseOrderSubmit() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $project_id        = $fn->getReqParam('project_id');
        $purchase_order_id = $fn->getReqParam('purchase_order_id');
        $po_date           = $fn->getPostParam('po_date');
        $po_code           = $fn->getPostParam('po_code');
        $gst               = $fn->getPostParam('gst');
        $quantity_arr      = $fn->getPostParam('quantity', array());
        $unit_arr          = $fn->getPostParam('unit', array());
        $amount_arr        = $fn->getPostParam('amount', array());
        $description_arr   = $fn->getPostParam('description', array());
        $title_arr    = $fn->getReqParam('title', array());
        $po_product_id_arr = $fn->getReqParam('po_product_id', array());

        if (!$this->getEditMultiplePurchaseOrderValidate()){
            return $validate->getErrorMessageXML();
        }

        $count = count($title_arr);
        for ($i= 0; $i < $count; $i++) {
            $title    = $title_arr[$i];
            $po_product_id = $po_product_id_arr[$i];
            $quantity      = $quantity_arr[$i];
            $unit          = $unit_arr[$i];
            $amount        = $amount_arr[$i];
            // $description   = $description_arr[$i];

            if ($title) {
                $faPo = array();
                $faPo['po_date'] = $po_date;
                $faPo['po_code'] = $po_code;
                $faPo['gst']     = $gst;

                if ($gst == 1) {
                    $faPo['gst_percentage'] = $cpCfg['cp.gstPercentage'];
                } else {
                    $faPo['gst_percentage'] = '0.00';
                }

                $faPo['modified_by']        = $fn->getSessionParam('userName');
                $faPo['modification_date']  = date('Y-m-d H:i:s');

                $whereConditionPO = "WHERE purchase_order_id = {$purchase_order_id}";
                $sqlUpdatePO      = $dbUtil->getUpdateSQLStringFromArray($faPo, "purchase_order", $whereConditionPO);
                $resultUpdatePO   = $db->sql_query($sqlUpdatePO);

                // $rowProduct = $fn->getRecordRowByID('product', 'product_id', $product_id);

                if($po_product_id == "") {
                    $fa = array();
                    $fa['purchase_order_id'] = $purchase_order_id;
                    $fa['item_title']        = $title;
                    $fa['qty']               = $quantity;
                    $fa['unit']              = $unit;
                    $fa['cost_price']        = $amount;
                    // $fa['description']       = $description;
                    // $fa['product_id']        = $product_id;
                    $fa['status']            = 'In Progress';
                    $fa['created_by']        = $fn->getSessionParam('userName');
                    $fa['creation_date']     = date('Y-m-d H:i:s');

                    $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'po_product');
                    $result = $db->sql_query($insert);
                    $po_product_id = $db->sql_nextid();
                } else {
                    $faPoP = array();
                    $faPoP['item_title']        = $title;
                    $faPoP['qty']               = $quantity;
                    $faPoP['unit']              = $unit;
                    $faPoP['cost_price']        = $amount;
                    // $faPoP['description']       = $description;
                    // $faPoP['product_id']        = $product_id;
                    $faPoP['modified_by']       = $fn->getSessionParam('userName');
                    $faPoP['modification_date'] = date('Y-m-d H:i:s');

                    $whereConditionPoP = "WHERE po_product_id = {$po_product_id}";
                    $sqlUpdatePoP      = $dbUtil->getUpdateSQLStringFromArray($faPoP, "po_product", $whereConditionPoP);
                    $resultUpdatePoP   = $db->sql_query($sqlUpdatePoP);
                }
            }
        }
        
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddMultiplePurchaseOrderValidate() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $validate->validateData('supplier_id', 'Please select Supplier');
        $validate->validateData('po_date', 'Please select date');

        // $product_arr = $fn->getReqParam('product_id', array());

        // $filterArray = array_filter($product_arr);
        // if (count($filterArray) == 0){
        //     $validate->errorArray['error_box']['name'] = "error_box1";
        //     $validate->errorArray['error_box']['msg']  = "Please select product";
        // }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
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
            $ProCode = $fn->getSettingsValueByKey('productCodePrefix') . '000' . $nextProductItemCode;
        }
        else if($nextProductItemCode < 99){
            $ProCode = $fn->getSettingsValueByKey('productCodePrefix') . '00' . $nextProductItemCode;
        }
        else if($nextProductItemCode < 999){
            $ProCode = $fn->getSettingsValueByKey('productCodePrefix') . '0' . $nextProductItemCode;
        }
        else{
            $ProCode = $fn->getSettingsValueByKey('productCodePrefix') . $nextProductItemCode;
        }

        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextProductItemCode'";
        $result = $db->sql_query($SQL);

        return $ProCode;
    }

    /**
     *
     */
    function getSearchProductTitle() {
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);

        $productTitle = $extractor[0];

        $SQL = "
        SELECT p.title AS value
              ,p.title AS label
              ,p.product_id AS id
              ,CONCAT_WS(' **** ', p.title) AS label
              ,p.category_id AS category
              ,p.product_type
              ,(SELECT i.actual_stock
                FROM inventory i
                WHERE i.product_id = p.product_id) AS stock
        FROM product p
        WHERE (p.title LIKE '{$productTitle}%')
          AND p.published = 1
        ORDER BY p.title
        ";

        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }

    /**
     *
     */
    function getCreationModificationPO() {
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');

        $po_product_id = $fn->getReqParam('po_product_id');

        $header = "
        <thead>
            <tr>
                <td>Created By/Creation Date</td>
                <td>Modified By/Modification Date</td>
            </tr>
        </thead>
        ";

        $SQLPO ="
        SELECT pp.creation_date
              ,pp.created_by
              ,pp.modification_date
              ,pp.modified_by
        FROM po_product pp
        WHERE pp.po_product_id = {$po_product_id}
        ";
        $resultPo = $db->sql_query($SQLPO);
        $rowPo    = $db->sql_fetchrow($resultPo);

        if($rowPo['modified_by'] != ""){
            $modified_by = "{$rowPo['modified_by']}/{$rowPo['modification_date']}";
        }else{
            $modified_by = "";
        }

        if($rowPo['created_by'] != ""){
            $created_by = "{$rowPo['created_by']}/{$rowPo['creation_date']}";
        }else{
            $created_by = "";
        }

        $rows = "
        <tbody>
            <tr>
                <td>{$created_by}</td>
                <td>{$modified_by}</td>
            </tr>
        </tbody>
        ";

        $text = "
        <form id='creationModificationPo' class='creationModificationPo' method='post'>
            <table class='thinlist' id='po_productTable'>
                {$header}
                {$rows}
            </table>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getProjectByCompanyJSON(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows = "";

        $company_id   = $fn->getReqParam('company_id');

        $json  = array();

        if ($company_id == ""){
            return json_encode($json);
        }

        $SQL = "
        SELECT project_id
              ,title
        FROM project
        WHERE company_id = '{$company_id}'
        ORDER BY title
        ";
        $result   = $db->sql_query($SQL);

        $json[] = array("value" => "", "caption" => "Select Project");
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['project_id'], "caption" => $row['title']);
        }

        return json_encode($json);
    }

    /**
     * Purchase Order Edit Form Submit
     */
    function getEditForPoSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $purchase_order_id      = $fn->getReqParam('purchase_order_id');
        $supplier_reference_no  = $fn->getPostParam('supplier_reference_no');
        $status                 = $fn->getPostParam('status');
        $our_reference_no       = $fn->getPostParam('our_reference_no');
        $po_date                = $fn->getPostParam('po_date');
        $po_code                = $fn->getPostParam('po_code');
        $shipping_method        = $fn->getPostParam('shipping_method');
        $payment_terms          = $fn->getPostParam('payment_terms');
        $delivery_date          = $fn->getPostParam('delivery_date');
        $delivery_to            = $fn->getPostParam('delivery_to');
        $payment                = $fn->getPostParam('payment');
        $project                = $fn->getPostParam('project');
        $contact                = $fn->getPostParam('contact');
        $mobile                 = $fn->getPostParam('mobile');
        $delivery_terms         = $fn->getPostParam('delivery_terms');
        //$delivery_address       = $fn->getPostParam('delivery_address');
        $company_id_supplier            = $fn->getPostParam('company_id_supplier');
        $payment_type                 = $fn->getPostParam('payment_type');
        $warranty_type                 = $fn->getPostParam('warranty_type');
        $delivery_type                 = $fn->getPostParam('delivery_type');
        $price_basis                 = $fn->getPostParam('price_basis');
        $document                 = $fn->getPostParam('document');


        $fa = array();
        $fa['supplier_reference_no']  = $supplier_reference_no;
        $fa['our_reference_no']       = $our_reference_no;
        $fa['po_date']                = $po_date;
        $fa['company_id_supplier']    = $company_id_supplier;
        $fa['shipping_method']        = $shipping_method;
        $fa['payment_terms']          = $payment_terms;
        $fa['delivery_date']          = $delivery_date;
        $fa['delivery_to']            = $delivery_to;
        $fa['payment']                = $payment;
        $fa['project']                = $project;
        $fa['contact']                = $contact;
        $fa['mobile']                 = $mobile ;
        $fa['delivery_terms']         = $delivery_terms;
        //$fa['delivery_address']       = $delivery_address;
        $fa['payment_type']                 = $payment_type ;
        $fa['warranty_type']                 = $warranty_type ;
        $fa['delivery_type']                 = $delivery_type ;
        $fa['price_basis']                 = $price_basis ;
        $fa['document']                 = $document ;

        if ($cpCfg['m.enggCrm.project.addShippingAddressInPO'] == 1) {
            $fa['shipping_address_flat']     = $fn->getPostParam('shipping_address_flat');
            $fa['shipping_address_street']   = $fn->getPostParam('shipping_address_street');
            $fa['shipping_address_country']  = $fn->getPostParam('shipping_address_country');
            $fa['shipping_address_po_code']  = $fn->getPostParam('shipping_address_po_code');
        }

        $fa = $fn->addModificationDetailsToFieldsArray($fa, 'purchase_order');

        $whereCondition = "WHERE purchase_order_id = {$purchase_order_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "purchase_order", $whereCondition);
        $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();
    }


    /**
     *
     */
    function getAddNewProductMasterValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter product name');
        $validate->validateData('product_type', 'Please select product type');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAddNewProductMasterSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        if (!$this->getAddNewProductMasterValidate()){
            return $validate->getErrorMessageXML();
        }

        $title        = $fn->getPostParam('title');
        $product_type = $fn->getPostParam('product_type');
        
        $title = trim($title);

        $fa = array();
        $fa['title']         = $title;
        $fa['published']     = 1;
        $fa['product_type']  = $product_type;
        $fa['item_code']     = $this->getUpdateProductCode();
        $fa['created_by']    = $fn->getSessionParam('userName');
        $fa['creation_date'] = date("Y-m-d H:i:s");

        $insert1    = $dbUtil->getInsertSQLStringFromArray($fa, 'product');
        $result1    = $db->sql_query($insert1);
        $product_id = $db->sql_nextid();

        return $validate->getSuccessMessageXML();
    } 

    /**
     *
     */
    function getCreateStockTransfer() {
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');

        $project_id    = $fn->getReqParam('project_id');
        $to_project_id = $fn->getReqParam('to_project_id');
        $qty           = $fn->getReqParam('qty');
        $product_id    = $fn->getReqParam('product_id');

        $fa = array();
        $fa['from_project_id'] = $project_id;
        $fa['to_project_id']   = $to_project_id;
        $fa['product_id']      = $product_id;
        $fa['quantity']        = $qty;
        $fa['creation_date']   = date("Y-m-d H:i:s");

        $SQLInsert         = $dbUtil->getInsertSQLStringFromArray($fa, 'stock_transfer');
        $resultInsert      = $db->sql_query($SQLInsert);
        $stock_transfer_id = $db->sql_nextid();

        return $stock_transfer_id;
    }


    /**
     *
     */
    function getUpdateQtyStockTransfer() {
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');

        $stock_transfer_id = $fn->getReqParam('stock_transfer_id');
        $quantity          = $fn->getReqParam('qty');

        if($stock_transfer_id != "") {
            $fa = array();
            $fa['quantity']          = $quantity;
            $fa['modification_date'] = date("Y-m-d H:i:s");

            $whereCondition = "WHERE stock_transfer_id = {$stock_transfer_id}";
            $sqlUpdate    = $dbUtil->getUpdateSQLStringFromArray($fa, "stock_transfer", $whereCondition);
            $resultUpdate = $db->sql_query($sqlUpdate);
        }
    }

    /**
     *
     */
    function getSearchClientName() {
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);

        $clientName = $extractor[0];

        $SQL = "
        SELECT c.company_name AS value
              ,c.company_name AS label
              ,c.company_id AS id
        FROM company c
        WHERE (c.company_name LIKE '%{$clientName}%')
        ORDER BY c.company_name
        ";
        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }

    /**
     *
     */
    function getSearchProjectTitle() {
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);

        $projectTitle = $extractor[0];

        $SQL = "
        SELECT p.title AS value
              ,p.title AS label
              ,p.project_id AS id
              ,c.company_name
              ,CONCAT_WS(' ** ', p.project_code, p.title) AS label
        FROM project p
        LEFT JOIN (company c) ON (p.company_id = c.company_id)
        WHERE (p.title LIKE '%{$projectTitle}%'
        OR p.project_code LIKE '%{$projectTitle}%')
        AND p.status = 'WIP'
        ORDER BY p.title
        ";
        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }

    /**
     * 
     */
    function getEditPoLineItemValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        
        $validate->resetErrorArray();
        $product_id = $fn->getPostParam('product_id');

        if($product_id == "") {
            $validate->errorArray['item_title']['name'] = "item_title";
            $validate->errorArray['item_title']['msg']  = "Please type and select product";
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Line Item Edit Form Submit
     */
    function getEditPoLineItemSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getEditPoLineItemValidate()){
            return $validate->getErrorMessageXML();
        }

        $po_product_id  = $fn->getReqParam('po_product_id');
        $purchase_order_id  = $fn->getReqParam('purchase_order_id');
                         
        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'item_title');
        $fa = $fn->addToFieldsArray($fa, 'unit');
        $fa = $fn->addToFieldsArray($fa, 'cost_price');
        $fa = $fn->addToFieldsArray($fa, 'qty');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addModificationDetailsToFieldsArray($fa, 'po_product');

        $whereCondition = "WHERE po_product_id = {$po_product_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "po_product", $whereCondition);
        $db->sql_query($SQL);

        $SQLPo = "
        SELECT SUM(qty * cost_price) AS total_amount
        FROM po_product
        WHERE purchase_order_id = {$purchase_order_id}
        ";
        $resultPo = $db->sql_query($SQLPo);
        $rowPo    = $db->sql_fetchrow($resultPo);

        $totalAmount = $rowPo['total_amount'];

        return $validate->getSuccessMessageXML();

    }

    /**
     *
     */
    function getTransferToOtherPO() {
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');

        $po_product_id = $fn->getReqParam('po_product_id');
        $product_id    = $fn->getReqParam('product_id');
        $project_id    = $fn->getReqParam('project_id');

        $SQLPO ="
        SELECT pp.qty
        FROM po_product pp
        WHERE pp.po_product_id = {$po_product_id}
        ";
        $resultPo = $db->sql_query($SQLPO);
        $rowPo    = $db->sql_fetchrow($resultPo);

        $SQL = "
        SELECT SUM(st.quantity) AS quantity
        FROM stock_transfer st
        WHERE st.product_id = {$product_id}
          AND st.from_project_id = {$project_id}
        ";
        $result = $db->sql_query($SQL);
        $rowST  = $db->sql_fetchrow($result);

        //$sqlProject = "SELECT project_id, title FROM project";
        $sqlProject = "";

        $rows = "
        <tbody>
            <tr>
                <td>
                    <input type='text' value='' id='clientName' class='text clientName' name='client_name'>
                    <input type='hidden' name='company_id' class='company_id_hidden' value=''>
                </td>
                <td>
                    <select name='to_project_id' class='poProductTitle'>
                        <option value=''>Select Project</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $sqlProject)}
                    </select>
                </td>
                <td class='stock'></td>
                <td class='quantity'>
                    <input type='text' value='' id='quantity' class='text quantity' name='quantity' disabled='disabled'>
                    <input type='hidden' name='stock_transfer_id' class='stockTransferId' value=''>
                </td>
                <td>
                    <a class='btn btn-success saveQty'>
                        Save
                    </a>
                </td>
            </tr>
        </tbody>
        ";

        $header = "
        <thead>
            <tr>
                <td>Client Name</td>
                <td>Project Name</td>
                <td>Stock</td>
                <td>Qty</td>
                <td></td>
            </tr>
        </thead>
        ";

        $totQty = $rowPo['qty'] - $rowST['quantity'];
        
        $newRow = "
        <a class='addTransferProjectRow btn btn-primary mb10 float_left'>Add Line Item</a>
        ";

        $text = "
        <form id='materialPurchasedTransfer' class='materialPurchasedTransfer' method='post'>
            {$newRow}
            <div class='float_left'>
                <div class='mt5'>
                    Total Quantity : {$totQty}
                </div>
            </div>
            <table class='list thinlist' id='po_productTable'>
                {$header}
                {$rows}
            </table>
            <input type='hidden' name='product_id' class='product_id_hidden' value='{$product_id}'>
            <input type='hidden' name='total_qty' class='tot_qty_hidden' value='{$totQty}'>
        </form>
        ";

        return $text;
    }

     /**
     *
     */
    function getAddTransferProjectRowRecord() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $sqlProject = "";

        $rows = "
        <tr>
            <td>
                <input type='text' value='' id='clientName' class='text clientName' name='client_name'>
                <input type='hidden' name='company_id' class='company_id_hidden' value=''>
            </td>
            <td>
                <select name='to_project_id' class='poProductTitle'>
                    <option value=''>Select Project</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlProject)}
                </select>
            </td>
            <td class='stock'></td>
            <td class='quantity'>
                <input type='text' value='' id='quantity' class='text quantity' name='quantity' disabled='disabled'>
                <input type='hidden' name='stock_transfer_id' class='stockTransferId' value=''>
            </td>
            <td>
                <a class='btn btn-success saveQty'>
                    Save
                </a>
            </td>
        </tr>
        ";

        return $rows;
    }

    /**
     *
     */
    function getUpdateInventoryCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of Purchase order Code */
        $inventoryCode = $fn->getSettingsValueByKey("inventoryCode");

        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'inventoryCode'";
        $result = $db->sql_query($SQL);

        return $inventoryCode;
    }

    /**
     *
     */
    function getUpdateQtyDelivered() {
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');

        $poProductId = $fn->getReqParam('deliveryOrderChecked', array());

        foreach($poProductId AS $po_product_id){
            if($po_product_id != ''){
                $SQLPoProdPrev = "
                SELECT product_id
                      ,qty
                      ,damage_qty
                      ,purchase_order_id
                      ,po_product_id
                      ,qty_updated
                FROM po_product
                WHERE po_product_id = '{$po_product_id}'
                ";
                $resultPoProdPrev = $db->sql_query($SQLPoProdPrev);
                $PoProdPrev       = $db->sql_fetchrow($resultPoProdPrev);

                $SQLPOProduct = "
                UPDATE po_product SET qty_updated = qty, status = 'Closed'
                WHERE po_product_id = '{$po_product_id}'
                ";
                $resultPOProduct  = $db->sql_query($SQLPOProduct);

                $SQLPoProd = "
                SELECT product_id
                      ,qty
                      ,damage_qty
                      ,purchase_order_id
                      ,po_product_id
                      ,qty_updated
                FROM po_product
                WHERE po_product_id = '{$po_product_id}'
                ";
                $resultPoProd = $db->sql_query($SQLPoProd);
                $PoProd       = $db->sql_fetchrow($resultPoProd);

                $poQty = $PoProd['qty'];

                $SQLInventory = "
                SELECT product_id
                      ,actual_stock
                FROM inventory
                WHERE product_id = {$PoProd['product_id']}
                ";
                $resultInventory  = $db->sql_query($SQLInventory);
                $numRowsInventory = $db->sql_numrows($resultInventory);

                if($numRowsInventory == 0) {
                    $stockUpdate = $poQty;

                    $SQLUpdateProduct = "
                    UPDATE product SET qty_in_stock = {$stockUpdate}
                    WHERE product_id = '{$PoProd['product_id']}'
                    ";
                    $resultUpdateProduct  = $db->sql_query($SQLUpdateProduct);

                    $fa = array();
                    $fa['product_id']     = $PoProd['product_id'];
                    $fa['actual_stock']   = $stockUpdate;
                    $fa['inventory_code'] = $this->getUpdateInventoryCode();
                    $fa['creation_date']  = date('Y-m-d H:i:s');

                    $inventory_id = $fn->addRecord($fa, 'inventory');
                } else {
                    $rowInventory = $db->sql_fetchrow($resultInventory);

                    $qty_batchwise = $poQty - $PoProdPrev['qty_updated'];
                    $stockCalc     = $rowInventory['actual_stock'] + $qty_batchwise;

                    $SQLUpdateProduct = "
                    UPDATE product SET qty_in_stock = {$stockCalc}
                    WHERE product_id = '{$PoProd['product_id']}'
                    ";
                    $resultUpdateProduct  = $db->sql_query($SQLUpdateProduct);

                    $SQLUpdateInventory = "
                    UPDATE inventory SET actual_stock = {$stockCalc}
                    WHERE product_id = '{$PoProd['product_id']}'
                    ";
                    $resultUpdateInventory  = $db->sql_query($SQLUpdateInventory);
                }

                $shRec = $fn->getRecordByCondition('stock_history', "po_product_id = '{$po_product_id}'");
                if($shRec['stock_history_id'] == ''){
                    $fa = array();
                    $fa['po_product_id'] = $po_product_id;
                    $fa['product_id']  = $PoProd['product_id'];
                    $fa['purchase_order_id']  = $PoProd['purchase_order_id'];
                    $fa['qty']  = $PoProd['qty'];
                    $fa['creation_date']  = date("Y-m-d H:i:s");

                    $SQLInsert = $dbUtil->getInsertSQLStringFromArray($fa, 'stock_history');
                    $resultInsert = $db->sql_query($SQLInsert);
                }
            }
        }
    }

    /**
     *
     */
    function getAddMultipleMaterialRequestValidate() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $validate->validateData('po_date', 'Please select date');
        //$validate->validateData('po_code', 'Please enter MR No.');

        $product_arr = $fn->getReqParam('product_id', array());

        $filterArray = array_filter($product_arr);
        if (count($filterArray) == 0){
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please select product";
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAddMultipleMaterialRequestSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $project_id      = $fn->getReqParam('project_id');
        $supplier_id_arr = $fn->getPostParam('supplier_id', array());
        $status_arr      = $fn->getPostParam('poStatus', array());
        $brand_arr       = $fn->getPostParam('brand', array());
        $po_date         = $fn->getPostParam('po_date');
        $po_code         = $fn->getPostParam('po_code');
        $gst             = $fn->getPostParam('gst');
        //$title_arr       = $fn->getPostParam('title', array());
        $quantity_arr    = $fn->getPostParam('quantity', array());
        $unit_arr        = $fn->getPostParam('unit', array());
        $amount_arr      = $fn->getPostParam('amount', array());
        $description_arr = $fn->getPostParam('description', array());
        $product_id_arr  = $fn->getReqParam('product_id', array());

        if (!$this->getAddMultipleMaterialRequestValidate()){
            return $validate->getErrorMessageXML();
        }

        if($gst == ''){
            $gst = 0;
        }

        $projectRec = $fn->getRecordRowByID('project', 'project_id', $project_id);

        $faPo = array();
        $faPo['project_id']          = $project_id;
        $faPo['mr_date']             = $po_date;
        $faPo['mr_code']             = $this->getUpdateAddMRCode();
        $faPo['gst']                 = $gst;
        $faPo['project_name']        = $projectRec['title'];
        $faPo['request_by']          = $fn->getSessionParam('userName');
        $faPo['request_date']        = date('Y-m-d');

        if ($gst == 1) {
            $faPo['gst_percentage'] = $cpCfg['cp.gstPercentage'];
        } else {
            $faPo['gst_percentage'] = '0.00';
        }

        $faPo['delivery_date']       = date('Y-m-d');
        $faPo['creation_date']       = date('Y-m-d H:i:s');
        $faPo['created_by']          = $fn->getSessionParam('userName');

        if ($cpCfg['m.enggCrm.project.addShippingAddressInPO'] == 1) {
            $country_po_code = explode("-", $cpCfg['cp.companyAddress3']);

            $faPo['shipping_address_flat']     = $cpCfg['cp.companyAddress1'];
            $faPo['shipping_address_street']   = $cpCfg['cp.companyAddress2'];
            $faPo['shipping_address_country']  = $country_po_code[0];
            $faPo['shipping_address_po_code']  = $country_po_code[1];
        }

        $SQLInsert    = $dbUtil->getInsertSQLStringFromArray($faPo, 'materials_request');
        $resultInsert = $db->sql_query($SQLInsert);
        $materials_request_id = $db->sql_nextid();
        
        $count = count($product_id_arr);
        $totalAmount = 0;
        for ($i= 0; $i < $count; $i++) {
            $product_id  = $product_id_arr[$i];
            $quantity    = $quantity_arr[$i];
            $unit        = $unit_arr[$i];
            $amount      = $amount_arr[$i];
            $supplier_id = $supplier_id_arr[$i];
            $brand       = $brand_arr[$i];
            $description = $description_arr[$i];
            $status      = $status_arr[$i];

            if ($product_id) {
                $rowProduct = $fn->getRecordRowByID('product', 'product_id', $product_id);

                $fa = array();
                $fa['materials_request_id'] = $materials_request_id;
                $fa['item_title']           = $rowProduct['title'];
                $fa['qty']                  = $quantity;
                $fa['supplier_id']          = $supplier_id;
                $fa['brand']                = $brand;
                $fa['unit']                 = $unit;
                $fa['cost_price']           = $amount;
                $fa['description']          = $description;
                $fa['product_id']           = $product_id;
                $fa['creation_date']        = date('Y-m-d H:i:s');
                $fa['status']               = $status;
                $fa['created_by']           = $fn->getSessionParam('userName');

                $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'materials_request_line_items');
                $result = $db->sql_query($insert);
                $materials_request_line_items_id = $db->sql_nextid();

                $totalAmount += $quantity * $amount;
            }
        }
        
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getUpdateAddMRCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of MR Code */
        $nextMRCode = $fn->getSettingsValueByKey("nextMRCode");

        if($nextMRCode < 10){
            $mRCode = $fn->getSettingsValueByKey('mRCodePrefix'). '00' . $nextMRCode;
        }
        else if($nextMRCode < 99){
            $mRCode = $fn->getSettingsValueByKey('mRCodePrefix'). '0' . $nextMRCode;
        }
        else if($nextMRCode > 99 || $nextOppCode < 999){
            $mRCode = $fn->getSettingsValueByKey('mRCodePrefix'). '' . $nextMRCode;
        }
        else{
            $mRCode = $fn->getSettingsValueByKey('mRCodePrefix'). '' . $nextMRCode;
        }

        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextMRCode'";
        $result = $db->sql_query($SQL);

        return $mRCode;
    }

    /**
     * Purchase Order Edit Form Submit
     */
    function getEditForMaterialsRequestSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $materials_request_id   = $fn->getReqParam('materials_request_id');
        $supplier_reference_no  = $fn->getPostParam('supplier_reference_no');
        $our_reference_no       = $fn->getPostParam('our_reference_no');
        $po_date                = $fn->getPostParam('po_date');
        $shipping_method        = $fn->getPostParam('shipping_method');
        $payment_terms          = $fn->getPostParam('payment_terms');
        $delivery_date          = $fn->getPostParam('delivery_date');
        $delivery_terms         = $fn->getPostParam('delivery_terms');
        $company_id_supplier    = $fn->getPostParam('company_id_supplier');
        $project_name           = $fn->getPostParam('project_name');
        $site_reference         = $fn->getPostParam('site_reference');
        $request_by             = $fn->getPostParam('request_by');
        $request_date           = $fn->getPostParam('request_date');
        $approved_by            = $fn->getPostParam('approved_by');
        $approved_date          = $fn->getPostParam('approved_date');

        $fa = array();
        $fa['supplier_reference_no'] = $supplier_reference_no;
        $fa['our_reference_no']      = $our_reference_no;
        $fa['mr_date']               = $po_date;
        $fa['shipping_method']       = $shipping_method;
        $fa['payment_terms']         = $payment_terms;
        $fa['delivery_date']         = $delivery_date;
        $fa['delivery_terms']        = $delivery_terms;
        $fa['project_name']          = $project_name;
        $fa['site_reference']        = $site_reference;
        $fa['request_by']            = $request_by;
        $fa['request_date']          = $request_date;
        $fa['approved_by']           = $approved_by;
        $fa['approved_date']         = $approved_date;

        if ($cpCfg['m.enggCrm.project.addShippingAddressInPO'] == 1) {
            $fa['shipping_address_flat']     = $fn->getPostParam('shipping_address_flat');
            $fa['shipping_address_street']   = $fn->getPostParam('shipping_address_street');
            $fa['shipping_address_country']  = $fn->getPostParam('shipping_address_country');
            $fa['shipping_address_po_code']  = $fn->getPostParam('shipping_address_po_code');
        }

        $fa = $fn->addModificationDetailsToFieldsArray($fa, 'materials_request');

        $whereCondition = "WHERE materials_request_id = {$materials_request_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "materials_request", $whereCondition);
        $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditMultipleMaterialRequestSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $materials_request_id                = $fn->getReqParam('materials_request_id');
        $project_id                          = $fn->getReqParam('project_id');
        $supplier_id_arr                     = $fn->getPostParam('supplier_id', array());
        $status_arr                          = $fn->getPostParam('poStatus', array());
        $poStatusHidden_arr                  = $fn->getPostParam('poStatusHidden', array());
        $brand_arr                           = $fn->getPostParam('brand', array());
        $po_date                             = $fn->getPostParam('po_date');
        $po_code                             = $fn->getPostParam('po_code');
        $gst                                 = $fn->getPostParam('gst');
        //$title_arr                           = $fn->getPostParam('title', array());
        $quantity_arr                        = $fn->getPostParam('quantity', array());
        $unit_arr                            = $fn->getPostParam('unit', array());
        $amount_arr                          = $fn->getPostParam('amount', array());
        $description_arr                     = $fn->getPostParam('description', array());
        $product_id_arr                      = $fn->getReqParam('product_id', array());
        $materials_request_line_items_id_arr = $fn->getReqParam('materials_request_line_items_id', array());

        if (!$this->getAddMultipleMaterialRequestValidate()){
            return $validate->getErrorMessageXML();
        }

        $count = count($product_id_arr);
        $faPo = array();
        $faPo['project_id']          = $project_id;
        $faPo['mr_date']             = $po_date;
        $faPo['mr_code']             = $po_code;
        $faPo['gst']                 = $gst;

        if ($gst == 1) {
            $faPo['gst_percentage'] = $cpCfg['cp.gstPercentage'];
        } else {
            $faPo['gst_percentage'] = '0.00';
        }

        $faPo['modification_date']   = date('Y-m-d H:i:s');
        $faPo['modified_by']         = $fn->getSessionParam('userName');

        if ($cpCfg['m.enggCrm.project.addShippingAddressInPO'] == 1) {
            $country_po_code = explode("-", $cpCfg['cp.companyAddress3']);

            $faPo['shipping_address_flat']     = $cpCfg['cp.companyAddress1'];
            $faPo['shipping_address_street']   = $cpCfg['cp.companyAddress2'];
            $faPo['shipping_address_country']  = $country_po_code[0];
            $faPo['shipping_address_po_code']  = $country_po_code[1];
        }

        $whereCondition = "WHERE materials_request_id = {$materials_request_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($faPo, 'materials_request', $whereCondition);
        $db->sql_query($SQL);

        $totalAmount = 0;
        $disaleStatus = array(
             'Sent for approval'
            ,'Approved by Company Admin'
            ,'PO generated'
            ,'Material delivered'
        );
        
        for ($i= 0; $i < $count; $i++) {
            $poStatus   = $poStatusHidden_arr[$i];
            $product_id = $product_id_arr[$i];

            if(in_array($poStatus, $disaleStatus)) {
                $quantity                        = $quantity_arr[$i];
                $unit                            = $unit_arr[$i];
                $amount                          = $amount_arr[$i];
                $description                     = $description_arr[$i];
                $materials_request_line_items_id = $materials_request_line_items_id_arr[$i];
            } else {
                $quantity                        = $quantity_arr[$i];
                $unit                            = $unit_arr[$i];
                $amount                          = $amount_arr[$i];
                $supplier_id                     = $supplier_id_arr[$i];
                $brand                           = $brand_arr[$i];
                $description                     = $description_arr[$i];
                $status                          = $status_arr[$i];
                $poStatus                        = $poStatusHidden_arr[$i];
                $materials_request_line_items_id = $materials_request_line_items_id_arr[$i];
            }

            if ($product_id) {
                $rowProduct = $fn->getRecordRowByID('product', 'product_id', $product_id);

                if($materials_request_line_items_id) {
                    $fa = array();

                    if(in_array($poStatus, $disaleStatus)) {
                        $fa['qty']                  = $quantity;
                        $fa['unit']                 = $unit;
                        $fa['cost_price']           = $amount;
                        $fa['description']          = $description;
                        $fa['modified_by']          = $fn->getSessionParam('userName');
                        $fa['modification_date']    = date('Y-m-d H:i:s');
                    } else {
                        $fa['materials_request_id'] = $materials_request_id;
                        $fa['item_title']           = $rowProduct['title'];
                        $fa['qty']                  = $quantity;
                        $fa['supplier_id']          = $supplier_id;
                        $fa['brand']                = $brand;
                        $fa['unit']                 = $unit;
                        $fa['cost_price']           = $amount;
                        $fa['description']          = $description;
                        $fa['product_id']           = $product_id;
                        $fa['status']               = $status;
                        $fa['modified_by']          = $fn->getSessionParam('userName');
                        $fa['modification_date']    = date('Y-m-d H:i:s');
                    }

                    $whereCondition2 = "WHERE materials_request_line_items_id = {$materials_request_line_items_id}";
                    $SQL2 = $dbUtil->getUpdateSQLStringFromArray($fa, 'materials_request_line_items', $whereCondition2);
                    $db->sql_query($SQL2);
                } else {
                    $fa = array();
                    $fa['materials_request_id'] = $materials_request_id;
                    $fa['item_title']           = $rowProduct['title'];
                    $fa['qty']                  = $quantity;
                    $fa['supplier_id']          = $supplier_id;
                    $fa['brand']                = $brand;
                    $fa['unit']                 = $unit;
                    $fa['cost_price']           = $amount;
                    $fa['description']          = $description;
                    $fa['product_id']           = $product_id;
                    $fa['creation_date']        = date('Y-m-d H:i:s');
                    $fa['status']               = $status;
                    $fa['created_by']           = $fn->getSessionParam('userName');

                    $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'materials_request_line_items');
                    $result = $db->sql_query($insert);
                    $materials_request_line_items_id = $db->sql_nextid();
                }
            }
        }
        
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getCreationModificationMR() {
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');

        $materials_request_line_items_id = $fn->getReqParam('materials_request_line_items_id');

        $header = "
        <thead>
            <tr>
                <td>Created By/Creation Date</td>
                <td>Modified By/Modification Date</td>
            </tr>
        </thead>
        ";

        $SQLPO ="
        SELECT mrli.creation_date
              ,mrli.created_by
              ,mrli.modification_date
              ,mrli.modified_by
        FROM materials_request_line_items mrli
        WHERE mrli.materials_request_line_items_id = {$materials_request_line_items_id}
        ";
        $resultPo = $db->sql_query($SQLPO);
        $rowPo    = $db->sql_fetchrow($resultPo);

        if($rowPo['modified_by'] != ""){
            $modified_by = "{$rowPo['modified_by']}/{$rowPo['modification_date']}";
        }else{
            $modified_by = "";
        }

        if($rowPo['created_by'] != ""){
            $created_by = "{$rowPo['created_by']}/{$rowPo['creation_date']}";
        }else{
            $created_by = "";
        }

        $rows = "
        <tbody>
            <tr>
                <td>{$created_by}</td>
                <td>{$modified_by}</td>
            </tr>
        </tbody>
        ";

        $text = "
        <form id='creationModificationMR' class='creationModificationMR' method='post'>
            <table class='thinlist' id='po_productTable'>
                {$header}
                {$rows}
            </table>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getUpdateMaterialSupplierConfirmStatus(){
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');

        $mrProductId = $fn->getReqParam('matReqProductChecked', array());

        foreach($mrProductId AS $materials_request_line_items_id) {
            if($materials_request_line_items_id != ''){
                $SQLPOProduct = "
                UPDATE materials_request_line_items SET status = 'Sent for approval'
                WHERE materials_request_line_items_id = '{$materials_request_line_items_id}'
                ";
                $resultPOProduct  = $db->sql_query($SQLPOProduct);
            }
        }
    }

    /**
     *
     */
    function getApproveMaterialRequestByAdmin(){
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');

        $mrProductId = $fn->getReqParam('matReqProductChecked', array());
        $project_id  = $fn->getReqParam('project_id');
        $materialIds = sprintf('("%s")', implode('","',array_values($mrProductId)));

        $sql = "
        SELECT mrli.*
        FROM materials_request_line_items mrli
        WHERE mrli.materials_request_line_items_id IN {$materialIds}
        GROUP BY mrli.supplier_id
        ";
        $result = $db->sql_query($sql);
        while ($row = $db->sql_fetchrow($result)) {
            $faPo = array();
            $faPo['project_id']               = $project_id;
            $faPo['company_id_supplier']      = $row['supplier_id'];
            $faPo['po_date']                  = date("Y-m-d");
            $faPo['created_by']               = $fn->getSessionParam('userName');
            $faPo['creation_date']            = date('Y-m-d H:i:s');

            $SQLInsert         = $dbUtil->getInsertSQLStringFromArray($faPo, 'purchase_order');
            $resultInsert      = $db->sql_query($SQLInsert);
            $purchase_order_id = $db->sql_nextid();

            $sqlAppendSupp = '';
            if($row['supplier_id'] != ''){
                $sqlAppendSupp = "AND mrli.supplier_id = {$row['supplier_id']}";
            }

            $sqlMaterialRequestLineItems = "
            SELECT mrli.*
            FROM materials_request_line_items mrli
            LEFT JOIN materials_request mr ON (mr.materials_request_id = mrli.materials_request_id)
            WHERE mr.project_id    = {$project_id}
              AND mrli.status != 'PO generated'
              {$sqlAppendSupp}
            ";
            $resultMaterialRequestLineItems = $db->sql_query($sqlMaterialRequestLineItems);
            while($rowMaterialRequestLineItems = $db->sql_fetchrow($resultMaterialRequestLineItems)){
                $fa = array();
                $fa['purchase_order_id'] = $purchase_order_id;
                $fa['item_title']        = $rowMaterialRequestLineItems['item_title'];
                $fa['qty']               = $rowMaterialRequestLineItems['qty'];
                $fa['unit']              = $rowMaterialRequestLineItems['unit'];
                $fa['cost_price']        = $rowMaterialRequestLineItems['cost_price'];
                $fa['description']       = $rowMaterialRequestLineItems['description'];
                $fa['product_id']        = $rowMaterialRequestLineItems['product_id'];
                $fa['supplier_id']       = $rowMaterialRequestLineItems['supplier_id'];
                $fa['brand']             = $rowMaterialRequestLineItems['brand'];
                $fa['status']            = 'In Progress';
                $fa['created_by']        = $fn->getSessionParam('userName');
                $fa['creation_date']     = date('Y-m-d H:i:s');

                $insertPO = $dbUtil->getInsertSQLStringFromArray($fa, 'po_product');
                $resultPO = $db->sql_query($insertPO);
                $po_product_id = $db->sql_nextid();

                $SQLMRProduct = "
                UPDATE materials_request_line_items SET status = 'PO generated'
                WHERE materials_request_line_items_id = '{$rowMaterialRequestLineItems['materials_request_line_items_id']}'
                ";
                $resultMRProduct  = $db->sql_query($SQLMRProduct);
            }
        }
    }

    /**
     *
     */
    function getAddNewSupplierValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $validate->resetErrorArray();
        $validate->validateData('company_name', 'Please enter supplier name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAddNewSupplierSubmit() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        if (!$this->getAddNewSupplierValidate()){
            return $validate->getErrorMessageXML();
        }

        $company_name    = $fn->getPostParam('company_name');
        $email           = $fn->getPostParam('email');
        $fax             = $fn->getPostParam('fax');
        $mobile          = $fn->getPostParam('mobile');
        $address_flat    = $fn->getPostParam('address_flat');
        $address_street  = $fn->getPostParam('address_street');
        $address_state   = $fn->getPostParam('address_state');
        $address_country = $fn->getPostParam('address_country');
        
        $fa = array();
        $fa['company_name']    = $company_name;
        $fa['email']           = $email;
        $fa['fax']             = $fax;
        $fa['mobile']          = $mobile;
        $fa['address_flat']    = $address_flat;
        $fa['address_street']  = $address_street;
        $fa['address_state']   = $address_state;
        $fa['address_country'] = $address_country;
        $fa['category']        = 'Supplier';
        $fa['created_by']      = $fn->getSessionParam('userName');
        $fa['creation_date']   = date("Y-m-d H:i:s");

        $insert1 = $dbUtil->getInsertSQLStringFromArray($fa, 'supplier');
        $result1 = $db->sql_query($insert1);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getSupplierByJSON(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $json = array();

        $SQL = "
        SELECT supplier_id
              ,company_name
        FROM supplier
        ORDER BY company_name
        ";
        $result   = $db->sql_query($SQL);

        $json[] = array("value" => "", "caption" => "Select");
        while ($row = $db->sql_fetchrow($result)) {
                $json[] = array("value" => $row['supplier_id'], "caption" => $row['company_name']);
        }

        return json_encode($json);
    }
}