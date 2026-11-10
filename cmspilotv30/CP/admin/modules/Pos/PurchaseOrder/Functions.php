<?
class CP_Admin_Modules_Pos_PurchaseOrder_Functions
{
    /**
     *
     */
    function setModuleArray($modules){



        $modObj = $modules->getModuleObj('pos_purchaseOrder');

       // <a class='button' href='{$urlAttendance}' id='printAttendance'>Print Attendance Format</a>

        $modules->registerModule($modObj, array(
            'tableName' => 'purchase_order'
           ,'keyField' => 'purchase_order_id'
           ,'title' => 'Purchase Order'
           ,'relatedTables' => array('purchase_order_items')
           ,'actBtnsList' => array('new')
           ,'actBtnsEdit' => array('save', 'apply', 'cancel', 'delete', 'duplicate', 'delivery')
           ,'actBtnsDetail' => array('tradingPrintPO', 'edit', 'delete', 'duplicate', 'delivery')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $mediaObj = $mediaArr->getMediaObj('pos_purchaseOrder', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj);
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $linkObj = $inst->getLinksArrayObj('pos_purchaseOrder', 'trading_productLink');

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
        //-------------------------- extra ---------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('pos_purchaseOrder', 'pos_staffLink');
        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'staff'
           ,'displayTitleFieldName' => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'linkMultiple'          => 0
        ));
	   //------------------------------------------------------------------------------//

        $linkObj = $inst->getLinksArrayObj('pos_purchaseOrder', 'pos_shopLink');
        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'shop'
           ,'linkMultiple'          => 0
        ));
	   //------------------------------------------------------------------------------//

        $linkObj = $inst->getLinksArrayObj('pos_purchaseOrder', 'pos_vendorLink');
        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'vendor'
           ,'linkMultiple'          => 0
        ));
	   //------------------------------------------------------------------------------//

        $linkObj = $inst->getLinksArrayObj('pos_purchaseOrder', 'pos_warehouseLink');
        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'warehouse'
           ,'linkMultiple'          => 0
        ));

        //-------------------------- extra ---------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('pos_purchaseOrder', 'pos_productLink');
        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'product'
           ,'displayTitleFieldName' => 'a.title'
           ,'linkMultiple'          => 0
        ));
	   //------------------------------------------------------------------------------//
    }

    function setReportsArray($repInst) {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $record_id = (int) $fn->getReqParam('record_id', 0);
        $report = $fn->getReqParam('report');

        $repInst->setReportArrayObj('pos_purchaseOrder', 'purchaseOrder');
        $arr = &$repInst->reportsArray['pos_purchaseOrder']['purchaseOrder'];
        $arr['jasperFileName'] = 'purchaseOrder.jasper';
    }

}