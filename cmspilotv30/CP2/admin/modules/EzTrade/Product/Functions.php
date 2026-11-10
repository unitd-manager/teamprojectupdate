<?
class CP_Admin_Modules_EzTrade_Product_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('ezTrade_product');
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
           ,'actBtnsList'   => array('new')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('ezTrade_product', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
            'maxWidthL' => 900
           ,'maxHeightL' => 900
        ));
    }
    
    /**
     *
     */
    function setLinksArray($inst) {
        $cpCfg = Zend_Registry::get('cpCfg');

        //--------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('ezTrade_product', 'ezTrade_rfqItemsLink', array(
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
               'company_name' => $inst->getLinkAnchorObj('company_name', 'company_id', false, 'company')
              ,'quote_request_line_no' => $inst->getLinkAnchorObj('quote_request_line_no'
                                                                 ,'quote_request_id'
                                                                 ,false
                                                                 ,'rfq'
                                          )
            )
            ,'fieldlabel'      => array('RFQ Line #'
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
                                 )
        ));
        $inst->registerLinksArray($linkObj);

    }

    /**
     *
     */
}
