<?
class CP_Admin_Modules_Wine_Product_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $fn = Zend_Registry::get('fn');
        $special_search  = $fn->getReqParam('special_search');
        $exportType = $fn->getReqParam('exportType');
        $additionalCol = '';
        if($special_search == 'Fault' || $exportType == 'fault'){
            $additionalCol = "
            , IF(p.product_code = '' OR p.product_code IS NULL OR (length(p.product_code) > 7 OR length(p.product_code) < 7), 'Yes', 'No') AS product_code_fault
            , IF(p.title = '' OR p.title IS NULL, 'Yes', 'No') AS title_fault
            , IF(p.category_id = '' OR p.category_id IS NULL, 'Yes', 'No') AS category_id_fault
            , IF(p.sub_category_id = '' OR p.sub_category_id IS NULL, 'Yes', 'No') AS sub_category_id_fault
            , IF(p.country_code = '' OR p.country_code IS NULL, 'Yes', 'No') AS country_code_fault
            , IF(p.region_id = '' OR p.region_id IS NULL, 'Yes', 'No') AS region_id_fault
            , IF(p.brand_code  = '' OR p.brand_code IS NULL, 'Yes', 'No') AS brand_code_fault
            , IF(p.color = '' OR p.color IS NULL, 'Yes', 'No') AS color_fault
            , IF(p.grape = '' OR p.grape IS NULL, 'Yes', 'No') AS grape_fault
            , IF(p.vintage = '' OR p.vintage IS NULL, 'Yes', 'No') AS vintage_fault
            , IF(p.producer_id = ''  OR p.producer_id IS NULL, 'Yes', 'No') AS producer_fault
            , IF(p.bottle_size = '' OR p.bottle_size IS NULL, 'Yes', 'No') AS bottle_size_fault
            , IF(p.description_short  = '' OR p.description_short IS NULL, 'Yes', 'No') AS description_short_fault
            , IF(p.product_id NOT IN (
                        SELECT DISTINCT record_id
                        FROM media
                        WHERE room_name = 'wine_product'
                        AND record_type = 'picture'
                        ), 'Yes', 'No') AS pic_ref_fault
            ";
//            , IF(p.description = '' OR p.description IS NULL, 'Yes', 'No') AS description_fault
        }
        
        $SQL = "
        SELECT   p.*
                ,gc.name AS country_name
                ,r.title AS region_name
                ,a.title AS appellation_title
                ,pd.title AS producer_title
                ,c.title AS category_title
                ,c.category_type
                ,sc.title AS sub_category_title
                ,(SELECT actual_file_name
                FROM media
                WHERE room_name = 'wine_product'
                AND record_type = 'picture'
                AND record_id = p.product_id
                LIMIT 0, 1
                ) AS picture_ref
                {$additionalCol}
        FROM product p
        LEFT JOIN (geo_country gc) ON (p.country_code = gc.country_code)
        LEFT JOIN (region r) ON (p.region_id = r.region_id)
        LEFT JOIN (category c)      ON (p.category_id      = c.category_id)
        LEFT JOIN (sub_category sc) ON (p.sub_category_id  = sc.sub_category_id)
        LEFT JOIN (appellation a) ON (p.appellation_id  = a.appellation_id)
        LEFT JOIN (producer pd) ON (pd.producer_id  = p.producer_id)
        ";

        return $SQL;
    }


    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $product_id   = $fn->getReqParam('product_id');
        $category     = $fn->getReqParam('category');
        $sub_category = $fn->getReqParam('sub_category');
        $special_search  = $fn->getReqParam('special_search');
        $producer_id    = $fn->getReqParam('producer_id');
        $country     = $fn->getReqParam('country');
        $color       = $fn->getReqParam('color');
        $region_id   = $fn->getReqParam('region');
        $grape       = $fn->getReqParam('grape');
        $bottle_size = $fn->getReqParam('bottle_size');
        $brand_code  = $fn->getReqParam('brand_code');
        $appellation_id  = $fn->getReqParam('appellation_id');

        $exportType = $fn->getReqParam('exportType');

        if ($product_id != '') {
            $searchVar->sqlSearchVar[] = "p.product_id = {$product_id}";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "p.product_id = {$tv['record_id']}";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'p.product_id');

            if($tv['linkName'] == 'product#product'){
                $searchVar->sqlSearchVar[] = "p.product_id != {$tv['linkMasterTableID']}";
            }

            if ($producer_id != '' ) {
                $searchVar->sqlSearchVar[] = "p.producer_id = {$producer_id}";
            }

            if ($country != '' ) {
                $searchVar->sqlSearchVar[] = "p.country_code = '{$country}'";
            }

            if ($region_id != '' ) {
                $searchVar->sqlSearchVar[] = "p.region_id = '{$region_id}'";
            }

            if ($appellation_id != '' ) {
                $searchVar->sqlSearchVar[] = "p.appellation_id = '{$appellation_id}'";
            }

            if ($tv['category_id'] != '' ) {
                $searchVar->sqlSearchVar[] = "c.category_id = '{$tv['category_id']}'";
            }

            if ($tv['sub_category_id'] != '' ) {
                $searchVar->sqlSearchVar[] = "sc.sub_category_id = '{$tv['sub_category_id']}'";
            }

            if ($tv['record_id'] != '') {
                $searchVar->sqlSearchVar[] = "p.product_id = '{$tv['record_id']}'";
            }

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "p.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(p.flag != 1 OR p.flag IS null)";
            }

            if ($tv['special_search']  == 'Published') {
                $searchVar->sqlSearchVar[] = "p.published = 1";
            }

            if ($tv['special_search'] == 'Not-Published' ) {
                $searchVar->sqlSearchVar[] = "p.published = 0 OR p.published IS NULL OR p.published = ''";
            }

            if ($special_search == 'Latest' ) {
                $searchVar->sqlSearchVar[] = "p.latest = 1";
            }

            if ($special_search == 'Records Missing in Source (JDE)' ) {
                $searchVar->sqlSearchVar[] = "p.record_missing_in_src = 1";
            }

            if ($special_search == 'New Records (from JDE)' ) {
                $searchVar->sqlSearchVar[] = "p.new_record = 1";
            }

            if ($special_search == 'Fault' || $exportType == 'fault'){
                $searchVar->sqlSearchVar[] = "(
                      p.product_code = '' OR p.product_code IS NULL
                   OR (length(p.product_code) > 7 OR length(p.product_code) < 7)
                   OR p.title = ''        OR p.title IS NULL
                   OR p.category_id = ''  OR p.category_id IS NULL
                   OR p.sub_category_id = '' OR p.sub_category_id IS NULL
                   OR p.color = ''        OR p.color IS NULL
                   OR p.grape = ''        OR p.grape IS NULL
                   OR p.vintage = ''      OR p.vintage IS NULL
                   OR p.country_code = '' OR p.country_code IS NULL
                   OR p.region_id = ''    OR p.region_id IS NULL
                   OR p.producer_id = ''  OR p.producer_id IS NULL
                   OR p.bottle_size = ''  OR p.bottle_size IS NULL

                   OR p.product_id NOT IN (
                        SELECT DISTINCT record_id
                        FROM media
                        WHERE room_name = 'wine_product'
                        AND record_type = 'picture'
                        )

                   OR p.brand_code  = ''  OR p.brand_code IS NULL
                   -- OR p.description = ''  OR p.description IS NULL
                   OR p.description_short  = ''  OR p.description_short IS NULL
                 )
                ";
            }

            //------------------------------------------------------------------------//
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    p.title        LIKE '%{$tv['keyword']}%' OR
                    p.chi_title    LIKE '%{$tv['keyword']}%' OR
                    p.description      LIKE '%{$tv['keyword']}%' OR
                    p.chi_description  LIKE '%{$tv['keyword']}%' OR
                    p.description_short      LIKE '%{$tv['keyword']}%' OR
                    p.chi_description_short  LIKE '%{$tv['keyword']}%' OR
                    p.product_code LIKE '%{$tv['keyword']}%' OR
                    p.brand_code   LIKE '%{$tv['keyword']}%' OR
                    p.product_id   LIKE '%{$tv['keyword']}%' OR
                    a.title LIKE '%{$tv['keyword']}%'
                )";
            }

            if ($color != '' ) {
                $searchVar->sqlSearchVar[] = "p.color = '{$color}'";
            }

            if ($grape != '' ) {
                $searchVar->sqlSearchVar[] = "p.grape = '{$grape}'";
            }

            if ($bottle_size != '' ) {
                $searchVar->sqlSearchVar[] = "p.bottle_size = '{$bottle_size}'";
            }

            if ($brand_code != '' ) {
                $searchVar->sqlSearchVar[] = "p.brand_code = '{$brand_code}'";
            }

        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        $validate->validateData('title', 'Please enter the title');
        $validate->validateData('category_id', 'Please select the category');

        $category_id = $fn->getReqParam('category_id');
        $catRec = $fn->getRecordRowById('category', 'category_id', $category_id);
        if ($catRec['category_type'] == 'Wine') {
            $expProductCode = array(
                 'validationType' => 'regEx'
                ,'minLength' => 7
                ,'maxLength' => 7
                ,'ignoreEmpty' => false
                ,'regEx' => "/^[a-zA-Z]{2}[a-zA-Z0-9]{5}$/"
            );
            $validate->validateData2('product_code', 'Product code must be non-empty 7 characters and first 2 characters must be alphabets', $expProductCode);
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
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $brandCode = substr($fa['product_code'], 0, 2);
        $fa['brand_code'] = $brandCode;
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the title');

        $category_id = $fn->getReqParam('category_id');
        $catRec = $fn->getRecordRowById('category', 'category_id', $category_id);

        if ($catRec['category_type'] == 'Wine') {
            $expBrand = array(
                 'validationType' => 'regEx'
                ,'minLength' => 2
                ,'maxLength' => 2
                ,'ignoreEmpty' => true
                ,'regEx' => "/^[a-zA-Z0-9][a-zA-Z0-9]$/"
            );
            $validate->validateData2('brand_code', 'Brand Code must be 2 letters.', $expBrand);

            $expVintage = array(
                 'validationType' => 'regEx'
                ,'minLength' => 2
                ,'maxLength' => 4
                ,'ignoreEmpty' => false
                ,'regEx' => "/^((19|20|NV|nv)\d{2}$)|^(NV|nv)$/"
            );
            $validate->validateData2('vintage', 'Vintage must be 4 numerical characters and start with 19 or 20 - or - NV', $expVintage);
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
        $this->getSaveProductCountryRecords($id);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'product_code');
        $fa = $fn->addToFieldsArray($fa, 'title', '', true);
        $fa = $fn->addToFieldsArray($fa, 'product_type');
        $fa = $fn->addToFieldsArray($fa, 'country_code');
        $fa = $fn->addToFieldsArray($fa, 'region_id');
        $fa = $fn->addToFieldsArray($fa, 'category_id');
        $fa = $fn->addToFieldsArray($fa, 'sub_category_id');
        $fa = $fn->addToFieldsArray($fa, 'appellation_id');
        $fa = $fn->addToFieldsArray($fa, 'brand_code');
        $fa = $fn->addToFieldsArray($fa, 'description_short', '', true);
        $fa = $fn->addToFieldsArray($fa, 'wine_type');
        $fa = $fn->addToFieldsArray($fa, 'color');
        $fa = $fn->addToFieldsArray($fa, 'grape');
        $fa = $fn->addToFieldsArray($fa, 'vintage');
        $fa = $fn->addToFieldsArray($fa, 'appellation');
        $fa = $fn->addToFieldsArray($fa, 'producer_id');
        $fa = $fn->addToFieldsArray($fa, 'bottle_size');
        $fa = $fn->addToFieldsArray($fa, 'price');
        $fa = $fn->addToFieldsArray($fa, 'previous_price');
        $fa = $fn->addToFieldsArray($fa, 'qty_in_stock');
        $fa = $fn->addToFieldsArray($fa, 'stock_threshold');
        $fa = $fn->addToFieldsArray($fa, 'latest');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'special_offer_description', '', true);
        $fa = $fn->addToFieldsArray($fa, 'description', '', true);

        $fa = $fn->addToFieldsArray($fa, 'meta_title', '', $cpCfg['cp.hasMultiLangForMetaData']);
        $fa = $fn->addToFieldsArray($fa, 'meta_keyword', '', $cpCfg['cp.hasMultiLangForMetaData']);
        $fa = $fn->addToFieldsArray($fa, 'meta_description', '', $cpCfg['cp.hasMultiLangForMetaData']);

        return $fa;
    }
    
    
    function getSaveProductCountryRecords($product_id) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        $total_fields = $cpCfg['m.wine.product.totalProductCountrySpecialFlds'];
        $product_country_idArr = $fn->getPostParam('product_country_id', '', false, true);
        foreach ($product_country_idArr AS $product_country_id) {
            $fa = array();
            $fa = $fn->addModificationDetailsToFieldsArray($fa, 'product_country');
            for($i = 1; $i <= $total_fields; $i++){
                $dbFldName = "special_{$i}";
                $formFldName = "{$dbFldName}_{$product_country_id}";
                $fa[$dbFldName] = $fn->getPostParam($formFldName);
            }

            //-------------- SPECIAL OFFER
            $dbFldName = "special_offer";
            $formFldName = "{$dbFldName}_{$product_country_id}";
            $fa[$dbFldName] = $fn->getPostParam($formFldName);            
            
            $whereCondition = "
            WHERE product_country_id = {$product_country_id}
              AND product_id = {$product_id}
            ";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, "product_country", $whereCondition);
            $result = $db->sql_query($SQL);
        }

    }

    /**
     *
     */
    function getWineProductEcommerceProductLinkSQL($id) {
        $SQL = "
        SELECT rp.related_product_id
              ,p.product_id
              ,p.title
              ,c.title AS category_title
        FROM related_product rp
        JOIN product p ON (p.product_id = rp.product_id_rel)
        LEFT JOIN category c ON (c.category_id = p.category_id)
        WHERE rp.product_id = {$id}
        ";
        return $SQL;
    }

    /**
     */
    function getWineProductEcommerceCountryLinkSQL($id) {
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');

        $flds = ($formObj->mode == 'detail') ? 'c.title' : 'b.country_id';

        $SQL = "
        SELECT b.product_country_id
              ,{$flds}
              ,(SELECT SUM(pc.stock)
                 FROM product_city pc
                 JOIN product_country pco ON (pc.product_country_id = pco.product_country_id)
                 WHERE pco.country_id = b.country_id
                 AND pco.product_id = {$id}
              ) AS total_stock
              ,b.price
              ,c.currency_code
              ,b.special_price
              ,c.currency_code AS currency_code2
              ,b.published
        FROM `product_country` b
        LEFT JOIN country c ON (b.country_id = c.country_id)
        WHERE b.product_id = {$id}
        ORDER BY b.product_country_id
        ";

        return $SQL;
    }

    /**
     *
     */
    function getImportData(){
        $fn = Zend_Registry::get('fn');
        $importType = $fn->getReqParam('importType');
        if ($importType == 'stock'){
            return $this->getImportStockData();
        } else if ($importType == 'threshold'){
            return $this->getImportStockThresholdData();
        } else if ($importType == 'specialSearch'){
            return $this->getImportSpecialSearchData();
        } else {
            return $this->getImportProductData();
        }
    }

    /**
     *
     */
    function getImportProductData(){
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');

        $fa = array(
              'product_code'        => $phpExcel->getImportFldObj('Product Code')
             ,'title'               => $phpExcel->getImportFldObj('Title E')
             ,'chi_title'           => $phpExcel->getImportFldObj('Title C')

             ,'category_id'         => $phpExcel->getImportFldObj('Record Type E')
             ,'sub_category_id'     => $phpExcel->getImportFldObj('Type E')
             ,'color'               => $phpExcel->getImportFldObj('Color E')
             ,'chi_color'           => $phpExcel->getImportFldObj('Color C')
             ,'grape'               => $phpExcel->getImportFldObj('Grape E')
             ,'chi_grape'           => $phpExcel->getImportFldObj('Grape C')
             ,'vintage'             => $phpExcel->getImportFldObj('Vintage')
             ,'country_code'        => $phpExcel->getImportFldObj('Country E')
             ,'chi_country'         => $phpExcel->getImportFldObj('Country C')
             ,'region_id'           => $phpExcel->getImportFldObj('Region E')
             ,'appellation_id'      => $phpExcel->getImportFldObj('Appellation E')
             ,'producer_id'         => $phpExcel->getImportFldObj('Producer Code')
             ,'producer_code'       => $phpExcel->getImportFldObj('Producer Code')
             ,'bottle_size'         => $phpExcel->getImportFldObj('Bottle Size')

             ,'description_short'   => $phpExcel->getImportFldObj('Short Description E')
             ,'chi_description_short'   => $phpExcel->getImportFldObj('Short Description C')
             ,'description'         => $phpExcel->getImportFldObj('Long Description E')
             ,'chi_description'     => $phpExcel->getImportFldObj('Long Description C')

             ,'picture'             => $phpExcel->getImportFldObj('Picture Ref')
             ,'picture_new'         => $phpExcel->getImportFldObj('Is New Picture?')
             ,'brand_code'          => $phpExcel->getImportFldObj('Brand Code')
             ,'rating_wa'           => $phpExcel->getImportFldObj('WA Rating')
             ,'rating_decanter'     => $phpExcel->getImportFldObj('Decanter Rating')
             ,'rating_ws'           => $phpExcel->getImportFldObj('WS Rating')
             ,'rating_st'           => $phpExcel->getImportFldObj('ST Rating')
             ,'rating_jr'           => $phpExcel->getImportFldObj('JR Rating')
             ,'rating_burghound'    => $phpExcel->getImportFldObj('Burghound Rating')
             ,'published'           => $phpExcel->getImportFldObj('Published')

             ,'tasting_note_1'      => $phpExcel->getImportFldObj('Tasting Note 1E')
             ,'chi_tasting_note_1'  => $phpExcel->getImportFldObj('Tasting Note 1C')
             ,'tasting_note_2'      => $phpExcel->getImportFldObj('Tasting Note 2E')
             ,'chi_tasting_note_2'  => $phpExcel->getImportFldObj('Tasting Note 2C')
             ,'tasting_note_3'      => $phpExcel->getImportFldObj('Tasting Note 3E')
             ,'chi_tasting_note_3'  => $phpExcel->getImportFldObj('Tasting Note 3C')
        );

        /******** SPECIAL MANIPULATIONS ********/
        $fa['category_id']['specialType'] = 'category';
        $fa['category_id']['exp'] = array('sectionType' => 'Product');

        $fa['sub_category_id']['specialType'] = 'subCategory';
        $fa['sub_category_id']['exp'] = array(
             'categoryFldKeyInArr' => 'category_id'
        );

        $fa['country_code']['specialType']    = 'geo_country';

        $fa['region_id']['specialType'] = 'fetchIdFromRefModule';
        $fa['region_id']['exp'] = array(
             'refModule' => 'common_region'
            ,'extraFldsOnCreation' => array('country_code')
            ,'extraLangFldsOnCreation' => array('chi_title' => 'Region C')
            ,'extraFldsInSqlCondn' => array('country_code')
        );

        $fa['appellation_id']['specialType'] = 'fetchIdFromRefModule';
        $fa['appellation_id']['exp'] = array(
             'refModule' => 'wine_appellation'
            ,'extraFldsOnCreation' => array('country_code', 'region_id')
            ,'extraLangFldsOnCreation' => array('chi_title' => 'Appellation C')
            ,'extraFldsInSqlCondn' => array('country_code', 'region_id')
        );
        
        $fa['producer_id']['specialType'] = 'fetchIdFromRefModule';
        $fa['producer_id']['exp'] = array(
             'refModule'  => 'wine_producer'
            ,'titleField' => 'producer_code'           
            ,'extraFldsOnCreation' => array('producer_code')            
            ,'extraFldsInSqlCondn' => array('producer_code')
        );

        $fa['bottle_size']['specialType'] = 'valuelist';
        $fa['bottle_size']['exp'] = array(
             'keyText' => 'wineBottleSize'
        );

        $fa['chi_country']['refOnly']      = true;
        $fa['producer_code']['refOnly']    = true;
        $fa['picture']['refOnly']          = true;
        $fa['picture_new']['refOnly']      = true;
        $fa['rating_wa']['refOnly']        = true;
        $fa['rating_decanter']['refOnly']  = true;
        $fa['rating_ws']['refOnly']        = true;
        $fa['rating_st']['refOnly']        = true;
        $fa['rating_jr']['refOnly']        = true;
        $fa['rating_burghound']['refOnly'] = true;

        $fa['published']['defaultValue'] = 1;

        $fa['tasting_note_1']['refOnly'] = true;
        $fa['chi_tasting_note_1']['refOnly'] = true;

        $fa['tasting_note_2']['refOnly'] = true;
        $fa['chi_tasting_note_2']['refOnly'] = true;

        $fa['tasting_note_3']['refOnly'] = true;
        $fa['chi_tasting_note_3']['refOnly'] = true;

        /****************************************/
        $config = array(
             'module'              => 'wine_product'
            ,'matchFieldArr'       => array('product_code')
            ,'mandatoryFldsArr'    => array('product_code')
            ,'fldsArr'             => $fa
            ,'callbackAfterInsert' => 'callbackAfterImportInsert'
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     * @param type $dataArray
     * @return type
     */
    function getExportData($dataArray){
        $fn = Zend_Registry::get('fn');
        $exportType = $fn->getReqParam('exportType');

        if ($exportType == 'fault'){
            return $this->getExportFaultyRecord($dataArray);
        } else {
            $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

            $fa = array(
                  'product_code'    => $phpExcel->getFldObj('Product Code')
                 ,'title'           => $phpExcel->getFldObj('Title')
                 ,'category_title'  => $phpExcel->getFldObj('Category')
                 ,'sub_category_title' => $phpExcel->getFldObj('Sub Category')
                 ,'country_name'    => $phpExcel->getFldObj('Country')
                 ,'region_name'     => $phpExcel->getFldObj('Region')
                 ,'appellation_title' => $phpExcel->getFldObj('Appellation')
                 ,'brand_code'      => $phpExcel->getFldObj('Brand Code')
                 ,'color'           => $phpExcel->getFldObj('Color')
                 ,'grape'           => $phpExcel->getFldObj('Grape')
                 ,'vintage'         => $phpExcel->getFldObj('Vintage')
                 ,'producer_title'  => $phpExcel->getFldObj('Producer')
                 ,'bottle_size'     => $phpExcel->getFldObj('Bottle Size')
                 ,'latest'          => $phpExcel->getFldObj('Latest')
                 ,'special'         => $phpExcel->getFldObj('Special')
                 ,'published'       => $phpExcel->getFldObj('Published')
                 ,'description_short' => $phpExcel->getFldObj('Short Description')
                 ,'special_offer_description' => $phpExcel->getFldObj('Special Offer Description')
                 ,'description' => $phpExcel->getFldObj('Description')
            );

            $file_name = "Product_" . date("d-m-Y") . ".xls";

            $config = array(
                 'filename'  => $file_name
                ,'fldsArr'   => $fa
                ,'dataArray' => $dataArray
            );

            return $phpExcel->exportData($config);
        }
    }

    /**
     *
     */
    function callbackAfterImportInsert($product_id, $fa) {
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        if ($fa['picture'] != ''){//attach the picture
            $picture = "{$fa['picture']}";
            $sourceFilePath = realpath('../media_import') . "/{$picture}";
            $exp = array(
                 'srcFile' => $sourceFilePath
                ,'actualFileName' => $picture
            );
            
            $condn = "
                record_id = {$product_id}
            AND actual_file_name = '{$fa['picture']}' 
            AND room_name = 'wine_product' 
            AND record_type = 'picture'";
            $mediaRow = $fn->getRecordByCondition('media', $condn);

            if(!is_array($mediaRow)){
                $media->model->createMedia('wine_product', 'picture', $product_id, $exp);
            } else if($fa['picture_new'] == 1 ){
                $this->deleteProductImage($mediaRow['media_id']);
                $media->model->createMedia('wine_product', 'picture', $product_id, $exp);
            }
            //print $picture . "<br>";
        }
        
        if($fa['chi_country'] != ''){//Update geo_country Chinese name
            $fa2 = array();
            $fa2['chi_name'] = $fa['chi_country'];
            $whereCondition = "WHERE country_code = '{$fa['country_code']}'";
            $SQL    = $dbUtil->getUpdateSQLStringFromArray($fa2, 'geo_country', $whereCondition);
            $result = $db->sql_query($SQL);            
        }

        $this->linkRatingData($product_id, 'WA'       , $fa['rating_wa']);
        $this->linkRatingData($product_id, 'Decanter' , $fa['rating_decanter']);
        $this->linkRatingData($product_id, 'WS'       , $fa['rating_ws']);
        $this->linkRatingData($product_id, 'ST'       , $fa['rating_st']);
        $this->linkRatingData($product_id, 'JR'       , $fa['rating_jr']);
        $this->linkRatingData($product_id, 'Burghound', $fa['rating_burghound']);

        //-------------- tasting notes ------------------------------------------------------------------
        $SQL = "
        DELETE FROM tasting_notes
        WHERE product_id = '{$product_id}'
        ";
        $result = $db->sql_query($SQL);
        
        $tasting_note_1 = $fn->getIssetParam($fa, 'tasting_note_1');
        $chi_tasting_note_1 = $fn->getIssetParam($fa, 'chi_tasting_note_1');
        $this->linkTastingNotes($product_id, $tasting_note_1, $chi_tasting_note_1, 1);//only 1st notes is published

        $tasting_note_2 = $fn->getIssetParam($fa, 'tasting_note_2');
        $chi_tasting_note_2 = $fn->getIssetParam($fa, 'chi_tasting_note_2');
        $this->linkTastingNotes($product_id, $tasting_note_2, $chi_tasting_note_2);

        $tasting_note_3 = $fn->getIssetParam($fa, 'tasting_note_3');
        $chi_tasting_note_3 = $fn->getIssetParam($fa, 'chi_tasting_note_3');
        $this->linkTastingNotes($product_id, $tasting_note_3, $chi_tasting_note_3);

    }
    
    function deleteProductImage($media_id){
        $dbz = Zend_Registry::get('dbz');
        $media = Zend_Registry::get('media');        
        $media->getDeleteMedia($media_id);
        $SQLDeleteMedia = "
        DELETE FROM `media` 
        WHERE room_name = 'wine_product' 
          AND media_id = {$media_id}  
        ";
        $dbz->query($SQLDeleteMedia);       
    }
    
    /**
     *
     */
    function linkRatingData($product_id, $source, $rating) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        if ($rating == ''){
            return;
        }

        $rec = $fn->getRecordByCondition('rating', "product_id = '{$product_id}' AND source = '{$source}'");

        $fa['product_id'] = $product_id;
        $fa['source']     = $source;
        $fa['rating']     = $rating;

        if(!is_array($rec)){
            $fa = $fn->addCreationDetailsToFieldsArray($fa, 'rating');
            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'rating');
            $result = $db->sql_query($SQL);
            $rating_id = $db->sql_nextid();
        } else {
            $fa = $fn->addModificationDetailsToFieldsArray($fa, 'rating');
            $rating_id = $rec['rating_id'];
            $whereCondition = "WHERE rating_id = {$rating_id}";
            $SQL    = $dbUtil->getUpdateSQLStringFromArray($fa, 'rating', $whereCondition);
            $result = $db->sql_query($SQL);
        }
    }

    /**
     *
     */
    function linkTastingNotes($product_id, $notes, $chi_notes, $published = 0) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        if ($notes == '' && $chi_notes  == ''){
            return;
        }

        $fa['product_id'] = $product_id;
        $fa['notes']      = $notes;
        $fa['chi_notes']  = $chi_notes;
        $fa['published']  = $published;

        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'tasting_notes');
        $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'tasting_notes');
        $result = $db->sql_query($SQL);
        $tasting_notes_id = $db->sql_nextid();
    }

    /**
     * http://pudao.localhost/admin/index.php?_spAction=importFromJDE&module=wine_product&showHTML=0
     * http://pudao.testpilotweb.com/admin/index.php?_spAction=importFromJDE&module=wine_product&showHTML=0
     */
    function getImportFromJDE(){
        $cpCfg = Zend_Registry::get('cpCfg');
        set_time_limit(20000);
        ini_set('memory_limit', '1536M');

        if(CP_ENV != 'production'){//download the latest file from FTP
            if(!$this->downloadJDEDataFromFTP()){
                return "<h3>Import failed. Can't download the file from FTP</h3>";
            }
        }
        
        require_once 'PHPExcel/IOFactory.php';
        $inputFileName = "{$cpCfg['cp.siteRoot']}/jde/jdetopudao.txt";
        $objReader = PHPExcel_IOFactory::createReader('CSV');
        $objReader->setDelimiter(',');
        $objReader->setLineEnding("\r\n");
        $objReader->setSheetIndex(1); 

        //import csv into xls (phpExcel Object)
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');
        $phpExcel->excelFilePath = "{$cpCfg['cp.siteRoot']}/jde/jdetopudao.xls";
        $phpExcel->setWorksheetObj();
        $objReader->loadIntoExisting($inputFileName, $phpExcel->excelObj);
        $phpExcel->worksheet = $phpExcel->excelObj->getActiveSheet();
        $phpExcel->countRows = $phpExcel->worksheet->getHighestRow();
        $phpExcel->countCols = $phpExcel->worksheet->getHighestColumn(); 
        $phpExcel->setFieldsColName();

        return $this->getImportStockData($phpExcel);
    }    
    
    /**
     * 
     * @return boolean
     */
    private function downloadJDEDataFromFTP(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $ftpConf = $cpCfg['production']['ftp'];
        $local_file = "{$cpCfg['cp.siteRoot']}/jde/jdetopudao.txt";
        $server_file = "/httpdocs/jde/jdetopudao.txt";
        
        $conn_id = ftp_connect($ftpConf['host']);
        $login_result = ftp_login($conn_id, $ftpConf['username'], $ftpConf['password']);
        if ($login_result && ftp_get($conn_id, $local_file, $server_file, FTP_BINARY)) {
            return true;
        } else {
            return false;
        }         
    }
    
    /**
     * 
     * @param Object $phpExcel
     * @return string
     */
    function getImportStockData($phpExcel){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $SQL = "
        UPDATE product
        SET new_record = 0
           ,record_missing_in_src = 1
        ";
        $result = $db->sql_query($SQL);
        $totalRows = $phpExcel->countRows;
        //$totalRows = 10;
        for ($curRow = 2; $curRow <= $totalRows; $curRow++) {
            $item_val = $phpExcel->getExcelFieldValue('Item', $curRow);
            if(strlen($item_val) != 5){ //ignore the non-5-digits items
                continue;
            }

            $item    = substr($item_val, 0, 5);
            $vintage = $phpExcel->getExcelFieldValue('Vintage', $curRow);
            $lot     = substr($vintage, 0, 2);

            $product_code = $item . $lot;

            //according to Mavis Zhang the Vintage can be either 2 digit number or the text NV
            // we are simply checking for length of the Vintage > 2 and ignore those records
            if (strlen($vintage) > 2){
                continue;
            }

            if ($product_code == ''){
                continue;
            }

            /**** check for record existance by querying against the match fields ****/
            $rec = $fn->getRecordByCondition('product', "product_code = '{$product_code}'");

            $fa['product_code'] = $product_code;
            //$fa['published'] = 1;

            $fa['record_missing_in_src'] = 0;

            if(!is_array($rec)){
               // $fa['title'] = $phpExcel->getExcelFieldValue('Item Name', $curRow);
               // $fa['new_record'] = 1;
               // $fa = $fn->addCreationDetailsToFieldsArray($fa, 'product');
               // $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'product');
               // $result = $db->sql_query($SQL);
               // $product_id = $db->sql_nextid();
                continue; //import only existing items in the product table
            } else {
                $fa = $fn->addCreationDetailsToFieldsArray($fa, 'product');
                $product_id = $rec['product_id'];
                $whereCondition = "WHERE product_id = {$product_id}";
                $SQL    = $dbUtil->getUpdateSQLStringFromArray($fa, 'product', $whereCondition);
                $result = $db->sql_query($SQL);
            }
   
            $this->callbackAfterStockImportInsert($product_id, $phpExcel, $curRow);
        }

        return "
        <script>
           window.opener.location = window.opener.location;
        </script>
        <div class='left'>
            <h1>Import Completed. Please <strong><a href='javascript:window.close();'>close</a></strong> this window</h1>
        </div>
        ";
    }
    
    /**
     * 
     * @param Object $phpExcel
     * @return string
     */
    function getImportSpecialSearchData(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');
        if (!$phpExcel->getUploadedFile()){
            return;
        }
        $phpExcel->setWorksheetObj();
        $phpExcel->setFieldsColName();
        $totalRows = $phpExcel->countRows;

        $country_id = $fn->getReqParam('country_id');
        if($country_id == ''){
            return;
        }
        
        for ($curRow = 2; $curRow <= $totalRows; $curRow++) {
            $product_code = $phpExcel->getExcelFieldValue('Product Code', $curRow);
            if ($product_code == ''){
                continue;
            }

            /**** check for record existance by querying against the match fields ****/
            $productRow = $fn->getRecordByCondition('product', "product_code = '{$product_code}'");

            if(!is_array($productRow)){
                continue;
            }
            
            $fa = array(
                  'special_1'  => $phpExcel->getExcelFieldValue('Special 1', $curRow)
                 ,'special_2'  => $phpExcel->getExcelFieldValue('Special 2', $curRow)
                 ,'special_3'  => $phpExcel->getExcelFieldValue('Special 3', $curRow)
                 ,'special_4'  => $phpExcel->getExcelFieldValue('Special 4', $curRow)
                 ,'special_5'  => $phpExcel->getExcelFieldValue('Special 5', $curRow)
                 ,'special_6'  => $phpExcel->getExcelFieldValue('Special 6', $curRow)
                 ,'special_7'  => $phpExcel->getExcelFieldValue('Special 7', $curRow)
                 ,'special_8'  => $phpExcel->getExcelFieldValue('Special 8', $curRow)
                 ,'special_9'  => $phpExcel->getExcelFieldValue('Special 9', $curRow)
                 ,'special_10' => $phpExcel->getExcelFieldValue('Special 10', $curRow)
                 ,'special_offer' => $phpExcel->getExcelFieldValue('Special Offer', $curRow)
                 ,'special_price' => $phpExcel->getExcelFieldValue('Special Price', $curRow)
                 );
            
            $product_id = $productRow['product_id'];
            
            $rec = $fn->getRecordByCondition('product_country', "product_id = {$product_id} AND country_id = {$country_id}");
            if(!is_array($rec)){
                $fa['product_id'] = $product_id;
                $fa['country_id'] = $country_id;
                $fa = $fn->addCreationDetailsToFieldsArray($fa, 'product_country');
                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'product_country');
                $result = $db->sql_query($SQL);
                $product_country_id = $db->sql_nextid();
            } else {
                $fa = $fn->addModificationDetailsToFieldsArray($fa, 'product_country');
                $product_country_id = $rec['product_country_id'];
                $whereCondition = "WHERE product_country_id = {$product_country_id}";
                $SQL    = $dbUtil->getUpdateSQLStringFromArray($fa, 'product_country', $whereCondition);
                $result = $db->sql_query($SQL);
            }
        }

        return "
        <script>
           window.opener.location = window.opener.location;
        </script>
        <div class='left'>
            <h1>Import Completed. Please <strong><a href='javascript:window.close();'>close</a></strong> this window</h1>
        </div>
        ";

    }

    /**
     *
     */
    function callbackAfterStockImportInsert($product_id, $phpExcel, $curRow) {
        $dbUtil = Zend_Registry::get('dbUtil');

        /********** COUNTRIES *************/
        $SQL = "
        SELECT *
        FROM country
        ";
        $countryArr = $dbUtil->getSQLResultAsArray($SQL);

        foreach ($countryArr AS $countryRec){
            if ($countryRec['country_code'] != ''){
                $priceFldName = $countryRec['country_code'] . ' Price';
                $country_price = $phpExcel->getExcelFieldValue($priceFldName, $curRow);
                $product_country_id = $this->setCountryPrice($product_id, $countryRec['country_code'], $country_price);

                /********** CITIES *************/
                if ($product_country_id != ''){
                    $SQL = "
                    SELECT *
                    FROM city
                    WHERE country_id = {$countryRec['country_id']}
                    ";
                    $cityArr = $dbUtil->getSQLResultAsArray($SQL);

                    foreach ($cityArr AS $cityRec){
                        $stockFldName = $cityRec['city_code'] . ' Qty'; /*** ex: SH Qty ***/
                        $stock = $phpExcel->getExcelFieldValue($stockFldName, $curRow);
                        if($stock != ''){
                            $this->setCityStock($product_id, $product_country_id, $cityRec['city_code'], $stock);
                        }//if ends
                    }//foreach ends
                }//if ends
            }
        }
    }

    /**
     *
     */
    function setCountryPrice($product_id, $country_code, $price) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $countryRec = $fn->getRecordByCondition('country', "country_code = '{$country_code}'");

        if (is_array($countryRec)){
            $country_id = $countryRec['country_id'];
            $rec = $fn->getRecordByCondition('product_country', "product_id = '{$product_id}' AND country_id = '{$country_id}'");
            
            $fa = array();
            $fa['price']  = $price;

            if(!is_array($rec)){
                $fa['product_id'] = $product_id;
                $fa['country_id'] = $country_id;
                $fa['published']  = 1;
                $fa = $fn->addCreationDetailsToFieldsArray($fa, 'product_country');
                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'product_country');
                $result = $db->sql_query($SQL);
                $product_country_id = $db->sql_nextid();

            } else {
                $fa = $fn->addModificationDetailsToFieldsArray($fa, 'product_country');
                $product_country_id = $rec['product_country_id'];
                $whereCondition = "WHERE product_country_id = {$product_country_id}";
                $SQL    = $dbUtil->getUpdateSQLStringFromArray($fa, 'product_country', $whereCondition);
                $result = $db->sql_query($SQL);
            }

            return $product_country_id;
        }
    }

    /**
     *
     */
    function setCityStock($product_id, $product_country_id, $city_code, $stock) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $cityRec = $fn->getRecordByCondition('city', "city_code= '{$city_code}'");
        if (!is_array($cityRec)){
            return;
        }

        $city_id = $cityRec['city_id'];

        $rec = $fn->getRecordByCondition('product_city', "product_id = {$product_id}
                                            AND product_country_id = {$product_country_id}
                                            AND city_id = {$city_id}
                                        ");

        $fa['product_id'] = $product_id;
        $fa['city_id']    = $city_id;
        $fa['stock']      = $stock;
        $fa['product_country_id'] = $product_country_id;

        if(!is_array($rec)){
            $fa['published'] = 1;
            $fa = $fn->addCreationDetailsToFieldsArray($fa, 'product_city');
            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'product_city');
            $result = $db->sql_query($SQL);

        } else {
            $fa = $fn->addModificationDetailsToFieldsArray($fa, 'product_city');
            $product_city_id = $rec['product_city_id'];
            $whereCondition = "WHERE product_city_id = {$product_city_id}";
            $SQL    = $dbUtil->getUpdateSQLStringFromArray($fa, 'product_city', $whereCondition);
            $result = $db->sql_query($SQL);
        }
        
        //print "<pre>{$SQL}</pre><hr />";
    }

    /**
     * 
     * @param Object $phpExcel
     * @return string
     */
    function getImportStockThresholdData(){
        $fn = Zend_Registry::get('fn');
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');

        if (!$phpExcel->getUploadedFile()){
            return;
        }
        $phpExcel->setWorksheetObj();
        $phpExcel->setFieldsColName();
        

        for ($curRow = 2; $curRow <= $phpExcel->countRows; $curRow++) {
            $lot =  substr($phpExcel->getExcelFieldValue('Vintage', $curRow), 0, 2);
            $item = substr($phpExcel->getExcelFieldValue('Item', $curRow), 0, 5);

            $product_code = $item . $lot;

            if ($product_code == ''){
                continue;
            }

            /**** check for record existance by querying against the match fields ****/
            $rec = $fn->getRecordByCondition('product', "product_code = '{$product_code}'");

            if(is_array($rec)){
                $product_id = $rec['product_id'];
                $this->updateStockThreshold($product_id, $phpExcel, $curRow);
            }

        }

        return "
        <script>
           window.opener.location = window.opener.location;
        </script>
        <div class='left'>
            <h1>Import Completed. Please <strong><a href='javascript:window.close();'>close</a></strong> this window</h1>
        </div>
        ";
    }    
    
    /**
     * 
     * @param type $product_id
     * @param type $phpExcel
     * @param type $curRow
     */
    private function updateStockThreshold($product_id, $phpExcel, $curRow){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        /********** COUNTRIES *************/
        $SQL = "
        SELECT *
        FROM country
        ";
        $countryArr = $dbUtil->getSQLResultAsArray($SQL);

        foreach ($countryArr AS $countryRec){
            $country_id = $countryRec['country_id'];
            $productCountryRec = $fn->getRecordByCondition('product_country', "product_id = {$product_id} AND country_id = {$country_id}");
            if(!is_array($productCountryRec)){//if product_country record doesn't exists
                $fa = array();
                $fa['product_id'] = $product_id;
                $fa['country_id'] = $country_id;
                $fa['published']  = 1;
                $fa = $fn->addCreationDetailsToFieldsArray($fa, 'product_country');
                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'product_country');
                $result = $db->sql_query($SQL);
                $product_country_id = $db->sql_nextid();                
            } else {
                $product_country_id = $productCountryRec['product_country_id'];                
            }           
            /********** CITIES *************/
            $SQL = "
            SELECT *
            FROM city
            WHERE country_id = {$countryRec['country_id']}
            ";
            $cityArr = $dbUtil->getSQLResultAsArray($SQL);

            foreach ($cityArr AS $cityRec){
                $thresholdFldName = $cityRec['city_code'] . 'DC1'; /*** ex: SHDC1 ***/
                $threshold_value = $phpExcel->getExcelFieldValue($thresholdFldName, $curRow);
                if($threshold_value != ''){
                    $this->setCityStockThreshold($product_id, $product_country_id, $cityRec['city_code'], $threshold_value);
                }
            }
        }        
    }

    /**
     *
     */
    private function setCityStockThreshold($product_id, $product_country_id, $city_code, $stock_threshold) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $cityRec = $fn->getRecordByCondition('city', "city_code= '{$city_code}'");
        if (!is_array($cityRec)){
            return;
        }

        $city_id = $cityRec['city_id'];

        $rec = $fn->getRecordByCondition('product_city', "product_id = '{$product_id}'
                                            AND product_country_id = '{$product_country_id}'
                                            AND city_id = '{$city_id}'
                                        ");

        $fa['product_id'] = $product_id;
        $fa['city_id']    = $city_id;
        $fa['stock_threshold']    = $stock_threshold;
        $fa['product_country_id'] = $product_country_id;

        if(!is_array($rec)){
            $fa['published'] = 1;
            $fa = $fn->addCreationDetailsToFieldsArray($fa, 'product_city');
            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'product_city');
            $result = $db->sql_query($SQL);

        } else {
            $fa = $fn->addModificationDetailsToFieldsArray($fa, 'product_city');
            $product_city_id = $rec['product_city_id'];
            $whereCondition = "WHERE product_city_id = {$product_city_id}";
            $SQL    = $dbUtil->getUpdateSQLStringFromArray($fa, 'product_city', $whereCondition);
            $result = $db->sql_query($SQL);
        }
        //print $SQL."<hr/>";
    }

    
    /**
     *
     */
    function getExportFaultyRecord($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'product_code'    => $phpExcel->getFldObj('Product Code')
             ,'title'           => $phpExcel->getFldObj('Title')
             ,'category_title'  => $phpExcel->getFldObj('Category')
             ,'sub_category_title' => $phpExcel->getFldObj('Sub Category')
             ,'country_name'    => $phpExcel->getFldObj('Country')
             ,'region_name'     => $phpExcel->getFldObj('Region')
             ,'appellation_title' => $phpExcel->getFldObj('Appellation')
             ,'brand_code'      => $phpExcel->getFldObj('Brand Code')
             ,'color'           => $phpExcel->getFldObj('Color')
             ,'grape'           => $phpExcel->getFldObj('Grape')
             ,'vintage'         => $phpExcel->getFldObj('Vintage')
             ,'producer_title'  => $phpExcel->getFldObj('Producer')
             ,'bottle_size'     => $phpExcel->getFldObj('Bottle Size')
             ,'published'       => $phpExcel->getFldObj('Published')
             ,'description_short' => $phpExcel->getFldObj('Short Description')
             ,'special_offer_description' => $phpExcel->getFldObj('Special Offer Description')
             ,'description' => $phpExcel->getFldObj('Description')                
             ,'product_code_fault'  => $phpExcel->getFldObj('Product Code Fault')
             ,'title_fault'         => $phpExcel->getFldObj('Title Fault')
             ,'category_id_fault'   => $phpExcel->getFldObj('Category Fault')
             ,'sub_category_id_fault'   => $phpExcel->getFldObj('Sub Category Fault')
             ,'country_code_fault'   => $phpExcel->getFldObj('Country Fault')
             ,'region_id_fault'      => $phpExcel->getFldObj('Region Fault')
             ,'brand_code_fault'     => $phpExcel->getFldObj('Brand Code Fault')
             ,'color_fault'      => $phpExcel->getFldObj('Color Fault')
             ,'grape_fault'      => $phpExcel->getFldObj('Grape Fault')
             ,'vintage_fault'    => $phpExcel->getFldObj('Vintage Fault')
             ,'producer_fault'   => $phpExcel->getFldObj('Producer Fault')
             ,'bottle_size_fault'=> $phpExcel->getFldObj('Bottle Size Fault')
             ,'description_short_fault'=> $phpExcel->getFldObj('Short Desc Fault')
             ,'pic_ref_fault'    => $phpExcel->getFldObj('Pic Ref. Fault')
        );
//             ,'description_fault'=> $phpExcel->getFldObj('Long Desc Fault')

        $file_name = "Product_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }
    
   /**
     * Use this function with care
     * http://pudao.localhost/admin/index.php?_spAction=wipeProductForImport&module=wine_product&showHTML=0
     * http://pudao.testpilotweb.com/admin/index.php?_spAction=wipeProductForImport&module=wine_product&showHTML=0
     */
    function getWipeProductForImport(){
        $dbz = Zend_Registry::get('dbz');
        $media = Zend_Registry::get('media');
        set_time_limit(20000);
        ini_set('memory_limit', '1536M');

        if(CP_ENV == 'production' || ($_SERVER['REMOTE_ADDR'] != '116.48.134.144' && $_SERVER['REMOTE_ADDR'] != '127.0.0.1')){
            return "Invalid Access";
        }
        
        $SQLTruncate = "TRUNCATE TABLE rating";
        $dbz->query($SQLTruncate);
        $SQLTruncate = "TRUNCATE TABLE tasting_notes";
        $dbz->query($SQLTruncate);
        $SQLTruncate = "TRUNCATE TABLE appellation";
        $dbz->query($SQLTruncate);
        $SQLTruncate = "TRUNCATE TABLE region";
        $dbz->query($SQLTruncate);
        $SQLTruncate = "TRUNCATE TABLE product";
        $dbz->query($SQLTruncate);
        $SQLTruncate = "TRUNCATE TABLE product_country";
        $dbz->query($SQLTruncate);
        $SQLTruncate = "TRUNCATE TABLE product_city";
        $dbz->query($SQLTruncate);
        
        $SQLMedia = "
        SELECT media_id FROM `media` WHERE room_name = 'wine_product'    
        ";
        $stmt = $dbz->query($SQLMedia);
        
        while($row = $stmt->fetch()){
            $this->deleteProductImage($row['media_id']);
        }
        
        return "Product & its related records are wiped";
    }     
}
