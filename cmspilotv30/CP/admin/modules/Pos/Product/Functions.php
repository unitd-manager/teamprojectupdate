<?
class CP_Admin_Modules_Pos_Product_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('pos_product');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
           ,'hasFlagInList' => 0
           ,'actBtnsList' => array('new', 'printListScreen')
        ));
    }


    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');

        $product_id   = $fn->getReqParam('product_id');
        $category     = $fn->getReqParam('category');
        $sub_category = $fn->getReqParam('sub_category');
        $special_search  = $fn->getReqParam('special_search');

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
                $searchVar->sqlSearchVar[] = "c.category_id = '{$tv['category_id']}'";
            }
    
            if ($tv['sub_category_id'] != '' ) {
                $searchVar->sqlSearchVar[] = "sc.sub_category_id = '{$tv['sub_category_id']}'";
            }
    
            if ($tv['record_id'] != '') {
                $searchVar->sqlSearchVar[] = "p.product_id = '{$tv['record_id']}'";
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
                $searchVar->sqlSearchVar[] = "( p.title        LIKE '%{$tv['keyword']}%'  OR
                                                p.description  LIKE '%{$tv['keyword']}%'
                                              )";
            }
        }
    }

    /**
     *
     */
    function getSubCatValueSql($type) {
        $db = Zend_Registry::get('db');

        $SQL = "
        SELECT v.valuelist_id
              ,v.code
        FROM valuelist v
        LEFT JOIN (sub_category_valuelist sv) ON (sv.valuelist_id = v.valuelist_id)
        WHERE sv.record_type = '{$type}'
        ";
        $result = $db->sql_query($SQL);        
    }
    /**
     *
     */
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
                
        $extStyleArr = array();
        $extColorArr = array();
        $extSizeArr = array();
        $extSeasonArr = array();
        $extElementArr = array();
        $extBrandArr = array();

        $expStyle = 0;
        $expColor = 0;
        $expSize = 0;
        $expSeason = 0;
        $expElement = 0;
        $expBrand = 0;
        
        if($cpCfg['prodEnableStyle'] == 1){
            $resultStyle = $this->getSubCatValueSql('Style');
            $extStyleArr = $dbUtil->getResultsetAsArrayForForm($resultStyle);
            $expStyle = 1;
        }

        if($cpCfg['prodEnableColor'] == 1){
            $resultColor = $this->getSubCatValueSql('Color');
            $extColorArr = $dbUtil->getResultsetAsArrayForForm($resultColor);
            $expColor = 1;
        }

        if($cpCfg['prodEnableSize'] == 1){
            $resultSize = $this->getSubCatValueSql('Size');
            $extSizeArr = $dbUtil->getResultsetAsArrayForForm($resultSize);
            $expSize = 1;
        }

        if($cpCfg['prodEnableSeason'] == 1){
            $resultSeason = $this->getSubCatValueSql('Season');
            $extSeasonArr = $dbUtil->getResultsetAsArrayForForm($resultSeason);
            $expSeason = 1;
        }

        if($cpCfg['prodEnableElement'] == 1){
            $resultElement = $this->getSubCatValueSql('Element');
            $extElementArr = $dbUtil->getResultsetAsArrayForForm($resultElement);
            $expElement = 1;
        }

        if($cpCfg['prodEnableBrand'] == 1){
            $resulBrand = $this->getSubCatValueSql('Brand');
            $extBrandArr = $dbUtil->getResultsetAsArrayForForm($resulBrand);
            $expBrand = 1;
        }
        
        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('pos_product', 'pos_productItemLink', array(
            'historyTableName'          => 'product_item'
           ,'historyTableKeyField'      => 'product_item_id'
           ,'showAnchorInLinkPortal' => 0
           ,'hasPortalEdit'          => 0
           ,'hasPortalDelete'        => 1
           ,'linkingType'            => 'grid'
           ,'fieldlabel'             => array(
                 'SKU No'
                ,'Barcode'
                ,'Style'
                ,'Color'
                ,'Size'
                ,'Season'
                ,'Element'
                ,'Brand'
            )
            ,'gridFieldTypeArray'  => array(
                 array('type' => 'textbox', 'editable' => 0)
                ,array('type' => 'textbox')
                ,array('type' => 'dropdown', 'ddArr' => $extStyleArr, 'editable' => $expStyle)
                ,array('type' => 'dropdown', 'ddArr' => $extColorArr, 'editable' => $expColor)
                ,array('type' => 'dropdown', 'ddArr' => $extSizeArr, 'editable' => $expSize)
                ,array('type' => 'dropdown', 'ddArr' => $extSeasonArr, 'editable' => $expSeason)
                ,array('type' => 'dropdown', 'ddArr' => $extElementArr, 'editable' => $expElement)
                ,array('type' => 'dropdown', 'ddArr' => $extBrandArr, 'editable' => $expBrand)
            )
        ));
        $inst->registerLinksArray($linkObj);

        //------------------------------------------------------------------------------//
        $SQL = "SELECT shop_id ,title FROM shop";
        $resultShop = $db->sql_query($SQL);        
        $extShopArr = $dbUtil->getResultsetAsArrayForForm($resultShop);

        $linkObj = $inst->getLinksArrayObj('pos_product', 'pos_shopLink', array(

            'historyTableName'      => 'product_shop'
           ,'historyTableKeyField'  => 'product_shop_id'
           ,'hasPortalDelete'        => 1
           ,'hasPortalEdit'          => 0
           ,'linkingType'           => 'grid'
           ,'fieldlabel'            => array(
                'Shop Code'
               ,'List Price'
               ,'Currency'
            )
            ,'gridFieldTypeArray'  => array(
                 array('type' => 'dropdown', 'ddArr' => $extShopArr)
                ,array('type' => 'textbox')
                ,array('type' => 'textbox', 'editable' => 0)
            )
          /*  ,'additionalFieldsArray' => array(
                 'b.shop_id'
                ,'b.list_price'
                ,'b.currency'
            ) */
        ));
        $inst->registerLinksArray($linkObj);
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('pos_product', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));

    }
}                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  