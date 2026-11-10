<?
class CP_Admin_Modules_Pos_Product_Model extends CP_Common_Lib_ModuleModelAbstract
{

    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT p.*
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
        $category     = $fn->getReqParam('category');
        $sub_category = $fn->getReqParam('sub_category');
        $special_search  = $fn->getReqParam('special_search');
        $style     = $fn->getReqParam('style');
        $color     = $fn->getReqParam('color');
        $size      = $fn->getReqParam('size');
        $season    = $fn->getReqParam('season');
        $brand     = $fn->getReqParam('brand');

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
            
            if ($tv['subCat'] != '' ) {
                $searchVar->sqlSearchVar[] = "p.sub_category_id = '{$tv['subCat']}'";
            }

            if ($style != '' ) {
                $searchVar->sqlSearchVar[] = "p.style = '{$style}'";
            }

            if ($color != '' ) {
                $searchVar->sqlSearchVar[] = "p.color = '{$color}'";
            }

            if ($size != '' ) {
                $searchVar->sqlSearchVar[] = "p.size = '{$size}'";
            }

            if ($season != '' ) {
                $searchVar->sqlSearchVar[] = "p.season = '{$season}'";
            }

            if ($brand != '' ) {
                $searchVar->sqlSearchVar[] = "p.brand = '{$brand}'";
            }

            if ($tv['srcRoom'] == 'pos_package' ) {
                $searchVar->sqlSearchVar[] = "p.allow_package = 1";
            }

            if ($tv['srcRoom'] == 'pos_discount' ) {
                $searchVar->sqlSearchVar[] = "p.allow_discount = 1";
            }

            if ($tv['srcRoom'] == 'pos_redeem' ) {
                $searchVar->sqlSearchVar[] = "p.redeem = 1";
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
                    p.sku LIKE '%{$tv['keyword']}%'
                    OR p.bar_code  LIKE '%{$tv['keyword']}%'
                    OR p.title  LIKE '%{$tv['keyword']}%'
                    OR p.alias_name  LIKE '%{$tv['keyword']}%'
                    OR p.tag_name  LIKE '%{$tv['keyword']}%'
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
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');
        $tv = Zend_Registry::get('tv');

        $validate->resetErrorArray();

        if ($tv['lang'] == 'eng') {
            $validate->validateData('title', 'Please enter the title');
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
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $record_id = $fn->getReqParam('product_id');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();

        $style = $fn->getReqParam('style');
        $color = $fn->getReqParam('color');
        $size = $fn->getReqParam('size');
        $season = $fn->getReqParam('season');
        $brand = $fn->getReqParam('brand');
        $element = $fn->getReqParam('element');

        $sku_order = $fn->getSettingsValueByKey('prodSkuLayoutOrder');
        $orderArr = explode(',', $sku_order);
        
        $sku_no = '';
        $sep = $fn->getSettingsValueByKey('prodSkuSeparator');
        
        foreach($orderArr as $fld){
            $fld = trim($fld);
        
            if($fld == 'Style' && $cpCfg['prodEnableStyle'] == 1 && $style != ''){
                $sku_no .= $style . $sep;
            }
        
            if($fld == 'Color' && $cpCfg['prodEnableColor'] == 1 && $color != ''){
                $sku_no .= $color . $sep;
            }

            if($fld == 'Size' && $cpCfg['prodEnableSize'] == 1 && $size != ''){
                $sku_no .= $size . $sep;
            }
        
            if($fld == 'Season' && $cpCfg['prodEnableSeason'] == 1 && $season != ''){
                $sku_no .= $season . $sep;
            }

            if($fld == 'Brand' && $cpCfg['prodEnableBrand'] == 1 && $brand != ''){
                $sku_no .= $brand . $sep;
            }
        
            if($fld == 'Element' && $cpCfg['prodEnableElement'] == 1 && $element != ''){
                $sku_no .= $element . $sep;
            }
        }
        
        $fa['sku'] = $sku_no . $record_id;

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
        $fa = $fn->addToFieldsArray($fa, 'currency');
        $fa = $fn->addToFieldsArray($fa, 'expiry_date_from');
        $fa = $fn->addToFieldsArray($fa, 'expiry_date_to');

        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'bar_code');
        $fa = $fn->addToFieldsArray($fa, 'sku');
        $fa = $fn->addToFieldsArray($fa, 'tag_name');
        $fa = $fn->addToFieldsArray($fa, 'alias_name');
        $fa = $fn->addToFieldsArray($fa, 'style');
        $fa = $fn->addToFieldsArray($fa, 'color');
        $fa = $fn->addToFieldsArray($fa, 'size');
        $fa = $fn->addToFieldsArray($fa, 'season');
        $fa = $fn->addToFieldsArray($fa, 'brand');
        $fa = $fn->addToFieldsArray($fa, 'element');
        $fa = $fn->addToFieldsArray($fa, 'currency');
        $fa = $fn->addToFieldsArray($fa, 'regional');
        $fa = $fn->addToFieldsArray($fa, 'reorder_level');
        $fa = $fn->addToFieldsArray($fa, 'uom_code');
        $fa = $fn->addToFieldsArray($fa, 'redeem');
        $fa = $fn->addToFieldsArray($fa, 'fixed_price');
        $fa = $fn->addToFieldsArray($fa, 'allow_discount');
        $fa = $fn->addToFieldsArray($fa, 'allow_member_discount');
        $fa = $fn->addToFieldsArray($fa, 'allow_package');
        $fa = $fn->addToFieldsArray($fa, 'allow_gift');
        $fa = $fn->addToFieldsArray($fa, 'status');

        return $fa;
    }

    /**
     *
     */
    function getPosProductPosProductItemLinkSQL($id) {

        $SQL = "
        SELECT pi.product_item_id
              ,pi.sku_no
              ,pi.barcode
              ,pi.style_id
              ,pi.color_id
              ,pi.size_id
              ,pi.season_id
              ,pi.element_id
              ,pi.brand_id
        FROM product_item pi
        WHERE pi.product_id = {$id}
        ";

        return $SQL;
    }

    /**
     *
     */
    function getPosProductPosShopLinkSQL($id) {

        $SQL = "
        SELECT ps.product_shop_id
              ,ps.shop_id
              ,ps.list_price
              ,ps.currency
        FROM product_shop ps
        WHERE ps.product_id = {$id}
        ";

        return $SQL;
    }

    /**
     *
     */
    function getUpdateCurrency(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $product_shop_id = $fn->getReqParam('product_shop_id');        
        $shop_id = $fn->getReqParam('shop_id');        

        $shop = $fn->getRecordRowByID('shop', 'shop_id', $shop_id);

        $SQL    = "
        UPDATE product_shop 
        set currency = '{$shop['currency']}' 
        WHERE product_shop_id = {$product_shop_id}
        "; 
        $result = $db->sql_query($SQL);        
    }
}
