<?
class CP_Admin_Modules_Trading_InventoryLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{

    /**
     *
     */
    function getSQL($linkRecType) {
        $tv = Zend_Registry::get('tv');

        if ($tv['srcRoom'] == 'trading_enquiry') {
            $SQL = "
            SELECT DISTINCT
                   i.product_id
                  ,i.location
                  ,i.flag
                  ,i.flag_blue
                  ,i.flag_green
                  ,p.product_code
                  ,p.web_code
                  ,p.title
                  ,c.title AS category_title
                  ,sc.title AS sub_category_title
                  ,(SELECT COUNT(*)
                    FROM inventory i2
                    WHERE i2.product_id = p.product_id
                      AND i2.status = 'available'
                   ) AS stock_qty
            FROM inventory i
            JOIN product p ON (p.product_id = i.product_id)
            LEFT JOIN category c ON (c.category_id = p.category_id)
            LEFT JOIN sub_category sc ON (sc.sub_category_id = p.sub_category_id)
            LEFT JOIN enquiry_product ep ON p.product_id = ep.product_id
            ";
        } else if ($tv['srcRoom'] == 'trading_salesOrder') {
            $sqlExtra = '';
            if ($linkRecType == 'linked') {
                $sqlExtra = "
                JOIN sales_order_inventory soi ON soi.inventory_id = i.inventory_id
                JOIN sales_order so ON so.sales_order_id = soi.sales_order_id
                ";
            }

            $SQL = "
            SELECT DISTINCT
                   i.inventory_id
                  ,i.location
                  ,i.serial_no
                  ,i.product_id
                  ,i.flag
                  ,i.flag_blue
                  ,i.flag_green
                  ,p.product_code
                  ,p.web_code
                  ,p.title
                  ,c.title AS category_title
                  ,sc.title AS sub_category_title
            FROM inventory i
            JOIN product p ON p.product_id = i.product_id
            {$sqlExtra}
            LEFT JOIN category c ON c.category_id = p.category_id
            LEFT JOIN sub_category sc ON sc.sub_category_id = p.sub_category_id
            ";
        } else if ($tv['srcRoom'] == 'trading_shipment') {
            $sqlExtra = '';
            if ($linkRecType == 'linked') {
                $sqlExtra = "
                JOIN shipment_items si ON si.inventory_id = i.inventory_id
                JOIN shipment s ON s.shipment_id = si.shipment_id
                ";
            }

            $SQL = "
            SELECT DISTINCT
                   i.inventory_id
                  ,i.location
                  ,i.serial_no
                  ,i.product_id
                  ,i.flag
                  ,i.flag_blue
                  ,i.flag_green
                  ,p.product_code
                  ,p.web_code
                  ,p.title
                  ,c.title AS category_title
                  ,sc.title AS sub_category_title
            FROM inventory i
            JOIN product p ON p.product_id = i.product_id
            {$sqlExtra}
            LEFT JOIN category c ON c.category_id = p.category_id
            LEFT JOIN sub_category sc ON sc.sub_category_id = p.sub_category_id
            LEFT JOIN sales_order_items soi ON p.product_id = soi.product_id
            ";
        } else {
            $product = getCPModelObj('trading_product');
            $SQL = $product->getSQL();
        }

        return $SQL;
    }


    function getSavePortalFromEnquiry(){
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        if (!$this->getEditPortalFromEnquiryValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFieldsFromEnquiry();

        $enquiry_product_id = $fn->getReqParam('enquiry_product_id');
        $rowEP = $fn->getRecordRowByID('enquiry_product', 'enquiry_product_id', $enquiry_product_id);

        $product_id = $rowEP['product_id'];
        $enquiry_id = $rowEP['enquiry_id'];
        $quantity = $fa['quantity'];

        //if quantity is changed then update the inventory status
        if ($quantity != $rowEP['quantity']) {
            $rowInventory = getCPModelObj('trading_inventory')->
                            getUpdateStockStatus('trading_enquiry', $enquiry_id, $product_id, $quantity, 'on enquiry');

            $SQL = "
            UPDATE enquiry_product
            SET purchase_order_items_id = {$rowInventory['purchase_order_items_id']}
            WHERE enquiry_product_id = {$enquiry_product_id}
            ";
            $db->sql_query($SQL);
        }

        //save history record
        $id = $fn->saveRecord($fa, 'enquiry_product', 'enquiry_product_id');

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditPortalFromEnquiryValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();
        $validate->validateData('quantity', 'Please enter the quantity');
        $quantity = $fn->getPostParam('quantity');

        if ($quantity == 0) {
            $validate->updateErrorArray('quantity', 'Please enter a valid quantity');
        }

        $enquiry_product_id = $fn->getReqParam('enquiry_product_id');
        $rowEP = $fn->getRecordRowByID('enquiry_product', 'enquiry_product_id', $enquiry_product_id);

        $availableStock = getCPModelObj('trading_inventory')->getAvailableStock($rowEP['product_id']);
        if ($quantity > $availableStock) {
            $msg = "We only have {$availableStock} items in the stock";
            $validate->updateErrorArray('quantity', $msg);
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getFieldsFromEnquiry(){
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'quantity');
        $fa = $fn->addToFieldsArray($fa, 'delivery_date');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'packing_requirement');
        $fa = $fn->addToFieldsArray($fa, 'remark');

        return $fa;
    }

    function getSavePortalFromQuote(){
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        if (!$this->getEditPortalFromQuoteValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFieldsFromQuote();
        $id = $fn->saveRecord($fa, 'quote_items', 'quote_items_id');
        //$id = $fn->saveRecord($fa);
        return $validate->getSuccessMessageXML();
    }

    function getEditPortalFromQuoteValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    function getFieldsFromQuote(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'lead_time');
        $fa = $fn->addToFieldsArray($fa, 'sell_unit_price');
        $fa = $fn->addToFieldsArray($fa, 'sell_unit_price_base');
        $fa = $fn->addToFieldsArray($fa, 'other_costs_1');
        $fa = $fn->addToFieldsArray($fa, 'other_costs_2');
        $fa = $fn->addToFieldsArray($fa, 'other_costs_3');
        $fa = $fn->addToFieldsArray($fa, 'other_costs_1_curr');
        $fa = $fn->addToFieldsArray($fa, 'other_costs_2_curr');
        $fa = $fn->addToFieldsArray($fa, 'other_costs_3_curr');
        $fa = $fn->addToFieldsArray($fa, 'other_costs_1_base');
        $fa = $fn->addToFieldsArray($fa, 'other_costs_2_base');
        $fa = $fn->addToFieldsArray($fa, 'other_costs_3_base');
        $fa = $fn->addToFieldsArray($fa, 'other_costs_1_label');
        $fa = $fn->addToFieldsArray($fa, 'other_costs_2_label');
        $fa = $fn->addToFieldsArray($fa, 'other_costs_3_label');
        $fa = $fn->addToFieldsArray($fa, 'markup');
        $fa = $fn->addToFieldsArray($fa, 'margin_percent');
        $fa = $fn->addToFieldsArray($fa, 'valid_until');
        $fa = $fn->addToFieldsArray($fa, 'note_to_customer');
        $fa = $fn->addToFieldsArray($fa, 'packing_details');
        $fa = $fn->addToFieldsArray($fa, 'carton_dimensions');
        $fa = $fn->addToFieldsArray($fa, 'gross_weight');
        $fa = $fn->addToFieldsArray($fa, 'delivery_terms');
        $fa = $fn->addToFieldsArray($fa, 'shipping_method');
        $fa = $fn->addToFieldsArray($fa, 'net_weight');
        $fa = $fn->addToFieldsArray($fa, 'total_volume');

        return $fa;
    }

    /**
     *
     */
    function getSavePortalFromShipment(){
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        if (!$this->getEditPortalFromShipmentValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFieldsFromShipment();
        $id = $fn->saveRecord($fa, 'shipment_items', 'shipment_items_id');
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditPortalFromShipmentValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        $validate->validateData('quantity_shipped', 'Please enter the Ship Quantity');
        $validate->validateData('status', 'Please enter the Status');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getFieldsFromShipment(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'quantity_shipped');
        $fa = $fn->addToFieldsArray($fa, 'no_of_carton');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'total_shipment_value');
        $fa = $fn->addToFieldsArray($fa, 'packing_details');
        $fa = $fn->addToFieldsArray($fa, 'dimension_h');
        $fa = $fn->addToFieldsArray($fa, 'dimension_w');
        $fa = $fn->addToFieldsArray($fa, 'dimension_d');
        $fa = $fn->addToFieldsArray($fa, 'net_weight');
        $fa = $fn->addToFieldsArray($fa, 'gross_weight');
        $fa = $fn->addToFieldsArray($fa, 'total_volume');
        $fa = $fn->addToFieldsArray($fa, 'country_of_origin');
        $fa = $fn->addToFieldsArray($fa, 'remarks');

        return $fa;
    }

    /**
     *
     */
    function setHavingVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');

        $product_id      = $fn->getReqParam('product_id');
        $category_id     = $fn->getReqParam('category_id');
        $sub_category_id = $fn->getReqParam('sub_category_id');
        $collection_name = $fn->getReqParam('collection_name');
        $status = $fn->getReqParam('status');

        if ($linkRecType != 'not linked') {
            //temporarily disabled
            //$searchVar->sqlHavingVar[] = "stock_qty > 0";
        }
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');

        $product_id      = $fn->getReqParam('product_id');
        $category_id     = $fn->getReqParam('category_id');
        $sub_category_id = $fn->getReqParam('sub_category_id');
        $collection_name = $fn->getReqParam('collection_name');
        $status          = $fn->getReqParam('status');
        $location        = $fn->getReqParam('location');
        $sales_order_id  = $fn->getReqParam('sales_order_id');
        $linkMasterTableID = $fn->getReqParam('linkMasterTableID');
        $company_id_customer = $fn->getReqParam('company_id_customer');

        if ($product_id != '') {
            $searchVar->sqlSearchVar[] = "p.product_id = {$product_id}";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "p.product_id = {$tv['record_id']}";
        } else {
            if ($linkRecType != '') {
                if ($tv['srcRoom'] == 'trading_enquiry' && $linkRecType == 'linked') {
                    $searchVar->sqlSearchVar[] = "ep.record_type = 'inventory'";

                } else if ($tv['srcRoom'] == 'trading_salesOrder') {
                    if ($linkRecType == 'notLinked') {
                        $searchVar->sqlSearchVar[] = "i.status = 'available'";
                    } else if ($linkRecType == 'linked') {
                        $searchVar->sqlSearchVar[] = "soi.sales_order_id = {$linkMasterTableID}";
                    }

                } else if ($tv['srcRoom'] == 'trading_shipment') {
                    if ($linkRecType == 'notLinked') {
                        $searchVar->sqlSearchVar[] = "i.location = 'ready to ship'";
                    }
                    if ($linkRecType == 'linked') {
                        $searchVar->sqlSearchVar[] = "si.shipment_id = {$linkMasterTableID}";
                    }
                    if ($sales_order_id != ''){
                        $searchVar->sqlSearchVar[] = "i.sales_order_id = {$sales_order_id}";
                    }
                    if ($company_id_customer != ''){
                        $searchVar->sqlSearchVar[] = "i.company_id_customer = {$company_id_customer}";
                    }
                }
            }
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'i.inventory_id');
            if ($category_id != ''){
                $searchVar->sqlSearchVar[] = "p.category_id = {$category_id}";
            }
            if ($sub_category_id != ''){
                $searchVar->sqlSearchVar[] = "p.sub_category_id = {$sub_category_id}";
            }
            if ($collection_name != ''){
                $searchVar->sqlSearchVar[] = "p.collection_name = '{$collection_name}'";
            }
            if ($status != ''){
                $searchVar->sqlSearchVar[] = "i.status = '{$status}'";
            }
            if ($location != ''){
                $searchVar->sqlSearchVar[] = "i.location = '{$location}'";
            }
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "i.flag = 1";
            }
            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(i.flag != 1 OR i.flag IS null)";
            }
            if ($tv['special_search'] == "Flagged - Blue") {
                $searchVar->sqlSearchVar[] = "i.flag_blue = 1";
            }
            if ($tv['special_search'] == "Not-Flagged - Blue") {
                $searchVar->sqlSearchVar[] = "(i.flag_blue != 1 OR i.flag_blue IS null)";
            }            
            if ($tv['special_search'] == "Flagged - Green") {
                $searchVar->sqlSearchVar[] = "i.flag_green = 1";
            }
            if ($tv['special_search'] == "Not-Flagged - Green") {
                $searchVar->sqlSearchVar[] = "(i.flag_green != 1 OR i.flag_green IS null)";
            }
            if ($tv['keyword'] != ""){
                $searchVar->sqlSearchVar[] = "
                (  p.title LIKE '%{$tv['keyword']}%'
                OR p.product_code LIKE '%{$tv['keyword']}%'
                OR p.web_code LIKE '%{$tv['keyword']}%'
                )";
            }
        }
    }

}
