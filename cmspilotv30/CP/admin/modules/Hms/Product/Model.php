<?
class CP_Admin_Modules_Hms_Product_Model extends CP_Common_Lib_ModuleModelAbstract
{

    /**
     *
     */
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSQL = '';

        $SQL = "
        SELECT DISTINCT p.product_id
              ,p.category_id
              ,p.product_code AS item_code
              ,p.sub_category_id
              ,p.title
              ,p.description
              ,p.qty_in_stock{$cpSiteIdSession} AS qty_in_stock
              ,p.price
              ,p.published
              ,p.creation_date
              ,p.modification_date
              ,p.description_short
              ,p.modified_by
              ,p.created_by
              ,p.pack_info
              ,p.pack_type
              ,p.mol1
              ,p.mol2
              ,p.mol3
              ,p.mol4
              ,p.mol5
              ,p.mol6
              ,p.general_quotation
              ,c.title AS category_title
              ,sc.title AS sub_category_title
        FROM product p
        LEFT JOIN (category c)      ON (p.category_id      = c.category_id)
        LEFT JOIN (sub_category sc) ON (p.sub_category_id  = sc.sub_category_id)
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

            if ($tv['keyword'] == "") {
                //$searchVar->sqlSearchVar[] = "p.qty_in_stock > 0";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                p.title LIKE '%{$tv['keyword']}%'
                OR p.description  LIKE '%{$tv['keyword']}%'
                OR p.product_code  LIKE '%{$tv['keyword']}%'
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
        
        $item_code = $fn->getSettingsValueByKey("nextProductCode");
        $fa = $this->getFields();
        $fa['product_code'] = $item_code;
        $fa['published'] = 1;

        $id = $fn->addRecord($fa);
        //To update patient code
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextProductCode'";
        $resultUpdate = $db->sql_query($SQLUpdate);

        $fn->returnAfterNewSave($id);
    }
    /**
     *
     */
    function getAddOld(){
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

        //$validate->validateData('price', 'Please enter the price');


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
        $fn = Zend_Registry::get('fn');
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'category_id');
        $fa = $fn->addToFieldsArray($fa, 'sub_category_id');
        $fa = $fn->addToFieldsArray($fa, 'price');

        $fa = $fn->addToFieldsArray($fa, 'title', '', true);
        $fa = $fn->addToFieldsArray($fa, 'description', '', true);
        $fa = $fn->addToFieldsArray($fa, 'description2', '', true);
        $fa = $fn->addToFieldsArray($fa, 'description_short', '', true);
        $fa = $fn->addToFieldsArray($fa, 'general_quotation');

        if(isset($_POST['published'])){
            $fa = $fn->addToFieldsArray($fa, 'published');
        }

        $fa = $fn->addToFieldsArray($fa, 'modified_by');
        $fa = $fn->addToFieldsArray($fa, 'created_by');


        return $fa;
    }

    /**
     *
     */
    function getImportData1(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper', 'PhpExcelImportWrapper');

        $fa = array(
              'item_code'    => $phpExcel->getImportFldObj('Item Code')
             ,'title'        => $phpExcel->getImportFldObj('Product Title')
             ,'model'        => $phpExcel->getImportFldObj('Model')
             ,'carton_no'    => $phpExcel->getImportFldObj('Carton No')
             ,'batch_no'     => $phpExcel->getImportFldObj('Batch No')
             ,'unit'         => $phpExcel->getImportFldObj('Unit')
             ,'price'         => $phpExcel->getImportFldObj('Price')
        );

        $fa['published']['defaultValue'] = 1;


        $config = array(
             'module'              => 'hms_product'
            ,'fldsArr'             => $fa
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
        $db = Zend_Registry::get('db');

        $fa = array(
              'item_code'      => $phpExcel->getImportFldObj('Item Code')
             ,'title'          => $phpExcel->getImportFldObj('Product Title')
             ,'CONNAUGHT'      => $phpExcel->getImportFldObj('CONNAUGHT')
             ,'ANGGERIK'       => $phpExcel->getImportFldObj('ANGGERIK')
             ,'SOLARIS'        => $phpExcel->getImportFldObj('SOLARIS')
             ,'TINGKAT'        => $phpExcel->getImportFldObj('TINGKAT')
             ,'DRLee'          => $phpExcel->getImportFldObj('DRLee')
        );

        $fa['CONNAUGHT']['refOnly'] = true;
        $fa['ANGGERIK']['refOnly']  = true;
        $fa['SOLARIS']['refOnly']   = true;
        $fa['TINGKAT']['refOnly']   = true;
        $fa['DRLee']['refOnly']     = true;

        /****************************************/
        $config = array(
             'module'              => 'hms_product'
            ,'matchFieldArr'       => array('item_code')
            ,'fldsArr'             => $fa
            ,'callbackAfterInsert' => 'updateDataRowCallback'
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
    function updateDataRowCallback($product_id, $fa) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $productRec = $fn->getRecordRowByID('product', 'product_id', $product_id);
        
        if($fa['CONNAUGHT'] != '' || $fa['ANGGERIK'] != ''){
            $SQLsite = "
            SELECT site_id
                  ,title
            FROM site
            ";
            $resultsite = $db->sql_query($SQLsite);
            $price = '';
            while ($rowsite = $db->sql_fetchrow($resultsite)) {
                $sqlPPRice = "
                SELECT *
                FROM product_price
                WHERE site_id = {$rowsite['site_id']}
                AND product_id = {$productRec['product_id']}
                ";
                $resultPPRice  = $db->sql_query($sqlPPRice);
                $numRowsPPRice = $db->sql_numrows($resultPPRice);
                $rowPPRice = $db->sql_fetchrow($resultPPRice);

                if($rowsite['site_id'] == 1){
                    $price = $fa['CONNAUGHT'];
                }elseif($rowsite['site_id'] == 2){
                    $price = $fa['ANGGERIK'];
                }

                if($price != ''){
                    if($numRowsPPRice > 0){
                        $fa2 = array();
                        $fa2['product_price_id']  = $rowPPRice['product_price_id'];
                        $fa2['product_id']        = $rowPPRice['product_id'];
                        $fa2['site_id']           = $rowsite['site_id'];
                        $fa2['price']             = $rowPPRice['price'];
                        $fa2['creation_date']     = date("Y-m-d H:i:s");
                        $fa2['created_by']        = $fn->getSessionParam('userName');
                        $insertpriceHistorySQL    = $dbUtil->getInsertSQLStringFromArray($fa2, 'product_price_history');
                        $resultpriceHistorySQL    = $db->sql_query($insertpriceHistorySQL);

                        $fa3 = array();
                        $fa3['product_id']        = $rowPPRice['product_id'];
                        $fa3['site_id']           = $rowsite['site_id'];
                        $fa3['price']             = $price;
                        $fa3['modification_date'] = date("Y-m-d H:i:s");
                        $fa3['modified_by']       = $fn->getSessionParam('userName');

                        $whereCondition = "WHERE product_price_id = {$rowPPRice['product_price_id']}";
                        $sqlUpdate = $dbUtil->getUpdateSQLStringFromArray($fa3, "product_price", $whereCondition);
                        $resultUpdate      = $db->sql_query($sqlUpdate);
                    }else{
                        $fa4 = array();
                        $fa4['product_id']        = $productRec['product_id'];
                        $fa4['site_id']           = $rowsite['site_id'];
                        $fa4['price']             = $price;
                        $fa4['creation_date']     = date("Y-m-d H:i:s");
                        $fa4['created_by']        = $fn->getSessionParam('userName');

                        $insertpriceSQL    = $dbUtil->getInsertSQLStringFromArray($fa4, 'product_price');
                        $resultPriceSQL    = $db->sql_query($insertpriceSQL);
                    }
                }
            }
        }
    }
    
    /**
     *
     */
    function getExportData1($dataArray){
        $db = Zend_Registry::get('db');
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'item_code'  => $phpExcel->getFldObj('Item Code')
             ,'title'      => $phpExcel->getFldObj('Product Title')
             ,'price'      => $phpExcel->getFldObj('Price')
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

        $file_name = "Product_Export_" . date("d-m-Y") . ".xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'PRODUCT CODE');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'PRODUCT NAME');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'CATEGORY');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'DESCRIPTION');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'PACK QTY');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'PACK TYPE');

        if ($cpCfg['cp.hasMultiUniqueSites']){
            $SQLSite = "
            SELECT title
            FROM site
            ";
            $resultSite = $db->sql_query($SQLSite);
            while ($rowSite = $db->sql_fetchrow($resultSite)) {
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'QUANTITY'.' '.$rowSite['title']);
            }

            $SQLSite = "
            SELECT title
            FROM site
            ";
            $resultSite = $db->sql_query($SQLSite);
            while ($rowSite = $db->sql_fetchrow($resultSite)) {
                $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'COST PRICE'.' '.$rowSite['title']);
            }
        }

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'SELLING PRICE');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'SUPPLIER NAME');

        $SQLSite = "
        SELECT title
        FROM site
        ";
        $resultSite = $db->sql_query($SQLSite);
        while ($rowSite = $db->sql_fetchrow($resultSite)) {
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'MOL'.' '.$rowSite['title']);
        }

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'MANUFACTURED DATE');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'EXPIRY DATE');

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

        foreach ($dataArray as $row){
            $colc = 0;
            $rowc++;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['item_code']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['title']);

            $categoryTitle = '';
            if($row['category_id'] != ''){
                $SQLCategory = "
                SELECT c.title
                FROM category c
                WHERE c.category_id = {$row['category_id']}
                ";
                $resultCategory = $db->sql_query($SQLCategory);
                $rowCategory = $db->sql_fetchrow($resultCategory);

                $categoryTitle = $rowCategory['title'];
            }

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $categoryTitle);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['description']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['pack_info']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['pack_type']);

            if ($cpCfg['cp.hasMultiUniqueSites']){
                $SQLSite1 = "
                SELECT title
                       ,site_id
                FROM site
                ";
                $resultSite1 = $db->sql_query($SQLSite1);
                while ($rowSite1 = $db->sql_fetchrow($resultSite1)) {
                      $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                }

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
                        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
                    }
                }
            }

        }

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['company_id_supplier']);
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, '');

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
    function getProductPriceValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $product_id  = $fn->getReqParam('product_id');
        $site_id     = $fn->getPostParam('site_id');

        $validate->resetErrorArray();

        $validate->validateData('site_id', 'Please Select the site');
        $validate->validateData('price', 'Please enter the price');

        if($site_id != ''){
            $SQLproduct = "
            SELECT pp.site_id 
                  ,s.title
            FROM product_price pp
            LEFT JOIN site s ON(s.site_id = pp.site_id)
            WHERE pp.site_id = {$site_id}
            AND pp.product_id = {$product_id}
            ";
            $resultproduct = $db->sql_query($SQLproduct);
            $numRowsproduct = $db->sql_numrows($resultproduct);

            if ($numRowsproduct > 0){
                $rowproduct = $db->sql_fetchrow($resultproduct);

                $validate->errorArray['site_id']['name'] = "site_id";
                $validate->errorArray['site_id']['msg']  = "Please note price already added for the site {$rowproduct['title']}.";
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
    function getAddProductPriceSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        if (!$this->getProductPriceValidate()){
            return $validate->getErrorMessageXML();
        }

        $product_id  = $fn->getReqParam('product_id');
        $site_id     = $fn->getPostParam('site_id');
        $price       = $fn->getPostParam('price');

        $fa = array();
        $fa['product_id']       = $product_id;
        $fa['price']            = $price;
        $fa['site_id']          = $site_id;
        $fa['creation_date']    = date("Y-m-d H:i:s");
        $fa['created_by']       = $fn->getSessionParam('userName');

        $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'product_price');
        $result = $db->sql_query($insert);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditProductPriceValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $product_id  = $fn->getReqParam('product_id');
        $site_id     = $fn->getPostParam('site_id');

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
    function getEditProductPriceSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        if (!$this->getEditProductPriceValidate()){
            return $validate->getErrorMessageXML();
        }

        $product_price_id  = $fn->getReqParam('product_price_id');
        $price             = $fn->getPostParam('price');

        $rowProductPrice = $fn->getRecordRowByID('product_price', 'product_price_id', $product_price_id);

        $fa = array();
        $fa['product_id']       = $rowProductPrice['product_id'];
        $fa['price']            = $rowProductPrice['price'];
        $fa['site_id']          = $rowProductPrice['site_id'];
        $fa['product_price_id'] = $rowProductPrice['product_price_id'];
        $fa['creation_date']    = date("Y-m-d H:i:s");
        $fa['created_by']       = $fn->getSessionParam('userName');

        $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'product_price_history');
        $result = $db->sql_query($insert);

        $fa1 = array();
        $fa1['price']             = $price;
        $fa1['modification_date'] = date("Y-m-d H:i:s");
        $fa1['modified_by']       = $fn->getSessionParam('userName');

        $whereCondition = "WHERE product_price_id = {$product_price_id}";
        $SQL    = $dbUtil->getUpdateSQLStringFromArray($fa1, "product_price", $whereCondition);
        $result = $db->sql_query($SQL);

        return $validate->getSuccessMessageXML();
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
