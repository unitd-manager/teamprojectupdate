<?
class CP_Admin_Modules_Trading_Invoice_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $SQL = "
        SELECT i.*
              ,i.status AS invoice_status
              ,com.company_name
              ,so.tax_percentage
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) AS contact_name_customer
              ,CONCAT_WS(' ', c.first_name, c.last_name) AS contact_name
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name

              ,(SELECT SUM(soi.sell_unit_price * soi.quantity)
                FROM sales_order_items soi
                WHERE soi.sales_order_id = so.sales_order_id) AS order_value

              ,(SELECT SUM(soi.sell_unit_price * soi.quantity) * (1 + so.tax_percentage /100)
                   FROM sales_order_items soi
                   WHERE soi.sales_order_id = so.sales_order_id
               ) AS order_value_tax

              ,(SELECT SUM(i.sell_unit_price_actual)
                FROM sales_order_inventory soi
                JOIN inventory i ON i.inventory_id = soi.inventory_id
                WHERE soi.sales_order_id = so.sales_order_id) AS order_value_inventory

              ,(SELECT SUM(i.sell_unit_price_actual) * (1 + so.tax_percentage /100)
                    FROM sales_order_inventory soi
                    JOIN inventory i ON i.inventory_id = soi.inventory_id
                    WHERE soi.sales_order_id = so.sales_order_id
               ) AS order_value_tax_inventory

              ,so.so_code
              ,so.client_so_no
              ,so.enquiry_id #used in right panel
              ,(i.invoice_amount + i.delivery_amount + i.other_cost) *
               ((100 + so.tax_percentage) / 100) AS invoice_total_amount
        FROM invoice i
        JOIN sales_order so ON so.sales_order_id = i.sales_order_id
        LEFT JOIN contact c ON c.contact_id = i.contact_id_customer
        LEFT JOIN company com ON i.company_id_customer = com.company_id
        LEFT JOIN contact cont ON i.contact_id_customer = cont.contact_id
        LEFT JOIN staff s ON i.staff_id = s.staff_id
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
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "i.flag = 1";
            }
            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(i.flag != 1 OR i.flag IS null)";
            }
        }
        $searchVar->sortOrder = "i.invoice_date DESC";

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
        $fa = $fn->addToFieldsArray($fa, 'invoice_amount');
        $fa = $fn->addToFieldsArray($fa, 'delivery_amount');
        $fa = $fn->addToFieldsArray($fa, 'delivery_terms');
        $fa = $fn->addToFieldsArray($fa, 'other_cost_lbl');
        $fa = $fn->addToFieldsArray($fa, 'other_cost');

        return $fa;
    }

    /**
     *
     */
    function getTradingInvoiceTradingProductLinkSQL($id) {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT ii.invoice_items_id
              ,CONCAT_WS('-', i.invoice_code, ii.line_no) AS line_no
              ,p.product_id
              ,p.product_code
              ,p.web_code
              ,p.title AS product_name
              ,ii.quantity
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
        ORDER BY p.web_code
        ";

        return $SQL;
    }

    /**
     *
     */
    function getTradingInvoiceTradingInventoryLinkSQL($id) {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT p.product_id
              ,p.product_code
              ,p.web_code
              ,ivt.inventory_id
              ,ivt.serial_no
              ,p.title AS product_name
              ,p.unit
              ,so.sell_currency
              ,CASE
               WHEN ivt.retail_unit_price_discount > 0
               THEN ivt.retail_unit_price_discount
               ELSE ivt.retail_unit_price
               END AS sell_unit_price

              ,ivt.sell_unit_price_actual
              ,ivt.status

              ,(SELECT SUM( CASE
                         WHEN ivt2.retail_unit_price_discount > 0
                         THEN ivt2.retail_unit_price_discount
                         ELSE ivt2.retail_unit_price
                         END
                       )
                FROM inventory ivt2
                JOIN invoice_inventory ii2 ON ii2.inventory_id = ivt2.inventory_id
                WHERE ii2.invoice_id = {$id}
               ) AS sell_unit_price_sum

              ,(SELECT SUM(ivt2.sell_unit_price_actual)
                FROM inventory ivt2
                JOIN invoice_inventory ii2 ON ii2.inventory_id = ivt2.inventory_id
                WHERE ii2.invoice_id = {$id}
               ) AS sell_unit_price_actual_sum

        FROM invoice_inventory ii
        JOIN inventory ivt ON ivt.inventory_id = ii.inventory_id
        JOIN invoice i ON i.invoice_id = ii.invoice_id
        JOIN sales_order so ON so.sales_order_id = i.sales_order_id
        JOIN product p ON ivt.product_id = p.product_id
        WHERE i.invoice_id = {$id}
        ORDER BY p.web_code
        ";

        return $SQL;
    }

    function getExportData(){
        $fn = Zend_Registry::get('fn');

        $tbsExcel = includeCPClass('Lib', 'TBSExcelExportWrapper', 'TBSExcelExportWrapper');

        $output_file_name = 'Invoice_' . date('d-m-Y') . '.xlsx';
        $template = __DIR__ . '/assets/invoice.xlsx';

        $config = array(
             'output_file_name' => $output_file_name
            ,'dataArray' => $this->dataArray
            ,'template' => $template
        );

        return $tbsExcel->exportData($config);
    }
}
