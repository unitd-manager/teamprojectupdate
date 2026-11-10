<?
class CP_Admin_Modules_EzTrade_PurchaseOrder_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('ezTrade_purchaseOrder');
        $modules->registerModule($modObj, array(
            'tableName' => 'purchase_order'
           ,'keyField' => 'purchase_order_id'
           ,'title' => 'Purchase Order'
           ,'relatedTables' => array('purchse_order_items')
           ,'actBtnsList' => array('new')
           ,'actBtnsDetail' => array('tradingPrintPO', 'edit', 'delete')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $mediaObj = $mediaArr->getMediaObj('ezTrade_purchaseOrder', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj);
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $linkObj = $inst->getLinksArrayObj('ezTrade_purchaseOrder', 'ezTrade_productLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'purchase_order_items'
           ,'hasPortalEdit' => 1
           ,'portalDialogHeight' => 700
           ,'anchorFieldsArr' => array('product_code' => $inst->getLinkAnchorObj('product_code', 'product_id'))
           ,'fieldlabel' => array('PO Line Number'
                                ,'Item Number'
                                ,'Item Name'
                                ,'UOM'
                                ,'Order Quantity'
                                ,'Buy Currency'
                                ,'Buy Unit Price'
                                ,'Total Buy Price'
                                ,'Delivered Quantity'
                                ,'Status'
               )
           ,'summaryFieldsArray' => array(
               'quantity'
              ,'buy_price'
            )
        ));
    }
}