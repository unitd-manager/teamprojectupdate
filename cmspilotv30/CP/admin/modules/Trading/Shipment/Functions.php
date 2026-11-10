<?
class CP_Admin_Modules_Trading_Shipment_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('trading_shipment');
        $modules->registerModule($modObj, array(
            'relatedTables' => array('shipment_items')
           ,'actBtnsList' => array('new')
           ,'actBtnsDetail' => array('edit', 'delete', 'tradingShipmentReceived', 'tradingPrintProductLabel')
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
        $linkObj = $inst->getLinksArrayObj('trading_shipment', 'trading_inventoryLink', array(
            'historyTableName' => 'shipment_items'
           ,'portalDialogHeight' => 600
           ,'keyFieldForHistory' => 'inventory_id'
           ,'keyFieldForLinking' => 'inventory_id'
           ,'anchorFieldsArr' => array('product_code' => $inst->getLinkAnchorObj('product_code', 'product_id'))
           ,'fieldlabel' => array('Product Code'
                                 ,'Web Code'
                                 ,'Inventory Serial'
                                 ,'Product Name'
                                 ,'Status'
                                 ,'Location'
                                 ,'UOM'
                                 ,'CBM'
                                 )
           ,'summaryFieldsArray' => array(
               'cbm_per_pc'
            )
           ,'showAnchorInLinkPortal' => false
           ,'anchorFieldsArr' => array(
               'product_code' => $inst->getLinkAnchorObj('product_code', 'product_id', false, 'trading_product')
              ,'product_name' => $inst->getLinkAnchorObj('product_name', 'product_id', false, 'trading_product')
              ,'serial_no' => $inst->getLinkAnchorObj('serial_no', 'inventory_id', false, 'trading_inventory')
           )
        ));
        $inst->registerLinksArray($linkObj);
    }


    function setReportsArray($repInst) {
        $fn = Zend_Registry::get('fn');

        $repInst->setReportArrayObj('trading_shipment', 'shipmentProductLabel');
        $arr = &$repInst->reportsArray['trading_shipment']['shipmentProductLabel'];
        $arr['jasperFileName'] = 'productLabel.jasper';
    }
    
    function getTradingShipmentTradingInventoryLinkAddLinkCallback($shipment_items_id, $rowSI) {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        //set default values
        $fa = array();
        $fa['location']    = 'in shipment';
        $fa['shipment_id'] = $rowSI['shipment_id'];

        $whereCondition = "
        WHERE inventory_id = {$rowSI['inventory_id']}
        ";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'inventory', $whereCondition);
        $db->sql_query($SQL);
    }

    function getTradingShipmentTradingInventoryLinkDeleteLinkCallback($shipment_id, $inventory_id) {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        //set default values
        $fa = array();
        $fa['location'] = 'ready to ship';

        $whereCondition = "
        WHERE inventory_id = {$inventory_id}
        ";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'inventory', $whereCondition);
        $db->sql_query($SQL);
    }


}