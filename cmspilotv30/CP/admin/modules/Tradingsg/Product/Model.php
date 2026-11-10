<?
class CP_Admin_Modules_Tradingsg_Product_Model extends CP_Common_Lib_ModuleModelAbstract
{

    /**
     *
     */
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $appendSQL = '';
        if ($cpCfg['m.ecommerce.product.hasProductItem'] == 1){
            $appendSQL = "
              ,(SELECT SUM(pi.stock)
                FROM product_item pi
                WHERE pi.product_id = p.product_id) AS total_stock
            ";
        }

        $SQL = "
        SELECT p.*
              ,c.title AS category_title
              ,sc.title AS sub_category_title
              ,co.company_id
              ,pg.title AS product_group_title
              ,pg.product_group_id As productGroupId
              ,(
               SELECT GROUP_CONCAT(co.company_name ORDER BY co.company_name SEPARATOR ', ')
               FROM company co, product_company pc
               WHERE co.company_id = pc.company_id
                 AND pc.product_id = p.product_id
              ) AS company_records
              {$appendSQL}
        FROM product p
        LEFT JOIN (category c)      ON (p.category_id      = c.category_id)
        LEFT JOIN (sub_category sc) ON (p.sub_category_id  = sc.sub_category_id)
        LEFT JOIN (product_group pg) ON (p.product_group_id = pg.product_group_id)
        LEFT JOIN (product_company pc) ON (pc.product_id = p.product_id)
        LEFT JOIN (company co) ON (co.company_id = pc.company_id)
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

            if ($general_quotation == "Yes") {
                $searchVar->sqlSearchVar[] = "p.general_quotation = 1";
            }
            if ($general_quotation == "No") {
                $searchVar->sqlSearchVar[] = "(p.general_quotation != 1 OR p.general_quotation IS null)";
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

        $validate->validateData('title', 'Please enter the title');

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
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getUpdateProductCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of Product Code */
        $nextProductItemCode = $fn->getSettingsValueByKey("nextProductItemCode");
        //$ProCode = $fn->getSettingsValueByKey('productCodePrefix') . '000' . $nextProductItemCode;

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
            $ProCode = $fn->getSettingsValueByKey('productCodePrefix') . $nextProductItemCode;
        }

        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextProductItemCode'";
        $result = $db->sql_query($SQL);

        return $ProCode;
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

        $validate->validateData('price', 'Please enter the price');
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
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
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
        $fa = $fn->addToFieldsArray($fa, 'item_code');
        $fa = $fn->addToFieldsArray($fa, 'member_only');
        $fa = $fn->addToFieldsArray($fa, 'latest');

        return $fa;
    }


    
    /**
     *
     */
    function getImportData1(){
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');

        $fa = array(
              'product_code'      => $phpExcel->getImportFldObj('Product Code')
             ,'title'             => $phpExcel->getImportFldObj('Title')
             ,'description_short' => $phpExcel->getImportFldObj('Short Description')
             ,'description'       => $phpExcel->getImportFldObj('Description')
             ,'picture'           => $phpExcel->getImportFldObj('Picture Ref')
             ,'published'         => $phpExcel->getImportFldObj('Published')
             ,'category_id'       => $phpExcel->getImportFldObj('Category')
             ,'sub_category_id'   => $phpExcel->getImportFldObj('Sub Category')
        );

        $fa['published']['defaultValue'] = 1;
        $fa['picture']['refOnly'] = true;

        $fa['category_id']['specialType'] = 'category';
        $fa['category_id']['exp'] = array('sectionType' => 'Product');

        $fa['sub_category_id']['specialType'] = 'subCategory';
        $fa['sub_category_id']['exp'] = array(
             'categoryFldKeyInArr' => 'category_id'
        );

        /****************************************/
        $config = array(
             'module'              => 'ecommerce_product'
            ,'matchFieldArr'       => array('product_code')
            ,'mandatoryFldsArr'    => array('product_code')
            ,'fldsArr'             => $fa
            ,'callbackAfterInsert' => 'callbackAfterImportInsert'
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     */
    function callbackAfterImportInsert($product_id, $fa) {
        $media = Zend_Registry::get('media');

        if ($fa['picture'] != ''){
            $sourceFilePath = realpath('../media_import') . "/{$picture}";
            $exp = array(
                 'srcFile' => $sourceFilePath
                ,'actualFileName' => $picture
            );
            $media->model->createMedia('ecommerce_product', 'picture', $product_id, $exp);
        }
    }

    /**
     *
     */
    function getImportData(){
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');

        $fa = array(
              'product_id'       => $phpExcel->getImportFldObj('PRODUCT ID')
             ,'products_group_id' => $phpExcel->getImportFldObj('DEPARTMENT')
             ,'category_id'      => $phpExcel->getImportFldObj('CATEGORY')
             ,'sub_category_id'  => $phpExcel->getImportFldObj('SUB-CATEGORY')
             ,'title'            => $phpExcel->getImportFldObj('PRODUCT NAME')
             ,'price'            => $phpExcel->getImportFldObj('PRICE')
             ,'unit'             => $phpExcel->getImportFldObj('UNIT')
             ,'general_quotation'=> $phpExcel->getImportFldObj('SHOW G.QTN')
             ,'company_id'       => $phpExcel->getImportFldObj('SUPPLIER')
        );
        $fa['company_id']['refOnly'] = true;

        $fa['published']['defaultValue'] = 1;

        /****************************************/
        $config = array(
             'module'              => 'tradingsg_product'
            ,'matchFieldArr'       => array('title')
            ,'fldsArr'             => $fa
            ,'callbackAfterInsert' => 'importDataRowCallback'
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

        $company_id = $fa['company_id'];
        $recCount = $fn->getRecordCount('product_company', "company_id = '{$company_id}' AND product_id = '{$product_id}'");
        if (is_numeric ($company_id) && $recCount == 0) {
            $fa2 = array();
            $fa2['company_id'] = $company_id;
            $fa2['product_id']  = $product_id;
            $fa2 = $fn->addCreationDetailsToFieldsArray($fa2, 'product_company');

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa2, 'product_company');
            $result = $db->sql_query($SQL);
        }
    }

    /**
     *
     */
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');


        $fa = array(
              'product_id'         => $phpExcel->getFldObj('PRODUCT ID')
             ,'productGroupId'     => $phpExcel->getFldObj('DEPARTMENT')
             ,'category_id'        => $phpExcel->getFldObj('CATEGORY')
             ,'sub_category_id'    => $phpExcel->getFldObj('SUB-CATEGORY')
             ,'title'              => $phpExcel->getFldObj('PRODUCT NAME')
             ,'price'              => $phpExcel->getFldObj('PRICE')
             ,'unit'               => $phpExcel->getFldObj('UNIT')
             ,'general_quotation'  => $phpExcel->getFldObj('SHOW G.QTN')
             ,'company_id'         => $phpExcel->getFldObj('SUPPLIER')

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
    function getGenerateBulkVouchers() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');

        $product_id= $fn->getReqParam('id');

        $formAction = "index.php?module=ecommerce_product&_spAction=generateVoucherFormSubmit&showHTML=0";

        $text = "
        <form name='portalForm' id='portalForm' method='post' action='{$formAction}'>
            <fieldset>
                <div class='floatbox'>
                    <div class='float_left'>
                     {$formObj->getTBRow('Number of Records', 'no_of_records')}
                    </div>
                </div>
                <input type='hidden' name='product_id' value='{$product_id}' />
            </fieldset>
        </form>
        ";

        return $text;
    }
    /**
     *
     */
    function getGenerateVoucherFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');

        if (!$this->getGenerateVoucherValidate()){
            return $validate->getErrorMessageXML();
        }

        $product_id    = $fn->getReqParam('product_id');
        $no_of_records = $fn->getReqParam('no_of_records');

        for ($i = 1; $i <= $no_of_records ; $i++){
            $fa = array();
            $rand_no = mt_rand();
            $fa['voucher_no']    = $rand_no;
            $fa['product_id'] = $product_id;
            $id = $fn->addRecord($fa, 'product_voucher');
        }

        return $validate->getSuccessMessageXML();

    }
    /**
     *
     */
    function getGenerateVoucherValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        //==================================================================//
        $validate->resetErrorArray();
        //$validate->validateData("email"       , $ln->gd("cp.form.fld.email.err")      , "email");

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
    /**
     *
     */
    function getPrintVoucher() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');


        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        //include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new FPDF();
        $pdf->SetFont('Arial','B',14);

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        $product_id  = $fn->getReqParam('id');
		$invoice_terms = '';
		$notes  = '';
        $total = '';

		$SQL = "
		SELECT pv.voucher_no
            ,pv.product_id
            ,p.title as product_title
		FROM product_voucher pv
		JOIN product p ON (pv.product_id = p.product_id)
		WHERE pv.product_id = {$product_id}
		ORDER BY pv.product_voucher_id
		";

        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);
		if ($numRows == 0){
            $pdf->SetXY(60,30);
            $pdf->Cell(50, 20, "Please set the values for your Voucher and print the PDF");
			$pdf->Output();
			return;
		}
        $count = 0;
        $total = 0;
        $rows = "";

        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){
                $pdf->Image('images/sgdealon_banner.jpg',0,0,210, 30);
                $pdf->SetY(32);
                $product_title = "Please find the Voucher Codes for the Product : " ;
                //$pdf->WordWrap($product_title, 200);
                $pdf->Write(5, $product_title);
                $pdf->Ln(8);
                $pdf->drawTextBox($row['product_title'], 195, 32, 'L', 'T', 0);
                $pdf->Ln(10);
            }
             //Table Content
            $voucher_no = $row['voucher_no'];
            $count++;
            //$pdf->Write(5, "Voucher No " . $count . ': ' . $voucher_no);
            $pdf->Cell(60, 5, "Voucher No " . $count . ': ' . $voucher_no, 1);
            if ($count % 3){
            }
            else{
                $pdf->Ln(10);
            }
        }
        //Final Values
        $pdf->Output();
    }

    /**
     *
     */
    function getEcommerceProductEcommerceProductLinkSQL($id) {
        $SQL = "
        SELECT rp.related_product_id
              ,p.product_id
              ,p.title
              ,c.title AS category_title
        FROM related_product rp
        JOIN product p  ON (p.product_id = rp.product_id_rel)
        JOIN category c ON (c.category_id = p.category_id)
        WHERE rp.product_id = {$id}
        ";

        return $SQL;
    }

    /**
     *
     */
    function getEcommerceProductEcommerceCountryLinkSQL($id) {
        $SQL = "
        SELECT pc.product_country_id
              ,c.country_name
              ,pc.price
        FROM product_country pc
        JOIN country c ON (c.country_id = pc.country_id)
        WHERE pc.product_id = {$id}
        ";

        return $SQL;
    }

    /**
     *
     */
    function getEcommerceProductEcommerceCompanyLinkSQL($id) {

        $SQL = "
        SELECT pc.product_company_id
              ,c.company_name
        FROM product_company pc
        LEFT JOIN company c ON (c.company_id = pc.company_id)
        WHERE pc.company_id = {$id}
        ORDER BY c.company_name DESC
        ";

        return $SQL;
    }

    /**
     *
     */
    function getEcommerceProductEcommerceProductItemLinkSQL($id) {
        $formObj = Zend_Registry::get('formObj');
        $colorFld = ($formObj->mode == 'edit') ? 'pi.color_id' : 'c.title AS color';

        $SQL = "
        SELECT pi.product_item_id
              ,pi.sku_no
              ,{$colorFld}
              ,pi.size
              ,pi.stock
              ,pi.sort_order
        FROM product_item pi
        LEFT JOIN color c ON (c.color_id = pi.color_id)
        WHERE pi.product_id = {$id}
        ";

        return $SQL;
    }

    /**
     *
     */
    function getProductSQL() {
        $SQL = '
        SELECT p.product_id
              ,p.title
        FROM product p
        ORDER BY p.title
        ';
        return $SQL;
    }

    /**
     *
     */
    function getProductContentHistoryLinkSQL($id) {
        $ln = Zend_Registry::get('ln');

        $lnPfx = $ln->getFieldPrefix();

        $SQL = "
        SELECT ch.content_history_id
              ,IF(ch.{$lnPfx}title != '', ch.{$lnPfx}title, ch.title) AS title
        FROM content_history ch
        WHERE ch.record_id = {$id}
        AND ch.room_name = 'product'
        ";

        return $SQL;
    }

    /**
     *
     */
    function getEcommerceProductEcommerceProductVoucherLinkSQL($id) {
        return "
        SELECT product_voucher_id
              ,voucher_no
              ,order_id
        FROM product_voucher
        WHERE product_id = {$id}
        ";
    }

    /**
     *
     */
    function getCategoryJsonByProductGroupId() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $product_group_id = $fn->getReqParam('product_group_id', '', true);

        $json  = array();

        if ($product_group_id == ''){
            $json[] = array('value' => '', 'caption' => 'Please Select');
            return json_encode($json);
        }

        $SQL = $this->getCategorySQLByProductGroup($product_group_id);
        $result = $db->sql_query($SQL);

        $json[] = array('value' => '', 'caption' => 'Please Select');
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['category_id'], "caption" => $row['title']);
        }

        return json_encode($json);
    }

    /**
     *
     */
    function getCategorySQLByProductGroup($product_group_id) {
        $cpCfg = Zend_Registry::get('cpCfg');

        $append = '';

        $SQL = "
        SELECT DISTINCT c.category_id
              ,c.title
        FROM category c
        WHERE c.product_group_id = {$product_group_id}
        ORDER BY c.title
        ";
        return $SQL;
    }
}
