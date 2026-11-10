<?
class CP_Admin_Modules_EzTrade_ProductLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{

    /**
     *
     */
    function getSQL() {
        $fn = Zend_Registry::get('fn');
        $srcRoom  = $fn->getReqParam('srcRoom');

        if ($srcRoom == 'ezTrade_shipment') {
            $SQL = "
            SELECT p.*
                  ,c.title AS category_title
                  ,sc.title AS sub_category_title
            FROM product p
            LEFT JOIN category c      ON (c.category_id = p.category_id)
            LEFT JOIN sub_category sc ON (sc.sub_category_id = p.sub_category_id)
            ";

        } else {
            $product = getCPModelObj('ezTrade_product');
            $SQL = $product->getSQL();
        }

        return $SQL;
    }

    function getSavePortalFromEnquiry(){
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        if (!$this->getEditPortalFromEnquiryValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFieldsFromEnquiry();
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

    /**
     *
     */
    function getSavePortalFromRfq(){
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        if (!$this->getEditPortalFromRFQValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFieldsFromRfq();
        $id = $fn->saveRecord($fa, 'quote_request_items', 'quote_request_items_id');
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditPortalFromRfqValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        $validate->validateData('quantity', 'Please enter the quantity');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getFieldsFromRfq(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'quantity');
        $fa = $fn->addToFieldsArray($fa, 'buy_unit_price');
        $fa = $fn->addToFieldsArray($fa, 'buy_unit_price_base');
        $fa = $fn->addToFieldsArray($fa, 'request_delivery_date');
        $fa = $fn->addToFieldsArray($fa, 'packing_requirement');
        $fa = $fn->addToFieldsArray($fa, 'notes_to_supplier');
        $fa = $fn->addToFieldsArray($fa, 'lead_time');
        $fa = $fn->addToFieldsArray($fa, 'min_order_quantity');
        $fa = $fn->addToFieldsArray($fa, 'order_multiplier');
        $fa = $fn->addToFieldsArray($fa, 'notes_from_supplier');
        $fa = $fn->addToFieldsArray($fa, 'country_of_origin');
        $fa = $fn->addToFieldsArray($fa, 'packing_details');
        $fa = $fn->addToFieldsArray($fa, 'carton_dimensions');
        $fa = $fn->addToFieldsArray($fa, 'gross_weight');
        $fa = $fn->addToFieldsArray($fa, 'net_weight');
        $fa = $fn->addToFieldsArray($fa, 'total_volume');

        return $fa;
    }

    /**
     *
     */
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

    /**
     *
     */
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

    /**
     *
     */
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
    function getSavePortalFromSO(){
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        if (!$this->getEditPortalFromSOValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFieldsFromSO();
        $id = $fn->saveRecord($fa, 'sales_order_items', 'sales_order_items_id');
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditPortalFromSOValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        $validate->validateData('quantity', 'Please enter the quantity');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getFieldsFromSO(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'quantity');
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
        $fa = $fn->addToFieldsArray($fa, 'remarks');
        $fa = $fn->addToFieldsArray($fa, 'packing_details');
        $fa = $fn->addToFieldsArray($fa, 'carton_dimensions');
        $fa = $fn->addToFieldsArray($fa, 'gross_weight');
        $fa = $fn->addToFieldsArray($fa, 'delivery_terms');
        $fa = $fn->addToFieldsArray($fa, 'shipping_method');
        $fa = $fn->addToFieldsArray($fa, 'net_weight');
        $fa = $fn->addToFieldsArray($fa, 'total_volume');
        $fa = $fn->addToFieldsArray($fa, 'request_date');
        $fa = $fn->addToFieldsArray($fa, 'promised_delivery_date');
        $fa = $fn->addToFieldsArray($fa, 'estimated_delivery_date');
        $fa = $fn->addToFieldsArray($fa, 'estimated_arrival_date');
        $fa = $fn->addToFieldsArray($fa, 'note_from_customer');
        $fa = $fn->addToFieldsArray($fa, 'customer_po_line_no');

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
    function getSavePortalFromInvoice(){
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        if (!$this->getEditPortalFromInvoiceValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFieldsFromInvoice();
        $id = $fn->saveRecord($fa, 'invoice_items', 'invoice_items_id');
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditPortalFromInvoiceValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        $validate->validateData('sell_price', 'Please enter the Invoice Amount');
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
    function getFieldsFromInvoice(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'sell_price');
        $fa = $fn->addToFieldsArray($fa, 'sell_price_received');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'remarks');

        return $fa;
    }

    /**
     *
     */
    function getSavePortalFromPO(){
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $fa = $this->getFieldsFromPO();
        $id = $fn->saveRecord($fa, 'purchase_order_items', 'purchase_order_items_id');
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getFieldsFromPO(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'quantity');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'remarks');
        $fa = $fn->addToFieldsArray($fa, 'buy_unit_price');
        $fa = $fn->addToFieldsArray($fa, 'buy_unit_price_base');
        $fa = $fn->addToFieldsArray($fa, 'request_delivery_date');
        $fa = $fn->addToFieldsArray($fa, 'packing_requirement');
        $fa = $fn->addToFieldsArray($fa, 'notes_to_supplier');
        $fa = $fn->addToFieldsArray($fa, 'lead_time');
        $fa = $fn->addToFieldsArray($fa, 'min_order_quantity');
        $fa = $fn->addToFieldsArray($fa, 'order_multiplier');
        $fa = $fn->addToFieldsArray($fa, 'valid_until2', '', false, 'valid_until');
        $fa = $fn->addToFieldsArray($fa, 'notes_from_supplier');
        $fa = $fn->addToFieldsArray($fa, 'country_of_origin');
        $fa = $fn->addToFieldsArray($fa, 'delivery_terms');
        $fa = $fn->addToFieldsArray($fa, 'total_volume');
        $fa = $fn->addToFieldsArray($fa, 'request_date');
        $fa = $fn->addToFieldsArray($fa, 'estimated_delivery_date');
        $fa = $fn->addToFieldsArray($fa, 'total_paid_amount');
        $fa = $fn->addToFieldsArray($fa, 'back_order_quantity');
        $fa = $fn->addToFieldsArray($fa, 'open_quantity');
        $fa = $fn->addToFieldsArray($fa, 'quantity_delivered');
        return $fa;
    }
}
