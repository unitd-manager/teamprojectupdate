<?
class CP_Admin_Modules_EzTrade_Quote_Functions
{
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('ezTrade_quote');
        $modules->registerModule($modObj, array(
            'relatedTables' => array('quote_item')
           ,'actBtnsList' => array()
           ,'actBtnsDetail' => array('tradingPrintQuote', 'tradingDuplicateQuote', 'tradingRaiseSO', 'edit', 'delete')
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
        $mediaObj = $mediaArr->getMediaObj('ezTrade_quote', 'attachment', 'attachment');

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

        $linkObj = $inst->getLinksArrayObj('ezTrade_quote', 'ezTrade_productLink');
        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'quote_items'
           ,'hasPortalEdit'          => 1
           ,'hasPortalDetail'        => 1
           ,'hasPortalDelete'        => 0
           ,'hasPortalNew'           => 0
           ,'linkingType'            => 'portal'
           ,'portalDialogWidth'      => 800
           ,'portalDialogHeight'     => 600
           ,'anchorFieldsArr' => array('product_code' => $inst->getLinkAnchorObj('product_code', 'product_id'))
           ,'fieldlabel' => array('Line #'
                                 ,'Item Number'
                                 ,'Item Name'
                                 ,'Quantity'
                                 ,'UOM'
                                 ,'Buy Currency'
                                 ,'Buy Unit Price'
                                 ,'Buy Price'
                                 ,'Sell Currency'
                                 ,'Sell Unit Price'
                                 ,'Sell Price'
                                 ,'Markup %'
                                 ,'Valid Untill'
                                 ,'Status'
                            )
           ,'summaryFieldsArray' => array(
               'quantity'
              ,'buy_price'
              ,'sell_price'
            )
        ));

    }

    /**
     *
     */
   function getQuoteStatusArray() {
      $arr = array('new'
                  ,'open'
                  ,'closed'
                  ,'on hold'
                  ,'cancelled'
             );

      return $arr;
   }
}