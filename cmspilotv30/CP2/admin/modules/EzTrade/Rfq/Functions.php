<?
class CP_Admin_Modules_EzTrade_Rfq_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('ezTrade_rfq');
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

        $mediaObj = $mediaArr->getMediaObj('ezTrade_rfq', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {

        $linkObj = $inst->getLinksArrayObj('ezTrade_rfq', 'ezTrade_productLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'quote_request_items'
           ,'hasPortalEdit'          => 1
           ,'hasPortalDetail'        => 1
           ,'hasPortalDelete'        => 0
           ,'hasPortalNew'           => 0
           ,'linkingType'           => 'portal'
           ,'portalDialogWidth'      => 600
           ,'portalDialogHeight'     => 650
           ,'anchorFieldsArr' => array('product_code' => $inst->getLinkAnchorObj('product_code', 'product_id'))
           ,'fieldlabel' => array('RFQ Line #'
                                 ,'Item Number'
                                 ,'Item Name'
                                 ,'UOM'
                                 ,'Request Quantity'
                                 ,'Buy Unit Price'
                                 ,'Buy Price'
                                 ,'RFQ Status'
                            )
           ,'summaryFieldsArray' => array(
               'quantity'
              ,'buy_price'
            )
        ));

    }
}