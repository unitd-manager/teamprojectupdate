<?
class CP_Admin_Modules_Trading_Rfq_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('trading_rfq');
        $modules->registerModule($modObj, array(
            'tableName' => 'quote_request'
           ,'keyField' => 'quote_request_id'
           ,'relatedTables' => array('media', 'quote_request_items')
           ,'title' => 'RFQ'
           ,'actBtnsList' => array()
           ,'actBtnsDetail' => array('tradingPrintRfq', 'edit', 'delete')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $mediaObj = $mediaArr->getMediaObj('trading_rfq', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {

        $linkObj = $inst->getLinksArrayObj('trading_rfq', 'trading_productLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'quote_request_items'
           ,'hasPortalEdit'          => 1
           ,'hasPortalDetail'        => 1
           ,'hasPortalDelete'        => 0
           ,'hasPortalNew'           => 0
           ,'linkingType'           => 'portal'
           ,'portalDialogWidth'      => 600
           ,'portalDialogHeight'     => 650
           ,'showAnchorInLinkPortal' => false
           ,'anchorFieldsArr' => array('product_code' => $inst->getLinkAnchorObj('product_code', 'product_id'))
           ,'editLinkItemValidateJsMethod' => 'cpm.trading.rfq.editProductLinkValidate'
           ,'fieldlabel' => array('RFQ Line #'
                                 ,'Product Code'
                                 ,'Web Code'
                                 ,'Product Name'
                                 ,'UOM'
                                 ,'Request Quantity'
                                 ,'Buy Unit Price'
                                 ,'Buy Price'
                                 ,'RFQ Status'
                            )
            ,'fieldClassArray' => array(
                 5 => 'al-right'
                ,6 => 'al-right'
                ,7 => 'al-right'
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

        $repInst->setReportArrayObj('trading_rfq', 'rfq');
        $arr = &$repInst->reportsArray['trading_rfq']['rfq'];
        $arr['jasperFileName'] = 'rfq.jasper';

    }

}