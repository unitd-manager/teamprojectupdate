<?
class CP_Admin_Modules_EzTrade_Invoice_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('ezTrade_invoice');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array()
           ,'actBtnsDetail' => array('tradingPrintInvoice', 'edit', 'delete')
           ,'relatedTables' => array('invoice_items')
           ,'title'         => 'Invoice'
        ));
    }

    //==================================================================//
    /**
     *
     */
    function getQuickSearch() {
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $mediaObj = $mediaArr->getMediaObj('ezTrade_invoice', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));

    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $cpUtil = Zend_Registry::get('cpUtil');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "i.invoice_id = {$tv['record_id']}";
        }

        $searchVar->sortOrder = "i.creation_date DESC";

    }

    /**
     *
     */
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $linkObj = $inst->getLinksArrayObj('ezTrade_invoice', 'ezTrade_productLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'invoice_items'
           ,'hasPortalEdit' => 1
           ,'hasPortalDetail' => 1
           ,'portalDialogHeight' => 500
           ,'anchorFieldsArr' => array(
                 'product_code' => $inst->getLinkAnchorObj('product_code', 'product_id')
           )
           ,'fieldlabel' => array('Line Number'
                                 ,'Item Number'
                                 ,'Item Name'
                                 ,'UOM'
                                 ,'Quantity'
                                 ,'Sell Currency'
                                 ,'Unit Sell Price'
                                 ,'Total Sell Price'
                                 ,'Invoice %'
                                 ,'Invoice Amount'
                                 ,'Status'
                               )
           ,'summaryFieldsArray' => array(
               'quantity'
              ,'sell_price_total'
            )
        ));

    }

    /**
     *
     */
    /**
     *
     */
    function getInvoiceStatusArray() {
        $arr = array('new'
                    ,'open'
                    ,'closed'
                    ,'on hold'
                    ,'cancelled'
               );
        return $arr;
    }

}