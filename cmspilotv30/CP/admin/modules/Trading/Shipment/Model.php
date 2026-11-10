<?
class CP_Admin_Modules_Trading_Shipment_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $SQL = "
        SELECT sm.*
               ,c.company_name
      	       ,CONCAT_WS(' ', con.first_name, con.last_name) AS contact_name_customer
      	       ,con.phone_country_code
      	       ,con.phone
      	       ,so.so_code
      	       ,so.client_so_no AS customer_so_number
              ,CONCAT_WS(', '
                        ,da.address_flat
                        ,da.address_street
                        ,da.address_town
                        ,da.address_state
                        ,da.address_country
                        ,da.address_po_code
               ) AS ship_to_location
              ,(SELECT SUM(si.sell_unit_price * si.quantity_shipped)
                FROM shipment_items si
                WHERE si.shipment_id = sm.shipment_id) AS shipment_value
        FROM shipment sm
        LEFT JOIN (company c) 	ON (c.company_id = sm.company_id)
      	LEFT JOIN (contact con) ON (sm.contact_id = con.contact_id)
      	LEFT JOIN (sales_order so) ON (so.sales_order_id = sm.sales_order_id)
      	LEFT JOIN (delivery_address da) ON (sm.delivery_address_id = da.delivery_address_id)
        ";
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $searchVar = Zend_Registry::get('searchVar');
        $fn = Zend_Registry::get('fn');

        $status = $fn->getReqParam('status');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "sm.shipment_id = {$tv['record_id']}";
        } else {
            if ($status != ''){
                $searchVar->sqlSearchVar[] = "sm.status = '{$status}'";
            }
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "sm.flag = 1";
            }
            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(sm.flag != 1 OR sm.flag IS null)";
            }
        }

        $searchVar->sortOrder = "sm.creation_date DESC";
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('company_id', 'Please choose a client');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $company_id = $fn->getReqParam('company_id');

        $shipment_code = 'S' . $fn->getSequenceFromSettings('m.trading.shipment.nextCode');

        $fa = $this->getFields();
        $fa['shipment_code']  = $shipment_code;
        $fa['company_id']  = $company_id;
        $fa['status']        = 'new';
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id, 'detail');

    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('scheduled_ship_date', 'Please enter Ship Date');
        $validate->validateData('estimated_arrival_date', 'Please enter Estimate Arrival Date');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $shipment_id = $fn->getReqParam('shipment_id');
        $row = $fn->getRecordRowByID('shipment', 'shipment_id', $shipment_id);

        $fa = $this->getFields();

        //update inventory status
        $inventoryStatus = '';
        $prevStatus = $row['status'];
        $newStatus = $fa['status'];
        if ($prevStatus != $newStatus) {
            if ($newStatus == 'confirmed') {
                $inventoryStatus = 'in shipment';
                $SQL = "
                UPDATE inventory
                SET location = '{$inventoryStatus}'
                WHERE shipment_id = {$shipment_id}
                ";
                $db->sql_query($SQL);
            }
        }


        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id, 'detail');
    }

    /**
     *
     */
    function getSaveList(){
        $fn = Zend_Registry::get('fn');
        $fn->getSaveList();
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'booking_no');
        $fa = $fn->addToFieldsArray($fa, 'shipping_mark');
        $fa = $fn->addToFieldsArray($fa, 'forwarder');
        $fa = $fn->addToFieldsArray($fa, 'port_of_loading');
        $fa = $fn->addToFieldsArray($fa, 'port_of_discharge');
        $fa = $fn->addToFieldsArray($fa, 'delivery_address_id', 0);
        $fa = $fn->addToFieldsArray($fa, 'delivery_terms');
        $fa = $fn->addToFieldsArray($fa, 'consignee_name');
        $fa = $fn->addToFieldsArray($fa, 'consignee_address');
        $fa = $fn->addToFieldsArray($fa, 'consignee_phone');
        $fa = $fn->addToFieldsArray($fa, 'scheduled_ship_date');
        $fa = $fn->addToFieldsArray($fa, 'estimated_arrival_date');
        $fa = $fn->addToFieldsArray($fa, 'estimated_delivery_date');
        $fa = $fn->addToFieldsArray($fa, 'container_no');
        $fa = $fn->addToFieldsArray($fa, 'container_type');
        $fa = $fn->addToFieldsArray($fa, 'status');

        return $fa;
    }

    /**
     *
     */
    function getTradingShipmentTradingInventoryLinkSQL($id) {
        $SQL = "
        SELECT si.shipment_items_id
              ,p.product_id
              ,p.product_code
              ,i.serial_no
              ,p.title AS product_name
              ,i.inventory_id
              ,i.status
              ,i.location
              ,p.unit
              ,p.cbm_per_pc
              ,(SELECT SUM(p2.cbm_per_pc)
                FROM shipment_items si
                JOIN shipment s ON s.shipment_id = si.shipment_id
                JOIN inventory i ON i.inventory_id = si.inventory_id
                JOIN product p2 ON p2.product_id = i.product_id
                WHERE si.shipment_id = {$id}
               ) AS cbm_per_pc_sum
        FROM shipment_items si
        JOIN shipment s ON s.shipment_id = si.shipment_id
        JOIN inventory i ON i.inventory_id = si.inventory_id
        JOIN product p ON p.product_id = i.product_id
        WHERE si.shipment_id = {$id}
        ORDER BY p.web_code
                ,i.serial_no
        ";
        return $SQL;
    }

    function getShipmentReceived() {
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $shipment_id = $fn->getReqParam('shipment_id');

        $SQL = "
        UPDATE inventory
        SET location = 'in warehouse'
        WHERE shipment_id = {$shipment_id}
        ";
        $db->sql_query($SQL);
    }

    function getSaveInventory() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $listObj = Zend_Registry::get('listObj');
        $formObj = Zend_Registry::get('formObj');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $purchase_order_items_id = $fn->getReqParam('purchase_order_items_id');
        $statusArr = $fn->getReqParam('status', array());
        $locationArr = $fn->getReqParam('location', array());

        foreach ($locationArr as $inventory_id => $location) {
            $status = $statusArr[$inventory_id];
            $SQL = "
            UPDATE inventory
            SET status = '{$status}'
               ,location = '{$location}'
            WHERE inventory_id = {$inventory_id}
            ";
            $db->sql_query($SQL);
        }

        return $cpUtil->getJsonText('success', 'Saved');
    }
}
