<?
class CP_Admin_Modules_Hms_PurchaseOrder_Model extends CP_Common_Lib_ModuleModelAbstract
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

        $SQL = "
        SELECT po.*
              ,m.title AS supplier_name
              ,clnt.company_name
              ,CONCAT_WS(' ', c.first_name, c.last_name) AS contact_name_supplier
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
        FROM purchase_order po
        LEFT JOIN medical_supplier m ON m.medical_supplier_id = po.company_id_supplier
        LEFT JOIN contact c ON po.contact_id_supplier = c.contact_id
        LEFT JOIN staff s ON po.staff_id = s.staff_id
        LEFT JOIN company clnt ON clnt.company_id = c.company_id
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

        $status            = $fn->getReqParam('status');
        $company_id        = $fn->getReqParam('company_id');
        $purchase_order_id     = $fn->getReqParam('purchase_order_id');

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
                    OR m.title LIKE '%{$tv['keyword']}%'
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
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('company_id_supplier', 'Please choose the Supplier');

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

        $supplier_id = $fn->getPostParam('company_id_supplier');

        $SQLsupplier = "
        SELECT title
        FROM `medical_supplier`
        WHERE medical_supplier_id = '{$supplier_id}'
        ";
        $resultsupplier = $db->sql_query($SQLsupplier);
        $rowsupplier    = $db->sql_fetchrow($resultsupplier);

        $po_code = $this->getUpdatePOCode();

        $fa = $this->getFields();
        $fa['purchase_order_date'] = date('Y-m-d');
        $fa['status']              = 'In progress';
        $fa['title']               = 'Purchase From '.$rowsupplier['title'];
        $fa['po_code']             = $po_code;
        $id = $fn->addRecord($fa);

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
        $fa = $fn->addToFieldsArray($fa, 'po_code');
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'company_id_supplier');
        $fa = $fn->addToFieldsArray($fa, 'contact_id_supplier');
        $fa = $fn->addToFieldsArray($fa, 'payment_terms');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'purchase_order_date');
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
    function getAddMultipleLineItemValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $purchase_order_id  = $fn->getPostParam('purchase_order_id');
        $supplier_id        = $fn->getPostParam('supplier_id');
        $product_arr        = $fn->getReqParam('product_id', array());
        $price_arr          = $fn->getPostParam('price', array());
        $qty_arr            = $fn->getPostParam('qty', array());

        $validate->resetErrorArray();

        $filterArray3 = array_filter($qty_arr);
        if (count($filterArray3) == 0){
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please Enter Qty";
        }

        /*$filterArray2 = array_filter($price_arr);
        if (count($filterArray2) == 0){
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please Enter Price";
        }
        */


        $filterArray = array_filter($product_arr);
        if (count($filterArray) == 0){
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please select product";
        }

        $SQLproduct = "
        SELECT product_id
        FROM po_product
        WHERE purchase_order_id = {$purchase_order_id}
        ";
        $resultproduct = $db->sql_query($SQLproduct);
        $product_id_db_arr = array();
        while($rowproduct    = $db->sql_fetchrow($resultproduct)){
            if($rowproduct['product_id'] != ''){
                $product_id_db_arr[] = $rowproduct['product_id'];
            }
        }

        $product_id_arr = array();
        foreach($product_arr as $key => $value){
            if($value != ''){
                if(in_array($value, $product_id_arr)){
                    $validate->errorArray['error_box']['name'] = "error_box1";
                    $validate->errorArray['error_box']['msg']  = "Please select different product";
                }

                if(!in_array($value, $product_id_arr)){
                    $product_id_arr[] = $value;
                }

                if(in_array($value, $product_id_db_arr)){
                    $validate->errorArray['error_box']['name'] = "error_box1";
                    $validate->errorArray['error_box']['msg']  = "Selected product already exists.";
                }
            }

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
    function getAddMultipleLineItemSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        if (!$this->getAddMultipleLineItemValidate()){
            return $validate->getErrorMessageXML();
        }

        $purchase_order_id  = $fn->getPostParam('purchase_order_id');
        $supplier_id        = $fn->getPostParam('supplier_id');
        $product_arr        = $fn->getReqParam('product_id', array());
        $price_arr          = $fn->getPostParam('price', array());
        $qty_arr            = $fn->getPostParam('qty', array());

        $count = count($price_arr);
        for ($i= 0; $i < $count; $i++) {
            $product_id         = $product_arr[$i];
            $price              = $price_arr[$i];
            $qty                = $qty_arr[$i];

            if ($product_id) {
                $fa = array();

                $fa['product_id']           = $product_id;
                $fa['price']                = $price;
                $fa['qty_requested']        = $qty;
                $fa['status']               = 'New';
                $fa['purchase_order_id']    = $purchase_order_id;
                $fa['supplier_id']          = $supplier_id;
                $fa['status']               = 'In progress';
                $fa['creation_date']        = date("Y-m-d H:i:s");
                $fa['created_by']           = $fn->getSessionParam('userName');

                $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'po_product');
                $result = $db->sql_query($insert);
                $po_product_id = $db->sql_nextid();
            }
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */

    function getProductFormSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        if (!$this->getProductValidate()){
            return $validate->getErrorMessageXML();
        }

        $product_id        = $fn->getPostParam('product_id');
        $purchase_order_id = $fn->getPostParam('purchase_order_id');
        $price             = $fn->getPostParam('price');
        $qty               = $fn->getPostParam('qty');
        $qty_delivered     = $fn->getPostParam('qty_delivered');
        $status            = $fn->getPostParam('status');

        $fa = array();

        $fa['product_id']       = $product_id;
        $fa['price']            = $price;
        $fa['qty_requested']    = $qty;
        $fa['qty']              = $qty_delivered;
        $fa['status']           = $status;
        $fa['purchase_order_id']= $purchase_order_id;
        $fa['creation_date']    = date("Y-m-d H:i:s");
        $fa['created_by']       = $fn->getSessionParam('userName');

        $fn->addRecord($fa, 'po_product');

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */

    function getProductValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        //$validate->validateData('product_group_id', 'Please select the product group');
        //$validate->validateData('product_id', 'Please enter Medicine Name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getEditPoProductRecordValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $validate->validateData('qty_delivered', 'Please enter the qty');
        $validate->validateData('status', 'Please select status');

        $po_product_id  = $fn->getPostParam('po_product_id');
        $qty_delivered  = $fn->getPostParam('qty_delivered');
        $qty            = $fn->getPostParam('qty');
        $damaged_qty    = $fn->getPostParam('damaged_qty');
        //$po_productRec  = $fn->getRecordRowByID('po_product', 'po_product_id', $po_product_id);
        $qty            = $qty - $damaged_qty;

        if($qty_delivered > $qty){
            $validate->errorArray['qty_delivered']['name'] = "qty_delivered";
            $validate->errorArray['qty_delivered']['msg']  = "Please enter qty less than or equal to {$qty}.";
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
    function getEditPoProductRecordSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        if (!$this->getEditPoProductRecordValidate()){
            return $validate->getErrorMessageXML();
        }

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $po_product_id   = $fn->getPostParam('po_product_id');
        $product_id      = $fn->getPostParam('product_id');
        $qty_delivered   = $fn->getPostParam('qty_delivered');
        $qty             = $fn->getPostParam('qty');
        $damaged_qty     = $fn->getPostParam('damaged_qty');
        $status          = $fn->getPostParam('status');
        $price           = $fn->getPostParam('price');

        $fa = array();
        $fa['qty_requested']     = $qty;
        $fa['qty']               = $qty_delivered;
        $fa['damaged_qty']       = $damaged_qty;
        $fa['status']            = $status;
        //$fa['price']             = $price;
        $fa['modification_date'] = date("Y-m-d H:i:s");
        $fa['modified_by']       = $fn->getSessionParam('userName');

        $whereCondition = "WHERE po_product_id = {$po_product_id}";
        $SQL    = $dbUtil->getUpdateSQLStringFromArray($fa, "po_product", $whereCondition);
        $result = $db->sql_query($SQL);

        $SQLPoProd = "
        SELECT product_id
        FROM po_product
        WHERE po_product_id = '{$po_product_id}'
        ";
        $resultPoProd  = $db->sql_query($SQLPoProd);
        $PoProd = $db->sql_fetchrow($resultPoProd);

        $SQLStockTransfer = "
        SELECT  st.from_location
                ,st.to_location
                ,sh.product_id
                ,SUM(sh.qty) AS Transfer_qty
        FROM stock_transfer st
        LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
        WHERE sh.product_id = {$PoProd['product_id']} AND st.from_location = {$cpSiteIdSession}";

        $resultStockTransfer = $db->sql_query($SQLStockTransfer);
        $rowStockTransfer = $db->sql_fetchrow($resultStockTransfer);

        $SQLStockTransferto = "
        SELECT  st.from_location
                ,st.to_location
                ,sh.product_id
                ,SUM(sh.qty) AS Transfer_qty_to
        FROM stock_transfer st
        LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
        WHERE sh.product_id = {$PoProd['product_id']} AND st.to_location = {$cpSiteIdSession}";

        $resultStockTransferto = $db->sql_query($SQLStockTransferto);
        $rowStockTransferto = $db->sql_fetchrow($resultStockTransferto);

        $SQLOthersite = "
        SELECT
            (SELECT SUM(qty) FROM po_product pp
             LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
             WHERE pp.product_id = {$PoProd['product_id']} AND po.site_id = {$cpSiteIdSession}) as product_qty_purchased

           ,(SELECT SUM(invItem.qty) FROM invoice_item invItem
            LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
            LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
            WHERE record_id = {$PoProd['product_id']}
              AND o.site_id = {$cpSiteIdSession}
            ) as product_qty_sold_from_quote

            ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
            LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
            LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
            WHERE ini.record_id = {$PoProd['product_id']}
            AND inv.site_id = {$cpSiteIdSession}
            ) as sales_return_qty

            ,(SELECT SUM(pp.damaged_qty) FROM po_product pp
              LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
              WHERE pp.product_id = {$PoProd['product_id']} AND po.site_id = {$cpSiteIdSession}
             ) as damaged_qty
        ";
        $resultothersite = $db->sql_query($SQLOthersite);
        $rowothersite = $db->sql_fetchrow($resultothersite);

        $SqlExpenseProduct = "
        SELECT SUM(ep.qty) AS qty
        FROM expense_product ep
        LEFT JOIN expense e ON (e.expense_id = ep.expense_id)
        WHERE ep.product_id = {$PoProd['product_id']}
        AND ep.status = 'Added'
        AND e.site_id = {$cpSiteIdSession}
        AND ep.stock_deducted = 1
        ";
        $resultExpenseProduct = $db->sql_query($SqlExpenseProduct);
        $rowExpenseProduct    = $db->sql_fetchrow($resultExpenseProduct);

        $stock = $rowothersite['product_qty_purchased'] - $rowothersite['product_qty_sold_from_quote'] + $rowothersite['sales_return_qty'] - $rowothersite['damaged_qty'] - $rowStockTransfer['Transfer_qty'] + $rowStockTransferto['Transfer_qty_to'] - $rowExpenseProduct['qty'];

        $SQLUpdateProduct = "
        UPDATE product SET qty_in_stock{$cpSiteIdSession} = {$stock}
        WHERE product_id = '{$PoProd['product_id']}'
        ";
        $resultUpdateProduct  = $db->sql_query($SQLUpdateProduct);

        $SQLUpdateInventory = "
        UPDATE inventory SET actual_stock{$cpSiteIdSession} = {$stock}
        WHERE product_id = '{$PoProd['product_id']}'
        ";
        $resultUpdateInventory  = $db->sql_query($SQLUpdateInventory);

        if($price != '' && $price > 0){
            $SQLProductPrice = "
            SELECT *
            FROM product_price
            WHERE product_id = {$product_id}
            AND site_id = {$cpSiteIdSession}
            ";
            $resultProductPrice  = $db->sql_query($SQLProductPrice);
            $numRowsProductPrice = $db->sql_numrows($resultProductPrice);
            $rowProductPrice     = $db->sql_fetchrow($resultProductPrice);
            if($numRowsProductPrice > 0){
                if($rowProductPrice['price'] != $price){
                    $fa6 = array();
                    $fa6['product_id']       = $rowProductPrice['product_id'];
                    $fa6['price']            = $rowProductPrice['price'];
                    $fa6['site_id']          = $cpSiteIdSession;
                    $fa6['product_price_id'] = $rowProductPrice['product_price_id'];
                    $fa6['creation_date']    = date("Y-m-d H:i:s");
                    $fa6['created_by']       = $fn->getSessionParam('userName');

                    $insert = $dbUtil->getInsertSQLStringFromArray($fa6, 'product_price_history');
                    $result = $db->sql_query($insert);

                    $fa7 = array();
                    $fa7['price']             = $price;
                    $fa7['modification_date'] = date("Y-m-d H:i:s");
                    $fa7['modified_by']       = $fn->getSessionParam('userName');

                    $whereCondition = "WHERE product_price_id = {$rowProductPrice['product_price_id']}";
                    $SQL    = $dbUtil->getUpdateSQLStringFromArray($fa7, "product_price", $whereCondition);
                    $result = $db->sql_query($SQL);
                }
            }else{
                $fa7 = array();
                $fa7['product_id']       = $product_id;
                $fa7['price']            = $price;
                $fa7['site_id']          = $cpSiteIdSession;
                $fa7['creation_date']    = date("Y-m-d H:i:s");
                $fa7['created_by']       = $fn->getSessionParam('userName');

                $insert = $dbUtil->getInsertSQLStringFromArray($fa7, 'product_price');
                $result = $db->sql_query($insert);
            }
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getHMSPurchaseOrderHMSProductLinkSQL($id) {

        $SQL = "
        SELECT po.po_product_id
              ,p.title AS product_name
              ,po.price
              ,po.qty
              ,po.qty_delivered
              ,po.status
        FROM po_product po
        JOIN product p ON (p.product_id = po.product_id)
        WHERE po.purchase_order_id = {$id}
        ";

        return $SQL;
    }
    /**
     *
     */
    function getExportData($dataArray){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');


        $rows = '';

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Purchaseorder_Export_" . date("d-m-Y") . ".xls";

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment;filename={$file_name}");
        header("Content-Transfer-Encoding: binary ");

        $objPHPExcel = new PHPExcel();

        //--------------------------------------------------//
        $rowc = 1;
        $colc = 0;
        $appendSql = '';
        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Product Code');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Product Title');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Cost Price');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Quantity');

        /*if ($cpCfg['cp.hasMultiUniqueSites']){
            $SQLSite = "
            SELECT title
            FROM site
            ";
            $resultSite = $db->sql_query($SQLSite);
            while ($rowSite = $db->sql_fetchrow($resultSite)) {
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowSite['title']);
            }
        }*/

        /******************** FORMAT HEADER *******************/
        $headStyle = array(
            'font' => array('bold' => true)
        );

        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A1:{$lastCol}1")->applyFromArray($headStyle);

        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }

        //foreach ($dataArray as $row){
            //$colc = 0;
            //$rowc++;

            $SQLProduct = "
                SELECT title
                       ,product_code
                       ,price
                       ,qty_in_stock
                FROM product
                ";
                $resultProduct = $db->sql_query($SQLProduct);
                while ($rowProduct    = $db->sql_fetchrow($resultProduct)) {

                $colc = 0;
                $rowc++;

                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowProduct['product_code']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowProduct['title']);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc);
                    $actSheet->setCellValueByColumnAndRow($colc++, $rowc);
                }

            /*if ($cpCfg['cp.hasMultiUniqueSites']){
                $SQLSite = "
                SELECT title
                       ,site_id
                FROM site
                ";
                $resultSite = $db->sql_query($SQLSite);
                while ($rowSite = $db->sql_fetchrow($resultSite)) {
                    $SQLProdPrice = "
                    SELECT price AS prod_price
                    FROM product_price
                    WHERE product_id = {$row['product_id']}
                    AND site_id = {$rowSite['site_id']}
                    ";
                    $resultProdPrice = $db->sql_query($SQLProdPrice);
                    while ($rowProdPrice = $db->sql_fetchrow($resultProdPrice)) {
                        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $rowProdPrice['prod_price']);
                    }
                }
            }*/

        //}

        $colc = 0;
        $rowc++;

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');

        $rowc++;

        $actSheet->getStyle("A{$rowc}:F{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
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
             'product_code'        => $phpExcel->getImportFldObj('PRODUCT CODE')
            ,'purchase_order_date' => $phpExcel->getImportFldObj('PURCHASE DATE')
            ,'title'               => $phpExcel->getImportFldObj('PRODUCT NAME')
            ,'category'            => $phpExcel->getImportFldObj('CATEGORY')
            ,'description'         => $phpExcel->getImportFldObj('DESCRIPTION')
            ,'pack_qty'            => $phpExcel->getImportFldObj('PACK QTY')
            ,'pack_type'           => $phpExcel->getImportFldObj('PACK TYPE')
            ,'qty_requested1'      => $phpExcel->getImportFldObj('QUANTITY1')
            ,'qty_requested2'      => $phpExcel->getImportFldObj('QUANTITY2')
            ,'qty_requested3'      => $phpExcel->getImportFldObj('QUANTITY3')
            ,'qty_requested4'      => $phpExcel->getImportFldObj('QUANTITY4')
            ,'qty_requested5'      => $phpExcel->getImportFldObj('QUANTITY5')
            ,'qty_requested6'      => $phpExcel->getImportFldObj('QUANTITY6')
            ,'price1'              => $phpExcel->getImportFldObj('COST PRICE1')
            ,'price2'              => $phpExcel->getImportFldObj('COST PRICE2')
            ,'price3'              => $phpExcel->getImportFldObj('COST PRICE3')
            ,'price4'              => $phpExcel->getImportFldObj('COST PRICE4')
            ,'price5'              => $phpExcel->getImportFldObj('COST PRICE5')
            ,'price6'              => $phpExcel->getImportFldObj('COST PRICE6')
            ,'selling_price'       => $phpExcel->getImportFldObj('SELLING PRICE')
            ,'company_id_supplier' => $phpExcel->getImportFldObj('SUPPLIER NAME')
            ,'mol1'                => $phpExcel->getImportFldObj('MOL1')
            ,'mol2'                => $phpExcel->getImportFldObj('MOL2')
            ,'mol3'                => $phpExcel->getImportFldObj('MOL3')
            ,'mol4'                => $phpExcel->getImportFldObj('MOL4')
            ,'mol5'                => $phpExcel->getImportFldObj('MOL5')
            ,'mol6'                => $phpExcel->getImportFldObj('MOL6')
            //,'mol_type'            => $phpExcel->getImportFldObj('MOL TYPE')
            ,'manufacture_date'    => $phpExcel->getImportFldObj('MANUFACTURED DATE')
            ,'expiry_date'         => $phpExcel->getImportFldObj('EXPIRY DATE')
        );

        $fa['status']['defaultValue']       = 'In progress';
        $fa['product_code']['refOnly']      = true;
        $fa['title']['refOnly']             = true;
        $fa['description']['refOnly']       = true;
        $fa['category']['refOnly']          = true;
        $fa['dosage']['refOnly']            = true;
        $fa['manufacture_date']['refOnly']  = true;
        $fa['expiry_date']['refOnly']       = true;
        $fa['unit']['refOnly']              = true;
        $fa['pack_qty']['refOnly']          = true;
        $fa['pack_type']['refOnly']         = true;
        $fa['medicine_type']['refOnly']     = true;
        $fa['selling_price']['refOnly']     = true;
       // $fa['supplier_name']['refOnly']     = true;
        $fa['mol1']['refOnly']              = true;
        $fa['mol2']['refOnly']              = true;
        $fa['mol3']['refOnly']              = true;
        $fa['mol4']['refOnly']              = true;
        $fa['mol5']['refOnly']              = true;
        $fa['mol6']['refOnly']              = true;
        $fa['qty_requested1']['refOnly']    = true;
        $fa['qty_requested2']['refOnly']    = true;
        $fa['qty_requested3']['refOnly']    = true;
        $fa['qty_requested4']['refOnly']    = true;
        $fa['qty_requested5']['refOnly']    = true;
        $fa['qty_requested6']['refOnly']    = true;
        $fa['price1']['refOnly']            = true;
        $fa['price2']['refOnly']            = true;
        $fa['price3']['refOnly']            = true;
        $fa['price4']['refOnly']            = true;
        $fa['price5']['refOnly']            = true;
        $fa['price6']['refOnly']            = true;
        //$fa['mol_type']['refOnly']          = true;
        $fa['batch_import']['defaultValue'] = $row['batch_import'] + 1;
        $fa['po_code']['defaultValue']      = $this->getUpdatePOCode();

        /****************************************/
        $config = array(
             'module'              => 'hms_purchaseOrder'
            ,'matchFieldArr'       => array('company_id_supplier')
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

        $product_code     = $fa['product_code'];
        $title            = $fa['title'];
        $description      = $fa['description'];
        $category         = $fa['category'];
        $dosage           = $fa['dosage'];
        $manufacture_date = $fa['manufacture_date'];
        $expiry_date      = $fa['expiry_date'];
        $unit             = $fa['unit'];
        $pack_qty         = $fa['pack_qty'];
        $pack_type        = $fa['pack_type'];
        $medicine_type    = $fa['medicine_type'];
        $mol1             = $fa['mol1'];
        $mol2             = $fa['mol2'];
        $mol3             = $fa['mol3'];
        $mol4             = $fa['mol4'];
        $mol5             = $fa['mol5'];
        $mol6             = $fa['mol6'];
        $selling_price    = $fa['selling_price'];
        //$supplier_name    = $fa['supplier_name'];
        $qty_requested1   = $fa['qty_requested1'];
        $qty_requested2   = $fa['qty_requested2'];
        $qty_requested3   = $fa['qty_requested3'];
        $qty_requested4   = $fa['qty_requested4'];
        $qty_requested5   = $fa['qty_requested5'];
        $qty_requested6   = $fa['qty_requested6'];
        $qty_requested    = $qty_requested1 + $qty_requested2 + $qty_requested3 + $qty_requested4 + $qty_requested5 + $qty_requested6;
        $price1           = $fa['price1'];
        $price2           = $fa['price2'];
        $price3           = $fa['price3'];
        $price4           = $fa['price4'];
        $price5           = $fa['price5'];
        $price6           = $fa['price6'];
        //$mol_type         = $fa['mol_type'];

        $purchaseOrderRow = $fn->getRecordRowByID('purchase_order', 'purchase_order_id', $purchase_order_id);
        $supplierRow      = $fn->getRecordRowByID('medical_supplier', 'medical_supplier_id', $purchaseOrderRow['company_id_supplier']);

        /*$SQLPOrder1 = "
        SELECT MAX(purchase_order_id) AS purchase_order_id_stored
        FROM `purchase_order`
        ";
        $resultPOrder1 = $db->sql_query($SQLPOrder1);
        $rowcPOrder1   = $db->sql_fetchrow($resultPOrder1);

        if($rowcPOrder1['purchase_order_id_stored'] != $purchase_order_id){
            $poCode       = $fn->getSettingsValueByKey("nextPurchaseOrderCode");
            $POCode       = $fn->getSettingsValueByKey('poCodePrefix') . $poCode;

            $fa5 = array();
            $fa5['title']     = 'Purchase From '.$supplierRow['title'];
            $fa5['po_code']   = $POCode;
            $whereConditionPO = "WHERE purchase_order_id = '{$purchase_order_id}'";
            $SQLPO = $dbUtil->getUpdateSQLStringFromArray($fa5, 'purchase_order', $whereConditionPO);
            $resultPO = $db->sql_query($SQLPO);

            $SQLPOCode    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextPurchaseOrderCode'";
            $resultPOCode = $db->sql_query($SQLPOCode);

        }
        /*else{
        }*/

        /*$SQLsupplier = "
        SELECT medical_supplier_id
        FROM `medical_supplier`
        WHERE medical_supplier_id = '{$supplier_name}'
        ";
        $resultsupplier  = $db->sql_query($SQLsupplier);
        $numRowssupplier = $db->sql_numrows($resultsupplier);

        if($numRowssupplier > 0){
            $rowsupplier    = $db->sql_fetchrow($resultsupplier);
            $supplier_id    = $rowsupplier['medical_supplier_id'];
        }else{
            $faC = array();
            $faC['title']          = $supplier_name;
            $faC['published']      = 1;
            $faC['creation_date']  = date("Y-m-d H:i:s");
            $faC['created_by']     = $fn->getSessionParam('userName');

            $SQLsupplier = $dbUtil->getInsertSQLStringFromArray($faC, 'medical_supplier');
            $resultsupplier = $db->sql_query($SQLsupplier);
            $supplier_id  = $db->sql_nextid();
        }*/

        $fa5 = array();
        $fa5['title']                = 'Purchase From '.$supplierRow['title'];
        $fa5['company_id_supplier']  = $supplierRow['medical_supplier_id'];
        $whereConditionPO = "WHERE purchase_order_id = '{$purchase_order_id}'";
        $SQLPO = $dbUtil->getUpdateSQLStringFromArray($fa5, 'purchase_order', $whereConditionPO);
        $resultPO = $db->sql_query($SQLPO);

        $SQLcategory = "
        SELECT category_id
        FROM `category`
        WHERE title = '{$category}'
        ";
        $resultcategory  = $db->sql_query($SQLcategory);
        $numRowscategory = $db->sql_numrows($resultcategory);

        if($numRowscategory > 0){
            $rowcategory    = $db->sql_fetchrow($resultcategory);
            $category_id    = $rowcategory['category_id'];
        }else{
            $faC = array();
            $faC['section_id']     = 28;
            $faC['title']          = $category;
            $faC['sort_order']     = 3;
            $faC['published']      = 1;
            $faC['creation_date']  = date("Y-m-d H:i:s");
            $faC['show_in_nav']    = 1;
            $faC['created_by']     = $fn->getSessionParam('userName');

            $SQLcategory = $dbUtil->getInsertSQLStringFromArray($faC, 'category');
            $resultcategory = $db->sql_query($SQLcategory);
            $category_id  = $db->sql_nextid();
        }

        $fa2 = array();
        $fa2['product_code']     = $product_code;
        $fa2['title']            = $title;
        $fa2['description']      = $description;
        $fa2['category_id']      = $category_id;
        //$fa2['dosage']           = $dosage;
        $fa2['unit']             = $unit;
        $fa2['pack_info']        = $pack_qty;
        $fa2['pack_type']        = $pack_type;
        $fa2['medicine_type']    = $medicine_type;
        $fa2['mol1']             = $mol1;
        $fa2['mol2']             = $mol2;
        $fa2['mol3']             = $mol3;
        $fa2['mol4']             = $mol4;
        $fa2['mol5']             = $mol5;
        $fa2['mol6']             = $mol6;
        //$fa2['mol_type']         = $mol_type;
        //$fa2['qty_in_stock']     = $qty_in_stock;
        //$fa2['price']            = $price;
        $fa2['published']        = 1;
        $fa2 = $fn->addCreationDetailsToFieldsArray($fa2, 'product');

        if($product_code == ''){
            $fa2['product_code'] = $this->getUpdateProductCode();

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa2, 'product');
            $result = $db->sql_query($SQL);
            $product_id  = $db->sql_nextid();
        } else {
            $whereCondition = "WHERE product_code = '{$product_code}'";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa2, 'product', $whereCondition);
            $result = $db->sql_query($SQL);

            $rowProduct = $fn->getRecordRowByID('product', 'product_code', $product_code);
            $product_id  = $rowProduct['product_id'];
        }

        $fa3 = array();
        $fa3['product_id']        = $product_id;
        $fa3['dosage']            = $dosage;
        $fa3['status']            = 'In progress';
        $fa3['manufacture_date']  = $manufacture_date;
        $fa3['expiry_date']       = $expiry_date;
        $fa3['pack_info']         = $pack_qty;
        $fa3['pack_type']         = $pack_type;
        $fa3['medicine_type']     = $medicine_type;
        $fa3['mol1']              = $mol1;
        $fa3['mol2']              = $mol2;
        $fa3['mol3']              = $mol3;
        $fa3['mol4']              = $mol4;
        $fa3['mol5']              = $mol5;
        $fa3['mol6']              = $mol6;
        //$fa3['mol_type']          = $mol_type;
        //$fa3['price']             = $price;
        $fa3['product_id']        = $product_id;
        $fa3['selling_price']     = $selling_price;
        $fa3['purchase_order_id'] = $purchase_order_id;
        $fa3['qty_requested']     = $qty_requested;
        $fa3['qty']               = $qty_requested;
        $fa3 = $fn->addCreationDetailsToFieldsArray($fa3, 'po_product');

        $SQL    = $dbUtil->getInsertSQLStringFromArray($fa3, 'po_product');
        $result = $db->sql_query($SQL);

        $cpSiteIdSession  = $fn->getSessionParam('cp_site_id');

        $SQLSite = "
        SELECT site_id
        FROM site
        WHERE site_id != '{$cpSiteIdSession}'
        ";
        $resultSite = $db->sql_query($SQLSite);
        while ($rowSite = $db->sql_fetchrow($resultSite)) {
            $siteCheckId = $rowSite['site_id'];
            $curren_date = date("Y-m-d");

            $SQLSiteCheck = "
            SELECT to_location
                  ,stock_transfer_id
            FROM stock_transfer
            WHERE from_location = {$cpSiteIdSession}
            AND to_location = {$rowSite['site_id']}
            AND date = '{$curren_date}'
            ";
            $resultSiteCheck = $db->sql_query($SQLSiteCheck);
            $numRowsiteCheck = $db->sql_numrows($resultSiteCheck);

            if($numRowsiteCheck == 0){
                $fa4 = array();
                $fa4['date']               = date("Y-m-d");
                $fa4['from_location']      = 1;
                $fa4['to_location']        = $rowSite['site_id'];
                $fa4['status']             = 'Request';
                $fa4['created_by']         = $fn->getSessionParam('userName');
                $fa4['creation_date']      = date("Y-m-d H:i:s");
                $SQLTransafer2             = $dbUtil->getInsertSQLStringFromArray($fa4, 'stock_transfer');
                $resultTransafer2          = $db->sql_query($SQLTransafer2);
                $StockTransfer_id          = $db->sql_nextid();
            }else{
                $rowSiteCheck = $db->sql_fetchrow($resultSiteCheck);
                $StockTransfer_id          = $rowSiteCheck['stock_transfer_id'];
            }

            if(${"qty_requested$siteCheckId"} != ''){
                $fa5 = array();
                $fa5['stock_transfer_id']  = $StockTransfer_id;
                $fa5['product_id']         = $product_id;
                $fa5['qty']                = ${"qty_requested$siteCheckId"};
                $fa5['qty_requested']      = ${"qty_requested$siteCheckId"};
                $fa5['creation_date']      = date("Y-m-d H:i:s");
                $fa5['created_by']         = $fn->getSessionParam('userName');
                $SQLTransafer3             = $dbUtil->getInsertSQLStringFromArray($fa5, 'stock_transfer_history');
                $resultTransafer3          = $db->sql_query($SQLTransafer3);
            }
        }

        $SQLSite = "
        SELECT site_id
        FROM site
        ";
        $resultSite = $db->sql_query($SQLSite);
        while ($rowSite = $db->sql_fetchrow($resultSite)) {
            $siteCheckId = $rowSite['site_id'];
            
            $SQLProductPrice = "
            SELECT *
            FROM product_price
            WHERE product_id = {$product_id}
            AND site_id = {$rowSite['site_id']}
            ";
            $resultProductPrice  = $db->sql_query($SQLProductPrice);
            $numRowsProductPrice = $db->sql_numrows($resultProductPrice);
            $rowProductPrice     = $db->sql_fetchrow($resultProductPrice);
            if($numRowsProductPrice > 0){
                $fa6 = array();
                $fa6['product_id']       = $rowProductPrice['product_id'];
                $fa6['price']            = $rowProductPrice['price'];
                $fa6['site_id']          = $rowProductPrice['site_id'];
                $fa6['product_price_id'] = $rowProductPrice['product_price_id'];
                $fa6['creation_date']    = date("Y-m-d H:i:s");
                $fa6['created_by']       = $fn->getSessionParam('userName');

                $insert = $dbUtil->getInsertSQLStringFromArray($fa6, 'product_price_history');
                $result = $db->sql_query($insert);

                $fa7 = array();
                $fa7['price']             = ${"price$siteCheckId"};
                $fa7['modification_date'] = date("Y-m-d H:i:s");
                $fa7['modified_by']       = $fn->getSessionParam('userName');

                $whereCondition = "WHERE product_price_id = {$rowProductPrice['product_price_id']}";
                $SQL    = $dbUtil->getUpdateSQLStringFromArray($fa7, "product_price", $whereCondition);
                $result = $db->sql_query($SQL);
            }else{
                $fa7 = array();
                $fa7['product_id']       = $product_id;
                $fa7['price']            = ${"price$siteCheckId"};
                $fa7['site_id']          = $rowSite['site_id'];
                $fa7['creation_date']    = date("Y-m-d H:i:s");
                $fa7['created_by']       = $fn->getSessionParam('userName');

                $insert = $dbUtil->getInsertSQLStringFromArray($fa7, 'product_price');
                $result = $db->sql_query($insert);
            }
        }

    }


    /**
     *
     */
    function getUpdatePOCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of Purchase order Code */
        $poCode = $fn->getSettingsValueByKey("nextPurchaseOrderCode");

        $POCode = $fn->getSettingsValueByKey('poCodePrefix') . $poCode;

        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextPurchaseOrderCode'";
        $result = $db->sql_query($SQL);

        return $POCode;
    }
    /**
     *
     */
    function getUpdateProductCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of Product Code */
        $nextProductItemCode = $fn->getSettingsValueByKey("nextProductCode");
        $ProCode = $nextProductItemCode;

        //To update Product code
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextProductCode'";
        $resultUpdate = $db->sql_query($SQLUpdate);


        return $ProCode;
    }
    /**
     *
     */

    function getPrintPOtoPDF() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();

        $purchase_order_id = $fn->getReqParam('purchase_order_id');

        $SQL = "
        SELECT p.*
               ,pop.qty as po_QTY
               ,pop.qty_delivered
               ,(pop.qty - pop.qty_delivered) AS qty_balance
               ,pop.status
               ,pop.price AS cost_price
               ,po.purchase_order_date
               ,po.po_code
               ,po.purchase_order_id
               ,m.title AS supplier_name
               ,m.address1
               ,m.address2
               ,m.address_town
               ,m.address_state
               ,m.address_country
               ,m.billing_address_flat
               ,m.billing_address_street
               ,m.billing_address_town
               ,m.billing_address_state
               ,m.billing_address_country
        FROM purchase_order po
        LEFT JOIN po_product pop ON po.purchase_order_id = pop.purchase_order_id
        LEFT JOIN product p ON p.product_id = pop.product_id
        LEFT JOIN medical_supplier m ON m.medical_supplier_id = po.company_id_supplier
        WHERE po.purchase_order_id = {$purchase_order_id}
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);
        //============================================================================= //

        $pdf->SetFont('Courier','B',10);

        $today = date("d-m-Y");
        $purchase_order_date = $fn->getCPDate($company['purchase_order_date'], 'd/m/Y');

        $tbl1 = '
        <table border="0" width="100%" style="font-size:17px;">
            <tr>
                <td align="center" style="font-weight:bold;font-family:andalusb;">PURCHASE ORDER</td>
            </tr>
        </table>
        ';

        $address1        = $company['address1'];
        $address2        = $company['address2'];
        $address_town    = $company['address_town'];
        $address_state   = $company['address_state'];
        $address_country = $company['address_country'];

        if($address1 == '' ||
           $address2 == '' ||
           $address_town == '' ||
           $address_state == '' ||
           $address_country == ''){
            $address1        = $company['billing_address_flat'];
            $address2        = $company['billing_address_street'];
            $address_town    = $company['billing_address_town'];
            $address_state   = $company['billing_address_state'];
            $address_country = $company['billing_address_country'];
        }

        $po_code = '';
        if($company['po_code'] != ''){
            $po_code = $company['po_code'];
        }

        $recCountry['name'] = '';
        if($address_country != ''){
            $recCountry = $fn->getRecordRowByID('geo_country', 'country_code', "'{$address_country}'");
        }

        $tbl2 ='<table border="1" width="100%" cellpadding="6" style="font-size:15px;">
                    <tr>
                        <td width="64%" style="line-height:20px;"><br/>
                            <span><b>TO:</b> '.strtoupper($company['supplier_name']).'</span><br/>
                            <span>&nbsp;&nbsp;&nbsp;&nbsp;'.$address1.' '.$address2.'</span><br/>
                            <span>&nbsp;&nbsp;&nbsp;&nbsp;'.$address_town.' </span><br/>
                            <span>&nbsp;&nbsp;&nbsp;&nbsp;'.$address_state.' - '.$recCountry['name'].'</span>
                        </td>
                        <td width="36%" style="line-height:20px;"><br/>
                            <span>DATE &nbsp;&nbsp;&nbsp;: '.$purchase_order_date.'</span><br/>
                            <span>PO Code : '.$po_code.'</span>
                        </td>
                    </tr>
                </table>
                ';

        $tbl3 ='<table border="1" width="100%" cellpadding="3" style="font-size:15px;">
                    <thead>
                        <tr>
                            <th width="10%">S.NO</th>
                            <th width="60%" style="text-align:center;">PRODUCT NAME</th>
                            <th width="30%" style="text-align:center;">QTY</th>
                        </tr>
                    </thead>
                    <tbody>
        ';

        $count = 1;
        while($row = $db->sql_fetchrow($result)){

            $SQLproduct    = "
            SELECT  po.product_id
                   ,po.price
            FROM  po_product po
            WHERE po.product_id = {$row['product_id']}
            AND po.purchase_order_id < {$row['purchase_order_id']}
            ORDER BY po.product_id DESC
            LIMIT 0,1
            ";

            $resultproduct = $db->sql_query($SQLproduct);
            $rowproduct    = $db->sql_fetchrow($resultproduct);

            $tbl3 = $tbl3.'<tr>
                                <td width="10%">'.$count.'</td>
                                <td width="60%">'.$row['title'].'</td>
                                <td width="30%" style="text-align:center;">'.$row['po_QTY'].'</td>
                            </tr>
                           ';

            $count++;
        }

        $tbl3 = $tbl3.'</tbody></table>';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $download_title = $company['po_code'].'-Purchase-Order'.date('Y-m-d').'.pdf';
        $pdf->Output($download_title, 'I');
    }
    /**
     *
     */

    function getPrintPOwithpricetoPDF() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();

        $purchase_order_id = $fn->getReqParam('purchase_order_id');

        $SQL = "
        SELECT p.*
               ,pop.qty as po_QTY
               ,pop.qty_delivered
               ,(pop.qty - pop.qty_delivered) AS qty_balance
               ,pop.status
               ,pop.price AS cost_price
               ,(pop.qty*pop.price) AS total_price
               ,po.purchase_order_date
               ,po.po_code
               ,po.purchase_order_id
               ,m.title AS supplier_name
               ,m.address1
               ,m.address2
               ,m.address_town
               ,m.address_state
               ,m.address_country
               ,m.billing_address_flat
               ,m.billing_address_street
               ,m.billing_address_town
               ,m.billing_address_state
               ,m.billing_address_country
        FROM purchase_order po
        LEFT JOIN po_product pop ON po.purchase_order_id = pop.purchase_order_id
        LEFT JOIN product p ON p.product_id = pop.product_id
        LEFT JOIN medical_supplier m ON m.medical_supplier_id = po.company_id_supplier
        WHERE po.purchase_order_id = {$purchase_order_id}
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);
        //============================================================================= //

        $pdf->SetFont('Courier','B',10);

        $today = date("d-m-Y");
        $purchase_order_date = $fn->getCPDate($company['purchase_order_date'], 'd/m/Y');

        $tbl1 = '
        <table border="0" width="100%" style="font-size:17px;">
            <tr>
                <td align="center" style="font-weight:bold;font-family:andalusb;">PURCHASE ORDER</td>
            </tr>
        </table>
        ';

        $address1        = $company['address1'];
        $address2        = $company['address2'];
        $address_town    = $company['address_town'];
        $address_state   = $company['address_state'];
        $address_country = $company['address_country'];

        if($address1 == '' ||
           $address2 == '' ||
           $address_town == '' ||
           $address_state == '' ||
           $address_country == ''){
            $address1        = $company['billing_address_flat'];
            $address2        = $company['billing_address_street'];
            $address_town    = $company['billing_address_town'];
            $address_state   = $company['billing_address_state'];
            $address_country = $company['billing_address_country'];
        }

        $po_code = '';
        if($company['po_code'] != ''){
            $po_code = $company['po_code'];
        }

        $recCountry['name'] = '';
        if($address_country != ''){
            $recCountry = $fn->getRecordRowByID('geo_country', 'country_code', "'{$address_country}'");
        }

        $tbl2 ='<table border="1" width="100%" cellpadding="6" style="font-size:15px;">
                    <tr>
                        <td width="64%" style="line-height:20px;"><br/>
                            <span><b>TO:</b> '.strtoupper($company['supplier_name']).'</span><br/>
                            <span>&nbsp;&nbsp;&nbsp;&nbsp;'.$address1.' '.$address2.'</span><br/>
                            <span>&nbsp;&nbsp;&nbsp;&nbsp;'.$address_town.' </span><br/>
                            <span>&nbsp;&nbsp;&nbsp;&nbsp;'.$address_state.' - '.$recCountry['name'].'</span>
                        </td>
                        <td width="36%" style="line-height:20px;"><br/>
                            <span>DATE &nbsp;&nbsp;&nbsp;: '.$purchase_order_date.'</span><br/>
                            <span>PO Code : '.$po_code.'</span>
                        </td>
                    </tr>
                </table>
                ';

        $tbl3 ='<table border="1" width="100%" cellpadding="4" style="font-size:15px;">
                    <thead>
                        <tr>
                            <th width="10%">S.NO</th>
                            <th width="40%" style="text-align:center;">PRODUCT NAME</th>
                            <th width="10%" style="text-align:center;">QTY</th>
                            <th width="20%" style="text-align:center;">COST PRICE</th>
                            <th width="20%" style="text-align:center;">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
        ';

        $count = 1;
        $overallTotal = 0;
        while($row = $db->sql_fetchrow($result)){

            $SQLproduct    = "
            SELECT  po.product_id
                   ,po.price
            FROM  po_product po
            WHERE po.product_id = {$row['product_id']}
            AND po.purchase_order_id < {$row['purchase_order_id']}
            ORDER BY po.product_id DESC
            LIMIT 0,1
            ";

            $resultproduct = $db->sql_query($SQLproduct);
            $rowproduct    = $db->sql_fetchrow($resultproduct);

            $tbl3 = $tbl3.'<tr>
                                <td width="10%">'.$count.'</td>
                                <td width="40%">'.$row['title'].'</td>
                                <td width="10%" style="text-align:center;">'.$row['po_QTY'].'</td>
                                <td width="20%" style="text-align:right;">'.$row['cost_price'].'</td>
                                <td width="20%" style="text-align:right;">'.$row['total_price'].'</td>
                            </tr>
                           ';

            $overallTotal += $row['total_price'];

            $count++;
        }

        $tbl3 = $tbl3.'<tr>
                            <td colspan="4" style="text-align:right;">TOTAL AMOUNT</td>
                            <td style="text-align:right;">'.number_format($overallTotal, 2).'</td>
                        </tr>
                        ';

        $tbl3 = $tbl3.'</tbody></table>';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $download_title = $company['po_code'].'-Purchase-Order'.date('Y-m-d').'.pdf';
        $pdf->Output($download_title, 'I');
    }


    /**
     *
     */
    function getPrintPOtoExcel() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');

        //-----------------------------------------------------------------//
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/tbs_class.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_opentbs.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_html.php');

        $TBS = new clsTinyButStrong;
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);

        $purchase_order_id  = $fn->getReqParam('purchase_order_id');
        $cpSiteIdSession    = $fn->getSessionParam('cp_site_id');
        $template = 'PO-Product-Export.xlsx';
        $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
        $TBS->LoadTemplate($templatePath);
        $rnd_no = mt_rand();
        $file_name = 'Purchase-Order_' . $purchase_order_id . '_' . $rnd_no;
        $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);

        $path = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        $file_name_save = $path . '\\' . $file_name;
        $sourceFilePath = $file_name_save;
        $today =  date('d/m/Y');

        $SQL = "
        SELECT p.*
               ,pop.qty AS po_QTY
               ,pop.qty AS qty_delivered
               ,(pop.qty_requested - pop.qty) AS qty_balance
               ,pop.status
               ,pop.price AS cost_price
               ,po.purchase_order_date
               ,po.po_code
               ,po.purchase_order_id
               ,(SELECT price
                  FROM product_price pp
                  WHERE pp.product_id = pop.product_id
                  AND pp.site_id = {$cpSiteIdSession}) AS product_price
               ,m.title AS supplier_name
               ,m.address1
               ,m.address2
               ,m.address_town
               ,m.address_state
               ,m.address_country
               ,m.billing_address_flat
               ,m.billing_address_street
               ,m.billing_address_town
               ,m.billing_address_state
               ,m.billing_address_country
        FROM purchase_order po
        LEFT JOIN po_product pop ON po.purchase_order_id = pop.purchase_order_id
        LEFT JOIN product p ON p.product_id = pop.product_id
        LEFT JOIN medical_supplier m ON m.medical_supplier_id = po.company_id_supplier
        WHERE po.purchase_order_id = {$purchase_order_id}
        ";
        $result = $db->sql_query($SQL);

        $serialNo       = 1;
        $arr            = array();
        $blkMain        = array();
        $blkProduct     = array();
        $blkQty         = array();
        $qty_delivered  = array();
        $blkSerialNo    = array();
        $qty_balance    = array();
        $blkItemCode    = array();
        $blkLastQPrice  = array();
        $blkcost_price  = array();
        $blktotal_price = array();
        $selling_price  = 0;
        $overallTotal   = 0;
        while ($row = $db->sql_fetchrow($result)) {
            //repeating rows of product values
            $arr1 = array('product_title' => $row['title']);
            $blkProduct[] = $arr1;

            /*$address1        = '';
            $address2        = '';
            $address_town    = '';
            $address_state   = '';
            $address_country = '';*/

            $address1        = $row['address1'];
            $address2        = $row['address2'];
            $address_town    = $row['address_town'];
            $address_state   = $row['address_state'];
            $address_country = $row['address_country'];

            if($address1 == '' ||
               $address2 == '' ||
               $address_town == '' ||
               $address_state == '' ||
               $address_country == ''){
                $address1        = $row['billing_address_flat'];
                $address2        = $row['billing_address_street'];
                $address_town    = $row['billing_address_town'];
                $address_state   = $row['billing_address_state'];
                $address_country = $row['billing_address_country'];
            }


            $arr2 = array('qty' => $row['po_QTY']);
            $blkQty[] = $arr2;

            $arr3 = array('serial_no' => $serialNo);
            $blkSerialNo[] = $arr3;

            $arr4 = array('qty_delivered' => $row['qty_delivered']);
            $qty_delivered[] = $arr4;

            $arr5 = array('qty_balance' => $row['qty_balance']);
            $qty_balance[] = $arr5;

            $arr6 = array('item_code' => 'PROD - '.$row['product_code']);
            $blkItemCode[] = $arr6;

            $SQLproduct    = "
            SELECT  po.product_id
                   ,po.price
            FROM  po_product po
            WHERE po.product_id = {$row['product_id']}
            AND po.purchase_order_id < {$row['purchase_order_id']}
            ORDER BY po.product_id DESC
            LIMIT 0,1
            ";

            $resultproduct = $db->sql_query($SQLproduct);
            $rowproduct    = $db->sql_fetchrow($resultproduct);

            $arr7 = array('last_price' => $rowproduct['price']);
            $blkLastQPrice[] = $arr7;

            $arr8 = array('cost_price' => $row['cost_price']);
            $blkcost_price[] = $arr8;

            $arr9 = array('total_price' => number_format(($row['po_QTY'] * $row['cost_price']), 2));
            $blktotal_price[] = $arr9;

            $overallTotal += $row['po_QTY'] * $row['cost_price'];

            $po_date   = $fn->getCPDate($row['purchase_order_date'], 'd-m-Y');

            $recCountry['name'] = '';
            if($address_country != ''){
                $recCountry = $fn->getRecordRowByID('geo_country', 'country_code', "'{$address_country}'");
            }

            $arr['po_code']         = $row['po_code'];
            $arr['supplier_name']   = $row['supplier_name'];
            $arr['address_flat']    = $address1;
            $arr['address_street']  = $address2;
            $arr['address_town']    = $address_town;
            $arr['address_state']   = $address_state;
            $arr['address_country'] = $recCountry['name'];
            $arr['po_date']         = $po_date;
            $blkMain[] = $arr;

            $serialNo++;
        }

        $arr10 = array('overall_Total' => number_format($overallTotal, 2));
        $blkoverallTotal[] = $arr10;

        $TBS->MergeBlock('blkMain', $blkMain);
        $TBS->MergeBlock('blkProduct', $blkProduct);
        $TBS->MergeBlock('blkQty', $blkQty);
        $TBS->MergeBlock('blkcost_price', $blkcost_price);
        $TBS->MergeBlock('qty_delivered', $qty_delivered);
        $TBS->MergeBlock('blkSerialNo', $blkSerialNo);
        $TBS->MergeBlock('qty_balance', $qty_balance);
        $TBS->MergeBlock('blkItemCode', $blkItemCode);
        $TBS->MergeBlock('blkLastQPrice', $blkLastQPrice);
        $TBS->MergeBlock('blktotal_price', $blktotal_price);
        $TBS->MergeBlock('blkoverallTotal', $blkoverallTotal);
        $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
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
              ,CONCAT_WS(' :: ', CONCAT('PROD-',p.product_code), p.title
              ,
                (SELECT pp.price 
                 FROM product_price pp
                 WHERE pp.product_id = p.product_id
                 AND site_id = {$cpSiteIdSession}
                )
              ,
                (SELECT SUM(pp.qty) FROM po_product pp
                 LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                 WHERE pp.product_id = p.product_id
                 AND po.site_id = {$cpSiteIdSession}
                 GROUP BY pp.product_id
                )
                -
                if(
                    (SELECT SUM(invItem.qty) FROM invoice_item invItem
                     LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
                     LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
                     WHERE record_id = p.product_id
                     AND o.site_id = {$cpSiteIdSession}
                    )
                    ,(SELECT SUM(invItem.qty) FROM invoice_item invItem
                      LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
                      LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
                      WHERE record_id = p.product_id
                      AND o.site_id = {$cpSiteIdSession}
                    )
                    ,''
                )
                +
                if(
                    (SELECT SUM(srh.qty_return) FROM sales_return_history srh
                     LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                     LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                     WHERE ini.record_id = p.product_id
                     AND inv.site_id = {$cpSiteIdSession}
                    )
                    ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
                      LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                      LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                      WHERE ini.record_id = p.product_id
                      AND inv.site_id = {$cpSiteIdSession}
                    )
                    ,''
                )
                -
                if(
                    (SELECT SUM(pp.damaged_qty) FROM po_product pp
                      LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                      WHERE pp.product_id = p.product_id AND po.site_id = {$cpSiteIdSession}
                     )
                    ,(SELECT SUM(pp.damaged_qty) FROM po_product pp
                      LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                      WHERE pp.product_id = p.product_id AND po.site_id = {$cpSiteIdSession}
                     )
                    ,''
                )
                -
                if(
                    (SELECT SUM(sh.qty) AS Transfer_qty
                     FROM stock_transfer st
                     LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
                     WHERE sh.product_id = p.product_id
                     AND st.from_location = {$cpSiteIdSession}
                    )
                    ,(SELECT  SUM(sh.qty) AS Transfer_qty
                      FROM stock_transfer st
                      LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
                      WHERE sh.product_id = p.product_id
                      AND st.from_location = {$cpSiteIdSession}
                    )
                    ,''
                )
                +
                if(
                    (SELECT  SUM(sh.qty) AS Transfer_qty_to
                     FROM stock_transfer st
                     LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
                     WHERE sh.product_id = p.product_id
                     AND st.to_location = {$cpSiteIdSession}
                    )
                    ,(SELECT  SUM(sh.qty) AS Transfer_qty_to
                      FROM stock_transfer st
                      LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
                      WHERE sh.product_id = p.product_id
                      AND st.to_location = {$cpSiteIdSession}
                    )
                    ,''
                )

              ) AS label
        FROM product p
        WHERE (p.title LIKE '%{$productTitle}%'
        OR p.item_code LIKE '%{$productTitle}%')
        ORDER BY p.title
        ";

        /*$SQL = "
        SELECT p.title AS value
              ,p.title AS label
              ,p.product_id AS id
        FROM product p
        WHERE (p.title LIKE '%{$productTitle}%'
        OR p.item_code LIKE '%{$productTitle}%')
        AND p.published = 1
        ORDER BY p.title
        ";*/
        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
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
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $poProductId  = $fn->getReqParam('poProductChecked', array());

        foreach($poProductId AS $po_product_id){
            if($po_product_id != ''){
                $SQLPOProduct = "
                UPDATE po_product SET qty = qty_requested, status = 'closed'
                WHERE po_product_id = '{$po_product_id}'
                ";
                $resultPOProduct  = $db->sql_query($SQLPOProduct);

                $SQLPoProd = "
                SELECT product_id
                FROM po_product
                WHERE po_product_id = '{$po_product_id}'
                ";
                $resultPoProd  = $db->sql_query($SQLPoProd);
                $PoProd = $db->sql_fetchrow($resultPoProd);

                $SQLStockTransfer = "
                SELECT  st.from_location
                        ,st.to_location
                        ,sh.product_id
                        ,SUM(sh.qty) AS Transfer_qty
                FROM stock_transfer st
                LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
                WHERE sh.product_id = {$PoProd['product_id']} AND st.from_location = {$cpSiteIdSession}";

                $resultStockTransfer = $db->sql_query($SQLStockTransfer);
                $rowStockTransfer = $db->sql_fetchrow($resultStockTransfer);

                $SQLStockTransferto = "
                SELECT  st.from_location
                        ,st.to_location
                        ,sh.product_id
                        ,SUM(sh.qty) AS Transfer_qty_to
                FROM stock_transfer st
                LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
                WHERE sh.product_id = {$PoProd['product_id']} AND st.to_location = {$cpSiteIdSession}";

                $resultStockTransferto = $db->sql_query($SQLStockTransferto);
                $rowStockTransferto = $db->sql_fetchrow($resultStockTransferto);

                $SQLOthersite = "
                SELECT
                    (SELECT SUM(qty) FROM po_product pp
                     LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                     WHERE pp.product_id = {$PoProd['product_id']} AND po.site_id = {$cpSiteIdSession}) as product_qty_purchased

                   ,(SELECT SUM(invItem.qty) FROM invoice_item invItem
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
                    LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
                    WHERE record_id = {$PoProd['product_id']}
                      AND o.site_id = {$cpSiteIdSession}
                    ) as product_qty_sold_from_quote

                    ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
                    LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                    WHERE ini.record_id = {$PoProd['product_id']}
                    AND inv.site_id = {$cpSiteIdSession}
                    ) as sales_return_qty

                    ,(SELECT SUM(pp.damaged_qty) FROM po_product pp
                      LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                      WHERE pp.product_id = {$PoProd['product_id']} AND po.site_id = {$cpSiteIdSession}
                     ) as damaged_qty
                ";
                $resultothersite = $db->sql_query($SQLOthersite);
                $rowothersite = $db->sql_fetchrow($resultothersite);

                $SqlExpenseProduct = "
                SELECT SUM(ep.qty) AS qty
                FROM expense_product ep
                LEFT JOIN expense e ON (e.expense_id = ep.expense_id)
                WHERE ep.product_id = {$PoProd['product_id']}
                AND ep.status = 'Added'
                AND e.site_id = {$cpSiteIdSession}
                AND ep.stock_deducted = 1
                ";
                $resultExpenseProduct = $db->sql_query($SqlExpenseProduct);
                $rowExpenseProduct    = $db->sql_fetchrow($resultExpenseProduct);

                $stock = $rowothersite['product_qty_purchased'] - $rowothersite['product_qty_sold_from_quote'] + $rowothersite['sales_return_qty'] - $rowothersite['damaged_qty'] - $rowStockTransfer['Transfer_qty'] + $rowStockTransferto['Transfer_qty_to'] - $rowExpenseProduct['qty'];

                $SQLUpdateProduct = "
                UPDATE product SET qty_in_stock{$cpSiteIdSession} = {$stock}
                WHERE product_id = '{$PoProd['product_id']}'
                ";
                $resultUpdateProduct  = $db->sql_query($SQLUpdateProduct);

                $SQLUpdateInventory = "
                UPDATE inventory SET actual_stock{$cpSiteIdSession} = {$stock}
                WHERE product_id = '{$PoProd['product_id']}'
                ";
                $resultUpdateInventory  = $db->sql_query($SQLUpdateInventory);
            }
        }
    }

    /**
     *
     */
    function getDeletePoProduct() {
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');

        $po_product_id     = $fn->getReqParam('po_product_id');
        $purchase_order_id = $fn->getReqParam('purchase_order_id');

        $SQLDeletePOProduct = "
        DELETE FROM po_product
        WHERE po_product_id = '{$po_product_id}'
        AND purchase_order_id = '{$purchase_order_id}'
        ";
        $resultDeletePOProduct  = $db->sql_query($SQLDeletePOProduct);
    }

}
