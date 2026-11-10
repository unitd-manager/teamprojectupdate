<?
class CP_Admin_Modules_Trading_Product_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('trading_product');
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 1
           ,'actBtnsList' => array('new', 'export', 'import', 'tradingProductExportForWeb')
           ,'actBtnsDetail' => array('edit', 'delete', 'duplicate')
        ));
    }

    function setMediaArray($mediaArr) {
        $mediaObj = $mediaArr->getMediaObj('trading_product', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
            'maxWidthL' => 900
           ,'maxHeightL' => 900
           ,'maxWidthT' => 200
           ,'maxHeightT' => 200
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $cpCfg = Zend_Registry::get('cpCfg');
        //$arrayMasterLink = Zend_Registry::get('arrayMasterLink');

        //--------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('trading_product', 'trading_rfqItemsLink', array(
            'historyTableName'    => 'quote_request_items'
           ,'keyFieldForHistory'  => 'quote_request_items_id'
           ,'linkingType'         => 'portal'
           ,'showLinkPanelInNew'  => 0
           ,'showLinkPanelInEdit' => 1
           ,'hasPortalNew'        => 0
           ,'hasPortalEdit'       => 0
           ,'hasPortalDetail'     => 1
           ,'portalDialogHeight'  => 560
           ,'anchorFieldsArr' => array(
               'company_name' => $inst->getLinkAnchorObj('company_name', 'company_id', false, 'trading_company')
              ,'quote_request_line_no' => $inst->getLinkAnchorObj('quote_request_line_no'
                                                                 ,'quote_request_id'
                                                                 ,false
                                                                 ,'trading_rfq'
                                          )
            )
            ,'fieldlabel' => array('RFQ Line #'
                                 ,'Supplier'
                                 ,'Request Quantity'
                                 ,'RFQ Creation Date'
                                 ,'Buy Currency'
                                 ,'Buy Unit Price'
                                 ,"Buy Unit Price ({$cpCfg['m.trading.companyCurrency']})"
                                 ,'Lead Time'
                                 ,'Country of Origin'
                                 ,'Status'
                                 ,'Valid Until'
                                 ,'Choose'
                            )
            ,'fieldClassArray' => array(
                5 => 'al-right'
               ,6 => 'al-right'
            )
        ));
        $inst->registerLinksArray($linkObj);

        //----------------------------------------------------------//
        $exp = array('align' => 'left');
        $linkObj = $inst->getLinksArrayObj('trading_product', 'trading_inventoryLink', array(

            'historyTableName' => 'inventory'
           ,'hasPortalDetail' => 0
           ,'hasPortalEdit' => 0
           ,'hasPortalNew' => 0
           ,'keyFieldForHistory' => 'inventory_id'
           ,'keyFieldForLinking' => 'inventory_id'
           ,'linkingType' => 'portal'
           ,'portalDialogWidth'  => 800
           ,'portalDialogHeight' => 750
           ,'chooseLinkValidateJsMethod' => 'cpm.trading.salesOrder.validateInventoryEditLink'
           ,'anchorFieldsArr' =>
                array('product_code' => $inst->getLinkAnchorObj('product_code', 'inventory_id')
                     ,'product_name' => $inst->getLinkAnchorObj('product_name', 'inventory_id')
                     ,'serial_no' => $inst->getLinkAnchorObj('serial_no', 'inventory_id')
                )
           ,'fieldlabel' => array('Product Code'
                                 ,'Inventory Serial'
                                 ,'Product Name'
                                 ,'Status'
                                 ,'Location'
                                 ,'UOM'
                            )
           ,'fieldsArr' => array('serial_no' => $inst->getFieldObj('serial_no', $exp))
        ));
        $inst->registerLinksArray($linkObj);

        //----------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('trading_product', 'trading_pricingTypeLink', array(
            'historyTableName' => 'product_pricing_type'
           ,'hasPortalDetail' => 0
           ,'hasPortalEdit' => 0
           ,'hasPortalNew' => 0
           ,'linkingType' => 'portal'
           ,'fieldlabel' => array('Pricing Type'
                                 ,'Price'
                                 ,''
                            )
           ,'fieldClassArray' => array(
               1 => 'al-right'
           )
           ,'linkHeaderHyperLink' => array(
               'url' => '#'
              ,'title' => 'View Breakdown'
              ,'class' => 'editCostBreakdown'
           )
        ));
        $inst->registerLinksArray($linkObj);
    }


}
