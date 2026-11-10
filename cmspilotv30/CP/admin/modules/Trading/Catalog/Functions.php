<?
class CP_Admin_Modules_Trading_Catalog_Functions extends CP_Admin_Modules_Trading_Product_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('trading_catalog');
        $modules->registerModule($modObj, array(
            'tableName' => 'product'
           ,'keyField' => 'product_id'
           ,'title' => 'Catalogue'
           ,'hasFlagInList' => 0
           ,'hasEditInList' => 0
           ,'actBtnsList' => array()
           ,'actBtnsDetail' => array()
        ));
    }

    function setMediaArray($mediaArr) {
        return getCPFnObj('trading_product')->setMediaArray($mediaArr);
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $cpCfg = Zend_Registry::get('cpCfg');

        //----------------------------------------------------------//
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
        ));
        $inst->registerLinksArray($linkObj);

        //----------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('trading_catalog', 'trading_pricingTypeLink', array(
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
        ));
        $inst->registerLinksArray($linkObj);
    }


}
