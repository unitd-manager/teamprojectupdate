<?
class CP_Admin_Modules_EzTrade_Invoice_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $SQL = "
        SELECT i.*
              ,com.company_name
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) AS contact_name_customer
              ,CONCAT_WS(' ', c.first_name, c.last_name) AS contact_name
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
              ,(SELECT SUM(ii.sell_price)
                FROM invoice_items ii
                WHERE ii.invoice_id = i.invoice_id) AS invoice_amount
              ,so.so_code
              ,so.client_so_no
        FROM invoice i
        JOIN sales_order so    ON (so.sales_order_id = i.sales_order_id)
        LEFT JOIN contact c    ON (c.contact_id = i.contact_id_customer)
        LEFT JOIN company com  ON (i.company_id_customer = com.company_id)
        LEFT JOIN contact cont ON (i.contact_id_customer = cont.contact_id)
        LEFT JOIN staff s      ON (i.staff_id = s.staff_id)
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
        $invoice_type = $fn->getReqParam('invoice_type');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "i.invoice_id = {$tv['record_id']}";
        } else {
            if ($status != ''){
                $searchVar->sqlSearchVar[] = "i.status = '{$status}'";
            }

            if ($invoice_type != ''){
                $searchVar->sqlSearchVar[] = "i.invoice_type = '{$invoice_type}'";
            }
        }
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('invoice_due_date', 'Please enter Invoice Due Date');
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
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'payment_terms');
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'creation_date');
        $fa = $fn->addToFieldsArray($fa, 'modification_date');
        $fa = $fn->addToFieldsArray($fa, 'sales_order_id');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'flag');
        $fa = $fn->addToFieldsArray($fa, 'invoice_date');
        $fa = $fn->addToFieldsArray($fa, 'invoice_type');
        $fa = $fn->addToFieldsArray($fa, 'contact_id_customer');
        $fa = $fn->addToFieldsArray($fa, 'company_id_customer');
        $fa = $fn->addToFieldsArray($fa, 'sell_currency');
        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'invoice_due_date');
        $fa = $fn->addToFieldsArray($fa, 'invoice_amount_received');

        return $fa;
    }

    /**
     *
     */
    function getEzTradeInvoiceEzTradeProductLinkSQL($id) {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT ii.invoice_items_id
              ,CONCAT_WS('-', i.invoice_code, ii.line_no) AS line_no
              ,p.product_id
              ,p.product_code
              ,p.title AS product_name
              ,p.unit
              ,soi.quantity
              ,so.sell_currency
              ,soi.sell_unit_price
              ,soi.quantity * soi.sell_unit_price AS sell_price_total
              ,ROUND( ((ii.sell_price / (soi.sell_unit_price * soi.quantity) ) * 100), 2) AS invoice_percentage
              ,ii.sell_price AS sell_price_invoice_amount
              ,ii.status
              ,(SELECT SUM(quantity) FROM invoice_items WHERE invoice_items_id = {$id}) AS quantity_sum
              ,(SELECT SUM(sell_unit_price * quantity) FROM invoice_items WHERE invoice_items_id = {$id}) AS sell_price_total_sum
        FROM invoice_items ii
        JOIN invoice i      ON (i.invoice_id = ii.invoice_id)
        JOIN sales_order so ON (so.sales_order_id = i.sales_order_id)
        LEFT JOIN sales_order_items soi ON (soi.sales_order_items_id = ii.sales_order_items_id)
        JOIN product p      ON (ii.product_id = p.product_id)
        WHERE ii.invoice_id = {$id}
        ";

        return $SQL;
    }
}
