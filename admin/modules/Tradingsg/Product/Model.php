<?
class CPL_Admin_Modules_Tradingsg_Product_Model extends CP_Admin_Modules_Tradingsg_Product_Model
{
    /**
     *
     */
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $appendSQL = '';
        $SQL = "
        SELECT DISTINCT p.product_id
              ,p.category_id
              ,p.sub_category_id
              ,p.title
              ,p.description
              ,p.qty_in_stock
              ,p.price
              ,p.published
              ,p.creation_date
              ,p.modification_date
              ,p.description_short
              ,p.general_quotation
              ,p.unit
              ,p.product_group_id
              ,p.item_code
              ,p.modified_by
              ,p.created_by
              ,p.part_number
              ,p.price_from_supplier
              ,p.latest
              ,p.section_id
              ,p.hsn
              ,p.gst
              ,p.mrp
              ,p.tag_no
              ,p.product_type
              ,p.bar_code
              ,p.product_code
              ,p.discount_type
              ,p.discount_percentage
              ,p.discount_amount
              ,p.discount_from_date
              ,p.discount_to_date
              ,s.title AS section_title
              ,c.title AS category_title
              ,sc.title AS sub_category_title
              ,co.company_name
              ,co.supplier_id
              ,(
               SELECT GROUP_CONCAT(co.company_name ORDER BY co.company_name SEPARATOR ', ')
               FROM supplier co, product_company pc
               WHERE co.supplier_id = pc.company_id
                 AND pc.product_id = p.product_id
              ) AS company_records
              {$appendSQL}
        FROM product p
        LEFT JOIN (category c)      ON (p.category_id      = c.category_id)
        LEFT JOIN (section s)      ON (p.section_id      = s.section_id)
        LEFT JOIN (sub_category sc) ON (p.sub_category_id  = sc.sub_category_id)
        LEFT JOIN (product_company pc) ON (pc.product_id = p.product_id)
        LEFT JOIN (supplier co) ON (co.supplier_id = pc.company_id)
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
        $searchVar->mainTableAlias = 'p';

        $product_id   = $fn->getReqParam('product_id');
        $company_id   = $fn->getReqParam('company_id');
        $supplier_id   = $fn->getReqParam('supplier_id');
        $category     = $fn->getReqParam('category');
        $sub_category = $fn->getReqParam('sub_category');
        $special_search  = $fn->getReqParam('special_search');
        $general_quotation   = $fn->getReqParam('general_quotation');
        $product_group_id = $fn->getReqParam('product_group_id');
        $product_type         = $fn->getReqParam('product_type');


        if (CP_SCOPE == 'www') {
            $searchVar->sqlSearchVar[] = "p.published = 1";
        }

        if ($product_id != '') {
            $searchVar->sqlSearchVar[] = "p.product_id = {$product_id}";

        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "p.product_id = {$tv['record_id']}";

        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'p.product_id');

            if($tv['linkName'] == 'product#product'){
                $searchVar->sqlSearchVar[] = "p.product_id != {$tv['linkMasterTableID']}";
            }

            if ($product_group_id != "") {
                $searchVar->sqlSearchVar[] = "p.product_group_id = '{$product_group_id}'";
            }

            if ($tv['category_id'] != '' ) {
                $searchVar->sqlSearchVar[] = "p.category_id = '{$tv['category_id']}'";
            }

            if ($tv['subRoom'] != '' ) {
                $searchVar->sqlSearchVar[] = "p.category_id = '{$tv['subRoom']}'";
            }

            if ($tv['sub_category_id'] != '' ) {
                $searchVar->sqlSearchVar[] = "p.sub_category_id = '{$tv['sub_category_id']}'";
            }

            if ($company_id != '' ) {
                $searchVar->sqlSearchVar[] = "p.company_id = '{$tv['company_id']}'";
            }

            if ($supplier_id != '' ) {
                $searchVar->sqlSearchVar[] = "pc.company_id = '{$supplier_id}'";
            }

            if ($tv['subCat'] != '' ) {
                $searchVar->sqlSearchVar[] = "p.sub_category_id = '{$tv['subCat']}'";
            }

            if ($product_type != '' ) {
                $searchVar->sqlSearchVar[] = "p.product_type = '{$product_type}'";
            }

            if ($special_search != '' ) {
                if ($special_search == 'Published') {
                    $searchVar->sqlSearchVar[] = "p.published = 1";
                }

                if ($special_search == 'Not-Published') {
                    $searchVar->sqlSearchVar[] = "p.published = 0 OR p.published IS NULL OR p.published = ''";
                }

                if ($special_search == 'Latest' ) {
                    $searchVar->sqlSearchVar[] = "p.latest = 1";
                }

                if ($special_search == 'Flag' ) {
                    $searchVar->sqlSearchVar[] = "p.flag = 1";
                }
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                p.title LIKE '%{$tv['keyword']}%'
                OR p.description  LIKE '%{$tv['keyword']}%'
                OR p.item_code  LIKE '%{$tv['keyword']}%'
                )";
            }

        }
        $searchVar->sortOrder = "p.product_id DESC";

    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn       = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $db       = Zend_Registry::get('db');

        $product_id  = $fn->getPostParam('product_id');
        $supplier_id = $fn->getPostParam('supplier_id');
        $discount_percentage = $fn->getPostParam('discount_percentage');
        $discount_amount     = $fn->getPostParam('discount_amount');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();

        if($discount_percentage == ""){
            $fa['discount_percentage'] = 0;
            $discount_percentage = 0;
        }
        if($discount_amount == ""){
            $fa['discount_amount'] = 0;
        }

        if($fa['discount_type'] == "%"){
            if($discount_percentage == ''){
                $discount_percentage = 0;
                
                if($discount_amount > 0){
                    $discount_percentage = $discount_amount;
                }
            }
            else{
                if($discount_amount > 0){
                    $discount_percentage = $discount_amount;
                }
            }

            $fa['discount_percentage'] = $discount_percentage;
            $fa['discount_amount']     = 0;
        }

        if($fa['discount_type'] == "Value"){
            if($discount_amount == ''){
                $discount_amount = 0;

                if($discount_percentage > 0){
                    $discount_amount = $discount_percentage;
                }
            }else{
                if($discount_percentage > 0){
                    $discount_amount = $discount_percentage;
                }
            }
            
            $fa['discount_amount']     = $discount_amount;
            $fa['discount_percentage'] = 0;
        }

        $fa2 = array();
        $fa2['company_id']    = $supplier_id;
        $whereCondition       = "WHERE product_id = {$product_id}";
        $SQLUpdateSupplier    = $dbUtil->getUpdateSQLStringFromArray($fa2, "product_company", $whereCondition);
        $resultUpdateSupplier = $db->sql_query($SQLUpdateSupplier);

        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'category_id');
        $fa = $fn->addToFieldsArray($fa, 'product_group_id');
        $fa = $fn->addToFieldsArray($fa, 'sub_category_id');
        $fa = $fn->addToFieldsArray($fa, 'price');
        $fa = $fn->addToFieldsArray($fa, 'unit');

        $fa = $fn->addToFieldsArray($fa, 'title', '', true);
        $fa = $fn->addToFieldsArray($fa, 'description', '', true);
        $fa = $fn->addToFieldsArray($fa, 'description2', '', true);
        $fa = $fn->addToFieldsArray($fa, 'description_short', '', true);
        $fa = $fn->addToFieldsArray($fa, 'weight_grams');
        $fa = $fn->addToFieldsArray($fa, 'embed_code');
        $fa = $fn->addToFieldsArray($fa, 'general_quotation');
        $fa = $fn->addToFieldsArray($fa, 'product_group_id');

        if(isset($_POST['published'])){
            $fa = $fn->addToFieldsArray($fa, 'published');
        }

        $fa = $fn->addToFieldsArray($fa, 'qty_in_stock');
        $fa = $fn->addToFieldsArray($fa, 'part_number');
        $fa = $fn->addToFieldsArray($fa, 'price_from_supplier');
        $fa = $fn->addToFieldsArray($fa, 'item_code');
        $fa = $fn->addToFieldsArray($fa, 'member_only');
        $fa = $fn->addToFieldsArray($fa, 'latest');
        $fa = $fn->addToFieldsArray($fa, 'hsn');
        $fa = $fn->addToFieldsArray($fa, 'product_type');
        $fa = $fn->addToFieldsArray($fa, 'bar_code');
        $fa = $fn->addToFieldsArray($fa, 'tag_no');
        $fa = $fn->addToFieldsArray($fa, 'discount_type');
        $fa = $fn->addToFieldsArray($fa, 'discount_percentage');
        $fa = $fn->addToFieldsArray($fa, 'discount_amount');
        $fa = $fn->addToFieldsArray($fa, 'discount_from_date');
        $fa = $fn->addToFieldsArray($fa, 'discount_to_date');

        return $fa;
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
    function getImportData(){
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');
        set_time_limit(50000);

        $fa = array(
              'item_code'        => $phpExcel->getImportFldObj('ITEM CODE')
             ,'title'            => $phpExcel->getImportFldObj('PRODUCT')
             ,'product_code'     => $phpExcel->getImportFldObj('Ref CODE')
             ,'hsn'     => $phpExcel->getImportFldObj('HSN CODE')
             ,'gst'     => $phpExcel->getImportFldObj('GST%')
             ,'unit'             => $phpExcel->getImportFldObj('UOM')
             ,'price'            => $phpExcel->getImportFldObj('Price')
        );

        $fa['published']['defaultValue'] = 1;
        //$fa['product_group']['refOnly'] = true;

        /****************************************/
        $config = array(
             'module'              => 'tradingsg_product'
            ,'matchFieldArr'       => array('item_code')
            ,'fldsArr'             => $fa
            //,'callbackAfterInsert' => 'importDataRowCallback'
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     */
    function importDataRowCallback($product_id, $fa) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $company_id = 98;

        $fa1 = array();
        $fa1['item_code'] = $this->getUpdateProductCode();

        $whereCondition = "
        WHERE product_id = {$product_id}
        ";
        $SQLProduct = $dbUtil->getUpdateSQLStringFromArray($fa1, 'product', $whereCondition);
        $db->sql_query($SQLProduct);

        $recCount = $fn->getRecordCount('product_company', "company_id = '{$company_id}' AND product_id = '{$product_id}'");
        if (is_numeric ($company_id) && $recCount == 0) {
            $fa2 = array();
            $fa2['company_id'] = $company_id;
            $fa2['product_id']  = $product_id;
            $fa2 = $fn->addCreationDetailsToFieldsArray($fa2, 'product_company');

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa2, 'product_company');
            $result = $db->sql_query($SQL);
        }

        /*if(!is_array($rec) || count($rec) == 0){
            $text .= "
            INSERT INTO product (part_number,title,unit,price_from_supplier,price,product_group_id,published)
            VALUES ('{$fa['part_number']}','{$fa['title']}','{$fa['unit']}',{$fa['price_from_supplier']},{$fa['price']},{$fa['product_group_id']},{$fa['published']})
            ";
            $template = 'engex_import_log.txt';
            $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
            $templatefile = fopen("{$templatePath}","w");
            fwrite($templatefile, $text);
        } else {
            $text .= "
            UPDATE product SET price_from_supplier='{$fa['price_from_supplier']}', price='{$fa['price']}'
            WHERE part_number = '{$fa['part_number']}'
            ";
            $template = 'engex_import_log.txt';
            $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
            $templatefile = fopen("{$templatePath}","w");
            fwrite($templatefile, $text);
        }*/

    }

    /**
     *
     */
    function getQuickAddSubmit() {
        checkLoggedIn();
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $validate = Zend_Registry::get('validate');

        /*if (!$this->getQuickAddSubmitValidate()){
            return $validate->getErrorMessageXML();
        }*/

        $unit_arr 				= $fn->getPostParam('unit', array());
        $part_number_arr		= $fn->getPostParam('part_number', array());
        $hsn_arr        = $fn->getPostParam('hsn', array());
        $product_title_arr 		= $fn->getPostParam('title', array());
        $product_group_id_arr 	= $fn->getPostParam('product_group_id', array());
        $price_arr				= $fn->getPostParam('price', array());
        $price_from_supplier_arr= $fn->getPostParam('price_from_supplier', array());
        $supplier_id_arr        = $fn->getPostParam('company_id', array());

        $count = count($product_title_arr);
        for ($i= 0; $i < $count; $i++){

            $unit                = $unit_arr[$i];
            $part_number         = $part_number_arr[$i];
            $hsn                 = $hsn_arr[$i];
            $product_title       = $product_title_arr[$i];
            $product_group_id    = $product_group_id_arr[$i];
            $price               = $price_arr[$i];
            $price_from_supplier = $price_from_supplier_arr[$i];

            $fa = array();
            $fa['unit'] 			= $unit;
            $fa['title']         	= $product_title;
            $fa['part_number'] 		= $part_number;
            $fa['hsn']              = $hsn;
    	    $fa['product_group_id'] = $product_group_id;
            $fa['price'] 		    = $price;
    	    $fa['price_from_supplier'] = $price_from_supplier;
            $fa['item_code']        = $this->getUpdateProductCode();
    	    $fa['published']        = 1;
            $fa['creation_date']    = date('Y-m-d H:i:s');

            $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'product');
            $result = $db->sql_query($insert);
            $product_id = $db->sql_nextid();

            $supplier_id = $supplier_id_arr[$i];

            if($supplier_id != ''){
                $fa1 = array();
                $fa1['company_id'] 		= $supplier_id;
                $fa1['product_id'] 		= $product_id;
                $fa1['creation_date']   = date('Y-m-d H:i:s');

                $insert = $dbUtil->getInsertSQLStringFromArray($fa1, 'product_company');
                $result = $db->sql_query($insert);
                $id     = $db->sql_nextid();
            }
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getQuickAddSubmitValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');

        //==================================================================//
        $validate->resetErrorArray();
        $validate->validateData('part_number', $ln->gd('Please enter the part number'));

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
    /**
     *
     */
    function getExportData($dataArray){
        $db = Zend_Registry::get('db');
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'item_code'  => $phpExcel->getFldObj('ITEM CODE')
             ,'title'      => $phpExcel->getFldObj('PRODUCT')
             ,'product_code'   => $phpExcel->getFldObj('Ref CODE')
             ,'hsn'   => $phpExcel->getFldObj('HSN CODE')
             ,'gst' => $phpExcel->getFldObj('GST%')
             ,'price'  => $phpExcel->getFldObj('Price')
             ,'company_name'    => $phpExcel->getFldObj('Supplier')
             ,'section_title'    => $phpExcel->getFldObj('Category')
             ,'description'    => $phpExcel->getFldObj('Description')
        );

        $config = array(
             'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }
    /**
     *
     */
    function getProductPriceValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $product_id  = $fn->getReqParam('product_id');

        $validate->resetErrorArray();

        $validate->validateData('price', 'Please enter the price');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }


    /**
     *
     */
    function getAddProductPriceSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        if (!$this->getProductPriceValidate()){
            return $validate->getErrorMessageXML();
        }

        $product_id  = $fn->getReqParam('product_id');
        $price       = $fn->getPostParam('price');
        $agent_commission = $fn->getPostParam('agent_commission');
        $product_weight   = $fn->getPostParam('product_weight');
        $gst   = $fn->getPostParam('gst');

        $fa = array();
        $fa['product_id']       = $product_id;
        $fa['price']            = $price;
        $fa['product_weight']   = $product_weight;
        $fa['gst']              = $gst;
        $fa['creation_date']    = date("Y-m-d H:i:s");
        $fa['created_by']       = $fn->getSessionParam('userName');

        $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'product_price');
        $result = $db->sql_query($insert);

        $fa1 = array();
        $fa1['price']            = $price;
        $fa1['gst']              = $gst;
        $fa1['product_weight']   = $product_weight;

        $whereCondition = "WHERE product_id = {$product_id}";
        $SQL    = $dbUtil->getUpdateSQLStringFromArray($fa1, "product", $whereCondition);
        $result = $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();
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

       /* if ($tv['lang'] == 'eng') {
            $validate->validateData('title', 'Please enter the title');
            $validate->validateData('product_group_id', 'Please enter the Department');
            $validate->validateData('category_id', 'Please enter the Category');
            $validate->validateData('sub_category_id', 'Please enter the Sub Category');
        } */

        //$validate->validateData('price', 'Please enter the price');
        $validate->validateData('unit', 'Please select the unit');

        if ($cpCfg['m.tradingsg.product.displayTradingmassProductNameValidate'] == 1){
            "{$validate->validateData('title', 'Please enter the title')}
             ";

        } else {

            "{$validate->validateData('title', 'Please enter the title')}
             {$validate->validateData('product_group_id', 'Please enter the Department')}
             {$validate->validateData('category_id', 'Please enter the Category')}
             {$validate->validateData('sub_category_id', 'Please enter the Sub Category')}
                  ";
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
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $SQL = "SELECT max(item_code) AS item_code FROM product";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $fa = $this->getFields();
        $fa['published'] = 1;
        $fa['item_code'] = $this->getUpdateProductCode();
        $fa['product_type'] = 'Purchasing and Selling';
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }
}