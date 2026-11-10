<?
class CP_Admin_Modules_EnggCrm_PurchaseOrder_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('enggCrm_purchaseOrder');
        $modules->registerModule($modObj, array(
            'tableName' => 'purchase_order'
           ,'keyField' => 'purchase_order_id'
           ,'title' => 'Purchase Order'
           ,'relatedTables' => array('purchase_order_items')
           #,'actBtnsList' => array('printPOPDFList', 'import')
           ,'actBtnsList' => array('import')
           ,'actBtnsDetail' => array('edit')
           ,'actBtnsEdit' => array('save', 'apply', 'cancel')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $mediaObj = $mediaArr->getMediaObj('enggCrm_purchaseOrder', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj);
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $linkObj = $inst->getLinksArrayObj('enggCrm_purchaseOrder', 'tradingsg_productLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'po_product'
           ,'historyTableKeyField' => 'po_product_id'
           ,'hasPortalEdit' => 0
           ,'hasPortalDetail' => 1
           ,'hasPortalDelete' => 0
           ,'hasPortalNew'=> 0
           ,'linkingType' => 'grid'
           ,'fieldlabel' => array('Item Ref Code'
                                ,'Product'
                                ,'Cost Price'
                                ,'Quantity'
                                ,'Base Price'
                                ,'Exchange Rate'
            )
           ,'gridFieldTypeArray'  => array(
                array('type' => 'textbox', 'editable' => 0)
               ,array('type' => 'textbox', 'editable' => 0)
               ,array('type' => 'textbox', 'editable' => 0)
               ,array('type' => 'textbox', 'editable' => 0)
               ,array('type' => 'textbox', 'editable' => 0)
               ,array('type' => 'textbox', 'editable' => 0)
           )
        ));
    }

    function setReportsArray($repInst) {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $record_id = (int) $fn->getReqParam('record_id', 0);
        $report = $fn->getReqParam('report');

        $repInst->setReportArrayObj('enggCrm_purchaseOrder', 'purchaseOrder');
        $arr = &$repInst->reportsArray['enggCrm_purchaseOrder']['purchaseOrder'];
        $arr['jasperFileName'] = 'purchaseOrder.jasper';
    }

}