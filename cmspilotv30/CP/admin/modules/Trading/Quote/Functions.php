<?
class CP_Admin_Modules_Trading_Quote_Functions
{
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('trading_quote');
        $modules->registerModule($modObj, array(
            'relatedTables' => array('quote_items')
           ,'actBtnsList' => array()
           ,'actBtnsDetail' => array('tradingPrintQuote'
                                    ,'tradingDuplicateQuote'
                                    ,'tradingRaiseSO'
                                    ,'edit'
                                    ,'delete'
            )
           ,'actBtnsEdit' => array('tradingDuplicateQuote', 'save', 'apply', 'cancel', 'delete')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('trading_quote', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    //==================================================================//
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $cpUtil = Zend_Registry::get('cpUtil');

        $status     = $fn->getReqParam('status');
        $enquiry_id = $fn->getReqParam('enquiry_id');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "q.quote_id = {$tv['record_id']}";
        }

        if ($status != ''){
            $searchVar->sqlSearchVar[] = "q.status = '{$status}'";
        }
        if ($enquiry_id != ''){
            $searchVar->sqlSearchVar[] = "q.enquiry_id = {$enquiry_id}";
        }
        $searchVar->sortOrder = "q.creation_date DESC";

    }

    /**
     *
     */
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $linkObj = $inst->getLinksArrayObj('trading_quote', 'trading_productLink', array(
            'historyTableName' => 'quote_items'
           ,'hasPortalEdit' => 1
           ,'hasPortalDetail' => 1
           ,'hasPortalDelete' => 0
           ,'hasPortalNew'=> 0
           ,'linkingType' => 'portal'
           ,'recordTypeForHistory' => 'product'
           ,'portalDialogWidth' => 800
           ,'portalDialogHeight' => 700
           ,'showAnchorInLinkPortal' => false
           ,'anchorFieldsArr' => array(
               'product_name' => $inst->getLinkAnchorObj('product_name', 'product_id')
           )
           ,'fieldlabel' => array('Line #'
                                 ,'Product Code'
                                 ,'Web Code'
                                 ,'Product Name'
                                 ,'Qty'
                                 ,'UOM'
                                 ,'Buy Curr'
                                 ,'Buy Unit Price'
                                 ,'Buy Price'
                                 ,'Sell Curr'
                                 ,'Sell Unit Price'
                                 ,'Sell Price'
                                 ,'Status'
                                 ,'Rec Type'
                            )
            ,'fieldClassArray' => array(
                 4 => 'al-right'
                ,7 => 'al-right'
                ,8 => 'al-right'
                ,10 => 'al-right'
                ,11 => 'al-right'
            )
           ,'summaryFieldsArray' => array(
               'quantity'
              ,'buy_price'
              ,'sell_price'
            )
        ));
        $inst->registerLinksArray($linkObj);

        //-------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('trading_quote', 'trading_inventoryLink', array(
            'historyTableName' => 'quote_items'
           ,'hasPortalEdit' => 1
           ,'hasPortalDetail' => 1
           ,'hasPortalDelete' => 0
           ,'hasPortalNew'=> 0
           ,'linkingType' => 'portal'
           ,'recordTypeForHistory' => 'product'
           ,'portalDialogWidth' => 800
           ,'portalDialogHeight' => 600
           ,'anchorFieldsArr' => array(
               'product_name' => $inst->getLinkAnchorObj('product_name', 'product_id')
           )
           ,'fieldlabel' => array('Line #'
                                 ,'Product Code'
                                 ,'Product Name'
                                 ,'Qty'
                                 ,'UOM'
                                 ,'Buy Curr'
                                 ,'Buy Unit Price'
                                 ,'Buy Price'
                                 ,'Sell Curr'
                                 ,'Sell Unit Price'
                                 ,'Sell Price'
                                 ,'Markup %'
                                 ,'Status'
                                 ,'Rec Type'
                            )
           ,'summaryFieldsArray' => array(
               'quantity'
              ,'buy_price'
              ,'sell_price'
            )
        ));
        $inst->registerLinksArray($linkObj);

    }

    function setReportsArray($repInst) {

        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $record_id = (int) $fn->getReqParam('record_id', 0);
        $report = $fn->getReqParam('report');

        $repInst->setReportArrayObj('trading_quote', 'quote');
        $arr = &$repInst->reportsArray['trading_quote']['quote'];
        $arr['jasperFileName'] = 'quote.jasper';
    }
}