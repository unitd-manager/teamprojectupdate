<?
class CP_Admin_Modules_Hms_PurchaseOrder_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('hms_purchaseOrder');
        $modObj['tableName'] = 'purchase_order';
        $modObj['keyField']  = 'purchase_order_id';
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new', 'import', 'export')
           ,'actBtnsDetail' => array('edit', 'delete')
           ,'actBtnsEdit'   => array('save', 'apply')
           ,'relatedTables' => array('media')
           ,'title'         => 'Purchase Order'
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

    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('hms_purchaseOrder', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('hms_purchaseOrder', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));

    }
    /**
     *
     */

    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $linkObj = $inst->getLinksArrayObj('hms_purchaseOrder', 'hms_po_productLink');
        $statusArr = $cpCfg['m.hms.purchaseOrder.poProductStatusArr'];

        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'po_product'
           ,'historyTableKeyField' => 'po_product_id'
           ,'hasPortalEdit' => 0
           ,'hasPortalDetail' => 1
           ,'hasPortalDelete' => 0
           ,'hasPortalNew'=> 0
           ,'linkingType' => 'grid'
           ,'fieldlabel' => array('Product'
                                ,'Cost Price'
                                ,'Quantity'
                                ,'Qty Delivered'
                                ,'Status'
            )
           ,'gridFieldTypeArray'  => array(
                array('type' => 'textbox', 'editable' => 0)
               ,array('type' => 'textbox', 'editable' => 0)
               ,array('type' => 'textbox', 'editable' => 0)
               ,array('type' => 'textbox', 'editable' => 0)
               ,array('type' => 'textbox', 'editable' => 0)
               ,array('type' => 'dropdown', 'ddArr' => $statusArr, 'useKey' => 0)
           )
        ));
    }

}