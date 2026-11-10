<?
class CP_Admin_Modules_Tradingsg_StockHistory_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('tradingsg_stockHistory');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1	
           ,'tableName' => 'stock_history'
           ,'keyField' => 'stock_history_id'
           ,'title' => 'Stock History'
           ,'hasFlagInList' => 0
           ,'actBtnsList'   => array('import')
           ,'actBtnsDetail' => array('delete')
        ));
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');

        $stock_history_id   = $fn->getReqParam('stock_history_id');
        $category     = $fn->getReqParam('category');
        $sub_category = $fn->getReqParam('sub_category');
        $special_search  = $fn->getReqParam('special_search');

        if ($stock_history_id != '') {
            $searchVar->sqlSearchVar[] = "p.stock_history_id = {$stock_history_id}";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "p.stock_history_id = {$tv['record_id']}";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'p.stock_history_id');
            
            if($tv['linkName'] == 'product#product'){
                $searchVar->sqlSearchVar[] = "p.stock_history_id != {$tv['linkMasterTableID']}";
            }
            
            if ($tv['category_id'] != '' ) {
                $searchVar->sqlSearchVar[] = "c.category_id = '{$tv['category_id']}'";
            }
    
            if ($tv['sub_category_id'] != '' ) {
                $searchVar->sqlSearchVar[] = "sc.sub_category_id = '{$tv['sub_category_id']}'";
            }
    
            if ($tv['record_id'] != '') {
                $searchVar->sqlSearchVar[] = "p.stock_history_id = '{$tv['record_id']}'";
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
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

     /*   //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('ecommerce_product', 'ecommerce_productLink', array(
             'historyTableName'    => 'related_product'
            ,'keyFieldForHistory'  => 'related_product_id'
            ,'keyField'            => 'product_id'
            ,'keyFieldForLinking'  => 'product_id_rel'
            ,'className'           => 'Product'
            ,'showAnchorInLinkPortal' => 0
            ,'anchorFieldsArr'     => array('title' => $inst->getLinkAnchorObj('title', 'product_id'))
            ,'additionalFieldsArray'  => array(
                 'a.price'
            )
            ,'fieldlabel' => array(
                 'Product Name'
                ,'Category'
            )
        ));
        
        $inst->registerLinksArray($linkObj);
        
        //------------------------------------------------------------------------------//
        if(@$cpCfg['m.ecommerce.product.hasProductItem']){
            $colorArr = $fn->getDdDataAsArray('ecommerce_color');
    
            $linkObj = $inst->getLinksArrayObj('ecommerce_product', 'ecommerce_productItemLink', array(
                 'historyTableName'       => 'product_item'
                ,'linkingType'            => 'grid'
                ,'historyTableKeyField'   => 'product_item_id'
                ,'showLinkPanelInEdit'    => 1
                ,'hasPortalEdit'          => 0
                ,'hasPortalDelete'        => 1
                ,'linkingType'            => 'grid'
                ,'showAnchorInLinkPortal' => false
                ,'fieldlabel'             => array(
                     'SKU No'
                    ,'Color'
                    ,'Size'
                    ,'Stock'
                    ,'Sort'
                )
                ,'gridFieldTypeArray'  => array(
                     array('type' => 'textbox')
                    ,array('type' => 'dropdown', 'ddArr' => $colorArr)
                )
            ));
            $inst->registerLinksArray($linkObj);
        }

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('ecommerce_product', 'ecommerce_productVoucherLink');


        $inst->registerLinksArray($linkObj, array(
             'historyTableName'    => 'product_voucher'
            ,'linkingType'         => 'grid'
            ,'showLinkPanelInNew'  => 0
            ,'keyField'            => 'product_voucher_id'
            ,'showLinkPanelInEdit' => 1
            ,'hasPortalEdit'       => 0
            ,'hasPortalDelete'     => 1
            ,'fieldlabel'          => array('Voucher No', 'Order')
            ,'fieldClassArray'     => array('w100', 'w100')
            ,'gridFieldTypeArray'  => array(
                 array('type' => 'textbox', 'editable' => 0)
                ,array('type' => 'textbox', 'editable' => 0)
            )
            ,'additionalFieldsArray'  => array(
                'b.product_id'
               ,'b.order_id'
            )
            
            ,'showAnchorInLinkPortal' => false
        ));      */  

        //-----------------------------------------------------------------// 
        $linkObj = $inst->getLinksArrayObj('tradingsg_stockHistory', 'tradingsg_companyLink', array( 
            'historyTableName'       => 'stock_history_company' 
           ,'displayTitleFieldName'  => "a.company_name" 
           ,'linkingType'            => 'modal' 
           ,'historyTableKeyField'   => 'stock_history_company_id' 
           ,'showLinkPanelInEdit'    => 1 
           ,'hasPortalEdit'          => 0 
           ,'showAnchorInLinkPortal' => false 
           ,'hasGridEdit'            => 0 
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
        $mediaObj = $mediaArr->getMediaObj('tradingsg_stockHistory', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('tradingsg_stockHistory', 'relatedPicture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

    }
}