<?
class CP_Admin_Modules_Trading_PurchaseOrder_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('trading_purchaseOrder');
        $modules->registerModule($modObj, array(
            'tableName' => 'purchase_order'
           ,'keyField' => 'purchase_order_id'
           ,'title' => 'Purchase Order'
           ,'relatedTables' => array('purchase_order_items')
           ,'actBtnsList' => array('new')
           ,'actBtnsDetail' => array('tradingPrintPO', 'edit', 'delete', 'duplicate')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $mediaObj = $mediaArr->getMediaObj('trading_purchaseOrder', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj);
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $linkObj = $inst->getLinksArrayObj('trading_purchaseOrder', 'trading_productLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'purchase_order_items'
           ,'hasPortalEdit' => 1
           ,'portalDialogHeight' => 700
           ,'anchorFieldsArr' => array('product_code' => $inst->getLinkAnchorObj('product_code', 'product_id'))
           //,'editLinkItemValidateJsMethod' => 'cpm.trading.purchaseOrder.validateEditProductItemLink'
           ,'fieldlabel' => array('PO Line Number'
                                ,'Product Code'
                                ,'Web Code'
                                ,'Product Name'
                                ,'UOM'
                                ,'Order Quantity'
                                ,'Buy Currency'
                                ,'Buy Unit Price'
                                ,'Total Buy Price'
                                ,'Delivered Quantity'
                                ,'Status'
                                ,''
               )
            ,'fieldClassArray' => array(
                 5 => 'al-right'
                ,7 => 'al-right'
                ,8 => 'al-right'
            )
           ,'summaryFieldsArray' => array(
               'quantity'
              ,'buy_price'
            )
        ));
    }

    function setReportsArray($repInst) {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $record_id = (int) $fn->getReqParam('record_id', 0);
        $report = $fn->getReqParam('report');

        $repInst->setReportArrayObj('trading_purchaseOrder', 'purchaseOrder');
        $arr = &$repInst->reportsArray['trading_purchaseOrder']['purchaseOrder'];
        $arr['jasperFileName'] = 'purchaseOrder.jasper';
    }

}