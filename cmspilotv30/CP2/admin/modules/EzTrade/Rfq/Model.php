<?
class CP_Admin_Modules_EzTrade_Rfq_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $fnsModDeliveryAddress = getCPFnObj('ezTrade_deliveryAddressLink');

        $SQL = "
      	SELECT qr.*
      	      ,e.enquiry_code
      	      ,e.company_id_customer
      	      ,c.company_name AS supplier_company_name
      	      ,CONCAT_WS(' ', con2.first_name, con2.last_name) AS supplier_contact
      	      ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
              ,{$fnsModDeliveryAddress->getShipToLocationSQLFields('da')} AS ship_to_location
      	FROM quote_request qr
      	LEFT JOIN (enquiry e)    ON (qr.enquiry_id = e.enquiry_id)
      	LEFT JOIN (company c)    ON (qr.company_id_supplier = c.company_id)
      	LEFT JOIN (contact con)  ON (e.contact_id_customer = con.contact_id)
      	LEFT JOIN (contact con2) ON (qr.contact_id_supplier = con2.contact_id)
      	LEFT JOIN (staff s)      ON (e.staff_id = s.staff_id)
        LEFT JOIN delivery_address da ON (da.delivery_address_id = qr.delivery_address_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');

        $status           = $fn->getReqParam('status');
        $enquiry_id       = $fn->getReqParam('enquiry_id');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "qr.quote_request_id = {$tv['record_id']}";
        } else {

            if ($status != ''){
                $searchVar->sqlSearchVar[] = "qr.status = '{$status}'";
            }

            if ($enquiry_id != ''){
                $searchVar->sqlSearchVar[] = "qr.enquiry_id = {$enquiry_id}";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       qr.title  LIKE '%{$tv['keyword']}%'
                    OR c.company_name LIKE '%{$tv['keyword']}%'
                    OR s.first_name LIKE '%{$tv['keyword']}%'
                    OR s.last_name LIKE '%{$tv['keyword']}%'
                    OR e.enquiry_code LIKE '%{$tv['keyword']}%'
                    OR qr.quote_request_code LIKE '%{$tv['keyword']}%'
                )";
            }
        }

        $searchVar->sortOrder = "qr.creation_date DESC";

    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        //$validate->validateData('subject', 'Please enter enquiry title');

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
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
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

        $fa = $fn->addToFieldsArray($fa, 'company_id_supplier');
        $fa = $fn->addToFieldsArray($fa, 'contact_id_supplier');
        $fa = $fn->addToFieldsArray($fa, 'quote_request_date');
        $fa = $fn->addToFieldsArray($fa, 'followup_date');
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'shipping_method');
        $fa = $fn->addToFieldsArray($fa, 'required_shipping_method');
        $fa = $fn->addToFieldsArray($fa, 'delivery_terms_supplier');
        $fa = $fn->addToFieldsArray($fa, 'delivery_address_id');
        $fa = $fn->addToFieldsArray($fa, 'required_delivery_terms');
        $fa = $fn->addToFieldsArray($fa, 'notes_from_supplier');
        $fa = $fn->addToFieldsArray($fa, 'notes_to_supplier');
        $fa = $fn->addToFieldsArray($fa, 'buy_currency');
        $fa = $fn->addToFieldsArray($fa, 'valid_until');
        $fa = $fn->addToFieldsArray($fa, 'payment_terms');
        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'ship_to_location_text');

        return $fa;
    }

    /**
     *
     */
    function getEzTradeRfqEzTradeProductLinkSQL($id) {

        $SQL = "
        SELECT DISTINCT
               qri.quote_request_items_id
              ,CONCAT_WS('-', qr.quote_request_code, qri.line_no) AS line_no
              ,p.product_id
              ,p.product_code
              ,p.title AS product_name
              ,p.unit
              ,qri.quantity
              ,qri.buy_unit_price
              ,qri.quantity * qri.buy_unit_price AS buy_price
              ,qri.status
              ,(SELECT SUM(quantity) FROM quote_request_items WHERE quote_request_id = {$id}) AS quantity_sum
              ,(SELECT SUM(quantity * buy_unit_price) FROM quote_request_items WHERE quote_request_id = {$id}) AS buy_price_sum
        FROM quote_request_items qri
        JOIN quote_request qr ON (qr.quote_request_id = qri.quote_request_id)
        JOIN product p ON (p.product_id = qri.product_id)
        WHERE qri.quote_request_id = {$id}
        ";

        return $SQL;
    }
}
