<?
class CP_Admin_Modules_Labsg_PurchaseOrder_Model extends CP_Common_Lib_ModuleModelAbstract
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
                    OR com.company_name LIKE '%{$tv['keyword']}%'
                    OR q.title LIKE '%{$tv['keyword']}%'
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

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }


        $fa = $this->getFields();
        $fa['purchase_order_date'] = date('Y-m-d');
        $fa['status'] = 'new';
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
        $product_arr        = $fn->getPostParam('product_id', array());
        $price_arr          = $fn->getPostParam('price', array());
        $qty_arr            = $fn->getPostParam('qty', array());

        $validate->resetErrorArray();

        $filterArray3 = array_filter($qty_arr);
        if (count($filterArray3) == 0){
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please Enter Qty";
        }

        $filterArray2 = array_filter($price_arr);
        if (count($filterArray2) == 0){
            $validate->errorArray['error_box']['name'] = "error_box1";
            $validate->errorArray['error_box']['msg']  = "Please Enter Price";
        }


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
        $product_arr        = $fn->getPostParam('product_id', array());
        $price_arr          = $fn->getPostParam('price', array());
        $qty_arr            = $fn->getPostParam('qty', array());

        $count = count($price_arr);
        for ($i= 0; $i < $count; $i++) {
            $product_id         = $product_arr[$i];
            $price              = $price_arr[$i];
            $qty                = $qty_arr[$i];

            if ($price) {
                $fa = array();

                $fa['product_id']           = $product_id;
                $fa['price']                = $price;
                $fa['qty']                  = $qty;
                $fa['status']               = 'New';
                $fa['purchase_order_id']    = $purchase_order_id;
                $fa['supplier_id']          = $supplier_id;
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
        $fa['qty']              = $qty;
        $fa['qty_delivered']    = $qty_delivered;
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
        $po_productRec  = $fn->getRecordRowByID('po_product', 'po_product_id', $po_product_id);

        if($qty_delivered > $po_productRec['qty']){
            $validate->errorArray['qty_delivered']['name'] = "qty_delivered";
            $validate->errorArray['qty_delivered']['msg']  = "Please enter qty less than or equal to {$po_productRec['qty']}.";
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

        $po_product_id   = $fn->getPostParam('po_product_id');
        $qty_delivered   = $fn->getPostParam('qty_delivered');
        $status          = $fn->getPostParam('status');

        $fa = array();
        $fa['qty_delivered']     = $qty_delivered;
        $fa['status']            = $status;
        $fa['modification_date'] = date("Y-m-d H:i:s");
        $fa['modified_by']       = $fn->getSessionParam('userName');

        $whereCondition = "WHERE po_product_id = {$po_product_id}";
        $SQL    = $dbUtil->getUpdateSQLStringFromArray($fa, "po_product", $whereCondition);
        $result = $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getLabsgPurchaseOrderLabsgProductLinkSQL($id) {

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

        $fa['company_id_supplier']['specialType'] = 'fetchIdFromRefModule';
        $fa['company_id_supplier']['exp']['refModule'] = 'tradingsg_supplier';

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
        $supplier_id = $fa['company_id_supplier'];
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
            $po_code = 'PO-'.$company['po_code'];
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
                            <th width="40%">PRODUCT NAME</th>
                            <th width="20%" style="text-align:center;">QTY</th>
                            <th width="30%" style="text-align:right;">LAST QUOTED PRICE</th>
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
                                <td width="40%">'.$row['title'].'</td>
                                <td width="20%" style="text-align:center;">'.$row['po_QTY'].'</td>
                                <td width="30%" style="text-align:right;">'.$rowproduct['price'].'</td>
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

            $arr6 = array('item_code' => $row['item_code']);
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

        $TBS->MergeBlock('blkMain', $blkMain);
        $TBS->MergeBlock('blkProduct', $blkProduct);
        $TBS->MergeBlock('blkQty', $blkQty);
        $TBS->MergeBlock('blkcost_price', $blkcost_price);
        $TBS->MergeBlock('qty_delivered', $qty_delivered);
        $TBS->MergeBlock('blkSerialNo', $blkSerialNo);
        $TBS->MergeBlock('qty_balance', $qty_balance);
        $TBS->MergeBlock('blkItemCode', $blkItemCode);
        $TBS->MergeBlock('blkLastQPrice', $blkLastQPrice);
        $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
    }

}
