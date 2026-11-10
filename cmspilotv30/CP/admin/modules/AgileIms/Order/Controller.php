<?
class CP_Admin_Modules_AgileIms_Order_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getGenerateInvoiceFormSubmit() {
        return $this->model->getGenerateInvoiceFormSubmit();
    }

    /**
     *
     */
    function getPrintInvoiceForStudent() {
        return $this->view->getPrintInvoiceForStudent();
    }
    /**
     *
     */
    function getPrintInvoiceForCompany() {
        return $this->view->getPrintInvoiceForCompany();
    }

    /**
     *
     */
    function getPrintReceipt() {
        return $this->view->getPrintReceipt();
    }

    /**
     *
     */
    function getGenerateReceiptForm() {
        return $this->view->getGenerateReceiptForm();
    }

    /**
     *
     */
    function getPrintInvoicePdf() {
        return $this->view->getPrintInvoicePdf();
    }

    /**
     *
     */
    function getGenerateReceiptFormSubmit() {
        return $this->model->getGenerateReceiptFormSubmit();
    }

    /**
     *
     */
    function getPopulateReceiptAmount() {
        return $this->model->getPopulateReceiptAmount();
    }

    /**
     *
     */
    function getInvoiceRecords() {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $order_id = $fn->getReqParam('order_id');
        $SQL = "
        SELECT o.*
              ,gc1.name AS cust_country_name
              ,gc2.name AS shipping_country_name
              ,IF(o.contact_id > 0, 'Indvidual', 'Company') AS contact_type
              ,(SELECT (SUM(i.invoice_amount))
               FROM invoice i
               WHERE i.order_id = o.order_id
               ) AS order_amount
        FROM `order` o
        LEFT JOIN geo_country gc1 ON (o.cust_address_country_code = gc1.country_code)
        LEFT JOIN geo_country gc2 ON (o.shipping_address_country_code = gc2.country_code)
        WHERE order_id = '{$order_id}'
        ";
        $arr = $dbUtil->getSQLResultAsArray($SQL);
        if (is_array($arr)){
            $row = $arr[0];
            return $this->view->getInvoiceRecords($row);
        }
    }
}