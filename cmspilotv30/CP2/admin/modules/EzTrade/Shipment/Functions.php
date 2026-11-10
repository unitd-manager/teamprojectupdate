<?
class CP_Admin_Modules_EzTrade_Shipment_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('ezTrade_shipment');
        $modules->registerModule($modObj, array(
            'relatedTables' => array('shipment_items')
           ,'actBtnsList' => array('new')
        ));

    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

    }

    /**
     *
     */
    function setLinksArray($inst) {

        $linkObj = $inst->getLinksArrayObj('ezTrade_shipment', 'ezTrade_productLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'shipment_items'
           ,'hasPortalEdit' => 1
           ,'hasPortalDetail' => 1
           ,'portalDialogHeight' => 600
           ,'anchorFieldsArr' => array('product_code' => $inst->getLinkAnchorObj('product_code', 'product_id'))
           ,'fieldlabel' => array('Line Number'
                                 ,'Item Number'
                                 ,'Item Name'
                                 ,'Ship Quantity'
                                 ,'Status'
                                 ,'No. of Cartons'
                                 ,'UOM'
                                 )
        ));

    }
}