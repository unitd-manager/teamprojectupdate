<?
class CP_Admin_Modules_AceIms_Order_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getPrintOrder() {
        return $this->fns->getPrintOrder();
    }    

    /**
     *
     */
    function getUpdateSubsidyStatus() {
        return $this->model->getUpdateSubsidyStatus();
    }    

    /**
     *
     */
    function getUpdateInvoiceStatus() {
        return $this->model->getUpdateInvoiceStatus();
    }    

    /**
     *
     */
    function getGenerateInvoice() {
        return $this->model->getGenerateInvoice();
    }    

    /**
     *
     */
    function getGenerateReceipt() {
        return $this->model->getGenerateReceipt();
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
    function getGenerateReceiptFormSubmit() {
        return $this->model->getGenerateReceiptFormSubmit();
    }    

    /**
     *
     */
    function getClearInvoice() {
        return $this->model->getClearInvoice();
    }    

    /**
     *
     */
    function getPrintInvoice() {
        return $this->model->getPrintInvoice();
    }    

    /**
     *
     */
    function getPrintReceipt() {
        return $this->model->getPrintReceipt();
    }    

    /**
     *
     */
    function getPrintInvoiceIndividual() {
        return $this->model->getPrintInvoiceIndividual();
    }    

    /**
     *
     */
    function getPrintReceiptIndividual() {
        return $this->model->getPrintReceiptIndividual();
    }    

    /**
     *
     */
    function getGenerateRefundForm() {
        return $this->view->getGenerateRefundForm();
    }    

    /**
     *
     */
    function getGenerateRefundFormSubmit() {
        return $this->model->getGenerateRefundFormSubmit();
    }    

    /**
     *
     */
    function getPrintRefund() {
        return $this->model->getPrintRefund();
    }    

    /**
     *
     */
    function getPrintRefundIndividual() {
        return $this->model->getPrintRefund();
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
    function getPopulateReceiptAmountPvt() {
        return $this->model->getPopulateReceiptAmountPvt();
    }    

    /**
     *
     */
    function getPopulateReceiptAmountMiscPvt() {
        return $this->model->getPopulateReceiptAmountMiscPvt();
    }    

    /**
     *
     */
    function getPopulateRefundAmount() {
        return $this->model->getPopulateRefundAmount();
    }    

    /**
     *
     */
    function getGenerateCreditNoteForm() {
        return $this->view->getGenerateCreditNoteForm();
    }    

    /**
     *
     */
    function getGenerateCreditNoteFormSubmit() {
        return $this->model->getGenerateCreditNoteFormSubmit();
    }    

    /**
     *
     */
    function getPopulateCreditNoteAmount() {
        return $this->model->getPopulateCreditNoteAmount();
    }    

    /**
     *
     */
    function getGenerateInvoiceFormPvt() {
        return $this->model->getGenerateInvoiceFormPvt();
    }    

    /**
     *
     */
    function getGenerateInvoiceFormSubmitPvt() {
        return $this->model->getGenerateInvoiceFormSubmitPvt();
    }    
    
    /**
     *
     */
    function getEditInvoiceFormPvt() {
        return $this->view->getEditInvoiceFormPvt();
    }    

    /**
     *
     */
    function getEditInvoiceFormSubmitPvt() {
        return $this->model->getEditInvoiceFormSubmitPvt();
    }    

    /**
     *
     */
    function getDeleteInvoiceFormPvt() {
        return $this->model->getDeleteInvoiceFormPvt();
    }    

    /**
     *
     */
    function getGenerateReceiptFormPvt() {
        return $this->view->getGenerateReceiptFormPvt();
    }    

    /**
     *
     */
    function getGenerateReceiptFormSubmitPvt() {
        return $this->model->getGenerateReceiptFormSubmitPvt();
    }    

    /**
     *
     */
    function getGenerateMiscReceiptFormPvt() {
        return $this->view->getGenerateMiscReceiptFormPvt();
    }    

    /**
     *
     */
    function getEditReceiptFormSubmitPvt() {
        return $this->model->getEditReceiptFormSubmitPvt();
    }    

    /**
     *
     */
    function getEditReceiptFormPvt() {
        return $this->view->getEditReceiptFormPvt();
    }    

    /**
     *
     */
    function getPopulateMiscTotalAmount() {
        return $this->model->getPopulateMiscTotalAmount();
    }    

    /**
     *
     */
    function getGenerateMonthlyInvoiceForEntForm() {
        return $this->view->getGenerateMonthlyInvoiceForEntForm();
    }    

    /**
     *
     */
    function getGenerateMonthlyInvoiceForEntFormSubmit() {
        return $this->model->getGenerateMonthlyInvoiceForEntFormSubmit();
    }    

    /**
     *
     */
    function getGenerateReceiptForEntForm() {
        return $this->view->getGenerateReceiptForEntForm();
    }    

    /**
     *
     */
    function getCancelReceipt() {
        return $this->model->getCancelReceipt();
    }    

    /**
     *
     */
    function getGenerateBookReceiptForm() {
        return $this->view->getGenerateBookReceiptForm();
    }    

     /**
     *
     */
    function getGenerateBookReceiptFormSubmit() {
        return $this->model->getGenerateBookReceiptFormSubmit();
    }    

     /**
     *
     */
    function getPrintInvoiceInFpdf() {
        return $this->model->getPrintInvoiceInFpdf();
    }    

     /**
     *
     */
    function getPrintReceiptInFpdf() {
        return $this->model->getPrintReceiptInFpdf();
    }    

    /**
     *
     */
    function getPrintSelectedInvoices() {
        return $this->model->getPrintSelectedInvoices();
    }    

    /**
     *
     */
    function getPrintGroupReceiptInFpdf() {
        return $this->model->getPrintGroupReceiptInFpdf();
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

    /**
     *
     */
    function getCalculateAmountPayable() {
        return $this->model->getCalculateAmountPayable();
    }    

    /**
     *
     */
    function getPopulateDiscountAmount() {
        return $this->model->getPopulateDiscountAmount();
    }

    /**
     *
     */
    function getViewSubjectsForOrderItem() {
        return $this->view->getViewSubjectsForOrderItem();
    }
}