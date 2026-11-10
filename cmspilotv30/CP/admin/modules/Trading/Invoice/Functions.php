<?
class CP_Admin_Modules_Trading_Invoice_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('trading_invoice');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('export')
           ,'actBtnsDetail' => array('tradingPrintInvoice'
                                    ,'tradingPrintDeliveryNote'
                                    ,'edit', 'delete')
           ,'relatedTables' => array('invoice_items')
           ,'title'         => 'Invoice'
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $mediaObj = $mediaArr->getMediaObj('trading_invoice', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $linkObj = $inst->getLinksArrayObj('trading_invoice', 'trading_productLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'invoice_items'
           ,'linkingType' => 'portal'
           ,'hasPortalEdit' => 1
           ,'hasPortalDetail' => 1
           ,'portalDialogHeight' => 500
           ,'anchorFieldsArr' => array(
                 'product_code' => $inst->getLinkAnchorObj('product_code', 'product_id')
           )
           ,'fieldlabel' => array('Line Number'
                                 ,'Product Code'
                                 ,'Web Code'
                                 ,'Product Name'
                                 ,'Quantity'
                                 ,'Sell Currency'
                                 ,'Unit Sell Price'
                                 ,'Total Sell Price'
                                 ,'Invoice %'
                                 ,'Invoice Amount'
                                 ,'Status'
                               )
            ,'fieldClassArray' => array(
                 4 => 'al-right'
                ,6 => 'al-right'
                ,7 => 'al-right'
                ,9 => 'al-right'
            )
           ,'summaryFieldsArray' => array(
               'quantity'
              ,'sell_price_total'
            )
        ));

        $linkObj = $inst->getLinksArrayObj('trading_invoice', 'trading_inventoryLink');
        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'invoice_inventory'
           ,'linkingType' => 'portal'
           ,'hasPortalEdit' => 0
           ,'hasPortalDetail' => 0
           ,'portalDialogHeight' => 500
           ,'anchorFieldsArr' => array(
               'product_code' => $inst->getLinkAnchorObj('product_code', 'product_id',
                                                         true, 'trading_product')
              ,'serial_no' => $inst->getLinkAnchorObj('serial_no', 'inventory_id')
           )
           ,'fieldlabel' => array('Product Code'
                                 ,'Web Code'
                                 ,'Inventory<br>Serial'
                                 ,'Product Name'
                                 ,'UOM'
                                 ,'Sell Currency'
                                 ,'Sell Price'
                                 ,'Sell Price Actual'
                                 ,'Status'
                               )
            ,'fieldClassArray' => array(
                 6 => 'al-right'
                ,7 => 'al-right'
            )
           ,'summaryFieldsArray' => array(
               'sell_unit_price'
              ,'sell_unit_price_actual'
            )
           ,'hideFieldsArray' => array(
              'inventory_id'
            )
        ));
    }

    function setReportsArray($repInst) {
        $fn = Zend_Registry::get('fn');

        $repInst->setReportArrayObj('trading_invoice', 'invoice');
        $arr = &$repInst->reportsArray['trading_invoice']['invoice'];
        $arr['jasperFileName'] = 'invoice.jasper';

        $repInst->setReportArrayObj('trading_invoice', 'invoiceInventory');
        $arr = &$repInst->reportsArray['trading_invoice']['invoiceInventory'];
        $arr['jasperFileName'] = 'invoiceInventory.jasper';

        $repInst->setReportArrayObj('trading_invoice', 'invoiceInventorySerial');
        $arr = &$repInst->reportsArray['trading_invoice']['invoiceInventorySerial'];
        $arr['jasperFileName'] = 'invoiceInventorySerial.jasper';

        $repInst->setReportArrayObj('trading_invoice', 'deliveryNote');
        $arr = &$repInst->reportsArray['trading_invoice']['deliveryNote'];
        $arr['jasperFileName'] = 'deliveryNote.jasper';

        $repInst->setReportArrayObj('trading_invoice', 'deliveryNoteInventory');
        $arr = &$repInst->reportsArray['trading_invoice']['deliveryNoteInventory'];
        $arr['jasperFileName'] = 'deliveryNoteInventory.jasper';
    }


}