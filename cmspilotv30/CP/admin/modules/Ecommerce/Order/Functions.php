<?
class CP_Admin_Modules_Ecommerce_Order_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('ecommerce_order');
        $modules->registerModule($modObj, array(
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $linkObj = $inst->getLinksArrayObj('ecommerce_order', 'ecommerce_orderItemLink');
        $productArr = $fn->getDdDataAsArray($cpCfg['m.ecommerce.order.itemsMainModule']);

        if($cpCfg['m.ecommerce.product.hasProductItem']){
            $additionalFldsArr = array(
                 'b.item_title'
                ,'b.sku_no'
                ,'b.unit_price'
                ,'b.qty'
                ,'b.qty * b.unit_price'
            );

            $fieldlabelArr = array(
                 'Product'
                ,'SKU No'
                ,'Unit Price'
                ,'Qty'
                ,'Sub-Total'
            );

        } else {
            $additionalFldsArr = array(
                 'b.item_title'
                ,'b.unit_price'
                ,'b.qty'
                ,'b.qty * b.unit_price'
            );

            $fieldlabelArr = array(
                 'Product'
                ,'Unit Price'
                ,'Qty'
                ,'Sub-Total'
            );
        }

        $inst->registerLinksArray($linkObj, array(
             'historyTableName'       => 'order_item'
            ,'linkingType'            => 'grid'
            ,'historyTableKeyField'   => 'order_item_id'
            ,'hasGridEdit'            => false
            ,'hasPortalDelete'        => false
            ,'hasPortalNew'           => false
            ,'fieldlabel'             => $fieldlabelArr
            ,'fieldClassArray'        => array()
            ,'showAnchorInLinkPortal' => false
            ,'gridFieldTypeArray'  => array(
                  array('type' => 'dropdown', 'ddArr' => $productArr)
            )
            ,'additionalFieldsArray' => $additionalFldsArr
            ,'fieldClassArray' => array('', '', 'txtRight', 'txtRight', 'txtRight')
        ));
    }

    //==================================================================//
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('ecommerce_order', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
        
        $mediaObj = $mediaArr->getMediaObj('ecommerce_order', 'label', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
                
    }
}